<?php

namespace App\Notifications;

use App\Models\IntroRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IntroductionUpdated extends Notification
{
    public function __construct(public IntroRequest $introRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $completed = $this->introRequest->status === IntroRequest::STATUS_COMPLETED;
        $message = (new MailMessage)
            ->subject($completed ? 'Your introduction is ready' : 'Update on your introduction request')
            ->greeting('Hello '.$notifiable->name.',');

        if ($completed) {
            $message->line('The team has made the introduction for: '.$this->introRequest->purpose.'.')
                ->line('You can now reach out directly and continue the conversation.');
        } else {
            $message->line('The team reviewed the introduction request for: '.$this->introRequest->purpose.'.')
                ->line('It could not be completed this time.');
        }

        if ($this->introRequest->admin_notes) {
            $message->line('Note: '.$this->introRequest->admin_notes);
        }

        return $message->action('View introductions', route('introductions.index'));
    }
}
