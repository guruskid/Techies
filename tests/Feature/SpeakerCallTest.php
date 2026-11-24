<?php

namespace Tests\Feature;

use App\Models\Hub;
use App\Models\SpeakerCall;
use App\Models\SpeakerSubmission;
use App\Models\User;
use App\Notifications\SpeakerSubmissionApproved;
use App\Notifications\SpeakerSubmissionRejected;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SpeakerCallTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_open_calls_are_visible_to_guests(): void
    {
        $call = $this->createCall(['title' => 'Lagos Frontend Call']);

        $this->get(route('speaker-calls.index'))
            ->assertOk()
            ->assertSee('Lagos Frontend Call');

        $this->get(route('speaker-calls.show', $call->slug))
            ->assertOk()
            ->assertSee('Lagos Frontend Call')
            ->assertSee('Frontend performance');
    }

    public function test_draft_calls_are_hidden_from_the_public(): void
    {
        $call = $this->createCall(['title' => 'Hidden Draft Call', 'status' => SpeakerCall::STATUS_DRAFT]);

        $this->get(route('speaker-calls.index'))
            ->assertOk()
            ->assertDontSee('Hidden Draft Call');

        $this->get(route('speaker-calls.show', $call->slug))->assertNotFound();
    }

    public function test_members_can_submit_a_topic_from_the_call(): void
    {
        $member = $this->member();
        $call = $this->createCall();

        $this->actingAs($member)->post(route('speaker-calls.submit', $call->slug), [
            'topic' => 'Frontend performance',
            'title' => 'Fast interfaces on slow networks',
            'abstract' => 'A practical talk about measuring and improving frontend performance.',
            'bio' => 'Frontend engineer and community mentor.',
        ])->assertRedirect(route('speaker-calls.show', $call->slug));

        $this->assertDatabaseHas('speaker_submissions', [
            'speaker_call_id' => $call->id,
            'user_id' => $member->id,
            'topic' => 'Frontend performance',
            'status' => SpeakerSubmission::STATUS_PENDING,
        ]);
    }

    public function test_members_cannot_submit_a_topic_the_call_did_not_publish(): void
    {
        $member = $this->member();
        $call = $this->createCall();

        $this->actingAs($member)->post(route('speaker-calls.submit', $call->slug), [
            'topic' => 'A topic that was not offered',
            'title' => 'Some talk',
            'abstract' => 'An abstract that should not be accepted.',
        ])->assertSessionHasErrors('topic');

        $this->assertDatabaseCount('speaker_submissions', 0);
    }

    public function test_members_cannot_submit_twice_for_the_same_call(): void
    {
        $member = $this->member();
        $call = $this->createCall();

        SpeakerSubmission::create([
            'speaker_call_id' => $call->id,
            'user_id' => $member->id,
            'topic' => 'Frontend performance',
            'title' => 'First proposal',
            'abstract' => 'First abstract.',
            'status' => SpeakerSubmission::STATUS_PENDING,
        ]);

        $this->actingAs($member)->post(route('speaker-calls.submit', $call->slug), [
            'topic' => 'Design systems',
            'title' => 'Second proposal',
            'abstract' => 'Second abstract.',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('speaker_submissions', 1);
    }

    public function test_closed_calls_do_not_accept_submissions(): void
    {
        $member = $this->member();
        $call = $this->createCall([
            'status' => SpeakerCall::STATUS_CLOSED,
            'closes_at' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($member)->post(route('speaker-calls.submit', $call->slug), [
            'topic' => 'Frontend performance',
            'title' => 'Too late',
            'abstract' => 'This should be rejected.',
        ])->assertForbidden();
    }

    public function test_creator_can_approve_a_submission_and_the_speaker_is_emailed(): void
    {
        Notification::fake();

        [$owner, $hub] = $this->hubOwner();
        $member = $this->member();
        $call = $this->createCall(['hub_id' => $hub->id, 'created_by' => $owner->id]);
        $submission = $this->submission($call, $member);

        $this->actingAs($owner)->post(route('admin.speaker-calls.submissions.review', [$call, $submission]), [
            'status' => SpeakerSubmission::STATUS_APPROVED,
            'review_notes' => 'We would love to have you speak.',
        ])->assertRedirect();

        $this->assertSame(SpeakerSubmission::STATUS_APPROVED, $submission->fresh()->status);
        $this->assertSame($owner->id, $submission->fresh()->reviewed_by);

        Notification::assertSentTo($member, SpeakerSubmissionApproved::class);
    }

    public function test_creator_can_reject_a_submission_and_the_speaker_is_emailed(): void
    {
        Notification::fake();

        [$owner, $hub] = $this->hubOwner();
        $member = $this->member();
        $call = $this->createCall(['hub_id' => $hub->id, 'created_by' => $owner->id]);
        $submission = $this->submission($call, $member);

        $this->actingAs($owner)->post(route('admin.speaker-calls.submissions.review', [$call, $submission]), [
            'status' => SpeakerSubmission::STATUS_REJECTED,
            'review_notes' => 'The programme is full this time.',
        ])->assertRedirect();

        $this->assertSame(SpeakerSubmission::STATUS_REJECTED, $submission->fresh()->status);
        $this->assertSame('The programme is full this time.', $submission->fresh()->review_notes);

        Notification::assertSentTo($member, SpeakerSubmissionRejected::class);
    }

    public function test_unrelated_hub_owners_cannot_review_submissions(): void
    {
        [$owner] = $this->hubOwner();
        [$otherOwner, $otherHub] = $this->hubOwner('other-owner@example.com', 'Other Hub');
        $member = $this->member();
        $call = $this->createCall(['hub_id' => $otherHub->id, 'created_by' => $otherOwner->id]);
        $submission = $this->submission($call, $member);

        $this->actingAs($owner)->get(route('admin.speaker-calls.submissions', $call))->assertForbidden();

        $this->actingAs($owner)->post(route('admin.speaker-calls.submissions.review', [$call, $submission]), [
            'status' => SpeakerSubmission::STATUS_APPROVED,
        ])->assertForbidden();

        $this->assertSame(SpeakerSubmission::STATUS_PENDING, $submission->fresh()->status);
    }

    public function test_hub_owner_calls_are_scoped_to_their_hub(): void
    {
        [$owner, $hub] = $this->hubOwner();

        $this->actingAs($owner)->post(route('admin.speaker-calls.store'), [
            'title' => 'Call for hub speakers',
            'description' => '<p>Share your work with the hub.</p>',
            'topics_text' => "Frontend\nCareer growth",
            'status' => SpeakerCall::STATUS_OPEN,
            'opens_at' => now()->toDateString(),
            'closes_at' => now()->addWeek()->toDateString(),
        ])->assertRedirect(route('admin.speaker-calls.index'));

        $this->assertDatabaseHas('speaker_calls', [
            'title' => 'Call for hub speakers',
            'hub_id' => $hub->id,
            'created_by' => $owner->id,
            'status' => SpeakerCall::STATUS_OPEN,
        ]);
    }

    private function createCall(array $overrides = []): SpeakerCall
    {
        $creator = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        return SpeakerCall::create(array_merge([
            'title' => 'Call for speakers',
            'slug' => 'call-for-speakers-'.uniqid(),
            'description' => '<p>Share a practical talk with the community.</p>',
            'topics' => ['Frontend performance', 'Design systems', 'Career growth'],
            'created_by' => $creator->id,
            'opens_at' => now()->subDay()->toDateString(),
            'closes_at' => now()->addWeek()->toDateString(),
            'status' => SpeakerCall::STATUS_OPEN,
        ], $overrides));
    }

    private function member(): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @return array{0: User, 1: Hub}
     */
    private function hubOwner(string $email = 'hub-owner@example.com', string $hubName = 'Test Hub'): array
    {
        $owner = User::factory()->create([
            'email' => $email,
            'role' => 'member',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $owner->assignRole('hub_owner');

        $hub = Hub::create([
            'user_id' => $owner->id,
            'name' => $hubName,
            'slug' => str()->slug($hubName).'-'.uniqid(),
            'status' => 'approved',
            'is_verified' => true,
            'is_public' => true,
        ]);

        return [$owner, $hub];
    }

    private function submission(SpeakerCall $call, User $member): SpeakerSubmission
    {
        return SpeakerSubmission::create([
            'speaker_call_id' => $call->id,
            'user_id' => $member->id,
            'topic' => 'Frontend performance',
            'title' => 'A practical performance talk',
            'abstract' => 'What we measured and what we changed.',
            'bio' => 'Frontend engineer.',
            'status' => SpeakerSubmission::STATUS_PENDING,
        ]);
    }
}
