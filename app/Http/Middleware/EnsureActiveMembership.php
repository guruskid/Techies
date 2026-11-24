<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasActiveMembership()) {
            return $next($request);
        }

        return redirect()
            ->route('membership.show')
            ->with('error', 'Complete your one-time membership payment to use this feature.');
    }
}
