<?php

namespace App\Notifications;

use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyCommunityReport extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    /**
     * @param  array<string, int>  $stats
     */
    public function __construct(public array $stats, public ?string $spotlight = null)
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
            ->subject('Your monthly Meet with the Techies report')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Here is what happened in the community this month:')
            ->line('• '.$this->stats['events'].' events')
            ->line('• '.$this->stats['opportunities'].' opportunities')
            ->line('• '.$this->stats['articles'].' articles published')
            ->line('• '.$this->stats['members'].' active members');

        if ($this->spotlight) {
            $message->line('Member spotlight: '.$this->spotlight);
        }

        return $message
            ->action('Explore the community', route('home'))
            ->line('Thank you for being part of the network.');
    }
}
