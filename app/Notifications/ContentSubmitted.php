<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentSubmitted extends Notification
{
    public function __construct(public Article $article) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Review requested: '.$this->article->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->article->author?->name.' submitted an article for review.')
            ->line('"'.$this->article->title.'" · '.$this->article->topicLabel())
            ->action('Open the review queue', route('admin.content.show', $this->article))
            ->line('This submission needs '.$this->article->approvalLabel().'.');
    }
}
