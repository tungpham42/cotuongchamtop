@extends('layout.gamelayout')

@section('aboveBoard')
@php
$locale = app()->getLocale() ?: 'vi';
$levelUrls = [
    'vi' => ['ban-co-moi-choi', 'ban-co-de', 'ban-co-binh-thuong', 'ban-co-kho', 'ban-co-kho-nhat'],
    'en' => ['newbie-board', 'easy-board', 'normal-board', 'hard-board', 'hardest-board'],
    'ja' => ['shoshinsha-bodo', 'kantan-bodo', 'tsujo-bodo', 'hado-bodo', 'mottomo-muzukashi-bodo'],
    'ko' => ['nyubi-bodeu', 'iji-bodeu', 'nomol-bodeu', 'hadeu-bodeu', 'gajang-dandanhan-bodeu'],
    'zh' => ['xinshouban', 'jianyiban', 'putongban', 'yingban', 'zuiyingban'],
];
$urls = $levelUrls[$locale] ?? $levelUrls['vi'];
@endphp
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Tăng kỹ năng chơi cờ") }}">{{ __("Bạn đang giải") }}<span id="puzzle-title"></span></h5>
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
<div class="dropup mx-auto text-center my-1">
  <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="Hãy chọn cấp độ phù hợp với bạn nhé" aria-expanded="false">
    <i class="fad fa-robot"></i> {{ __("Chọn cấp độ bàn cờ") }}
  </button>
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
    <a class="add-fen dropdown-item" href="{{ url('/' . $urls[0]) }}" style="cursor: pointer !important;">{{ __("Mới chơi") }}</a>
    <a class="add-fen dropdown-item" href="{{ url('/' . $urls[1]) }}" style="cursor: pointer !important;">{{ __("Dễ") }}</a>
    <a class="add-fen dropdown-item" href="{{ url('/' . $urls[2]) }}" style="cursor: pointer !important;">{{ __("Bình thường") }}</a>
    <a class="add-fen dropdown-item" href="{{ url('/' . $urls[3]) }}" style="cursor: pointer !important;">{{ __("Khó") }}</a>
    <a class="add-fen dropdown-item" href="{{ url('/' . $urls[4]) }}" style="cursor: pointer !important;">{{ __("Khó nhất") }}</a>
  </div>
</div>
<div class="dropup mx-auto text-center my-1">
  <button class="btn btn-danger btn-lg dropdown-toggle" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span data-toggle="tooltip" data-placement="top" title="{{ __("Đấu với bạn bè trong phòng") }}"><i class="fad fa-gamepad-alt"></i> {{ __("Chơi online") }}</span>
  </button>
  @if ( isset($name) && $name != '' )
  <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> {{ __("Đổi bên") }}</a>
  @endif
  @include('common.volumeBtn')
  @include('common.tourBtn')
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ localized_url('room.host', ['code' => md5(time())]) }}">
    @if (!auth()->check())
    <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Đăng nhập để tham gia thi đấu") }}" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="{{ localized_url('login') }}"><i class="fas fa-sign-in text-dark"></i> {{ __("Đăng nhập") }}</a>
    @else
    <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Thi đấu tính điểm và xếp hạng") }}" id="create-room" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="javascript:createRoom();"><i class="fas fa-trophy-alt text-dark"></i> {{ __("Thi đấu") }}</a>
    @endif
    {{-- <a data-toggle="tooltip" data-placement="bottom" title="Chơi không cần mật khẩu" id="tao-phong-public" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-globe text-dark"></i> Công khai</a> --}}
    <a data-toggle="tooltip" data-placement="bottom" title="Chơi cần mật khẩu" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> {{ __("Riêng tư") }}</a>
    @if ($randomRoom != null)
    <a data-toggle="tooltip" data-placement="bottom" title="Chơi trong phòng Công khai ngẫu nhiên" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ localized_url('room.random', ['code' => $randomRoom['code'] ]) }}"><i class="fas fa-random text-dark"></i> {{ __("Ngẫu nhiên") }}</a>
    @endif
    <a data-toggle="tooltip" data-placement="bottom" title="Tìm phòng trống" id="room-list" class="dropdown-item rooms-list" style="cursor: pointer !important;" href="{{ localized_url('room.list') }}"><i class="fas fa-list-alt text-dark"></i> {{ __("Sảnh chờ") }}</a>
  </div>
</div>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> {{ __("Mời bạn bè chơi bằng cách gửi liên kết bên dưới") }}.
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto" data-toggle="tooltip" data-placement="bottom" data-original-title="Ấn để sao chép">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="2" data-intro="{{ __("Ấn vào đây để mời bạn bè cùng chơi") }}" type="text" class="form-control" id="url" value="{{ url()->current() }}">
</div>
<script>
$('#copy-url').on('click', function() {
  copyToClipboard('#url');
  selectText('#url');
  $(this).tooltip('update');
});
</script>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="3" data-intro="Ấn vào đây để đổi sang màu khác" id="switch" class="w-25 btn btn-dark btn-lg"><i class="fad fa-sync"></i> {{ __("Đổi bên") }}</a>
  <a data-step="4" data-intro="Ấn vào đây để quay lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo"></i> {{ __("Đi lại") }}</a>
</p>
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
  nuocCo.play();
  updateStatus();
}

function onMoveEnd () {
  $board.find('.square-' + squareToHighlight).addClass('highlight-' + colorToHighlight);
}

function updateStatus () {
  var status = ''

  var moveColor = '{{ __("Đỏ") }}'
  if (game.turn() === 'b') {
    moveColor = '{{ __("Đen") }}'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' {{ __("bị chiếu bí") }}'
  }

  // draw?
  else if (game.in_draw()) {
    status = '{{ __("Hòa") }}'
  }

  // game still on
  else {
    status = moveColor

    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + ' {{ __("đang bị chiếu") }}'

      if ((board.orientation() == 'red' && game.turn() === 'r') || (board.orientation() == 'black' && game.turn() === 'b')) {
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
  $('#header-status').html(': '+status);
  if (game.game_over()) {
    hetTran.play();
    $('#header-status').html(': '+status+' - {{ __("Hết trận") }}');
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
  showNotation: true
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

    // PREVENT DEFAULT NAVIGATION SO JS CAN APPEND THE FEN
    e.preventDefault();

    window.location.href = $(this).attr('href') + '/' + game.fen();

  }).on('mouseenter mouseleave', function(){
    if ($(this).has('i').length) {
      $(this).find('i').remove();
    } else {
      $(this).prepend(activePointer);
    }
  });
});
</script>
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
@include('layout.partials.players')
@include('layout.partials.userPuzzles')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
{{-- @include('layout.partials.puzzles') --}}
{{-- @include('layout.partials.books') --}}
@endsection
