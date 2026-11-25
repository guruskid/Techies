<?php

namespace App\Notifications;

use App\Models\Article;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentApproved extends Notification implements ShouldQueue
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
            ->subject('Your article has been published')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your article "'.$this->article->title.'" has been approved and published.')
            ->action('View article', route('articles.show', $this->article))
            ->line('Thank you for contributing to the MWTT community.');
    }
}
