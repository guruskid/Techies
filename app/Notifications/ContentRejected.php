<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentRejected extends Notification
{
    public function __construct(public Article $article) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
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
