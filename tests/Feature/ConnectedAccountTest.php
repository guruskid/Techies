<?php

namespace Tests\Feature;

use App\Models\ConnectedAccount;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ConnectedAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_members_can_start_a_github_connection(): void
    {
        $this->configureGithub();
        $member = $this->member();

        $response = $this->actingAs($member)->get(route('connections.redirect', 'github'));

        $response->assertRedirectContains('https://github.com/login/oauth/authorize');
        $this->assertNotEmpty(session('oauth_state.github'));
    }

    public function test_github_callback_stores_the_connected_account(): void
    {
        $this->configureGithub();
        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'github-token',
                'scope' => 'read:user user:email',
            ]),
            'https://api.github.com/user' => Http::response([
                'id' => 12345,
                'login' => 'octocat',
                'name' => 'Octo Cat',
                'email' => 'octo@example.com',
                'avatar_url' => 'https://example.com/avatar.png',
            ]),
        ]);

        $member = $this->member();

        $this->actingAs($member)
            ->withSession(['oauth_state.github' => 'test-state'])
            ->get(route('connections.callback', [
                'provider' => 'github',
                'state' => 'test-state',
                'code' => 'test-code',
            ]))
            ->assertRedirect(route('profile.edit').'#connections');

        $this->assertDatabaseHas('connected_accounts', [
            'user_id' => $member->id,
            'provider' => 'github',
            'provider_user_id' => '12345',
            'username' => 'octocat',
        ]);
    }

    /**
     * @param  array<string, mixed>  $tokenResponse
     * @param  array<string, mixed>  $profileResponse
     */
    #[DataProvider('providerCases')]
    public function test_other_providers_are_normalised(
        string $provider,
        string $tokenUrl,
        string $profileUrl,
        array $tokenResponse,
        array $profileResponse,
        string $expectedUserId,
        ?string $expectedUsername,
    ): void {
        config([
            'services.'.$provider.'.client_id' => $provider.'-client',
            'services.'.$provider.'.client_secret' => $provider.'-secret',
        ]);

        Http::fake([
            $tokenUrl => Http::response($tokenResponse),
            $profileUrl => Http::response($profileResponse),
        ]);

        $member = $this->member($provider.'@example.com');

        $this->actingAs($member)
            ->withSession(['oauth_state.'.$provider => 'state-'.$provider])
            ->get(route('connections.callback', [
                'provider' => $provider,
                'state' => 'state-'.$provider,
                'code' => 'code-'.$provider,
            ]))
            ->assertRedirect(route('profile.edit').'#connections');

        $this->assertDatabaseHas('connected_accounts', [
            'user_id' => $member->id,
            'provider' => $provider,
            'provider_user_id' => $expectedUserId,
            'username' => $expectedUsername,
        ]);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: array<string, mixed>, 4: array<string, mixed>, 5: string, 6: ?string}>
     */
    public static function providerCases(): array
    {
        return [
            'google' => [
                'google',
                'https://oauth2.googleapis.com/token',
                'https://openidconnect.googleapis.com/v1/userinfo',
                ['access_token' => 'google-token', 'scope' => 'openid email profile'],
                ['sub' => 'google-1', 'email' => 'google@example.com', 'name' => 'Google Member', 'picture' => 'https://example.com/google.png'],
                'google-1',
                'google@example.com',
            ],
            'facebook' => [
                'facebook',
                'https://graph.facebook.com/v19.0/oauth/access_token',
                'https://graph.facebook.com/me?fields=id,name,email,picture.type(large)',
                ['access_token' => 'facebook-token', 'scope' => 'email public_profile'],
                ['id' => 'facebook-1', 'name' => 'Facebook Member', 'email' => 'facebook@example.com', 'picture' => ['data' => ['url' => 'https://example.com/facebook.png']]],
                'facebook-1',
                'Facebook Member',
            ],
            'figma' => [
                'figma',
                'https://api.figma.com/v1/oauth/token',
                'https://api.figma.com/v1/me',
                ['access_token' => 'figma-token', 'scope' => 'current_user:read'],
                ['id' => 'figma-1', 'handle' => 'figma-member', 'email' => 'figma@example.com', 'img_url' => 'https://example.com/figma.png'],
                'figma-1',
                'figma-member',
            ],
        ];
    }

    public function test_an_account_cannot_be_connected_to_two_members(): void
    {
        $this->configureGithub();
        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'github-token',
                'scope' => 'read:user',
            ]),
            'https://api.github.com/user' => Http::response([
                'id' => 12345,
                'login' => 'octocat',
                'name' => 'Octo Cat',
                'email' => 'octo@example.com',
            ]),
        ]);

        $owner = $this->member('owner@example.com');
        $other = $this->member('other@example.com');

        ConnectedAccount::create([
            'user_id' => $owner->id,
            'provider' => 'github',
            'provider_user_id' => '12345',
            'username' => 'octocat',
            'access_token' => 'existing-token',
        ]);

        $this->actingAs($other)
            ->withSession(['oauth_state.github' => 'test-state'])
            ->get(route('connections.callback', [
                'provider' => 'github',
                'state' => 'test-state',
                'code' => 'test-code',
            ]))
            ->assertRedirect(route('profile.edit').'#connections')
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('connected_accounts', [
            'user_id' => $other->id,
            'provider' => 'github',
        ]);
    }

    public function test_members_can_disconnect_an_account(): void
    {
        $member = $this->member();
        ConnectedAccount::create([
            'user_id' => $member->id,
            'provider' => 'github',
            'provider_user_id' => '12345',
            'username' => 'octocat',
            'access_token' => 'token',
        ]);

        $this->actingAs($member)
            ->delete(route('connections.destroy', 'github'))
            ->assertRedirect(route('profile.edit').'#connections');

        $this->assertDatabaseMissing('connected_accounts', [
            'user_id' => $member->id,
            'provider' => 'github',
        ]);
    }

    public function test_unconfigured_providers_cannot_be_connected(): void
    {
        config(['services.figma.client_id' => null, 'services.figma.client_secret' => null]);

        $member = $this->member();

        $this->actingAs($member)
            ->get(route('connections.redirect', 'figma'))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    private function configureGithub(): void
    {
        config([
            'services.github.client_id' => 'github-client',
            'services.github.client_secret' => 'github-secret',
        ]);
    }

    private function member(string $email = 'member@example.com'): User
    {
        return User::factory()->create([
            'email' => $email,
            'role' => 'member',
            'status' => 'active',
            'membership_status' => 'active',
        ]);
    }
}
