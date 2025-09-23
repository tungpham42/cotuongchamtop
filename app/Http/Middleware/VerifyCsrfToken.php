<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/createRoom',
        '/changePass',
        '/doiPass',
        '/processMail',
        '/xulyMail',
        '/updateFEN',
        '/test-*'  // Allow all test routes
    ];

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        // Bypass CSRF in non-production environments
        if (app()->environment() !== 'production') {
            return true;
        }

        return parent::inExceptArray($request);
    }
}
