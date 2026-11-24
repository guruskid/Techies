<?php

namespace App\Notifications;

use App\Models\Chapter;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChapterCoordinatorRemoved extends Notification
{
    public function __construct(public Chapter $chapter, public User $removedBy) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Coordinator role removed: '.$this->chapter->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your coordinator role for the '.$this->chapter->name.' chapter has been removed.')
            ->line('You will no longer receive chapter join and leave notifications.')
            ->line('Changed by '.$this->removedBy->name.'.');
    }
}
