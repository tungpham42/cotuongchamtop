<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <head>
    @include('layout.partials.head')

    <style>
      /* Tổng thể body và Typography */
      body {
        background-color: #121418 !important; /* Nền tối sâu giảm mỏi mắt */
      }

      /* Khu vực chứa game chính */
      .game.container-fluid {
        background: linear-gradient(135deg, #1a1c23 0%, #121418 100%);
        min-height: calc(100vh - 80px);
      }

      /* Khung bao quanh Bàn cờ - Default */
      #ban-co {
        background-color: #252a36;
        border-radius: 8px;
        padding: 5px;
        margin-bottom: 15px;
      }

      /* Tùy chỉnh Alerts cho Dark Theme */
      .alert {
        border-radius: 8px;
        border: none;
        backdrop-filter: blur(5px);
        margin-top: 15px;
      }
      .alert-success {
        background-color: rgba(46, 125, 50, 0.2);
        color: #81c784;
        border-left: 4px solid #4caf50;
      }
      .alert-warning {
        background-color: rgba(245, 124, 0, 0.2);
        color: #ffb74d;
        border-left: 4px solid #ff9800;
      }

      /* Dòng chữ "Chiếu!" */
      #checkmateText {
        font-size: 2.5rem;
        font-weight: 800;
        color: #ff5252;
        text-shadow: 0 0 15px rgba(255, 82, 82, 0.8), 0 0 5px rgba(255, 255, 255, 0.5);
        text-transform: uppercase;
        letter-spacing: 3px;
        display: block;
        text-align: center;
        margin-bottom: 15px;
      }

      /* Tùy chỉnh thanh cuộn (Scrollbar) cho đồng bộ Dark theme */
      ::-webkit-scrollbar { width: 8px; height: 8px; }
      ::-webkit-scrollbar-track { background: #121418; }
      ::-webkit-scrollbar-thumb { background: #3a3f4c; border-radius: 4px; }
      ::-webkit-scrollbar-thumb:hover { background: #505769; }

      .mt-lg-0.mt-md-5 { margin-top: 2rem !important; }

      /* ==========================================================
         NEW MATCH LAYOUT STYLES (4-Columns fitting Viewport)
         ========================================================== */
      .fluid-match-container {
        max-width: 100% !important;
        padding-left: 15px !important;
        padding-right: 15px !important;
      }

      .match-layout-wrapper {
        display: grid;
        grid-template-columns: minmax(320px, 35%) minmax(250px, 20%) minmax(250px, 20%) minmax(280px, 25%);
        gap: 15px;
        height: calc(100vh - 180px); /* Fit within viewport, accounting for headers/bottom */
        min-height: 550px;
        margin-bottom: 15px;
      }

      /* Responsive fallback for smaller screens */
      @media (max-width: 1400px) {
        .match-layout-wrapper {
          grid-template-columns: 1fr 1fr;
          height: auto;
          overflow-y: auto;
        }
      }
      @media (max-width: 768px) {
        .match-layout-wrapper {
          grid-template-columns: 1fr;
        }
      }

      .layout-board-col, .layout-kypho-col, .layout-info-col, .layout-comments-col {
        background-color: #252a36;
        border-radius: 8px;
        padding: 15px;
        height: 100%;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
      }

      .layout-board-col {
        padding: 5px;
        justify-content: center;
        align-items: center;
        overflow: hidden; /* Important to let board resize logic handle aspect ratio */
      }

      .layout-board-col #ban-co {
        width: 100%;
        height: auto;
        margin-bottom: 0;
      }

      /* Inner Scrollbars for Match Cols */
      .layout-kypho-col::-webkit-scrollbar, .layout-info-col::-webkit-scrollbar, .layout-comments-col::-webkit-scrollbar { width: 6px; }
      .layout-kypho-col::-webkit-scrollbar-thumb, .layout-info-col::-webkit-scrollbar-thumb, .layout-comments-col::-webkit-scrollbar-thumb { background: #505769; border-radius: 4px; }

      .game-status-display {
        font-size: 1.5rem;
        font-weight: 800;
        color: #ffb74d;
        text-align: center;
        margin-bottom: 15px;
        text-transform: uppercase;
      }

      .layout-info-col #checkmateText {
        display: none;
        margin-bottom: 10px;
      }

      .layout-controls {
        background-color: #1a1c23;
        border-radius: 8px;
        border: 1px solid #252a36;
        padding: 15px;
      }
    </style>
  </head>
  <body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.scripts')
    <script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
      window.Pusher = Pusher;
      window.Echo = new Echo({
          broadcaster: 'pusher',
          key: '{{ env("PUSHER_APP_KEY") }}',
          cluster: '{{ env("PUSHER_APP_CLUSTER", "ap1") }}',
          forceTLS: true
      });
    </script>
    @include('layout.partials.header')
    @include('layout.partials.analysisModal')

    @if (session('status'))
      <div class="container"><div class="alert alert-success" role="alert">{{ session('status') }}</div></div>
    @endif
    @if (session('success'))
      <div class="container"><div class="alert alert-success">{{ session('success') }}</div></div>
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

      <!-- Show global Checkmate Text only if NOT in match layout (non-match layout uses original) -->
      @if (!$roomCode)
      <span id="checkmateText">{{ __('Chiếu!') }}</span>
      @endif

      <div class="container-fluid game px-0" itemscope itemtype="http://schema.org/Game">
        <div class="container {{ isset($board) ? 'px-3 pb-0 pt-3' : 'p-3' }} {{ $roomCode ? 'fluid-match-container' : '' }}">
          <audio id="nuoc-co">
            <source src="{{ $cdnUrl }}/sound/nuocCo.mp3" type="audio/mpeg">
            <source src="{{ $cdnUrl }}/sound/nuocCo.wav" type="audio/wav">
          </audio>
          <audio id="het-tran">
            <source src="{{ $cdnUrl }}/sound/hetTran.mp3" type="audio/mpeg">
            <source src="{{ $cdnUrl }}/sound/hetTran.wav" type="audio/wav">
          </audio>

          @if (!isset($room->tournament_id))
          <div class="row">
            @include('layout.partials.findMatch')
          </div>
          @endif

          @if (!$roomCode)
          <!-- ORIGINAL LAYOUT LOGIC (Non-Match View) -->
          <div class="row">
            <div class="col-12 text-center mb-3">
              @yield('aboveBoard')
            </div>
          </div>
          @if ( !$roomCode && !isset($slug) )
          <div class="row">
            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 my-1">
              <div id="ban-co" class="mx-auto mr-lg-0 h-auto"></div>
              @include('layout.partials.themeSelector')
              @include('layout.partials.analyzeBtn')
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 my-auto">
              @yield('rightSide')
            </div>
          </div>
          @elseif ( !$roomCode && isset($slug) )
          <div class="puzzle-layout-wrapper">
            <div class="puzzle-layout-board">
              <div id="ban-co" class="mx-auto mr-lg-0 h-auto"></div>
              @include('layout.partials.themeSelector')
              @include('layout.partials.analyzeBtn')
              @yield('belowBoardExtras')
            </div>
            <div class="puzzle-layout-panel">
              @yield('rightSide')
            </div>
          </div>
          @endif

          <div class="row">
            <div class="col-12">
              <div class="mt-3">
                @include('common.ads')
              </div>
              @yield('aboveContent')
              <div class="row">
                <input type="hidden" name="FEN" id="FEN" >
                <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}" >
                @include('common.themes')
                @include('layout.partials.scripts')
                @yield('belowContent')
              </div>
            </div>
          </div>
          @else

          <input type="hidden" name="FEN" id="FEN" >
          <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}" >
          <div class="d-none">
            @include('common.themes')
          </div>
          @include('layout.partials.scripts')

          <div class="match-layout-wrapper">
            <div class="layout-board-col">
              <div id="ban-co" class="mx-auto"></div>
            </div>

            <div class="layout-kypho-col">
              @yield('belowContent')
            </div>

            <div class="layout-info-col text-center">
              <span id="checkmateText" style="display: none;">{{ __('Chiếu!') }}</span>
              <div id="game-status" class="game-status-display"></div>
              @yield('aboveBoard')
              <div class="mt-3">
                @yield('aboveContent')
              </div>
            </div>

            <div class="layout-comments-col">
              @include('layout.partials.comments')
            </div>
          </div>

          <div class="layout-controls text-center">
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 my-2">
                @if (!isset($room->result) && isset($room->host_id))
                  @if (auth()->check())
                  @if (!isset($room->tournament_id))<a id="choi" class="btn btn-danger text-light btn-lg showPromotion mx-2" href="javascript:joinMatch('{{ $roomCode }}')"><i class="fad fa-mouse"></i> {{ __('Chơi') }}</a>@endif
                  <script>
                    function joinMatch(roomCode) {
                      var hostId = '';
                      var guestId = '';
                      $.ajax({
                        type: "POST",
                        url: '{{ url('/api') }}/getRoomIds',
                        data: { 'ma-phong': roomCode },
                        dataType: 'json'
                      }).done(function(data){
                        hostId = data.host_id;
                        guestId = data.guest_id;
                        if (hostId != '{{ auth()->id() }}' && guestId != '{{ auth()->id() }}') {
                          $.ajax({
                            type: "POST",
                            url: '{{ url('/api') }}/joinRoom',
                            data: {
                              'ma-phong': roomCode,
                              'guest_id': '{{ auth()->id() }}'
                            },
                            dataType: 'text'
                          }).done(function() {
                            bootbox.alert({
                              message: "{{ __('Hãy chuẩn bị vào phòng!') }}",
                              size: 'small',
                              centerVertical: true,
                              closeButton: false,
                              buttons: { ok: { className: 'btn-danger', label: 'Oki' } },
                              callback: function(){ window.location.href = '{{ url(__('/phong/')) }}' + '/' + roomCode + '{{ __('/khach') }}'; }
                            });
                          });
                        } else if (guestId == '{{ auth()->id() }}') {
                          bootbox.alert({
                            message: "{{ __('Mời bạn quay lại phòng!') }}",
                            size: 'small',
                            centerVertical: true,
                            closeButton: false,
                            buttons: { ok: { className: 'btn-danger', label: 'Oki' } },
                            callback: function(){ window.location.href = '{{ url(__('/phong/')) }}' + '/' + roomCode + '{{ __('/khach') }}'; }
                          });
                        } else if (hostId == '{{ auth()->id() }}') {
                          bootbox.alert({
                            message: "{{ __('Mời bạn vào lại phòng của mình!') }}",
                            size: 'small',
                            centerVertical: true,
                            closeButton: false,
                            buttons: { ok: { className: 'btn-danger', label: 'Oki' } },
                            callback: function(){ window.location.href = '{{ url(__('/phong/')) }}' + '/' + roomCode; }
                          });
                        }
                      });
                    }
                  </script>
                  @else
                  <a class="btn btn-danger text-light btn-lg showPromotion thi-dau mx-2" href="{{ localized_url('login') }}" data-toggle="tooltip" data-placement="top" title="{{ __('Đăng nhập để thi đấu') }}"><i class="fad fa-sign-in"></i> {{ __("Đăng nhập") }}</a>
                  @endif
                @else
                <a class="btn btn-danger text-light btn-lg showPromotion mx-2 rooms-list" href="{{ localized_url('room.list') }}"><i class="fad fa-chevron-circle-left"></i> {{ __("Quay lại sảnh chờ") }}</a>
                @endif

                @include('common.volumeBtn')
                @include('common.tourBtn')
                @include('layout.partials.themeSelector')
                @include('layout.partials.analyzeBtn')
            </div>

            <div class="mt-3 text-center">
              @include('common.ads')
            </div>

            <div class="d-flex justify-content-center mt-2">
              @include('common.volume')
            </div>
          </div>
          @endif

          <!-- ==========================================
               COMMON SCRIPTS (Applied to both layouts)
               ========================================== -->
          @if ( !isset($board) )
          <script>
            $('#btn-analyze').on('click', function(e) {
              e.preventDefault();
              if (typeof game === 'undefined') {
                bootbox.alert("{{ __('Chưa tải được dữ liệu bàn cờ.') }}");
                return;
              }
              var currentFen = game.fen();
              if (!currentFen) {
                bootbox.alert("{{ __('Không lấy được mã FEN.') }}");
                return;
              }
              openAnalysisModal(currentFen);
            });

            function createRoom() {
              $.ajax({
                type: "POST",
                url: '{{ url('/api') }}/hasRoomcode',
                data: { 'ma-phong': '{{ md5(time()) }}' },
                dataType: 'text'
              }).done(function(data){
                if (data == 'no') {
                  bootbox.prompt({
                    title: "{{ __('Mời đặt tên cho Phòng thi đấu:') }}",
                    locale: '{{ __("vi") }}',
                    centerVertical: true,
                    closeButton: false,
                    maxlength: 32,
                    buttons: { confirm: { label: '<i class="fas fa-check"></i> {{ __('Đặt tên') }}', className: 'btn-danger' } },
                    callback: function(roomName){
                      if (roomName != null) {
                        if (roomName.trim().length === 0 || roomName.length === 0) {
                          bootbox.alert({
                            message: "{{ __('Vui lòng đặt tên cho phòng!') }}",
                            size: 'small', locale: '{{ __("vi") }}', centerVertical: true, closeButton: false,
                            buttons: { ok: { className: 'btn-danger', label: '{{ __('Oki') }}' } },
                            callback: function () { $('#create-room').trigger('click'); }
                          });
                        } else {
                          $.ajax({
                            type: "POST",
                            url: '{{ url('/api') }}/createRoom',
                            data: {
                              'ma-phong': '{{ md5(time()) }}',
                              'ten-phong': roomName,
                              'FEN': '{{ env('INITIAL_FEN') }}',
                              'pass': '',
                              'host_id': '{{ auth()->id() }}'
                            },
                            dataType: 'text'
                          }).done(function() {
                            bootbox.alert({
                              message: "{{ __('Bạn đã tạo phòng thành công.') }}",
                              size: 'small', centerVertical: true, closeButton: false,
                              buttons: { ok: { className: 'btn-danger', label: 'Oki' } },
                              callback: function(){ window.location.href = '{{ url(__('/phong/')) }}' + '/' + '{{ md5(time()) }}'; }
                            });
                          });
                        }
                      }
                    }
                  });
                } else if (data == 'yes') {
                  bootbox.alert({
                    message: "{{ __('Mã phòng bị trùng, vui lòng thử lại.') }}",
                    size: 'small', centerVertical: true, closeButton: false,
                    buttons: { ok: { className: 'btn-danger', label: 'Oki' } },
                    callback: function(){ setTimeout(() => { location.reload(); }, 500); }
                  });
                }
              });
            }

            // Enhanced adjustBoard to handle both Match 4-column Grid resizing and normal layout resizing
            function adjustBoard() {
              if ($('.layout-board-col').length) {
                // Resize logic for Match Grid Structure
                let container = $('.layout-board-col');
                let w = container.width();
                let h = container.height();
                // Maintain roughly a 1.1 height-to-width Xiangqi Ratio
                let targetSize = Math.min(w, h * 0.95);
                $('#ban-co').css({'width': targetSize, 'height': targetSize});
                if(typeof board !== 'undefined' && board !== null) {
                  board.resize();
                }
              } else {
                // Original logic for Non-Match Structures
                const ratio = $('#ban-co').height() / $('#ban-co').width();
                let width = ($(window).height() - 192) / ratio;
                if ($(window).width() >= $(window).height() && $(window).height() < 992) {
                  width = ($(window).height() - 50) / ratio;
                }
                width = Math.min(width, $('header > .container').width());
                $('#ban-co').css({'width': width});
                if(typeof board !== 'undefined' && board !== null) {
                  board.resize();
                }
              }
            }

            $(window).on('load resize', adjustBoard);
            $(document).ready(adjustBoard);

            $('#share-board').on('click auxclick', function(e){
              e.preventDefault();
              window.location.href = $(this).attr('href') + '/' + game.fen();
            });
          </script>
          @endif
        </div>
      </div>
    </main>
    @include('layout.partials.playFooter')
  </body>
</html>
