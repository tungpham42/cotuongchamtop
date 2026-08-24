<?php

use App\Services\LocalizedUrlService;
use Illuminate\Support\Facades\NumberFormat;

if (!function_exists('localized_path')) {
    function localized_path(string $key, array $parameters = [], ?string $locale = null): string
    {
        return app(LocalizedUrlService::class)->path($key, $parameters, $locale);
    }
}

if (!function_exists('localized_url')) {
    function localized_url(string $key, array $parameters = [], ?string $locale = null): string
    {
        return app(LocalizedUrlService::class)->url($key, $parameters, $locale);
    }
}

if (!function_exists('localized_alternate_paths')) {
    function localized_alternate_paths(string $key, array $parameters = []): array
    {
        return app(LocalizedUrlService::class)->alternatePaths($key, $parameters);
    }
}

if (!function_exists('localized_page_data')) {
    /**
     * @param array $parameters Shared route parameters used for every locale.
     *        Fine for keys whose parameters don't change per locale (e.g. an id).
     * @param array $parametersByLocale Optional. Route parameters keyed by
     *        locale, for keys where a parameter is itself translated (e.g. an
     *        article slug, which differs per ArticleTranslation). When given,
     *        this takes precedence over $parameters.
     */
    function localized_page_data(string $key, string $locale, array $data = [], array $parameters = [], array $parametersByLocale = []): array
    {
        $service = app(LocalizedUrlService::class);

        if (!empty($parametersByLocale)) {
            $paths = $service->alternatePathsForLocales($key, $parametersByLocale);
            $alternateUrls = $service->alternateUrlsForLocales($key, $parametersByLocale);
        } else {
            $paths = $service->alternatePaths($key, $parameters);
            $alternateUrls = $service->alternateUrls($key, $parameters);
        }

        return array_merge([
            'locale' => $locale,
            'langViUrl' => $paths['vi'] ?? '/',
            'langEnUrl' => $paths['en'] ?? '/en',
            'langJaUrl' => $paths['ja'] ?? '/ja',
            'langKoUrl' => $paths['ko'] ?? '/ko',
            'langZhUrl' => $paths['zh'] ?? '/zh',
            'canonicalUrl' => $paths[$locale] ?? ($paths[config('locales.default', 'vi')] ?? '/'),
            'alternateUrls' => $alternateUrls,
        ], $data);
    }
}

function numberToWordsVi($number)
{
    $formatter = new NumberFormatter('vi_VN', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}

function numberToWordsEn($number)
{
    $formatter = new NumberFormatter('en_US', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}

function numberToWordsJa($number)
{
    $formatter = new NumberFormatter('ja_JP', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}

function numberToWordsKo($number)
{
    $formatter = new NumberFormatter('ko_KR', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}

function numberToWordsZh($number)
{
    $formatter = new NumberFormatter('zh_CN', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}

function containsCJK(string $string): bool {
    // \p{Han} covers Chinese characters (including Japanese Kanji and Korean Hanja)
    // \p{Hiragana} and \p{Katakana} cover Japanese syllabaries
    // \p{Hangul} covers the Korean alphabet
    // The 'u' modifier at the end turns on UTF-8 mode
    $pattern = '/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]/u';

    return preg_match($pattern, $string) === 1;
}
