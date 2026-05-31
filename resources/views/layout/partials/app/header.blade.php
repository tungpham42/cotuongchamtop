@php
  $homeUrl = localized_url('home');
  $roomListUrl = localized_url('room.list');
  $membersUrl = localized_url('user.list');
  $puzzleListUrl = localized_url('puzzle.list');
  $playingUrl = localized_url('app.dashboard');
  $rankingUrl = localized_url('app.ranking');
  $searchUrl = localized_url('search');
  $historyUrl = localized_url('app.history');

  // 1. Get the raw route name and parameters
  $rawRouteName = Route::currentRouteName() ?: 'home';
  $currentParams = Route::current() ? Route::current()->parameters() : [];

  // 2. Strip the locale prefix if it exists (e.g., transforms "ko.login" to "login")
  $currentRoute = preg_replace('/^(vi|en|ja|ko|zh)\./', '', $rawRouteName);

  // 3. Generate the clean URLs
  $currentLangViUrl = $langViUrl ?? localized_path($currentRoute, $currentParams, 'vi');
  $currentLangEnUrl = $langEnUrl ?? localized_path($currentRoute, $currentParams, 'en');
  $currentLangJaUrl = $langJaUrl ?? localized_path($currentRoute, $currentParams, 'ja');
  $currentLangKoUrl = $langKoUrl ?? localized_path($currentRoute, $currentParams, 'ko');
  $currentLangZhUrl = $langZhUrl ?? localized_path($currentRoute, $currentParams, 'zh');
  $currentCanonicalUrl = $canonicalUrl ?? localized_path($currentRoute, $currentParams);
