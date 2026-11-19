<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs a user out when their access changes while they are signed in.
 *
 * The access signature is captured at login. If an administrator later changes
 * the person's role, status or assigned roles, the next request no longer
 * matches and the session is destroyed so the person has to sign in again
 * with their current permissions.
 */
class EnsureRoleIsCurrent
{
    public const SESSION_KEY = 'auth.access_signature';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $stored = $request->session()->get(self::SESSION_KEY);

        if (is_string($stored) && hash_equals($user->accessSignature(), $stored)) {
            return $next($request);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your access has changed. Please sign in again.',
            ], 401);
        }

        return redirect()
            ->route('login')
            ->with('error', 'Your access changed, so you were signed out. Please sign in again.');
    }
}
