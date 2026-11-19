<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\User;
use App\Notifications\ChapterJoined;
use App\Notifications\ChapterMemberJoined;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ChapterMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_join_a_chapter_and_everyone_is_emailed(): void
    {
        Notification::fake();

        $coordinator = $this->member();
        $chapter = $this->chapter($coordinator);
        $member = $this->member();

        $this->actingAs($member)
            ->post(route('chapters.join', $chapter))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('chapter_members', [
            'chapter_id' => $chapter->id,
            'user_id' => $member->id,
        ]);

        Notification::assertSentTo($member, ChapterJoined::class);
        Notification::assertSentTo($coordinator, ChapterMemberJoined::class);
    }

    public function test_joining_twice_does_not_create_a_duplicate_membership(): void
    {
        Notification::fake();

        $chapter = $this->chapter($this->member());
        $member = $this->member();

        $this->actingAs($member)->post(route('chapters.join', $chapter));

        $this->actingAs($member)
            ->post(route('chapters.join', $chapter))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(1, $chapter->members()->whereKey($member->id)->count());
        Notification::assertSentToTimes($member, ChapterJoined::class, 1);
    }

    public function test_a_member_can_leave_a_chapter(): void
    {
        $chapter = $this->chapter($this->member());
        $member = $this->member();
        $chapter->members()->attach($member->id);

        $this->actingAs($member)
            ->post(route('chapters.leave', $chapter))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('chapter_members', [
            'chapter_id' => $chapter->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_guests_cannot_join_a_chapter(): void
    {
        $chapter = $this->chapter($this->member());

        $this->post(route('chapters.join', $chapter))->assertRedirect(route('login'));

        $this->assertDatabaseMissing('chapter_members', ['chapter_id' => $chapter->id]);
    }

    public function test_the_chapter_page_shows_the_join_button_to_members(): void
    {
        $chapter = $this->chapter($this->member());
        $member = $this->member();

        $this->actingAs($member)
            ->get(route('chapters.show', $chapter->slug))
            ->assertOk()
            ->assertSee('Join this chapter');
    }

    private function member(): User
    {
        return User::factory()->create(['role' => 'member', 'status' => 'active']);
    }

    private function chapter(User $coordinator): Chapter
    {
        return Chapter::create([
            'name' => 'Lagos Builders',
            'slug' => 'lagos-builders',
            'status' => 'active',
            'coordinator_id' => $coordinator->id,
        ]);
    }
}
