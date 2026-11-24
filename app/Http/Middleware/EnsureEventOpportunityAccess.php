<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEventOpportunityAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $allowed = $user->isAdmin()
            || $user->isSuperAdmin()
            || $user->hasRole('hub_owner')
            || $user->coordinatesChapter();

        if (! $allowed) {
            abort(403);
        }

        return $next($request);
    }
}
