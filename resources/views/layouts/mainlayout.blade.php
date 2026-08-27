<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.themes')
    @include('common.adsenseTop')
    @include('layouts.partials.header')
    @if (session('status'))
        <div class="container">
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="container">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if ($errors->any())
        <div class="container">
            <div class="alert alert-warning">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    <main>
        @include('common.xiangqiBanner')
        @yield('aboveContent')
        <div class="sharethis-inline-reaction-buttons"></div>
        @include('common.ads')
        @include('layouts.partials.scripts')
        @yield('belowContent')
        {{-- @desktop
            @include('layouts.partials.fb')
        @enddesktop --}}
    </main>
    @include('layouts.partials.footer')
    @include('common.contactBtn')
    @include('common.onlineCounter')
    @if (session('karma_earned'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof showKarmaBootbox === 'function') {
                    showKarmaBootbox(@json(session('karma_earned')));
                }
            });
        </script>
    @endif
</body>
</html>
