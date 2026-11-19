<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use App\Services\Mail\MailboxSynchronizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InboundMailController extends Controller
{
    /**
     * Receive a forwarded email from a provider webhook (Mailgun, Postmark,
     * SendGrid) or a generic JSON/form payload.
     */
    public function store(Request $request, string $token, MailboxSynchronizer $synchronizer): JsonResponse
    {
        $account = EmailAccount::where('inbound_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $this->signatureIsValid($request, $account)) {
            return response()->json([
                'status' => 'error',
                'message' => 'A valid webhook signature is required.',
            ], 403);
        }

        $payload = $request->json()->all() ?: $request->all();

        $from = $this->value($payload, ['From', 'from', 'from_email', 'sender', 'fromEmail']);
        $fromName = $this->value($payload, ['from_name', 'FromName', 'sender_name']);

        if (is_array($from)) {
            $fromName = $from['name'] ?? $fromName;
            $from = $from['email'] ?? null;
        }

        if (is_array($payload['FromFull'] ?? null)) {
            $fromName = $payload['FromFull']['Name'] ?? $fromName;
            $from = $payload['FromFull']['Email'] ?? $from;
        }

        [$fromEmail, $parsedName] = $this->parseAddress($from);
        $fromName = $fromName ?: $parsedName;

        $to = $this->value($payload, ['to_email', 'to', 'recipient', 'To']);

        if (is_array($to)) {
            $to = is_array($to[0] ?? null) ? ($to[0]['email'] ?? null) : ($to[0] ?? null);
        }

        [$toEmail] = $this->parseAddress($to);

        $subject = $this->value($payload, ['subject', 'Subject']) ?: '(no subject)';
        $text = $this->value($payload, ['body_text', 'body-plain', 'text', 'TextBody', 'body']);
        $html = $this->value($payload, ['body_html', 'body-html', 'html', 'HtmlBody']);
        $messageId = $this->value($payload, ['message_id', 'Message-Id', 'MessageID', 'messageId']);
        $inReplyTo = $this->value($payload, ['in_reply_to', 'In-Reply-To', 'InReplyTo']);
        $receivedAt = $this->value($payload, ['received_at', 'Date', 'date']);
        $attachments = $this->attachmentsFrom($payload);

        if (blank($fromEmail) || ! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 'error', 'message' => 'A valid sender email is required.'], 422);
        }

        $stored = $synchronizer->store($account, [
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'to_email' => $toEmail ?: $account->email,
            'to_name' => null,
            'subject' => $subject,
            'body_text' => $text,
            'body_html' => $html,
            'message_id' => filled($messageId) ? trim((string) $messageId, " \t<>") : null,
            'in_reply_to' => filled($inReplyTo) ? trim((string) $inReplyTo, " \t<>") : null,
            'date' => $receivedAt ? Carbon::parse($receivedAt) : now(),
            'attachments' => $attachments,
        ]);

        return response()->json([
            'status' => 'ok',
            'stored' => $stored,
            'account' => $account->email,
        ]);
    }

    /**
     * Normalise attachments from provider webhooks (Postmark style base64
     * payloads and generic `attachments` arrays).
     *
     * @param  array<string, mixed>  $payload
     * @return array<int, array{filename: string, mime_type: string|null, content: string}>
     */
    private function attachmentsFrom(array $payload): array
    {
        $items = $payload['attachments'] ?? $payload['Attachments'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        $attachments = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $filename = $item['filename'] ?? $item['name'] ?? $item['Name'] ?? null;
            $mimeType = $item['content_type'] ?? $item['ContentType'] ?? $item['type'] ?? null;
            $content = $item['content'] ?? $item['Content'] ?? $item['content_base64'] ?? $item['data'] ?? null;

            if (blank($filename) || blank($content)) {
                continue;
            }

            $decoded = base64_decode((string) $content, true);
            $attachments[] = [
                'filename' => (string) $filename,
                'mime_type' => $mimeType ? (string) $mimeType : null,
                'content' => $decoded === false ? (string) $content : $decoded,
            ];
        }

        return $attachments;
    }

    /**
     * Verify an HMAC-SHA256 signature over the raw request body. Signature
     * checking is optional unless the account requires it, which keeps simple
     * forwarding rules working while allowing providers to sign payloads.
     */
    private function signatureIsValid(Request $request, EmailAccount $account): bool
    {
        $signature = $request->header('X-Techies-Signature')
            ?: $request->header('X-Webhook-Signature')
            ?: $request->header('X-Hub-Signature-256');

        if (blank($signature)) {
            return ! $account->requires_signature;
        }

        if (blank($account->inbound_secret)) {
            return false;
        }

        $provided = preg_replace('/^sha256=/i', '', trim((string) $signature));
        $expected = hash_hmac('sha256', $request->getContent(), (string) $account->inbound_secret);

        return is_string($provided) && hash_equals($expected, $provided);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function value(array $payload, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (isset($payload[$key]) && $payload[$key] !== '') {
                return $payload[$key];
            }
        }

        return null;
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function parseAddress(mixed $value): array
    {
        if (! is_string($value) || blank($value)) {
            return [null, null];
        }

        if (preg_match('/^(.*)<([^>]+)>$/', $value, $matches)) {
            return [trim($matches[2]), trim($matches[1], " \t\"'") ?: null];
        }

        return [trim($value), null];
    }
}
