<?php

namespace App\Services\Mail;

use App\Models\Chapter;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\NewsletterSubscriber;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Collection;

class BroadcastAudience
{
    /**
     * Snapshot the recipients for a broadcast audience.
     *
     * @return Collection<int, array{user_id: int|null, email: string, name: string|null}>
     */
    public function recipients(string $audience, ?int $audienceId = null): Collection
    {
        $recipients = match ($audience) {
            'all_members' => User::where('status', 'active')
                ->get()
                ->map(fn (User $user) => $this->fromUser($user)),
            'chapter' => $this->chapterRecipients($audienceId),
            'event' => $this->eventRecipients($audienceId),
            'newsletter' => NewsletterSubscriber::all()
                ->map(fn (NewsletterSubscriber $subscriber) => [
                    'user_id' => null,
                    'email' => $subscriber->email,
                    'name' => $subscriber->name,
                ]),
            'hub_owners' => User::where('status', 'active')
                ->whereHas('hub')
                ->get()
                ->map(fn (User $user) => $this->fromUser($user)),
            'opportunity_applicants' => $this->applicantRecipients($audienceId),
            default => collect(),
        };

        return $recipients
            ->filter(fn (array $recipient) => filter_var($recipient['email'], FILTER_VALIDATE_EMAIL))
            ->keyBy(fn (array $recipient) => strtolower($recipient['email']))
            ->values();
    }

    private function chapterRecipients(?int $chapterId): Collection
    {
        $chapter = Chapter::findOrFail($chapterId);

        return $chapter->members()
            ->where('users.status', 'active')
            ->get()
            ->map(fn (User $user) => $this->fromUser($user));
    }

    private function eventRecipients(?int $eventId): Collection
    {
        $event = Event::findOrFail($eventId);

        return $event->registrations()
            ->where('status', EventRegistration::STATUS_REGISTERED)
            ->with('user')
            ->get()
            ->filter(fn (EventRegistration $registration) => $registration->user !== null)
            ->map(fn (EventRegistration $registration) => $this->fromUser($registration->user));
    }

    private function applicantRecipients(?int $opportunityId): Collection
    {
        $opportunity = Opportunity::findOrFail($opportunityId);

        return $opportunity->applications()
            ->with('user')
            ->get()
            ->filter(fn ($application) => $application->user !== null)
            ->map(fn ($application) => $this->fromUser($application->user));
    }

    /**
     * @return array{user_id: int|null, email: string, name: string|null}
     */
    private function fromUser(User $user): array
    {
        return [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ];
    }
}
