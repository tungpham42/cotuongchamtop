<!DOCTYPE html>
<html amp lang="en">
  <head>
    @include('amp.en.layout.partials.head')
  </head>
  <body class="{{ $bodyClass }} py-0">
    {{-- @include('amp.en.layout.partials.header') --}}
    <main>
      @yield('aboveContent')
      @include('amp.en.layout.partials.scripts')
      @yield('belowContent')
      {{-- @include('amp.en.layout.partials.adsense') --}}
      {{-- @include('amp.en.layout.partials.fb') --}}
    </main>
    {{-- @include('amp.en.layout.partials.footer') --}}
  </body>
</html>