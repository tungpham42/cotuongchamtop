@extends('layout.gamelayout')
@section('aboveContent')
<p id="room-code" class="w-100 text-center mt-2">
  <span class="alert alert-info d-inline-block" role="alert"><i class="fad fa-trophy-alt"></i> Mã phòng: {{ $roomCode }}</span>
</p>
@if ($request->is('phong/{roomCode}'))
<p class="w-100 text-center mt-2">
  <a class="w-25 mx-auto btn btn-success btn-sm" target="_blank" href="/phong/{{ $roomCode }}/duoc-moi"><i class="fad fa-external-link-alt"></i> Mời bạn bè cùng chơi</a>
</p>
<div id="copy-url" class="input-group mb-2 w-50 mx-auto">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input type="text" class="form-control" id="url" aria-describedby="url-addon" value="https://cotuong.top/phong/{{ $roomCode }}/duoc-moi" />
</div>
<p class="w-100 text-center mt-2">
  <span class="side-color red">QUÂN ĐỎ</span>
</p>
@elseif ($request->is('phong/{roomCode}/duoc-moi'))
<p class="w-100 text-center mt-2">
  <span class="alert alert-success d-inline-block" role="alert">Đã được mời</span>
  <span class="side-color black">QUÂN ĐEN</span>
</p>
@elseif ($request->is('phong/{roomCode}/{color}'))
  @if (color == 'do')
<p class="w-100 text-center mt-2">
  <span class="side-color red">QUÂN ĐỎ</span>
</p>
  @elseif (color == 'den')
<p class="w-100 text-center mt-2">
  <span class="side-color black">QUÂN ĐEN</span>
</p>
  @endif
@endif
@endsection
@section('belowContent')
<p class="w-100 text-center mt-4">
  <a class="w-25 btn btn-danger btn-lg" href="/phong/{{ $roomCode }}/do"><i class="fad fa-chess-clock-alt"></i> Bên ĐỎ</a>
  <a class="w-25 btn btn-dark btn-lg" href="/phong/{{ $roomCode }}/den"><i class="fad fa-chess-clock"></i> Bên ĐEN</a>
</p>
<script>
let board = null;
let game = new Xiangqi();

function writeTextFile(roomCode) {
  $.ajax({
    type: "POST",
    url: '/updateFEN',
    data: {
      'ma-phong': roomCode,
      'FEN': game.fen()
    },
    dataType: 'text'
  });
  $('#FEN').val(game.fen());
}

function manipulateRoom(roomCode) {
  $.ajax({
    type: "GET",
    url: '/readFEN',
    data: {
      'ma-phong': roomCode
    },
    dataType: 'text'
  }).done(function(data) {
    var boardPosition = data.substring(0, data.indexOf(" - -") - 2);
    var gameTurn = data.substring(data.indexOf(" - -") - 1, data.indexOf(" - -"));
    if (data != game.fen()) {
      board.position(boardPosition, false);
      game.load(data);
      nuocCo.play();
      if (game.game_over()) {
        hetTran.play();
        $('#game-over').removeClass('d-none').addClass('d-inline-block');
      }
    }
    if (gameTurn === 'r') {
      $('#game-status').removeClass('black').addClass('red').html('<i class="fal fa-chess-clock-alt"></i> Tới lượt ĐỎ');
    } else if (gameTurn === 'b') {
      $('#game-status').removeClass('red').addClass('black').html('<i class="fal fa-chess-clock"></i> Tới lượt ĐEN');
    }
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
  if (move === null) return 'snapback';
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
  // board.position(game.fen());
  $('#FEN').val(game.fen());
  nuocCo.play();
  writeTextFile('{{ $roomCode }}');
  if (game.game_over()) {
    hetTran.play();
    $('#game-over').removeClass('d-none').addClass('d-inline-block');
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
  @if (@isset($_GET['duoc-moi']) || @isset($_GET['den'])) 'orientation: "black"' @else 'orientation: "red"' @endif
  //pieceTheme: '/static/img/xiangqipieces/traditional/{piece}.svg'

};
board = Xiangqiboard('ban-co', config);
if (game.turn() === 'r') {
  $('#game-status').removeClass('black').addClass('red').html('<i class="fal fa-chess-clock-alt"></i> Tới lượt ĐỎ');
} else if (game.turn() === 'b') {
  $('#game-status').removeClass('red').addClass('black').html('<i class="fal fa-chess-clock"></i> Tới lượt ĐEN');
}
function updateRoom() {
  manipulateRoom('{{ $roomCode }}');
}
setInterval(updateRoom, 1000);
</script>
@endsection