<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventRegistrationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admins_can_see_the_attendee_list(): void
    {
        $admin = $this->admin();
        $event = $this->event();
        $attendee = User::factory()->create(['name' => 'Ada Attendee', 'email' => 'ada@example.com']);
        $this->register($event, $attendee);

        $this->actingAs($admin)
            ->get(route('admin.events.registrations', $event))
            ->assertOk()
            ->assertSee('Ada Attendee')
            ->assertSee('ada@example.com')
            ->assertSee('Registered');
    }

    public function test_admins_can_check_an_attendee_in_and_undo_it(): void
    {
        $admin = $this->admin();
        $event = $this->event();
        $attendee = User::factory()->create();
        $registration = $this->register($event, $attendee);

        $this->actingAs($admin)
            ->post(route('admin.events.registrations.check-in', [$event, $registration]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($registration->fresh()->checked_in_at);

        $this->actingAs($admin)
            ->post(route('admin.events.registrations.check-in', [$event, $registration]))
            ->assertRedirect();

        $this->assertNull($registration->fresh()->checked_in_at);
    }

    public function test_waitlisted_members_cannot_be_checked_in(): void
    {
        $admin = $this->admin();
        $event = $this->event();
        $waitlisted = User::factory()->create();
        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $waitlisted->id,
            'status' => EventRegistration::STATUS_WAITLISTED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.events.registrations.check-in', [$event, $registration]))
            ->assertStatus(422);

        $this->assertNull($registration->fresh()->checked_in_at);
    }

    public function test_members_cannot_manage_event_attendees(): void
    {
        $event = $this->event();
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($member)
            ->get(route('admin.events.registrations', $event))
            ->assertForbidden();
    }

    public function test_admins_can_download_the_attendee_csv(): void
    {
        $admin = $this->admin();
        $event = $this->event();
        $attendee = User::factory()->create(['name' => 'Ada Attendee', 'email' => 'ada@example.com']);
        $this->register($event, $attendee);

        $response = $this->actingAs($admin)->get(route('admin.events.registrations.export', $event));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('event-'.$event->slug.'-attendees.csv', (string) $response->headers->get('content-disposition'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Name,Email,Phone,Status', $csv);
        $this->assertStringContainsString('Ada Attendee', $csv);
        $this->assertStringContainsString('ada@example.com', $csv);
        $this->assertStringContainsString('registered', $csv);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }

    private function event(): Event
    {
        return Event::create([
            'title' => 'Community Meetup',
            'slug' => 'community-meetup',
            'start_date' => today()->addWeek()->toDateString(),
            'status' => 'published',
            'registration_type' => 'free',
        ]);
    }

    private function register(Event $event, User $user): EventRegistration
    {
        return EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);
    }
}
