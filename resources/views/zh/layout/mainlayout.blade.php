<!DOCTYPE html>
<html lang="zh">
  <head>
    @include('zh.layout.partials.head')
  </head>
  <body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.scripts')
    @include('common.themes')
    @include('zh.layout.partials.header')
    @include('zh.layout.partials.adsenseModal')
    @include('zh.layout.partials.shopeeModal')
    <main>
      @yield('aboveContent')
      <div class="sharethis-inline-reaction-buttons"></div>
      @include('common.ads')
      @include('zh.layout.partials.scripts')
      @yield('belowContent')
      @include('zh.layout.partials.adsense')
      @desktop
        @include('zh.layout.partials.fb')
      @enddesktop
    </main>
    @include('zh.layout.partials.footer')
    @include('common.adcash')
  </body>
</html>