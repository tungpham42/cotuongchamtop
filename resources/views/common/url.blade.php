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
<link rel="canonical" href="{{ URL::to('/').$canonicalPath }}" >
@if (!empty($alternateUrls ?? []))
@foreach ($alternateUrls as $alternateLocale => $alternateUrl)
<link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $alternateUrls[config('locales.default', 'vi')] ?? URL::to('/').$canonicalPath }}">
@else
<link rel="alternate" hreflang="vi" href="{{ URL::to('/').$fallbackLangUrls['vi'] }}">
<link rel="alternate" hreflang="en" href="{{ URL::to('/').$fallbackLangUrls['en'] }}">
<link rel="alternate" hreflang="ja" href="{{ URL::to('/').$fallbackLangUrls['ja'] }}">
<link rel="alternate" hreflang="ko" href="{{ URL::to('/').$fallbackLangUrls['ko'] }}">
<link rel="alternate" hreflang="zh" href="{{ URL::to('/').$fallbackLangUrls['zh'] }}">
<link rel="alternate" hreflang="x-default" href="{{ URL::to('/').$fallbackLangUrls[config('locales.default', 'vi')] }}">
@endif
