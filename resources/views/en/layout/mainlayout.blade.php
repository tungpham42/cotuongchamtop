<!DOCTYPE html>
<html lang="en">
  <head>
    @include('en.layout.partials.head')
  </head>
  <body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.scripts')
    @include('common.themes')
    @include('en.layout.partials.header')
    @include('en.layout.partials.adsenseModal')
    @include('en.layout.partials.shopeeModal')
    <main>
      @yield('aboveContent')
      <div class="sharethis-inline-reaction-buttons"></div>
      @include('common.ads')
      @include('en.layout.partials.scripts')
      @yield('belowContent')
      @include('en.layout.partials.adsense')
      @desktop
        @include('en.layout.partials.fb')
      @enddesktop
    </main>
    @include('en.layout.partials.footer')
    @include('common.adcash')
  </body>
</html>