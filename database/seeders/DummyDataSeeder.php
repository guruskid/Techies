<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\OpportunityCategory;
use App\Models\User;
use App\Models\UserProfile;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        // chapters
        foreach (['Lagos', 'Abuja', 'Rivers', 'Akwa Ibom', 'Oyo', 'Enugu', 'Edo'] as $loc) {
            Chapter::firstOrCreate(['name' => 'MWTT '.$loc], ['slug' => Str::slug('mwtt '.$loc), 'location' => $loc, 'description' => 'MWTT chapter in '.$loc, 'status' => 'active']);
        }
        // members
        for ($i = 0; $i < 12; $i++) {
            $u = User::create(['name' => $faker->name, 'email' => 'member'.$i.'@example.com', 'password' => bcrypt('Password123!'), 'role' => 'member', 'status' => 'active', 'email_verified_at' => now()]);
            $u->assignRole('member');
            UserProfile::create([
                'user_id' => $u->id, 'username' => 'member'.$i, 'professional_title' => $faker->jobTitle,
                'city' => $faker->city, 'state' => $faker->randomElement(['Lagos', 'Abuja', 'Rivers', 'Oyo', 'Enugu', 'Edo']),
                'country' => 'Nigeria', 'bio' => $faker->sentence, 'is_public' => true,
                'technology_category' => $faker->randomElement(['Software Development', 'Frontend Development', 'Backend Development', 'Data Science', 'UI/UX Design']),
                'experience_level' => $faker->randomElement(['Student', 'Beginner', 'Intermediate', 'Experienced', 'Expert']),
            ]);
        }
        // hubs
        for ($i = 0; $i < 6; $i++) {
            $owner = User::create(['name' => $faker->name, 'email' => 'hubowner'.$i.'@example.com', 'password' => bcrypt('Password123!'), 'role' => 'member', 'status' => 'active', 'email_verified_at' => now()]);
            $owner->assignRole('member');
            $hub = Hub::create([
                'user_id' => $owner->id, 'name' => $faker->company.' Tech Hub', 'slug' => 'hub-'.$i, 'description' => $faker->paragraph,
                'city' => $faker->city, 'state' => $faker->randomElement(['Lagos', 'Abuja', 'Rivers', 'Oyo']),
                'country' => 'Nigeria', 'status' => 'approved', 'is_verified' => true, 'is_public' => true,
                'email' => 'hub'.$i.'@example.com', 'website' => 'https://example.com',
            ]);
        }
        // events
        $cat = EventCategory::first();
        for ($i = 0; $i < 6; $i++) {
            Event::create([
                'title' => 'MWTT '.$faker->words(3, true).' Meetup', 'slug' => 'event-'.$i, 'description' => $faker->paragraph,
                'category_id' => $cat?->id, 'start_date' => now()->addDays(rand(1, 30))->toDateString(),
                'city' => $faker->city, 'state' => 'Lagos', 'country' => 'Nigeria', 'is_online' => rand(0, 1),
                'status' => 'published', 'organizer_id' => 1,
            ]);
        }
        // opportunities
        $ocat = OpportunityCategory::first();
        for ($i = 0; $i < 6; $i++) {
            Opportunity::create([
                'title' => $faker->jobTitle.' Opportunity', 'slug' => 'opp-'.$i, 'description' => $faker->paragraph,
                'category_id' => $ocat?->id, 'organization' => $faker->company, 'location' => $faker->city,
                'is_remote' => rand(0, 1), 'application_link' => 'https://example.com/apply', 'deadline' => now()->addDays(rand(7, 60))->toDateString(),
                'status' => 'published', 'submitted_by' => 1,
            ]);
        }
    }
}
