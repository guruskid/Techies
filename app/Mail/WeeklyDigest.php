<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyDigest extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\Event>  $events
     * @param  Collection<int, \App\Models\Article>  $articles
     * @param  Collection<int, \App\Models\Opportunity>  $opportunities
     */
    public function __construct(
        public User $member,
        public Collection $events,
        public Collection $articles,
        public Collection $opportunities,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your weekly MWTT digest');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.weekly-digest');
    }
}
