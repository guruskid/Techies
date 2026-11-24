<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventPublished extends Notification
{
    public function __construct(public Event $event) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New event: '.$this->event->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new event has been published on Meet with the Techies.')
            ->line($this->event->title.' — '.$this->event->start_date->format('M j, Y'))
            ->action('View event', route('events.show', $this->event->slug))
            ->line('Register directly from the platform.');
    }
}
