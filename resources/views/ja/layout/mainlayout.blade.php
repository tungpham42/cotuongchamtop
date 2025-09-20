<!DOCTYPE html>
<html lang="ja">
  <head>
    @include('ja.layout.partials.head')
  </head>
  <body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.scripts')
    @include('common.themes')
    @include('ja.layout.partials.header')
    @include('ja.layout.partials.adsenseModal')
    @include('ja.layout.partials.shopeeModal')
    <main>
      @yield('aboveContent')
      <div class="sharethis-inline-reaction-buttons"></div>
      @include('common.ads')
      @include('ja.layout.partials.scripts')
      @yield('belowContent')
      @include('ja.layout.partials.adsense')
      @desktop
        @include('ja.layout.partials.fb')
      @enddesktop
    </main>
    @include('ja.layout.partials.footer')
    @include('common.adcash')
  </body>
</html>