@extends('layout.playlayout')
@section('aboveBoard')
@if (isset($room->host_id))
<h5 id="room-title" class="text-center my-1"><span id="host-title">{!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($room->host_id) !!}</span> <span id="guest-title">{!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($room->guest_id) !!}</span></h5>
@else
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Bạn đang đi quân đen") }}">{{ __("Bạn đã được mời") }}</h5>
@endif
<span id="room-name">{{ __("Tên phòng") }}: {{ $room->name }}</span>
@if (!isset($room->tournament_id))
    @include('layout.partials.timer')
@endif
@endsection
@section('aboveContent')
<p id="room-code" class="w-100 text-center mt-0 mb-1">
  <span data-step="1" data-intro="{{ __("Dùng mã phòng này để tìm kiếm trận đấu") }}" class="alert alert-dark d-inline-block" role="alert" data-toggle="tooltip" data-placement="bottom" data-original-title="{{ __("Sao chép mã phòng này nhé") }}"><i class="fad fa-trophy-alt"></i> {{ __("Mã phòng") }}: <strong style="cursor: pointer;">{{ $roomCode }}</strong></span>
  <input type="hidden" id="room-code-input" value="{{ $roomCode }}">
</p>
@endsection
@section('belowContent')
@if (!auth()->check() || (isset($room->guest_id) && auth()->id() == $room->guest_id))
  <p class="w-100 text-center">
    <a data-step="2" data-intro="{{ __("Ấn vào đây nếu bạn không biết đi nước nào") }}" id="resign" class="btn btn-dark btn-lg"><i class="fad fa-flag"></i> {{ __("Bỏ cuộc") }}</a>
  </p>
@endif
@include('layout.partials.kypho')
<script>
@if ($room['pass'] != null)
$(document).ready(function() {
  bootbox.prompt({
    title: "{{ __("Nhập mật khẩu để vào phòng") }}:",
    centerVertical: true,
    closeButton: false,
    locale: '{{ __("vi") }}',
    buttons: {
      confirm: {
        label: '<i class="fas fa-check"></i> {{ __("Nhập") }}',
        className: 'btn-danger pulse-red'
      },
      cancel: {
        className: 'btn-dark text-light'
      }
    },
    callback: function(password){
      if (password != null) {
        $.ajax({
          type: "GET",
          url: '{{ url('/api') }}/getPass/' + '{{ $roomCode }}',
          dataType: 'text'
        }).done(function(data) {
          if (data != password) {
            bootbox.alert({
              message: "{{ __("Sai mật khẩu! Bạn sẽ được chuyển hướng về Trang chủ") }}",
              size: 'small',
              centerVertical: true,
              closeButton: false,
              locale: '{{ __("vi") }}',
              buttons: {
                ok: {
                  className: 'btn-danger pulse-red'
                }
              },
              callback: function () {
                window.location.href = '{{ url('/') }}';
              }
            });
          }
        });
      } else {
        bootbox.alert({
          message: "{{ __("Bạn đã ấn Hủy! Bạn sẽ được chuyển hướng về Trang chủ") }}",
          size: 'small',
          centerVertical: true,
          closeButton: false,
          locale: '{{ __("vi") }}',
          buttons: {
            ok: {
              className: 'btn-danger pulse-red'
            }
          },
          callback: function () {
            window.location.href = '{{ url('/') }}';
          }
        });
      }
    }
  });
});
@endif
let board = null;
let game = new Xiangqi();
@if (!empty($room->fen))
  game.load('{!! $room->fen !!}');
@endif
let currentFEN = game.fen();
let alertShown = false;
let hasGameOverSound = false;
let resignAlertShown = false;
let kypho = null;
let lastMoveIccs = null;

function updateFenCode(roomCode, moveIccs) {
  currentFEN = game.fen();

  const payload = {
    'ma-phong': roomCode,
    'FEN': game.fen()
  };
  if (moveIccs) {
    payload.move = moveIccs;
  }

  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/updateFEN',
    data: payload,
    dataType: 'text'
  }).done(function() {

    // ĐIỂM QUAN TRỌNG: Chỉ đổi đồng hồ sau khi FEN mới đã được lưu thành công trên Server.
    // Điều này chặn đứng hoàn toàn lỗi Server gửi nhầm FEN cũ về trình duyệt.
    @if (!isset($room->tournament_id))
      if (!game.game_over()) {
        switchTurn('{{ $roomCode }}', game.turn() === 'b' ? 'red' : 'black');
      }
    @endif

    if (kypho) {
      kypho.syncMoves('{{ url('/api') }}/readMoves/' + roomCode);
    }
  });
}

