<?php

namespace Tests\Feature;

use App\Models\Cohort;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Grant;
use App\Models\IntroRequest;
use App\Models\LoungePost;
use App\Models\MemberBenefit;
use App\Models\MemberSpotlight;
use App\Models\Opportunity;
use App\Models\ReferralOffer;
use App\Models\ShowcaseProject;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\GrantApplicationUpdated;
use App\Notifications\IntroductionUpdated;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommunityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_members_directory_filters_by_availability(): void
    {
        $open = $this->member('open@example.com', ['open_to_work' => true]);
        $closed = $this->member('closed@example.com');

        $this->get(route('members', ['open_to' => 'open_to_work']))
            ->assertOk()
            ->assertSee($open->name)
            ->assertDontSee($closed->name);
    }

    public function test_members_can_request_an_introduction_and_admins_can_complete_it(): void
    {
        Notification::fake();

        $member = $this->member();
        $target = $this->member('target@example.com');
        $admin = $this->admin();

        $this->actingAs($member)->post(route('introductions.store'), [
            'target_user_id' => $target->id,
            'purpose' => 'Advice on product design',
            'context' => 'I am moving from support into product design.',
        ])->assertRedirect();

        $intro = IntroRequest::firstOrFail();

        $this->actingAs($admin)->post(route('admin.introductions.update', $intro), [
            'status' => IntroRequest::STATUS_COMPLETED,
            'admin_notes' => 'Connected by email.',
        ])->assertRedirect();

        $this->assertSame(IntroRequest::STATUS_COMPLETED, $intro->fresh()->status);
        Notification::assertSentTo($member, IntroductionUpdated::class);
        Notification::assertSentTo($target, IntroductionUpdated::class);
    }

    public function test_members_can_join_and_leave_a_cohort(): void
    {
        $member = $this->member();
        $cohort = Cohort::create([
            'title' => 'Frontend Sprint',
            'slug' => 'frontend-sprint',
            'status' => Cohort::STATUS_OPEN,
            'created_by' => $this->admin()->id,
        ]);

        $this->actingAs($member)->post(route('cohorts.join', $cohort))->assertRedirect();
        $this->assertDatabaseHas('cohort_members', ['cohort_id' => $cohort->id, 'user_id' => $member->id]);

        $this->actingAs($member)->post(route('cohorts.leave', $cohort))->assertRedirect();
        $this->assertDatabaseMissing('cohort_members', ['cohort_id' => $cohort->id, 'user_id' => $member->id]);
    }

    public function test_members_can_submit_projects_and_admins_can_approve_them(): void
    {
        $member = $this->member();
        $admin = $this->admin();

        $this->actingAs($member)->post(route('projects.store'), [
            'title' => 'Transit Guide',
            'summary' => 'A guide to navigating the city.',
            'description' => 'Built with Laravel.',
            'stack_text' => 'Laravel, Tailwind',
        ])->assertRedirect(route('projects.index'));

        $project = ShowcaseProject::firstOrFail();
        $this->assertSame(ShowcaseProject::STATUS_PENDING, $project->status);

        $this->actingAs($admin)->post(route('admin.showcase.moderate', $project), [
            'decision' => ShowcaseProject::STATUS_APPROVED,
        ])->assertRedirect();

        $this->assertSame(ShowcaseProject::STATUS_APPROVED, $project->fresh()->status);
    }

    public function test_referral_offer_request_and_response_flow(): void
    {
        $referrer = $this->member('referrer@example.com');
        $requester = $this->member('requester@example.com');
        $opportunity = Opportunity::create([
            'title' => 'Frontend Engineer',
            'slug' => 'frontend-engineer',
            'status' => 'published',
            'submitted_by' => $this->admin()->id,
        ]);

        $this->actingAs($referrer)->post(route('referrals.offer', $opportunity), [
            'note' => 'I can refer strong candidates.',
        ])->assertRedirect();

        $offer = ReferralOffer::firstOrFail();

        $this->actingAs($requester)->post(route('referrals.request', $offer), [
            'message' => 'I would love a referral.',
        ])->assertRedirect();

        $request = $offer->requests()->firstOrFail();

        $this->actingAs($referrer)->post(route('referrals.respond', $request), [
            'status' => 'accepted',
        ])->assertRedirect();

        $this->assertSame('accepted', $request->fresh()->status);
    }

    public function test_members_can_post_and_reply_in_the_lounge(): void
    {
        $member = $this->member();

        $this->actingAs($member)->post(route('lounge.store'), [
            'title' => 'What are you building?',
            'category' => 'General',
            'body' => 'Share your current project.',
        ])->assertRedirect();

        $post = LoungePost::firstOrFail();

        $this->actingAs($member)->post(route('lounge.reply', $post), [
            'body' => 'I am building a community tool.',
        ])->assertRedirect();

        $this->assertDatabaseHas('lounge_replies', ['lounge_post_id' => $post->id, 'user_id' => $member->id]);
    }

    public function test_members_can_apply_for_grants_and_admins_can_review_them(): void
    {
        Notification::fake();

        $member = $this->member();
        $admin = $this->admin();
        $grant = Grant::create([
            'title' => 'Build Grant',
            'slug' => 'build-grant',
            'description' => 'A small grant.',
            'status' => Grant::STATUS_OPEN,
            'closes_at' => now()->addWeek(),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($member)->post(route('grants.apply', $grant), [
            'project_title' => 'Community tool',
            'summary' => 'A tool for study groups.',
        ])->assertRedirect();

        $application = $grant->applications()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.grants.review', [$grant, $application]), [
            'status' => 'approved',
            'review_notes' => 'Approved for this cycle.',
        ])->assertRedirect();

        $this->assertSame('approved', $application->fresh()->status);
        Notification::assertSentTo($member, GrantApplicationUpdated::class);
    }

    public function test_members_can_redeem_a_benefit(): void
    {
        $member = $this->member();
        $benefit = MemberBenefit::create([
            'title' => 'Hosting credit',
            'category' => 'Tools',
            'is_active' => true,
            'redemption_code' => 'MWTT-CODE',
        ]);

        $this->actingAs($member)->post(route('benefits.redeem', $benefit))->assertRedirect();

        $this->assertDatabaseHas('benefit_redemptions', [
            'member_benefit_id' => $benefit->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_event_check_in_requires_a_registration_and_sets_the_timestamp(): void
    {
        $member = $this->member();
        $event = Event::create([
            'title' => 'Check-in Event',
            'slug' => 'check-in-event',
            'start_date' => today()->toDateString(),
            'registration_type' => 'free',
            'status' => 'published',
            'check_in_token' => 'TESTTOKEN1234567890ABCD',
        ]);

        $this->actingAs($member)->get(route('events.check-in', [$event->slug, $event->check_in_token]))
            ->assertRedirect(route('events.show', $event->slug));

        $this->assertDatabaseMissing('event_registrations', ['event_id' => $event->id, 'user_id' => $member->id]);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        $this->actingAs($member)->get(route('events.check-in', [$event->slug, $event->check_in_token]))
            ->assertRedirect(route('events.show', $event->slug));

        $this->assertNotNull(EventRegistration::where('event_id', $event->id)->where('user_id', $member->id)->first()->checked_in_at);
    }

    public function test_member_spotlights_appear_on_the_homepage(): void
    {
        $member = $this->member();
        MemberSpotlight::create([
            'user_id' => $member->id,
            'month' => now()->format('Y-m'),
            'quote' => 'The community helped me grow.',
            'is_published' => true,
            'created_by' => $this->admin()->id,
        ]);

        $this->get(route('home'))->assertOk()->assertSee('The community helped me grow.');
    }

    /**
     * @param  array<string, mixed>  $flags
     */
    private function member(string $email = 'member@example.com', array $flags = []): User
    {
        $member = User::factory()->create([
            'email' => $email,
            'role' => 'member',
            'status' => 'active',
            'membership_status' => 'active',
            'membership_paid_at' => now()->subMonth(),
        ]);

        UserProfile::create(array_merge([
            'user_id' => $member->id,
            'username' => str()->slug(str()->before($email, '@')),
            'is_public' => true,
            'weekly_digest' => true,
        ], $flags));

        return $member->fresh('profile');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }
}
