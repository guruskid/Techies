<?php

namespace App\Notifications;

use App\Models\Opportunity;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OpportunityPublished extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Opportunity $opportunity)
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
            ->subject('New opportunity: '.$this->opportunity->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new opportunity has been published on Meet with the Techies.')
            ->line($this->opportunity->title.' — '.$this->opportunity->organization)
            ->action('View opportunity', route('opportunities.show', $this->opportunity->slug))
            ->line('If you are a match, apply directly from the platform.');
    }
}
