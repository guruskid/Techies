<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberSubdomainTest extends TestCase
{
    use RefreshDatabase;

    private function member(string $username, bool $isPublic = true): User
    {
        $user = User::factory()->create([
            'name' => 'Ada Lovelace',
            'role' => 'member',
            'status' => 'active',
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'username' => $username,
            'first_name' => 'Ada',
            'is_public' => $isPublic,
        ]);

        return $user;
    }

    private function profileHost(string $username): string
    {
        return 'http://'.$username.'.'.config('techies.profile_domain').'/';
    }

    public function test_public_profile_is_available_on_username_subdomain(): void
    {
        $this->member('ada-lovelace');

        $this->get($this->profileHost('ada-lovelace'))
            ->assertOk()
            ->assertSee('Ada');
    }

    public function test_other_paths_on_a_profile_subdomain_redirect_to_the_main_site(): void
    {
        $this->member('ada-lovelace');

        $this->get($this->profileHost('ada-lovelace').'about')
            ->assertRedirect(rtrim(config('app.url'), '/').'/about');
    }

    public function test_www_subdomain_still_serves_the_main_site(): void
    {
        $this->get('http://www.'.config('techies.profile_domain').'/')
            ->assertOk()
            ->assertSee('Meet with the Techies');
    }

    public function test_private_profile_returns_not_found_on_subdomain(): void
    {
        $this->member('ada-private', false);

        $this->get($this->profileHost('ada-private'))->assertNotFound();
    }

    public function test_reserved_subdomain_is_not_found(): void
    {
        $this->get($this->profileHost('admin'))->assertNotFound();
    }

    public function test_member_can_choose_their_username(): void
    {
        $user = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($user)->post('/profile', [
            'username' => 'Grace-Hopper',
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'username' => 'grace-hopper',
        ]);
    }

    public function test_username_must_be_unique_and_not_reserved(): void
    {
        $this->member('taken-name');
        $user = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->actingAs($user)->post('/profile', ['username' => 'taken-name'])->assertSessionHasErrors('username');
        $this->actingAs($user)->post('/profile', ['username' => 'admin'])->assertSessionHasErrors('username');
        $this->actingAs($user)->post('/profile', ['username' => 'Bad Name!'])->assertSessionHasErrors('username');
    }

    public function test_public_profile_url_uses_the_subdomain(): void
    {
        $user = $this->member('ada-lovelace');
        $url = $user->profile->publicUrl();

        $this->assertSame(
            'ada-lovelace.'.config('techies.profile_domain'),
            parse_url($url, PHP_URL_HOST)
        );
        $this->assertSame('/', parse_url($url, PHP_URL_PATH) ?? '/');
    }

    public function test_profile_edit_page_includes_the_live_username_checker(): void
    {
        $user = $this->member('ada-lovelace');

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('username-availability')
            ->assertSee('Public profile address');
    }

    public function test_username_availability_endpoint_reports_status(): void
    {
        $this->member('taken-name');

        $user = User::factory()->create(['role' => 'member', 'status' => 'active']);
        UserProfile::create(['user_id' => $user->id, 'username' => 'my-name']);

        $this->actingAs($user)->getJson(route('profile.username.check', ['username' => 'free-name']))
            ->assertOk()
            ->assertJson(['available' => true]);

        $this->actingAs($user)->getJson(route('profile.username.check', ['username' => 'taken-name']))
            ->assertJson(['available' => false]);

        $this->actingAs($user)->getJson(route('profile.username.check', ['username' => 'my-name']))
            ->assertJson(['available' => true]);

        $this->actingAs($user)->getJson(route('profile.username.check', ['username' => 'admin']))
            ->assertJson(['available' => false]);

        $this->actingAs($user)->getJson(route('profile.username.check', ['username' => 'Bad Name']))
            ->assertJson(['available' => false]);
    }
}
