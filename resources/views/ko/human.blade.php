@extends('ko.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="게임 기술 향상">너는 혼자 놀고 있다</h5>
@endsection
@section('rightSide')
<p class="w-100 text-center m-0">
  <span class="rounded p-0 d-block" id="game-status"></span>
</p>
<p class="w-100 text-center mx-0 mb-0 mt-2">
  <span class="rounded d-none" id="game-over"><i class="fad fa-flag-checkered"></i> 게임 오버</span>
</p>
<div class="sharethis-inline-reaction-buttons"></div>
<div class="dropup mx-auto text-center my-3">
  <button class="btn btn-danger btn-lg dropdown-toggle pulse-red" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span data-toggle="tooltip" data-placement="top" title="방에서 누군가와 놀기"><i class="fad fa-gamepad-alt"></i> 온라인으로 재생</span>
  </button>
  <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> 스위치</a>
  @include('common.volumeBtn')
  @include('common.tourBtn')
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ URL::to('/') }}/bang/{{ md5(time()) }}">
    <a data-toggle="tooltip" data-placement="bottom" title="암호로 재생" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> 사적인</a>
    @if ($randomRoom != null)
    <a data-toggle="tooltip" data-placement="bottom" title="임의의 공용 룸에서 재생" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ URL::to('/') }}/bang/{{ $randomRoom['code'] }}/mujag-wiui"><i class="fas fa-random text-dark"></i> 무작위의</a>
    <a data-toggle="tooltip" data-placement="bottom" title="대기자 명단" id="room-list" class="dropdown-item rooms-list" style="cursor: pointer !important;" href="{{ URL::to('/bang-moglog') }}"><i class="fas fa-list-alt text-dark"></i> 방 목록</a>
    @endif
  </div>
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="1" data-intro="단서가 부족한 경우 여기를 클릭하세요" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> 사직하다</a>
  <a data-step="2" data-intro="이전 동작으로 돌아가고 싶으면 여기를 클릭하세요" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> 실행 취소</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="3" data-intro="컴퓨터와 함께 플레이하려면 여기를 클릭하세요" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ url('/ko') }}"><i class="fad fa-desktop"></i> 컴퓨터로 놀기</a>
  <a data-step="4" data-intro="처음부터 다시 시작하려면 여기를 클릭하세요" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> 다시 시작</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="5" data-intro="이 페이지를 모바일에서 열어주세요">
{{-- @include('common.qrCode') --}}
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
  board.position(game.fen());
  $('#FEN').val(game.fen());
  nuocCo.play();
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
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> 게임 오버');
  }
  if (game.fen().includes('resign') && !resignAlertShown) {
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
  position: 'start',
  onDragStart: onDragStart,
  onDrop: onDrop,
  onMouseoutSquare: onMouseoutSquare,
  onMouseoverSquare: onMouseoverSquare,
  onSnapEnd: onSnapEnd,
  onMoveEnd: onMoveEnd,
  showNotation: true
};
board = Xiangqiboard('ban-co', config);
$(window).resize(board.resize);
updateStatus();
$('#resign').on('click', function() {
  game.load(game.fen() + ' resign');
  updateStatus();
});
$('#undo').on('click', function(){
  game.undo();
  board.position(game.fen());
  nuocCo.play();
  updateStatus();
});
$('#switch').on('click', board.flip);
$('#reset').on('click', function() {
  board.position('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR');
  game.load('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1');
  $('#game-status').removeClass('black').addClass('red');
  updateStatus();
  $('#game-over').removeClass('d-inline-block').addClass('d-none');
  $('#resign').removeClass('disabled').attr('aria-disabled', false);
  config.draggable = true;
});
</script>
@include('ko.layout.partials.puzzles')
@endsection
