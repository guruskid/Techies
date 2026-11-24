<?php

namespace App\Notifications;

use App\Models\MentorshipRequest;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentorshipMatched extends Notification
{
    public function __construct(
        public MentorshipRequest $request,
        public User $counterpart,
        public string $role,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $theyAre = $this->role === MentorshipRequest::ROLE_MENTOR ? 'mentee' : 'mentor';

        return (new MailMessage)
            ->subject('Your mentorship match is ready')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have been matched with '.$this->counterpart->name.' as your '.$theyAre.'.')
            ->line('Take a few minutes to introduce yourself and agree on a first check-in.')
            ->action('View your mentorship', route('mentorship.index'))
            ->line('Matches run in three-month cycles. If you need help, reply to this email.');
    }
}
