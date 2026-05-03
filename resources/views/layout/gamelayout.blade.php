<!DOCTYPE html>
<html lang="vi">
  <head>
    @include('layout.partials.head')
  </head>
  <body class="{{ $bodyClass }}">
    @include('common.afterBody')
    @include('common.scripts')
    @include('layout.partials.header')
    @include('layout.partials.adsenseModal')
    @include('layout.partials.shopeeModal')
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
            <div class="col-12 text-center">
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
          @elseif ( !$roomCode && $slug )
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
              <p class="w-100 text-center my-3">
                @if (!isset($room->result) && isset($room->host_id))
                  @if (auth()->check())
                  <a id="choi" class="btn btn-danger text-light btn-lg showPromotion mx-auto" href="javascript:joinMatch('{{ $roomCode }}')"><i class="fad fa-mouse"></i> {{ __('Chơi') }}</a>
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
                  <a class="btn btn-danger text-light btn-lg showPromotion thi-dau" href="{{ url('/dang-nhap') }}" data-toggle="tooltip" data-placement="top" title="{{ __('Đăng nhập để thi đấu') }}"><i class="fad fa-sign-in"></i> {{ __("Đăng nhập") }}</a>
                  @endif
                @else
                <a class="btn btn-danger text-light btn-lg showPromotion mx-auto rooms-list" href="{{ URL::to('/sanh-cho') }}"><i class="fad fa-chevron-circle-left"></i> {{ __("Quay lại sảnh chờ") }}</a>
                @endif
                @include('common.volumeBtn')
                @include('common.tourBtn')
              </p>
              @endif
              @include('common.ads')
              @yield('aboveContent')
              <div class="row">
                <input type="hidden" name="FEN" id="FEN" >
                <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ URL::to('/') }}" >
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
