<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRegistered extends Notification
{
    public function __construct(public Event $event) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You are registered: '.$this->event->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your spot is confirmed for '.$this->event->title.'.')
            ->line($this->event->start_date->format('l, F j, Y').($this->event->start_time ? ' at '.$this->event->start_time : ''))
            ->action('View event', route('events.show', $this->event->slug))
            ->line('Add it to your calendar so you do not miss it.');
    }
}
