<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use App\Models\OpportunityCategory;
use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        // seed categories
        foreach (['Meetups', 'Conferences', 'Workshops', 'Hackathons', 'Networking', 'Webinars', 'Training', 'Career Events', 'Community Events'] as $c) {
            EventCategory::firstOrCreate(['name' => $c], ['slug' => Str::slug($c)]);
        }
        foreach (['Jobs', 'Internships', 'Scholarships', 'Grants', 'Fellowships', 'Training', 'Mentorship', 'Hackathons', 'Competitions'] as $c) {
            OpportunityCategory::firstOrCreate(['name' => $c], ['slug' => Str::slug($c)]);
        }
        foreach (['Software Development', 'Frontend Development', 'Backend Development', 'Full-Stack Development', 'Mobile Development', 'DevOps and Cloud Engineering', 'Cybersecurity', 'Data Science', 'Artificial Intelligence and Machine Learning', 'UI/UX Design', 'Product Management', 'Digital Marketing', 'Networking and Infrastructure', 'Quality Assurance', 'Blockchain', 'Hardware and Embedded Systems', 'Technical Writing', 'Other'] as $c) {
            Skill::firstOrCreate(['name' => $c], ['slug' => Str::slug($c)]);
        }
    }
}
