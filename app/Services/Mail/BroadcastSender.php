<?php

namespace App\Services\Mail;

use App\Models\EmailAccount;
use App\Models\EmailBroadcast;
use App\Models\EmailBroadcastRecipient;
use App\Models\EmailMessage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BroadcastSender
{
    public function __construct(private readonly MailAccountMailer $mailer) {}

    /**
     * Send every pending recipient for a broadcast.
     */
    public function send(EmailBroadcast $broadcast): void
    {
        $account = $broadcast->account ?: EmailAccount::default();

        if (! $account || ! $account->canSend()) {
            throw new RuntimeException('Connect an email account with SMTP settings before sending this broadcast.');
        }

        $broadcast->update([
            'status' => EmailBroadcast::STATUS_SENDING,
            'email_account_id' => $account->id,
            'recipients_count' => $broadcast->recipients()->count(),
        ]);

        $sent = 0;
        $failed = 0;

        $broadcast->recipients()
            ->where('status', EmailBroadcastRecipient::STATUS_PENDING)
            ->chunkById(50, function ($recipients) use ($broadcast, $account, &$sent, &$failed): void {
                foreach ($recipients as $recipient) {
                    $replacements = [
                        'name' => (string) $recipient->name,
                        'first_name' => Str::before((string) $recipient->name, ' '),
                        'email' => $recipient->email,
                    ];

                    $message = new EmailMessage([
                        'email_account_id' => $account->id,
                        'email_broadcast_id' => $broadcast->id,
                        'direction' => EmailMessage::DIRECTION_OUTBOUND,
                        'status' => EmailMessage::STATUS_SENT,
                        'from_email' => $account->email,
                        'from_name' => $account->fromName(),
                        'to_email' => $recipient->email,
                        'to_name' => $recipient->name,
                        'subject' => $this->replace($broadcast->subject, $replacements),
                        'body_html' => $this->replace($broadcast->body, $replacements),
                        'sent_at' => now(),
                    ]);

                    try {
                        $this->mailer->deliver($account, $message);

                        $recipient->update([
                            'status' => EmailBroadcastRecipient::STATUS_SENT,
                            'sent_at' => now(),
                            'error' => null,
                        ]);
                        $sent++;
                    } catch (Throwable $exception) {
                        $recipient->update([
                            'status' => EmailBroadcastRecipient::STATUS_FAILED,
                            'error' => $exception->getMessage(),
                        ]);
                        $failed++;
                    }
                }
            });

        $broadcast->update([
            'status' => $failed > 0 && $sent === 0
                ? EmailBroadcast::STATUS_FAILED
                : EmailBroadcast::STATUS_SENT,
            'sent_count' => $sent,
            'failed_count' => $failed,
            'sent_at' => now(),
        ]);
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function replace(string $value, array $replacements): string
    {
        foreach ($replacements as $key => $replacement) {
            $value = str_replace(
                ['{{'.$key.'}}', '{{ '.$key.' }}', '{{'.$key.' }}', '{{ '.$key.'}}'],
                $replacement,
                $value,
            );
        }

        return str_replace(
            ['{{site_name}}', '{{ site_name }}'],
            (string) config('app.name'),
            $value,
        );
    }
}
