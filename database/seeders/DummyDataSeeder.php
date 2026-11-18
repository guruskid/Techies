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

        foreach (['Lagos', 'Abuja', 'Rivers', 'Akwa Ibom', 'Oyo', 'Enugu', 'Edo'] as $loc) {
            Chapter::firstOrCreate(['name' => 'MWTT '.$loc], [
                'slug' => Str::slug('mwtt '.$loc),
                'location' => $loc,
                'description' => 'MWTT chapter in '.$loc,
                'status' => 'active',
            ]);
        }

        for ($i = 0; $i < 12; $i++) {
            $user = User::firstOrCreate(['email' => 'member'.$i.'@example.com'], [
                'name' => $faker->name,
                'password' => bcrypt('Password123!'),
                'role' => 'member',
                'status' => 'active',
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole('member');
            UserProfile::firstOrCreate(['user_id' => $user->id], [
                'username' => 'member'.$i,
                'professional_title' => $faker->jobTitle,
                'city' => $faker->city,
                'state' => $faker->randomElement(['Lagos', 'Abuja', 'Rivers', 'Oyo', 'Enugu', 'Edo']),
                'country' => 'Nigeria',
                'bio' => $faker->sentence,
                'is_public' => true,
                'technology_category' => $faker->randomElement(['Software Development', 'Frontend Development', 'Backend Development', 'Data Science', 'UI/UX Design']),
                'experience_level' => $faker->randomElement(['Student', 'Beginner', 'Intermediate', 'Experienced', 'Expert']),
            ]);
        }

        for ($i = 0; $i < 6; $i++) {
            $owner = User::firstOrCreate(['email' => 'hubowner'.$i.'@example.com'], [
                'name' => $faker->name,
                'password' => bcrypt('Password123!'),
                'role' => 'member',
                'status' => 'active',
            ]);
            $owner->forceFill(['email_verified_at' => now()])->save();
            $owner->assignRole('hub_owner');

            Hub::firstOrCreate(['slug' => 'hub-'.$i], [
                'user_id' => $owner->id,
                'name' => $faker->company.' Tech Hub',
                'description' => $faker->paragraph,
                'city' => $faker->city,
                'state' => $faker->randomElement(['Lagos', 'Abuja', 'Rivers', 'Oyo']),
                'country' => 'Nigeria',
                'status' => 'approved',
                'is_verified' => true,
                'is_public' => true,
                'email' => 'hub'.$i.'@example.com',
                'website' => 'https://example.com',
            ]);
        }

        $adminId = User::where('role', 'super_admin')->value('id') ?? User::first()?->id;

        $eventCategory = EventCategory::first();
        for ($i = 0; $i < 6; $i++) {
            Event::firstOrCreate(['slug' => 'event-'.$i], [
                'title' => 'MWTT '.$faker->words(3, true).' Meetup',
                'description' => $faker->paragraph,
                'category_id' => $eventCategory?->id,
                'start_date' => now()->addDays(rand(1, 30))->toDateString(),
                'city' => $faker->city,
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'is_online' => (bool) rand(0, 1),
                'status' => 'published',
                'organizer_id' => $adminId,
            ]);
        }

        $opportunityCategory = OpportunityCategory::first();
        for ($i = 0; $i < 6; $i++) {
            Opportunity::firstOrCreate(['slug' => 'opp-'.$i], [
                'title' => $faker->jobTitle.' Opportunity',
                'description' => $faker->paragraph,
                'category_id' => $opportunityCategory?->id,
                'organization' => $faker->company,
                'location' => $faker->city,
                'is_remote' => (bool) rand(0, 1),
                'application_link' => 'https://example.com/apply',
                'deadline' => now()->addDays(rand(7, 60))->toDateString(),
                'status' => 'published',
                'submitted_by' => $adminId,
            ]);
        }

        $this->call(ArticleSeeder::class);
    }
}
