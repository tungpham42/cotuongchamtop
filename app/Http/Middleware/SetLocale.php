<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get available locales from config
        $availableLocales = config('app.available_locales', ['vi', 'en', 'ko', 'ja', 'zh']);
        
        // Get locale from route parameter
        $locale = $request->route('locale');
        
        // If no locale in route, try to get from session or use default
        if (!$locale) {
            $locale = Session::get('locale', config('app.locale', 'vi'));
        }
        
        // Validate locale
        if (!in_array($locale, $availableLocales)) {
            $locale = config('app.fallback_locale', 'vi');
        }
        
        // Set application locale
        App::setLocale($locale);
        
        // Store locale in session for future requests
        Session::put('locale', $locale);
        
        // Make locale available in views
        view()->share('currentLocale', $locale);
        view()->share('availableLocales', $availableLocales);
        
        return $next($request);
    }
}