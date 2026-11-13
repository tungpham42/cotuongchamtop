@php
    // Body class: explicit $bodyClass wins, otherwise derive it from the route name
    // (wildcards cover every locale prefix).
    $bodyClassToApply = $bodyClass ?? '';

    if (!$bodyClassToApply) {
        if (request()->routeIs('login', '*.login', 'register', '*.register', 'password.*', '*.password.*')) {
            $bodyClassToApply = 'login';
        } elseif (request()->routeIs('app.dashboard', '*.app.dashboard', 'app.ranking', '*.app.ranking', 'app.history', '*.app.history', 'room.list', '*.room.list', 'puzzle.list', '*.puzzle.list', 'search', '*.search')) {
            $bodyClassToApply = 'dashboard';
        }
    }
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.app.head')
</head>
<body class="{{ $bodyClassToApply }}" data-layout="app">
    @include('common.afterBody')

    <div id="app">
        @include('common.adsenseTop')
        @include('layouts.partials.header')
        <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}">
        @include('common.themes')

        {{-- No bg-dark here: the xiangqi artwork on <body> must show through. --}}
        <main class="py-5">
            <script>
                // Global AJAX loading state
                $(document).on({
                    ajaxStart: () => $('body').addClass('waiting'),
                    ajaxComplete: () => $('body').removeClass('waiting')
                });
            </script>
            @yield('content')
        </main>

        @include('layouts.partials.app.footer')
    </div>

    @include('common.contactBtn')
    @include('common.onlineCounter')
    @include('layouts.partials.karmaToast')
</body>
</html>
