<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WM9GZXN"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<header class="site-header shadow-lg sticky-top">
  <div class="container mx-auto">
    <div class="row align-items-center">
      <a class="navbar-brand small mr-auto my-0 stopPromotion" href="{{ url('/ja') }}"><img src="{{ $cdnUrl }}/img/app-icons/logo.png" class="xiangqi-logo" alt="xiangqi logo"><h1 class="d-inline" style="font-size: inherit !important;"><strong>シャンチー</strong></h1><span id="header-status"></span></a>
      <div class="menu-toggle open" title="Trình đơn"></div>
      <nav class="navbar py-0">
        <ul class="nav navbar-nav">
          <li>
            <a class="home stopPromotion" href="{{ url('/ja') }}"><i class="far fa-house"></i> ホームページ</a>
          </li>
          <li>
            <a class="room stopPromotion rooms-list" href="{{ url('/heya-ichiran') }}"><i class="far fa-list-alt"></i> 部屋一覧</a>
          </li>
          <li>
            <a class="setup puzzle stopPromotion" href="{{ url('/pazuru') }}"><i class="far fa-puzzle-piece"></i> パズル</a>
          </li>
          <li>
          <a class="contact stopPromotion" href="{{ url('/kontakuto') }}"><i class="far fa-envelope"></i> コンタクト</a>
          </li>
          <li class="dropdown language-switcher">
            <a class="lang dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false"><i class="far fa-language"></i> 日本語</a>
            <div class="dropdown-menu dropdown-menu-right shadow">
              <a class="dropdown-item stopPromotion{{ $canonicalUrl === $langViUrl ? ' active disabled' : '' }}" href="{{ url($langViUrl) }}"><span class="shadow-sm fi fi-vn"></span> Tiếng Việt</a>
              <a class="dropdown-item stopPromotion{{ $canonicalUrl === $langEnUrl ? ' active disabled' : '' }}" href="{{ url($langEnUrl) }}"><span class="shadow-sm fi fi-us"></span> English</a>
              <a class="dropdown-item stopPromotion{{ $canonicalUrl === $langJaUrl ? ' active disabled' : '' }}" href="{{ url($langJaUrl) }}"><span class="shadow-sm fi fi-jp"></span> 日本語</a>
              <a class="dropdown-item stopPromotion{{ $canonicalUrl === $langKoUrl ? ' active disabled' : '' }}" href="{{ url($langKoUrl) }}"><span class="shadow-sm fi fi-kr"></span> 한국어</a>
              <a class="dropdown-item stopPromotion{{ $canonicalUrl === $langZhUrl ? ' active disabled' : '' }}" href="{{ url($langZhUrl) }}"><span class="shadow-sm fi fi-cn"></span> 中文</a>
            </div>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</header>