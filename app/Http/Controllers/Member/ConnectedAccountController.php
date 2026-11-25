<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ConnectedAccount;
use App\Services\OAuthAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class ConnectedAccountController extends Controller
{
    public function redirect(Request $request, string $provider, OAuthAccountService $oauth): RedirectResponse
    {
        if (! $oauth->isConfigured($provider)) {
            return back()->with('error', 'That account provider is not configured yet.');
        }

        $state = Str::random(48);
        $request->session()->put('oauth_state.'.$provider, $state);

        return redirect()->away($oauth->redirectUrl($provider, $state));
    }

    public function callback(Request $request, string $provider, OAuthAccountService $oauth): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('profile.edit')
                ->withFragment('connections')
                ->with('error', 'The connection was cancelled or rejected.');
        }

        $expected = (string) $request->session()->pull('oauth_state.'.$provider, '');
        $state = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');

        if ($expected === '' || $state === '' || ! hash_equals($expected, $state)) {
            return redirect()->route('profile.edit')
                ->withFragment('connections')
                ->with('error', 'The connection request expired. Please try again.');
        }

        if ($code === '') {
            return redirect()->route('profile.edit')
                ->withFragment('connections')
                ->with('error', 'The account provider did not return an authorisation code.');
        }

        try {
            $token = $oauth->exchange($provider, $code);
            $profile = $oauth->fetchProfile($provider, $token);
        } catch (RuntimeException $exception) {
            return redirect()->route('profile.edit')
                ->withFragment('connections')
                ->with('error', $exception->getMessage());
        }

        if ($profile['provider_user_id'] === '') {
            return redirect()->route('profile.edit')
                ->withFragment('connections')
                ->with('error', 'The account provider did not return a usable account identifier.');
        }

        $alreadyLinked = ConnectedAccount::where('provider', $provider)
            ->where('provider_user_id', $profile['provider_user_id'])
            ->where('user_id', '!=', $request->user()->id)
            ->exists();

        if ($alreadyLinked) {
            return redirect()->route('profile.edit')
                ->withFragment('connections')
                ->with('error', 'That account is already connected to another member.');
        }

        ConnectedAccount::updateOrCreate(
            ['user_id' => $request->user()->id, 'provider' => $provider],
            [
                'provider_user_id' => $profile['provider_user_id'],
                'username' => $profile['username'],
                'email' => $profile['email'],
                'name' => $profile['name'],
                'avatar' => $profile['avatar'],
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
                'scopes' => $profile['scopes'],
            ]
        );

        return redirect()->route('profile.edit')
            ->withFragment('connections')
            ->with('success', $oauth->providers()[$provider]['label'].' connected.');
    }

    public function destroy(Request $request, string $provider): RedirectResponse
    {
        $request->user()->connectedAccounts()->where('provider', $provider)->delete();

        return redirect()->route('profile.edit')
            ->withFragment('connections')
            ->with('success', 'Account disconnected.');
    }
}
