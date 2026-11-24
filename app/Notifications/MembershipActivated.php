<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipActivated extends Notification
{
    public function __construct(public User $member) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Meet with the Techies')
            ->greeting('Welcome, '.$notifiable->name.'!')
            ->line('Your membership is active. Thank you for joining the community.')
            ->line('Here is the fastest way to get value from your membership:')
            ->line('1. Complete your profile so other members can find and support you: '.route('profile.edit'))
            ->line('2. Join your local chapter and introduce yourself: '.route('chapters'))
            ->line('3. Register for the next community event or workshop: '.route('events'))
            ->line('4. Browse opportunities and speaker calls that match your goals: '.route('opportunities'))
            ->action('Open your dashboard', route('dashboard'))
            ->line('If you need help at any point, reply to this email and the team will point you in the right direction.');
    }
}