// WEBSOCKET: Listen for real-time game updates
if (typeof Echo !== 'undefined') {
  Echo.channel('room.{{ $roomCode }}')
    .listen('.room.updated', (e) => {
      let newFEN = e.room.fen;

      if (!newFEN) return;

      if (newFEN !== game.fen()) {
        // ĐIỂM MẤU CHỐT: Nếu FEN từ server gửi về giống hệt FEN trước khi ta đi (currentFEN)
        // Nghĩa là server đang bị trễ và gửi tiếng vọng FEN cũ của lệnh đổi lượt. Ta bỏ qua ngay!
        if (newFEN === currentFEN) return;

        currentFEN = newFEN;
        game.load(newFEN);
        board.position(newFEN, true);

        if (!newFEN.includes('resign')) {
          if (typeof nuocCo !== 'undefined') {
            let playPromise = nuocCo.play();
            if (playPromise !== undefined) {
              playPromise.catch(error => {
                console.warn("Audio playback prevented by browser:", error);
              });
            }
          }
        }

        if (typeof kypho !== 'undefined' && kypho !== null) {
          kypho.syncMoves('{{ url('/api') }}/readMoves/{{ $roomCode }}');
        }
      } else {
        currentFEN = newFEN;
      }

      updateStatus();
    });
}

function updateResult(roomCode, result) {
  if (alertShown) return;
  alertShown = true;
  @if(auth()->check() && (auth()->id() == $room->host_id || auth()->id() == $room->guest_id))
  let successMsg = '{{ __("Trận đấu kết thúc") }}';
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/updateResult',
    data: {
      'ma-phong': roomCode,
      'result': result,
      'id': '{{ auth()->id() }}'
    },
    dataType: 'json'
  }).done(function(data) {
    if (data && data.success) successMsg = data.success;
  }).always(function() {
    bootbox.alert({
      message: successMsg,
      size: 'small',
      centerVertical: true,
      closeButton: false,
      locale: '{{ __("vi") }}',
      buttons: {
        ok: { className: 'btn-danger pulse-red' }
      },
      callback: function () {
        $.ajax({
          type: "POST",
          url: '{{ url('/api') }}/updateElo',
          data: { 'ma-phong': roomCode, 'result': result },
          dataType: 'json'
        }).always(function(){
          setTimeout(function(){
            @if (!isset($room->tournament_id))
            window.location.href = "{{ localized_url('app.dashboard') }}";
            @else
            window.location.href = "{{ localized_url('tournaments.show', ['slug' => $tournament->slug]) }}";
            @endif
          }, 500);
        });
      }
    });
  });
  @elseif(!isset($room->host_id))
  let sideMsg = '{{ __("Trận đấu kết thúc") }}';
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/updateSideResult',
    data: {
      'ma-phong': roomCode,
      'result': result,
      'lang': '{{ __('vi') }}',
      'side': 'black'
    },
    dataType: 'json'
  }).done(function(data) {
    if (data && data.success) sideMsg = data.success;
  }).always(function() {
    bootbox.alert({
      message: sideMsg,
      size: 'small',
      centerVertical: true,
      closeButton: false,
      locale: '{{ __("vi") }}',
      buttons: { ok: { className: 'btn-danger pulse-red' } },
      callback: function () {
        window.location.href = "{{ localized_url('room.list') }}";
      }
    });
  });
  @endif
}

function removeGreySquares () {
  $('#ban-co .square-2b8ce').removeClass('highlight');
}

function greySquare (square) {
  let $square = $('#ban-co .square-' + square);

  $square.addClass('highlight');
}

function onDragStart (source, piece) {
  // do not pick up pieces if the game is over
  if (game.game_over()) return false;

  // or if it's not that side's turn
  if ((game.turn() === 'r' && piece.search(/^b/) !== -1) ||
      (game.turn() === 'b' && piece.search(/^r/) !== -1)) {
    return false;
  }

  if ((board.orientation() == 'red' && game.turn() === 'b') || (board.orientation() == 'black' && game.turn() === 'r')) {
    return false;
  }
}

