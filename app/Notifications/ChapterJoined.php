<?php

namespace App\Notifications;

use App\Models\Chapter;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChapterJoined extends Notification
{
    public function __construct(public Chapter $chapter) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('You joined '.$this->chapter->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You are now a member of the '.$this->chapter->name.' chapter.')
            ->action('View chapter', route('chapters.show', $this->chapter->slug));

        if ($this->chapter->coordinator) {
            $message->line('Your chapter coordinator is '.$this->chapter->coordinator->name.'.');
        }

        return $message->line('Keep an eye on chapter events and announcements.');
    }
}
