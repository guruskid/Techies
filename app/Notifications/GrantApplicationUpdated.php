<?php

namespace App\Notifications;

use App\Models\Grant;
use App\Models\GrantApplication;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GrantApplicationUpdated extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Grant $grant, public GrantApplication $application)
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
            ->subject('Grant application update: '.$this->grant->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your application for '.$this->grant->title.' is now '.$this->application->status.'.')
            ->action('View grant', route('grants.show', $this->grant->slug))
            ->line($this->application->review_notes ?: 'The team will follow up with next steps.');
    }
}
