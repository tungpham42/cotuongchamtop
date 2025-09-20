@extends('amp.en.layout.gamelayout')
@section('aboveContent')
<p id="room-code" class="w-100 text-center mt-2">
  <span class="alert alert-info d-inline-block" role="alert" data-toggle="tooltip" data-placement="bottom" data-original-title="Remember this room code"><i class="fad fa-trophy-alt"></i> Room code: {{ $roomCode }}</span>
</p>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-4">
  <a style="color: white" class="w-25 btn btn-danger btn-lg" href="{{ url('/amp') }}/room/{{ $roomCode }}/red"><i class="fad fa-chess-clock-alt"></i> RED side</a>
  <a style="color: white" class="w-25 btn btn-dark btn-lg" href="{{ url('/amp') }}/room/{{ $roomCode }}/black"><i class="fad fa-chess-clock"></i> BLACK side</a>
</p>
<script>
$(document).ready(function() {
  bootbox.prompt({
    title: "Please enter the password for this Room:",
    required: true,
    centerVertical: true,
    buttons: {
      confirm: {
        className: 'btn-success'
      }
    },
    callback: function(password){
      if (password && password != "") {
        $.ajax({
          type: "GET",
          url: '{{ url('/api') }}/getPass/' + '{{ $roomCode }}',
          dataType: 'text'
        }).done(function(data) {
          if (data != password) {
            bootbox.alert({
              message: "Wrong password! You will be redirected to the Home page",
              size: 'small',
              centerVertical: true,
              buttons: {
                ok: {
                  className: 'btn-success'
                }
              },
              callback: function () {
                window.location.href = '{{ url('/amp/en') }}';
              }
            });
          }
        });
      } else {
        bootbox.alert({
          message: "You clicked Cancel! You will be redirected to the Home page",
          size: 'small',
          centerVertical: true,
          buttons: {
            ok: {
              className: 'btn-success'
            }
          },
          callback: function () {
            window.location.href = '{{ url('/amp/en') }}';
          }
        });
      }
    }
  });
});
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
        if (game.game_over()) {
          hetTran.play();
          $('#game-over').removeClass('d-none').addClass('d-inline-block');
        }
      }
    }
    updateStatus()
  });
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
  if (game.game_over()) {
    hetTran.play();
    $('#game-over').removeClass('d-none').addClass('d-inline-block');
  }
}

function updateStatus () {
  var status = ''

  var moveColor = 'Red'
  if (game.turn() === 'b') {
    moveColor = 'Black'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' is in checkmate'
  }

  // draw?
  else if (game.in_draw()) {
    status = 'Drawn position'
  }

  // game still on
  else {
    status = moveColor + "'s turn to move"

    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + ' is in check'
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
    $('#header-status').html(': '+status+' - Game over');
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
  orientation: "black"
  //pieceTheme: '/static/img/xiangqipieces/traditional/{piece}.svg'

};
board = Xiangqiboard('ban-co', config);

updateStatus()
let evtSource = new EventSource("{{ url('/api') }}/getFEN/{{ $roomCode }}");

evtSource.onmessage = function (e) {
  let newFEN = e.data;
  console.log(newFEN);
  if (newFEN != currentFEN) {
    currentFEN = game.fen();
    $.ajax({
      type: "POST",
      url: '{{ url('/api') }}/updateFEN',
      data: {
        'ma-phong': '{{ $roomCode }}',
        'FEN': newFEN
      },
      dataType: 'text'
    });
    if (newFEN == game.fen()) {
      // my move
      board.position(newFEN, true);
      game.load(newFEN);
    } else {
      // opponent's move
      board.position(newFEN, true);
      game.load(newFEN);
      nuocCo.play();
      if (game.game_over()) {
        hetTran.play();
        $('#game-over').removeClass('d-none').addClass('d-inline-block');
      }
    }
  }
  updateStatus();
};
</script>
@endsection