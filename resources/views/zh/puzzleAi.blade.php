@extends('zh.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="你正在用计算机解黑板">你正在用计算机解黑板</h5>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="1" data-intro="如果您没有线索，请点击这里" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> 辞职</a>
  <a data-step="2" data-intro="如果您想回到上一步，请点击这里" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> 打开</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="3" data-intro="点击这里设置拼图板" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ url('/mi') }}"><i class="fad fa-puzzle-piece"></i> 谜</a>
  <a data-step="4" data-intro="如果您想重新开始，请点击这里" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> 重新启动</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="5" data-intro="请在手机上打开这个页面">
@include('common.qrCode')
</div>
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> 通过发送下面的链接邀请朋友玩。
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto pulse-light" data-toggle="tooltip" data-placement="bottom" data-original-title="点击复制">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="6" data-intro="请点击这里复制链接并邀请朋友一起玩" type="text" class="form-control" id="url" value="{{ url()->current() }}">
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
let isComputerThinking = false;
let resignAlertShown = false;

function removeGreySquares () {
  $('#ban-co .square-2b8ce').removeClass('highlight');
}

function greySquare (square) {
  let $square = $('#ban-co .square-' + square);
  $square.addClass('highlight');
}

function onDragStart (source, piece, position, orientation) {
  if (game.in_checkmate() === true || game.in_draw() === true ||
      piece.search(/^b/) !== -1 || isComputerThinking) {
    return false;
  }
}

// Use Pikafish engine for AI moves
async function makeBestMove() {
  if (isComputerThinking || game.game_over()) return;

  isComputerThinking = true;
  $('#game-status').html('Máy đang suy nghĩ... <i class="fas fa-spinner fa-spin"></i>');

  try {
    const response = await fetch('/api/xiangqi/best-move', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        fen: game.fen(),
        timeout: getTimeoutByLevel({{ $level }})
      })
    });

    const data = await response.json();

    if (data.success && data.best_move) {
      // Convert engine move format to Xiangqi.js format
      const move = convertEngineMoveToXiangqiJS(data.best_move);

      if (move && game.move(move) !== null) {
        board.position(game.fen());
        nuocCo.play();
        updateStatus();
      } else {
        console.error('Invalid move from engine:', data.best_move);
        makeRandomMove(); // Fallback
      }
    } else {
      console.error('Engine error:', data.error);
      makeRandomMove(); // Fallback to random move
    }
  } catch (error) {
    console.error('Request failed:', error);
    makeRandomMove(); // Fallback to random move
  } finally {
    isComputerThinking = false;
  }
}

// Convert engine move format ("h2e2") to Xiangqi.js move object
function convertEngineMoveToXiangqiJS(engineMove) {
  if (!engineMove || engineMove.length !== 4) return null;

  const from = engineMove.substring(0, 2);
  const to = engineMove.substring(2, 4);

  return {
    from: from,
    to: to
  };
}

// Get timeout based on level
function getTimeoutByLevel(level) {
  const timeouts = {
    1: 1000,   // Mới chơi
    2: 2000,   // Dễ
    3: 3000,   // Bình thường
    4: 5000,   // Khó
    5: 10000   // Khó nhất
  };
  return timeouts[level] || 3000;
}

// Fallback function if engine fails
function makeRandomMove() {
  const moves = game.moves({verbose: true});
  if (moves.length > 0) {
    const randomMove = moves[Math.floor(Math.random() * moves.length)];
    if (game.move(randomMove) !== null) {
      board.position(game.fen());
      nuocCo.play();
      updateStatus();
    }
  }
}

function onDrop (source, target) {
  if (isComputerThinking) return 'snapback';

  // see if the move is legal
  let move = game.move({
    from: source,
    to: target,
    promotion: 'q'
  });

  // illegal move
  if (move === null) return 'snapback';

  updateStatus();

  // If it's computer's turn after player move
  if (!game.game_over() && game.turn() === 'b') {
    setTimeout(makeBestMove, 500);
  }
}

function onMouseoverSquare (square, piece) {
  if (isComputerThinking) return;

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
  nuocCo.play();
  updateStatus();
}

function updateStatus () {
  var status = '';
  var moveColor = '红方';

  if (game.turn() === 'b') {
    moveColor = '黑方';
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' 被将死';
  }
  // draw?
  else if (game.in_draw()) {
    status = '平局';
  }
  // game still on
  else {
    status = '轮到 ' + moveColor + ' 走棋';

    // check?
    if (game.in_check()) {
      status += '，' + moveColor + ' 正在被将军';
      if ((board.orientation() == 'red' && game.turn() === 'r') ||
          (board.orientation() == 'black' && game.turn() === 'b')) {
        $('#checkmateText').show();
      }
    } else {
      $('#checkmateText').hide();
    }
  }

  if (game.turn() === 'r') {
    $('#game-status').removeClass('black').addClass('red');
  } else if (game.turn() === 'b') {
    $('#game-status').removeClass('red').addClass('black');
  }

  $('#game-status').html(status);

  if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
    $('#header-status').html('：' + status);
  }

  if (game.game_over()) {
    if (typeof hetTran !== 'undefined') {
      hetTran.play();
    }
    if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
      $('#header-status').html('：' + status + ' - 对局结束');
    }
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> 对局结束');
    isComputerThinking = false;
  }

  if (game.fen().includes('resign') && !resignAlertShown) {
    if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
      $('#header-status').html('：' + status + ' - 认输');
    }

    if (typeof bootbox !== 'undefined') {
      bootbox.alert({
        message: '<i class="fad fa-flag-checkered"></i> 认输',
        locale: 'zh',
        centerVertical: true,
        closeButton: false,
        size: 'small',
        buttons: {
          ok: {
            className: 'btn-danger'
          }
        }
      });
    }

    $('#game-over').html('<i class="fad fa-flag-checkered"></i> 认输');
    $('#resign, #switch').addClass('disabled').attr('aria-disabled', true);
    config.draggable = false;
    isComputerThinking = false;
    resignAlertShown = true;
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
@include('zh.layout.partials.puzzles')
@endsection
