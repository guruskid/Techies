<?php

namespace App\Notifications;

use App\Models\Chapter;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChapterMemberJoined extends Notification
{
    public function __construct(public Chapter $chapter, public User $member) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New member in '.$this->chapter->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->member->name.' just joined the '.$this->chapter->name.' chapter.')
            ->line('As coordinator you can reach out and welcome them.')
            ->action('View chapter', route('chapters.show', $this->chapter->slug));
    }
}
