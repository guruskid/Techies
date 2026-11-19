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
        $message = (new MailMessage)
            ->subject(($this->event->requiresPayment() ? 'Registration received' : 'You are registered').': '.$this->event->title)
            ->greeting('Hello '.$notifiable->name.',');

        if ($this->event->requiresPayment()) {
            $message->line('We received your registration for '.$this->event->title.'.');
            $message->line('Your place is confirmed once payment is received. The fee is ₦'.number_format((float) $this->event->fee, 2).'.');

            if ($this->event->payment_url) {
                $message->action('Pay for your place', $this->event->payment_url);
            }
        } else {
            $message->line('Your spot is confirmed for '.$this->event->title.'.');
        }

        return $message
            ->line($this->event->start_date->format('l, F j, Y').($this->event->start_time ? ' at '.$this->event->start_time : ''))
            ->action('View event', route('events.show', $this->event->slug))
            ->line('Add it to your calendar so you do not miss it.');
    }
}
