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

      /* Khung bao quanh Bàn cờ */
      #ban-co {
        background-color: #252a36;
        border-radius: 8px;
        padding: 5px;
        margin-bottom: 15px;
      }

      /* Nút bấm (Buttons) hiện đại hơn */
      .btn-danger {
        background: linear-gradient(145deg, #d32f2f, #b71c1c) !important;
        border: none !important;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(211, 47, 47, 0.3);
        transition: all 0.3s ease;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
      }
      .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(211, 47, 47, 0.6);
        background: linear-gradient(145deg, #f44336, #d32f2f) !important;
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
        display: none;
        text-align: center;
        margin-bottom: 15px;
      }

      /* Tùy chỉnh thanh cuộn (Scrollbar) cho đồng bộ Dark theme */
      ::-webkit-scrollbar { width: 8px; height: 8px; }
      ::-webkit-scrollbar-track { background: #121418; }
      ::-webkit-scrollbar-thumb { background: #3a3f4c; border-radius: 4px; }
      ::-webkit-scrollbar-thumb:hover { background: #505769; }

      /* Cân bằng các khoảng trống section bên dưới */
      .mt-lg-0.mt-md-5 {
          margin-top: 2rem !important;
      }
    </style>
  </head>
  <body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.scripts')
    @include('layout.partials.header')
    @include('layout.partials.analysisModal')
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
      @include('common.xiangqiBanner')
      <span id="checkmateText">{{ __('Chiếu!') }}</span>
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
            @include('layout.partials.findMatch')
          </div>
          <div class="row">
            <div class="col-12 text-center mb-3">
              @yield('aboveBoard')
            </div>
          </div>
          @guest
            @if(app()->getLocale() === 'vi')
            <div class="row mb-4 justify-content-center">
              <div class="col-lg-8 col-md-10 col-12 text-center">
                <div class="card text-light shadow-lg" style="border-radius: 15px; background: linear-gradient(145deg, #252a36 0%, #1a1c23 100%); border: 1px solid #3a3f4c; overflow: hidden;">

                  {{-- Header Banner of the Card --}}
                  <div class="card-header border-0 text-dark py-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(90deg, #fbc02d, #ff9800);">
                    <i class="fad fa-trophy-alt text-dark fa-lg mr-2"></i>
                    <strong style="letter-spacing: 0.5px; font-size: 1.1rem;">{{ __('GIẢI ĐẤU CỜ TƯỚNG ĐANG CHỜ BẠN!') }}</strong>
                  </div>

                  <div class="card-body p-4">
                    <p class="lead mb-3" style="font-size: 1.05rem; color: #b0bec5;">
                      {{ __('Bạn đang chơi với tư cách Khách. Bạn có biết tài khoản miễn phí cho phép bạn tham gia các giải đấu, theo dõi Elo và ghi danh vào Bảng Xếp Hạng?') }}
                    </p>

                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-3">
                      <a href="{{ route('tournaments.index') }}" class="btn btn-outline-warning font-weight-bold px-4 py-2 mx-md-2 mb-2 mb-md-0" style="border-radius: 25px; transition: 0.3s;">
                        <i class="fad fa-eye"></i> {{ __('Xem Các Giải Đấu') }}
                      </a>
                      <a href="{{ route('register') }}" class="btn btn-warning text-dark font-weight-bold px-4 py-2 mx-md-2 pulse-red" style="border-radius: 25px; box-shadow: 0 4px 15px rgba(255, 152, 0, 0.4);">
                        <i class="fad fa-user-plus"></i> {{ __('Đăng Ký Miễn Phí Ngay') }}
                      </a>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            @endif
          @endguest
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
          @else
          <div class="row">
            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
              <div id="ban-co" class="mx-auto mr-lg-0 h-auto"></div>
              @include('layout.partials.themeSelector')
              @include('layout.partials.analyzeBtn')
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mt-lg-0 mt-md-5 mt-sm-5 mt-xs-5">
              @include('layout.partials.comments')
              {{-- @include('common.sideAds') --}}
            </div>
          </div>
          @endif
          <div class="row">
            <div class="col-12">
              @if ( $roomCode != '' )
              <p class="w-100 text-center my-3 d-flex justify-content-center align-items-center flex-wrap gap-2">
                @if (!isset($room->result) && isset($room->host_id))
                  @if (auth()->check())
                  <a id="choi" class="btn btn-danger text-light btn-lg showPromotion mx-2" href="javascript:joinMatch('{{ $roomCode }}')"><i class="fad fa-mouse"></i> {{ __('Chơi') }}</a>
                  <script>
                    function joinMatch(roomCode) {
                      var hostId = '';
                      var guestId = '';
                      $.ajax({
                        type: "POST",
                        url: '{{ url('/api') }}/getRoomIds',
                        data: {
                          'ma-phong': roomCode
                        },
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
                              buttons: {
                                ok: {
                                  className: 'btn-danger',
                                  label: 'Oki'
                                }
                              },
                              callback: function(){
                                window.location.href = '{{ url('/phong/') }}' + '/' + roomCode + '/khach';
                              }
                            });
                          });
                        } else if (guestId == '{{ auth()->id() }}') {
                          bootbox.alert({
                            message: "{{ __('Mời bạn quay lại phòng!') }}",
                            size: 'small',
                            centerVertical: true,
                            closeButton: false,
                            buttons: {
                              ok: {
                                className: 'btn-danger',
                                label: 'Oki'
                              }
                            },
                            callback: function(){
                              window.location.href = '{{ url('/phong/') }}' + '/' + roomCode + '/khach';
                            }
                          });
                        } else if (hostId == '{{ auth()->id() }}') {
                          bootbox.alert({
                            message: "{{ __('Mời bạn vào lại phòng của mình!') }}",
                            size: 'small',
                            centerVertical: true,
                            closeButton: false,
                            buttons: {
                              ok: {
                                className: 'btn-danger',
                                label: 'Oki'
                              }
                            },
                            callback: function(){
                              window.location.href = '{{ url('/phong/') }}' + '/' + roomCode;
                            }
                          });
                        }
                      });
                    }
                  </script>
                  @else
                  <a class="btn btn-danger text-light btn-lg showPromotion thi-dau mx-2" href="{{ url('/dang-nhap') }}" data-toggle="tooltip" data-placement="top" title="{{ __('Đăng nhập để thi đấu') }}"><i class="fad fa-sign-in"></i> {{ __("Đăng nhập") }}</a>
                  @endif
                @else
                <a class="btn btn-danger text-light btn-lg showPromotion mx-2 rooms-list" href="{{ url(__('/sanh-cho')) }}"><i class="fad fa-chevron-circle-left"></i> {{ __("Quay lại sảnh chờ") }}</a>
                @endif
                @include('common.volumeBtn')
                @include('common.tourBtn')
              </p>
              @endif

              <div class="mt-3">
                @include('common.ads')
              </div>

              @yield('aboveContent')
              <div class="row">
                <input type="hidden" name="FEN" id="FEN" >
                <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}" >
                @include('common.themes')
                @include('layout.partials.scripts')
                @if ( !isset($board) )
                  @include('layout.partials.rules')
                @endif
                @yield('belowContent')
                @if ( !isset($board) )
                <script>
                $('#btn-analyze').on('click', function(e) {
                  e.preventDefault();

                  if (typeof game === 'undefined') {
                    bootbox.alert("{{ __('Chưa tải được dữ liệu bàn cờ.') }}");
                    return;
                  }

                  // Lấy dữ liệu FEN
                  var currentFen = game.fen();

                  if (!currentFen) {
                    bootbox.alert("{{ __('Không lấy được mã FEN.') }}");
                    return;
                  }

                  // Gọi hàm mở Modal với chỉ FEN string
                  // openAnalysisModal được định nghĩa trong analysisModal.blade.php
                  openAnalysisModal(currentFen);
                });
                function createRoom() {
                  $.ajax({
                    type: "POST",
                    url: '{{ url('/api') }}/hasRoomcode',
                    data: {
                      'ma-phong': '{{ md5(time()) }}'
                    },
                    dataType: 'text'
                  }).done(function(data){
                    if (data == 'no') {
                      bootbox.prompt({
                        title: "{{ __('Mời đặt tên cho Phòng thi đấu:') }}",
                        locale: '{{ __("vi") }}',
                        centerVertical: true,
                        closeButton: false,
                        maxlength: 32,
                        buttons: {
                          confirm: {
                            label: '<i class="fas fa-check"></i> {{ __('Đặt tên') }}',
                            className: 'btn-danger'
                          }
                        },
                        callback: function(roomName){
                          if (roomName != null) {
                            if (roomName.trim().length === 0 || roomName.length === 0) {
                              bootbox.alert({
                                message: "{{ __('Vui lòng đặt tên cho phòng!') }}",
                                size: 'small',
                                locale: '{{ __("vi") }}',
                                centerVertical: true,
                                closeButton: false,
                                buttons: {
                                  ok: {
                                    className: 'btn-danger',
                                    label: '{{ __('Oki') }}'
                                  }
                                },
                                callback: function () {
                                  $('#create-room').trigger('click');
                                }
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
                                  size: 'small',
                                  centerVertical: true,
                                  closeButton: false,
                                  buttons: {
                                    ok: {
                                      className: 'btn-danger',
                                      label: 'Oki'
                                    }
                                  },
                                  callback: function(){
                                    window.location.href = '{{ url('/phong/') }}' + '/' + '{{ md5(time()) }}';
                                  }
                                });
                              });
                            }
                          }
                        }
                      });
                    } else if (data == 'yes') {
                      bootbox.alert({
                        message: "{{ __('Mã phòng bị trùng, vui lòng thử lại.') }}",
                        size: 'small',
                        centerVertical: true,
                        closeButton: false,
                        buttons: {
                          ok: {
                            className: 'btn-danger',
                            label: 'Oki'
                          }
                        },
                        callback: function(){
                          setTimeout(() => {
                            location.reload();
                          }, 500);
                        }
                      });
                    }
                  });
                }
                const ratio = $('#ban-co').height() / $('#ban-co').width();
                function adjustBoard() {
                  const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
                  width = ($(window).height() - 192) / ratio;
                  if ($(window).width() >= $(window).height() && $(window).height() < 992) {
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
                  // $('#AdSenseModal').attr('data-url', $(this).attr('href') + '/' + game.fen()).modal('show');
                  window.location.href = $(this).attr('href') + '/' + game.fen();
                });
                </script>
                @include('common.volume')
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
      {{-- @include('layout.partials.fb') --}}
    </main>
    @include('layout.partials.aiChatWidget')
    @include('layout.partials.footer')
    @include('common.contactBtn')
  </body>
</html>
