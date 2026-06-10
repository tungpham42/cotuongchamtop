<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layout.partials.app.head')
</head>

@php
    $bodyClassToApply = $bodyClass ?? '';

    if (!$bodyClassToApply) {
        // Check using route names (with wildcard for locales) instead of hardcoded paths
        if (request()->routeIs('login', '*.login', 'register', '*.register', 'password.*', '*.password.*')) {
            $bodyClassToApply = 'login';
        } elseif (request()->routeIs('app.dashboard', '*.app.dashboard', 'app.ranking', '*.app.ranking', 'app.history', '*.app.history', 'room.list', '*.room.list', 'puzzle.list', '*.puzzle.list', 'search', '*.search')) {
            $bodyClassToApply = 'dashboard';
        }
    }
@endphp

<body class="{{ $bodyClassToApply }}">
    @include('common.afterBody')
    <div id="app">
        @include('common.adsenseTop')
        @include('layout.partials.app.header')
        <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}" >
        @include('common.themes')

        <main class="py-5 bg-dark">
            <script>
                // Modernized AJAX global loading state
                $(document).on({
                    ajaxStart: () => $('body').addClass('waiting'),
                    ajaxComplete: () => $('body').removeClass('waiting')
                });
            </script>
            @yield('content')
        </main>

        @include('layout.partials.app.footer')
    </div>
    @include('common.contactBtn')
</body>
</html>
