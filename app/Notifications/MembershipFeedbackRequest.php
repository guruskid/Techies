<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipFeedbackRequest extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('How is your membership going?')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have been a member for a month. We would love to know how it is going.')
            ->line('A two-minute answer helps us improve events, mentorship and opportunities for everyone.')
            ->action('Share feedback', route('feedback.show'))
            ->line('Thank you for helping shape the community.');
    }
}
