<?php

namespace Tests\Feature;

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
}
