<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComingSoon
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('techies.coming_soon')) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && $user->hasAnyRole(['admin', 'super_admin'])) {
            return $next($request);
        }

        $allowed = [
            'coming-soon',
            'about',
            'login',
            'register',
            'forgot-password',
            'sitemap.xml',
            'up',
        ];

        $path = trim($request->path(), '/');

        $allowedPrefixes = [
            'reset-password',
            'email/verify',
            'storage',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        if (in_array($path, $allowed, true)) {
            return $next($request);
        }

        return redirect('/coming-soon');
    }
}
