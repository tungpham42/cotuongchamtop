<!DOCTYPE html>
<html lang="ko">
  <head>
    @include('ko.layout.partials.head')
  </head>
  <body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.scripts')
    @include('ko.layout.partials.header')
    {{-- @include('ko.layout.partials.puzzlesBtn') --}}
    @include('ko.layout.partials.adsenseModal')
    @include('ko.layout.partials.shopeeModal')
    <main>
      <div class="container-fluid game px-0" itemscope itemtype="http://schema.org/Game">
        <div class="container {{ isset($board) ? 'px-3 pb-0 pt-3' : 'p-3' }}">
          <audio id="nuoc-co">
            <source src="{{ $cdnUrl }}/sound/nuocCo.mp3" type="audio/mpeg">
            <source src="{{ $cdnUrl }}/sound/nuocCo.wav" type="audio/wav">
            Your browser does not support the audio element.
          </audio>
          <audio id="het-tran">
            <source src="{{ $cdnUrl }}/sound/hetTran.mp3" type="audio/mpeg">
            <source src="{{ $cdnUrl }}/sound/hetTran.wav" type="audio/wav">
            Your browser does not support the audio element.
          </audio>
          <div class="row">
            @include('ko.layout.partials.findMatch')
          </div>
          <div class="row">
            <div class="col-12 text-center">
              @include('common.topAds')
              @yield('aboveBoard')
            </div>
          </div>
          @if ( $roomCode == '' )
          <div class="row">
            <div class="col-12">
              <div id="ban-co" class="mx-auto h-auto"></div>
            </div>
          </div>
          @else
          <div class="row">
            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
              <div id="ban-co" class="mx-auto mr-lg-0 h-auto"></div>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mt-lg-0 mt-md-5 mt-sm-5 mt-xs-5">
              @include('ko.layout.partials.comments')
              @include('common.sideAds')
            </div>
          </div>
          @endif
          <div class="row">
            <div class="col-12">
              @if ( !isset($board) && $roomCode == '' )
              <div class="dropup mx-auto text-center my-3">
                <button class="btn btn-danger btn-lg dropdown-toggle pulse-red" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span data-toggle="tooltip" data-placement="top" title="방에서 누군가와 놀기"><i class="fad fa-gamepad-alt"></i> 온라인으로 재생</span>
                </button>
                @include('common.volumeBtn')
                @include('common.tourBtn')
                {{-- <a class="btn btn-dark text-light btn-lg stopPromotion shopee-link" href="https://shopee.vn/tungpham42" target="_blank"><i class="fad fa-shopping-cart"></i> 가게</a> --}}
                <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ URL::to('/') }}/bang/{{ md5(time()) }}">
                  <a data-toggle="tooltip" data-placement="bottom" title="암호로 재생" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> 사적인</a>
                  @if ($randomRoom != null)
                  <a data-toggle="tooltip" data-placement="bottom" title="임의의 공용 룸에서 재생" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ URL::to('/') }}/bang/{{ $randomRoom['code'] }}/mujag-wiui"><i class="fas fa-random text-dark"></i> 무작위의</a>
                  <a data-toggle="tooltip" data-placement="bottom" title="대기자 명단" id="room-list" class="dropdown-item rooms-list" style="cursor: pointer !important;" href="{{ URL::to('/bang-moglog') }}"><i class="fas fa-list-alt text-dark"></i> 방 목록</a>
                  @endif
                </div>
              </div>
              @elseif ( $roomCode != '' )
              <p class="w-100 text-center my-3">
                <a class="btn btn-danger text-light btn-lg stopPromotion mx-auto pulse-red rooms-list" href="{{ URL::to('/bang-moglog') }}"><i class="fad fa-chevron-circle-left"></i> 대기자 목록으로 돌아가기</a>
                @include('common.volumeBtn')
                @include('common.tourBtn')
              </p>
              @endif
              <p class="w-100 text-center m-0">
                <span class="rounded p-0" id="game-status"></span>
              </p>
              <p class="w-100 text-center mx-0 mb-0 mt-2">
                <span class="rounded d-none" id="game-over"><i class="fad fa-flag-checkered"></i> 게임 오버</span>
              </p>
              <div class="sharethis-inline-reaction-buttons"></div>
              @include('common.ads')
              @yield('aboveContent')
              <div class="row">
                <input type="hidden" name="FEN" id="FEN" >
                <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ URL::to('/') }}" >
                @include('common.themes')
                @include('ko.layout.partials.scripts')
                @if ( !isset($board) )
                  @include('ko.layout.partials.rules')
                @endif
                @yield('belowContent')
                @if ( !isset($board) )
                <script>
                const ratio = $('#ban-co').height() / $('#ban-co').width();
                function adjustBoard() {
                  const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
                  width = ($(window).height() - 192) / ratio;
                  if ($(window).width() >= $(window).height() && $(window).height() < 576) {
                    width = ($(window).height() - 50) / ratio;
                  }
                  width = Math.min(width, $('header > .container').width());
                  height = width * ratio;
                  $('#ban-co').css({'width': width});
                  board.resize();
                }
                // adjustBoard();
                // $(window).on('load resize', adjustBoard);
                // $(document).ready(adjustBoard);
                $('#share-board').on('click auxclick', function(e){
                  e.preventDefault();
                  $('#AdSenseModal').attr('data-url', $(this).attr('href') + '/' + game.fen()).modal('show');
                });
                </script>
                @include('common.volume')
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
      @include('ko.layout.partials.fb')
    </main>
    @include('ko.layout.partials.footer')
    @include('common.adcash')
  </body>
</html>