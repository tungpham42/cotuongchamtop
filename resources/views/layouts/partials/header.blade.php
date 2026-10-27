@php
    $homeUrl = localized_url('home');

    // localized_url() already returns absolute URLs, so it compares directly with url()->current().
    $activeClass = fn (string $target): string => url()->current() === $target ? ' active disabled' : '';

    // [route key, icon, label, extra css classes]
    $competeMenu = [
        ['room.list',         'far fa-list-alt',     'Sảnh chờ',        'dashboard'],
        ['tournaments.index', 'far fa-swords',       'Giải đấu',        'dashboard'],
        ['user.list',         'far fa-users',        'Thành viên',      'dashboard'],
        ['puzzle.list',       'far fa-puzzle-piece', 'Cờ thế',          'dashboard'],
        ['app.dashboard',     'far fa-list',         'Đang thi đấu',    'dashboard'],
        ['app.ranking',       'far fa-star',         'Bảng xếp hạng',   'dashboard'],
        ['search',            'far fa-search',       'Tìm kiếm kỳ thủ', 'dashboard'],
        ['app.history',       'far fa-archive',      'Lịch sử thi đấu', 'dashboard'],
    ];

    $accountMenu = [
        ['app.profile',  'far fa-id-card',   'Hồ sơ của tôi',   'profile'],
        ['app.name',     'far fa-user-edit', 'Đổi tên',         'profile'],
        ['app.password', 'far fa-lock-alt',  'Đổi mật khẩu',    'profile'],
        ['app.ui',       'far fa-palette',   'Đổi giao diện',   'profile'],
    ];

    $supportedLocales = config('locales.supported', []);
    $labels = config('locales.labels', []);
    $flags = config('locales.flags', []);

    // Per-locale paths of the current page, set by localized_page_data() for every supported locale.
    // Pages that don't use it fall back to each language's home page.
    $langPaths = $langUrls ?? localized_alternate_paths('home');
    $currentPath = $canonicalUrl ?? null;
@endphp
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WM9GZXN"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<header class="site-header shadow-lg sticky-top">
    <div class="container mx-auto">
        <div class="row align-items-center">

            <a class="navbar-brand small mr-auto my-0 showPromotion" href="{{ $homeUrl }}">
                <img src="{{ asset('img/app-icons/logo.png') }}" class="xiangqi-logo" alt="xiangqi logo">
                <h1 class="d-inline" style="font-size: inherit !important;"><strong>{{ __("Cờ tướng") }}</strong></h1>
            </a>

            <div class="menu-toggle open" title="Trình đơn"></div>

            <nav class="navbar py-0">
                <ul class="nav navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="home showPromotion" href="{{ $homeUrl }}"><i class="far fa-home-lg-alt"></i> {{ __("Trang chủ") }}</a>
                    </li>

                    <li class="dropdown">
                        <a id="dashboardDropdown" class="dashboard room trophy thi-dau dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="far fa-trophy-alt"></i> {{ __("Thi đấu") }}
                        </a>
                        <div class="scrollable-menu dropdown-menu dropdown-menu-right shadow" aria-labelledby="dashboardDropdown">
                            @foreach ($competeMenu as [$key, $icon, $label, $extraClass])
                                @php $itemUrl = localized_url($key); @endphp
                                <a class="{{ $extraClass ? $extraClass . ' ' : '' }}showPromotion dropdown-item{{ $activeClass($itemUrl) }}" href="{{ $itemUrl }}"><i class="{{ $icon }}"></i> {{ __($label) }}</a>
                            @endforeach
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="library showPromotion" href="{{ localized_url('games.library') }}"><i class="far fa-database"></i> {{ __("Thư viện") }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="showPromotion" target="_blank" href="https://blog.cotuong.top"><i class="far fa-blog"></i> {{ __("Blog") }}</a>
                    </li>

                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item ml-2">
                                <a class="showPromotion login" href="{{ localized_url('login') }}"><i class="far fa-sign-in"></i> {{ __('Đăng nhập') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="dropdown ml-2">
                            <a id="navbarDropdown" class="profile dropdown-toggle p-0" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false">
                                <img src="{{ Auth::user()->getAvatarUrl(24, 12) }}" class="shadow-sm pulse-gold" style="width: 32px; height: 32px; object-fit: cover; border-radius: 50%; border: 2px solid var(--royal-gold);" />
                            </a>

                            <div class="scrollable-menu dropdown-menu dropdown-menu-right shadow" aria-labelledby="navbarDropdown">
                                @if (Auth::user()->isStandard())
                                    <span class="dropdown-item text-success"><i class="far fa-crown"></i> {{ __("Standard (ẩn quảng cáo)") }}</span>
                                @else
                                    <a href="{{ localized_url('app.profile') }}#standard-plan" class="showPromotion dropdown-item">
                                        <i class="far fa-crown"></i> {{ __("Nâng cấp Standard") }}
                                    </a>
                                @endif
                                <div class="dropdown-divider"></div>
                                @foreach ($accountMenu as [$key, $icon, $label, $extraClass])
                                    @php $itemUrl = localized_url($key); @endphp
                                    <a href="{{ $itemUrl }}" class="{{ $extraClass ? $extraClass . ' ' : '' }}showPromotion dropdown-item{{ $activeClass($itemUrl) }}"><i class="{{ $icon }}"></i> {{ __($label) }}</a>
                                @endforeach
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ localized_url('logout') }}"
                                    onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                                    <i class="far fa-sign-out"></i> {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ localized_url('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest

                    <li class="dropdown language-switcher ml-2 pl-2">
                        <a class="lang dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="far fa-language"></i> {{ $labels[$locale] ?? $labels[config('locales.default')] ?? '' }}
                        </a>
                        <div class="scrollable-menu dropdown-menu dropdown-menu-right shadow">
                            @foreach ($supportedLocales as $code)
                                @php $langPath = $langPaths[$code] ?? localized_path('home', [], $code); @endphp
                                <a class="dropdown-item showPromotion{{ $currentPath === $langPath ? ' active disabled' : '' }}" href="{{ url($langPath) }}">
                                    <span class="shadow-sm fi fi-{{ $flags[$code] ?? $code }} mr-2"></span> {{ $labels[$code] ?? $code }}
                                </a>
                            @endforeach
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>
