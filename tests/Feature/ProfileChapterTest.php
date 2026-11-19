<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileChapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_chapter_adds_it_without_removing_existing_memberships(): void
    {
        $user = User::factory()->create(['role' => 'member', 'status' => 'active']);
        UserProfile::create([
            'user_id' => $user->id,
            'username' => 'chapter-member',
            'is_public' => true,
        ]);

        $first = Chapter::create(['name' => 'Lagos Builders', 'slug' => 'lagos-builders', 'status' => 'active']);
        $second = Chapter::create(['name' => 'Abuja Builders', 'slug' => 'abuja-builders', 'status' => 'active']);

        $user->chapters()->attach($first->id);

        $this->actingAs($user)
            ->post('/profile', [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'chapter_id' => $second->id,
                'is_public' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($user->chapters()->whereKey($first->id)->exists());
        $this->assertTrue($user->chapters()->whereKey($second->id)->exists());
    }
}
