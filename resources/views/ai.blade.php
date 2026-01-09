@extends('layout.gamelayout')
@section('aboveBoard')
@php
switch ($levelTxt) {
  case 'Mới chơi':
    $action = 'tập chơi';
    break;
  case 'Dễ':
    $action = 'thư giãn';
    break;
  case 'Bình thường':
    $action = 'chơi';
    break;
  case 'Khó':
    $action = 'đấu';
    break;
  case 'Khó nhất':
    $action = 'đấu trí';
    break;
}
@endphp
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="Cấp độ máy: {{ $levelTxt }}">Bạn đang {{ $action }} với máy<span id="puzzle-title"></span></h5>
@endsection
@section('aboveContent')

@endsection
@section('rightSide')
<p class="w-100 text-center m-0">
  <span class="rounded p-0 d-block" id="game-status"></span>
</p>
<p class="w-100 text-center mx-0 mb-0 mt-2">
  <span class="rounded d-none" id="game-over"><i class="fad fa-flag-checkered"></i> HẾT TRẬN</span>
</p>
<div class="sharethis-inline-reaction-buttons"></div>
<h5 class="text-center my-1">Cấp độ: {{ $levelTxt }}</h5>
<div class="level dropup mx-auto text-center my-1">
  <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="Hãy chọn cấp độ phù hợp với bạn nhé" aria-expanded="false">
    <i class="fad fa-robot"></i> Chọn cấp độ máy
  </button>
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
    <a class="dropdown-item{{ $levelTxt === 'Mới chơi' ? ' active disabled' : '' }}" href="{{ url('/moi-choi') }}" style="cursor: pointer !important;">Mới chơi</a>
    <a class="dropdown-item{{ $levelTxt === 'Dễ' ? ' active disabled' : '' }}" href="{{ url('/de') }}" style="cursor: pointer !important;">Dễ</a>
    <a class="dropdown-item{{ $levelTxt === 'Bình thường' ? ' active disabled' : '' }}" href="{{ url('/binh-thuong') }}" style="cursor: pointer !important;">Bình thường</a>
    <a class="dropdown-item{{ $levelTxt === 'Khó' ? ' active disabled' : '' }}" href="{{ url('/kho') }}" style="cursor: pointer !important;">Khó</a>
    <a class="dropdown-item{{ $levelTxt === 'Khó nhất' ? ' active disabled' : '' }}" href="{{ url('/kho-nhat') }}" style="cursor: pointer !important;">Khó nhất</a>
  </div>
</div>
<div class="dropup mx-auto text-center my-1">
  <button class="btn btn-danger btn-lg dropdown-toggle" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span data-toggle="tooltip" data-placement="top" title="Đấu với bạn bè trong phòng"><i class="fad fa-gamepad-alt"></i> Chơi online</span>
  </button>
  <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> Đổi bên</a>
  @include('common.volumeBtn')
  @include('common.tourBtn')
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ URL::to('/') }}/phong/{{ md5(time()) }}">
    @if (!auth()->check())
    <a data-toggle="tooltip" data-placement="bottom" title="Đăng nhập để tham gia thi đấu" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="{{ URL::to('/dang-nhap') }}"><i class="fas fa-sign-in text-dark"></i> Đăng nhập</a>
    @else
    <a data-toggle="tooltip" data-placement="bottom" title="Thi đấu tính điểm và xếp hạng" id="create-room" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="javascript:createRoom();"><i class="fas fa-trophy-alt text-dark"></i> Thi đấu</a>
    @endif
    <a data-toggle="tooltip" data-placement="bottom" title="Chơi cần mật khẩu" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> Riêng tư</a>
    @if ($randomRoom != null)
    <a data-toggle="tooltip" data-placement="bottom" title="Chơi trong phòng Công khai ngẫu nhiên" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ URL::to('/') }}/phong/{{ $randomRoom['code'] }}/ngau-nhien"><i class="fas fa-random text-dark"></i> Ngẫu nhiên</a>
    @endif
    <a data-toggle="tooltip" data-placement="bottom" title="Tìm phòng trống" id="room-list" class="dropdown-item rooms-list" style="cursor: pointer !important;" href="{{ URL::to('/sanh-cho') }}"><i class="fas fa-list-alt text-dark"></i> Sảnh chờ</a>
  </div>
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="2" data-intro="Ấn vào đây nếu bạn không biết đi nước nào" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> Bỏ cuộc</a>
  <a data-step="3" data-intro="Ấn vào đây để quay lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> Đi lại</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="5" data-intro="Nơi luyện tập với chính mình nhé" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ url('/choi-mot-minh') }}"><i class="fad fa-user"></i> Một mình</a>
  <a data-step="4" data-intro="Ấn vào đây để chơi lại từ đầu" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> Chơi lại</a>
