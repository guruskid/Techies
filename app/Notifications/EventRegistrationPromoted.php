<?php

namespace App\Notifications;

use App\Models\Event;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRegistrationPromoted extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Event $event)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('A spot opened up: '.$this->event->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Good news — a spot opened up and your registration for "'.$this->event->title.'" is now confirmed.')
            ->line($this->event->start_date->format('l, F j, Y').($this->event->start_time ? ' at '.$this->event->start_time : ''))
            ->action('View event', route('events.show', $this->event->slug))
            ->line('Add it to your calendar so you do not miss it.');
    }
}
