<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admins_cannot_access_the_super_admin_console(): void
    {
        $admin = $this->staff('admin');

        $this->actingAs($admin)->get(route('super-admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('super-admin.users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('super-admin.settings.edit'))->assertForbidden();
    }

    public function test_super_admin_can_access_every_console_screen(): void
    {
        $superAdmin = $this->staff('super_admin');

        $this->actingAs($superAdmin)->get(route('super-admin.dashboard'))->assertOk();
        $this->actingAs($superAdmin)->get(route('super-admin.users.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('super-admin.roles.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('super-admin.settings.edit'))->assertOk();
        $this->actingAs($superAdmin)->get(route('super-admin.audit-logs.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('super-admin.content.index'))->assertOk();
    }

    public function test_super_admin_can_change_another_users_role(): void
    {
        $superAdmin = $this->staff('super_admin');
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $member->assignRole('member');

        $this->actingAs($superAdmin)
            ->post(route('super-admin.users.role', $member), ['role' => 'events_manager'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($member->fresh()->hasRole('events_manager'));
        $this->assertDatabaseHas('users', ['id' => $member->id, 'role' => 'events_manager']);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'user.role_changed',
            'model_id' => $member->id,
        ]);
    }

    public function test_super_admin_cannot_change_their_own_role(): void
    {
        $superAdmin = $this->staff('super_admin');

        $this->actingAs($superAdmin)
            ->post(route('super-admin.users.role', $superAdmin), ['role' => 'member'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue($superAdmin->fresh()->isSuperAdmin());
    }

    public function test_super_admin_can_suspend_and_restore_a_member(): void
    {
        $superAdmin = $this->staff('super_admin');
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $member->assignRole('member');

        $this->actingAs($superAdmin)->post(route('super-admin.users.suspend', $member))->assertRedirect();
        $this->assertSame('suspended', $member->fresh()->status);

        $this->actingAs($superAdmin)->post(route('super-admin.users.restore', $member))->assertRedirect();
        $this->assertSame('active', $member->fresh()->status);
    }

    public function test_super_admin_can_update_organisation_settings(): void
    {
        $superAdmin = $this->staff('super_admin');

        $this->actingAs($superAdmin)
            ->post(route('super-admin.settings.update'), [
                'site_tagline' => 'Connecting builders across Africa.',
                'contact_email' => 'team@meettechies.ng',
                'support_phone' => '+234 800 000 0000',
                'default_meta_description' => 'A community for technology professionals and hubs.',
                'community_guidelines_url' => '/community-guidelines',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'key' => 'site_tagline',
            'value' => 'Connecting builders across Africa.',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'contact_email',
            'value' => 'team@meettechies.ng',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('contact'))
            ->assertOk()
            ->assertSee('team@meettechies.ng');
    }

    public function test_super_admin_can_force_publish_an_article(): void
    {
        $superAdmin = $this->staff('super_admin');
        $article = Article::create([
            'user_id' => User::factory()->create(['role' => 'member'])->id,
            'title' => 'Official partnership announcement',
            'topic' => 'partnerships',
            'body' => str_repeat('This partnership will expand community programmes. ', 15),
            'status' => Article::STATUS_DRAFT,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.content.force-publish', $article))
            ->assertRedirect()
            ->assertSessionHas('success');

        $article->refresh();
        $this->assertSame(Article::STATUS_APPROVED, $article->status);
        $this->assertNotNull($article->published_at);
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create(['role' => $role, 'status' => 'active']);
        $user->assignRole($role);

        return $user;
    }
}
