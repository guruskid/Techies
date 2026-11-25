<?php

namespace App\Notifications;

use App\Models\Chapter;
use App\Models\User;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChapterMemberJoined extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Chapter $chapter, public User $member)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
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
