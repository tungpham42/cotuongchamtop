<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="{{ $bodyClass }}" data-layout="main">
    @include('common.afterBody')
    @include('common.themes')
    @include('common.adsenseTop')
    @include('layouts.partials.header')
    @include('layouts.partials.flashMessages')

    <main>
        {{-- @include('common.xiangqiBanner') --}}
        @yield('aboveContent')
        <div class="sharethis-inline-reaction-buttons"></div>
        @include('common.ads')
        @include('layouts.partials.scripts')
        @yield('belowContent')
    </main>

    @include('layouts.partials.footer')
    @include('common.contactBtn')
    @include('common.onlineCounter')
    @include('layouts.partials.karmaToast')
</body>
</html>