function onDrop (source, target) {
  removeGreySquares();

  // Kiểm tra nước đi có hợp lệ không
  let move = game.move({
    from: source,
    to: target
  });

  if (move !== null) {
    // Đã xóa switchTurn ở đây đi để không bị racy!
    lastMoveIccs = move.iccs;
  } else {
    // Nếu đi sai luật, bật trả quân cờ lại vị trí cũ
    return 'snapback';
  }

  updateStatus();
}

function onMouseoverSquare (square, piece) {
  // get list of possible moves for this square
  let moves = game.moves({
    square: square,
    verbose: true
  });

  // exit if there are no moves available for this square
  if (moves.length === 0) return;

  // highlight the square they moused over
  greySquare(square);

  // highlight the possible squares for this piece
  for (let i = 0; i < moves.length; i++) {
    greySquare(moves[i].to);
  }
}

function onMouseoutSquare (square, piece) {
  removeGreySquares();
}

function onSnapEnd () {
  nuocCo.play();
  updateFenCode('{{ $roomCode }}', lastMoveIccs);
  lastMoveIccs = null;
  // updateStatus();
}

function updatePlayersTitle() {
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/renderPlayersTitle',
    data: {
      'ma-phong': '{{ $roomCode }}'
    },
    dataType: 'text'
  }).done(function(data){
    $('h5#room-title').html(data);
  });
}



@if (isset($room->host_id))
const updatePlayers = setInterval(function() {
  updatePlayersTitle();
}, 5000);
@endif

function updateStatus () {
  var status = ''

  var moveColor = '{{ __("Đỏ") }}'
  if (game.turn() === 'b') {
    moveColor = '{{ __("Đen") }}'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' {{ __("bị chiếu bí") }}';
    if (game.turn() === 'b') {
      setTimeout(function() { updateResult('{{ $roomCode }}', '1'); }, 1000);
    } else if (game.turn() === 'r') {
      setTimeout(function() { updateResult('{{ $roomCode }}', '-1'); }, 1000);
    }
  }

  // draw?
  else if (game.in_draw()) {
    status = '{{ __("Hòa") }}';
    setTimeout(function() { updateResult('{{ $roomCode }}', '0'); }, 1000);
  }

  // game still on
  else {
    status = moveColor
    if (game.game_over() && !game.in_draw() && !game.fen().includes('resign')) {
      if (game.turn() === 'b') {
        setTimeout(function() { updateResult('{{ $roomCode }}', '1'); }, 1000);
      } else if (game.turn() === 'r') {
        setTimeout(function() { updateResult('{{ $roomCode }}', '-1'); }, 1000);
      }
    }
    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + ' {{ __("đang bị chiếu") }}'

      if ((board.orientation() == 'red' && game.turn() === 'r') || (board.orientation() == 'black' && game.turn() === 'b')) {
        $('#checkmateText').show();
      }
    } else {
      $('#checkmateText').hide();
    }
  }
  if (game.turn() === 'r') {
    $('#game-status').removeClass('black').addClass('red');
  } else if (game.turn() === 'b') {
    $('#game-status').removeClass('red').addClass('black');
  }

  $('#game-status').html(status);
  $('#header-status').html(': '+status);
  if (game.game_over()) {
    if (!hasGameOverSound) {
      hasGameOverSound = true;
      hetTran.play();
    }
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> {{ __("Hết trận") }}');
    $('#header-status').html(': '+status+' - {{ __("Hết trận") }}');
    // evtSource.close();
    // ADD THIS TO DISCONNECT THE WEBSOCKET
    if (typeof Echo !== 'undefined') {
      Echo.leave('room.{{ $roomCode }}');
    }
    @if (isset($room->host_id))
    clearInterval(updatePlayers);
    @endif
  }
  if (game.fen().includes('resign') && !resignAlertShown) {
    resignAlertShown = true;
    $('#header-status').html(': '+status+' - {{ __("Đã bỏ cuộc") }}');

    // Determine who resigned based on the explicit FEN string marker
    let resignResult;
    if (game.fen().includes('resign-black')) {
      // Black player explicitly resigned (Black loses, Red wins = 1)
      resignResult = '1';
    } else if (game.fen().includes('resign-red')) {
      // Red player explicitly resigned (Red loses, Black wins = -1)
      resignResult = '-1';
    } else {
      // Fallback for any old cached sessions
      resignResult = board.orientation() === 'black' ? '1' : '-1';
    }

    bootbox.alert({
      message: '<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}',
      locale: '{{ __("vi") }}',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      },
      callback: function() {
        updateResult('{{ $roomCode }}', resignResult);
      }
    });
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}');
    $('#resign').addClass('disabled').attr('aria-disabled', true);
  }
  if (kypho) {
    kypho.updateControls();
  }
}
let config = {
  draggable: true,
  position: game.fen(),
  onDragStart: onDragStart,
  onDrop: onDrop,
  onMouseoutSquare: onMouseoutSquare,
  onMouseoverSquare: onMouseoverSquare,
  onSnapEnd: onSnapEnd,
  showNotation: true,
  orientation: "black",
  //pieceTheme: '/static/img/xiangqipieces/traditional/{piece}.svg'

};
board = Xiangqiboard('ban-co', config);
$(window).resize(board.resize);
kypho = KyPho.initRoom({
  board: board,
  startFen: '{{ env('INITIAL_FEN', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1') }}',
  isLive: function() { return !game.game_over(); }
});
if (kypho) {
  kypho.syncMoves('{{ url('/api') }}/readMoves/{{ $roomCode }}');
}
updateStatus();
@if (isset($room->guest_id) && auth()->id() == $room->guest_id)
$('#choi').removeClass('pulse-red').addClass('disabled');
@endif
// let evtSource = new EventSource("{{ url('/api') }}/getFEN/{{ $roomCode }}");

