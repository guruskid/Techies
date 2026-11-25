<?php

namespace App\Notifications;

use App\Models\Certificate;
use App\Notifications\Concerns\StoresNotificationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssued extends Notification implements ShouldQueue
{
    use Queueable;
    use StoresNotificationSummary;

    public function __construct(public Certificate $certificate)
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
            ->subject('Your certificate is ready: '.$this->certificate->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A certificate has been added to your member passport.')
            ->line($this->certificate->title)
            ->action('View certificate', route('certificates.show', $this->certificate->certificate_number))
            ->line('You can print it or share the verification link.');
    }
}
