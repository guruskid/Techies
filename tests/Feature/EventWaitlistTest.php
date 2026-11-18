<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\EventRegistrationPromoted;
use App\Notifications\EventWaitlisted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EventWaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_full_event_waitlists_the_next_member_and_emails_them(): void
    {
        Notification::fake();

        $first = User::factory()->create();
        $second = User::factory()->create();
        $event = $this->fullEvent($first);

        $this->actingAs($second)
            ->post(route('events.register', $event))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $second->id,
            'status' => EventRegistration::STATUS_WAITLISTED,
        ]);

        Notification::assertSentTo($second, EventWaitlisted::class);

        $this->actingAs($second)
            ->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('You are on the waitlist');
    }

    public function test_cancelling_promotes_the_first_waitlisted_member_and_emails_them(): void
    {
        Notification::fake();

        $first = User::factory()->create();
        $second = User::factory()->create();
        $event = $this->fullEvent($first);

        $this->actingAs($second)->post(route('events.register', $event));

        $this->actingAs($first)
            ->post(route('events.cancel', $event))
            ->assertRedirect();

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $second->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        Notification::assertSentTo($second, EventRegistrationPromoted::class);
    }

    public function test_waitlisted_member_can_leave_the_waitlist(): void
    {
        Notification::fake();

        $first = User::factory()->create();
        $second = User::factory()->create();
        $event = $this->fullEvent($first);

        $this->actingAs($second)->post(route('events.register', $event));

        $this->actingAs($second)
            ->post(route('events.cancel', $event))
            ->assertRedirect()
            ->assertSessionHas('success', 'You have been removed from the waitlist.');

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $second->id,
            'status' => EventRegistration::STATUS_CANCELLED,
        ]);
    }

    private function fullEvent(User $attendee): Event
    {
        $event = Event::create([
            'title' => 'Small Workshop',
            'slug' => 'small-workshop',
            'start_date' => now()->addWeek()->toDateString(),
            'status' => 'published',
            'max_attendees' => 1,
            'registration_type' => 'free',
        ]);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        return $event;
    }
}
