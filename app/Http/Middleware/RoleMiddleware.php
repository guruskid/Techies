<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role guard that follows the users.role column for staff roles.
 *
 * Spatie's middleware only reads the pivot table, which can drift from
 * users.role. Going through the model keeps route access and the navigation
 * badges in agreement.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowed = collect($roles)
            ->flatMap(fn (string $role) => explode('|', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->values()
            ->all();

        abort_unless($user && $user->hasAnyRole($allowed), 403);

        return $next($request);
    }
}
