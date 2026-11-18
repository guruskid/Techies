<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberContactMessage extends Notification
{
    public function __construct(
        public User $sender,
        public string $subjectLine,
        public string $body,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('MWTT message: '.$this->subjectLine)
            ->replyTo($this->sender->email, $this->sender->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->sender->name.' sent you a message through Meet with the Techies.')
            ->line($this->body)
            ->line('Reply directly to this email to answer '.$this->sender->name.'.');
    }
}
