@php
    $homeUrl = localized_url('home');

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

    $resourceMenu = [
        ['article.index',   'far fa-file',         'Bài viết',     'resource'],
        ['games.library',   'far fa-database',     'Thư viện',     'resource'],
    ];

    $accountMenu = [
        ['app.profile',  'far fa-id-card',   'Hồ sơ của tôi',   'profile'],
        ['app.name',     'far fa-user-edit', 'Đổi tên',         'profile'],
        ['app.password', 'far fa-lock-alt',  'Đổi mật khẩu',    'profile'],
        ['app.ui',       'far fa-palette',   'Đổi giao diện',   'profile'],
    ];

    // Strip trailing slashes so "/vi/puzzles/" and "/vi/puzzles" compare equal.
    $normalize = fn (string $url): string => rtrim($url, '/');
    $currentUrl = $normalize(url()->current());

    // Detail pages whose URL does NOT sit under their menu item's URL, keyed by the menu item's route key.
    // Each value is a list of route keys from config('locales.paths'). The part of the child path before
    // its first "{placeholder}" is used as the match prefix, e.g. "the-co/{slug}" -> "/the-co".
    $childRoutes = [
        'room.list'         => ['room.host', 'room.guest', 'room.random', 'room.watch', 'room.red', 'room.black'],
        'puzzle.list'       => ['puzzle.setup', 'puzzle.board', 'puzzle.rating'],
        'user.list'         => ['app.player'],
        'tournaments.index' => ['tournaments.show'],
        'article.index'     => ['article.show'],
        'games.library'     => ['games.show'],
    ];
    $configuredPaths = config('locales.paths', []);

    // Every way a menu item can match: its own URL, plus the URL prefix of each of its child routes.
    $menuMatchers = [];
    foreach (collect([$competeMenu, $resourceMenu, $accountMenu])->flatten(1) as $item) {
        $menuUrl = $normalize(localized_url($item[0]));
        $menuMatchers[] = ['menu' => $menuUrl, 'match' => $menuUrl];

        foreach ($childRoutes[$item[0]] ?? [] as $childKey) {
            if (! isset($configuredPaths[$childKey])) {
                continue;
            }

            $prefix = rtrim(\Illuminate\Support\Str::before(localized_path($childKey), '{'), '/');

            if ($prefix !== '') {
                $menuMatchers[] = ['menu' => $menuUrl, 'match' => $normalize(url($prefix))];
            }
        }
    }

    // The single best-matching menu item for the current page. A matcher hits when it equals the current
    // URL or is a parent path of it ("/the-co" hits "/the-co/123"). The trailing "/" in the check stops
    // "/user" from hitting "/users". If several hit, the longest, most specific one wins, so only one
    // item is ever highlighted.
    $bestMatcher = collect($menuMatchers)
        ->filter(fn (array $m): bool => $currentUrl === $m['match'] || str_starts_with($currentUrl, $m['match'] . '/'))
        ->sortByDesc(fn (array $m): int => strlen($m['match']))
        ->first();

    $matchedMenuUrl = $bestMatcher['menu'] ?? null;

    // The matched menu item (the exact page or any of its child pages) always gets "active".
    $activeClass = fn (string $target): string => $normalize($target) === $matchedMenuUrl ? ' active' : '';

    // True on any account page or its children. The avatar toggle uses it for its own "current page" ring,
    // because the underline that text links get makes no sense on a round image.
    $onAccountPage = $matchedMenuUrl !== null
        && collect($accountMenu)->contains(fn (array $item): bool => $normalize(localized_url($item[0])) === $matchedMenuUrl);

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

                    <li class="dropdown">
                        <a id="resourceDropdown" class="resource dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="far fa-books"></i> {{ __("Tài nguyên") }}
                        </a>
                        <div class="scrollable-menu dropdown-menu dropdown-menu-right shadow" aria-labelledby="resourceDropdown">
                            @foreach ($resourceMenu as [$key, $icon, $label, $extraClass])
                                @php $itemUrl = localized_url($key); @endphp
                                <a class="{{ $extraClass ? $extraClass . ' ' : '' }}showPromotion dropdown-item{{ $activeClass($itemUrl) }}" href="{{ $itemUrl }}"><i class="{{ $icon }}"></i> {{ __($label) }}</a>
                            @endforeach
                            <a class="showPromotion dropdown-item" target="_blank" href="https://blog.cotuong.top"><i class="far fa-blog"></i> {{ __("Blog") }}</a>
                        </div>
                    </li>

                    <li class="dropdown">
                        <a id="vipDropdown" class="vip dropdown-toggle font-weight-bold" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false" style="color: #d4af37 !important;">
                            <i class="fas fa-crown text-warning"></i> VIP
                        </a>
                        <div class="scrollable-menu dropdown-menu dropdown-menu-right shadow" aria-labelledby="vipDropdown">
                            <a class="showPromotion vip dropdown-item{{ $activeClass(localized_url('app.vip')) }}" href="{{ localized_url('app.vip') }}">
                                <i class="fas fa-crown text-warning"></i> {{ __("VIP") }}
                            </a>
                            <a class="showPromotion dropdown-item text-danger font-weight-bold" href="javascript:void(0);" onclick="var btn = document.getElementById('ai-chat-btn'); if(btn) { btn.click(); } else { window.location.href='{{ localized_url('ai.normal') }}'; }" title="{{ __('Trợ lý Cờ Tướng AI') }}">
                                <i class="fas fa-robot text-danger"></i> AI Coach
                            </a>
                        </div>
                    </li>

                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item ml-2">
                                <a class="showPromotion login" href="{{ localized_url('login') }}"><i class="far fa-sign-in"></i> {{ __('Đăng nhập') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="dropdown ml-2">
                            <a id="navbarDropdown" class="profile nav-avatar-link dropdown-toggle p-0{{ $onAccountPage ? ' is-current' : '' }}" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-expanded="false" @if ($onAccountPage) aria-current="page" @endif>
                                <img src="{{ Auth::user()->getAvatarUrl(24, 12) }}" class="nav-avatar{{ $onAccountPage ? '' : ' pulse-gold' }}" alt="{{ __('Hồ sơ của tôi') }}" />
                            </a>

                            <div class="scrollable-menu dropdown-menu dropdown-menu-right shadow" aria-labelledby="navbarDropdown">
                                @if (Auth::user()->isStandard())
                                    <span class="dropdown-item text-success"><i class="fas fa-crown text-warning"></i> {{ Auth::user()->getVipLabel() }}</span>
                                @else
                                    <a href="{{ localized_url('app.vip') }}" class="showPromotion dropdown-item text-warning font-weight-bold">
                                        <i class="fas fa-crown text-warning"></i> {{ __("Nâng cấp VIP") }}
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
                                <a class="dropdown-item showPromotion{{ $currentPath === $langPath ? ' active' : '' }}" href="{{ url($langPath) }}">
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
