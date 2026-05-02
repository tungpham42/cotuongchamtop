<?php

namespace App\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;

class LocalizedUrlService
{
    public function supportedLocales(): array
    {
        return config('locales.supported', ['vi']);
    }

    public function defaultLocale(): string
    {
        return config('locales.default', 'vi');
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->supportedLocales(), true);
    }

    public function path(string $key, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $configuredPaths = config('locales.paths', []);
        $paths = $configuredPaths[$key] ?? null;

        if (!is_array($paths)) {
            throw new InvalidArgumentException("Localized path [{$key}] is not configured.");
        }

        $path = $paths[$locale] ?? $paths[$this->defaultLocale()] ?? null;

        if ($path === null) {
            throw new InvalidArgumentException("Localized path [{$key}] has no path for locale [{$locale}].");
        }

        foreach ($parameters as $name => $value) {
            $path = str_replace('{' . $name . '}', $value, $path);
        }

        return $path === '/' ? '/' : '/' . ltrim($path, '/');
    }

    public function url(string $key, array $parameters = [], ?string $locale = null): string
    {
        return url($this->path($key, $parameters, $locale));
    }

    public function alternatePaths(string $key, array $parameters = []): array
    {
        $paths = [];

        foreach ($this->supportedLocales() as $locale) {
            $paths[$locale] = $this->path($key, $parameters, $locale);
        }

        return $paths;
    }

    public function alternateUrls(string $key, array $parameters = []): array
    {
        $urls = [];

        foreach ($this->supportedLocales() as $locale) {
            $urls[$locale] = $this->url($key, $parameters, $locale);
        }

        return $urls;
    }

    public function detectLocaleFromPath(string $path): string
    {
        $firstSegment = Str::of($path)->trim('/')->explode('/')->first();

        if ($firstSegment && $this->isSupported($firstSegment)) {
            return $firstSegment;
        }

        $legacyRoomLocales = [
            'room' => 'en',
            'rumu' => 'ja',
            'bang' => 'ko',
            'fangjian' => 'zh',
            'phong' => 'vi',
        ];

        return $legacyRoomLocales[$firstSegment] ?? $this->defaultLocale();
    }
}
