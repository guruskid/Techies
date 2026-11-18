<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_update_profile_and_social_links_are_kept(): void
    {
        $user = User::factory()->create();
        UserProfile::create([
            'user_id' => $user->id,
            'username' => 'testmember',
            'linkedin' => 'https://linkedin.com/in/testmember',
            'github' => 'https://github.com/testmember',
            'is_public' => false,
        ]);

        $response = $this->actingAs($user)->post('/profile', [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'headline' => 'Building developer communities',
            'professional_title' => 'Software Engineer',
            'linkedin' => 'https://linkedin.com/in/testmember',
            'github' => 'https://github.com/testmember',
            'facebook' => 'https://facebook.com/testmember',
            'likes' => 'Reading, chess',
            'certifications' => 'AWS Cloud Practitioner',
            'languages' => 'English, Igbo',
            'availability' => 'Open to mentoring',
            'is_public' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'linkedin' => 'https://linkedin.com/in/testmember',
            'github' => 'https://github.com/testmember',
            'facebook' => 'https://facebook.com/testmember',
            'likes' => 'Reading, chess',
            'certifications' => 'AWS Cloud Practitioner',
            'languages' => 'English, Igbo',
            'availability' => 'Open to mentoring',
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Ada Lovelace',
        ]);

        $this->assertGreaterThan(0, UserProfile::where('user_id', $user->id)->first()->profile_completion);
    }

    public function test_member_profile_page_only_returns_public_profiles_to_other_members(): void
    {
        $owner = User::factory()->create();
        UserProfile::create([
            'user_id' => $owner->id,
            'username' => 'private-member',
            'is_public' => false,
        ]);

        $visitor = User::factory()->create();

        $this->actingAs($visitor)->get('/members/private-member')->assertForbidden();
        $this->actingAs($owner)->get('/members/private-member')->assertOk();
    }
}
