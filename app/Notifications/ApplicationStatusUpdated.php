<?php

namespace App\Notifications;

use App\Models\Opportunity;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification
{
    public function __construct(public Opportunity $opportunity, public string $status) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = ucfirst($this->status);

        return (new MailMessage)
            ->subject('Application update: '.$this->opportunity->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('There is an update on your application for "'.$this->opportunity->title.'".')
            ->line('Your application status is now: '.$status.'.')
            ->action('View opportunity', route('opportunities.show', $this->opportunity->slug))
            ->line('Thank you for applying through Meet with the Techies.');
    }
}
