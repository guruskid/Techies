<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipPaymentSubmitted extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public User $member)
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
            ->subject('Membership payment submitted: '.$this->member->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->member->name.' has submitted a membership payment reference.')
            ->line('Email: '.$this->member->email)
            ->line('Reference: '.($this->member->membership_reference ?: 'Not provided'))
            ->action('Review the member', route('admin.members.show', $this->member))
            ->line('Confirm the payment from the member screen once it has been verified.');
    }
}
