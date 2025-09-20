<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layout.partials.app.head')
</head>
@if (isset($bodyClass))
<body class="{{ $bodyClass }}">
@elseif (url()->current() == url('/dang-nhap'))
<body class="login">
@elseif (url()->current() == url('/dang-ky'))
<body class="register">
@elseif (url()->current() == url('/quen-mat-khau'))
<body class="login">
@elseif (url()->current() == url('/tao-mat-khau'))
<body class="login">
@elseif (str_contains(url()->current(), url('/dat-lai-mat-khau').'/'))
<body class="login">
@elseif (url()->current() == url('/thi-dau') || url()->current() == url('/bang-xep-hang') || url()->current() == url('/lich-su') || url()->current() == url('/sanh-cho') || url()->current() == url('/co-the') || url()->current() == url('/tim-kiem'))
<body class="dashboard">
@else
<body>
@endif
    @include('common.afterBody')
    @include('layout.partials.adsenseModal')
    <div id="app">
        @include('layout.partials.app.header')
        <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ URL::to('/') }}" >
        @include('common.themes')

        <main class="py-5 bg-dark">
            <script>
                $(document).ajaxStart(function(){
                    $('body').addClass('waiting');
                }).ajaxComplete(function(){
                    $('body').removeClass('waiting');
                })
            </script>
            @yield('content')
        </main>
        @include('layout.partials.app.footer')
    </div>
    @include('common.contactBtn')
</body>
</html>
