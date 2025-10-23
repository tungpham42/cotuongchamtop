<?php

namespace App\Http\Helpers;

class LocaleHelper
{
    /**
     * Get the available locales
     */
    public static function getAvailableLocales()
    {
        return config('app.available_locales', ['vi', 'en', 'ko', 'ja', 'zh']);
    }

    /**
     * Get locale display names
     */
    public static function getLocaleNames()
    {
        return [
            'vi' => 'Tiếng Việt',
            'en' => 'English', 
            'ko' => '한국어',
            'ja' => '日本語',
            'zh' => '中文',
        ];
    }

    /**
     * Get current locale from app
     */
    public static function getCurrentLocale()
    {
        return app()->getLocale();
    }

    /**
     * Generate localized URLs for all languages
     */
    public static function getLocalizedUrls($currentPath = null)
    {
        $currentPath = $currentPath ?: request()->path();
        $availableLocales = self::getAvailableLocales();
        $currentLocale = self::getCurrentLocale();
        
        $urls = [];
        
        foreach ($availableLocales as $locale) {
            if ($locale === 'vi') {
                // Vietnamese is the default locale, no prefix
                $urls[$locale] = '/' . $currentPath;
            } else {
                // Other locales have prefix
                $urls[$locale] = '/' . $locale . '/' . $currentPath;
            }
        }
        
        return $urls;
    }

    /**
     * Generate hreflang attributes for SEO
     */
    public static function getHreflangUrls($currentPath = null)
    {
        $localizedUrls = self::getLocalizedUrls($currentPath);
        $hreflangs = [];
        
        foreach ($localizedUrls as $locale => $url) {
            $hreflangs[] = [
                'hreflang' => $locale,
                'href' => url($url)
            ];
        }
        
        // Add x-default for Vietnamese (default language)
        $hreflangs[] = [
            'hreflang' => 'x-default',
            'href' => url($localizedUrls['vi'])
        ];
        
        return $hreflangs;
    }

    /**
     * Check if a locale is valid
     */
    public static function isValidLocale($locale)
    {
        return in_array($locale, self::getAvailableLocales());
    }

    /**
     * Get fallback locale
     */
    public static function getFallbackLocale()
    {
        return config('app.fallback_locale', 'vi');
    }
}