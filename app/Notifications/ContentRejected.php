<?php

namespace App\Notifications;

use App\Models\Article;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentRejected extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Article $article)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Changes requested on your article')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A reviewer requested changes to your article "'.$this->article->title.'".')
            ->line($this->article->review_notes ?? '')
            ->action('Revise article', route('member.content.edit', $this->article))
            ->line('Please update and resubmit when ready.');
    }
}
