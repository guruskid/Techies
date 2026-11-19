<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Notifications\EventRegistered;
use App\Notifications\EventRegistrationPromoted;
use App\Notifications\EventWaitlisted;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function show($slug)
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->with('category', 'organizer', 'hub', 'chapter')->firstOrFail();

        $registration = auth()->check()
            ? $event->registrations()->where('user_id', auth()->id())->first()
            : null;

        $registeredCount = $event->registrations()->where('status', 'registered')->count();
        $spotsLeft = $event->max_attendees ? max(0, $event->max_attendees - $registeredCount) : null;
        $canViewMeetingLink = $event->canAccessMeetingLink(auth()->user());

        return view('pages.event-detail', compact('event', 'registration', 'registeredCount', 'spotsLeft', 'canViewMeetingLink'));
    }

    public function register(Event $event)
    {
        if ($event->status !== 'published') {
            abort(404);
        }

        if ($event->registration_deadline && $event->registration_deadline->isPast()) {
            return back()->with('error', 'Registration for this event has closed.');
        }

        $user = auth()->user();
        $existing = $event->registrations()->where('user_id', $user->id)->first();

        if ($existing?->status === EventRegistration::STATUS_REGISTERED) {
            return back()->with('status', 'You are already registered for this event.');
        }

        $registered = $event->registrations()->where('status', EventRegistration::STATUS_REGISTERED)->count();
        $isFull = $event->max_attendees !== null && $registered >= $event->max_attendees;

        if ($isFull) {
            if ($existing?->status === EventRegistration::STATUS_WAITLISTED) {
                return back()->with('status', 'You are already on the waitlist for '.$event->title.'.');
            }

            if ($existing) {
                $existing->update([
                    'status' => EventRegistration::STATUS_WAITLISTED,
                    'payment_status' => EventRegistration::PAYMENT_NOT_REQUIRED,
                    'waitlisted_at' => now(),
                ]);
            } else {
                EventRegistration::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'status' => EventRegistration::STATUS_WAITLISTED,
                    'payment_status' => EventRegistration::PAYMENT_NOT_REQUIRED,
                    'waitlisted_at' => now(),
                ]);
            }

            $user->notify(new EventWaitlisted($event));

            return back()->with('success', 'This event is full, so you are on the waitlist. We will email you if a spot opens.');
        }

        $registration = EventRegistration::firstOrCreate(
            ['event_id' => $event->id, 'user_id' => $user->id],
            [
                'status' => EventRegistration::STATUS_REGISTERED,
                'payment_status' => $event->requiresPayment()
                    ? EventRegistration::PAYMENT_UNPAID
                    : EventRegistration::PAYMENT_NOT_REQUIRED,
            ]
        );

        $reenlisted = false;
        if (! $registration->wasRecentlyCreated && $registration->status !== EventRegistration::STATUS_REGISTERED) {
            $paymentStatus = $event->requiresPayment()
                ? ($registration->isPaid() ? EventRegistration::PAYMENT_PAID : EventRegistration::PAYMENT_UNPAID)
                : EventRegistration::PAYMENT_NOT_REQUIRED;

            $registration->update([
                'status' => EventRegistration::STATUS_REGISTERED,
                'payment_status' => $paymentStatus,
                'waitlisted_at' => null,
            ]);
            $reenlisted = true;
        }

        if ($registration->wasRecentlyCreated || $reenlisted) {
            $user->notify(new EventRegistered($event));
        }

        return back()->with('success', 'You are registered for '.$event->title.'.');
    }

    public function cancel(Event $event)
    {
        $registration = $event->registrations()
            ->where('user_id', auth()->id())
            ->whereIn('status', [EventRegistration::STATUS_REGISTERED, EventRegistration::STATUS_WAITLISTED])
            ->first();

        if (! $registration) {
            return back()->with('error', 'You are not registered for this event.');
        }

        $wasRegistered = $registration->status === EventRegistration::STATUS_REGISTERED;
        $registration->update(['status' => EventRegistration::STATUS_CANCELLED]);

        if ($wasRegistered) {
            $next = $event->registrations()
                ->where('status', EventRegistration::STATUS_WAITLISTED)
                ->oldest()
                ->with('user')
                ->first();

            if ($next) {
                $next->update([
                    'status' => EventRegistration::STATUS_REGISTERED,
                    'payment_status' => $event->requiresPayment()
                        ? EventRegistration::PAYMENT_UNPAID
                        : EventRegistration::PAYMENT_NOT_REQUIRED,
                    'waitlisted_at' => null,
                ]);
                $next->user?->notify(new EventRegistrationPromoted($event));
            }
        }

        return back()->with('success', $wasRegistered
            ? 'Your registration has been cancelled.'
            : 'You have been removed from the waitlist.');
    }

    /**
     * Download the event as an iCalendar (.ics) file.
     */
    public function calendar(Event $event): Response
    {
        abort_unless($event->status === 'published', 404);

        $start = $this->eventMoment($event, true);
        $end = $this->eventMoment($event, false);

        if ($end->lessThanOrEqualTo($start)) {
            $end = $start->copy()->addHours(2);
        }

        $meetingUrl = $event->canAccessMeetingLink(auth()->user()) ? $event->meeting_url : null;

        $location = $event->is_online
            ? 'Online'.($meetingUrl ? ' - '.$meetingUrl : '')
            : collect([$event->venue, $event->city, $event->state, $event->country])->filter()->implode(', ');

        $description = collect([
            strip_tags((string) $event->description),
            $event->is_online && $meetingUrl ? 'Join online: '.$meetingUrl : null,
        ])->filter()->implode("\n\n");

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Meet with the Techies//Events//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:event-'.$event->id.'@'.(parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'meettechies.ng'),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start->format('Ymd\THis'),
            'DTEND:'.$end->format('Ymd\THis'),
            'SUMMARY:'.$this->icsText($event->title),
            'DESCRIPTION:'.$this->icsText(Str::limit($description, 1000)),
            'LOCATION:'.$this->icsText($location),
            'URL:'.route('events.show', $event->slug),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$event->slug.'.ics"',
        ]);
    }

    private function eventMoment(Event $event, bool $start): Carbon
    {
        $date = $start ? $event->start_date : ($event->end_date ?: $event->start_date);
        $time = $start ? $event->start_time : $event->end_time;
        $moment = $date->copy();

        if ($time && preg_match('/^(\d{1,2}):(\d{2})/', (string) $time, $matches)) {
            $moment->setTime((int) $matches[1], (int) $matches[2]);
        } else {
            $moment->setTime($start ? 9 : 11, 0);
        }

        return $moment;
    }

    private function icsText(string $value): string
    {
        $value = str_replace(['\\', "\r\n", "\n", "\r"], ['\\\\', '\\n', '\\n', '\\n'], $value);

        return str_replace([';', ','], ['\\;', '\\,'], $value);
    }
}
