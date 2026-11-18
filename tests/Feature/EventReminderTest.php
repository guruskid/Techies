<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\EventReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EventReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminders_are_sent_to_attendees_of_events_happening_tomorrow(): void
    {
        Notification::fake();

        $attendee = User::factory()->create();
        $event = Event::create([
            'title' => 'Tomorrow Meetup',
            'slug' => 'tomorrow-meetup',
            'start_date' => today()->addDay()->toDateString(),
            'start_time' => '18:00',
            'status' => 'published',
            'registration_type' => 'free',
        ]);
        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertSentTo($attendee, EventReminder::class);
        $this->assertNotNull($registration->fresh()->reminded_at);
    }

    public function test_reminders_are_not_sent_twice_and_skip_far_future_events(): void
    {
        Notification::fake();

        $attendee = User::factory()->create();
        $event = Event::create([
            'title' => 'Tomorrow Meetup',
            'slug' => 'tomorrow-meetup',
            'start_date' => today()->addDay()->toDateString(),
            'status' => 'published',
            'registration_type' => 'free',
        ]);
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        $futureEvent = Event::create([
            'title' => 'Next Month Conference',
            'slug' => 'next-month-conference',
            'start_date' => today()->addMonth()->toDateString(),
            'status' => 'published',
            'registration_type' => 'free',
        ]);
        $futureRegistration = EventRegistration::create([
            'event_id' => $futureEvent->id,
            'user_id' => $attendee->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        $this->artisan('events:send-reminders')->assertSuccessful();
        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertSentTimes(EventReminder::class, 1);
        $this->assertNull($futureRegistration->fresh()->reminded_at);
    }

    public function test_waitlisted_members_do_not_receive_reminders(): void
    {
        Notification::fake();

        $attendee = User::factory()->create();
        $event = Event::create([
            'title' => 'Tomorrow Meetup',
            'slug' => 'tomorrow-meetup',
            'start_date' => today()->addDay()->toDateString(),
            'status' => 'published',
            'registration_type' => 'free',
        ]);
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'status' => EventRegistration::STATUS_WAITLISTED,
        ]);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
