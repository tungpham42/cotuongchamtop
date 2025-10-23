<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WM9GZXN"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<header class="site-header shadow-lg sticky-top">
  <div class="container mx-auto">
    <div class="row align-items-center">
      <a class="navbar-brand small mr-auto my-0 haltPromotion" href="{{ url('') }}"><img src="{{ url('/') }}/img/app-icons/logo.png" class="xiangqi-logo" alt="xiangqi logo"><h1 class="d-inline" style="font-size: inherit !important;"><strong>Cờ tướng</strong></h1>
        @if ($roomCode != '')
        <span id="header-status"></span>
        @endif
      </a>
      <div class="menu-toggle open" title="{{ __('app.navigation.menu') }}"></div>
      <nav class="navbar py-0">
        <ul class="nav navbar-nav">
          <li class="nav-item">
            <a class="home haltPromotion" href="{{ url('') }}"><i class="far fa-house"></i> {{ __('app.navigation.home') }}</a>
          </li>
          <li class="dropdown">
            <a id="dashboardDropdown" class="dashboard room trophy thi-dau dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false"><i class="far fa-trophy-alt"></i> {{ __('app.navigation.compete') }}</a>
            <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="dashboardDropdown">
              <a class="rooms-list haltPromotion dropdown-item{{ url()->current() == url('/sanh-cho') ? ' active disabled' : '' }}" href="{{ url('/sanh-cho') }}"><i class="far fa-list-alt"></i> {{ __('app.navigation.waiting_room') }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/thanh-vien') ? ' active disabled' : '' }}" href="{{ url('/thanh-vien') }}"><i class="far fa-users"></i> {{ __('app.navigation.members') }}</a>
              <a class="setup puzzle haltPromotion dropdown-item{{ url()->current() == url('/tat-ca-the-co') ? ' active disabled' : '' }}" href="{{ url('/tat-ca-the-co') }}"><i class="far fa-puzzle-piece"></i> {{ __('app.navigation.puzzles') }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/thi-dau') ? ' active disabled' : '' }}" href="{{ url('/thi-dau') }}"><i class="far fa-list"></i> {{ __('app.navigation.competing') }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/bang-xep-hang') ? ' active disabled' : '' }}" href="{{ url('/bang-xep-hang') }}"><i class="far fa-star"></i> {{ __('app.navigation.leaderboard') }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/tim-kiem') ? ' active disabled' : '' }}" href="{{ url('/tim-kiem') }}"><i class="far fa-search"></i> {{ __('app.navigation.search_players') }}</a>
              <a class="showPromotion dropdown-item{{ url()->current() == url('/lich-su') ? ' active disabled' : '' }}" href="{{ url('/lich-su') }}"><i class="far fa-archive"></i> {{ __('app.navigation.match_history') }}</a>
              <a target="_blank" class="showPromotion dropdown-item" href="https://diendan.cotuong.top/"><i class="far fa-comments"></i> {{ __('app.navigation.forum') }}</a>
              {{-- <a class="dropdown-item" href="https://blog.cotuong.top/"><i class="far fa-blog"></i> Tin tức</a> --}}
            </div>
          </li>
          <li class="nav-item">
            <a class="showPromotion" target="_blank" href="https://www.facebook.com/groups/HoiChoiCoTuong"><i class="far fa-user-friends"></i> {{ __('app.navigation.facebook_group') }}</a>
          </li>
          @guest
            @if (Route::has('login'))
              <li class="nav-item">
                <a class="showPromotion login" href="{{ route('login') }}"><i class="far fa-sign-in"></i> {{ __('app.navigation.login') }}</a>
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
                <img src="{{ Avatar::create(Auth::user()->name)->setDimension(24)->setFontSize(12) }}" /> {{ Auth::user()->name }}
              </a>

              <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="navbarDropdown">
                <a href="{{ url('/ho-so-cua-toi') }}" class="showPromotion dropdown-item{{ url()->current() == url('/ho-so-cua-toi') ? ' active disabled' : '' }}"><i class="far fa-id-card"></i> {{ __('app.navigation.my_profile') }}</a>
                <a href="{{ url('/doi-ten') }}" class="showPromotion dropdown-item{{ url()->current() == url('/doi-ten') ? ' active disabled' : '' }}"><i class="far fa-user-edit"></i> {{ __('app.navigation.change_name') }}</a>
                <a href="{{ url('/doi-giao-dien') }}" class="showPromotion dropdown-item{{ url()->current() == url('/doi-giao-dien') ? ' active disabled' : '' }}"><i class="far fa-palette"></i> {{ __('app.navigation.change_theme') }}</a>
                <a href="{{ url('/doi-mat-khau') }}" class="showPromotion dropdown-item{{ url()->current() == url('/doi-mat-khau') ? ' active disabled' : '' }}"><i class="far fa-lock-alt"></i> {{ __('app.navigation.change_password') }}</a>
                <a class="dropdown-item" href="{{ route('logout') }}"
                  onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">
                  <i class="far fa-sign-out"></i> {{ __('app.navigation.logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                  @csrf
                </form>
              </div>
            </li>
          @endguest
          <li class="nav-item">
            @include('common.languageSwitcher')
          </li>
        </ul>
      </nav>
    </div>
  </div>
</header>
