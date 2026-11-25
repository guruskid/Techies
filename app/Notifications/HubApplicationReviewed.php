<?php

namespace App\Notifications;

use App\Models\Hub;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HubApplicationReviewed extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Hub $hub, public string $status)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $approved = $this->status === 'approved';

        $message = (new MailMessage)
            ->subject($approved ? 'Your hub has been approved' : 'Update on your hub application')
            ->greeting('Hello '.$notifiable->name.',');

        if ($approved) {
            $message->line('"'.$this->hub->name.'" is now live in the MWTT hub directory.')
                ->action('Open hub dashboard', route('hub.dashboard'))
                ->line('Keep your hub profile and team details up to date.');
        } else {
            $message->line('We could not approve "'.$this->hub->name.'" at this time.')
                ->line('You can review and resubmit your hub application from your dashboard.')
                ->action('Open hub dashboard', route('hub.dashboard'));
        }

        return $message;
    }
}
