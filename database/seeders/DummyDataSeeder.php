<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Cohort;
use App\Models\EmailTemplate;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventRegistration;
use App\Models\Grant;
use App\Models\GrantApplication;
use App\Models\Hub;
use App\Models\HubApplication;
use App\Models\Interest;
use App\Models\IntroRequest;
use App\Models\LoungePost;
use App\Models\LoungeReply;
use App\Models\MemberBenefit;
use App\Models\MemberFeedback;
use App\Models\MemberSpotlight;
use App\Models\MentorshipRequest;
use App\Models\NewsletterSubscriber;
use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Models\OpportunityCategory;
use App\Models\ReferralOffer;
use App\Models\ReferralRequest;
use App\Models\Setting;
use App\Models\ShowcaseProject;
use App\Models\Skill;
use App\Models\SpeakerCall;
use App\Models\SpeakerSubmission;
use App\Models\TechnologyArea;
use App\Models\User;
use App\Models\UserProfile;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * A realistic demo dataset for local review.
 *
 * Run it with: php artisan db:seed --class=DummyDataSeeder
 *
 * The seeder is idempotent: records are matched by email, slug or title, so
 * running it again refresh the demo content instead of duplicating it.
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        $faker = Factory::create();

        $superAdmin = $this->seedSuperAdmin();
        $staff = $this->seedStaff();
        $this->seedTaxonomy();

        $members = $this->seedMembers($faker);
        $this->seedPendingMember($faker);
        $owners = $this->seedHubOwners($faker);

        $hubs = $this->seedHubs($owners, $superAdmin);
        $this->seedHubTeams($hubs, $members);
        $chapters = $this->seedChapters($members);
        $events = $this->seedEvents($staff, $members, $hubs, $chapters);

        $this->seedRegistrations($events, $members);
        $this->seedSpeakerCalls($events, $members, $staff);
        $this->seedOpportunities($staff, $members, $hubs);
        $this->seedAnnouncements($staff, $chapters);
        $this->seedNewsletter($faker);
        $this->seedEmailTemplates($staff);

        Setting::updateOrCreate(['key' => 'coming_soon'], ['value' => '0']);
        $this->seedMembershipSettings();
        $this->seedMemberExperience($members);
        $this->seedCommunityExtras($members);

        $this->call(ArticleSeeder::class);
        $this->seedRichArticles($members, $superAdmin);
    }

    private function seedSuperAdmin(): User
    {
        $email = env('ADMIN_EMAIL', 'admin@meettechies.ng');

        $user = User::firstOrCreate(['email' => $email], [
            'name' => 'Super Admin',
            'password' => Hash::make('Password123!'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $user->forceFill([
            'role' => 'super_admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ])->save();

        $user->syncRoles(['super_admin']);

        $this->profile($user, [
            'username' => 'mwttadmin',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone' => '+234 800 000 0001',
            'avatar' => 'https://ui-avatars.com/api/?name=MWTT+Admin&background=FF5C3D&color=fff&size=256',
            'professional_title' => 'Platform Administrator',
            'technology_category' => 'Product Management',
            'experience_level' => 'Expert',
            'current_organization' => 'Meet with the Techies',
            'education' => 'MSc Information Technology',
            'bio' => 'Keeps the Meet with the Techies platform running for members, hubs and chapters.',
            'headline' => 'Platform operations and community support.',
            'country' => 'Nigeria',
            'state' => 'Lagos',
            'city' => 'Lagos',
            'linkedin' => 'https://www.linkedin.com/company/meet-with-the-techies',
            'github' => 'https://github.com/meettechies',
            'twitter' => 'https://x.com/meettechies',
            'facebook' => 'https://facebook.com/meettechies',
            'tiktok' => 'https://www.tiktok.com/@meettechies',
            'is_public' => true,
            'weekly_digest' => true,
            'likes' => 'Community building, documentation, open source',
            'certifications' => 'AWS Cloud Practitioner',
            'languages' => 'English (fluent), Igbo (native)',
            'availability' => 'Open to mentoring',
        ]);

        return $user;
    }

    private function seedStaff(): User
    {
        $user = User::updateOrCreate(['email' => 'staff@meettechies.ng'], [
            'name' => 'Ngozi Achebe',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->syncRoles(['admin']);

        $this->profile($user, [
            'username' => 'ngoziachebe',
            'first_name' => 'Ngozi',
            'last_name' => 'Achebe',
            'phone' => '+234 800 000 0002',
            'avatar' => 'https://i.pravatar.cc/256?img=47',
            'professional_title' => 'Community Programmes Lead',
            'technology_category' => 'Product Management',
            'experience_level' => 'Experienced',
            'current_organization' => 'Meet with the Techies',
            'education' => 'BSc Mass Communication, University of Nigeria',
            'bio' => 'Coordinates programmes, reviews community content and supports chapter leads across the network.',
            'headline' => 'Programmes, partnerships and community operations.',
            'country' => 'Nigeria',
            'state' => 'Enugu',
            'city' => 'Enugu',
            'linkedin' => 'https://www.linkedin.com/in/ngozi-achebe',
            'facebook' => 'https://facebook.com/ngoziachebe',
            'tiktok' => 'https://www.tiktok.com/@ngoziachebe',
            'is_public' => true,
            'weekly_digest' => true,
            'likes' => 'Mentorship, storytelling, event design',
            'certifications' => 'Project Management Professional (PMP)',
            'languages' => 'English (fluent), Igbo (native)',
            'availability' => 'Open to collaborations',
        ]);

        return $user;
    }

    private function seedTaxonomy(): void
    {
        foreach ([
            'Artificial Intelligence',
            'Web Development',
            'Mobile Development',
            'Cloud & DevOps',
            'Data Science',
            'Cybersecurity',
            'UI/UX Design',
            'Blockchain',
            'Internet of Things',
            'Product Management',
        ] as $name) {
            TechnologyArea::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
        }

        foreach ([
            'Open Source',
            'Startups',
            'Mentorship',
            'Hackathons',
            'AI & Ethics',
            'Design Systems',
            'Community Building',
            'Career Growth',
            'Web3',
            'Data for Good',
        ] as $name) {
            Interest::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
        }
    }

    private function seedPendingMember(Generator $faker): void
    {
        $user = User::updateOrCreate(['email' => 'newmember@example.com'], [
            'name' => 'Chisom Nnadi',
            'password' => Hash::make('Password123!'),
            'role' => 'member',
            'status' => 'active',
            'membership_status' => 'unpaid',
            'is_founding_member' => false,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->syncRoles(['member']);

        $this->profile($user, [
            'username' => 'chisomnnadi',
            'first_name' => 'Chisom',
            'last_name' => 'Nnadi',
            'phone' => '+234 805 555 0110',
            'avatar' => 'https://i.pravatar.cc/256?img=32',
            'professional_title' => 'Junior Data Analyst',
            'technology_category' => 'Data Science',
            'experience_level' => 'Beginner',
            'current_organization' => 'Freelance',
            'education' => 'BSc Statistics, University of Nigeria',
            'bio' => 'Learning data analysis and looking for a community that helps me grow.',
            'headline' => 'Early-career data analyst building public projects.',
            'country' => 'Nigeria',
            'state' => 'Enugu',
            'city' => 'Enugu',
            'linkedin' => 'https://www.linkedin.com/in/chisomnnadi',
            'is_public' => false,
            'weekly_digest' => true,
            'likes' => 'Data, reading, community meetups',
            'languages' => 'English (fluent), Igbo (native)',
            'availability' => 'Open to mentoring',
        ]);
    }

    private function seedMembershipSettings(): void
    {
        $settings = [
            'membership_fee' => '15000',
            'membership_currency' => 'NGN',
            'membership_payment_url' => 'https://example.com/pay/mwtt-membership',
            'membership_payment_instructions' => 'Pay ₦15,000 through the payment link above. If you prefer a bank transfer, contact the team for the account details and use your email address as the reference.',
            'membership_benefits' => implode("\n", [
                'Member events, workshops and meetups',
                'A public profile that showcases your work',
                'Mentorship and peer learning circles',
                'Opportunity applications and career support',
                'Speaker calls, volunteering and chapter membership',
            ]),
            'membership_guarantee' => 'If the community is not a fit, contact us within 14 days of joining and we will review your refund.',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Setting::forget($key);
        }
    }

    /**
     * @return array<int, User>
     */
    private function seedMembers(Generator $faker): array
    {
        $seeds = [
            ['Adaeze Okafor', 'adaezeokafor', 'Senior Frontend Engineer', 'Kite Labs', 'Frontend Development', 'Experienced', 'Lagos', 'Lagos', ['Frontend Development', 'UI/UX Design'], ['Open Source', 'Design Systems'], true],
            ['Tunde Balogun', 'tundebalogun', 'Backend Engineer', 'Northstar Systems', 'Backend Development', 'Experienced', 'Lagos', 'Lagos', ['Backend Development', 'DevOps and Cloud Engineering'], ['Open Source', 'Startups'], true],
            ['Amaka Nwosu', 'amakanwosu', 'Product Designer', 'Studio Kora', 'UI/UX Design', 'Intermediate', 'Enugu', 'Enugu', ['UI/UX Design', 'Product Management'], ['Design Systems', 'Mentorship'], true],
            ['Ibrahim Musa', 'ibrahimmusa', 'Data Analyst', 'Sahel Insights', 'Data Science', 'Intermediate', 'Abuja', 'FCT', ['Data Science'], ['Data for Good', 'AI & Ethics'], true],
            ['Chiamaka Eze', 'chiamakaeze', 'DevOps Engineer', 'Riverline Cloud', 'DevOps and Cloud Engineering', 'Experienced', 'Port Harcourt', 'Rivers', ['DevOps and Cloud Engineering', 'Cybersecurity'], ['Open Source', 'Community Building'], true],
            ['Seun Adeyemi', 'seunadeyemi', 'Mobile Developer', 'Ibadan Apps', 'Mobile Development', 'Intermediate', 'Ibadan', 'Oyo', ['Mobile Development', 'Frontend Development'], ['Startups', 'Hackathons'], true],
            ['Fatima Bello', 'fatimabello', 'Cybersecurity Analyst', 'Shield Lab', 'Cybersecurity', 'Experienced', 'Abuja', 'FCT', ['Cybersecurity', 'Networking and Infrastructure'], ['AI & Ethics', 'Career Growth'], true],
            ['Emeka Obi', 'emekaobi', 'Full-Stack Developer', 'Enugu Digital', 'Full-Stack Development', 'Intermediate', 'Enugu', 'Enugu', ['Full-Stack Development', 'Backend Development'], ['Open Source', 'Mentorship'], true],
            ['Zainab Yusuf', 'zainabyusuf', 'Product Manager', 'Lagos Builders Hub', 'Product Management', 'Experienced', 'Lagos', 'Lagos', ['Product Management', 'UI/UX Design'], ['Startups', 'Community Building'], true],
            ['David Etim', 'davidetim', 'Data Scientist', 'Uyo Analytics', 'Data Science', 'Intermediate', 'Uyo', 'Akwa Ibom', ['Data Science', 'Artificial Intelligence and Machine Learning'], ['AI & Ethics', 'Data for Good'], true],
            ['Blessing Ojo', 'blessingojo', 'Technical Writer', 'Docs Collective', 'Technical Writing', 'Intermediate', 'Benin City', 'Edo', ['Technical Writing', 'Frontend Development'], ['Open Source', 'Community Building'], true],
            ['Samuel Ade', 'samuelade', 'QA Engineer', 'Kite Labs', 'Quality Assurance', 'Intermediate', 'Lagos', 'Lagos', ['Quality Assurance', 'Frontend Development'], ['Open Source', 'Career Growth'], true],
            ['Halima Sani', 'halimasani', 'AI Engineer', 'Abuja Innovation Lab', 'Artificial Intelligence and Machine Learning', 'Experienced', 'Abuja', 'FCT', ['Artificial Intelligence and Machine Learning', 'Data Science'], ['AI & Ethics', 'Mentorship'], false],
            ['Peter Okon', 'peterokon', 'Cloud Engineer', 'Riverline Cloud', 'DevOps and Cloud Engineering', 'Experienced', 'Port Harcourt', 'Rivers', ['DevOps and Cloud Engineering', 'Networking and Infrastructure'], ['Open Source', 'Startups'], true],
        ];

        $members = [];

        foreach ($seeds as $index => $seed) {
            [$name, $username, $title, $organisation, $category, $level, $city, $state, $skills, $interests, $isPublic] = $seed;

            $user = User::updateOrCreate(['email' => 'member'.$index.'@example.com'], [
                'name' => $name,
                'password' => Hash::make('Password123!'),
                'role' => 'member',
                'status' => 'active',
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->syncRoles(['member']);

            [$firstName, $lastName] = array_pad(explode(' ', $name, 2), 2, '');

            $this->profile($user, [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => '+234 80'.str_pad((string) (10000000 + $index), 8, '0', STR_PAD_LEFT),
                'avatar' => 'https://i.pravatar.cc/256?img='.($index + 1),
                'professional_title' => $title,
                'technology_category' => $category,
                'experience_level' => $level,
                'current_organization' => $organisation,
                'education' => $faker->randomElement([
                    'BSc Computer Science, University of Lagos',
                    'BEng Software Engineering, Covenant University',
                    'BSc Information Technology, University of Nigeria',
                    'MSc Data Science, University of Abuja',
                    'BSc Computer Science, University of Ibadan',
                ]),
                'bio' => $faker->randomElement([
                    'Builds products for African users and enjoys helping newcomers find their first opportunity.',
                    'Works across product and engineering, with a focus on shipping reliable systems that people trust.',
                    'Community volunteer, occasional speaker and a strong believer in learning in public.',
                    'Turns messy problems into clear interfaces and documents the decisions along the way.',
                ]),
                'headline' => $title.' focused on practical, community-driven technology.',
                'country' => 'Nigeria',
                'state' => $state,
                'city' => $city,
                'linkedin' => 'https://www.linkedin.com/in/'.$username,
                'github' => 'https://github.com/'.$username,
                'twitter' => 'https://x.com/'.$username,
                'facebook' => 'https://facebook.com/'.$username,
                'tiktok' => 'https://www.tiktok.com/@'.$username,
                'areas_of_interest' => $interests,
                'preferred_participation' => 'Mentoring, speaking and community projects',
                'membership_type' => 'Community member',
                'is_public' => $isPublic,
                'weekly_digest' => $index % 3 !== 0,
                'likes' => $faker->randomElement([
                    'Open source, football, music',
                    'Reading, design, community meetups',
                    'Running, podcasts, mentoring',
                    'Travel, photography, product design',
                ]),
                'certifications' => $faker->randomElement([
                    'Google Data Analytics Certificate',
                    'AWS Cloud Practitioner',
                    'Meta Front-End Developer Certificate',
                    'Certified Scrum Product Owner',
                ]),
                'languages' => 'English (fluent), '.$faker->randomElement(['Yoruba', 'Igbo', 'Hausa']).' (conversational)',
                'availability' => $faker->randomElement(['Open to mentoring', 'Open to speaking', 'Open to collaborations']),
            ]);

            $user->skills()->sync(Skill::whereIn('name', $skills)->pluck('id'));
            $user->interests()->sync(Interest::whereIn('name', $interests)->pluck('id'));

            $members[] = $user;
        }

        return $members;
    }

    /**
     * @return array<int, User>
     */
    private function seedHubOwners(Generator $faker): array
    {
        $seeds = [
            ['Bola Martins', 'lagosbuilders', 'Lagos', 'Lagos'],
            ['Aisha Garba', 'abujainnovation', 'Abuja', 'FCT'],
            ['Tamuno Briggs', 'portharcourttech', 'Port Harcourt', 'Rivers'],
            ['Kemi Alabi', 'ibadanstartups', 'Ibadan', 'Oyo'],
            ['Chinedu Umeh', 'enugucampus', 'Enugu', 'Enugu'],
            ['Sadiq Lawal', 'kanodata', 'Kano', 'Kano'],
        ];

        $owners = [];

        foreach ($seeds as $index => $seed) {
            [$name, $username, $city, $state] = $seed;

            $user = User::updateOrCreate(['email' => 'hubowner'.$index.'@example.com'], [
                'name' => $name,
                'password' => Hash::make('Password123!'),
                'role' => 'member',
                'status' => 'active',
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->syncRoles(['member', 'hub_owner']);

            [$firstName, $lastName] = array_pad(explode(' ', $name, 2), 2, '');

            $this->profile($user, [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => '+234 70'.str_pad((string) (20000000 + $index), 8, '0', STR_PAD_LEFT),
                'avatar' => 'https://i.pravatar.cc/256?img='.($index + 20),
                'professional_title' => 'Hub Lead',
                'technology_category' => 'Product Management',
                'experience_level' => 'Experienced',
                'current_organization' => 'Community Hub',
                'education' => 'BSc Computer Science',
                'bio' => 'Runs a community space for builders, founders and students in '.$city.'.',
                'headline' => 'Building the local technology community in '.$city.'.',
                'country' => 'Nigeria',
                'state' => $state,
                'city' => $city,
                'linkedin' => 'https://www.linkedin.com/in/'.$username,
                'facebook' => 'https://facebook.com/'.$username,
                'tiktok' => 'https://www.tiktok.com/@'.$username,
                'is_public' => true,
                'weekly_digest' => true,
                'likes' => 'Community building, partnerships, events',
                'languages' => 'English (fluent)',
                'availability' => 'Open to partnerships',
            ]);

            $owners[] = $user;
        }

        return $owners;
    }

    /**
     * @param  array<int, User>  $owners
     * @return array<string, Hub>
     */
    private function seedHubs(array $owners, User $superAdmin): array
    {
        $seeds = [
            [
                'key' => 'lagos',
                'slug' => 'lagos-builders-hub',
                'name' => 'Lagos Builders Hub',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'description' => '<p>A co-working and community space in Yaba for engineers, designers and founders building products for African users.</p><p>We run weekly build nights, monthly meetups and a mentorship circle for early-career members.</p>',
                'type' => 'Co-working and community space',
                'year' => 2019,
                'size' => '201-500',
                'members' => 340,
                'status' => 'approved',
                'verified' => true,
                'public' => true,
                'areas' => ['Web Development', 'Mobile Development', 'Product Management'],
            ],
            [
                'key' => 'abuja',
                'slug' => 'abuja-innovation-lab',
                'name' => 'Abuja Innovation Lab',
                'city' => 'Abuja',
                'state' => 'FCT',
                'description' => '<p>An innovation lab supporting public-sector technology, data and design teams across the capital.</p><p>Our programmes connect practitioners with mentors and real community projects.</p>',
                'type' => 'Innovation lab',
                'year' => 2020,
                'size' => '51-200',
                'members' => 180,
                'status' => 'approved',
                'verified' => true,
                'public' => true,
                'areas' => ['Data Science', 'Artificial Intelligence', 'UI/UX Design'],
            ],
            [
                'key' => 'ph',
                'slug' => 'port-harcourt-tech-circle',
                'name' => 'Port Harcourt Tech Circle',
                'city' => 'Port Harcourt',
                'state' => 'Rivers',
                'description' => '<p>A growing community of cloud, security and software professionals in the Niger Delta.</p><p>We meet monthly and run practical workshops for students and career switchers.</p>',
                'type' => 'Community group',
                'year' => 2021,
                'size' => '51-200',
                'members' => 120,
                'status' => 'approved',
                'verified' => true,
                'public' => true,
                'areas' => ['Cloud & DevOps', 'Cybersecurity', 'Web Development'],
            ],
            [
                'key' => 'ibadan',
                'slug' => 'ibadan-startup-studio',
                'name' => 'Ibadan Startup Studio',
                'city' => 'Ibadan',
                'state' => 'Oyo',
                'description' => '<p>A startup studio and community for founders building early-stage products in Oyo State.</p><p>Members get access to build days, pitch practice and a network of operators.</p>',
                'type' => 'Startup studio',
                'year' => 2022,
                'size' => '11-50',
                'members' => 70,
                'status' => 'approved',
                'verified' => true,
                'public' => true,
                'areas' => ['Web Development', 'Product Management', 'Mobile Development'],
            ],
            [
                'key' => 'enugu',
                'slug' => 'enugu-campus-innovation',
                'name' => 'Enugu Campus Innovation',
                'city' => 'Enugu',
                'state' => 'Enugu',
                'description' => '<p>A campus-focused innovation club connecting students with mentors, internships and practical projects.</p>',
                'type' => 'Campus club',
                'year' => 2023,
                'size' => '51-200',
                'members' => 95,
                'status' => 'pending',
                'verified' => false,
                'public' => false,
                'areas' => ['Web Development', 'UI/UX Design'],
            ],
            [
                'key' => 'kano',
                'slug' => 'kano-data-collective',
                'name' => 'Kano Data Collective',
                'city' => 'Kano',
                'state' => 'Kano',
                'description' => '<p>A data-focused collective for analysts and researchers working on public-interest problems.</p>',
                'type' => 'Community group',
                'year' => 2024,
                'size' => '11-50',
                'members' => 40,
                'status' => 'rejected',
                'verified' => false,
                'public' => false,
                'areas' => ['Data Science', 'Artificial Intelligence'],
            ],
        ];

        $hubs = [];

        foreach ($seeds as $index => $seed) {
            $owner = $owners[$index];

            $hub = Hub::updateOrCreate(['slug' => $seed['slug']], [
                'user_id' => $owner->id,
                'name' => $seed['name'],
                'logo' => 'https://ui-avatars.com/api/?name='.urlencode($seed['name']).'&background=101E33&color=fff&size=256',
                'cover_image' => 'https://picsum.photos/seed/'.$seed['slug'].'/1600/600',
                'description' => $seed['description'],
                'year_established' => $seed['year'],
                'organization_type' => $seed['type'],
                'email' => $owner->email,
                'phone' => '+234 811 000 '.str_pad((string) (1000 + $index), 4, '0', STR_PAD_LEFT),
                'website' => 'https://example.com/'.$seed['slug'],
                'contact_person' => $owner->name,
                'contact_position' => 'Hub Lead',
                'country' => 'Nigeria',
                'state' => $seed['state'],
                'city' => $seed['city'],
                'address' => $seed['city'].', '.$seed['state'].', Nigeria',
                'community_size' => $seed['size'],
                'active_members_count' => $seed['members'],
                'programs_offered' => 'Meetups, workshops, mentorship, build nights, career clinics',
                'linkedin' => 'https://www.linkedin.com/company/'.$seed['slug'],
                'facebook' => 'https://facebook.com/'.$seed['slug'],
                'instagram' => 'https://instagram.com/'.$seed['slug'],
                'x' => 'https://x.com/'.$seed['slug'],
                'status' => $seed['status'],
                'is_verified' => $seed['verified'],
                'is_public' => $seed['public'],
            ]);

            $hub->technologyAreas()->sync(
                TechnologyArea::whereIn('name', $seed['areas'])->pluck('id')
            );

            $application = [
                'status' => $seed['status'] === 'approved' ? 'approved' : ($seed['status'] === 'rejected' ? 'rejected' : 'pending'),
                'review_notes' => $seed['status'] === 'rejected'
                    ? 'We could not verify the organisation details supplied. The owner can update the application and resubmit.'
                    : null,
                'reviewed_by' => $seed['status'] === 'pending' ? null : $superAdmin->id,
                'reviewed_at' => $seed['status'] === 'pending' ? null : now()->subDays(10 - $index),
            ];

            HubApplication::updateOrCreate(['hub_id' => $hub->id], $application);

            $hubs[$seed['key']] = $hub;
        }

        return $hubs;
    }

    /**
     * @param  array<int, User>  $members
     * @return array<string, Chapter>
     */
    private function seedChapters(array $members): array
    {
        $seeds = [
            ['key' => 'lagos', 'slug' => 'mwtt-lagos', 'name' => 'MWTT Lagos', 'city' => 'Lagos', 'state' => 'Lagos', 'coordinator' => 0, 'second' => 1],
            ['key' => 'abuja', 'slug' => 'mwtt-abuja', 'name' => 'MWTT Abuja', 'city' => 'Abuja', 'state' => 'FCT', 'coordinator' => 3, 'second' => 6],
            ['key' => 'ph', 'slug' => 'mwtt-port-harcourt', 'name' => 'MWTT Port Harcourt', 'city' => 'Port Harcourt', 'state' => 'Rivers', 'coordinator' => 4, 'second' => 13],
            ['key' => 'ibadan', 'slug' => 'mwtt-ibadan', 'name' => 'MWTT Ibadan', 'city' => 'Ibadan', 'state' => 'Oyo', 'coordinator' => 5, 'second' => null],
            ['key' => 'enugu', 'slug' => 'mwtt-enugu', 'name' => 'MWTT Enugu', 'city' => 'Enugu', 'state' => 'Enugu', 'coordinator' => 2, 'second' => 7],
            ['key' => 'uyo', 'slug' => 'mwtt-uyo', 'name' => 'MWTT Uyo', 'city' => 'Uyo', 'state' => 'Akwa Ibom', 'coordinator' => 9, 'second' => null],
            ['key' => 'benin', 'slug' => 'mwtt-benin-city', 'name' => 'MWTT Benin City', 'city' => 'Benin City', 'state' => 'Edo', 'coordinator' => 10, 'second' => 11],
        ];

        $chapters = [];

        foreach ($seeds as $index => $seed) {
            $chapter = Chapter::updateOrCreate(['slug' => $seed['slug']], [
                'name' => $seed['name'],
                'logo' => 'https://ui-avatars.com/api/?name='.urlencode($seed['name']).'&background=101E33&color=fff&size=256',
                'location' => $seed['city'],
                'country' => 'Nigeria',
                'state' => $seed['state'],
                'city' => $seed['city'],
                'description' => '<p>The '.$seed['name'].' chapter brings together developers, designers and founders in '.$seed['city'].'.</p><p>We host monthly meetups, study groups and a peer mentorship circle. Everyone is welcome, whether you are writing your first line of code or leading a team.</p>',
                'coordinator_id' => $members[$seed['coordinator']]->id,
                'second_coordinator_id' => $seed['second'] === null ? null : $members[$seed['second']]->id,
                'status' => 'active',
            ]);

            $chapterMembers = collect([
                $members[$seed['coordinator']]->id,
                $seed['second'] === null ? null : $members[$seed['second']]->id,
            ])->filter()->merge(
                collect($members)->slice($index % 6, 5)->pluck('id')
            )->unique()->values()->all();

            $chapter->members()->syncWithoutDetaching($chapterMembers);

            $chapters[$seed['key']] = $chapter;
        }

        return $chapters;
    }

    /**
     * @param  array<int, User>  $members
     * @param  array<string, Hub>  $hubs
     * @param  array<string, Chapter>  $chapters
     * @return array<string, Event>
     */
    private function seedEvents(User $staff, array $members, array $hubs, array $chapters): array
    {
        $seeds = [
            [
                'slug' => 'lagos-frontend-meetup',
                'title' => 'Lagos Frontend Meetup: Building Fast Interfaces',
                'category' => 'Meetups',
                'organizer' => $members[0],
                'hub' => $hubs['lagos'],
                'chapter' => $chapters['lagos'],
                'host' => 'Lagos Builders Hub',
                'description' => '<p>Join us for an evening of practical frontend talks, live demos and honest conversations about building interfaces that feel fast on real networks.</p><h2>What to expect</h2><ul><li>Two short talks and a live code review</li><li>A panel on performance budgets and accessibility</li><li>Open networking with the Lagos community</li></ul>',
                'agenda' => '<h3>Agenda</h3><ol><li>17:00 — Doors open and welcome</li><li>17:30 — Building a design system that scales</li><li>18:15 — Performance debugging in the browser</li><li>19:00 — Panel and audience questions</li><li>19:45 — Networking</li></ol>',
                'requirements' => '<p>Bring a laptop if you want to follow the live demo. No prior experience is required.</p>',
                'speakers' => "Adaeze Okafor\nTunde Balogun\nZainab Yusuf",
                'speaker_images' => [
                    'https://picsum.photos/seed/mwtt-speaker-1/800/800',
                    'https://picsum.photos/seed/mwtt-speaker-2/800/800',
                    'https://picsum.photos/seed/mwtt-speaker-3/800/800',
                ],
                'start_date' => now()->addDays(12)->toDateString(),
                'start_time' => '17:00',
                'end_time' => '20:00',
                'venue' => 'Yaba Technology Hub',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'is_online' => false,
                'max_attendees' => 60,
                'registration_type' => 'free',
                'status' => 'published',
            ],
            [
                'slug' => 'remote-work-career-clinic',
                'title' => 'Remote Work Career Clinic',
                'category' => 'Webinars',
                'organizer' => $staff,
                'hub' => $hubs['lagos'],
                'chapter' => null,
                'host' => 'Meet with the Techies',
                'description' => '<p>A practical online clinic for members who want to move into remote roles or improve how they work with distributed teams.</p><h2>We will cover</h2><ul><li>Writing a remote-ready CV and profile</li><li>Preparing for asynchronous interviews</li><li>Setting boundaries and staying visible</li></ul>',
                'agenda' => '<p>45 minutes of guided discussion followed by 30 minutes of live Q&amp;A.</p>',
                'requirements' => '<p>Register to receive the meeting link. Bring one question you want answered.</p>',
                'speakers' => "Ngozi Achebe\nTunde Balogun",
                'speaker_images' => [
                    'https://picsum.photos/seed/mwtt-remote-1/800/800',
                    'https://picsum.photos/seed/mwtt-remote-2/800/800',
                ],
                'start_date' => now()->addDays(20)->toDateString(),
                'start_time' => '18:00',
                'end_time' => '19:30',
                'venue' => null,
                'city' => 'Online',
                'state' => 'Lagos',
                'is_online' => true,
                'meeting_url' => 'https://meet.example.com/techies-career-clinic',
                'max_attendees' => 200,
                'registration_type' => 'free',
                'status' => 'published',
            ],
            [
                'slug' => 'product-design-workshop-abuja',
                'title' => 'Product Design Workshop: From Idea to Prototype',
                'category' => 'Workshops',
                'organizer' => $staff,
                'hub' => $hubs['abuja'],
                'chapter' => $chapters['abuja'],
                'host' => 'Abuja Innovation Lab',
                'description' => '<p>A hands-on Saturday workshop for designers and product people. You will leave with a clickable prototype and a clearer process for turning user research into decisions.</p><h2>Format</h2><p>Small groups, practical exercises and a critique session with practising designers.</p>',
                'agenda' => '<h3>Schedule</h3><ol><li>10:00 — Framing the problem</li><li>11:00 — Sketching and user flows</li><li>13:00 — Lunch</li><li>14:00 — Prototyping in Figma</li><li>15:30 — Critique and next steps</li></ol>',
                'requirements' => '<p>Bring a laptop with Figma installed. A free account is enough.</p>',
                'speakers' => "Amaka Nwosu\nZainab Yusuf",
                'speaker_images' => [
                    'https://picsum.photos/seed/mwtt-design-1/800/800',
                    'https://picsum.photos/seed/mwtt-design-2/800/800',
                ],
                'start_date' => now()->addDays(30)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '16:00',
                'venue' => 'Abuja Innovation Lab, Wuse II',
                'city' => 'Abuja',
                'state' => 'FCT',
                'is_online' => false,
                'max_attendees' => 30,
                'registration_type' => 'paid',
                'fee' => 7500,
                'payment_url' => 'https://paystack.example.com/techies-design-workshop',
                'payment_instructions' => 'Pay ₦7,500 through the payment link or transfer to Techies Community, Access Bank, 0123456789. Use your email as the reference and bring your receipt.',
                'status' => 'published',
            ],
            [
                'slug' => 'ai-safety-responsible-innovation',
                'title' => 'AI Safety and Responsible Innovation Panel',
                'category' => 'Conferences',
                'organizer' => $staff,
                'hub' => $hubs['lagos'],
                'chapter' => $chapters['lagos'],
                'host' => 'Meet with the Techies',
                'description' => '<p>Researchers, builders and policy practitioners discuss what responsible AI looks like in African products and public services.</p><p>The panel is followed by a structured networking session for people working on AI projects.</p>',
                'agenda' => '<p>Panel discussion, audience Q&amp;A and networking.</p>',
                'requirements' => '<p>No technical background required, but registration is mandatory.</p>',
                'speakers' => "Dr. Halima Sani\nIbrahim Musa\nFatima Bello",
                'speaker_images' => [
                    'https://picsum.photos/seed/mwtt-ai-1/800/800',
                    'https://picsum.photos/seed/mwtt-ai-2/800/800',
                    'https://picsum.photos/seed/mwtt-ai-3/800/800',
                ],
                'start_date' => now()->addDays(45)->toDateString(),
                'start_time' => '15:00',
                'end_time' => '18:00',
                'venue' => 'Lagos Builders Hub, Yaba',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'is_online' => false,
                'max_attendees' => 120,
                'registration_type' => 'free',
                'status' => 'published',
            ],
            [
                'slug' => 'community-showcase-2025',
                'title' => 'Community Showcase 2025',
                'category' => 'Community Events',
                'organizer' => $staff,
                'hub' => $hubs['lagos'],
                'chapter' => $chapters['lagos'],
                'host' => 'Meet with the Techies',
                'description' => '<p>A look back at the projects, people and partnerships that shaped the community last year.</p><p>Thank you to everyone who demoed, volunteered and showed up.</p>',
                'agenda' => '<p>Project demos, community awards and a closing networking session.</p>',
                'requirements' => null,
                'speakers' => "Adaeze Okafor\nEmeka Obi",
                'speaker_images' => [
                    'https://picsum.photos/seed/mwtt-showcase-1/800/800',
                ],
                'start_date' => now()->subDays(30)->toDateString(),
                'start_time' => '16:00',
                'end_time' => '20:00',
                'venue' => 'Yaba Technology Hub',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'is_online' => false,
                'max_attendees' => 150,
                'registration_type' => 'free',
                'status' => 'published',
            ],
            [
                'slug' => 'lagos-builders-hackathon',
                'title' => 'Lagos Builders Hackathon',
                'category' => 'Hackathons',
                'organizer' => $hubs['lagos']->owner,
                'hub' => $hubs['lagos'],
                'chapter' => $chapters['lagos'],
                'host' => 'Lagos Builders Hub',
                'description' => '<p>A two-day hackathon for teams building tools that make local communities stronger.</p><p>This event is awaiting staff review before it is published.</p>',
                'agenda' => '<p>Day one: team formation and build. Day two: build, demos and judging.</p>',
                'requirements' => '<p>Teams of two to four. Bring your own laptop and charger.</p>',
                'speakers' => 'Bola Martins',
                'speaker_images' => [
                    'https://picsum.photos/seed/mwtt-hackathon-1/800/800',
                ],
                'start_date' => now()->addDays(60)->toDateString(),
                'start_time' => '09:00',
                'end_time' => '18:00',
                'venue' => 'Lagos Builders Hub',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'is_online' => false,
                'max_attendees' => 80,
                'registration_type' => 'free',
                'status' => 'draft',
            ],
            [
                'slug' => 'cloud-native-meetup-cancelled',
                'title' => 'Cloud Native Meetup: Cancelled',
                'category' => 'Meetups',
                'organizer' => $staff,
                'hub' => $hubs['ph'],
                'chapter' => $chapters['ph'],
                'host' => 'Port Harcourt Tech Circle',
                'description' => '<p>This meetup has been cancelled. We will announce a new date soon.</p>',
                'agenda' => null,
                'requirements' => null,
                'speakers' => 'Chiamaka Eze',
                'speaker_images' => [],
                'start_date' => now()->addDays(15)->toDateString(),
                'start_time' => '17:00',
                'end_time' => '19:00',
                'venue' => 'Port Harcourt Tech Circle',
                'city' => 'Port Harcourt',
                'state' => 'Rivers',
                'is_online' => false,
                'max_attendees' => 50,
                'registration_type' => 'free',
                'status' => 'cancelled',
            ],
            [
                'slug' => 'cybersecurity-career-roundtable',
                'title' => 'Cybersecurity Career Roundtable',
                'category' => 'Career Events',
                'organizer' => $staff,
                'hub' => $hubs['abuja'],
                'chapter' => $chapters['abuja'],
                'host' => 'Abuja Innovation Lab',
                'description' => '<p>A small roundtable for people moving into security roles. We will review paths into SOC, GRC and application security work.</p>',
                'agenda' => '<p>Introductions, three short career stories and open questions.</p>',
                'requirements' => '<p>Places are limited to keep the conversation useful.</p>',
                'speakers' => "Fatima Bello\nIbrahim Musa",
                'speaker_images' => [
                    'https://picsum.photos/seed/mwtt-security-1/800/800',
                ],
                'start_date' => now()->addDays(8)->toDateString(),
                'start_time' => '16:00',
                'end_time' => '18:00',
                'venue' => 'Abuja Innovation Lab',
                'city' => 'Abuja',
                'state' => 'FCT',
                'is_online' => false,
                'max_attendees' => 2,
                'registration_type' => 'free',
                'status' => 'published',
            ],
            [
                'slug' => 'monthly-career-clinic',
                'title' => 'Monthly Career Clinic',
                'category' => 'Career Clinics',
                'organizer' => $staff,
                'hub' => $hubs['lagos'],
                'chapter' => null,
                'host' => 'Meet with the Techies',
                'description' => '<p>A member-only clinic for CV reviews, LinkedIn feedback, mock interviews and salary conversations.</p><p>Bring one challenge you want help with and leave with a clear next step.</p>',
                'agenda' => '<p>Small-group reviews followed by open questions.</p>',
                'requirements' => '<p>Register to receive the meeting link.</p>',
                'speakers' => "Ngozi Achebe\nAdaeze Okafor",
                'speaker_images' => [],
                'start_date' => now()->addDays(10)->toDateString(),
                'start_time' => '18:00',
                'end_time' => '19:30',
                'venue' => null,
                'city' => 'Online',
                'state' => 'Lagos',
                'is_online' => true,
                'meeting_url' => 'https://meet.example.com/member-career-clinic',
                'max_attendees' => 40,
                'registration_type' => 'free',
                'status' => 'published',
            ],
            [
                'slug' => 'member-office-hours',
                'title' => 'Member Office Hours',
                'category' => 'Office Hours',
                'organizer' => $staff,
                'hub' => null,
                'chapter' => null,
                'host' => 'Meet with the Techies',
                'description' => '<p>An open room with staff and senior members. Bring one question about your career, project or community idea.</p>',
                'agenda' => '<p>Open questions, no slides.</p>',
                'requirements' => null,
                'speakers' => 'Ngozi Achebe',
                'speaker_images' => [],
                'start_date' => now()->addDays(5)->toDateString(),
                'start_time' => '17:00',
                'end_time' => '18:00',
                'venue' => null,
                'city' => 'Online',
                'state' => 'Lagos',
                'is_online' => true,
                'meeting_url' => 'https://meet.example.com/member-office-hours',
                'max_attendees' => 30,
                'registration_type' => 'free',
                'status' => 'published',
            ],
        ];

        $events = [];

        foreach ($seeds as $seed) {
            $event = Event::updateOrCreate(['slug' => $seed['slug']], [
                'title' => $seed['title'],
                'description' => $seed['description'],
                'cover_image' => 'https://picsum.photos/seed/'.$seed['slug'].'/1600/900',
                'category_id' => EventCategory::where('name', $seed['category'])->value('id'),
                'organizer_id' => $seed['organizer']->id,
                'host' => $seed['host'],
                'key_speakers' => $seed['speakers'],
                'speaker_images' => $seed['speaker_images'],
                'agenda' => $seed['agenda'],
                'requirements' => $seed['requirements'],
                'contact_email' => 'events@meettechies.ng',
                'contact_phone' => '+234 800 000 0003',
                'hub_id' => $seed['hub']?->id,
                'chapter_id' => $seed['chapter']?->id,
                'start_date' => $seed['start_date'],
                'end_date' => $seed['start_date'],
                'start_time' => $seed['start_time'],
                'end_time' => $seed['end_time'],
                'venue' => $seed['venue'],
                'city' => $seed['city'],
                'state' => $seed['state'],
                'country' => 'Nigeria',
                'is_online' => $seed['is_online'] ?? false,
                'meeting_url' => $seed['meeting_url'] ?? null,
                'registration_deadline' => now()->parse($seed['start_date'])->subDay()->toDateString(),
                'max_attendees' => $seed['max_attendees'] ?? null,
                'registration_type' => $seed['registration_type'],
                'fee' => $seed['fee'] ?? null,
                'payment_url' => $seed['payment_url'] ?? null,
                'payment_instructions' => $seed['payment_instructions'] ?? null,
                'status' => $seed['status'],
            ]);

            $events[$seed['slug']] = $event;
        }

        return $events;
    }

    /**
     * @param  array<string, Event>  $events
     * @param  array<int, User>  $members
     */
    private function seedRegistrations(array $events, array $members): void
    {
        $attendance = [
            'lagos-frontend-meetup' => [
                'registered' => [$members[0], $members[1], $members[2], $members[5], $members[7], $members[8], $members[11]],
            ],
            'remote-work-career-clinic' => [
                'registered' => [$members[2], $members[3], $members[7], $members[10], $members[13]],
            ],
            'product-design-workshop-abuja' => [
                'registered' => [$members[2], $members[3], $members[5], $members[8]],
                'paid' => [$members[2], $members[3]],
            ],
            'ai-safety-responsible-innovation' => [
                'registered' => [$members[1], $members[3], $members[6], $members[8], $members[9], $members[12]],
            ],
            'community-showcase-2025' => [
                'registered' => [$members[0], $members[1], $members[2], $members[4], $members[5], $members[7], $members[8], $members[11]],
                'checked_in' => [$members[0], $members[1], $members[2], $members[4], $members[7]],
                'reminded' => [$members[0], $members[1], $members[2], $members[4], $members[5], $members[7], $members[8], $members[11]],
            ],
            'cybersecurity-career-roundtable' => [
                'registered' => [$members[6], $members[12]],
                'waitlisted' => [$members[3], $members[9]],
                'cancelled' => [$members[10]],
            ],
        ];

        foreach ($attendance as $slug => $groups) {
            $event = $events[$slug];
            $isPaid = $event->registration_type === 'paid';

            foreach ($groups['registered'] ?? [] as $user) {
                $paid = in_array($user, $groups['paid'] ?? [], true);

                EventRegistration::updateOrCreate(
                    ['event_id' => $event->id, 'user_id' => $user->id],
                    [
                        'status' => EventRegistration::STATUS_REGISTERED,
                        'payment_status' => $isPaid
                            ? ($paid ? EventRegistration::PAYMENT_PAID : EventRegistration::PAYMENT_UNPAID)
                            : EventRegistration::PAYMENT_NOT_REQUIRED,
                        'payment_reference' => $isPaid && $paid ? 'MWTT-'.strtoupper(Str::random(8)) : null,
                        'paid_at' => $isPaid && $paid ? now()->subDays(3) : null,
                        'checked_in_at' => in_array($user, $groups['checked_in'] ?? [], true)
                            ? now()->parse($event->start_date)->setTime(16, 30)
                            : null,
                        'reminded_at' => in_array($user, $groups['reminded'] ?? [], true)
                            ? now()->subDays(1)
                            : null,
                    ]
                );
            }

            foreach ($groups['waitlisted'] ?? [] as $user) {
                EventRegistration::updateOrCreate(
                    ['event_id' => $event->id, 'user_id' => $user->id],
                    [
                        'status' => EventRegistration::STATUS_WAITLISTED,
                        'payment_status' => EventRegistration::PAYMENT_NOT_REQUIRED,
                        'waitlisted_at' => now()->subDays(2),
                    ]
                );
            }

            foreach ($groups['cancelled'] ?? [] as $user) {
                EventRegistration::updateOrCreate(
                    ['event_id' => $event->id, 'user_id' => $user->id],
                    [
                        'status' => EventRegistration::STATUS_CANCELLED,
                        'payment_status' => EventRegistration::PAYMENT_NOT_REQUIRED,
                    ]
                );
            }
        }
    }

    /**
     * @param  array<string, Event>  $events
     * @param  array<int, User>  $members
     */
    private function seedSpeakerCalls(array $events, array $members, User $staff): void
    {
        $seeds = [
            [
                'slug' => 'call-for-speakers-lagos-frontend-meetup',
                'title' => 'Call for speakers: Lagos Frontend Meetup',
                'description' => '<p>We are looking for practical frontend talks for our next Lagos meetup. First-time speakers are welcome, and we will help you shape the session.</p><h2>What we are looking for</h2><ul><li>Real lessons from building interfaces</li><li>Live demos or code walkthroughs</li><li>Talks that send people home with something they can try</li></ul><p>Sessions run for 20 minutes plus questions.</p>',
                'topics' => ['Frontend performance', 'Design systems', 'Accessibility', 'Career growth'],
                'event' => $events['lagos-frontend-meetup'],
                'opens_at' => now()->subDays(5)->toDateString(),
                'closes_at' => now()->addDays(7)->toDateString(),
                'status' => SpeakerCall::STATUS_OPEN,
            ],
            [
                'slug' => 'call-for-speakers-ai-safety',
                'title' => 'Call for speakers: AI Safety and Responsible Innovation',
                'description' => '<p>We are inviting researchers, builders and policy practitioners to propose short talks for our AI safety panel and workshop track.</p><p>We are especially interested in examples from African products, public services and community projects.</p>',
                'topics' => ['Responsible AI', 'AI policy', 'Building AI products', 'AI and community impact'],
                'event' => $events['ai-safety-responsible-innovation'],
                'opens_at' => now()->subDays(2)->toDateString(),
                'closes_at' => now()->addDays(20)->toDateString(),
                'status' => SpeakerCall::STATUS_OPEN,
            ],
            [
                'slug' => 'call-for-speakers-community-showcase',
                'title' => 'Call for speakers: Community Showcase',
                'description' => '<p>Our community showcase is a chance to share what you have built, learned or contributed over the year.</p><p>This call has closed. Thank you to everyone who submitted.</p>',
                'topics' => ['Community stories', 'Open source', 'Career journeys'],
                'event' => $events['community-showcase-2025'],
                'opens_at' => now()->subDays(60)->toDateString(),
                'closes_at' => now()->subDays(10)->toDateString(),
                'status' => SpeakerCall::STATUS_CLOSED,
            ],
        ];

        $calls = [];

        foreach ($seeds as $seed) {
            $event = $seed['event'];

            $call = SpeakerCall::updateOrCreate(['slug' => $seed['slug']], [
                'title' => $seed['title'],
                'description' => $seed['description'],
                'topics' => $seed['topics'],
                'event_id' => $event->id,
                'hub_id' => $event->hub_id,
                'chapter_id' => $event->chapter_id,
                'created_by' => $staff->id,
                'opens_at' => $seed['opens_at'],
                'closes_at' => $seed['closes_at'],
                'status' => $seed['status'],
            ]);

            $calls[$seed['slug']] = $call;
        }

        $submissions = [
            'call-for-speakers-lagos-frontend-meetup' => [
                [$members[0], 'Frontend performance', 'Making interfaces fast on real networks', 'A practical walkthrough of the performance work that mattered most in a production dashboard, including what we measured and what we removed.', 'Senior frontend engineer at Kite Labs. I build accessible product interfaces and enjoy helping newcomers ship their first feature.', SpeakerSubmission::STATUS_APPROVED, null],
                [$members[1], 'Design systems', 'A design system that survives real product work', 'Lessons from maintaining a component library across three product teams, with a candid look at the parts that did not work.', 'Backend engineer who spends a lot of time at the boundary between APIs and interfaces.', SpeakerSubmission::STATUS_PENDING, null],
                [$members[5], 'Career growth', 'From tutorials to your first production feature', 'A short talk for early-career developers on turning tutorial projects into work that teams can review and ship.', 'Mobile developer in Ibadan and a community mentor.', SpeakerSubmission::STATUS_REJECTED, 'Strong idea, but the programme is already full for this meetup. Please submit again for the next call.'],
            ],
            'call-for-speakers-ai-safety' => [
                [$members[3], 'AI policy', 'What African AI policy needs from builders', 'A practical view of the policy conversations that affect product teams, and how builders can contribute without waiting for regulation to land.', 'Data analyst working on public-interest data projects in Abuja.', SpeakerSubmission::STATUS_APPROVED, null],
                [$members[6], 'Responsible AI', 'Threat modelling for AI features', 'A security-led approach to reviewing AI features before launch, with questions every team should ask about data, access and failure modes.', 'Cybersecurity analyst focused on application security and risk.', SpeakerSubmission::STATUS_PENDING, null],
            ],
            'call-for-speakers-community-showcase' => [
                [$members[7], 'Community stories', 'What a small study group taught us about consistency', 'A story about a chapter study group that kept showing up, what changed and what other chapters can copy.', 'Full-stack developer and community volunteer in Enugu.', SpeakerSubmission::STATUS_APPROVED, null],
                [$members[2], 'Open source', 'Designing in public without burning out', 'A candid look at maintaining an open-source design resource while working full time.', 'Product designer and community mentor.', SpeakerSubmission::STATUS_REJECTED, 'The showcase programme is now full. Thank you for the thoughtful proposal.'],
            ],
        ];

        foreach ($submissions as $slug => $rows) {
            $call = $calls[$slug];

            foreach ($rows as [$user, $topic, $title, $abstract, $bio, $status, $notes]) {
                SpeakerSubmission::updateOrCreate(
                    ['speaker_call_id' => $call->id, 'user_id' => $user->id],
                    [
                        'topic' => $topic,
                        'title' => $title,
                        'abstract' => $abstract,
                        'bio' => $bio,
                        'status' => $status,
                        'review_notes' => $notes,
                        'reviewed_by' => $status === SpeakerSubmission::STATUS_PENDING ? null : $staff->id,
                        'reviewed_at' => $status === SpeakerSubmission::STATUS_PENDING ? null : now()->subDays(2),
                    ]
                );
            }
        }
    }

    /**
     * @param  array<int, User>  $members
     * @param  array<string, Hub>  $hubs
     */
    private function seedOpportunities(User $staff, array $members, array $hubs): void
    {
        $seeds = [
            [
                'slug' => 'frontend-engineer-lagos',
                'title' => 'Frontend Engineer',
                'category' => 'Jobs',
                'organization' => 'Kite Labs',
                'hub' => $hubs['lagos'],
                'location' => 'Lagos, Nigeria',
                'remote' => false,
                'member_only' => false,
                'deadline' => now()->addDays(25)->toDateString(),
                'description' => '<p>Kite Labs is looking for a frontend engineer to help build fast, accessible product experiences for small businesses.</p><h2>You will</h2><ul><li>Ship features across our customer and admin surfaces</li><li>Work with designers to turn prototypes into production UI</li><li>Improve performance, accessibility and test coverage</li></ul><h2>We look for</h2><p>Strong JavaScript fundamentals, experience with a modern framework and clear written communication.</p>',
                'eligibility' => '<p>Two or more years of professional frontend experience. Candidates based in Lagos or willing to work hybrid from Yaba are preferred.</p>',
                'status' => 'published',
                'submitted_by' => $staff,
            ],
            [
                'slug' => 'backend-engineer-remote',
                'title' => 'Backend Engineer (Remote)',
                'category' => 'Jobs',
                'organization' => 'Northstar Systems',
                'hub' => $hubs['lagos'],
                'location' => 'Remote',
                'remote' => true,
                'member_only' => true,
                'deadline' => now()->addDays(40)->toDateString(),
                'description' => '<p>Northstar Systems builds infrastructure for community finance teams. We are hiring a backend engineer to own services from design through operation.</p><h2>The work</h2><ul><li>Design and ship APIs used by partner organisations</li><li>Improve reliability, observability and deployment safety</li><li>Mentor engineers in a small, distributed team</li></ul>',
                'eligibility' => '<p>Professional experience with PHP or Go, relational databases and production incident response. This role is reserved for verified members.</p>',
                'status' => 'published',
                'submitted_by' => $staff,
            ],
            [
                'slug' => 'product-design-internship',
                'title' => 'Product Design Intern',
                'category' => 'Internships',
                'organization' => 'Abuja Innovation Lab',
                'hub' => $hubs['abuja'],
                'location' => 'Abuja, Nigeria',
                'remote' => false,
                'member_only' => false,
                'deadline' => now()->addDays(15)->toDateString(),
                'description' => '<p>A six-month paid internship for a designer who wants to work on public-interest digital services.</p><p>You will join a small product team, run research sessions and help ship real features.</p>',
                'eligibility' => '<p>Final-year students and recent graduates are welcome. A portfolio with at least two case studies is required.</p>',
                'status' => 'published',
                'submitted_by' => $staff,
            ],
            [
                'slug' => 'women-in-tech-scholarship',
                'title' => 'Women in Tech Scholarship 2026',
                'category' => 'Scholarships',
                'organization' => 'Techies Foundation',
                'hub' => null,
                'location' => 'Remote',
                'remote' => true,
                'member_only' => false,
                'application_link' => 'https://example.com/apply/women-in-tech-2026',
                'deadline' => now()->addDays(50)->toDateString(),
                'description' => '<p>The Techies Foundation scholarship covers fees, a laptop stipend and mentorship for women starting careers in technology.</p><h2>What is included</h2><ul><li>Up to ₦250,000 towards tuition or training</li><li>A six-month mentorship pairing</li><li>Community membership and career support</li></ul>',
                'eligibility' => '<p>Open to women based in Nigeria who are enrolled in, or applying to, a recognised technology programme. Applicants should demonstrate financial need and a clear learning plan.</p>',
                'status' => 'published',
                'submitted_by' => $staff,
            ],
            [
                'slug' => 'cloud-devops-fellowship',
                'title' => 'Cloud & DevOps Fellowship',
                'category' => 'Fellowships',
                'organization' => 'Port Harcourt Tech Circle',
                'hub' => $hubs['ph'],
                'location' => 'Port Harcourt, Nigeria',
                'remote' => true,
                'member_only' => false,
                'deadline' => now()->addDays(35)->toDateString(),
                'description' => '<p>A twelve-week fellowship for engineers moving into cloud and platform work. Fellows learn in public, ship a capstone and receive weekly coaching.</p>',
                'eligibility' => '<p>Comfort with Linux and one programming language. Fellowship sessions run on Saturday mornings.</p>',
                'status' => 'published',
                'submitted_by' => $staff,
            ],
            [
                'slug' => 'community-manager-draft',
                'title' => 'Community Manager',
                'category' => 'Jobs',
                'organization' => 'Enugu Campus Innovation',
                'hub' => $hubs['enugu'],
                'location' => 'Enugu, Nigeria',
                'remote' => false,
                'member_only' => false,
                'deadline' => now()->addDays(21)->toDateString(),
                'description' => '<p>We are looking for a community manager to run campus programmes and support student chapters.</p><p>This listing is awaiting staff review.</p>',
                'eligibility' => '<p>Experience organising events or student communities.</p>',
                'status' => 'draft',
                'submitted_by' => $hubs['enugu']->owner,
            ],
            [
                'slug' => 'data-analyst-remote',
                'title' => 'Data Analyst (Remote)',
                'category' => 'Jobs',
                'organization' => 'Kano Data Collective',
                'hub' => $hubs['kano'],
                'location' => 'Remote',
                'remote' => true,
                'member_only' => false,
                'deadline' => now()->subDays(3)->toDateString(),
                'description' => '<p>Support a public-health research team with cleaning, analysis and reporting.</p>',
                'eligibility' => '<p>Experience with SQL and a spreadsheet or BI tool.</p>',
                'status' => 'published',
                'submitted_by' => $staff,
            ],
        ];

        $opportunities = [];

        foreach ($seeds as $seed) {
            $opportunity = Opportunity::updateOrCreate(['slug' => $seed['slug']], [
                'title' => $seed['title'],
                'description' => $seed['description'],
                'category_id' => OpportunityCategory::where('name', $seed['category'])->value('id'),
                'organization' => $seed['organization'],
                'hub_id' => $seed['hub']?->id,
                'location' => $seed['location'],
                'is_remote' => $seed['remote'],
                'is_member_only' => $seed['member_only'],
                'application_link' => $seed['application_link'] ?? null,
                'deadline' => $seed['deadline'],
                'eligibility' => $seed['eligibility'],
                'cover_image' => 'https://picsum.photos/seed/'.$seed['slug'].'/1600/900',
                'status' => $seed['status'],
                'submitted_by' => $seed['submitted_by']->id,
            ]);

            $opportunities[$seed['slug']] = $opportunity;
        }

        $applications = [
            'frontend-engineer-lagos' => [
                [$members[0], 'accepted', 'I have shipped three production design systems and would love to bring that experience to Kite Labs.'],
                [$members[1], 'shortlisted', 'My last two roles were frontend-heavy and I enjoy performance work.'],
                [$members[8], 'reviewing', 'I work closely with frontend teams as a product manager and want to move closer to the craft.'],
                [$members[10], 'applied', 'I document component APIs and would like to contribute as a frontend engineer.'],
            ],
            'backend-engineer-remote' => [
                [$members[1], 'applied', 'I have six years of backend experience, mostly PHP and PostgreSQL.'],
                [$members[7], 'rejected', 'I am early in my backend journey but keen to learn.'],
            ],
            'product-design-internship' => [
                [$members[2], 'shortlisted', 'My portfolio includes two public-sector service redesigns.'],
                [$members[6], 'applied', 'I am moving from security analysis into product design and have completed two case studies.'],
            ],
            'women-in-tech-scholarship' => [
                [$members[3], 'accepted', 'I am enrolled in a data analytics programme and need support with fees and a laptop.'],
                [$members[5], 'reviewing', 'I want to formalise my mobile development skills with a structured programme.'],
            ],
            'cloud-devops-fellowship' => [
                [$members[4], 'accepted', 'I already run deployments at work and want to deepen my platform engineering skills.'],
                [$members[13], 'shortlisted', 'I have been studying Kubernetes and would benefit from the coaching.'],
            ],
        ];

        foreach ($applications as $slug => $rows) {
            $opportunity = $opportunities[$slug];

            foreach ($rows as $index => [$user, $status, $letter]) {
                OpportunityApplication::updateOrCreate(
                    ['opportunity_id' => $opportunity->id, 'user_id' => $user->id],
                    [
                        'cover_letter' => $letter,
                        'status' => $status,
                        'applied_at' => now()->subDays(10 - $index),
                    ]
                );
            }
        }
    }

    /**
     * @param  array<string, Chapter>  $chapters
     */
    private function seedAnnouncements(User $staff, array $chapters): void
    {
        $seeds = [
            [
                'title' => 'Techies Community Update — November',
                'chapter' => null,
                'published' => true,
                'published_at' => now()->subDays(2),
                'body' => '<p>Hello community. Here is a short update on what is happening across Meet with the Techies this month.</p><h2>What is new</h2><ul><li>New chapter pages now show coordinators and upcoming events.</li><li>Hub owners and chapter coordinators can submit events and opportunities for review.</li><li>Articles now support richer formatting and inline images.</li></ul><p>If you have something the community should know about, reply to this email or contact your chapter coordinator.</p>',
            ],
            [
                'title' => 'Lagos Chapter: End-of-Year Meetup',
                'chapter' => $chapters['lagos'],
                'published' => true,
                'published_at' => now()->subDays(5),
                'body' => '<p>Our last Lagos meetup of the year is taking shape. Expect short talks, a community showcase and plenty of time to reconnect.</p><p>Registration opens soon. Watch the events page for the date and venue.</p>',
            ],
            [
                'title' => 'Applications Open for the 2026 Mentorship Cohort',
                'chapter' => null,
                'published' => true,
                'published_at' => now()->subDays(9),
                'body' => '<p>The 2026 mentorship cohort is now accepting applications from members who want a structured six-month pairing.</p><h2>Who it is for</h2><p>Early-career members who can commit to two hours a month and want help with a specific career goal.</p><p><a href="https://example.com/mentorship">Read the programme details and apply</a>.</p>',
            ],
            [
                'title' => 'Draft: Volunteer Call for Community Events',
                'chapter' => null,
                'published' => false,
                'published_at' => null,
                'body' => '<p>We are preparing a call for volunteers to support event check-in, photography and social coverage.</p><p>This draft is not visible to members yet.</p>',
            ],
        ];

        foreach ($seeds as $seed) {
            Announcement::updateOrCreate(['title' => $seed['title']], [
                'body' => $seed['body'],
                'author_id' => $staff->id,
                'chapter_id' => $seed['chapter']?->id,
                'is_published' => $seed['published'],
                'published_at' => $seed['published_at'],
            ]);
        }
    }

    private function seedNewsletter(Generator $faker): void
    {
        $names = [
            'Amina Bello', 'Chuka Nnamdi', 'Funke Adebayo', 'Musa Danjuma', 'Rita Eze',
            'Kunle Ajayi', 'Safiya Ibrahim', 'Tobi Oladipo', 'Grace Udo', 'Yusuf Ali',
            'Ifeoma Nwankwo', 'Daniel Osei',
        ];

        foreach ($names as $index => $name) {
            NewsletterSubscriber::updateOrCreate(['email' => 'subscriber'.$index.'@example.com'], [
                'name' => $name,
            ]);
        }
    }

    private function seedEmailTemplates(User $staff): void
    {
        $templates = [
            [
                'name' => 'Community welcome',
                'subject' => 'Welcome to {{site_name}}',
                'body' => '<p>Hi {{name}},</p><p>Welcome to {{site_name}}. Complete your profile, join a chapter and introduce yourself in the community.</p><p><a href="{{site_url}}/dashboard">Open your dashboard</a></p>',
            ],
            [
                'name' => 'Event reminder',
                'subject' => 'Reminder: {{event_title}}',
                'body' => '<p>Hi {{name}},</p><p>This is a reminder that <strong>{{event_title}}</strong> starts on {{event_date}}.</p><p>We look forward to seeing you there.</p>',
            ],
            [
                'name' => 'Opportunity digest',
                'subject' => 'New opportunities for the {{site_name}} community',
                'body' => '<p>Hi {{name}},</p><p>There are new opportunities on the platform this week. Visit <a href="{{site_url}}/opportunities">the opportunities page</a> to see the details and apply.</p>',
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(['name' => $template['name']], [
                'subject' => $template['subject'],
                'body' => $template['body'],
                'created_by' => $staff->id,
            ]);
        }
    }

    /**
     * @param  array<string, Hub>  $hubs
     * @param  array<int, User>  $members
     */
    private function seedHubTeams(array $hubs, array $members): void
    {
        $teams = [
            'lagos' => [0, 1, 8],
            'abuja' => [3, 6],
            'ph' => [4, 13],
            'ibadan' => [5],
        ];

        foreach ($teams as $key => $indexes) {
            foreach ($indexes as $position => $index) {
                DB::table('hub_team_members')->updateOrInsert(
                    ['hub_id' => $hubs[$key]->id, 'user_id' => $members[$index]->id],
                    [
                        'role' => $position === 0 ? 'organizer' : 'editor',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * @param  array<int, User>  $members
     */
    private function seedCommunityExtras(array $members): void
    {
        $availability = [
            0 => ['open_to_work' => true, 'open_to_mentor' => true, 'available_for_speaking' => true],
            1 => ['open_to_work' => true, 'open_to_collaborate' => true],
            2 => ['open_to_mentor' => true, 'available_for_speaking' => true],
            3 => ['open_to_work' => true],
            4 => ['open_to_collaborate' => true],
            5 => ['open_to_work' => true, 'open_to_collaborate' => true],
            8 => ['hiring' => true, 'available_for_speaking' => true],
        ];

        foreach ($availability as $index => $flags) {
            $members[$index]->profile?->update($flags);
        }

        $cohorts = [
            ['title' => 'Frontend Build Sprint', 'track' => 'Frontend', 'status' => Cohort::STATUS_OPEN, 'starts_at' => now()->addDays(14), 'ends_at' => now()->addDays(45), 'capacity' => 24, 'description' => '<p>A four-week sprint for frontend developers. Ship a real feature, get peer review and present at demo day.</p>'],
            ['title' => 'Data Storytelling Cohort', 'track' => 'Data', 'status' => Cohort::STATUS_OPEN, 'starts_at' => now()->addDays(21), 'ends_at' => now()->addDays(56), 'capacity' => 20, 'description' => '<p>Learn to turn a dataset into a clear story with weekly exercises and a final presentation.</p>'],
        ];

        foreach ($cohorts as $seed) {
            $cohort = Cohort::updateOrCreate(['title' => $seed['title']], [
                'slug' => Str::slug($seed['title']),
                'description' => $seed['description'],
                'track' => $seed['track'],
                'starts_at' => $seed['starts_at']->toDateString(),
                'ends_at' => $seed['ends_at']->toDateString(),
                'capacity' => $seed['capacity'],
                'status' => $seed['status'],
                'created_by' => $members[0]->id,
            ]);

            $cohort->members()->syncWithoutDetaching(collect($members)->slice(0, 4)->pluck('id')->all());
        }

        foreach ([
            ['Lagos Transit Guide', 'A lightweight guide to navigating Lagos with real-time transport notes.', 'A community-built transit guide with route notes, fares and accessibility details.', $members[5], ShowcaseProject::STATUS_APPROVED, true],
            ['Community Study Tracker', 'A small tool for study groups to track sessions and shared resources.', 'Built with Laravel and Alpine, this started as a chapter study group tool.', $members[7], ShowcaseProject::STATUS_APPROVED, false],
            ['Payroll Calculator for Small Teams', 'A simple payroll estimate tool for small Nigerian teams.', 'A calculator that helps founders estimate take-home pay and deductions.', $members[1], ShowcaseProject::STATUS_PENDING, false],
        ] as [$title, $summary, $description, $user, $status, $featured]) {
            ShowcaseProject::updateOrCreate(['slug' => Str::slug($title)], [
                'user_id' => $user->id,
                'title' => $title,
                'summary' => $summary,
                'description' => $description,
                'url' => 'https://example.com/'.Str::slug($title),
                'image' => 'https://picsum.photos/seed/'.Str::slug($title).'/1200/700',
                'stack' => ['Laravel', 'Tailwind', 'MySQL'],
                'looking_for_collaborators' => true,
                'status' => $status,
                'is_featured' => $featured,
            ]);
        }

        IntroRequest::updateOrCreate([
            'requester_id' => $members[10]->id,
            'purpose' => 'Advice on moving into product design',
        ], [
            'target_user_id' => $members[2]->id,
            'status' => IntroRequest::STATUS_PENDING,
        ]);

        $opportunity = Opportunity::where('slug', 'frontend-engineer-lagos')->first();

        if ($opportunity) {
            $offer = ReferralOffer::updateOrCreate([
                'user_id' => $members[0]->id,
                'opportunity_id' => $opportunity->id,
            ], [
                'note' => 'I work with this team and can refer strong frontend candidates.',
                'status' => 'open',
            ]);

            ReferralRequest::updateOrCreate([
                'referral_offer_id' => $offer->id,
                'requester_id' => $members[5]->id,
            ], [
                'message' => 'I have three years of frontend experience and would love a referral.',
                'status' => 'pending',
            ]);
        }

        $post = LoungePost::updateOrCreate(['title' => 'What are you building this month?'], [
            'user_id' => $members[8]->id,
            'category' => 'General',
            'body' => 'Share what you are working on. A sentence is enough. Let us see if anyone can help.',
            'is_pinned' => true,
        ]);

        LoungeReply::updateOrCreate([
            'lounge_post_id' => $post->id,
            'user_id' => $members[0]->id,
        ], [
            'body' => 'I am rebuilding our design system and documenting the decisions. Happy to share notes.',
        ]);

        $grant = Grant::updateOrCreate(['slug' => 'community-build-grant-2026'], [
            'title' => 'Community Build Grant 2026',
            'description' => '<p>Small grants for member projects that make the community stronger. We fund tools, events and open resources.</p><p>Grants range from ₦50,000 to ₦150,000.</p>',
            'amount' => 150000,
            'opens_at' => now()->subDays(5)->toDateString(),
            'closes_at' => now()->addDays(25)->toDateString(),
            'status' => Grant::STATUS_OPEN,
            'created_by' => $members[0]->id,
        ]);

        GrantApplication::updateOrCreate([
            'grant_id' => $grant->id,
            'user_id' => $members[7]->id,
        ], [
            'project_title' => 'Chapter study tracker',
            'summary' => 'A shared tool for study groups to run sessions and keep resources in one place.',
            'budget_breakdown' => 'Hosting, design support and a small stipend for two contributors.',
            'status' => 'pending',
        ]);

        MemberSpotlight::updateOrCreate([
            'user_id' => $members[0]->id,
            'month' => now()->format('Y-m'),
        ], [
            'quote' => 'The community gave me the confidence to speak and the feedback to get better.',
            'achievement' => 'Spoke at the Lagos Frontend Meetup and mentored two members.',
            'is_published' => true,
            'created_by' => $members[0]->id,
        ]);

        foreach (['Co-working day pass' => 'MWTT-HUB-20', 'Course discount' => 'MWTT-LEARN-20'] as $title => $code) {
            MemberBenefit::where('title', $title)->update([
                'redemption_code' => $code,
                'redemption_instructions' => 'Show this code to the partner. One redemption per member.',
            ]);
        }
    }

    /**
     * @param  array<int, User>  $members
     */
    private function seedMemberExperience(array $members): void
    {
        $benefits = [
            ['Co-working day pass', 'Lagos Builders Hub', 'A free day pass at the hub for focused work and community meetups.', '1 day pass', 'Hub perks', 'https://example.com/hub-day-pass'],
            ['Cloud credits', 'Northstar Systems', 'Starter cloud credits for a personal or community project.', '$100 credit', 'Tools', 'https://example.com/cloud-credits'],
            ['Course discount', 'Kite Academy', 'A member discount on practical frontend and backend courses.', '20% off', 'Learning', 'https://example.com/courses'],
            ['CV and LinkedIn review', 'MWTT career team', 'A 30-minute review with actionable feedback on your CV or LinkedIn profile.', 'Free review', 'Career support', null],
            ['Book bundle', 'Techies Library', 'A curated reading list and a discount on selected technology books.', '15% off', 'Learning', 'https://example.com/books'],
        ];

        foreach ($benefits as $index => [$title, $provider, $description, $label, $category, $url]) {
            MemberBenefit::updateOrCreate(['title' => $title], [
                'provider' => $provider,
                'description' => $description,
                'discount_label' => $label,
                'category' => $category,
                'url' => $url,
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }

        $mentor = MentorshipRequest::updateOrCreate(['user_id' => $members[0]->id], [
            'role' => MentorshipRequest::ROLE_MENTOR,
            'expertise' => 'Frontend engineering, design systems and career growth',
            'goals' => 'Help two early-career members ship their first production feature.',
            'availability' => 'Two evenings a month',
            'status' => MentorshipRequest::STATUS_PENDING,
        ]);

        $mentee = MentorshipRequest::updateOrCreate(['user_id' => $members[10]->id], [
            'role' => MentorshipRequest::ROLE_MENTEE,
            'expertise' => 'Technical writing and frontend',
            'goals' => 'Build a portfolio and prepare for a first frontend role.',
            'availability' => 'Weekday evenings',
            'status' => MentorshipRequest::STATUS_PENDING,
        ]);

        MentorshipRequest::updateOrCreate(['user_id' => $members[2]->id], [
            'role' => MentorshipRequest::ROLE_MENTOR,
            'expertise' => 'Product design and user research',
            'goals' => 'Mentor a designer moving into product work.',
            'availability' => 'Weekends',
            'status' => MentorshipRequest::STATUS_PENDING,
        ]);

        MentorshipRequest::updateOrCreate(['user_id' => $members[8]->id], [
            'role' => MentorshipRequest::ROLE_MENTEE,
            'expertise' => 'Product management',
            'goals' => 'Move from project coordination into product management.',
            'availability' => 'Flexible',
            'status' => MentorshipRequest::STATUS_MATCHED,
            'matched_with' => $members[2]->id,
            'matched_at' => now()->subDays(14),
        ]);

        MentorshipRequest::updateOrCreate(['user_id' => $members[2]->id], [
            'role' => MentorshipRequest::ROLE_MENTOR,
            'expertise' => 'Product design and user research',
            'goals' => 'Mentor a designer moving into product work.',
            'availability' => 'Weekends',
            'status' => MentorshipRequest::STATUS_MATCHED,
            'matched_with' => $members[8]->id,
            'matched_at' => now()->subDays(14),
        ]);

        $certificates = [
            [$members[0], 'speaker', 'Speaker at Lagos Frontend Meetup', 'Gave a practical talk on building fast interfaces.', 'MWTT-CERT-2026-A1B2C3'],
            [$members[7], 'attendance', 'Community Showcase attendance', 'Attended and participated in the community showcase.', 'MWTT-CERT-2026-D4E5F6'],
            [$members[2], 'mentorship', 'Mentorship cycle completion', 'Completed a three-month mentorship cycle as a mentor.', 'MWTT-CERT-2026-G7H8I9'],
        ];

        foreach ($certificates as [$user, $type, $title, $description, $number]) {
            Certificate::updateOrCreate(['certificate_number' => $number], [
                'user_id' => $user->id,
                'issued_by' => $members[0]->id,
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'issued_at' => now()->subDays(20),
            ]);
        }

        MemberFeedback::updateOrCreate(['user_id' => $members[1]->id], [
            'rating' => 5,
            'comments' => 'The events are practical and the people are generous with their time.',
            'submitted_at' => now()->subDays(5),
        ]);
    }

    /**
     * Two articles that exercise the rich-text renderer, alongside the plain
     * text examples created by ArticleSeeder.
     *
     * @param  array<int, User>  $members
     */
    private function seedRichArticles(array $members, User $superAdmin): void
    {
        $articles = [
            [
                'title' => 'Designing accessible interfaces for low-bandwidth users',
                'topic' => 'product',
                'author' => $members[2],
                'excerpt' => 'Practical decisions that keep product interfaces usable when connections are slow and devices are modest.',
                'keywords' => 'accessible design, performance, low bandwidth',
                'body' => '<p>Accessibility and performance are often treated as separate workstreams. In practice, the same decisions that help someone on a slow connection also help someone using a screen reader or an older device.</p><h2>Start with the critical path</h2><p>Identify the action a person came to complete, then remove everything that competes with it. Large hero images and autoplaying media are expensive before they are annoying.</p><ul><li>Serve compressed images with sensible dimensions.</li><li>Render useful content before loading secondary widgets.</li><li>Keep forms short and label every field clearly.</li></ul><blockquote><p>If the page only works on a fast connection, it does not work for most of the people who need it.</p></blockquote><h2>Test under real constraints</h2><p>Throttle the network, use a mid-range phone and try the interface with a keyboard. These small tests expose problems that a fast laptop hides.</p><p>The goal is not a perfect audit score. It is a product that respects the time and context of the person using it.</p>',
            ],
            [
                'title' => 'What I learned from running my first community workshop',
                'topic' => 'community',
                'author' => $members[10],
                'excerpt' => 'A honest account of planning, facilitating and following up on a first technical workshop.',
                'keywords' => 'community workshop, facilitation, learning',
                'body' => '<p>I had attended plenty of workshops before I agreed to run one. Facilitating is a different skill, and the first session taught me more than any slide deck could.</p><h2>Plan for the room, not the slides</h2><p>I spent hours polishing slides and almost no time thinking about what participants would do. The best moments came when people worked through a problem together.</p><ol><li>Open with a concrete task.</li><li>Keep explanations short and check understanding.</li><li>Leave room for people to help each other.</li></ol><h2>Follow up while it is fresh</h2><p>Send notes, links and a short feedback form within a day. Ask what people want to build next, not only whether they enjoyed the session.</p><p>Most importantly, thank the people who took a chance on the session. A workshop is a shared effort, even when one person stands at the front.</p>',
            ],
        ];

        foreach ($articles as $index => $seed) {
            Article::updateOrCreate(['slug' => Str::slug($seed['title'])], [
                'user_id' => $seed['author']->id,
                'title' => $seed['title'],
                'topic' => $seed['topic'],
                'excerpt' => $seed['excerpt'],
                'body' => $seed['body'],
                'cover_image' => 'https://picsum.photos/seed/'.Str::slug($seed['title']).'/1200/630',
                'status' => Article::STATUS_APPROVED,
                'seo_title' => $seed['title'],
                'seo_description' => Str::limit($seed['excerpt'], 155),
                'seo_keywords' => $seed['keywords'],
                'canonical_url' => 'https://example.com/articles/'.Str::slug($seed['title']),
                'views' => 140 + ($index * 63),
                'reviewed_by' => $superAdmin->id,
                'reviewed_at' => now()->subDays(3),
                'submitted_at' => now()->subDays(5),
                'published_at' => now()->subDays(2),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function profile(User $user, array $data): UserProfile
    {
        $profile = UserProfile::updateOrCreate(['user_id' => $user->id], $data);

        $profile->profile_completion = $profile->completionPercentage();
        $profile->save();

        return $profile;
    }
}
