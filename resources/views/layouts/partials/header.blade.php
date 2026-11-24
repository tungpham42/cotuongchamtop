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

    // The 8 items above are shown in three labelled groups instead of one flat list.
    // Every route key in $competeMenu must appear in exactly one group.
    $competeGroups = [
        'Chơi'                => ['room.list', 'tournaments.index', 'app.dashboard'],
        'Cộng đồng'           => ['app.ranking', 'user.list', 'search'],
        'Luyện tập & lưu trữ' => ['puzzle.list', 'app.history'],
    ];
    $competeByKey = collect($competeMenu)->keyBy(fn (array $item): string => $item[0]);

    $resourceMenu = [
        ['article.index',   'far fa-file',         'Bài viết',     'resource'],
        ['games.library',   'far fa-database',     'Thư viện',     'resource'],
    ];

    $vipMenu = [
        ['app.vip', 'fas fa-crown', 'VIP', 'vip'],
    ];

    $accountMenu = [
        ['app.profile',  'far fa-id-card',   'Hồ sơ của tôi',   'profile'],
        ['app.name',     'far fa-user-edit', 'Đổi tên',         'profile'],
        ['app.password', 'far fa-lock-alt',  'Đổi mật khẩu',    'profile'],
        ['app.ui',       'far fa-palette',   'Đổi giao diện',   'profile'],
    ];

    // One short line under each menu label. Wrapped in __() so it can be translated per locale;
    // untranslated locales simply fall back to the Vietnamese text.
    $menuHints = [
        'room.list'         => 'Vào phòng, tìm đối thủ',
        'tournaments.index' => 'Tranh tài, nhận phần thưởng',
        'app.dashboard'     => 'Xem các ván đang diễn ra',
        'app.ranking'       => 'Top kỳ thủ mạnh nhất',
        'user.list'         => 'Làm quen bạn cờ mới',
        'search'            => 'Tìm theo tên kỳ thủ',
        'puzzle.list'       => 'Rèn tính toán mỗi ngày',
        'app.history'       => 'Xem lại các ván đã chơi',
        'article.index'     => 'Kiến thức & chiến thuật',
        'games.library'     => 'Kỳ phổ các danh thủ',
        'app.vip'           => 'Mở khóa quyền lợi riêng',
        'app.profile'       => 'Thành tích & thông tin',
        'app.name'          => 'Tên hiển thị của bạn',
        'app.password'      => 'Giữ tài khoản an toàn',
        'app.ui'            => 'Chọn bàn cờ & màu sắc',
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
    foreach (collect([$competeMenu, $resourceMenu, $vipMenu, $accountMenu])->flatten(1) as $item) {
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

    // Does the matched item live inside this menu? Used to light up the top-level pill (Thi đấu, Tài nguyên, ...)
    // so people always see which section they are in, even while the dropdown is closed.
    $menuHasMatch = fn (array $menu): bool => $matchedMenuUrl !== null
        && collect($menu)->contains(fn (array $item): bool => $normalize(localized_url($item[0])) === $matchedMenuUrl);

    $onHome = $currentUrl === $normalize($homeUrl);
    $onCompete = $menuHasMatch($competeMenu);
    $onResource = $menuHasMatch($resourceMenu);
    $onVip = $menuHasMatch($vipMenu);
    $onAccountPage = $menuHasMatch($accountMenu);

    $supportedLocales = config('locales.supported', []);
    $labels = config('locales.labels', []);
    $flags = config('locales.flags', []);
    $activeLocale = $locale ?? config('locales.default');

    // Per-locale paths of the current page, set by localized_page_data() for every supported locale.
    // Pages that don't use it fall back to each language's home page.
    $langPaths = $langUrls ?? localized_alternate_paths('home');
    $currentPath = $canonicalUrl ?? null;

    // Small helper: one rich dropdown row (icon tile + label + hint).
    $renderItem = function (string $key, string $icon, string $label, string $extraClass) use ($menuHints, $activeClass): string {
        $itemUrl = localized_url($key);
        $hint = $menuHints[$key] ?? null;

        return '<a class="' . ($extraClass ? e($extraClass) . ' ' : '') . 'showPromotion dropdown-item cz-item' . $activeClass($itemUrl) . '" href="' . e($itemUrl) . '">'
            . '<span class="cz-item-ico" aria-hidden="true"><i class="' . e($icon) . '"></i></span>'
            . '<span class="cz-item-text"><b class="cz-item-label">' . e(__($label)) . '</b>'
            . ($hint ? '<small class="cz-item-hint">' . e(__($hint)) . '</small>' : '')
            . '</span></a>';
    };
@endphp
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WM9GZXN"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<header class="site-header cz-header sticky-top" id="siteHeader">
    <div class="cz-bar">

        {{-- Mobile / tablet menu button (hidden on large screens) --}}
        <button type="button" class="cz-burger" id="czBurger" aria-expanded="false" aria-controls="czNav"
                aria-label="{{ __('Trình đơn') }}" data-label-open="{{ __('Trình đơn') }}" data-label-close="{{ __('Đóng') }}">
            <span></span><span></span><span></span>
        </button>

        {{-- Brand: lantern-lit logo coin + name + tagline --}}
        <a class="cz-brand showPromotion" href="{{ $homeUrl }}">
            <img src="{{ asset('img/app-icons/logo.png') }}" class="xiangqi-logo" alt="xiangqi logo" width="46" height="46">
            <div class="cz-brand-text">
                <strong class="cz-brand-name">{{ __("Cờ tướng") }}</strong>
                <em class="cz-brand-tag">{{ __('Chơi · Học · Kết bạn') }}</em>
            </div>
        </a>

        {{-- Main navigation: a floating pill rail on desktop, a slide-down sheet on phones/tablets --}}
        <nav class="navbar cz-nav" id="czNav" aria-label="{{ __('Trình đơn') }}">
            <ul class="cz-menu">
                <li>
                    <a class="cz-link showPromotion{{ $onHome ? ' is-current' : '' }}" href="{{ $homeUrl }}" @if ($onHome) aria-current="page" @endif>
                        <i class="far fa-home-lg-alt"></i> {{ __("Trang chủ") }}
                    </a>
                </li>

                {{-- Thi đấu: a three-column mega menu on desktop, an accordion on mobile --}}
                <li class="dropdown cz-has-mega">
                    <a id="dashboardDropdown" class="cz-link dropdown-toggle{{ $onCompete ? ' is-current' : '' }}" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="far fa-trophy-alt"></i> {{ __("Thi đấu") }}
                    </a>
                    <div class="dropdown-menu cz-mega" aria-labelledby="dashboardDropdown">
                        <div class="cz-panel cz-panel--mega">
                            @foreach ($competeGroups as $groupLabel => $groupKeys)
                                <div class="cz-group">
                                    <div class="cz-group-title">{{ __($groupLabel) }}</div>
                                    @foreach ($groupKeys as $groupKey)
                                        @php [$key, $icon, $label, $extraClass] = $competeByKey[$groupKey]; @endphp
                                        {!! $renderItem($key, $icon, $label, $extraClass) !!}
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </li>

                <li class="dropdown">
                    <a id="resourceDropdown" class="cz-link dropdown-toggle{{ $onResource ? ' is-current' : '' }}" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="far fa-books"></i> {{ __("Tài nguyên") }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="resourceDropdown">
                        <div class="cz-panel">
                            @foreach ($resourceMenu as [$key, $icon, $label, $extraClass])
                                {!! $renderItem($key, $icon, $label, $extraClass) !!}
                            @endforeach
                            <a class="showPromotion dropdown-item cz-item" target="_blank" rel="noopener" href="https://blog.cotuong.top">
                                <span class="cz-item-ico" aria-hidden="true"><i class="far fa-blog"></i></span>
                                <span class="cz-item-text">
                                    <b class="cz-item-label">{{ __("Blog") }}</b>
                                    <small class="cz-item-hint">{{ __('Tin tức & bài hay') }}</small>
                                </span>
                            </a>
                        </div>
                    </div>
                </li>

                <li class="dropdown">
                    <a id="vipDropdown" class="cz-link cz-vip dropdown-toggle{{ $onVip ? ' is-current' : '' }}" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-crown"></i> VIP
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="vipDropdown">
                        <div class="cz-panel">
                            {!! $renderItem('app.vip', 'fas fa-crown', 'VIP', 'vip') !!}
                            {{-- Real href as a fallback; when the chat widget is on the page we open it instead. --}}
                            <a class="showPromotion dropdown-item cz-item cz-item--ai" href="{{ localized_url('ai.normal') }}"
                               onclick="var btn = document.getElementById('ai-chat-btn'); if (btn) { btn.click(); return false; }"
                               title="{{ __('Trợ lý Cờ Tướng AI') }}">
                                <span class="cz-item-ico" aria-hidden="true"><i class="fas fa-robot"></i></span>
                                <span class="cz-item-text">
                                    <b class="cz-item-label">AI Coach</b>
                                    <small class="cz-item-hint">{{ __('Trợ lý Cờ Tướng AI') }}</small>
                                </span>
                            </a>
                        </div>
                    </div>
                </li>

                <li class="dropdown cz-lang">
                    <a class="cz-link cz-lang-toggle dropdown-toggle" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="{{ __('Ngôn ngữ') }}">
                        <span class="shadow-sm fi fi-{{ $flags[$activeLocale] ?? $activeLocale }}"></span>
                        <span class="cz-lang-code">{{ strtoupper($activeLocale) }}</span>
                        <span class="cz-lang-label">{{ $labels[$activeLocale] ?? $labels[config('locales.default')] ?? '' }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right cz-lang-menu">
                        <div class="cz-panel cz-panel--lang">
                            @foreach ($supportedLocales as $code)
                                @php $langPath = $langPaths[$code] ?? localized_path('home', [], $code); @endphp
                                <a class="dropdown-item cz-item cz-item--lang showPromotion{{ $currentPath === $langPath ? ' active' : '' }}" href="{{ url($langPath) }}" hreflang="{{ $code }}" lang="{{ $code }}">
                                    <span class="shadow-sm fi fi-{{ $flags[$code] ?? $code }}"></span>
                                    <span class="cz-item-label">{{ $labels[$code] ?? $code }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </li>
            </ul>
        </nav>

        {{-- Always-visible actions: the big "play" button + account. They stay in the bar on phones too. --}}
        <div class="navbar cz-actions">
            {{-- Dark / light switcher: JS below flips <html data-theme> and remembers the choice. --}}
            <button type="button" class="cz-theme-toggle" id="czThemeToggle" aria-pressed="false"
                    data-label-to-light="{{ __('Chuyển sang giao diện sáng') }}"
                    data-label-to-dark="{{ __('Chuyển sang giao diện tối') }}"
                    aria-label="{{ __('Chuyển sang giao diện sáng') }}" title="{{ __('Chuyển sang giao diện sáng') }}">
                <i class="fas fa-sun cz-ico-sun" aria-hidden="true"></i>
                <i class="fas fa-moon cz-ico-moon" aria-hidden="true"></i>
            </button>

            <a class="cz-cta showPromotion" href="{{ localized_url('room.list') }}">
                <i class="fas fa-chess-board" aria-hidden="true"></i>
                <b class="cz-cta-text">{{ __('Chơi ngay') }}</b>
            </a>

            @guest
                @if (Route::has('login'))
                    <a class="cz-login showPromotion" href="{{ localized_url('login') }}" aria-label="{{ __('Đăng nhập') }}">
                        <i class="far fa-sign-in" aria-hidden="true"></i>
                        <b class="cz-login-text">{{ __('Đăng nhập') }}</b>
                    </a>
                @endif
            @else
                <div class="dropdown cz-user">
                    <a id="navbarDropdown" class="nav-avatar-link dropdown-toggle p-0{{ $onAccountPage ? ' is-current' : '' }}" href="javascript:void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="{{ __('Hồ sơ của tôi') }}" @if ($onAccountPage) aria-current="page" @endif>
                        <img src="{{ Auth::user()->getAvatarUrl(24, 12) }}" class="nav-avatar{{ $onAccountPage ? '' : ' pulse-gold' }}" alt="{{ __('Hồ sơ của tôi') }}" />
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <div class="cz-panel">
                            <div class="cz-me">
                                <img src="{{ Auth::user()->getAvatarUrl(24, 12) }}" class="cz-me-avatar" alt="" />
                                <div class="cz-me-text">
                                    <b class="cz-me-name">{{ Auth::user()->name }}</b>
                                    @if (Auth::user()->isStandard())
                                        <small class="cz-me-badge is-vip"><i class="fas fa-crown"></i> {{ Auth::user()->getVipLabel() }}</small>
                                    @else
                                        <a href="{{ localized_url('app.vip') }}" class="showPromotion cz-me-badge is-upgrade"><i class="fas fa-crown"></i> {{ __("Nâng cấp VIP") }}</a>
                                    @endif
                                </div>
                            </div>
                            <div class="cz-divider"></div>
                            @foreach ($accountMenu as [$key, $icon, $label, $extraClass])
                                {!! $renderItem($key, $icon, $label, $extraClass) !!}
                            @endforeach
                            <div class="cz-divider"></div>
                            <a class="dropdown-item cz-item cz-item--logout" href="{{ localized_url('logout') }}"
                                onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                <span class="cz-item-ico" aria-hidden="true"><i class="far fa-sign-out"></i></span>
                                <span class="cz-item-text"><b class="cz-item-label">{{ __('Logout') }}</b></span>
                            </a>

                            <form id="logout-form" action="{{ localized_url('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            @endguest
        </div>
    </div>
</header>
{{-- Dimmed backdrop behind the mobile menu sheet (CSS shows it only while the header has .is-open). --}}
<div class="cz-scrim" id="czScrim" aria-hidden="true"></div>

<script>
(function () {
    var header = document.getElementById('siteHeader');
    var burger = document.getElementById('czBurger');
    var scrim = document.getElementById('czScrim');
    if (!header || !burger) { return; }

    var bar = header.querySelector('.cz-bar');

    function isCollapsed() { return header.classList.contains('is-collapsed'); }

    function closeDropdowns() {
        // Bootstrap 4 dropdowns: collapse anything left open so the sheet reopens clean.
        if (window.jQuery && jQuery.fn.dropdown) {
            jQuery(header).find('[data-toggle="dropdown"][aria-expanded="true"]').dropdown('hide');
        }
    }

    function setOpen(open) {
        header.classList.toggle('is-open', open);
        document.body.classList.toggle('cz-lock', open);
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        burger.setAttribute('aria-label', burger.getAttribute(open ? 'data-label-close' : 'data-label-open'));
        if (!open) { closeDropdowns(); }
    }

    burger.addEventListener('click', function () {
        setOpen(!header.classList.contains('is-open'));
    });

    if (scrim) { scrim.addEventListener('click', function () { setOpen(false); }); }

    // Tapping a real link inside the sheet closes it (the page is about to change anyway;
    // this also covers same-page links and the AI Coach button).
    document.getElementById('czNav').addEventListener('click', function (e) {
        var link = e.target.closest('a');
        if (!link || link.getAttribute('data-toggle') === 'dropdown') { return; }
        if (isCollapsed()) { setOpen(false); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && header.classList.contains('is-open')) {
            setOpen(false);
            burger.focus();
        }
    });

    // Responsive fit, measured instead of guessed. Labels differ a lot between the 10 languages
    // (Wettkampf, Соревнование, Jouer maintenant, 日本語 ...), so a fixed pixel breakpoint can never be right
    // for all of them. We try three layouts in order and keep the first one that fits the bar:
    //   full  ->  .is-tight (no tagline / link icons, tighter padding)  ->  .is-collapsed (burger + sheet).
    var SLACK = 16;
    function barFits() {
        var cs = getComputedStyle(bar);
        var gap = parseFloat(cs.columnGap) || parseFloat(cs.gap) || 0;
        var need = (parseFloat(cs.paddingLeft) || 0) + (parseFloat(cs.paddingRight) || 0);
        var shown = 0;
        for (var i = 0; i < bar.children.length; i++) {
            var w = bar.children[i].getBoundingClientRect().width;
            if (w > 0) { need += w; shown++; }
        }
        need += gap * Math.max(0, shown - 1);
        return need + SLACK <= bar.clientWidth;
    }
    var fitQueued = false;
    function fit() {
        fitQueued = false;
        var wasCollapsed = isCollapsed();
        header.classList.add('is-fitting'); // no transitions while the layout switches (no flash of the desktop menu)
        // Measure at the largest size (the logo shrinks while scrolled) so the layout does not flip on scroll.
        var wasScrolled = header.classList.contains('is-scrolled');
        header.classList.remove('is-scrolled', 'is-tight', 'is-collapsed');
        var tier = '';
        if (!barFits()) {
            header.classList.add('is-tight');
            tier = barFits() ? 'is-tight' : 'is-collapsed';
            header.classList.remove('is-tight');
        }
        if (tier) { header.classList.add(tier); }
        if (wasScrolled) { header.classList.add('is-scrolled'); }
        void header.offsetWidth; // settle the new layout while transitions are off
        header.classList.remove('is-fitting');
        if (wasCollapsed && tier !== 'is-collapsed') { setOpen(false); }
    }
    function queueFit() {
        if (fitQueued) { return; }
        fitQueued = true;
        (window.requestAnimationFrame || window.setTimeout)(fit);
    }

    fit();
    window.addEventListener('resize', queueFit);
    window.addEventListener('orientationchange', queueFit);
    window.addEventListener('load', queueFit);
    // Web fonts change text widths after first paint.
    if (document.fonts && document.fonts.ready) { document.fonts.ready.then(queueFit); }

})();
</script>
{{-- Dark / light mode switcher. The saved choice is applied before first paint by the snippet in head.blade.php;
     this script only wires up the button, keeps tabs in sync and updates the labels. --}}
<script>
(function () {
    var KEY = 'xq-theme';
    var root = document.documentElement;
    var toggle = document.getElementById('czThemeToggle');
    var themeColor = document.querySelector('meta[name="theme-color"]');
    var COLORS = { dark: '#7a291c', light: '#b4472f' };

    function stored() {
        try { var v = localStorage.getItem(KEY); return (v === 'light' || v === 'dark') ? v : null; }
        catch (e) { return null; }
    }
    function current() { return root.getAttribute('data-theme') === 'light' ? 'light' : 'dark'; }

    function paint(theme) {
        root.setAttribute('data-theme', theme);
        if (themeColor) { themeColor.setAttribute('content', COLORS[theme]); }
        if (toggle) {
            // The button always describes the action it will perform.
            var label = toggle.getAttribute(theme === 'light' ? 'data-label-to-dark' : 'data-label-to-light');
            toggle.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
            toggle.setAttribute('aria-label', label);
            toggle.setAttribute('title', label);
        }
    }

    function apply(theme, persist, animate) {
        if (animate) {
            root.classList.add('theme-switching');
            window.setTimeout(function () { root.classList.remove('theme-switching'); }, 450);
        }
        paint(theme);
        if (persist) { try { localStorage.setItem(KEY, theme); } catch (e) {} }
        // Lets other scripts (board, charts, embeds) react: document.addEventListener('themechange', ...)
        document.dispatchEvent(new CustomEvent('themechange', { detail: { theme: theme } }));
    }

    paint(stored() || current());

    if (toggle) {
        toggle.addEventListener('click', function () {
            apply(current() === 'light' ? 'dark' : 'light', true, true);
        });
    }

    // Another tab changed the theme.
    window.addEventListener('storage', function (e) {
        if (e.key === KEY && (e.newValue === 'light' || e.newValue === 'dark')) { apply(e.newValue, false, true); }
    });
})();
</script>
{{-- The brand name in the header is a <strong>, so every page needs exactly one real <h1>.
     Pages that render their own visible <h1> opt out with @section('hasPageH1', '1');
     all other pages get a screen-reader/SEO heading that matches their <title>. --}}
@sectionMissing('hasPageH1')
    @php
        $pageH1 = url()->current() == localized_url('ai.home')
            ? __('Cờ Tướng Online Miễn Phí – Chơi Cờ Tướng 2 Người, Với Máy & Cờ Thế')
            : (! empty($headTitle) ? __($headTitle) : __('Cờ tướng'));
    @endphp
    <h1 class="sr-only">{{ $pageH1 }}</h1>
@endif
