<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminRolePreservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_changing_a_primary_role_keeps_extra_capability_roles(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);
        $superAdmin->assignRole('super_admin');

        $target = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        Role::findOrCreate('hub_owner');
        $target->assignRole(['admin', 'hub_owner']);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.users.role', $target), ['role' => 'member'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $target->refresh();

        $this->assertSame('member', $target->role);
        $this->assertTrue($target->hasRole('hub_owner'));
        $this->assertFalse($target->hasRole('admin'));
    }
}
