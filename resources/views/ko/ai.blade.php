@extends('ko.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="게임 기술 향상">당신은 컴퓨터로 훈련하고 있습니다</h5>
@endsection
@section('aboveContent')
<h5 class="text-center my-1">레벨: {{ $levelTxt }}</h5>
<div class="level dropup mx-auto text-center my-1">
  <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="당신에게 적합한 레벨을 선택해 봅시다" aria-expanded="false">
    <i class="fad fa-robot"></i> 컴퓨터 수준 선택
  </button>
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
    <a class="dropdown-item{{ $levelTxt === '뉴비' ? ' active disabled' : '' }}" href="{{ url('/nyubi') }}" style="cursor: pointer !important;">뉴비</a>
    <a class="dropdown-item{{ $levelTxt === '쉬운' ? ' active disabled' : '' }}" href="{{ url('/iji') }}" style="cursor: pointer !important;">쉬운</a>
    <a class="dropdown-item{{ $levelTxt === '노멀' ? ' active disabled' : '' }}" href="{{ url('/nomol') }}" style="cursor: pointer !important;">노멀</a>
    <a class="dropdown-item{{ $levelTxt === '하드' ? ' active disabled' : '' }}" href="{{ url('/hadeu') }}" style="cursor: pointer !important;">하드</a>
    <a class="dropdown-item{{ $levelTxt === '가장 단단한' ? ' active disabled' : '' }}" href="{{ url('/gajang-dandanhan') }}" style="cursor: pointer !important;">가장 단단한</a>
  </div>
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="2" data-intro="단서가 부족한 경우 여기를 클릭하세요" id="resign" class="w-25 btn btn-dark btn-lg text-light"><i class="fad fa-flag"></i> 사직하다</a>
  <a data-step="3" data-intro="이전 동작으로 돌아가고 싶으면 여기를 클릭하세요" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> 실행 취소</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="5" data-intro="이곳은 혼자 연습하기 좋은 장소입니다" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ url('/honja-nolda') }}"><i class="fad fa-user"></i> 혼자 놀다</a>
  <a data-step="4" data-intro="처음부터 다시 시작하려면 여기를 클릭하세요" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> 다시 시작</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="6" data-intro="이 페이지를 모바일에서 열어주세요">
@include('common.qrCode')
</div>
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
    1: 500,   // Mới chơi
    2: 1000,   // Dễ
    3: 1500,   // Bình thường
    4: 2000,   // Khó
    5: 2500   // Khó nhất
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
  var moveColor = '빨강';

  if (game.turn() === 'b') {
    moveColor = '검정';
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' 이(가) 체크메이트되었습니다';
  }
  // draw?
  else if (game.in_draw()) {
    status = '무승부';
  }
  // game still on
  else {
    status = moveColor + ' 의 차례입니다';

    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + ' 이(가) 체크 상태입니다';
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
    $('#header-status').html(': ' + status);
  }

  if (game.game_over()) {
    if (typeof hetTran !== 'undefined') {
      hetTran.play();
    }
    if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
      $('#header-status').html(': ' + status + ' - 게임 종료');
    }
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> 게임 종료');
    isComputerThinking = false;
  }

  if (game.fen().includes('resign') && !resignAlertShown) {
    if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
      $('#header-status').html(': ' + status + ' - 기권');
    }

    if (typeof bootbox !== 'undefined') {
      bootbox.alert({
        message: '<i class="fad fa-flag-checkered"></i> 기권',
        locale: 'ko',
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

    $('#game-over').html('<i class="fad fa-flag-checkered"></i> 기권');
    $('#resign, #switch').addClass('disabled').attr('aria-disabled', true);
    config.draggable = false;
    isComputerThinking = false;
    resignAlertShown = true;
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

// Handle window resize
if (typeof $(window).resize === 'function') {
  $(window).resize(board.resize);
}

updateStatus();

$(document).ready(function() {
  if (typeof $('#FEN') !== 'undefined' && $('#FEN').length) {
    $('#FEN').val(game.fen());
  }
});

$('#resign').on('click', function() {
  if (isComputerThinking) return;
  game.load(game.fen() + ' resign');
  updateStatus();
});

$('#undo').on('click', function(){
  if (isComputerThinking) return;

  if (game.history().length >= 2) {
    game.undo();
    game.undo();
    board.position(game.fen());
    if (typeof nuocCo !== 'undefined') {
      nuocCo.play();
    }
    updateStatus();
  }
});

$('#switch').on('click', function() {
  if (isComputerThinking) return;
  board.flip();
});

$('#reset').on('click', function() {
  isComputerThinking = false;
  resignAlertShown = false;
  board.position('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR');
  game.load('rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1');
  $('#game-status').removeClass('black').addClass('red');
  updateStatus();
  $('#game-over').removeClass('d-inline-block').addClass('d-none');
  $('#resign, #switch').removeClass('disabled').attr('aria-disabled', false);
  config.draggable = true;
});
</script>
@include('ko.layout.partials.puzzles')
@endsection
