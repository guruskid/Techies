<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Event;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\User;
use App\Notifications\EventPublished;
use App\Notifications\OpportunityPublished;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HubEventOpportunityAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_hub_owners_cannot_reach_the_admin_area(): void
    {
        [$owner] = $this->hubOwner();

        $this->actingAs($owner)->get('/admin')->assertForbidden();
        $this->actingAs($owner)->get('/admin/manage')->assertForbidden();
    }

    public function test_hub_owners_can_open_the_event_and_opportunity_forms(): void
    {
        [$owner] = $this->hubOwner();

        $this->actingAs($owner)->get(route('admin.events.create'))->assertOk();
        $this->actingAs($owner)->get(route('admin.opportunities.create'))->assertOk();
    }

    public function test_hub_owners_do_not_see_the_publishing_status_controls(): void
    {
        [$owner] = $this->hubOwner();

        $this->actingAs($owner)->get(route('admin.events.create'))
            ->assertOk()
            ->assertDontSee('Publishing')
            ->assertDontSee('id="status"', false)
            ->assertSee('name="status"', false)
            ->assertSee('An admin reviews it and publishes it', false);

        $this->actingAs($owner)->get(route('admin.opportunities.create'))
            ->assertOk()
            ->assertDontSee('id="status"', false)
            ->assertSee('name="status"', false)
            ->assertSee('An admin reviews it and publishes it', false);
    }

    public function test_admins_still_see_the_publishing_status_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->get(route('admin.events.create'))
            ->assertOk()
            ->assertSee('Publishing')
            ->assertSee('id="status"', false);

        $this->actingAs($admin)->get(route('admin.opportunities.create'))
            ->assertOk()
            ->assertSee('id="status"', false);
    }

    public function test_hub_owner_events_are_saved_as_drafts_on_their_hub(): void
    {
        [$owner, $hub] = $this->hubOwner();

        $this->actingAs($owner)->post(route('admin.events.store'), [
            'title' => 'Ikeja Meetup',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'title' => 'Ikeja Meetup',
            'status' => 'draft',
            'hub_id' => $hub->id,
            'organizer_id' => $owner->id,
        ]);
    }

    public function test_hub_owner_opportunities_are_saved_as_drafts_on_their_hub(): void
    {
        [$owner, $hub] = $this->hubOwner();

        $this->actingAs($owner)->post(route('admin.opportunities.store'), [
            'title' => 'Junior Developer',
            'organization' => 'Ikeja Labs',
            'status' => 'published',
        ])->assertRedirect(route('admin.opportunities.index'));

        $this->assertDatabaseHas('opportunities', [
            'title' => 'Junior Developer',
            'status' => 'draft',
            'hub_id' => $hub->id,
            'submitted_by' => $owner->id,
        ]);
    }

    public function test_hub_owners_cannot_publish_their_own_draft_by_editing_it(): void
    {
        [$owner, $hub] = $this->hubOwner();

        $event = Event::create([
            'title' => 'Draft Meetup',
            'slug' => 'draft-meetup',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'draft',
            'hub_id' => $hub->id,
            'organizer_id' => $owner->id,
        ]);

        $this->actingAs($owner)->patch(route('admin.events.update', $event), [
            'title' => 'Draft Meetup',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertSame('draft', $event->fresh()->status);
    }

    public function test_hub_owners_cannot_manage_events_from_another_hub(): void
    {
        [$owner] = $this->hubOwner();
        [, $otherHub] = $this->hubOwner('other@example.com', 'Other Hub');
        $otherEvent = Event::create([
            'title' => 'Not Yours',
            'slug' => 'not-yours',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'draft',
            'hub_id' => $otherHub->id,
        ]);

        $this->actingAs($owner)->get(route('admin.events.edit', $otherEvent))->assertForbidden();
    }

    public function test_chapter_coordinators_can_create_drafts_and_cannot_reach_the_admin_area(): void
    {
        $coordinator = User::factory()->create(['role' => 'member', 'status' => 'active']);
        Chapter::create([
            'name' => 'Lagos Chapter',
            'slug' => 'lagos-chapter',
            'coordinator_id' => $coordinator->id,
        ]);

        $this->actingAs($coordinator)->get(route('admin.events.create'))->assertOk();
        $this->actingAs($coordinator)->get('/admin')->assertForbidden();

        $this->actingAs($coordinator)->post(route('admin.events.store'), [
            'title' => 'Chapter Meetup',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'title' => 'Chapter Meetup',
            'status' => 'draft',
            'organizer_id' => $coordinator->id,
        ]);
    }

    public function test_publishing_a_hub_event_emails_members_in_that_state_only(): void
    {
        Notification::fake();

        [$owner, $hub] = $this->hubOwner();
        $local = $this->memberInState('Lagos');
        $faraway = $this->memberInState('Kano');

        $event = Event::create([
            'title' => 'Lagos Tech Night',
            'slug' => 'lagos-tech-night',
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'draft',
            'hub_id' => $hub->id,
            'organizer_id' => $owner->id,
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->patch(route('admin.events.update', $event), [
            'title' => $event->title,
            'start_date' => $event->start_date->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
        ])->assertRedirect(route('admin.events.index'));

        Notification::assertSentTo($local, EventPublished::class);
        Notification::assertNotSentTo($faraway, EventPublished::class);
    }

    public function test_publishing_a_hub_opportunity_emails_members_in_that_state_only(): void
    {
        Notification::fake();

        [$owner, $hub] = $this->hubOwner();
        $local = $this->memberInState('Lagos');
        $faraway = $this->memberInState('Kano');

        $opportunity = Opportunity::create([
            'title' => 'Lagos Internship',
            'slug' => 'lagos-internship',
            'status' => 'draft',
            'hub_id' => $hub->id,
            'submitted_by' => $owner->id,
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->patch(route('admin.opportunities.update', $opportunity), [
            'title' => $opportunity->title,
            'status' => 'published',
        ])->assertRedirect(route('admin.opportunities.index'));

        Notification::assertSentTo($local, OpportunityPublished::class);
        Notification::assertNotSentTo($faraway, OpportunityPublished::class);
    }

    /**
     * @return array{0: User, 1: Hub}
     */
    private function hubOwner(string $email = 'owner@example.com', string $hubName = 'Lagos Tech Hub'): array
    {
        $owner = User::factory()->create([
            'email' => $email,
            'role' => 'member',
            'status' => 'active',
        ]);
        $owner->assignRole('hub_owner');

        $hub = Hub::create([
            'user_id' => $owner->id,
            'name' => $hubName,
            'slug' => str($hubName)->slug(),
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'status' => 'approved',
            'is_verified' => true,
            'is_public' => true,
        ]);

        return [$owner, $hub];
    }

    private function memberInState(string $state): User
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $profile = $member->ensureProfile();
        $profile->update(['state' => $state]);

        return $member;
    }
}
