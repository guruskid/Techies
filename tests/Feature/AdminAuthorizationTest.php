<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_are_redirected_from_admin_area(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_members_cannot_access_admin_area(): void
    {
        $member = User::factory()->create();
        $member->assignRole('member');

        $this->actingAs($member)->get('/admin')->assertForbidden();
    }

    public function test_admins_can_access_admin_area(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/members')->assertOk();
    }

    public function test_admins_cannot_suspend_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.members.suspend', $admin))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'status' => 'active',
        ]);
    }
}
