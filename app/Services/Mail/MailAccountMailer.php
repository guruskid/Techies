<?php

namespace App\Services\Mail;

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MailAccountMailer
{
    /**
     * Register the account's SMTP settings as a dynamic Laravel mailer.
     */
    public function configure(EmailAccount $account): string
    {
        $name = $account->mailerName();

        config(["mail.mailers.{$name}" => $account->mailerConfig()]);
        Mail::purge($name);

        return $name;
    }

    /**
     * Send a stored outbound message through its connected account.
     */
    public function send(EmailAccount $account, EmailMessage $message): void
    {
        $messageId = $this->deliver($account, $message);

        $message->forceFill([
            'status' => EmailMessage::STATUS_SENT,
            'sent_at' => now(),
            'error' => null,
            'message_id' => $messageId,
        ])->save();
    }

    /**
     * Deliver a message without persisting it, returning the Message-ID used.
     */
    public function deliver(EmailAccount $account, EmailMessage $message): string
    {
        if (! $account->canSend()) {
            throw new RuntimeException('This email account has no active SMTP connection.');
        }

        $mailer = $this->configure($account);
        $body = $this->renderBody($message, $account);
        $messageId = $this->messageId($account);
        $attachments = $message->attachments()->get();

        $mail = Mail::mailer($mailer);

        $mail->html($body, function ($mail) use ($account, $message, $messageId, $attachments): void {
            $mail->from($account->email, $account->fromName())
                ->subject((string) $message->subject);

            $this->applyRecipients($mail, $message);

            if ($account->reply_to) {
                $mail->replyTo($account->reply_to);
            }

            $headers = $mail->getSymfonyMessage()->getHeaders();
            $headers->addIdHeader('Message-ID', $messageId);

            if ($message->in_reply_to) {
                $headers->addTextHeader('In-Reply-To', $message->in_reply_to);
                $headers->addTextHeader('References', $message->in_reply_to);
            }

            foreach ($attachments as $attachment) {
                $path = Storage::disk($attachment->disk)->path($attachment->path);

                if (is_file($path)) {
                    $mail->attach($path, [
                        'as' => $attachment->filename,
                        'mime' => $attachment->mime_type,
                    ]);
                }
            }
        });

        return $messageId;
    }

    /**
     * Send a small test email so an account can verify its SMTP settings.
     */
    public function sendTest(EmailAccount $account, string $to): void
    {
        if (! $account->canSend()) {
            throw new RuntimeException('Add an SMTP host and email address before sending a test.');
        }

        $mailer = $this->configure($account);
        $html = view('emails.mail-layout', [
            'content' => '<p>This is a test email from <strong>'.e(config('app.name')).'</strong>.</p>'
                .'<p>If you can read this, the <strong>'.e($account->name).'</strong> mail account is connected and ready to send.</p>',
            'account' => $account,
        ])->render();

        Mail::mailer($mailer)->html($html, function ($mail) use ($account, $to): void {
            $mail->from($account->email, $account->fromName())
                ->to($to)
                ->subject('Test email from '.config('app.name'));
        });
    }

    /**
     * Wrap a message body in the branded email layout.
     */
    public function renderBody(EmailMessage $message, EmailAccount $account): string
    {
        $body = (string) ($message->body_html ?: $message->body_text);
        $content = preg_match('/<[a-z][^>]*>/i', $body) ? $body : nl2br(e($body));

        if (filled($account->signature)) {
            $content .= view('emails.partials.signature', ['signature' => $account->signature])->render();
        }

        return view('emails.mail-layout', [
            'content' => $content,
            'account' => $account,
        ])->render();
    }

    private function applyRecipients(Message $mail, EmailMessage $message): void
    {
        $this->addAddresses($mail, 'to', $message->to_email, $message->to_name);

        foreach ($this->splitAddresses($message->cc) as [$email, $name]) {
            $mail->cc($email, $name);
        }

        foreach ($this->splitAddresses($message->bcc) as [$email, $name]) {
            $mail->bcc($email, $name);
        }
    }

    private function addAddresses(Message $mail, string $type, string $value, ?string $name = null): void
    {
        $addresses = $this->splitAddresses($value);

        if ($addresses === []) {
            throw new RuntimeException('No recipient email address was supplied.');
        }

        foreach ($addresses as $index => [$email, $addressName]) {
            $mail->{$type}($email, $addressName ?: ($index === 0 ? $name : null));
        }
    }

    /**
     * @return array<int, array{0: string, 1: string|null}>
     */
    public function splitAddresses(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return collect(preg_split('/[,;]+/', $value))
            ->map(fn (string $part) => trim($part))
            ->filter()
            ->map(function (string $part): array {
                if (preg_match('/^(.*)<([^>]+)>$/', $part, $matches)) {
                    return [trim($matches[2]), trim($matches[1], " \t\"'") ?: null];
                }

                return [$part, null];
            })
            ->values()
            ->all();
    }

    private function messageId(EmailAccount $account): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        return Str::uuid()->toString().'@'.($account->smtp_host ?: $host);
    }
}
