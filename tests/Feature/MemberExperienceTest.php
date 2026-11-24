<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\MemberBenefit;
use App\Models\MentorshipRequest;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\CertificateIssued;
use App\Notifications\MembershipFeedbackRequest;
use App\Notifications\MentorshipMatched;
use App\Notifications\OpportunityMatches;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MemberExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_members_can_open_their_passport(): void
    {
        $member = $this->member();

        $this->actingAs($member)->get(route('passport.show'))
            ->assertOk()
            ->assertSee('Member passport')
            ->assertSee($member->member_number);
    }

    public function test_benefits_page_only_lists_active_benefits(): void
    {
        MemberBenefit::create(['title' => 'Active partner offer', 'category' => 'Perks', 'is_active' => true]);
        MemberBenefit::create(['title' => 'Hidden partner offer', 'category' => 'Perks', 'is_active' => false]);

        $this->get(route('benefits.index'))
            ->assertOk()
            ->assertSee('Active partner offer')
            ->assertDontSee('Hidden partner offer');
    }

    public function test_members_can_request_mentorship(): void
    {
        $member = $this->member();

        $this->actingAs($member)->post(route('mentorship.store'), [
            'role' => MentorshipRequest::ROLE_MENTEE,
            'expertise' => 'Frontend development',
            'goals' => 'Prepare for a first frontend role.',
            'availability' => 'Weekday evenings',
        ])->assertRedirect();

        $this->assertDatabaseHas('mentorship_requests', [
            'user_id' => $member->id,
            'role' => MentorshipRequest::ROLE_MENTEE,
            'status' => MentorshipRequest::STATUS_PENDING,
        ]);
    }

    public function test_admins_can_match_mentorship_and_both_members_are_emailed(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $mentor = $this->member('mentor@example.com');
        $mentee = $this->member('mentee@example.com');

        $mentorRequest = MentorshipRequest::create([
            'user_id' => $mentor->id,
            'role' => MentorshipRequest::ROLE_MENTOR,
            'status' => MentorshipRequest::STATUS_PENDING,
        ]);
        $menteeRequest = MentorshipRequest::create([
            'user_id' => $mentee->id,
            'role' => MentorshipRequest::ROLE_MENTEE,
            'status' => MentorshipRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.mentorship.match', $menteeRequest), [
            'mentor_request_id' => $mentorRequest->id,
        ])->assertRedirect();

        $this->assertSame(MentorshipRequest::STATUS_MATCHED, $menteeRequest->fresh()->status);
        $this->assertSame($mentor->id, $menteeRequest->fresh()->matched_with);

        Notification::assertSentTo($mentor, MentorshipMatched::class);
        Notification::assertSentTo($mentee, MentorshipMatched::class);
    }

    public function test_admins_can_issue_certificates_and_members_are_emailed(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $member = $this->member();

        $this->actingAs($admin)->post(route('admin.certificates.store'), [
            'user_id' => $member->id,
            'type' => 'speaker',
            'title' => 'Speaker at Lagos Frontend Meetup',
            'description' => 'A practical talk on frontend performance.',
            'issued_at' => now()->toDateString(),
        ])->assertRedirect(route('admin.certificates.index'));

        $certificate = Certificate::firstOrFail();

        $this->assertSame('speaker', $certificate->type);
        Notification::assertSentTo($member, CertificateIssued::class);

        $this->get(route('certificates.show', $certificate->certificate_number))
            ->assertOk()
            ->assertSee('Speaker at Lagos Frontend Meetup')
            ->assertSee($member->name);
    }

    public function test_registration_with_a_referral_code_links_the_referrer(): void
    {
        $referrer = $this->member('referrer@example.com');

        $this->post('/register', [
            'name' => 'Referred Member',
            'email' => 'referred@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'referral_code' => $referrer->referral_code,
        ])->assertRedirect('/email/verify');

        $this->assertDatabaseHas('users', [
            'email' => 'referred@example.com',
            'referred_by' => $referrer->id,
        ]);
    }

    public function test_members_can_submit_feedback(): void
    {
        $member = $this->member();

        $this->actingAs($member)->post(route('feedback.store'), [
            'rating' => 5,
            'comments' => 'Useful events and good people.',
        ])->assertRedirect();

        $this->assertDatabaseHas('member_feedback', [
            'user_id' => $member->id,
            'rating' => 5,
        ]);
    }

    public function test_feedback_command_emails_members_after_thirty_days(): void
    {
        Notification::fake();

        $member = $this->member();
        $member->forceFill(['membership_paid_at' => now()->subDays(31)])->save();

        $this->artisan('members:request-feedback')->assertSuccessful();

        Notification::assertSentTo($member, MembershipFeedbackRequest::class);
        $this->assertNotNull($member->fresh()->feedback_requested_at);
    }

    public function test_opportunity_match_command_emails_matching_members(): void
    {
        Notification::fake();

        $member = $this->member();
        $member->profile->update(['state' => 'Lagos', 'technology_category' => 'Frontend Development', 'weekly_digest' => true]);

        Opportunity::create([
            'title' => 'Frontend Engineer',
            'slug' => 'frontend-engineer-match',
            'description' => 'Build accessible interfaces.',
            'location' => 'Lagos, Nigeria',
            'status' => 'published',
            'deadline' => now()->addWeek()->toDateString(),
            'submitted_by' => $this->admin()->id,
        ]);

        $this->artisan('opportunities:send-matches')->assertSuccessful();

        Notification::assertSentTo($member, OpportunityMatches::class);
    }

    private function member(string $email = 'member@example.com'): User
    {
        $member = User::factory()->create([
            'email' => $email,
            'role' => 'member',
            'status' => 'active',
            'membership_status' => 'active',
            'membership_paid_at' => now()->subMonths(2),
        ]);

        UserProfile::create([
            'user_id' => $member->id,
            'username' => str()->slug(str()->before($email, '@')),
            'is_public' => true,
            'weekly_digest' => true,
        ]);

        return $member->fresh('profile');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }
}
