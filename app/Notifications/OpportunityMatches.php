<?php

namespace App\Notifications;

use App\Models\Opportunity;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class OpportunityMatches extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    /**
     * @param  Collection<int, Opportunity>  $opportunities
     */
    public function __construct(public Collection $opportunities)
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
            ->subject('Opportunities picked for you this week')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Here are opportunities that match your profile and interests:');

        foreach ($this->opportunities as $opportunity) {
            $message->line('• '.$opportunity->title.' — '.($opportunity->organization ?: 'Community opportunity').($opportunity->is_remote ? ' (Remote)' : ''));
        }

        return $message
            ->action('View opportunities', route('opportunities'))
            ->line('Update your profile skills and location to improve future matches.');
    }
}
