<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BirthdayGreeting extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Happy birthday from Meet with the Techies')
            ->greeting('Happy birthday, '.$notifiable->name.'!')
            ->line('Everyone at Meet with the Techies is wishing you a wonderful year ahead.')
            ->line('Thank you for being part of the community. We are glad you are here.')
            ->action('Visit your passport', route('passport.show'))
            ->line('Have a great day.');
    }
}