</p>
@include('layout.partials.kypho')
<script>
let board = null;
let game = new Xiangqi();
let isComputerThinking = false;
let resignAlertShown = false;
let kypho = null;

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

      if (move) {
        const moveResult = game.move(move);
        if (moveResult !== null) {
          if (kypho) {
            kypho.recordMove(moveResult);
          }
          board.position(game.fen());
          nuocCo.play();
          updateStatus();
        } else {
          console.error('Invalid move from engine:', data.best_move);
          makeRandomMove(); // Fallback
        }
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
    const moveResult = game.move(randomMove);
    if (moveResult !== null) {
      if (kypho) {
        kypho.recordMove(moveResult);
      }
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
  if (kypho) {
    kypho.recordMove(move);
  }

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
  var moveColor = 'Đỏ';

  if (game.turn() === 'b') {
    moveColor = 'Đen';
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' bị chiếu bí';
  }
  // draw?
  else if (game.in_draw()) {
    status = 'Hòa';
  }
  // game still on
  else {
    status = 'Tới lượt ' + moveColor + ' đi';

    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + ' đang bị chiếu';
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
    $('#header-status').html(': '+status);
  }

  if (game.game_over()) {
    if (typeof hetTran !== 'undefined') {
      hetTran.play();
    }
    if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
      $('#header-status').html(': '+status+' - Hết trận');
    }
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> Hết trận');
    isComputerThinking = false;
  }

  if (game.fen().includes('resign') && !resignAlertShown) {
    if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
      $('#header-status').html(': '+status+' - Đã bỏ cuộc');
    }

    if (typeof bootbox !== 'undefined') {
      bootbox.alert({
        message: '<i class="fad fa-flag-checkered"></i> Đã bỏ cuộc',
        locale: 'vi',
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

    $('#game-over').html('<i class="fad fa-flag-checkered"></i> Đã bỏ cuộc');
    $('#resign, #switch').addClass('disabled').attr('aria-disabled', true);
    config.draggable = false;
    isComputerThinking = false;
    resignAlertShown = true;
  }
  if (kypho) {
    kypho.updateControls();
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
  showNotation: true
};

// Initialize the board
board = Xiangqiboard('ban-co', config);

// Handle window resize
if (typeof $(window).resize === 'function') {
  $(window).resize(board.resize);
}

updateStatus();
kypho = KyPho.initLocal({
  board: board,
  startFen: game.fen(),
  isLive: function() { return !game.game_over(); }
});

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
    if (kypho) {
      kypho.setMoves(game.history());
    }
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
  if (kypho) {
    kypho.setMoves([]);
  }
});

$('.level.dropup .dropdown-item').each(function(){
  const activePointer = '<i class="far fa-hand-point-right"></i>  ';
  if ($(this).hasClass('active')) {
    $(this).prepend(activePointer);
  }
  $(this).on('click auxclick', function(e){
    window.location.href = $(this).attr('href');
  }).on('mouseenter mouseleave', function(){
    if ($(this).has('i').length) {
      $(this).find('i').remove();
    } else {
      $(this).prepend(activePointer);
    }
  });
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
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
@include('layout.partials.players')
@include('layout.partials.userPuzzles')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
{{-- @include('layout.partials.puzzles') --}}
{{-- @include('layout.partials.books') --}}
@endsection
