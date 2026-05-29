<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layout.partials.app.head')
</head>

@php
    $currentPath = request()->path();
    $bodyClassToApply = $bodyClass ?? '';

    // Define route patterns for specific body classes
    $loginRoutes = ['dang-nhap', 'dang-ky', 'quen-mat-khau', 'tao-mat-khau', 'dat-lai-mat-khau*'];
    $dashboardRoutes = ['thi-dau', 'bang-xep-hang', 'lich-su', __('sanh-cho'), 'co-the', 'tim-kiem'];

    if (!$bodyClassToApply) {
        if (request()->is($loginRoutes)) {
            $bodyClassToApply = 'login';
        } elseif (request()->is($dashboardRoutes)) {
            $bodyClassToApply = 'dashboard';
        }
    }
@endphp

<body class="{{ $bodyClassToApply }}">
    @include('common.afterBody')
    <div id="app">
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
