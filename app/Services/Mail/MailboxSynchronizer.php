<?php

namespace App\Services\Mail;

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class MailboxSynchronizer
{
    public function __construct(
        private readonly MimeParser $parser,
        private readonly MailAttachmentStore $attachments,
    ) {}

    /**
     * Fetch the most recent messages from an account's IMAP inbox.
     *
     * @return int Number of new messages stored.
     */
    public function sync(EmailAccount $account, int $limit = 30): int
    {
        if (! $account->canReceive()) {
            throw new \RuntimeException('This account has no IMAP connection configured.');
        }

        $client = new ImapClient(
            $account->imap_host,
            (int) $account->imap_port,
            (string) $account->imap_username,
            (string) $account->imap_password,
            $account->imap_encryption ?: 'ssl',
        );

        $stored = 0;

        try {
            $client->connect();
            $client->select('INBOX');

            $uids = $client->search('ALL');
            $uids = array_slice($uids, -max(1, $limit));

            foreach ($uids as $uid) {
                $raw = $client->fetchRaw($uid);

                if (blank($raw)) {
                    continue;
                }

                if ($this->store($account, $this->parser->parse($raw))) {
                    $stored++;
                }
            }

            $account->forceFill([
                'last_synced_at' => now(),
                'last_sync_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $account->forceFill([
                'last_synced_at' => now(),
                'last_sync_error' => $exception->getMessage(),
            ])->save();

            Log::warning('Mailbox sync failed', [
                'account' => $account->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            $client->close();
        }

        return $stored;
    }

    /**
     * Open and authenticate an IMAP session without fetching messages.
     */
    public function testConnection(EmailAccount $account): void
    {
        if (! $account->canReceive()) {
            throw new \RuntimeException('Add an IMAP host, username and password first.');
        }

        $client = new ImapClient(
            $account->imap_host,
            (int) $account->imap_port,
            (string) $account->imap_username,
            (string) $account->imap_password,
            $account->imap_encryption ?: 'ssl',
        );

        try {
            $client->connect();
            $client->select('INBOX');
        } finally {
            $client->close();
        }
    }

    /**
     * Store a parsed inbound message, skipping duplicates by Message-ID.
     *
     * @param  array<string, mixed>  $parsed
     */
    public function store(EmailAccount $account, array $parsed): bool
    {
        $messageId = $parsed['message_id'] ?? null;

        if ($messageId && EmailMessage::where('email_account_id', $account->id)
            ->where('message_id', $messageId)
            ->exists()) {
            return false;
        }

        $message = EmailMessage::create([
            'email_account_id' => $account->id,
            'direction' => EmailMessage::DIRECTION_INBOUND,
            'status' => EmailMessage::STATUS_RECEIVED,
            'from_email' => $parsed['from_email'] ?: 'unknown@unknown',
            'from_name' => $parsed['from_name'] ?? null,
            'to_email' => $parsed['to_email'] ?: $account->email,
            'to_name' => $parsed['to_name'] ?? null,
            'subject' => $parsed['subject'] ?: '(no subject)',
            'body_text' => $parsed['body_text'] ?? null,
            'body_html' => $parsed['body_html'] ?? null,
            'message_id' => $messageId,
            'in_reply_to' => $parsed['in_reply_to'] ?? null,
            'received_at' => $parsed['date'] ?? now(),
        ]);

        foreach ($parsed['attachments'] ?? [] as $attachment) {
            if (blank($attachment['content'] ?? null)) {
                continue;
            }

            $this->attachments->storeContent(
                $message,
                (string) ($attachment['filename'] ?? 'attachment'),
                $attachment['mime_type'] ?? null,
                (string) $attachment['content'],
            );
        }

        return true;
    }
}
