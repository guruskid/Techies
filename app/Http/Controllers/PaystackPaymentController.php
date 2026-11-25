<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\MembershipActivated;
use App\Services\PaystackPaymentService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class PaystackPaymentController extends Controller
{
    public function initialize(Request $request, PaystackPaymentService $payments): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->hasActiveMembership(), 403, 'Your membership is already active.');

        try {
            $checkoutUrl = $payments->initializeMembershipPayment(
                $user,
                (int) Setting::value('membership_fee', '15000'),
                (string) Setting::value('membership_currency', 'NGN'),
                route('membership.payment.callback'),
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return redirect()->away($checkoutUrl);
    }

    public function callback(Request $request, PaystackPaymentService $payments): RedirectResponse
    {
        $reference = $request->query('reference');

        if (! is_string($reference) || $reference === '') {
            return redirect()->route('membership.show')->withErrors(['payment' => 'Payment reference was not returned.']);
        }

        try {
            $transaction = $payments->verify($reference);
            $this->confirmPayment($transaction);
        } catch (RuntimeException $exception) {
            return redirect()->route('membership.show')->withErrors(['payment' => $exception->getMessage()]);
        }

        if ($transaction->purpose === 'event') {
            $event = Event::find($transaction->metadata['event_id'] ?? null);

            return $event
                ? redirect()->route('events.show', $event->slug)->with('success', 'Payment confirmed. Your event place is paid.')
                : redirect()->route('events')->with('success', 'Payment confirmed.');
        }

        return redirect()->route('membership.show')->with('success', 'Payment confirmed. Your membership is active.');
    }

    public function webhook(Request $request, PaystackPaymentService $payments): Response
    {
        abort_unless($payments->hasValidWebhookSignature(
            $request->getContent(),
            $request->header('paystack-signature'),
            $request->header('x-paystack-signature'),
        ), 401);

        if ($request->input('event') === 'charge.success') {
            $reference = $request->input('data.reference');

            if (is_string($reference)) {
                $transaction = PaymentTransaction::where('reference', $reference)->first();

                if ($transaction && ! ($transaction->metadata['payment_applied'] ?? false)) {
                    try {
                        $verifiedTransaction = $transaction->status === 'success'
                            ? $transaction
                            : $payments->verify($reference);
                        $this->confirmPayment($verifiedTransaction);
                    } catch (RuntimeException) {
                        abort(422);
                    }
                }
            }
        } elseif (str_starts_with((string) $request->input('event'), 'refund.')) {
            $event = $request->input('event');
            $data = $request->input('data', []);

            if (is_string($event) && is_array($data)) {
                $this->processRefundWebhook($event, $data);
            }
        }

        return response('OK', 200);
    }

    public function initializeEvent(Request $request, Event $event, PaystackPaymentService $payments): RedirectResponse
    {
        abort_unless($event->status === 'published' && $event->requiresPayment(), 404);
        abort_if(! $request->user()->hasActiveMembership(), 403);

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->where('status', EventRegistration::STATUS_REGISTERED)
            ->firstOrFail();

        abort_if($registration->isPaid(), 403, 'Your event payment is already confirmed.');

        try {
            $checkoutUrl = $payments->initializeEventPayment(
                $request->user(),
                $event,
                $registration,
                route('membership.payment.callback'),
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return redirect()->away($checkoutUrl);
    }

    private function confirmPayment(PaymentTransaction $transaction): void
    {
        if ($transaction->status !== 'success') {
            return;
        }

        DB::transaction(function () use ($transaction): void {
            $lockedTransaction = PaymentTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($lockedTransaction->metadata['payment_applied'] ?? false) {
                return;
            }

            if ($lockedTransaction->purpose === 'membership') {
                $member = User::whereKey($lockedTransaction->user_id)->lockForUpdate()->firstOrFail();
                $member->update([
                    'membership_status' => 'active',
                    'membership_paid_at' => $lockedTransaction->paid_at ?? now(),
                    'membership_reference' => $lockedTransaction->reference,
                ]);
                $member->notify((new MembershipActivated($member))->afterCommit());
            } elseif ($lockedTransaction->purpose === 'event') {
                $registrationId = $lockedTransaction->metadata['event_registration_id'] ?? null;
                $registration = EventRegistration::whereKey($registrationId)
                    ->where('user_id', $lockedTransaction->user_id)
                    ->lockForUpdate()
                    ->first();

                if (! $registration) {
                    return;
                }

                $registration->update([
                    'payment_status' => EventRegistration::PAYMENT_PAID,
                    'payment_reference' => $lockedTransaction->reference,
                    'paid_at' => $lockedTransaction->paid_at ?? now(),
                ]);
            } else {
                return;
            }

            $lockedTransaction->update([
                'metadata' => array_merge($lockedTransaction->metadata ?? [], ['payment_applied' => true]),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function processRefundWebhook(string $event, array $data): void
    {
        $reference = $data['transaction_reference'] ?? null;

        if (! is_string($reference)) {
            return;
        }

        $transaction = PaymentTransaction::where('reference', $reference)
            ->whereNotNull('refund_reference')
            ->first();

        if (! $transaction) {
            return;
        }

        $refundStatus = match ($event) {
            'refund.pending' => 'pending',
            'refund.processing' => 'processing',
            'refund.needs-attention' => 'needs_attention',
            'refund.processed' => 'processed',
            'refund.failed' => 'failed',
            default => null,
        };

        if ($refundStatus === null) {
            return;
        }

        if ($refundStatus === 'processed') {
            DB::transaction(function () use ($transaction, $refundStatus): void {
                $lockedTransaction = PaymentTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

                if ($lockedTransaction->status === 'refunded') {
                    return;
                }

                $lockedTransaction->update([
                    'status' => 'refunded',
                    'refunded_at' => now(),
                    'metadata' => array_merge($lockedTransaction->metadata ?? [], ['refund_status' => $refundStatus]),
                ]);

                if ($lockedTransaction->purpose === 'membership') {
                    $lockedTransaction->user()->update([
                        'membership_status' => 'unpaid',
                        'membership_paid_at' => null,
                        'membership_reference' => null,
                    ]);
                } elseif ($lockedTransaction->purpose === 'event') {
                    $registrationId = $lockedTransaction->metadata['event_registration_id'] ?? null;
                    EventRegistration::whereKey($registrationId)
                        ->where('user_id', $lockedTransaction->user_id)
                        ->update([
                            'payment_status' => EventRegistration::PAYMENT_REFUNDED,
                            'refunded_at' => now(),
                        ]);
                }

                AuditLogger::log('payment.refund_processed', $lockedTransaction, [
                    'purpose' => $lockedTransaction->purpose,
                    'reference' => $lockedTransaction->reference,
                    'refund_reference' => $lockedTransaction->refund_reference,
                ]);
            });

            return;
        }

        $transaction->update([
            'status' => $refundStatus === 'failed' ? 'success' : ($refundStatus === 'needs_attention' ? 'refund_needs_attention' : 'refund_pending'),
            'metadata' => array_merge($transaction->metadata ?? [], ['refund_status' => $refundStatus]),
            'refund_reference' => $refundStatus === 'failed' ? null : $transaction->refund_reference,
        ]);
    }
}
