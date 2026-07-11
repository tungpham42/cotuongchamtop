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
    function localized_page_data(string $key, string $locale, array $data = [], array $parameters = []): array
    {
        $paths = localized_alternate_paths($key, $parameters);

        return array_merge([
            'locale' => $locale,
            'langViUrl' => $paths['vi'] ?? '/',
            'langEnUrl' => $paths['en'] ?? '/en',
            'langJaUrl' => $paths['ja'] ?? '/ja',
            'langKoUrl' => $paths['ko'] ?? '/ko',
            'langZhUrl' => $paths['zh'] ?? '/zh',
            'canonicalUrl' => $paths[$locale] ?? ($paths[config('locales.default', 'vi')] ?? '/'),
            'alternateUrls' => app(LocalizedUrlService::class)->alternateUrls($key, $parameters),
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
