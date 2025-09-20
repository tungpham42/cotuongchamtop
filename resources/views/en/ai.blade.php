@extends('en.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="Improve your chess skills with level {{ $levelTxt }}">You are training with computer</h5>
@endsection
@section('aboveContent')
<h5 class="text-center my-1">Level: {{ $levelTxt }}</h5>
<div class="level dropup mx-auto text-center my-1">
  <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="Let's choose a suitable level for you" aria-expanded="false">
    <i class="fad fa-robot"></i> Choose computer level
  </button>
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
    <a class="dropdown-item{{ $levelTxt === 'Newbie' ? ' active disabled' : '' }}" href="{{ url('/newbie') }}" style="cursor: pointer !important;">Newbie</a>
    <a class="dropdown-item{{ $levelTxt === 'Easy' ? ' active disabled' : '' }}" href="{{ url('/easy') }}" style="cursor: pointer !important;">Easy</a>
    <a class="dropdown-item{{ $levelTxt === 'Normal' ? ' active disabled' : '' }}" href="{{ url('/normal') }}" style="cursor: pointer !important;">Normal</a>
    <a class="dropdown-item{{ $levelTxt === 'Hard' ? ' active disabled' : '' }}" href="{{ url('/hard') }}" style="cursor: pointer !important;">Hard</a>
    <a class="dropdown-item{{ $levelTxt === 'Hardest' ? ' active disabled' : '' }}" href="{{ url('/hardest') }}" style="cursor: pointer !important;">Hardest</a>
  </div>
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="2" data-intro="Click here if you are out of clues" id="resign" class="w-25 btn btn-dark btn-lg text-light"><i class="fad fa-flag"></i> Resign</a>
  <a data-step="3" data-intro="Click here if you want to go back to previous move" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> Undo</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="5" data-intro="This is a good place to practice alone" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ url('/play-alone') }}"><i class="fad fa-user"></i> Play alone</a>
  <a data-step="4" data-intro="Click here if your want to go all over again" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> Restart</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="6" data-intro="Open this page on mobile">
@include('common.qrCode')
</div>
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
  var aiWorker = new Worker('/js/xiangqi_bot_worker.js');
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
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> Game over');
  }
  if (game.fen().includes('resign')) {
    $('#header-status').html(': '+status+' - Resigned');
    bootbox.alert({
      message: '<i class="fad fa-flag-checkered"></i> Resigned',
      locale: 'en',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      }
    });
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> Resigned');
    $('#resign, #switch').addClass('disabled').attr('aria-disabled', true);
    config.draggable = false;
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
$(window).resize(board.resize);
updateStatus();
$(document).ready(function() {
  $('#FEN').val(game.fen());
});
$('#resign').on('click', function() {
  game.load(game.fen() + ' resign');
  updateStatus();
});
$('#undo').on('click', function(){
  if (game.turn() == 'r') {
    game.undo();
    game.undo();
    board.position(game.fen());
    nuocCo.play();
    updateStatus();
  }
});
$('#reset').on('click', function() {
  board.position('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR');
  game.load('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1');
  $('#game-status').removeClass('black').addClass('red');
  updateStatus();
  $('#game-over').removeClass('d-inline-block').addClass('d-none');
  $('#resign, #switch').removeClass('disabled').attr('aria-disabled', false);
  config.draggable = true;
});
$('.level.dropup .dropdown-item').each(function(){
  const activePointer = '<i class="far fa-hand-point-right"></i>  ';
  if ($(this).hasClass('active')) {
    $(this).prepend(activePointer);
  }
  $(this).on('click auxclick', function(e){
    if (removeTrailingSlash($(this).attr('href')) !== removeTrailingSlash(window.location.href)) {
      e.preventDefault();
      $('#AdSenseModal').attr('data-url', $(this).attr('href')).modal('show');
    } else {
      window.location.href = $(this).attr('href');
    }
  }).on('mouseenter mouseleave', function(){
    if ($(this).has('i').length) {
      $(this).find('i').remove();
    } else {
      $(this).prepend(activePointer);
    }
  });
});
</script>
@include('en.layout.partials.puzzles')
@endsection