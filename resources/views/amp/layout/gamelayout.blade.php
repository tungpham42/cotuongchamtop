<!DOCTYPE html>
<html amp lang="vi">
  <head>
    @include('amp.layout.partials.head')
  </head>
  <body class="{{ $bodyClass }} py-0">
    {{-- @include('amp.layout.partials.header') --}}
    <main>
      <div class="container-fluid game px-0" itemscope itemtype="http://schema.org/Game">
        <div class="container {{ isset($board) ? 'px-3 pb-0 pt-3' : 'p-3' }}">
          <audio id="nuoc-co">
            <source src="https://cotuong.r.worldssl.net/sound/nuocCo.mp3" type="audio/mpeg">
            <source src="https://cotuong.r.worldssl.net/sound/nuocCo.wav" type="audio/wav">
            Your browser does not support the audio element.
          </audio>
          <audio id="het-tran">
            <source src="https://cotuong.r.worldssl.net/sound/hetTran.mp3" type="audio/mpeg">
            <source src="https://cotuong.r.worldssl.net/sound/hetTran.wav" type="audio/wav">
            Your browser does not support the audio element.
          </audio>
{{--           @if ( isset($board) )
          <p class="w-100 text-center">
            <a style="color: white" id="capture" class="btn btn-danger btn-lg" href="javascript:void(0);"><i class="fal fa-camera"></i> Chụp bàn cờ thế</a>
          </p>
          @endif --}}
          <div id="ban-co" class="mx-auto"></div>
          <p class="w-100 text-center my-3">
            <span class="d-inline-block rounded" id="game-status"></span>
          </p>
          <p class="w-100 text-center mt-2">
            <span class="rounded d-none" id="game-over" data-toggle="tooltip" data-placement="top" data-original-title="Ấn 'Tạo phòng mới' để chơi ván khác nhé"><i class="fad fa-flag-checkered"></i> HẾT TRẬN</span>
          </p>
          <p class="w-100 text-center my-4">
            <a style="color: white" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ URL::to('/amp') }}/phong/{{ md5(time()) }}" class="btn btn-success btn-lg"><i class="fad fa-plus-circle"></i> Tạo phòng mới</a>
          </p>
          @yield('aboveContent')
          <div class="row">
            <input type="hidden" name="FEN" id="FEN" />
            <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ URL::to('/') }}" />
            @include('amp.layout.partials.scripts')
            @yield('belowContent')
            <p class="w-100 text-center mt-2">
              <a style="color: white" id="share-board" class="mx-auto btn btn-success btn-lg py-2" href="{{ URL::to('/amp/ban-co/') }}"><i class="fad fa-share"></i> Chia sẻ bàn cờ</a>
            </p>
            <script>
            $('#share-board').on('click', function(){
              $(this).attr('href', $(this).attr('href') + '/' + game.fen());
            });
            </script>
{{--             @if ( !isset($board) )
            <p class="w-100 text-center mt-2">
              <a style="color: white" id="share-board" class="mx-auto btn btn-success btn-lg py-2" href="{{ URL::to('/amp/ban-co/') }}"><i class="fad fa-share"></i> Chia sẻ bàn cờ</a>
            </p>
            <script>
            $('#share-board').on('click', function(){
              $(this).attr('href', $(this).attr('href') + '/' + game.fen());
            });
            </script>
            @else
            <h3 class="mx-auto text-center my-2 d-block w-100">Ai đi trước?</h3>
            <p class="w-100 text-center mt-2">
              <a id="red-first" class="w-25 btn btn-danger btn-lg" href="{{ URL::to('/amp/ban-co/') }}"><i class="fad fa-chess-clock-alt"></i> Đỏ tiên</a>
              <a id="black-first" class="w-25 btn btn-dark btn-lg" href="{{ URL::to('/amp/ban-co/') }}"><i class="fad fa-chess-clock"></i> Đen tiên</a>
            </p>
            <script>
            $('#red-first').on('click', function(){
              $(this).attr('href', $(this).attr('href') + '/' + board.fen() + ' r - - 0 1');
            });
            $('#black-first').on('click', function(){
              $(this).attr('href', $(this).attr('href') + '/' + board.fen() + ' b - - 0 1');
            });
            </script>
            @endif --}}
          </div>
        </div>
      </div>
      @include('amp.layout.partials.rules')
      {{-- @include('amp.layout.partials.adsense') --}}
      {{-- @include('amp.layout.partials.fb') --}}
    </main>
    {{-- @include('amp.layout.partials.footer') --}}
  </body>
</html>