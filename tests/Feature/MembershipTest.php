<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\SpeakerCall;
use App\Models\User;
use App\Notifications\MembershipActivated;
use App\Notifications\MembershipPaymentSubmitted;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_registration_creates_an_unpaid_membership(): void
    {
        $this->post('/register', [
            'name' => 'New Member',
            'email' => 'new-member@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect('/email/verify');

        $this->assertDatabaseHas('users', [
            'email' => 'new-member@example.com',
            'membership_status' => 'unpaid',
        ]);
    }

    public function test_unpaid_members_are_redirected_to_the_membership_page(): void
    {
        $member = $this->member('unpaid');
        $event = $this->event();

        $this->actingAs($member)
            ->post(route('events.register', $event))
            ->assertRedirect(route('membership.show'));

        $this->assertDatabaseCount('event_registrations', 0);
    }

    public function test_membership_page_shows_the_one_time_fee(): void
    {
        $this->get(route('membership.show'))
            ->assertOk()
            ->assertSee('15,000')
            ->assertSee('one time', false);
    }

    public function test_members_can_submit_a_payment_reference_and_admins_are_notified(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = $this->member('unpaid');

        $this->actingAs($member)->post(route('membership.payment.submit'), [
            'membership_reference' => 'TRX-12345',
        ])->assertRedirect(route('membership.show'));

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'membership_status' => 'pending',
            'membership_reference' => 'TRX-12345',
        ]);

        Notification::assertSentTo($admin, MembershipPaymentSubmitted::class);
    }

    public function test_admins_can_confirm_membership_and_the_member_is_emailed(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = $this->member('pending');

        $this->actingAs($admin)
            ->post(route('admin.members.membership.confirm', $member))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'membership_status' => 'active',
            'membership_confirmed_by' => $admin->id,
        ]);

        $this->assertNotNull($member->fresh()->membership_paid_at);

        Notification::assertSentTo($member, MembershipActivated::class);
    }

    public function test_admins_can_waive_the_membership_fee(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = $this->member('unpaid');

        $this->actingAs($admin)
            ->post(route('admin.members.membership.waive', $member))
            ->assertRedirect();

        $this->assertSame('exempt', $member->fresh()->membership_status);
        $this->assertTrue($member->fresh()->hasActiveMembership());
    }

    public function test_active_members_can_register_for_events(): void
    {
        $member = $this->member('active');
        $event = $this->event();

        $this->actingAs($member)
            ->post(route('events.register', $event))
            ->assertRedirect();

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'registered',
        ]);
    }

    public function test_unpaid_members_cannot_submit_speaker_proposals(): void
    {
        $member = $this->member('unpaid');
        $call = SpeakerCall::create([
            'title' => 'Membership gate call',
            'slug' => 'membership-gate-call',
            'topics' => ['Frontend'],
            'created_by' => User::factory()->create(['role' => 'admin', 'status' => 'active'])->id,
            'opens_at' => now()->subDay()->toDateString(),
            'closes_at' => now()->addWeek()->toDateString(),
            'status' => SpeakerCall::STATUS_OPEN,
        ]);

        $this->actingAs($member)->post(route('speaker-calls.submit', $call->slug), [
            'topic' => 'Frontend',
            'title' => 'A talk',
            'abstract' => 'An abstract.',
        ])->assertRedirect(route('membership.show'));

        $this->assertDatabaseCount('speaker_submissions', 0);
    }

    private function member(string $membershipStatus): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'email_verified_at' => now(),
            'membership_status' => $membershipStatus,
        ]);
    }

    private function event(): Event
    {
        return Event::create([
            'title' => 'Member Event',
            'slug' => 'member-event-'.uniqid(),
            'start_date' => today()->addWeek()->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
        ]);
    }
}