// evtSource.onmessage = function (e) {
//   let newFEN = e.data;
//   console.log(newFEN);
//   if (newFEN != currentFEN) {
//     currentFEN = game.fen();
//     $.ajax({
//       type: "POST",
//       url: '{{ url('/api') }}/updateFEN',
//       data: {
//         'ma-phong': '{{ $roomCode }}',
//         'FEN': newFEN
//       },
//       dataType: 'text'
//     });
//     if (newFEN == game.fen()) {
//       // my move
//       board.position(newFEN, true);
//       game.load(newFEN);
//     } else {
//       // opponent's move
//       board.position(newFEN, true);
//       game.load(newFEN);
//       if (!game.fen().includes('resign')) {
//         nuocCo.play();
//       }
//     }
//   }
//   updateStatus();
// };
@if (isset($room->host_id))
// $.ajax({
//     type: "POST",
//     url: '{{ url('/api') }}/getNameEmail',
//     data: {
//         'id': '{{ $room->host_id }}'
//     },
//     dataType: 'json'
// }).done(function(hostData){
//     $('#host-title').html('<a class="text-light" target="_blank" href="{{ url('/ky-thu/') }}/{{ $room->host_id }}">' + '<img src="' + get_gravatar_image_url(hostData.email, 25) + '" />' + '# {{ $room->host_id }}  ' + hostData.name + '</a>');
//     $.ajax({
//         type: "POST",
//         url: '{{ url('/api') }}/getNameEmail',
//         data: {
//             'id': '{{ $room->guest_id }}'
//         },
//         dataType: 'json'
//     }).done(function(guestData){
//       if (guestData && guestData != '') {
//         $('#guest-title').html('<a class="text-light" target="_blank" href="{{ url('/ky-thu/') }}/{{ $room->guest_id }}">' + '<img src="' + get_gravatar_image_url(guestData.email, 25) + '" />' + '# {{ $room->guest_id }}  ' + guestData.name + '</a>');
//       } else {
//         $('#guest-title').text('đang đợi');
//       }
//     });
// });
@endif
$('#resign').on('click', function() {
  // Append the specific color of the person resigning
  game.load(game.fen() + ' resign-' + board.orientation());
  updateFenCode('{{ $roomCode }}');
  updateStatus();
});

// Add CSS for loading state
const style = document.createElement('style');
style.textContent = `
  .fa-spinner {
    margin-left: 5px;
  }
  .disabled {
    opacity: 0.5;
    pointer-events: none;
  }
  .highlight {
    background-color: #ffeb3b !important;
    opacity: 0.6;
  }
`;
document.head.appendChild(style);
</script>
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
{{-- @include('layout.partials.players') --}}
{{-- @include('layout.partials.userPuzzles') --}}
{{-- @include('layout.partials.boards') --}}
{{-- @include('layout.partials.playedBoards') --}}
{{-- @include('layout.partials.puzzles') --}}
{{-- @include('layout.partials.comments') --}}
@endsection