@endphp
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WM9GZXN"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<header class="site-header shadow-lg sticky-top">
  <div class="container mx-auto">
    <div class="row align-items-center">
      <a class="navbar-brand small mr-auto my-0 showPromotion" href="{{ $homeUrl }}"><img src="{{ url('/') }}/img/app-icons/logo.png" class="xiangqi-logo" alt="xiangqi logo"><h1 class="d-inline" style="font-size: inherit !important;"><strong>{{ __("Cờ tướng") }}</strong></h1><span id="header-status"></span></a>
      <div class="menu-toggle open" title="Trình đơn"></div>
      <nav class="navbar py-0">
        <ul class="nav navbar-nav">
          <li class="nav-item">
            <a class="home showPromotion" href="{{ $homeUrl }}"><i class="far fa-house"></i> {{ __("Trang chủ") }}</a>
          </li>
          <li class="dropdown">
            <a id="dashboardDropdown" class="dashboard room trophy thi-dau dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false"><i class="far fa-trophy-alt"></i> {{ __("Thi đấu") }}</a>
            <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="dashboardDropdown">
              <a class="rooms-list showPromotion dropdown-item{{ url()->current() == $roomListUrl ? ' active disabled' : '' }}" href="{{ $roomListUrl }}"><i class="far fa-list-alt"></i> {{ __("Sảnh chờ") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == $membersUrl ? ' active disabled' : '' }}" href="{{ $membersUrl }}"><i class="far fa-users"></i> {{ __("Thành viên") }}</a>
              <a class="setup puzzle showPromotion dropdown-item{{ url()->current() == $puzzleListUrl ? ' active disabled' : '' }}" href="{{ $puzzleListUrl }}"><i class="far fa-puzzle-piece"></i> {{ __("Cờ thế") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == $playingUrl ? ' active disabled' : '' }}" href="{{ $playingUrl }}"><i class="far fa-list"></i> {{ __("Đang thi đấu") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == $rankingUrl ? ' active disabled' : '' }}" href="{{ $rankingUrl }}"><i class="far fa-star"></i> {{ __("Bảng xếp hạng") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == $searchUrl ? ' active disabled' : '' }}" href="{{ $searchUrl }}"><i class="far fa-search"></i> {{ __("Tìm kiếm kỳ thủ") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == $historyUrl ? ' active disabled' : '' }}" href="{{ $historyUrl }}"><i class="far fa-archive"></i> {{ __("Lịch sử thi đấu") }}</a>
              <a target="_blank" class="showPromotion dropdown-item" href="https://diendan.cotuong.top/"><i class="far fa-comments"></i> {{ __("Diễn đàn") }}</a>
            </div>
          </li>
          <li class="nav-item">
            <a class="showPromotion" target="_blank" href="https://www.facebook.com/groups/HoiChoiCoTuong"><i class="far fa-user-friends"></i> {{ __("Nhóm Facebook") }}</a>
          </li>
          @guest
            @if (Route::has('login'))
              <li class="nav-item">
                <a class="showPromotion login" href="{{ localized_url('login') }}"><i class="far fa-sign-in"></i> {{ __('Đăng nhập') }}</a>
              </li>
            @endif
          @else
            <li class="dropdown">
              <a id="navbarDropdown" class="dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false">
                <img src="{{ Avatar::create(Auth::user()->name)->setDimension(24)->setFontSize(12) }}" />
              </a>

              <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="navbarDropdown">
                @if (Auth::user()->isStandard())
                  <span class="dropdown-item text-success"><i class="far fa-crown"></i> {{ __("Standard (ẩn quảng cáo)") }}</span>
                @else
                  <a href="{{ localized_url('app.profile') }}#standard-plan" class="showPromotion dropdown-item text-danger">
                    <i class="far fa-crown"></i> {{ __("Nâng cấp Standard") }}
                  </a>
                @endif
                <a href="{{ localized_url('app.profile') }}" class="showPromotion dropdown-item{{ url()->current() == localized_url('app.profile') ? ' active disabled' : '' }}"><i class="far fa-id-card"></i> {{ __("Hồ sơ của tôi") }}</a>
                <a href="{{ localized_url('app.name') }}" class="showPromotion dropdown-item{{ url()->current() == localized_url('app.name') ? ' active disabled' : '' }}"><i class="far fa-user-edit"></i> {{ __("Đổi tên") }}</a>
                <a href="{{ localized_url('app.ui') }}" class="showPromotion dropdown-item{{ url()->current() == localized_url('app.ui') ? ' active disabled' : '' }}"><i class="far fa-palette"></i> {{ __("Đổi giao diện") }}</a>
                <a href="{{ localized_url('app.password') }}" class="showPromotion dropdown-item{{ url()->current() == localized_url('app.password') ? ' active disabled' : '' }}"><i class="far fa-lock-alt"></i> {{ __("Đổi mật khẩu") }}</a>
                <a class="dropdown-item" href="{{ route('logout') }}"
                  onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">
                  <i class="far fa-sign-out"></i> {{ __('Logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                  @csrf
                </form>
              </div>
            </li>
          @endguest
          <li class="dropdown language-switcher">
            <a class="lang dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false"><i class="far fa-language"></i> {{ $localeLabels[$locale] ?? __('Tiếng Việt') }}</a>
            <div class="dropdown-menu dropdown-menu-right shadow">
              <a class="dropdown-item showPromotion{{ $currentCanonicalUrl === $currentLangViUrl ? ' active disabled' : '' }}" href="{{ url($currentLangViUrl) }}"><span class="shadow-sm fi fi-vn"></span> {{ __("Tiếng Việt") }}</a>
              <a class="dropdown-item showPromotion{{ $currentCanonicalUrl === $currentLangEnUrl ? ' active disabled' : '' }}" href="{{ url($currentLangEnUrl) }}"><span class="shadow-sm fi fi-us"></span> {{ __("English") }}</a>
              <a class="dropdown-item showPromotion{{ $currentCanonicalUrl === $currentLangJaUrl ? ' active disabled' : '' }}" href="{{ url($currentLangJaUrl) }}"><span class="shadow-sm fi fi-jp"></span> {{ __("日本語") }}</a>
              <a class="dropdown-item showPromotion{{ $currentCanonicalUrl === $currentLangKoUrl ? ' active disabled' : '' }}" href="{{ url($currentLangKoUrl) }}"><span class="shadow-sm fi fi-kr"></span> {{ __("한국어") }}</a>
              <a class="dropdown-item showPromotion{{ $currentCanonicalUrl === $currentLangZhUrl ? ' active disabled' : '' }}" href="{{ url($currentLangZhUrl) }}"><span class="shadow-sm fi fi-cn"></span> {{ __("中文") }}</a>
            </div>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</header>
