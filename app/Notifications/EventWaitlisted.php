<?php

namespace App\Notifications;

use App\Models\Event;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventWaitlisted extends Notification implements ShouldQueue
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
            ->subject('You are on the waitlist: '.$this->event->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('"'.$this->event->title.'" is currently full, so we added you to the waitlist.')
            ->line('If a spot opens up we will email you straight away. You do not need to do anything else.')
            ->action('View event', route('events.show', $this->event->slug));
    }
}
