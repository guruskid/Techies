<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PaystackPaymentService
{
    public function initializeMembershipPayment(User $user, int $amount, string $currency, string $callbackUrl): string
    {
        return $this->initialize($user, $amount, $currency, $callbackUrl, 'membership', []);
    }

    public function initializeEventPayment(User $user, Event $event, EventRegistration $registration, string $callbackUrl): string
    {
        return $this->initialize($user, (float) $event->fee, 'NGN', $callbackUrl, 'event', [
            'event_id' => $event->id,
            'event_registration_id' => $registration->id,
        ]);
    }

    /**
     * @param  array<string, int|string>  $metadata
     */
    private function initialize(User $user, float|int $amount, string $currency, string $callbackUrl, string $purpose, array $metadata): string
    {
        if ($amount <= 0) {
            throw new RuntimeException('The payment amount must be greater than zero.');
        }

        $secretKey = (string) config('services.paystack.secret_key');

        if ($secretKey === '') {
            throw new RuntimeException('Online payments are not configured.');
        }

        $transaction = PaymentTransaction::create([
            'user_id' => $user->id,
            'reference' => 'MWTT-'.Str::upper(Str::random(24)),
            'purpose' => $purpose,
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'status' => 'pending',
            'metadata' => array_merge(['purpose' => $purpose], $metadata),
        ]);

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->timeout(15)
            ->post(rtrim((string) config('services.paystack.base_url'), '/').'/transaction/initialize', [
                'email' => $user->email,
                'amount' => (string) round($amount * 100),
                'currency' => strtoupper($currency),
                'reference' => $transaction->reference,
                'callback_url' => $callbackUrl,
                'metadata' => array_merge(['purpose' => $purpose, 'transaction_id' => $transaction->id], $metadata),
            ]);

        $checkoutUrl = $response->json('data.authorization_url');

        if (! $response->successful() || ! $response->json('status') || ! is_string($checkoutUrl)) {
            $transaction->update(['status' => 'failed']);
            throw new RuntimeException((string) $response->json('message', 'Could not start the payment.'));
        }

        $transaction->update([
            'metadata' => array_merge($transaction->metadata ?? [], ['checkout_url' => $checkoutUrl]),
        ]);

        return $checkoutUrl;
    }

    public function verify(string $reference): PaymentTransaction
    {
        $transaction = PaymentTransaction::where('reference', $reference)->firstOrFail();

        if ($transaction->status === 'success') {
            return $transaction;
        }

        $secretKey = (string) config('services.paystack.secret_key');

        if ($secretKey === '') {
            throw new RuntimeException('Online payments are not configured.');
        }

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->timeout(15)
            ->get(rtrim((string) config('services.paystack.base_url'), '/').'/transaction/verify/'.urlencode($reference));

        $payment = $response->json('data');

        if (! $response->successful() || ! $response->json('status') || ! is_array($payment)) {
            throw new RuntimeException('The payment could not be verified.');
        }

        if (($payment['status'] ?? null) !== 'success'
            || ($payment['reference'] ?? null) !== $reference
            || (int) ($payment['amount'] ?? 0) !== (int) round((float) $transaction->amount * 100)
            || strtoupper((string) ($payment['currency'] ?? '')) !== strtoupper($transaction->currency)) {
            throw new RuntimeException('The payment details do not match the expected amount.');
        }

        $transaction->update([
            'status' => 'success',
            'paid_at' => now(),
            'metadata' => array_merge($transaction->metadata ?? [], [
                'channel' => $payment['channel'] ?? null,
                'gateway_reference' => $payment['id'] ?? null,
            ]),
        ]);

        return $transaction->refresh();
    }

    public function refund(PaymentTransaction $transaction, string $reason): void
    {
        if ($transaction->provider !== 'paystack' || $transaction->status !== 'success') {
            throw new RuntimeException('Only successful Paystack transactions can be refunded.');
        }

        $secretKey = (string) config('services.paystack.secret_key');

        if ($secretKey === '') {
            throw new RuntimeException('Paystack payments are not configured.');
        }

        $reference = $transaction->refund_reference ?: 'MWTT-RF-'.Str::upper(Str::random(24));
        $transaction->update(['refund_reference' => $reference]);

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->timeout(15)
            ->post(rtrim((string) config('services.paystack.base_url'), '/').'/refund', [
                'transaction' => $transaction->reference,
                'reference' => $reference,
                'merchant_note' => $reason,
            ]);

        if (! $response->successful() || ! $response->json('status')) {
            throw new RuntimeException((string) $response->json('message', 'Paystack could not start the refund.'));
        }

        $transaction->update([
            'status' => 'refund_pending',
            'metadata' => array_merge($transaction->metadata ?? [], [
                'refund_status' => $response->json('data.status', 'pending'),
                'refund_reason' => $reason,
            ]),
        ]);
    }

    public function hasValidWebhookSignature(string $payload, ?string $signature, ?string $legacySignature): bool
    {
        $webhookSecret = (string) config('services.paystack.webhook_secret');

        if ($webhookSecret !== '' && is_string($signature)) {
            $timestamp = null;
            $signatures = [];

            foreach (explode(',', $signature) as $part) {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

                if ($key === 't' && is_string($value) && ctype_digit($value)) {
                    $timestamp = (int) $value;
                }

                if ($key === 'v1' && is_string($value) && str_contains($value, ':')) {
                    $signatures[] = substr($value, strrpos($value, ':') + 1);
                }
            }

            if ($timestamp !== null && abs(now()->timestamp - $timestamp) <= 300) {
                $expected = hash_hmac('sha512', $timestamp.'.'.$payload, $webhookSecret);

                foreach ($signatures as $candidate) {
                    if (preg_match('/^[a-f0-9]{128}$/', $candidate) === 1 && hash_equals($expected, $candidate)) {
                        return true;
                    }
                }
            }
        }

        $secretKey = (string) config('services.paystack.secret_key');

        return $secretKey !== '' && is_string($legacySignature)
            && preg_match('/^[a-f0-9]{128}$/', $legacySignature) === 1
            && hash_equals(hash_hmac('sha512', $payload, $secretKey), $legacySignature);
    }
}
