@extends('amp.en.layout.gamelayout')
@section('aboveContent')
<h3 class="text-center my-2">Playing with AI</h3>
<h4 class="text-center my-2">Level: {{ $levelTxt }}</h4>
<div class="dropup mx-auto text-center">
  <button class="btn btn-lg btn-danger dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fal fa-trophy"></i> Choose level
  </button>
  <div class="dropdown-menu" aria-labelledby="levelDropdown">
    <a class="dropdown-item" href="{{ url('/amp/newbie') }}">Newbie</a>
    <a class="dropdown-item" href="{{ url('/amp/easy') }}">Easy</a>
    <a class="dropdown-item" href="{{ url('/amp/normal') }}">Normal</a>
    <a class="dropdown-item" href="{{ url('/amp/hard') }}">Hard</a>
    <a class="dropdown-item" href="{{ url('/amp/hardest') }}">Hardest</a>
  </div>
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-4">
  <a style="color: white" class="w-25 btn btn-dark btn-lg" href="{{ url('/amp/play-alone') }}"><i class="fad fa-user"></i> Play alone</a>
  <a style="color: white" id="reset" class="w-25 btn btn-danger btn-lg"><i class="fad fa-redo-alt"></i> Restart</a>
</p>
<script>
let board = null;
let game = new Xiangqi();

function removeGreySquares () {
  $('#ban-co .square-2b8ce').removeClass('highlight');
}

function greySquare (square) {
  let $square = $('#ban-co .square-' + square);

  $square.addClass('highlight');
}

function onDragStart (source, piece, position, orientation) {
  if (game.in_checkmate() === true || game.in_draw() === true || piece.search(/^b/) !== -1) {
    return false;
  }
}

function makeBestMove() {
  var aiWorker = new Worker('/js/ai-worker.js');
  var bestMove;
  aiWorker.postMessage({
    fen: game.fen(),
    depth: {{ $level }}
  });
  aiWorker.onmessage = function(e) {
    bestMove = e.data;
    console.log(bestMove);
    game.ugly_move(bestMove);
    board.position(game.fen());
    nuocCo.play();
    updateStatus();
  }
}

function onDrop (source, target) {
  // see if the move is legal
  let move = game.move({
    from: source,
    to: target,
    promotion: 'q' // NOTE: always promote to a queen for example simplicity
  });

  // illegal move
  if (move === null) return 'snapback';
  updateStatus();
  // make random legal move for black
  //window.setTimeout(makeRandomMove, 250);
  makeBestMove();
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
// update the board position after the piece snap
// for castling, en passant, pawn promotion
function onSnapEnd () {
  board.position(game.fen());
  nuocCo.play();
  updateStatus();
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
    hetTran.play();
    $('#header-status').html(': '+status+' - Game over');
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
  //pieceTheme: '/static/img/xiangqipieces/traditional/{piece}.svg'
};
board = Xiangqiboard('ban-co', config);
updateStatus();
$(document).ready(function() {
  $('#FEN').val(game.fen());
});
$('#reset').on('click', function() {
  board.position('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR');
  game.load('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1');
  $('#game-status').removeClass('black').addClass('red');
  updateStatus();
  $('#game-over').removeClass('d-inline-block').addClass('d-none');
});
</script>
@endsection