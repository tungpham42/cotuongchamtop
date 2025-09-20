<!DOCTYPE html>
<html amp lang="en">
  <head>
    @include('amp.en.layout.partials.head')
  </head>
  <body class="error">
    @include('amp.en.layout.partials.header')
    <main>
      @yield('content')
    </main>
    @include('amp.en.layout.partials.footer')
  </body>
</html>