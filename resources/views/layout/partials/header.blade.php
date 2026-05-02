<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WM9GZXN"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<header class="site-header shadow-lg sticky-top">
  <div class="container mx-auto">
    <div class="row align-items-center">
      <a class="navbar-brand small mr-auto my-0 showPromotion" href="{{ url('') }}"><img src="{{ url('/') }}/img/app-icons/logo.png" class="xiangqi-logo" alt="xiangqi logo"><h1 class="d-inline" style="font-size: inherit !important;"><strong>{{ __("Cờ tướng") }}</strong></h1>
        @if ($roomCode != '')
        <span id="header-status"></span>
        @endif
      </a>
      <div class="menu-toggle open" title="Trình đơn"></div>
      <nav class="navbar py-0">
        <ul class="nav navbar-nav">
          <li class="nav-item">
            <a class="home showPromotion" href="{{ url('') }}"><i class="far fa-house"></i> {{ __("Trang chủ") }}</a>
          </li>
          <li class="dropdown">
            <a id="dashboardDropdown" class="dashboard room trophy thi-dau dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false"><i class="far fa-trophy-alt"></i> {{ __("Thi đấu") }}</a>
            <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="dashboardDropdown">
              <a class="rooms-list showPromotion dropdown-item{{ url()->current() == url('/sanh-cho') ? ' active disabled' : '' }}" href="{{ url('/sanh-cho') }}"><i class="far fa-list-alt"></i> {{ __("Sảnh chờ") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/thanh-vien') ? ' active disabled' : '' }}" href="{{ url('/thanh-vien') }}"><i class="far fa-users"></i> {{ __("Thành viên") }}</a>
              <a class="setup puzzle showPromotion dropdown-item{{ url()->current() == url('/tat-ca-the-co') ? ' active disabled' : '' }}" href="{{ url('/tat-ca-the-co') }}"><i class="far fa-puzzle-piece"></i> {{ __("Cờ thế") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/thi-dau') ? ' active disabled' : '' }}" href="{{ url('/thi-dau') }}"><i class="far fa-list"></i> {{ __("Đang thi đấu") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/bang-xep-hang') ? ' active disabled' : '' }}" href="{{ url('/bang-xep-hang') }}"><i class="far fa-star"></i> {{ __("Bảng xếp hạng") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/tim-kiem') ? ' active disabled' : '' }}" href="{{ url('/tim-kiem') }}"><i class="far fa-search"></i> {{ __("Tìm kiếm kỳ thủ") }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/lich-su') ? ' active disabled' : '' }}" href="{{ url('/lich-su') }}"><i class="far fa-archive"></i> {{ __("Lịch sử thi đấu") }}</a>
              <a target="_blank" class="showPromotion dropdown-item" href="https://diendan.cotuong.top/"><i class="far fa-comments"></i> {{ __("Diễn đàn") }}</a>
              {{-- <a class="dropdown-item" href="https://blog.cotuong.top/"><i class="far fa-blog"></i> Tin tức</a> --}}
            </div>
          </li>
          <li class="nav-item">
            <a class="showPromotion" target="_blank" href="https://www.facebook.com/groups/HoiChoiCoTuong"><i class="far fa-user-friends"></i> {{ __("Nhóm Facebook") }}</a>
          </li>
          @guest
            @if (Route::has('login'))
              <li class="nav-item">
                <a class="showPromotion login" href="{{ route('login') }}"><i class="far fa-sign-in"></i> {{ __('Login') }}</a>
              </li>
            @endif

            {{-- @if (Route::has('register'))
              <li class="nav-item">
                <a class="showPromotion register" href="{{ route('register') }}"><i class="far fa-user-plus"></i> {{ __('Register') }}</a>
              </li>
            @endif --}}
          @else
            <li class="dropdown">
              <a id="navbarDropdown" class="dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false">
                <img src="{{ Avatar::create(Auth::user()->name)->setDimension(24)->setFontSize(12) }}" />
              </a>

              <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="navbarDropdown">
                @if (Auth::user()->isStandard())
                  <span class="dropdown-item text-success"><i class="far fa-crown"></i> {{ __("Standard (ẩn quảng cáo)") }}</span>
                @else
                  <a href="{{ url('/ho-so-cua-toi') }}#standard-plan" class="showPromotion dropdown-item text-danger">
                    <i class="far fa-crown"></i> {{ __("Nâng cấp Standard") }}
                  </a>
                @endif
                <a href="{{ url('/ho-so-cua-toi') }}" class="showPromotion dropdown-item{{ url()->current() == url('/ho-so-cua-toi') ? ' active disabled' : '' }}"><i class="far fa-id-card"></i> {{ __("Hồ sơ của tôi") }}</a>
                <a href="{{ url('/doi-ten') }}" class="showPromotion dropdown-item{{ url()->current() == url('/doi-ten') ? ' active disabled' : '' }}"><i class="far fa-user-edit"></i> {{ __("Đổi tên") }}</a>
                <a href="{{ url('/doi-giao-dien') }}" class="showPromotion dropdown-item{{ url()->current() == url('/doi-giao-dien') ? ' active disabled' : '' }}"><i class="far fa-palette"></i> {{ __("Đổi giao diện") }}</a>
                <a href="{{ url('/doi-mat-khau') }}" class="showPromotion dropdown-item{{ url()->current() == url('/doi-mat-khau') ? ' active disabled' : '' }}"><i class="far fa-lock-alt"></i> {{ __("Đổi mật khẩu") }}</a>
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
            <a class="lang dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false"><i class="far fa-language"></i> {{ $localeLabels[$locale] ?? 'Tiếng Việt' }}</a>
            <div class="dropdown-menu dropdown-menu-right shadow">
              <a class="dropdown-item showPromotion{{ $canonicalUrl === $langViUrl ? ' active disabled' : '' }}" href="{{ url($langViUrl) }}"><span class="shadow-sm fi fi-vn"></span> Tiếng Việt</a>
              @if (isset($room->host_id))
              <a class="dropdown-item showPromotion" href="{{ url('/en') }}"><span class="shadow-sm fi fi-us"></span> English</a>
              <a class="dropdown-item showPromotion" href="{{ url('/ja') }}"><span class="shadow-sm fi fi-jp"></span> 日本語</a>
              <a class="dropdown-item showPromotion" href="{{ url('/ko') }}"><span class="shadow-sm fi fi-kr"></span> 한국어</a>
              <a class="dropdown-item showPromotion" href="{{ url('/zh') }}"><span class="shadow-sm fi fi-cn"></span> 中文</a>
              @else
              <a class="dropdown-item showPromotion{{ $canonicalUrl === $langEnUrl ? ' active disabled' : '' }}" href="{{ url($langEnUrl) }}"><span class="shadow-sm fi fi-us"></span> English</a>
              <a class="dropdown-item showPromotion{{ $canonicalUrl === $langJaUrl ? ' active disabled' : '' }}" href="{{ url($langJaUrl) }}"><span class="shadow-sm fi fi-jp"></span> 日本語</a>
              <a class="dropdown-item showPromotion{{ $canonicalUrl === $langKoUrl ? ' active disabled' : '' }}" href="{{ url($langKoUrl) }}"><span class="shadow-sm fi fi-kr"></span> 한국어</a>
              <a class="dropdown-item showPromotion{{ $canonicalUrl === $langZhUrl ? ' active disabled' : '' }}" href="{{ url($langZhUrl) }}"><span class="shadow-sm fi fi-cn"></span> 中文</a>
              @endif
            </div>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</header>
