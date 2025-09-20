<!DOCTYPE html>
<html lang="vi">
  <head>
    @include('layout.partials.head')
  </head>
  <body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.scripts')
    @include('common.themes')
    @include('layout.partials.header')
    @include('layout.partials.adsenseModal')
    @include('layout.partials.shopeeModal')
    @if (session('status'))
      <div class="container">
        <div class="alert alert-success" role="alert">
          {{ session('status') }}
        </div>
      </div>
    @endif
    @if (session('success'))
      <div class="container">
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
      </div>
    @endif
    @if ($errors->any())
      <div class="container">
        <div class="alert alert-warning">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif
    <main>
      @include('layout.partials.adsense')
      @yield('aboveContent')
      <div class="sharethis-inline-reaction-buttons"></div>
      @include('common.ads')
      @include('layout.partials.scripts')
      @yield('belowContent')
      @include('layout.partials.adsense')
      @desktop
        @include('layout.partials.fb')
      @enddesktop
    </main>
    @include('layout.partials.footer')
    @include('common.contactBtn')
  </body>
</html>