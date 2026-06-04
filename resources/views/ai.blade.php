@extends('layout.gamelayout')
@section('aboveBoard')
@php
$locale = app()->getLocale() ?: 'vi';
$levelUrls = [
    'vi' => ['moi-choi', 'de', 'binh-thuong', 'kho', 'kho-nhat', 'kien-tuong'],
    'en' => ['newbie', 'easy', 'normal', 'hard', 'hardest', 'master'],
    'ja' => ['shoshinsha', 'kantan', 'tsujo', 'hado', 'mottomo-muzukashi', 'masuta'],
    'ko' => ['nyubi', 'iji', 'nomol', 'hadeu', 'gajang-dandanhan', 'maseuteo'],
    'zh' => ['xinshou', 'rongyide', 'dianxingde', 'jiangude', 'zuinande', 'dashi'],
];
$urls = $levelUrls[$locale] ?? $levelUrls['vi'];
$actionMap = [
    '1' => 'tập chơi',
    '2' => 'thư giãn',
    '3' => 'chơi',
    '4' => 'đấu',
    '5' => 'đấu trí',
    '8' => 'khiêu chiến',
];

$action = $actionMap[$level ?? '3'] ?? 'chơi';
@endphp

@if(isset($level) && $level == '8')
    {{-- GRANDMASTER CUSTOM UI --}}
    <div class="grandmaster-header text-center mt-3 mb-2 p-3 rounded" style="background: linear-gradient(45deg, #1a0505, #3a0000); border: 2px solid #ffd700; color: #ffd700; box-shadow: 0 0 20px rgba(255, 215, 0, 0.4);">
        <img src="/img/xiangqipieces/wiki/rK.svg" width="55" class="mb-2" style="filter: drop-shadow(0 0 8px #ffd700);" alt="Grandmaster">
        <h4 class="text-uppercase font-weight-bold mb-1"><i class="fas fa-crown"></i> {{ __("Đại Kiện Tướng") }} Phạm Tùng</h4>
        <p class="m-0 small text-light">{{ __("Bạn đang khiêu chiến với đối thủ mạnh nhất.") }}</p>
    </div>
    <style>
        body.home { background-color: #120808 !important; color: #fff !important; }
        #ban-co { box-shadow: 0 0 30px rgba(255, 0, 0, 0.6); border: 2px solid #ffd700; }
        .btn-dark { background-color: #2b0a0a !important; border-color: #ffd700 !important; color: #ffd700 !important; }
        .btn-dark i * { color: #ffd700 !important; }
        .btn-dark:hover i, .btn-dark.text-light:hover i, .btn-dark:hover i *, .btn-dark.text-light:hover i *, .btn-danger i, .btn-danger.text-light i, .btn-danger i *, .btn-danger.text-light i * { color: #2b0a0a !important; }
        .btn:hover, .btn-danger, .btn-danger:hover { background-color: #ffd700 !important; background-image: linear-gradient(45deg, #ffd700, #ffaa00) !important; color: #2b0a0a !important; }
        .btn-danger { background: linear-gradient(45deg, #ffd700, #ffaa00) !important; color: #000 !important; border: none !important; }
        #game-status.black { color: #ffd700 !important; } /* Make black text visible on dark bg */
        #game-status.red { color: #ff4444 !important; }
    </style>
@else
    {{-- STANDARD AI UI --}}
    <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Cấp độ") }} máy: {{ __($levelTxt) }}">{{ __('Bạn đang') }} {{ __($action) }} {{ __('với máy') }}<span id="puzzle-title"></span></h5>
@endif
@endsection

@section('aboveContent')
@endsection

@section('rightSide')
<p class="w-100 text-center m-0">
  <span class="rounded p-0 d-block" id="game-status"></span>
</p>
<p class="w-100 text-center mx-0 mb-0 mt-2">
  <span class="rounded d-none" id="game-over"><i class="fad fa-flag-checkered"></i> {{ __("HẾT TRẬN") }}</span>
</p>
<div class="sharethis-inline-reaction-buttons"></div>
<h5 class="text-center my-1">{{ __("Cấp độ") }}: {{ __($levelTxt) }}</h5>
<div class="level dropup mx-auto text-center my-1">
  <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="Hãy chọn cấp độ phù hợp với bạn nhé" aria-expanded="false">
    <i class="fad fa-robot"></i> {{ __("Chọn cấp độ máy") }}
  </button>
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
    <a class="dropdown-item{{ request()->is($urls[0]) ? ' active disabled' : '' }}" href="{{ url('/' . $urls[0]) }}" style="cursor: pointer !important;">{{ __("Mới chơi") }}</a>
    <a class="dropdown-item{{ request()->is($urls[1]) ? ' active disabled' : '' }}" href="{{ url('/' . $urls[1]) }}" style="cursor: pointer !important;">{{ __("Dễ") }}</a>
    <a class="dropdown-item{{ request()->is($urls[2]) ? ' active disabled' : '' }}" href="{{ url('/' . $urls[2]) }}" style="cursor: pointer !important;">{{ __("Bình thường") }}</a>
    <a class="dropdown-item{{ request()->is($urls[3]) ? ' active disabled' : '' }}" href="{{ url('/' . $urls[3]) }}" style="cursor: pointer !important;">{{ __("Khó") }}</a>
    <a class="dropdown-item{{ request()->is($urls[4]) ? ' active disabled' : '' }}" href="{{ url('/' . $urls[4]) }}" style="cursor: pointer !important;">{{ __("Khó nhất") }}</a>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item text-danger font-weight-bold{{ request()->is($urls[5]) ? ' active disabled' : '' }}" href="{{ url('/' . $urls[5]) }}" style="cursor: pointer !important;"><i class="fas fa-crown"></i> {{ __("Kiện tướng") }}</a>
</div>
</div>
<div class="dropup mx-auto text-center my-1">
  <button class="btn btn-danger btn-lg dropdown-toggle" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span data-toggle="tooltip" data-placement="top" title="{{ __("Đấu với bạn bè trong phòng") }}"><i class="fad fa-gamepad-alt"></i> {{ __("Chơi online") }}</span>
  </button>
  <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> {{ __("Đổi bên") }}</a>
  @include('common.volumeBtn')
  @include('common.tourBtn')
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ url('/') }}/phong/{{ md5(time()) }}">
    @if (!auth()->check())
    <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Đăng nhập để tham gia thi đấu") }}" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="{{ localized_url('login') }}"><i class="fas fa-sign-in text-dark"></i> {{ __("Đăng nhập") }}</a>
    @else
    <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Thi đấu tính điểm và xếp hạng") }}" id="create-room" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="javascript:createRoom();"><i class="fas fa-trophy-alt text-dark"></i> {{ __("Thi đấu") }}</a>
    @endif
    <a data-toggle="tooltip" data-placement="bottom" title="Chơi cần mật khẩu" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> {{ __("Riêng tư") }}</a>
    @if ($randomRoom != null)
    <a data-toggle="tooltip" data-placement="bottom" title="Chơi trong phòng Công khai ngẫu nhiên" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ url('/') }}/phong/{{ $randomRoom['code'] }}/ngau-nhien"><i class="fas fa-random text-dark"></i> {{ __("Ngẫu nhiên") }}</a>
    @endif
    <a data-toggle="tooltip" data-placement="bottom" title="Tìm phòng trống" id="room-list" class="dropdown-item rooms-list" style="cursor: pointer !important;" href="{{ url(__('/sanh-cho')) }}"><i class="fas fa-list-alt text-dark"></i> {{ __("Sảnh chờ") }}</a>
  </div>
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="2" data-intro="Ấn vào đây nếu bạn không biết đi nước nào" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> {{ __("Bỏ cuộc") }}</a>
  <a data-step="3" data-intro="Ấn vào đây để quay lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> {{ __("Đi lại") }}</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="5" data-intro="Nơi luyện tập với chính mình nhé" class="w-25 btn btn-dark btn-lg showPromotion" href="{{ url('/' . __('choi-mot-minh')) }}"><i class="fad fa-user"></i> {{ __("Một mình") }}</a>
  <a data-step="4" data-intro="Ấn vào đây để {{ __("chơi") }} lại từ đầu" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> {{ __("Chơi lại") }}</a>
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

async function makeBestMove() {
  if (isComputerThinking || game.game_over()) return;

  isComputerThinking = true;
  $('#game-status').html('{{ __("Đang suy nghĩ") }}... <i class="fas fa-spinner fa-spin"></i>');

  try {
    const response = await fetch('/api/xiangqi/best-move', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        fen: game.fen(),
        timeout: getTimeoutByLevel({{ $level ?? 3 }})
      })
    });

    const data = await response.json();

    if (data.success && data.best_move) {
      const move = convertEngineMoveToXiangqiJS(data.best_move);

      if (move) {
        const moveResult = game.move(move);
        if (moveResult !== null) {
          if (kypho) {
            kypho.recordMove(moveResult);
          }
          board.position(game.fen());
          if (typeof nuocCo !== 'undefined') nuocCo.play();
          updateStatus();
        } else {
          makeRandomMove();
        }
      } else {
        makeRandomMove();
      }
    } else {
      makeRandomMove();
    }
  } catch (error) {
    makeRandomMove();
  } finally {
    isComputerThinking = false;
  }
}

