@extends('zh.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="提高你的国际象棋技能">你一个人玩</h5>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="1" data-intro="如果您没有线索，请点击这里" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> 辞职</a>
  <a data-step="2" data-intro="如果您想回到上一步，请点击这里" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> 打开</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="3" data-intro="请点击这里与电脑对战" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ url('/zh') }}"><i class="fad fa-desktop"></i> 用计算机训练</a>
  <a data-step="4" data-intro="如果您想重新开始，请点击这里" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> 重新启动</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="5" data-intro="请在手机上打开这个页面">
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

  var moveColor = '红色'
  if (game.turn() === 'b') {
    moveColor = '黑色'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + '将死'
  }

  // draw?
  else if (game.in_draw()) {
    status = '平仓'
  }

  // game still on
  else {
    status = moveColor + "转向移动"

    // check?
    if (game.in_check()) {
      status += ',' + moveColor + '在检查中'
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
    $('#header-status').html(': '+status+' - 游戏结束了');
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> 游戏结束了');
  }
  if (game.fen().includes('resign')) {
    $('#header-status').html(': '+status+' - 听天由命');
    bootbox.alert({
      message: '<i class="fad fa-flag-checkered"></i> 听天由命',
      locale: 'zh',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      }
    });
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> 听天由命');
    $('#resign').addClass('disabled').attr('aria-disabled', true);
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
  onMoveEnd: onMoveEnd
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
@include('zh.layout.partials.puzzles')
@endsection
