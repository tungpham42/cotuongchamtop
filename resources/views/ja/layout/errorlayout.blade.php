<!DOCTYPE html>
<html lang="ja">
  <head>
    @include('en.layout.partials.head')
  </head>
  <body class="error">
    @include('common.afterBody')
    @include('en.layout.partials.header')
    <main>
      @yield('content')
    </main>
    @include('en.layout.partials.footer')
  </body>
</html>