function convertEngineMoveToXiangqiJS(engineMove) {
  if (!engineMove || engineMove.length !== 4) return null;
  return {
    from: engineMove.substring(0, 2),
    to: engineMove.substring(2, 4)
  };
}

function getTimeoutByLevel(level) {
  // Parse the level to ensure it's an integer, avoiding string-key lookup failures
  const parsedLevel = parseInt(level, 10);

  const timeouts = {
    1: 500,    // Mới chơi
    2: 1000,   // Dễ
    3: 1500,   // Bình thường
    4: 2000,   // Khó
    5: 2500,   // Khó nhất
    6: 5000,   // Kiện tướng
    8: 10000   // Đại kiện tướng (Max depth/timeout)
  };

  // Return the mapped timeout, or fallback to 1500ms if the level isn't found
  return timeouts[parsedLevel] || 1500;
}

function makeRandomMove() {
  const moves = game.moves({verbose: true});
  if (moves.length > 0) {
    const randomMove = moves[Math.floor(Math.random() * moves.length)];
    const moveResult = game.move(randomMove);
    if (moveResult !== null) {
      if (kypho) kypho.recordMove(moveResult);
      board.position(game.fen());
      if (typeof nuocCo !== 'undefined') nuocCo.play();
      updateStatus();
    }
  }
}

