<!DOCTYPE html>
<html amp lang="en">
  <head>
    @include('amp.en.layout.partials.head')
  </head>
  <body class="{{ $bodyClass }} py-0">
    {{-- @include('amp.en.layout.partials.header') --}}
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
            <a style="color: white" id="capture" class="btn btn-danger btn-lg" href="javascript:void(0);"><i class="fal fa-camera"></i> Capture the board</a>
          </p>
          @endif --}}
          <div id="ban-co" class="mx-auto"></div>
          <p class="w-100 text-center my-3">
            <span class="d-inline-block rounded" id="game-status"></span>
          </p>
          <p class="w-100 text-center mt-2">
            <span class="rounded d-none" id="game-over" data-toggle="tooltip" data-placement="top" data-original-title="Press 'Host a Room' to play with real person"><i class="fad fa-flag-checkered"></i> GAME OVER</span>
          </p>
{{--           <p class="w-100 text-center my-4">
            <a style="color: white" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ URL::to('/amp') }}/room/{{ md5(time()) }}" class="btn btn-success btn-lg"><i class="fad fa-plus-circle"></i> Host a room</a>
          </p> --}}
          @yield('aboveContent')
          <div class="row">
            <input type="hidden" name="FEN" id="FEN" />
            <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ URL::to('/') }}" />
            @include('amp.en.layout.partials.scripts')
            @yield('belowContent')
{{--             @if ( !isset($board) )
            <p class="w-100 text-center mt-2">
              <a style="color: white" id="share-board" class="mx-auto btn btn-success btn-lg py-2" href="{{ URL::to('/amp/board/') }}"><i class="fad fa-share"></i> Share board</a>
            </p>
            <script>
            $('#share-board').on('click', function(){
              $(this).attr('href', $(this).attr('href') + '/' + game.fen());
            });
            </script>
            @else
            <h3 class="mx-auto text-center my-2 d-block w-100">Who goes first?</h3>
            <p class="w-100 text-center mt-2">
              <a id="red-first" class="w-25 btn btn-danger btn-lg" href="{{ URL::to('/amp/board/') }}"><i class="fad fa-chess-clock-alt"></i> Red first</a>
              <a id="black-first" class="w-25 btn btn-dark btn-lg" href="{{ URL::to('/amp/board/') }}"><i class="fad fa-chess-clock"></i> Black first</a>
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
      @include('amp.en.layout.partials.rules')
      {{-- @include('amp.en.layout.partials.adsense') --}}
      {{-- @include('amp.en.layout.partials.fb') --}}
    </main>
    {{-- @include('amp.en.layout.partials.footer') --}}
  </body>
</html>