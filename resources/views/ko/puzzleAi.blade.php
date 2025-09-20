@extends('ko.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="게임 기술 향상">너는 컴퓨터로 퍼즐을 풀고 있다</h5>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="1" data-intro="단서가 부족한 경우 여기를 클릭하세요" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> 사직하다</a>
  <a data-step="2" data-intro="이전 동작으로 돌아가고 싶으면 여기를 클릭하세요" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> 실행 취소</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="3" data-intro="퍼즐 보드를 설정하려면 여기를 클릭하세요" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ url('/peojeul') }}"><i class="fad fa-puzzle-piece"></i> 퍼즐</a>
  <a data-step="4" data-intro="처음부터 다시 시작하려면 여기를 클릭하세요" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> 다시 시작</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="5" data-intro="이 페이지를 모바일에서 열어주세요">
@include('common.qrCode')
</div>
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> 아래 링크를 전송하여 친구를 초대하여 게임을 진행합니다.
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto pulse-light" data-toggle="tooltip" data-placement="bottom" data-original-title="복사하려면 클릭">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="6" data-intro="여기를 클릭하여 링크를 복사하고 친구를 초대하여 함께 플레이하세요" type="text" class="form-control" id="url" value="{{ url()->current() }}">
</div>
<script>
$('#copy-url').on('click', function() {
  copyToClipboard('#url');
  selectText('#url');
  $(this).tooltip('update');
});
</script>
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
  //window.setTimeout(makeRandomMove, 1000);
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

  var moveColor = '빨간'
  if (game.turn() === 'b') {
    moveColor = '검은색'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + '은 체크메이트에 있다'
  }

  // draw?
  else if (game.in_draw()) {
    status = '그린위치'
  }

  // game still on
  else {
    status = moveColor + "움직일 차례"

    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + '이 체크되어 있다'
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
    $('#header-status').html(': '+status+' - 게임 오버');
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> 게임 오버');
  }
  if (game.fen().includes('resign')) {
    $('#header-status').html(': '+status+' - 사임');
    bootbox.alert({
      message: '<i class="fad fa-flag-checkered"></i> 사임',
      locale: 'ko',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      }
    });
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> 사임');
    $('#resign, #switch').addClass('disabled').attr('aria-disabled', true);
    config.draggable = false;
  }
}
let config = {
  draggable: true,
  position: '{{ $fen }}',
  onDragStart: onDragStart,
  onDrop: onDrop,
  onMouseoutSquare: onMouseoutSquare,
  onMouseoverSquare: onMouseoverSquare,
  onSnapEnd: onSnapEnd,
  showNotation: true,
  //pieceTheme: '/static/img/xiangqipieces/traditional/{piece}.svg'
};
board = Xiangqiboard('ban-co', config);
$(window).resize(board.resize);
game.load('{{ $fen }}');
updateStatus();
$(document).ready(function() {
  $('#FEN').val(game.fen());
  if (game.turn() === 'b') {
    // board.flip();
    makeBestMove();
  }
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
  board.position('{{ $fen }}');
  game.load('{{ $fen }}');
  // board.position('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR');
  // game.load('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1');
  $('#game-status').removeClass('black').addClass('red');
  updateStatus();
  $('#game-over').removeClass('d-inline-block').addClass('d-none');
  $('#resign').removeClass('disabled').attr('aria-disabled', false);
  config.draggable = true;
});
$('#board').on('click auxclick', function(e){
  e.preventDefault();
  $('#AdSenseModal').attr('data-url', $(this).attr('href') + '/' + game.fen()).modal('show');
});
$('.level.dropup .dropdown-item').each(function(){
  const activePointer = '<i class="far fa-hand-point-right"></i>  ';
  if ($(this).hasClass('active')) {
    $(this).prepend(activePointer);
  }
  $(this).on('click auxclick', function(e){
    if (removeTrailingSlash($(this).attr('href') + '/' + game.fen()) !== removeTrailingSlash(window.location.href)) {
      e.preventDefault();
      $('#AdSenseModal').attr('data-url', $(this).attr('href') + '/' + game.fen()).modal('show');
    } else {
      window.location.href = $(this).attr('href') + '/' + game.fen();
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
@include('ko.layout.partials.puzzles')
@endsection