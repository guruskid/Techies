<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Chapter;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admins_can_publish_an_announcement(): void
    {
        $admin = $this->admin();
        $chapter = Chapter::create(['name' => 'Lagos Builders', 'slug' => 'lagos-builders', 'status' => 'active']);

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                'title' => 'Meetups resume this month',
                'body' => 'Our Lagos chapter is back at the hub on Saturday.',
                'chapter_id' => $chapter->id,
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.announcements.index'))
            ->assertSessionHas('success');

        $announcement = Announcement::firstOrFail();

        $this->assertTrue($announcement->is_published);
        $this->assertNotNull($announcement->published_at);
        $this->assertSame($admin->id, $announcement->author_id);
        $this->assertSame($chapter->id, $announcement->chapter_id);

        $this->get(route('news'))
            ->assertOk()
            ->assertSee('Meetups resume this month');
    }

    public function test_admins_can_save_a_draft(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.announcements.store'), [
                'title' => 'Not ready yet',
                'body' => 'Still working on this.',
            ])
            ->assertRedirect();

        $announcement = Announcement::firstOrFail();

        $this->assertFalse($announcement->is_published);
        $this->assertNull($announcement->published_at);

        $this->get(route('news'))->assertOk()->assertDontSee('Not ready yet');
    }

    public function test_admins_can_edit_and_unpublish_an_announcement(): void
    {
        $admin = $this->admin();
        $announcement = Announcement::create([
            'title' => 'Original title',
            'body' => 'Original body',
            'author_id' => $admin->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.announcements.update', $announcement), [
                'title' => 'Updated title',
                'body' => 'Updated body',
            ])
            ->assertRedirect();

        $announcement->refresh();

        $this->assertSame('Updated title', $announcement->title);
        $this->assertFalse($announcement->is_published);
        $this->assertNull($announcement->published_at);
    }

    public function test_admins_can_delete_an_announcement(): void
    {
        $admin = $this->admin();
        $announcement = Announcement::create([
            'title' => 'Temporary',
            'body' => 'Remove me',
            'author_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.announcements.destroy', $announcement))
            ->assertRedirect(route('admin.announcements.index'));

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_members_cannot_manage_announcements(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($member)->get(route('admin.announcements.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.announcements.create'))->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }
}
