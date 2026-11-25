<?php

namespace App\Notifications;

use App\Models\Hub;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HubApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Hub $hub)
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
            ->subject('New hub application: '.$this->hub->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->hub->owner?->name.' submitted a new hub for review.')
            ->line($this->hub->name.' · '.collect([$this->hub->city, $this->hub->state, $this->hub->country])->filter()->implode(', '))
            ->action('Review the hub application', route('admin.hubs.show', $this->hub))
            ->line('Approve or reject it from the hubs management screen.');
    }
}
