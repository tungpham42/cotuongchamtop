@extends('ja.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="チェスのスキルを向上させる">あなたはコンピューターでボードを解いています</h5>
@endsection
@section('aboveContent')
<h5 class="text-center my-2">ボードレベル:{{ $levelTxt }}</h5>
<div class="level dropup mx-auto text-center my-1">
  <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="私の適切なレベルを選びましょう" aria-expanded="false">
    <i class="fad fa-robot"></i> ボードレベルを選択
  </button>
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
    <a class="add-fen dropdown-item{{ $levelTxt === '初心者' ? ' active disabled' : '' }}" href="{{ url('/shoshinsha-bodo') }}" style="cursor: pointer !important;">初心者</a>
    <a class="add-fen dropdown-item{{ $levelTxt === '簡単' ? ' active disabled' : '' }}" href="{{ url('/kantan-bodo') }}" style="cursor: pointer !important;">簡単</a>
    <a class="add-fen dropdown-item{{ $levelTxt === 'ツジョ' ? ' active disabled' : '' }}" href="{{ url('/tsujo-bodo') }}" style="cursor: pointer !important;">ツジョ</a>
    <a class="add-fen dropdown-item{{ $levelTxt === 'ハード' ? ' active disabled' : '' }}" href="{{ url('/hado-bodo') }}" style="cursor: pointer !important;">ハード</a>
    <a class="add-fen dropdown-item{{ $levelTxt === '最も難しい' ? ' active disabled' : '' }}" href="{{ url('/mottomo-muzukashi-bodo') }}" style="cursor: pointer !important;">最も難しい</a>
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
  <input data-step="6" data-intro="リンクをコピーして友達を招待して一緒に遊ぶにはここをクリックしてください" type="text" class="form-control" id="url" value="{{ url()->current() }}">
</div>
<script>
$('#copy-url').on('click', function() {
  copyToClipboard('#url');
  selectText('#url');
  $(this).tooltip('update');
});
</script>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="2" data-intro="手掛かりが尽きた場合はここをクリックしてください" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> 辞任</a>
  <a data-step="3" data-intro="前の手に戻りたい場合はここをクリックしてください" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> 元に戻す</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="4" data-intro="友達とこのボードをプレイするにはここをクリックしてください" id="board" class="add-fen w-25 btn btn-dark btn-lg" href="{{ url('/bodo') }}"><i class="fad fa-user"></i> 友達と遊ぶ</a>
  <a data-step="5" data-intro="最初からやり直したい場合はここをクリックしてください" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> 再起動</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="7" data-intro="このページをモバイルで開いてください">
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
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> ゲームオーバー');
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
      }
    });
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> 辞任');
    $('#resign').addClass('disabled').attr('aria-disabled', true);
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
@include('ja.layout.partials.puzzles')
@endsection