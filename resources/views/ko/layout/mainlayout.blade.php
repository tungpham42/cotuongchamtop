<!DOCTYPE html>
<html lang="ko">
  <head>
    @include('ko.layout.partials.head')
  </head>
  <body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.scripts')
    @include('common.themes')
    @include('ko.layout.partials.header')
    @include('ko.layout.partials.adsenseModal')
    @include('ko.layout.partials.shopeeModal')
    <main>
      @yield('aboveContent')
      <div class="sharethis-inline-reaction-buttons"></div>
      @include('common.ads')
      @include('ko.layout.partials.scripts')
      @yield('belowContent')
      @include('ko.layout.partials.adsense')
      @desktop
        @include('ko.layout.partials.fb')
      @enddesktop
    </main>
    @include('ko.layout.partials.footer')
    @include('common.adcash')
  </body>
</html>