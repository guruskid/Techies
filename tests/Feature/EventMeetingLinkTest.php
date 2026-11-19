<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventMeetingLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_do_not_see_the_meeting_link(): void
    {
        $event = $this->event();

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertDontSee('https://meet.example.com/private-room')
            ->assertSee('The meeting link is shared with confirmed attendees.');
    }

    public function test_unregistered_members_do_not_see_the_meeting_link(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($member)
            ->get(route('events.show', $this->event()->slug))
            ->assertOk()
            ->assertDontSee('https://meet.example.com/private-room');
    }

    public function test_registered_members_see_the_meeting_link(): void
    {
        $event = $this->event();
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        $this->actingAs($member)
            ->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('https://meet.example.com/private-room');
    }

    public function test_waitlisted_members_do_not_see_the_meeting_link(): void
    {
        $event = $this->event();
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => EventRegistration::STATUS_WAITLISTED,
        ]);

        $this->actingAs($member)
            ->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertDontSee('https://meet.example.com/private-room');
    }

    public function test_organizers_and_staff_see_the_meeting_link(): void
    {
        $event = $this->event();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('https://meet.example.com/private-room');
    }

    public function test_the_calendar_redacts_the_link_for_guests_and_includes_it_for_attendees(): void
    {
        $event = $this->event();

        $guestBody = $this->get(route('events.calendar', $event->slug))->getContent();
        $this->assertStringNotContainsString('https://meet.example.com/private-room', $guestBody);
        $this->assertStringContainsString('LOCATION:Online', $guestBody);

        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        $attendeeBody = $this->actingAs($member)
            ->get(route('events.calendar', $event->slug))
            ->getContent();

        $this->assertStringContainsString('https://meet.example.com/private-room', $attendeeBody);
    }

    private function event(): Event
    {
        return Event::create([
            'title' => 'Private Remote Workshop',
            'slug' => 'private-remote-workshop',
            'start_date' => today()->addWeek()->toDateString(),
            'start_time' => '10:00',
            'is_online' => true,
            'meeting_url' => 'https://meet.example.com/private-room',
            'status' => 'published',
            'registration_type' => 'free',
        ]);
    }
}
