<meta charset="utf-8" >
<meta property="og:url" content="{{ url()->full() }}" >
<meta property="fb:app_id" content="279071963296709">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="twitter:card" content="summary">
<meta property="og:type" content="website">
<meta name="theme-color" content="#f04124" >
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta property="og:image" content="@yield('og_image', url('/img/1200x630.jpg'))">
<meta property="og:image:width" content="@yield('og_image_width', '1200')" >
<meta property="og:image:height" content="@yield('og_image_height', '630')" >
<meta property="og:image:alt" content="@yield('og_image_alt', 'Cờ tướng 2 người')" >
<meta property="og:image:type" content="@yield('og_image_type', 'image/jpeg')" />

{{-- SEO: Canonical and Hreflang Tags --}}
@isset($canonicalUrl)
<link rel="canonical" href="{{ url($canonicalUrl) }}" >
@endisset

@isset($alternateUrls)
    @foreach($alternateUrls as $locale => $url)
<link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}" />
    @endforeach
<link rel="alternate" hreflang="x-default" href="{{ $alternateUrls[config('locales.default', 'vi')] ?? url('/') }}" />
@else
    {{-- Fallback using localized path variables --}}
    @isset($langViUrl)<link rel="alternate" hreflang="vi" href="{{ url($langViUrl) }}" />@endisset
    @isset($langEnUrl)<link rel="alternate" hreflang="en" href="{{ url($langEnUrl) }}" />@endisset
    @isset($langJaUrl)<link rel="alternate" hreflang="ja" href="{{ url($langJaUrl) }}" />@endisset
    @isset($langKoUrl)<link rel="alternate" hreflang="ko" href="{{ url($langKoUrl) }}" />@endisset
    @isset($langZhUrl)<link rel="alternate" hreflang="zh" href="{{ url($langZhUrl) }}" />@endisset
    @isset($langViUrl)<link rel="alternate" hreflang="x-default" href="{{ url($langViUrl) }}" />@endisset
@endisset

<link rel="apple-touch-icon" href="{{ url('/') }}/img/app-icons/apple-touch-icon-iphone-game.png">
<link rel="apple-touch-icon" sizes="76x76" href="{{ url('/') }}/img/app-icons/apple-touch-icon-ipad-game.png">
<link rel="apple-touch-icon" sizes="120x120" href="{{ url('/') }}/img/app-icons/apple-touch-icon-iphone-retina-game.png">
<link rel="apple-touch-icon" sizes="152x152" href="{{ url('/') }}/img/app-icons/apple-touch-icon-ipad-retina-game.png">
<link rel="icon" sizes="32x32" href="{{ url('/') }}/img/favicon-32x32-game.png" >
<link rel="stylesheet" href="{{ url('/') }}/css/fa/css/all.min.css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" integrity="sha512-rt/SrQ4UNIaGfDyEXZtNcyWvQeOq0QLygHluFQcSjaGB04IxWhal71tKuzP6K8eYXYB6vJV4pHkXcmFGGQ1/0w==" crossorigin="anonymous" referrerpolicy="no-referrer" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/4.6.2/yeti/bootstrap.min.css" integrity="sha512-o9NK3edLgKJjQxISJIJFMI2w1yPCyBVK0OffzNAN7j3BNt6am8T5VIq9ZblOFKdhkJhvyLWnOWslPSj1uS4MjQ==" crossorigin="anonymous" referrerpolicy="no-referrer" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css" integrity="sha512-PT0RvABaDhDQugEbpNMwgYBCnGCiTZMh9yOzUsJHDgl/dMhD9yjHAwoumnUk3JydV3QTcIkNDuN40CJxik5+WQ==" crossorigin="anonymous" referrerpolicy="no-referrer" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icons/7.1.0/css/flag-icons.min.css" integrity="sha512-bZBu2H0+FGFz/stDN/L0k8J0G8qVsAL0ht1qg5kTwtAheiXwiRKyCq1frwfbSFSJN3jooR5kauE0YjtPzhZtJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/introjs.min.css" integrity="sha512-DcHJLWkmfnv+isBrT8M3PhKEhsHWhEgulhr8m5EuGhdAG9w+vUyjlwgR4ISLN0+s/m4ItmPsTOqPzW714dtr5w==" crossorigin="anonymous" referrerpolicy="no-referrer">
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pace/1.2.4/themes/red/pace-theme-loading-bar.min.css" integrity="sha512-L7L86P7/Kgjmnc8/oz6wEpwzWXf2ezUPLgOt+bAdquy7SA0kK8ZtAFrTQpJN0mdtkILJ71UsRTL8sZGRS9bs1g==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100..900&family=Pacifico&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Roboto:ital,wght@0,100..900;1,100..900&family=Texturina:ital,opsz,wght@0,12..72,100..900;1,12..72,100..900&display=swap" rel="stylesheet">
{{-- <link href="{{ url('/') }}/css/index.css?v=321" rel="stylesheet"> --}}
<link href="{{ url('/') }}/css/index_new.css?v=34" rel="stylesheet">
@if (!($showAds ?? true))
<style>
  .adsense,
  .adsbygoogle,
  #ads,
  .aff-link,
  .hoc-link {
    display: none !important;
  }
</style>
@endif
<link rel="manifest" href="{{ url('/') }}/manifest.webmanifest?v=2">
@if ($showAds ?? true)
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3585118770961536" crossorigin="anonymous"></script>
@endif
<script async src="https://www.googletagmanager.com/gtag/js?id=G-QEW6K9YPY7"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());

gtag('config', 'G-QEW6K9YPY7');
</script>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WM9GZXN');</script>
@include('common.schemaOrg')
@if ($showAds ?? true)
<script type="text/javascript" src="{{ url('/') }}/js/aclib.js"></script>
@endif
<script type="text/javascript" src="{{ url('/') }}/js/theme-manager.js?v=1"></script>

@guest
<script src="https://accounts.google.com/gsi/client" async defer></script>

<div id="g_id_onload"
     data-client_id="{{ config('services.google.client_id') }}"
     data-login_uri="{{ route('login.google.onetap') }}"
     data-auto_prompt="true"
     data-position="top_right">
</div>
@endguest
