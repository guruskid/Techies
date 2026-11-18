<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_accounts_get_a_profile_on_demand(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->assertNull($admin->profile);

        $profile = $admin->ensureProfile();

        $this->assertDatabaseHas('user_profiles', [
            'id' => $profile->id,
            'user_id' => $admin->id,
        ]);
        $this->assertNotNull($profile->username);
    }

    public function test_admin_and_super_admin_profiles_render_on_their_subdomain(): void
    {
        foreach (['admin' => 'staff-admin', 'super_admin' => 'staff-super-admin'] as $role => $username) {
            $user = User::factory()->create(['role' => $role, 'status' => 'active']);
            $user->ensureProfile()->update(['username' => $username, 'is_public' => true]);

            $this->get('http://'.$username.'.'.config('techies.profile_domain').'/')
                ->assertOk()
                ->assertSee($user->name);
        }
    }

    public function test_staff_profiles_appear_in_the_public_members_directory_when_public(): void
    {
        $admin = User::factory()->create([
            'name' => 'Staff Admin',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $admin->ensureProfile()->update(['is_public' => true]);

        $this->get('/members')
            ->assertOk()
            ->assertSee('Staff Admin');
    }

    public function test_staff_can_edit_their_profile(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);

        $this->actingAs($admin)->get('/profile')
            ->assertOk()
            ->assertSee('Public profile address');

        $this->assertNotNull($admin->fresh()->profile);
    }
}
