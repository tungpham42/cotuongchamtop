@extends('ko.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="게임 기술 향상">너는 보드를 풀고 있다</h5>
@endsection
@section('aboveContent')
<div class="dropup mx-auto text-center my-1">
  <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="당신에게 적합한 레벨을 선택해 봅시다" aria-expanded="false">
    <i class="fad fa-robot"></i> 보드 레벨 선택
  </button>
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
    <a class="add-fen dropdown-item" href="{{ url('/nyubi-bodeu') }}" style="cursor: pointer !important;">뉴비</a>
    <a class="add-fen dropdown-item" href="{{ url('/iji-bodeu') }}" style="cursor: pointer !important;">쉬운</a>
    <a class="add-fen dropdown-item" href="{{ url('/nomol-bodeu') }}" style="cursor: pointer !important;">노멀</a>
    <a class="add-fen dropdown-item" href="{{ url('/hadeu-bodeu') }}" style="cursor: pointer !important;">하드</a>
    <a class="add-fen dropdown-item" href="{{ url('/gajang-dandanhan-bodeu') }}" style="cursor: pointer !important;">가장 단단한</a>
  </div>
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> 아래 링크를 전송하여 친구를 초대하여 게임을 진행합니다.
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto pulse-light" data-toggle="tooltip" data-placement="bottom" data-original-title="복사하려면 클릭">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="2" data-intro="여기를 클릭하여 링크를 복사하고 친구를 초대하여 함께 플레이하세요" type="text" class="form-control" id="url" value="{{ url()->current() }}">
</div>
<script>
$('#copy-url').on('click', function() {
  copyToClipboard('#url');
  selectText('#url');
  $(this).tooltip('update');
});
</script>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="3" data-intro="다른 색상으로 변경하려면 여기를 클릭하세요" id="switch" class="w-25 btn btn-dark btn-lg"><i class="fad fa-chess-board"></i> 스위치 측</a>
  <a data-step="4" data-intro="이전 동작으로 돌아가고 싶으면 여기를 클릭하세요" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo"></i> 실행 취소</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="5" data-intro="이 페이지를 모바일에서 열어주세요">
@include('common.qrCode')
</div>
<script>
let board = null;
let $board = $('#ban-co');
let game = new Xiangqi();
let squareToHighlight = null;
let colorToHighlight = null;
let squareClass = 'square-2b8ce';

function removeHighlights (color) {
  $board.find('.' + squareClass).removeClass('highlight-' + color);
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
  
  if (move.color === 'r') {
    removeHighlights('red');
    $board.find('.square-' + source).addClass('highlight-red');
    $board.find('.square-' + target).addClass('highlight-red');
    squareToHighlight = target;
    colorToHighlight = 'red';
  } else {
    removeHighlights('black');
    $board.find('.square-' + source).addClass('highlight-black');
    $board.find('.square-' + target).addClass('highlight-black');
    squareToHighlight = target;
    colorToHighlight = 'black';
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
  board.position(board.fen());
  $('#FEN').val(game.fen());
  updateStatus();
}

function onMoveEnd () {
  $board.find('.square-' + squareToHighlight).addClass('highlight-' + colorToHighlight);
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
    $('#game-over').removeClass('d-none').addClass('d-inline-block');
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
  // onMoveEnd: onMoveEnd
  //pieceTheme: '/static/img/xiangqipieces/traditional/{piece}.svg'
};
board = Xiangqiboard('ban-co', config);
$(window).resize(board.resize);
game.load('{{ $fen }}');
updateStatus();
$(document).ready(function() {
  $('#FEN').val(game.fen());
  if (game.turn() === 'b') {
    board.flip();
  }
});
$('#undo').on('click', function() {
  game.undo();
  board.position(game.fen());
  removeHighlights('red');
  removeHighlights('black');
  $board.find('.square-' + squareToHighlight).removeClass('highlight-' + colorToHighlight);
  // board.position('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR');
  // game.load('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1');
  $('#game-status').removeClass('black').addClass('red');
  updateStatus();
  $('#game-over').removeClass('d-inline-block').addClass('d-none');
});
$('#switch').on('click', board.flip);
$('.add-fen').each(function(){
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