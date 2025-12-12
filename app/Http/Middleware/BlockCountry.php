<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Stevebauman\Location\Facades\Location;

class BlockCountry
{
    /**
     * Handle an incoming request.
     */
    // app/Http/Middleware/BlockCountry.php
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // Localhost testing override (MaxMind usually returns false for 127.0.0.1)
        if ($ip == '127.0.0.1') {
            // You can manually set a test IP here to verify it works
            // $ip = '123.125.114.144'; // China
        }

        $position = Location::get($ip);

        if ($position) {
            // CN = China, SG = Singapore
            if (in_array($position->countryCode, ['CN', 'SG'])) {
                abort(403, 'Access denied.');
            }
        }

        return $next($request);
    }
}
