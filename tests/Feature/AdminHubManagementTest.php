<?php

namespace Tests\Feature;

use App\Models\Hub;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHubManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function hub(array $attributes = []): Hub
    {
        $owner = User::factory()->create(['role' => 'member', 'status' => 'active']);

        return Hub::create(array_merge([
            'user_id' => $owner->id,
            'name' => 'Lagos Tech Hub',
            'slug' => 'lagos-tech-hub',
            'status' => 'pending',
            'is_verified' => false,
            'is_public' => false,
        ], $attributes));
    }

    public function test_hub_index_renders_the_suspend_action(): void
    {
        $hub = $this->hub(['status' => 'approved', 'is_verified' => true, 'is_public' => true]);

        $this->actingAs($this->admin())
            ->get(route('admin.hubs.index'))
            ->assertOk()
            ->assertSee(route('admin.hubs.suspend', $hub), false);
    }

    public function test_admin_can_approve_a_hub(): void
    {
        $hub = $this->hub();

        $this->actingAs($this->admin())
            ->post(route('admin.hubs.approve', $hub))
            ->assertRedirect();

        $hub->refresh();

        $this->assertSame('approved', $hub->status);
        $this->assertTrue($hub->is_public);
    }

    public function test_admin_can_suspend_a_hub(): void
    {
        $hub = $this->hub(['status' => 'approved', 'is_verified' => true, 'is_public' => true]);

        $this->actingAs($this->admin())
            ->post(route('admin.hubs.suspend', $hub))
            ->assertRedirect();

        $hub->refresh();

        $this->assertSame('suspended', $hub->status);
        $this->assertFalse($hub->is_public);
    }
}
