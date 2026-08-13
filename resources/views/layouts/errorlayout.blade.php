<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="error">
    @include('common.afterBody')
    @include('layouts.partials.header')
    <main>
        @yield('content')
    </main>
    @include('layouts.partials.footer')
</body>
</html>
