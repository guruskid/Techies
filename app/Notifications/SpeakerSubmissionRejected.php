<?php

namespace App\Notifications;

use App\Models\SpeakerSubmission;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpeakerSubmissionRejected extends Notification
{
    public function __construct(public SpeakerSubmission $submission) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $call = $this->submission->speakerCall;

        $message = (new MailMessage)
            ->subject('Update on your speaker proposal: '.$this->submission->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Thank you for submitting a proposal for "'.$call->title.'".')
            ->line('After reviewing the submissions, the organiser is unable to include this talk in the programme.')
            ->line('Talk: '.$this->submission->title)
            ->line('Topic: '.$this->submission->topic)
            ->action('View the call', route('speaker-calls.show', $call->slug));

        if ($this->submission->review_notes) {
            $message->line('Notes from the organiser: '.$this->submission->review_notes);
        }

        return $message->line('We hope you will submit again for a future call.');
    }
}
