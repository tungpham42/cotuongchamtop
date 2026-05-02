<?php

namespace App\Http\Middleware;

use App\Services\LocalizedUrlService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SetLocale
{
    private LocalizedUrlService $localizedUrls;

    public function __construct(LocalizedUrlService $localizedUrls)
    {
        $this->localizedUrls = $localizedUrls;
    }

    public function handle(Request $request, Closure $next, ?string $locale = null)
    {
        $locale = $locale ?: $this->localizedUrls->detectLocaleFromPath($request->path());

        if (!$this->localizedUrls->isSupported($locale)) {
            $locale = $this->localizedUrls->defaultLocale();
        }

        app()->setLocale($locale);

        View::share('locale', $locale);
        View::share('supportedLocales', $this->localizedUrls->supportedLocales());
        View::share('localeLabels', config('locales.labels', []));
        View::share('localeFlags', config('locales.flags', []));

        return $next($request);
    }
}
