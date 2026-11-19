<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureRoleIsCurrent;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_changing_a_signed_in_users_role_signs_them_out_on_the_next_request(): void
    {
        $member = $this->member();

        $this->actingAs($member)->get(route('dashboard'))->assertOk();

        $member->update(['role' => 'admin']);
        $member->syncRoles(['admin']);

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_suspending_a_signed_in_member_ends_their_session(): void
    {
        $member = $this->member();

        $this->actingAs($member)->get(route('dashboard'))->assertOk();

        $member->update(['status' => 'suspended']);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_a_tampered_or_missing_access_signature_is_signed_out(): void
    {
        $member = $this->member();

        $this->actingAs($member)
            ->withSession([EnsureRoleIsCurrent::SESSION_KEY => 'stale-signature'])
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_a_real_login_keeps_the_new_session_active(): void
    {
        $member = $this->member();

        $this->post(route('login'), [
            'email' => $member->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertOk();
        $this->assertAuthenticatedAs($member);
    }

    public function test_the_users_role_column_wins_over_a_stale_spatie_role(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user->syncRoles(['super_admin']);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());

        $this->actingAs($user)->get(route('super-admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }

    private function member(): User
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $member->assignRole('member');

        return $member;
    }
}
