@extends('layout.gamelayout')
@section('aboveBoard')
@if (isset($room->host_id))
<h5 id="room-title" class="text-center my-1"><span id="host-title">{!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($room->host_id) !!}</span> <span id="guest-title">{!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($room->guest_id) !!}</span></h5>
@else
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="Bạn đang đi quân đỏ">Bạn là quân Đỏ</h5>
@endif
<span id="room-name">Tên phòng: {{ $room->name }}</span>
@endsection
@section('aboveContent')
<p id="room-code" class="w-100 text-center mt-0 mb-1">
  <span data-step="2" data-intro="Dùng mã phòng này để tìm kiếm trận đấu" class="alert alert-dark d-inline-block" role="alert" data-toggle="tooltip" data-placement="bottom" data-original-title="Sao chép mã phòng này nhé"><i class="fad fa-trophy-alt"></i> Mã phòng: <strong style="cursor: pointer;">{{ $roomCode }}</strong></span>
  <input type="hidden" id="room-code-input" value="{{ $roomCode }}">
</p>
@endsection
@section('belowContent')
@if (!auth()->check() || (isset($room->guest_id) && auth()->id() == $room->host_id))
<p class="w-100 text-center">
  <a data-step="5" data-intro="Ấn vào đây nếu bạn không biết đi nước nào" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> Bỏ cuộc</a>
</p>
@endif
<script>
@if ($room['pass'] != null)
$(document).ready(function() {
  bootbox.prompt({
    title: "Nhập mật khẩu để vào phòng:",
    centerVertical: true,
    closeButton: false,
    locale: 'vi',
    buttons: {
      confirm: {
        label: '<i class="fas fa-check"></i> Nhập',
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
              message: "Sai mật khẩu! Bạn sẽ được chuyển hướng về Trang chủ",
              size: 'small',
              centerVertical: true,
              closeButton: false,
              locale: 'vi',
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
          message: "Bạn đã ấn Hủy! Bạn sẽ được chuyển hướng về Trang chủ",
          size: 'small',
          centerVertical: true,
          closeButton: false,
          locale: 'vi',
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
let currentFEN = game.fen();

function updateFenCode(roomCode) {
  board.position(game.fen(), true);
  game.load(game.fen());
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/updateFEN',
    data: {
      'ma-phong': roomCode,
      'FEN': game.fen()
    },
    dataType: 'text'
  });
}

function manipulateRoom(roomCode) {
  $.ajax({
    type: "GET",
    url: '{{ url('/api') }}/readFEN/' + roomCode,
    dataType: 'text'
  }).done(function(newFEN) {
    if (newFEN != currentFEN) {
      currentFEN = game.fen();
      if (newFEN == game.fen()) {
        // my move
        board.position(newFEN, true);
        game.load(newFEN);
      } else {
        // opponent's move
        board.position(newFEN, true);
        game.load(newFEN);
        nuocCo.play();
      }
    }
    updateStatus()
  });
}

function updateResult(roomCode, result) {
  @if(auth()->check() && (auth()->id() == $room->host_id || auth()->id() == $room->guest_id))
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
    bootbox.alert({
      message: data.success,
      size: 'small',
      centerVertical: true,
      closeButton: false,
      locale: 'vi',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      },
      callback: function () {
        $.ajax({
          type: "POST",
          url: '{{ url('/api') }}/updateElo',
          data: {
            'ma-phong': roomCode,
            'result': result,
          },
          dataType: 'json'
        }).done(function(){
          const updatePointsTimeout = setTimeout(function(){
            window.location.href = "{{ url('/thi-dau') }}";
          }, 500);
        });
      }
    });
  });
  @elseif(!isset($room->host_id))
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/updateSideResult',
    data: {
      'ma-phong': roomCode,
      'result': result,
      'lang': 'vi',
      'side': 'red'
    },
    dataType: 'json'
  }).done(function(data) {
    bootbox.alert({
      message: data.success,
      size: 'small',
      centerVertical: true,
      closeButton: false,
      locale: 'vi',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      },
      callback: function () {
        window.location.href = "{{ url('/sanh-cho') }}";
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

  // see if the move is legal
  let move = game.move({
    from: source,
    to: target
  });

  // illegal move
  //if (move === null) return 'snapback';
  updateStatus()
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
  updateFenCode('{{ $roomCode }}');
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

const updateBoard = setInterval(function() {
  manipulateRoom('{{ $roomCode }}');
}, 1000);

@if (isset($room->host_id))
const updatePlayers = setInterval(function() {
  updatePlayersTitle();
}, 5000);
@endif

function updateStatus () {
  var status = ''

  var moveColor = 'Đỏ'
  if (game.turn() === 'b') {
    moveColor = 'Đen'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' bị chiếu bí';
    if (game.turn() === 'b') {
      updateResult('{{ $roomCode }}', '1');
    } else if (game.turn() === 'r') {
      updateResult('{{ $roomCode }}', '-1');
    }
  }

  // draw?
  else if (game.in_draw()) {
    status = 'Hòa';
    updateResult('{{ $roomCode }}', '0');
  }

  // game still on
  else {
    status = 'Tới lượt ' + moveColor + ' đi'
    if (game.game_over() && !game.in_draw() && !game.fen().includes('resign')) {
      if (game.turn() === 'b') {
        updateResult('{{ $roomCode }}', '1');
      } else if (game.turn() === 'r') {
        updateResult('{{ $roomCode }}', '-1');
      }
    }
    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + ' đang bị chiếu'
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
    hetTran.play();
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> Hết trận');
    $('#header-status').html(': '+status+' - Hết trận');
    // evtSource.close();
    clearInterval(updateBoard);
    @if (isset($room->host_id))
    clearInterval(updatePlayers);
    @endif
  }
  if (game.fen().includes('resign')) {
    $('#header-status').html(': '+status+' - Đã bỏ cuộc');
    bootbox.alert({
      message: '<i class="fad fa-flag-checkered"></i> Đã bỏ cuộc',
      locale: 'vi',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      },
      callback: function() {
        updateResult('{{ $roomCode }}', '0');
      }
    });
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> Đã bỏ cuộc');
    $('#resign').addClass('disabled').attr('aria-disabled', true);
  }
}
let config = {
  @if ((isset($room->host_id) && auth()->id() == $room->host_id) || !isset($room->host_id))
  draggable: true,
  @else
  draggable: false,
  @endif
  position: 'start',
  onDragStart: onDragStart,
  onDrop: onDrop,
  onMouseoutSquare: onMouseoutSquare,
  onMouseoverSquare: onMouseoverSquare,
  onSnapEnd: onSnapEnd,
  orientation: "red",
  showNotation: true
  //pieceTheme: '/static/img/xiangqipieces/traditional/{piece}.svg'

};
board = Xiangqiboard('ban-co', config);
$(window).resize(board.resize);
updateStatus()
// window.onload = function(){
//   if (board.orientation() == 'red' && game.turn() === 'b') {
//     location.href = $('#black-link').attr('href');
//   }
// };
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
  game.load(game.fen() + ' resign');
  updateFenCode('{{ $roomCode }}');
  updateStatus();
});
@if (isset($room->host_id) && auth()->id() == $room->host_id)
$('#choi').removeClass('pulse-red').addClass('disabled');
@endif
</script>
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
@include('layout.partials.players')
@include('layout.partials.userPuzzles')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
{{-- @include('layout.partials.puzzles') --}}
{{-- @include('layout.partials.comments') --}}
@endsection