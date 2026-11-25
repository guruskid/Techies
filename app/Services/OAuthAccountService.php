<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OAuthAccountService
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function providers(): array
    {
        return [
            'github' => [
                'label' => 'GitHub',
                'authorize_url' => 'https://github.com/login/oauth/authorize',
                'token_url' => 'https://github.com/login/oauth/access_token',
                'user_url' => 'https://api.github.com/user',
                'scope' => 'read:user user:email',
                'token_headers' => ['Accept' => 'application/json'],
                'user_headers' => ['Accept' => 'application/vnd.github+json', 'User-Agent' => 'MWTT'],
            ],
            'google' => [
                'label' => 'Google / Gmail',
                'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
                'token_url' => 'https://oauth2.googleapis.com/token',
                'user_url' => 'https://openidconnect.googleapis.com/v1/userinfo',
                'scope' => 'openid email profile',
                'extra_authorize' => ['access_type' => 'offline', 'prompt' => 'consent'],
                'user_headers' => ['Accept' => 'application/json'],
            ],
            'facebook' => [
                'label' => 'Facebook',
                'authorize_url' => 'https://www.facebook.com/v19.0/dialog/oauth',
                'token_url' => 'https://graph.facebook.com/v19.0/oauth/access_token',
                'user_url' => 'https://graph.facebook.com/me?fields=id,name,email,picture.type(large)',
                'scope' => 'email public_profile',
                'user_headers' => ['Accept' => 'application/json'],
            ],
            'figma' => [
                'label' => 'Figma',
                'authorize_url' => 'https://www.figma.com/oauth',
                'token_url' => 'https://api.figma.com/v1/oauth/token',
                'user_url' => 'https://api.figma.com/v1/me',
                'scope' => 'current_user:read',
                'user_headers' => ['Accept' => 'application/json'],
            ],
        ];
    }

    public function isConfigured(string $provider): bool
    {
        $config = $this->providers()[$provider] ?? null;

        return $config !== null
            && filled(config('services.'.$provider.'.client_id'))
            && filled(config('services.'.$provider.'.client_secret'));
    }

    public function redirectUrl(string $provider, string $state): string
    {
        $config = $this->providerConfig($provider);
        $params = array_merge([
            'client_id' => config('services.'.$provider.'.client_id'),
            'redirect_uri' => route('connections.callback', $provider),
            'response_type' => 'code',
            'scope' => $config['scope'],
            'state' => $state,
        ], $config['extra_authorize'] ?? []);

        return $config['authorize_url'].'?'.http_build_query($params);
    }

    /**
     * @return array<string, mixed>
     */
    public function exchange(string $provider, string $code): array
    {
        $config = $this->providerConfig($provider);

        try {
            $response = Http::asForm()
                ->withHeaders($config['token_headers'] ?? ['Accept' => 'application/json'])
                ->timeout(15)
                ->post($config['token_url'], [
                    'client_id' => config('services.'.$provider.'.client_id'),
                    'client_secret' => config('services.'.$provider.'.client_secret'),
                    'code' => $code,
                    'redirect_uri' => route('connections.callback', $provider),
                    'grant_type' => 'authorization_code',
                ]);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Could not reach the account provider.');
        }

        if (! $response->successful() || ! is_string($response->json('access_token'))) {
            throw new RuntimeException((string) $response->json('error_description', 'The account provider rejected the connection.'));
        }

        return $response->json();
    }

    /**
     * @param  array<string, mixed>  $token
     * @return array{provider_user_id: string, username: ?string, email: ?string, name: ?string, avatar: ?string, scopes: array<int, string>}
     */
    public function fetchProfile(string $provider, array $token): array
    {
        $config = $this->providerConfig($provider);

        try {
            $response = Http::withToken((string) $token['access_token'])
                ->withHeaders($config['user_headers'] ?? ['Accept' => 'application/json'])
                ->timeout(15)
                ->get($config['user_url']);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Could not read your profile from the account provider.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('The account provider did not return a profile.');
        }

        $profile = $response->json();

        return match ($provider) {
            'github' => [
                'provider_user_id' => (string) ($profile['id'] ?? ''),
                'username' => $profile['login'] ?? null,
                'email' => $profile['email'] ?? $this->githubEmail($token),
                'name' => $profile['name'] ?? null,
                'avatar' => $profile['avatar_url'] ?? null,
                'scopes' => $this->scopes($token),
            ],
            'google' => [
                'provider_user_id' => (string) ($profile['sub'] ?? ''),
                'username' => $profile['email'] ?? null,
                'email' => $profile['email'] ?? null,
                'name' => $profile['name'] ?? null,
                'avatar' => $profile['picture'] ?? null,
                'scopes' => $this->scopes($token),
            ],
            'facebook' => [
                'provider_user_id' => (string) ($profile['id'] ?? ''),
                'username' => $profile['name'] ?? null,
                'email' => $profile['email'] ?? null,
                'name' => $profile['name'] ?? null,
                'avatar' => $profile['picture']['data']['url'] ?? null,
                'scopes' => $this->scopes($token),
            ],
            'figma' => [
                'provider_user_id' => (string) ($profile['id'] ?? ''),
                'username' => $profile['handle'] ?? null,
                'email' => $profile['email'] ?? null,
                'name' => $profile['handle'] ?? null,
                'avatar' => $profile['img_url'] ?? null,
                'scopes' => $this->scopes($token),
            ],
            default => throw new RuntimeException('Unsupported account provider.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function providerConfig(string $provider): array
    {
        $config = $this->providers()[$provider] ?? null;

        if ($config === null) {
            throw new RuntimeException('Unsupported account provider.');
        }

        if (! $this->isConfigured($provider)) {
            throw new RuntimeException($config['label'].' connection is not configured yet.');
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $token
     */
    private function githubEmail(array $token): ?string
    {
        try {
            $emails = Http::withToken((string) $token['access_token'])
                ->withHeaders(['Accept' => 'application/vnd.github+json', 'User-Agent' => 'MWTT'])
                ->timeout(15)
                ->get('https://api.github.com/user/emails')
                ->json();
        } catch (\Throwable) {
            return null;
        }

        if (! is_array($emails)) {
            return null;
        }

        foreach ($emails as $email) {
            if (is_array($email) && ($email['primary'] ?? false) && ($email['verified'] ?? false)) {
                return $email['email'] ?? null;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $token
     * @return array<int, string>
     */
    private function scopes(array $token): array
    {
        $scopes = $token['scope'] ?? '';

        return is_string($scopes) ? array_values(array_filter(explode(' ', $scopes))) : [];
    }
}
