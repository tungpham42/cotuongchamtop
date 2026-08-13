<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure the user is logged in and is marked as an admin
        if (auth()->check() && auth()->user()->is_admin) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}
