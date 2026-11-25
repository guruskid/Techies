<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\BirthdayGreeting;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BirthdayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_members_can_save_their_date_of_birth(): void
    {
        $member = $this->member();

        $this->actingAs($member)->post(route('profile.update'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'date_of_birth' => '1995-11-25',
        ])->assertRedirect();

        $this->assertSame('1995-11-25', $member->fresh()->profile->date_of_birth->toDateString());
    }

    public function test_birthday_command_emails_only_members_with_a_birthday_today(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-11-25 08:00:00');

        $birthday = $this->member('birthday@example.com', '1995-11-25');
        $other = $this->member('other@example.com', '1995-11-26');

        $this->artisan('members:send-birthday-wishes')->assertSuccessful();

        Notification::assertSentTo($birthday, BirthdayGreeting::class);
        Notification::assertNotSentTo($other, BirthdayGreeting::class);
        $this->assertNotNull($birthday->fresh()->birthday_wished_at);
    }

    public function test_birthday_command_does_not_send_twice_in_the_same_year(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-11-25 08:00:00');

        $member = $this->member('birthday@example.com', '1995-11-25');

        $this->artisan('members:send-birthday-wishes')->assertSuccessful();
        $this->artisan('members:send-birthday-wishes')->assertSuccessful();

        Notification::assertSentTimes(BirthdayGreeting::class, 1);
    }

    public function test_members_can_opt_out_of_community_messages(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-11-25 08:00:00');

        $member = $this->member('birthday@example.com', '1995-11-25');
        $member->profile->update([
            'notification_preferences' => [
                'events' => true,
                'opportunities' => true,
                'applications' => true,
                'mentorship' => true,
                'membership' => true,
                'community' => false,
            ],
        ]);

        $this->artisan('members:send-birthday-wishes')->assertSuccessful();

        Notification::assertNotSentTo($member, BirthdayGreeting::class);
    }

    public function test_february_29_birthdays_are_greeted_on_february_28_in_non_leap_years(): void
    {
        Notification::fake();
        Carbon::setTestNow('2027-02-28 08:00:00');

        $member = $this->member('leap@example.com', '2000-02-29');

        $this->artisan('members:send-birthday-wishes')->assertSuccessful();

        Notification::assertSentTo($member, BirthdayGreeting::class);
    }

    public function test_date_of_birth_is_not_shown_on_the_public_profile(): void
    {
        $member = $this->member('private@example.com', '1995-11-25');

        $this->get($member->profile->publicUrl())
            ->assertOk()
            ->assertDontSee('1995-11-25')
            ->assertDontSee('Date of birth');
    }

    private function member(string $email = 'member@example.com', ?string $birthday = null): User
    {
        $member = User::factory()->create([
            'email' => $email,
            'role' => 'member',
            'status' => 'active',
            'membership_status' => 'active',
            'membership_paid_at' => now()->subMonth(),
        ]);

        UserProfile::create([
            'user_id' => $member->id,
            'username' => str()->slug(str()->before($email, '@')),
            'is_public' => true,
            'date_of_birth' => $birthday,
        ]);

        return $member->fresh('profile');
    }
}
