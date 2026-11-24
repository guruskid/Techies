<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipPaymentSubmitted extends Notification
{
    public function __construct(public User $member) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
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
