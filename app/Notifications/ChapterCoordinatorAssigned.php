<?php

namespace App\Notifications;

use App\Models\Chapter;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChapterCoordinatorAssigned extends Notification
{
    public function __construct(public Chapter $chapter, public User $assignedBy) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You are now a coordinator of '.$this->chapter->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have been assigned as a coordinator of the '.$this->chapter->name.' chapter.')
            ->line('You will receive emails when members join or leave the chapter.')
            ->action('View chapter', route('chapters.show', $this->chapter->slug))
            ->line('Assigned by '.$this->assignedBy->name.'.');
    }
}
