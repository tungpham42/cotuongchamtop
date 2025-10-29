@extends('en.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="Improve your chess skills with level {{ $levelTxt }}">You are training with computer</h5>
@endsection
@section('aboveContent')
@endsection
@section('rightSide')
<p class="w-100 text-center m-0">
  <span class="rounded p-0 d-block" id="game-status"></span>
</p>
<p class="w-100 text-center mx-0 mb-0 mt-2">
  <span class="rounded d-none" id="game-over"><i class="fad fa-flag-checkered"></i> GAME OVER</span>
</p>
<div class="sharethis-inline-reaction-buttons"></div>
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
<div class="dropup mx-auto text-center my-3">
  <button class="btn btn-danger btn-lg dropdown-toggle pulse-red" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span data-toggle="tooltip" data-placement="top" title="Play with someone in a room"><i class="fad fa-gamepad-alt"></i> Play online</span>
  </button>
  <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> Switch side</a>
  @include('common.volumeBtn')
  @include('common.tourBtn')
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ URL::to('/') }}/room/{{ md5(time()) }}">
    <a data-toggle="tooltip" data-placement="bottom" title="Play with password" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> Private</a>
    @if ($randomRoom != null)
    <a data-toggle="tooltip" data-placement="bottom" title="Play in random Public room" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ URL::to('/') }}/room/{{ $randomRoom['code'] }}/random"><i class="fas fa-random text-dark"></i> Random</a>
    <a data-toggle="tooltip" data-placement="bottom" title="Waiting list" id="room-list" class="dropdown-item rooms-list" style="cursor: pointer !important;" href="{{ URL::to('/rooms') }}"><i class="fas fa-list-alt text-dark"></i> Rooms list</a>
    @endif
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
{{-- @include('common.qrCode') --}}
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
  $('#game-status').html('Thinking... <i class="fas fa-spinner fa-spin"></i>');

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
  var moveColor = 'Red';

  if (game.turn() === 'b') {
    moveColor = 'Black';
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' is checkmated';
  }
  // draw?
  else if (game.in_draw()) {
    status = 'Draw';
  }
  // game still on
  else {
    status = "It's " + moveColor + "'s turn";

    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + ' is in check';
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
      $('#header-status').html(': ' + status + ' - Game over');
    }
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> Game over');
    isComputerThinking = false;
  }

  if (game.fen().includes('resign') && !resignAlertShown) {
    if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
      $('#header-status').html(': ' + status + ' - Resigned');
    }

    if (typeof bootbox !== 'undefined') {
      bootbox.alert({
        message: '<i class="fad fa-flag-checkered"></i> Resigned',
        locale: 'en',
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

    $('#game-over').html('<i class="fad fa-flag-checkered"></i> Resigned');
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

// Add CSS for loading state
const style = document.createElement('style');
style.textContent = `
  .fa-spinner {
    margin-left: 5px;
  }
  .disabled {
    opacity: 0.5;
    pointer-events: none;
  }
  .highlight {
    background-color: #ffeb3b !important;
    opacity: 0.6;
  }
`;
document.head.appendChild(style);

// Initialize computer move if black starts first
@if(isset($computerStarts) && $computerStarts)
$(document).ready(function() {
  setTimeout(makeBestMove, 1000);
});
@endif
</script>
@include('en.layout.partials.puzzles')
@endsection
