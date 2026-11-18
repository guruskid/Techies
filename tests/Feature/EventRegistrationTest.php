<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_register_for_and_cancel_an_event(): void
    {
        $user = User::factory()->create();
        $event = Event::create([
            'title' => 'Lagos Developer Meetup',
            'slug' => 'lagos-developer-meetup',
            'start_date' => now()->addWeek()->toDateString(),
            'status' => 'published',
            'max_attendees' => 50,
            'registration_type' => 'free',
        ]);

        $this->actingAs($user)
            ->post(route('events.register', $event))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
        ]);

        $this->actingAs($user)
            ->post(route('events.cancel', $event))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_member_is_waitlisted_when_event_is_full(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $event = Event::create([
            'title' => 'Small Workshop',
            'slug' => 'small-workshop',
            'start_date' => now()->addWeek()->toDateString(),
            'status' => 'published',
            'max_attendees' => 1,
            'registration_type' => 'free',
        ]);

        $this->actingAs($first)->post(route('events.register', $event));

        $this->actingAs($second)
            ->post(route('events.register', $event))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $second->id,
            'status' => 'waitlisted',
        ]);
    }

    public function test_draft_events_are_not_publicly_visible(): void
    {
        Event::create([
            'title' => 'Unpublished Event',
            'slug' => 'unpublished-event',
            'start_date' => now()->addWeek()->toDateString(),
            'status' => 'draft',
        ]);

        $this->get('/events/unpublished-event')->assertNotFound();
    }
}
