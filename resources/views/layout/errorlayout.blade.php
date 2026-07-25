<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layout.partials.head')
</head>
<body class="error">
    @include('common.afterBody')
    @include('layout.partials.header')
    <main>
        @yield('content')
    </main>
    @include('layout.partials.footer')
</body>
</html>
