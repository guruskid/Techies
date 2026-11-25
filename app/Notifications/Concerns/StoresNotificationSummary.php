<?php

namespace App\Notifications\Concerns;

use App\Models\Chapter;
use App\Models\Event;
use App\Models\Grant;
use App\Models\Hub;
use App\Models\IntroRequest;
use App\Models\MentorshipRequest;
use App\Models\Opportunity;
use App\Models\SpeakerCall;
use App\Models\UserProfile;
use Illuminate\Support\Str;

trait StoresNotificationSummary
{
    /**
     * @return array{title: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => Str::headline(class_basename($this)),
            'url' => $this->actionUrl(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function channelsFor(object $notifiable): array
    {
        $category = $this->preferenceCategory();
        $preferences = $notifiable->profile?->notification_preferences ?? [];

        return ($preferences[$category] ?? true) ? ['database', 'mail'] : ['database'];
    }

    private function preferenceCategory(): string
    {
        $type = class_basename($this);

        return match (true) {
            str_starts_with($type, 'Event') => 'events',
            in_array($type, ['ApplicationSubmitted', 'ApplicationStatusUpdated', 'GrantApplicationUpdated', 'SpeakerSubmissionApproved', 'SpeakerSubmissionRejected'], true) => 'applications',
            in_array($type, ['OpportunityPublished', 'OpportunityMatches'], true) => 'opportunities',
            in_array($type, ['MentorshipMatched', 'IntroductionUpdated'], true) => 'mentorship',
            str_starts_with($type, 'Membership') => 'membership',
            default => 'community',
        };
    }

    private function actionUrl(): string
    {
        foreach (get_object_vars($this) as $value) {
            if ($value instanceof Event) {
                return route('events.show', $value->slug, false);
            }

            if ($value instanceof Opportunity) {
                return route('opportunities.show', $value->slug, false);
            }

            if ($value instanceof Grant) {
                return route('grants.show', $value->slug, false);
            }

            if ($value instanceof SpeakerCall) {
                return route('speaker-calls.show', $value->slug, false);
            }

            if ($value instanceof Chapter) {
                return route('chapters.show', $value->slug, false);
            }

            if ($value instanceof Hub) {
                return class_basename($this) === 'HubApplicationSubmitted'
                    ? route('admin.hubs.show', $value->id, false)
                    : route('hub.dashboard', [], false);
            }

            if ($value instanceof MentorshipRequest) {
                return route('mentorship.index', [], false);
            }

            if ($value instanceof IntroRequest) {
                return route('introductions.index', [], false);
            }

            if ($value instanceof UserProfile) {
                return route('profile.edit', [], false);
            }
        }

        return route('dashboard', [], false);
    }
}
