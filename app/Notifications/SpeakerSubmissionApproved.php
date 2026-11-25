<?php

namespace App\Notifications;

use App\Models\SpeakerSubmission;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpeakerSubmissionApproved extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public SpeakerSubmission $submission)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $call = $this->submission->speakerCall;

        $message = (new MailMessage)
            ->subject('Your speaker proposal was approved: '.$this->submission->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your proposal for "'.$call->title.'" has been approved.')
            ->line('Talk: '.$this->submission->title)
            ->line('Topic: '.$this->submission->topic)
            ->action('View the call', route('speaker-calls.show', $call->slug));

        if ($this->submission->review_notes) {
            $message->line('Notes from the organiser: '.$this->submission->review_notes);
        }

        return $message->line('The organiser will contact you with the next steps. Congratulations!');
    }
}
