<?php

namespace App\Notifications;

use App\Models\Event;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminder extends Notification implements ShouldQueue
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
        $message = (new MailMessage)
            ->subject('Reminder: '.$this->event->title.' is coming up')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('This is a reminder that "'.$this->event->title.'" starts soon.')
            ->line($this->event->start_date->format('l, F j, Y').($this->event->start_time ? ' at '.$this->event->start_time : ''))
            ->action('View event', route('events.show', $this->event->slug));

        if ($this->event->is_online && $this->event->meeting_url) {
            $message->line('Join online: '.$this->event->meeting_url);
        } elseif ($this->event->venue) {
            $message->line('Venue: '.collect([$this->event->venue, $this->event->city])->filter()->implode(', '));
        }

        return $message->line('See you there.');
    }
}
