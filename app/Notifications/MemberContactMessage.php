<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberContactMessage extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(
        public ?User $senderUser = null,
        public ?string $senderName = null,
        public ?string $senderEmail = null,
        public string $subjectLine = '',
        public string $body = '',
    ) {
        $this->afterCommit();
    }

    public function senderName(): string
    {
        return $this->senderUser?->name ?? (string) $this->senderName;
    }

    public function senderEmail(): string
    {
        return $this->senderUser?->email ?? (string) $this->senderEmail;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->senderName();
        $email = $this->senderEmail();

        return (new MailMessage)
            ->subject('MWTT message: '.$this->subjectLine)
            ->replyTo($email, $name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($name.' sent you a message through Meet with the Techies.')
            ->line($this->body)
            ->line('Reply directly to this email to answer '.$name.'.');
    }
}
