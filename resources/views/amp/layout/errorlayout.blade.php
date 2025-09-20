<!DOCTYPE html>
<html amp lang="vi">
  <head>
    @include('amp.layout.partials.head')
  </head>
  <body class="error">
    @include('amp.layout.partials.header')
    <main>
      @yield('content')
    </main>
    @include('amp.layout.partials.footer')
  </body>
</html>