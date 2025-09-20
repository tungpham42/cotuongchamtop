@extends('ja.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="チェスのスキルを向上させる">あなたはボードを解決しています</h5>
@endsection
@section('aboveContent')
<div class="dropup mx-auto text-center my-3">
  <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="私の適切なレベルを選びましょう" aria-expanded="false">
    <i class="fad fa-robot"></i> ボードレベルを選択
  </button>
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
    <a class="add-fen dropdown-item" href="{{ url('/shoshinsha-bodo') }}" style="cursor: pointer !important;">初心者</a>
    <a class="add-fen dropdown-item" href="{{ url('/kantan-bodo') }}" style="cursor: pointer !important;">簡単</a>
    <a class="add-fen dropdown-item" href="{{ url('/tsujo-bodo') }}" style="cursor: pointer !important;">ツジョ</a>
    <a class="add-fen dropdown-item" href="{{ url('/hado-bodo') }}" style="cursor: pointer !important;">ハード</a>
    <a class="add-fen dropdown-item" href="{{ url('/mottomo-muzukashi-bodo') }}" style="cursor: pointer !important;">最も難しい</a>
  </div>
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> 以下のリンクを送信して、フレンドをプレイに招待します。
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto pulse-light" data-toggle="tooltip" data-placement="bottom" data-original-title="クリックしてコピー">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="2" data-intro="リンクをコピーして友達を招待して一緒に遊ぶにはここをクリックしてください" type="text" class="form-control" id="url" value="{{ url()->current() }}">
</div>
<script>
$('#copy-url').on('click', function() {
  copyToClipboard('#url');
  selectText('#url');
  $(this).tooltip('update');
});
</script>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="3" data-intro="別の色に変更するにはここをクリックしてください" id="switch" class="w-25 btn btn-dark btn-lg"><i class="fad fa-chess-board"></i> スイッチ側</a>
  <a data-step="4" data-intro="前の手に戻りたい場合はここをクリックしてください" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo"></i> 元に戻す</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="5" data-intro="このページをモバイルで開いてください">
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

  var moveColor = '赤'
  if (game.turn() === 'b') {
    moveColor = '黒'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + 'はチェックメイト'
  }

  // draw?
  else if (game.in_draw()) {
    status = '描画位置'
  }

  // game still on
  else {
    status = moveColor + "が動く番"

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
@include('ja.layout.partials.puzzles')
@endsection