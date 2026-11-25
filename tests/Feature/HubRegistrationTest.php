<?php

namespace Tests\Feature;

use App\Models\Hub;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HubRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_register_a_hub_without_preseeded_roles(): void
    {
        $user = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->assertDatabaseCount('roles', 0);

        $this->actingAs($user)->post('/hubs/register', [
            'name' => 'Lagos Tech Hub',
            'description' => 'A community hub for Lagos techies.',
            'country' => 'Nigeria',
            'state' => 'Lagos',
            'city' => 'Ikeja',
        ])->assertRedirect(route('hub.dashboard'));

        $this->assertDatabaseHas('hubs', [
            'user_id' => $user->id,
            'name' => 'Lagos Tech Hub',
            'status' => 'pending',
        ]);
        $this->assertTrue($user->fresh()->hasRole('hub_owner'));
    }

    public function test_member_cannot_register_a_second_hub(): void
    {
        $user = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($user)->post('/hubs/register', ['name' => 'First Hub']);
        $this->actingAs($user)->post('/hubs/register', ['name' => 'Second Hub'])
            ->assertRedirect(route('hub.dashboard'));

        $this->assertDatabaseCount('hubs', 1);
    }

    public function test_hub_directory_links_to_hub_registration(): void
    {
        $this->get(route('hubs'))
            ->assertOk()
            ->assertSee('Register your hub')
            ->assertSee(route('hubs.register'), false);
    }

    public function test_guests_see_account_steps_instead_of_an_unavailable_application_form(): void
    {
        $this->get(route('hubs.register'))
            ->assertOk()
            ->assertSee('Create an account')
            ->assertSee(route('login'), false)
            ->assertDontSee('name="name"', false);
    }

    public function test_members_without_active_membership_see_the_membership_step(): void
    {
        $user = User::factory()->create(['membership_status' => 'pending']);

        $this->actingAs($user)->get(route('hubs.register'))
            ->assertOk()
            ->assertSee('Activate your membership to apply.')
            ->assertSee(route('membership.show'), false)
            ->assertDontSee('name="name"', false);
    }

    public function test_existing_hub_owners_are_sent_to_their_workspace(): void
    {
        $user = User::factory()->create();
        Hub::create([
            'user_id' => $user->id,
            'name' => 'Existing Hub',
            'slug' => 'existing-hub',
            'status' => 'pending',
            'is_verified' => false,
            'is_public' => false,
        ]);

        $this->actingAs($user)->get(route('hubs.register'))
            ->assertOk()
            ->assertSee('Your hub is already registered.')
            ->assertSee(route('hub.dashboard'), false)
            ->assertDontSee('name="name"', false);
    }
}