function onDrop (source, target) {
  if (isComputerThinking) return 'snapback';

  let move = game.move({
    from: source,
    to: target,
    promotion: 'q'
  });

  if (move === null) return 'snapback';
  if (kypho) kypho.recordMove(move);

  updateStatus();

  if (!game.game_over() && game.turn() === 'b') {
    setTimeout(makeBestMove, 500);
  }
}

function onMouseoverSquare (square, piece) {
  if (isComputerThinking) return;
  let moves = game.moves({ square: square, verbose: true });
  if (moves.length === 0) return;
  greySquare(square);
  for (let i = 0; i < moves.length; i++) {
    greySquare(moves[i].to);
  }
}

function onMouseoutSquare (square, piece) {
  removeGreySquares();
}

function onSnapEnd () {
  board.position(game.fen());
  if (typeof nuocCo !== 'undefined') nuocCo.play();
  updateStatus();
}

function updateStatus () {
  var status = '';
  var moveColor = '{{ __("Đỏ") }}';

  if (game.turn() === 'b') moveColor = '{{ __("Đen") }}';

  if (game.in_checkmate()) {
    status = moveColor + ' {{ __("bị chiếu bí") }}';
  } else if (game.in_draw()) {
    status = '{{ __("Hòa") }}';
  } else {
    status = moveColor;
    if (game.in_check()) {
      status += ', ' + moveColor + ' {{ __("đang bị chiếu") }}';
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
    if (typeof hetTran !== 'undefined') hetTran.play();
    if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
      $('#header-status').html(': '+status+' - {{ __("Hết trận") }}');
    }
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> {{ __("Hết trận") }}');
    isComputerThinking = false;
  }

  if (game.fen().includes('resign') && !resignAlertShown) {
    if (typeof $('#header-status') !== 'undefined' && $('#header-status').length) {
      $('#header-status').html(': '+status+' - {{ __("Đã bỏ cuộc") }}');
    }

    if (typeof bootbox !== 'undefined') {
      bootbox.alert({
        message: '<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}',
        locale: '{{ __("vi") }}',
        centerVertical: true,
        closeButton: false,
        size: 'small',
        buttons: { ok: { className: 'btn-danger' } }
      });
    }

    $('#game-over').html('<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}');
    $('#resign, #switch').addClass('disabled').attr('aria-disabled', true);
    config.draggable = false;
    isComputerThinking = false;
    resignAlertShown = true;
  }
  if (kypho) kypho.updateControls();
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

board = Xiangqiboard('ban-co', config);

if (typeof $(window).resize === 'function') $(window).resize(board.resize);

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
    if (typeof nuocCo !== 'undefined') nuocCo.play();
    updateStatus();
    if (kypho) kypho.setMoves(game.history());
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
  if (kypho) kypho.setMoves([]);
});

$('.level.dropup .dropdown-item').each(function(){
  const activePointer = '<i class="far fa-hand-point-right"></i>  ';
  if ($(this).hasClass('active')) $(this).prepend(activePointer);
  $(this).on('click auxclick', function(e){
    window.location.href = $(this).attr('href');
  }).on('mouseenter mouseleave', function(){
    if ($(this).has('i').length && !$(this).hasClass('text-danger')) {
      $(this).find('i').remove();
    } else if (!$(this).hasClass('text-danger')) {
      $(this).prepend(activePointer);
    }
  });
});

const style = document.createElement('style');
style.textContent = `
  .fa-spinner { margin-left: 5px; }
  .disabled { opacity: 0.5; pointer-events: none; }
  .highlight { background-color: #ffeb3b !important; opacity: 0.6; }
`;
document.head.appendChild(style);

@if(isset($computerStarts) && $computerStarts)
$(document).ready(function() {
  setTimeout(makeBestMove, 1000);
});
@endif
</script>
@if(isset($level) && $level != '8')
@include('layout.partials.players')
@include('layout.partials.userPuzzles')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
@endif
@endsection
