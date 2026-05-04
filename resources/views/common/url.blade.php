@php
  $canonicalPath = $canonicalUrl ?? parse_url(url()->current(), PHP_URL_PATH);
  $canonicalPath = $canonicalPath === '/' ? '/' : '/' . ltrim($canonicalPath ?: '/', '/');
  $fallbackLangUrls = [
    'vi' => $langViUrl ?? $canonicalPath,
    'en' => $langEnUrl ?? $canonicalPath,
    'ja' => $langJaUrl ?? $canonicalPath,
    'ko' => $langKoUrl ?? $canonicalPath,
    'zh' => $langZhUrl ?? $canonicalPath,
  ];
@endphp
<link rel="canonical" href="{{ url('/').$canonicalPath }}" >
@if (!empty($alternateUrls ?? []))
@foreach ($alternateUrls as $alternateLocale => $alternateUrl)
<link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $alternateUrls[config('locales.default', 'vi')] ?? url('/').$canonicalPath }}">
@else
<link rel="alternate" hreflang="vi" href="{{ url('/').$fallbackLangUrls['vi'] }}">
<link rel="alternate" hreflang="en" href="{{ url('/').$fallbackLangUrls['en'] }}">
<link rel="alternate" hreflang="ja" href="{{ url('/').$fallbackLangUrls['ja'] }}">
<link rel="alternate" hreflang="ko" href="{{ url('/').$fallbackLangUrls['ko'] }}">
<link rel="alternate" hreflang="zh" href="{{ url('/').$fallbackLangUrls['zh'] }}">
<link rel="alternate" hreflang="x-default" href="{{ url('/').$fallbackLangUrls[config('locales.default', 'vi')] }}">
@endif
