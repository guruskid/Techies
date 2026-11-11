<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComingSoon
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! env('COMING_SOON', false)) {
            return $next($request);
        }
        $allowed = ['coming-soon', 'about', 'login', 'register', 'up'];
        $path = trim($request->path(), '/');
        if (in_array($path, $allowed, true)) {
            return $next($request);
        }

        return redirect('/coming-soon');
    }
}
