<?php

namespace App\Notifications;

use App\Models\Opportunity;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Opportunity $opportunity)
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
            ->subject('Application received: '.$this->opportunity->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your application for "'.$this->opportunity->title.'" has been received.')
            ->line('We will keep you updated as the organization reviews applications.')
            ->action('View opportunity', route('opportunities.show', $this->opportunity->slug));
    }
}
