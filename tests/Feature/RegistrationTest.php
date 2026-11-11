<?php

namespace Tests\Feature;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }
}
