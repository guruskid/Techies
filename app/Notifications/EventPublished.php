<?php

namespace App\Notifications;

use App\Models\Event;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventPublished extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Event $event)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
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
