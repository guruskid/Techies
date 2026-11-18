<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@meettechies.ng')],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Password123!')),
                'role' => 'super_admin',
                'status' => 'active',
            ]
        );

        $admin->forceFill(['email_verified_at' => now()])->save();
        $admin->assignRole('super_admin');

        // Staff accounts are community members too, so they own a profile.
        $admin->ensureProfile();
    }
}
