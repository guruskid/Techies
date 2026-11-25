<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\MembershipFeedbackRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_preferences_keep_in_app_notifications_enabled(): void
    {
        $member = User::factory()->create();
        UserProfile::create([
            'user_id' => $member->id,
            'notification_preferences' => ['membership' => false],
        ]);

        $this->assertSame(['database'], (new MembershipFeedbackRequest)->via($member->fresh('profile')));
    }

    public function test_members_cannot_open_another_members_notification(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $owner = User::factory()->create();
        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => MembershipFeedbackRequest::class,
            'data' => ['title' => 'Membership update', 'url' => '/search?q=member'],
        ]);

        $this->actingAs($member)
            ->get(route('notifications.open', $notification->id))
            ->assertNotFound();
    }
}
