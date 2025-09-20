@extends('ja.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="赤で遊ぶ">あなたは赤です</h5>
<span id="room-name">部屋: {{ $room->name }}</span>
@endsection
@section('aboveContent')
<p id="room-code" class="w-100 text-center mt-0 mb-1">
  <span data-step="2" data-intro="このルームコードを使ってマッチを検索してください" class="alert alert-dark d-inline-block" role="alert" data-toggle="tooltip" data-placement="bottom" data-original-title="このルームコードをコピー"><i class="fad fa-trophy-alt"></i> ルームコード: <strong style="cursor: pointer;">{{ $roomCode }}</strong></span>
  <input type="hidden" id="room-code-input" value="{{ $roomCode }}">
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="3" data-intro="このページをモバイルで開いてください">
@include('common.qrCode')
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center">
  <a data-step="1" data-intro="手掛かりが尽きた場合はここをクリックしてください" id="resign" class="btn btn-dark btn-lg"><i class="fad fa-flag"></i> 辞任</a>
</p>
<script>
@if ($room['pass'] != null)
$(document).ready(function() {
  bootbox.prompt({
    title: "このルームのパスワードを入力してください:",
    centerVertical: true,
    locale: 'ja',
    closeButton: false,
    buttons: {
      confirm: {
        label: '<i class="fas fa-check"></i> 入る',
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
              message: "間違ったパスワード！ホームページにリダイレクトされます",
              size: 'small',
              centerVertical: true,
              locale: 'ja',
              closeButton: false,
              buttons: {
                ok: {
                  className: 'btn-danger pulse-red'
                }
              },
              callback: function () {
                window.location.href = '{{ url('/ja') }}';
              }
            });
          }
        });
      } else {
        bootbox.alert({
          message: "[キャンセル] をクリックしました。ホームページにリダイレクトされます",
          size: 'small',
          centerVertical: true,
          locale: 'ja',
          closeButton: false,
          buttons: {
            ok: {
              className: 'btn-danger pulse-red'
            }
          },
          callback: function () {
            window.location.href = '{{ url('/ja') }}';
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
  @if(!isset($room->host_id))
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/updateSideResult',
    data: {
      'ma-phong': roomCode,
      'result': result,
      'lang': 'ja',
      'side': 'red'
    },
    dataType: 'json'
  }).done(function(data) {
    bootbox.alert({
      message: data.success,
      size: 'small',
      centerVertical: true,
      closeButton: false,
      locale: 'ja',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      },
      callback: function () {
        window.location.href = "{{ url('/heya-ichiran') }}";
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

const updateBoard = setInterval(function() {
  manipulateRoom('{{ $roomCode }}');
}, 1000);

function updateStatus () {
  var status = ''

  var moveColor = '赤'
  if (game.turn() === 'b') {
    moveColor = '黒'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + 'はチェックメイト'
    if (game.turn() === 'b') {
      updateResult('{{ $roomCode }}', '1');
    } else if (game.turn() === 'r') {
      updateResult('{{ $roomCode }}', '-1');
    }
  }

  // draw?
  else if (game.in_draw()) {
    status = '描画位置'
    updateResult('{{ $roomCode }}', '0');
  }

  // game still on
  else {
    status = moveColor + "が動く番"
    if (game.game_over() && !game.in_draw() && !game.fen().includes('resign')) {
      if (game.turn() === 'b') {
        updateResult('{{ $roomCode }}', '1');
      } else if (game.turn() === 'r') {
        updateResult('{{ $roomCode }}', '-1');
      }
    }
    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + 'はチェック中です'
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
    $('#header-status').html(': '+status+' - ゲームオーバー');
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> ゲームオーバー');
    // evtSource.close();
    clearInterval(updateBoard);
  }
  if (game.fen().includes('resign')) {
    $('#header-status').html(': '+status+' - 辞任');
    bootbox.alert({
      message: '<i class="fad fa-flag-checkered"></i> 辞任',
      locale: 'ja',
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
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> 辞任');
    $('#resign').addClass('disabled').attr('aria-disabled', true);
  }
}

let config = {
  draggable: true,
  position: 'start',
  onDragStart: onDragStart,
  onDrop: onDrop,
  onMouseoutSquare: onMouseoutSquare,
  onMouseoverSquare: onMouseoverSquare,
  onSnapEnd: onSnapEnd,
  orientation: "red"
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
$('#resign').on('click', function() {
  game.load(game.fen() + ' resign');
  updateFenCode('{{ $roomCode }}');
  updateStatus();
});
</script>
{{-- @include('ja.layout.partials.comments') --}}
@endsection