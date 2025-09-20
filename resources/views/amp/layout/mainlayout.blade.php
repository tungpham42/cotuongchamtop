<!DOCTYPE html>
<html amp lang="vi">
  <head>
    @include('amp.layout.partials.head')
  </head>
  <body class="{{ $bodyClass }} py-0">
    {{-- @include('amp.layout.partials.header') --}}
    <main>
      @yield('aboveContent')
      @include('amp.layout.partials.scripts')
      @yield('belowContent')
      {{-- @include('amp.layout.partials.adsense') --}}
      {{-- @include('amp.layout.partials.fb') --}}
    </main>
    {{-- @include('amp.layout.partials.footer') --}}
  </body>
</html>