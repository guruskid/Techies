<?php

namespace App\Notifications;

use App\Models\Opportunity;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Opportunity $opportunity, public string $status)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
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
