@extends('layout.gamelayout')

@php
    $reactionData = $reactions ?? ['likes' => 0, 'hard' => 0, 'unsolved' => 0, 'rating' => 0];
    $totalReactions = $reactionData['likes'] + $reactionData['hard'] + $reactionData['unsolved'];
    $puzzleDescription = $description ?? ($puzzle->description ?? '');
    $isPrivate = isset($isPublic) ? !$isPublic : (!$puzzle->is_public ?? false);
@endphp

@if($name && !containsCJK($name))
    @section('og_image', 'https://placehold.co/1080x1080/DA251D/FFFF00/jpeg?font=roboto&text=' . urlencode(__("Thế cờ") . "\n" . $name))
    @section('og_image_alt', $name)
    @section('og_image_width', '1080')
    @section('og_image_height', '1080')
    @section('og_image_type', 'image/jpeg')
@else
    @section('og_image', url('/') . '/img/co-the.jpg')
    @section('og_image_alt', 'Cờ thế')
    @section('og_image_width', '1080')
    @section('og_image_height', '1080')
    @section('og_image_type', 'image/jpeg')
@endif

@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title='{{ __("Bạn đang thi đấu") }} {{ __("thế cờ") }} "{{ $name }}'>{{ __("Bạn đang thi đấu") }} {{ __("thế cờ") }} "{{ $name }}"</h5>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="1" data-intro='Ấn vào đây để tải {{ __("thế cờ") }} "{{ $name }}" về máy' id="capture" class="btn btn-danger btn-lg text-light" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="{{ __("Lưu thành ảnh nào") }}"><i class="fad fa-download"></i> {{ __("Tải bàn cờ thế") }}</a>
  @include('common.volumeBtn')
  @include('common.tourBtn')
</p>
@endsection
@section('rightSide')
<style>
  .puzzle-side-panel {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    scrollbar-width: thin;
    scrollbar-color: var(--royal-red) var(--royal-bg);
  }
  @media (min-width: 992px) {
    .puzzle-side-panel {
      position: sticky;
      overflow-y: auto;
      padding-right: 0.5rem;
    }
  }

  /* ROYAL SCROLLBAR OVERRIDE FOR SIDE PANEL */
  .puzzle-side-panel::-webkit-scrollbar {
    width: 8px;
  }
  .puzzle-side-panel::-webkit-scrollbar-track {
    background: var(--royal-bg);
    border-left: 1px solid rgba(212, 175, 55, 0.2);
    box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.9);
  }
  .puzzle-side-panel::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, var(--royal-red), #5c0a0a);
    border: 1px solid var(--royal-gold);
    border-radius: 6px;
    box-shadow: inset 0 0 5px rgba(255, 215, 0, 0.3);
  }
  .puzzle-side-panel::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #d4af37, #b89020);
    border-color: #fff;
    box-shadow: 0 0 10px rgba(212, 175, 55, 0.8);
  }

  #puzzle-note-block {
    width: 100%;
    margin: 1rem 0 0 auto;
  }
  @media (max-width: 768px) {
    #puzzle-note-block {
      max-width: 100% !important;
    }
  }
  @media (min-width: 992px) {
    .puzzle-layout-board {
      max-width: 380px !important;
    }
  }

  /* LIQUID GLASS SIDE CARDS */
  .puzzle-side-card, .puzzle-comment-feed {
    background: var(--glass-bg-dark) !important;
    backdrop-filter: var(--glass-blur);
    -webkit-backdrop-filter: var(--glass-blur);
    border: 1px solid var(--glass-border) !important;
    border-top: 1px solid rgba(255, 215, 0, 0.5) !important;
    border-radius: 12px;
    box-shadow: var(--liquid-shadow), inset 0 2px 15px var(--liquid-highlight) !important;
    color: var(--royal-gold-light);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }
  .puzzle-side-card:hover, .puzzle-comment-feed:hover {
    transform: translateY(-2px);
    border-color: rgba(212, 175, 55, 0.6) !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.9), 0 0 25px rgba(212, 175, 55, 0.3), inset 0 4px 20px var(--liquid-highlight) !important;
  }

  .puzzle-side-card .card-body {
    padding: 1rem 1.2rem;
  }
  .puzzle-side-card h5, .puzzle-comment-feed h6 {
    font-family: "Texturina", "Noto Sans JP", serif !important;
    color: var(--royal-gold) !important;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
    font-weight: 600;
  }
  .puzzle-side-card .text-muted {
    color: #aa8c4a !important;
  }

  /* GLOSSY REACTION BUTTONS */
  .puzzle-reaction-mini {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
  }
  .puzzle-reaction-mini .reaction-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    padding: 0.3rem 0.6rem;
    border-radius: 8px;
    border: 1px solid var(--glass-border);
    background: var(--glass-bg-dark);
    color: var(--royal-gold-light);
    box-shadow: inset 0 1px 5px var(--liquid-highlight);
    transition: all 0.3s ease;
  }
  .puzzle-reaction-mini .reaction-btn.disabled,
  .puzzle-reaction-mini .reaction-btn:disabled {
    opacity: 0.5;
    pointer-events: none;
  }
  .puzzle-reaction-mini .reaction-btn:hover {
    background: var(--glass-bg-red);
    border-color: rgba(255, 215, 0, 0.6);
    color: var(--royal-gold);
    box-shadow: 0 0 10px rgba(212, 175, 55, 0.5), inset 0 2px 5px rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
  }
  .puzzle-reaction-mini .reaction-btn .reaction-count {
    padding: 0.1rem 0.45rem;
    border-radius: 4px;
    background: rgba(11, 12, 16, 0.8);
    color: var(--royal-gold);
    border: 1px solid rgba(212, 175, 55, 0.3);
    font-size: 0.68rem;
    font-weight: 600;
  }

  /* COMMENT FEED */
  .puzzle-comment-feed {
    padding: 1.2rem;
  }
  .puzzle-comments {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .puzzle-comment-card {
    background: rgba(11, 12, 16, 0.5);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 8px;
    padding: 0.85rem 1rem;
    box-shadow: inset 0 1px 5px var(--liquid-highlight);
    color: var(--royal-gold-light);
    transition: all 0.3s ease;
  }
  .puzzle-comment-card:hover {
    background: rgba(11, 12, 16, 0.7);
    border-color: rgba(212, 175, 55, 0.4);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5), inset 0 2px 8px var(--liquid-highlight);
  }
  .puzzle-comment-card .comment-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.6rem;
  }
  .puzzle-comment-card .comment-avatar {
    width: 38px;
    height: 38px;
    border-radius: 4px; /* Squared off royal look */
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--royal-bg);
    background: linear-gradient(135deg, var(--royal-gold), #b89020);
    border: 1px solid #fff;
    box-shadow: 0 0 10px rgba(212, 175, 55, 0.5);
  }
  .puzzle-comment-card .comment-body {
    color: var(--royal-gold-light);
    line-height: 1.45;
  }
  .puzzle-comment-card .comment-meta {
    font-size: 0.8rem;
    color: #aa8c4a;
  }

  /* ACTION LINKS */
  .puzzle-comment-card .comment-actions {
    display: flex;
    gap: 1.25rem;
    margin-top: 0.5rem;
    font-size: 0.82rem;
    color: #aa8c4a;
  }
  .puzzle-comment-card .comment-actions .comment-action {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    cursor: pointer;
    transition: color 0.15s ease, text-shadow 0.15s ease;
    font-weight: 600;
  }
  .puzzle-comment-card .comment-actions .comment-action:hover {
    color: var(--royal-gold);
    text-shadow: 0 0 8px rgba(212, 175, 55, 0.8);
  }
  .puzzle-comment-card .comment-actions .comment-action .comment-like-count {
    padding: 0.05rem 0.45rem;
    border-radius: 4px;
    background: rgba(11, 12, 16, 0.6);
    border: 1px solid rgba(212, 175, 55, 0.2);
    font-size: 0.72rem;
    line-height: 1;
    color: var(--royal-gold-light);
  }
  .puzzle-comment-card .comment-actions .comment-action.liked {
    color: var(--royal-red-light);
    text-shadow: 0 0 8px rgba(230, 57, 70, 0.5);
    cursor: default;
  }
  .puzzle-comment-card .comment-actions .comment-action.liked .comment-like-count {
    color: var(--royal-red-light);
    border-color: rgba(230, 57, 70, 0.4);
  }
  .puzzle-comment-card .comment-actions .comment-action.disabled,
  .puzzle-comment-card .comment-actions .comment-action.loading {
    pointer-events: none;
    opacity: 0.6;
  }

  /* EMPTY COMMENT STATE */
  .puzzle-empty-comment {
    background: rgba(11, 12, 16, 0.6);
    border: 1px dashed var(--royal-gold);
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    color: var(--royal-gold-light);
    font-style: italic;
    box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
  }
  .puzzle-empty-comment.error {
    color: var(--royal-red-light);
    border-color: var(--royal-red);
  }

  /* FORM CONTROLS (Using Royal Form Design) */
  .puzzle-side-panel label {
    font-weight: 600;
    color: var(--royal-gold);
    letter-spacing: 0.5px;
  }
  .puzzle-side-panel .form-control {
    background-color: var(--royal-gold-light) !important;
    border: 1px solid var(--royal-wood) !important;
    color: var(--royal-bg) !important;
    border-radius: 4px;
    font-weight: 600;
  }
  .puzzle-side-panel .form-control::placeholder {
    color: rgba(74, 37, 17, 0.6) !important;
    font-weight: normal;
  }
  .puzzle-side-panel .form-control:focus {
    background-color: #fff !important;
    border-color: var(--royal-gold) !important;
    box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25) !important;
  }

  /* REPLIES */
  .puzzle-comment-card.reply {
    margin-left: 1.5rem;
    background: rgba(138, 21, 21, 0.15); /* Tint of red for replies */
    border-left: 2px solid var(--royal-gold);
  }
  .puzzle-comment-children {
    margin-top: 0.75rem;
    border-left: 1px dashed rgba(212, 175, 55, 0.3);
    padding-left: 1rem;
  }

  /* REPLY FORM */
  .comment-reply-form {
    background: rgba(11, 12, 16, 0.7);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 0.75rem;
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.8);
  }
</style>

<div id="puzzle-note-block" class="puzzle-side-card mr-lg-0 mx-md-auto mw-md-100">
  <div class="card-body">
    <h5 class="mb-2 text-left"><i class="fas fa-info-circle text-danger"></i> {{ __("Ghi chú thế cờ") }}</h5>
    @if (!empty($puzzleDescription))
      <div style="color: var(--royal-gold-light);" class="mb-3">{!! nl2br(e($puzzleDescription)) !!}</div>
    @else
      <div class="puzzle-empty-comment mb-3">{{ __("Chưa có ghi chú cho thế cờ này.") }}</div>
    @endif
    <p class="text-muted small mb-2 text-left" id="reaction-summary">
      {{ __("Tổng phản hồi:") }} <span class="badge badge-status" style="background: rgba(212, 175, 55, 0.2); color: var(--royal-gold); border: 1px solid var(--royal-gold);" id="reaction-total">{{ $totalReactions }}</span>
    </p>
    <div class="puzzle-reaction-mini justify-content-end">
      <button type="button" class="reaction-btn reaction-btn-like pulse-light" data-reaction="like">
        <i class="fas fa-thumbs-up"></i>
        <span class="reaction-count" id="reaction-like-count">{{ $reactionData['likes'] }}</span>
      </button>
      <button type="button" class="reaction-btn reaction-btn-hard pulse-light" data-reaction="hard">
        <i class="fas fa-heart"></i>
        <span class="reaction-count" id="reaction-hard-count">{{ $reactionData['hard'] }}</span>
      </button>
      <button type="button" class="reaction-btn reaction-btn-unsolved pulse-light" data-reaction="unsolved">
        <i class="fas fa-question-circle"></i>
        <span class="reaction-count" id="reaction-unsolved-count">{{ $reactionData['unsolved'] }}</span>
      </button>
    </div>
  </div>
</div>

<div class="puzzle-side-panel">
  <div class="puzzle-side-card">
    <div class="card-body text-center">
      <div class="puzzle-note-status mb-3">
        <span class="rounded d-block" id="game-status"></span>
        <span class="rounded d-none mt-2" id="game-over"><i class="fad fa-flag-checkered"></i> {{ __("HẾT TRẬN") }}</span>
      </div>
      @if ($isPrivate)
        <div class="alert shadow-sm mb-3 text-left" style="background: rgba(212, 175, 55, 0.2); border: 1px solid var(--royal-gold); color: var(--royal-gold-light);">
          <i class="fas fa-lock text-warning"></i> {{ __("Thế cờ này đang ở chế độ") }} <strong>{{ __("riêng tư") }}</strong>{{ __(". Chỉ những ai có liên kết mới có thể xem.") }}
        </div>
      @endif
      <div class="sharethis-inline-reaction-buttons mb-3"></div>
      <div class="dropup">
        <button class="btn btn-danger btn-lg dropdown-toggle w-100" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <span data-toggle="tooltip" data-placement="top" title="{{ __("Đấu với bạn bè trong phòng") }}"><i class="fad fa-gamepad-alt"></i> {{ __("Chơi online") }}</span>
        </button>
        @if ( isset($name) && $name != '' )
        <a id="switch" class="btn btn-dark btn-lg w-100 mt-2"><i class="fad fa-sync"></i> {{ __("Đổi bên") }}</a>
        <a id="solve-puzzle" class="btn btn-dark btn-lg w-100 mt-2" href="javascript:solvePuzzle('{{ $fen }}')"><i class="fad fa-mouse"></i> {{ __("Giải") }} {{ __("thế cờ") }} "{{ $name }}"</a>
        @endif
        <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ localized_url('room.host', ['code' => md5(time())]) }}">
          @if (!auth()->check())
          <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Đăng nhập để tham gia thi đấu") }}" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="{{ localized_url('login') }}"><i class="fas fa-sign-in text-dark"></i> {{ __("Đăng nhập") }}</a>
          @else
          <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Thi đấu tính điểm và xếp hạng") }}" id="create-room" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="javascript:createRoom();"><i class="fas fa-trophy-alt text-dark"></i> {{ __("Thi đấu") }}</a>
          @endif
          <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Chơi cần mật khẩu") }}" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> {{ __("Riêng tư") }}</a>
          @if ($randomRoom != null)
          <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Chơi trong phòng Công khai ngẫu nhiên") }}" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ localized_url('room.random', ['code' => $randomRoom['code'] ]) }}"><i class="fas fa-random text-dark"></i> {{ __("Ngẫu nhiên") }}</a>
          @endif
          <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Tìm phòng trống") }}" id="room-list" class="dropdown-item rooms-list" style="cursor: pointer !important;" href="{{ localized_url('room.list') }}"><i class="fas fa-list-alt text-dark"></i> {{ __("Sảnh chờ") }}</a>
        </div>
      </div>
    </div>
  </div>

  <div class="puzzle-side-card">
    <div class="card-body">
      <h5 class="mb-3"><i class="fas fa-comments text-danger"></i> {{ __("Bình luận & góp ý") }}</h5>
      <form id="puzzle-comment-form">
        <div class="form-group">
          <label for="comment_author">{{ __("Tên bạn (không bắt buộc)") }}</label>
          <input type="text" class="form-control" id="comment_author" maxlength="120" placeholder="{{ __("Ví dụ: Kỳ thủ A") }}">
        </div>
        <div class="form-group">
          <label for="comment_content">{{ __("Nội dung") }}</label>
          <textarea class="form-control" id="comment_content" rows="3" maxlength="1000" placeholder="{{ __("Chia sẻ hướng giải hoặc góp ý cho thế cờ này...") }}" required></textarea>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <small class="d-none text-danger" id="comment-feedback"></small>
          <button type="submit" class="btn btn-danger pulse-red" id="comment-submit"><i class="fas fa-paper-plane"></i> {{ __("Gửi") }} {{ __("bình luận") }}</button>
        </div>
      </form>
    </div>
  </div>

  <div class="puzzle-comment-feed">
    <h6 class="mb-3"><i class="fas fa-comment-dots text-danger"></i> {{ __("Dòng thảo luận") }}</h6>
    <div id="puzzle-comment-list" class="puzzle-comments"></div>
  </div>
</div>
@endsection
@section('belowContent')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const noteBlock = document.getElementById('puzzle-note-block');
  const board = document.getElementById('ban-co');
  function syncNoteBlockWidth() {
    if (!noteBlock) return;
    const boardSurface = document.querySelector('#ban-co .xiangqiboard-8ddcb');
    if (boardSurface) {
      noteBlock.style.maxWidth = boardSurface.getBoundingClientRect().width + 'px';
    }
  }

  if (noteBlock && board && board.parentElement && !noteBlock.dataset.moved) {
    board.parentElement.appendChild(noteBlock);
    noteBlock.dataset.moved = 'true';
    syncNoteBlockWidth();
    setTimeout(syncNoteBlockWidth, 300);
    window.addEventListener('resize', syncNoteBlockWidth);
  }
});
</script>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="2" data-intro="Ấn vào đây để xếp {{ __("thế cờ") }} mới" id="setup" class="w-25 btn btn-dark btn-lg" href="{{ url('/') }}{{ __("/co-the") }}"><i class="fad fa-plus-hexagon"></i> {{ __("Xếp ván mới") }}</a>
  <a data-step="3" data-intro="Ấn vào đây để quay lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> {{ __("Đi lại") }}</a>
</p>

<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> {{ __("Mời bạn bè chơi bằng cách gửi liên kết bên dưới") }}.
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto" data-toggle="tooltip" data-placement="bottom" data-original-title="{{ __("Ấn để sao chép") }}">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="6" data-intro="Ấn vào đây để mời bạn bè cùng chơi" type="text" class="form-control" id="url" value="{{ url('/') }}{{ __("/the-co/") }}{{ $slug }}">
</div>
<script>
$('#copy-url').on('click', function() {
  copyToClipboard('#url');
  selectText('#url');
  $(this).tooltip('update');
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js" integrity="sha512-OqcrADJLG261FZjar4Z6c4CfLqd861A3yPNMb+vRQ2JwzFT49WT4lozrh3bcKxHxtDTgNiqgYbEUStzvZQRfgQ==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.svg.min.js" integrity="sha512-cX+p7MRIKvgo59Ap3QDj2ymdc7XFFCEJ71X+nWPT+3UxNylm/ztqgDJTbko2atIo4jiozj0dUpYb+xfv1bCl8g==" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.2/dist/FileSaver.min.js" integrity="sha256-u/J1Urdrk3nCYFefpoeTMgI5viU1ujCDu2fXXoSJjhg=" crossorigin="anonymous"></script>
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
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> {{ __("Hết trận") }}');
    $('#header-status').html(': '+status+' - {{ __("Hết trận") }}');
  }
  if (game.fen().includes('resign') && !resignAlertShown) {
    $('#header-status').html(': '+status+' - {{ __("Đã bỏ cuộc") }}');
    bootbox.alert({
      message: '<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}',
      locale: '{{ __("vi") }}',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger'
        }
      }
    });
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> {{ __("Đã bỏ cuộc") }}');
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
  onMoveEnd: onMoveEnd,
  showNotation: true
  //pieceTheme: '/static/img/xiangqipieces/traditional/{piece}.svg'
};
board = Xiangqiboard('ban-co', config);
$(window).resize(board.resize);
game.load('{{ $fen }}' + ' r - - 0 1');
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
$("#capture").on('click', function() {
  if (!game.validate_fen(board.fen() + ' r - - 0 1').valid) {
    bootbox.alert({
      message: '{{ __("Bàn cờ thế") }} {{ __("không hợp lệ") }}',
      locale: '{{ __("vi") }}',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger',
          label: '{{ __("Xếp lại") }}'
        }
      }
    });
  } else {
    html2canvas(document.getElementById("ban-co"), {
      windowWidth: document.getElementById("ban-co").scrollWidth,
      windowHeight: document.getElementById("ban-co").scrollHeight,
      allowTaint: true,
      useCORS: true,
      onrendered: function(originalCanvas) {
        // Create a new canvas with added top and bottom padding
        var paddingTop = 40;
        var paddingBottom = 40;
        var finalCanvas = document.createElement('canvas');
        finalCanvas.width = originalCanvas.width;
        finalCanvas.height = originalCanvas.height + paddingTop + paddingBottom;
        var context = finalCanvas.getContext('2d');

        // Sample a pixel near the bottom edge of the original canvas to dynamically match the board's background color
        var origCtx = originalCanvas.getContext('2d');
        var pixel = origCtx.getImageData(5, originalCanvas.height - 5, 1, 1).data;
        var bgColor = 'rgba(' + pixel[0] + ', ' + pixel[1] + ', ' + pixel[2] + ', ' + (pixel[3] / 255) + ')';

        // Fill the new canvas background so the padding visually matches the board
        context.fillStyle = bgColor;
        context.fillRect(0, 0, finalCanvas.width, finalCanvas.height);

        // Draw the original board capture onto the new canvas, offsetting it to make room for the top padding
        context.drawImage(originalCanvas, 0, paddingTop);

        // Context general settings
        context.textAlign = 'center';
        context.textBaseline = 'middle';

        // 1. Puzzle Name right in the middle of the TOP padded area
        context.globalCompositeOperation = 'source-over';
        context.font = 'bold 18px sans-serif';
        context.fillStyle = '#444422';
        context.fillText('{{ $name }}', finalCanvas.width / 2, paddingTop / 2);

        // 2. COTUONG.TOP drawn in the newly padded bottom space - MOVED TO BOTTOM RIGHT
        context.font = 'bold 16px serif';
        context.textAlign = 'right';

        // Shift X coordinate to the right edge with a 10px margin, adjusting the Y position to account for the new height offset
        context.fillText('COTUONG.TOP', finalCanvas.width - 10, originalCanvas.height + paddingTop + (paddingBottom / 2));

        finalCanvas.toBlob(function(blob) {
          saveAs(blob, "ban-co-{{ date('Y-m-d h:i:s A') }}.png");
        });
      }
    });
  }
});
$('#switch').on('click', board.flip);

const reactionEndpoints = {
  list: '{{ url('/api/puzzles/'.$slug.'/reactions') }}',
  react: '{{ url('/api/puzzles/'.$slug.'/reactions') }}'
};
const commentsEndpoint = '{{ url('/api/puzzles/'.$slug.'/comments') }}';
const commentLikeBaseEndpoint = commentsEndpoint;
const commentLikeStorageKey = 'puzzle_comment_likes_{{ $slug }}';

function loadStoredLikedComments() {
  if (typeof localStorage === 'undefined') {
    return [];
  }
  try {
    const raw = localStorage.getItem(commentLikeStorageKey);
    if (!raw) {
      return [];
    }
    const parsed = JSON.parse(raw);
    if (!Array.isArray(parsed)) {
      return [];
    }
    const numericIds = parsed
      .map(function(id) { return parseInt(id, 10); })
      .filter(function(id) { return !Number.isNaN(id); });
    return Array.from(new Set(numericIds));
  } catch (error) {
    return [];
  }
}

let likedCommentIds = loadStoredLikedComments();

function hasLikedComment(id) {
  const numericId = Number(id);
  if (Number.isNaN(numericId)) {
    return false;
  }
  return likedCommentIds.includes(numericId);
}

function persistLikedComments() {
  if (typeof localStorage === 'undefined') {
    return;
  }
  try {
    localStorage.setItem(commentLikeStorageKey, JSON.stringify(likedCommentIds));
  } catch (error) {
    // localStorage might be unavailable (privacy modes, etc.)
  }
}

function rememberLikedComment(id) {
  const numericId = Number(id);
  if (Number.isNaN(numericId) || hasLikedComment(numericId)) {
    return;
  }
  likedCommentIds.push(numericId);
  likedCommentIds = Array.from(new Set(likedCommentIds));
  persistLikedComments();
}

function renderReactions(data) {
  const like = parseInt(data.likes ?? 0, 10);
  const hard = parseInt(data.hard ?? 0, 10);
  const unsolved = parseInt(data.unsolved ?? 0, 10);
  $('#reaction-like-count').text(like);
  $('#reaction-hard-count').text(hard);
  $('#reaction-unsolved-count').text(unsolved);
  $('#reaction-total').text(like + hard + unsolved);
}

function loadReactions() {
  $.get(reactionEndpoints.list).done(function(response) {
    renderReactions(response);
  });
}

$('.reaction-btn').on('click', function() {
  const $btn = $(this);
  if ($btn.data('loading')) {
    return;
  }
  const type = $btn.data('reaction');
  $btn.data('loading', true).prop('disabled', true).addClass('disabled');

  $.ajax({
    url: reactionEndpoints.react,
    method: 'POST',
    data: {
      type: type
    },
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  }).done(function(response) {
    renderReactions(response);
  }).fail(function(xhr) {
    let message = '{{ __("Không thể ghi nhận phản hồi, vui lòng thử lại.") }}';
    if (xhr.responseJSON && xhr.responseJSON.message) {
      message = xhr.responseJSON.message;
    }
    bootbox.alert({
      message: message,
      locale: '{{ __("vi") }}',
      centerVertical: true,
      closeButton: false,
      size: 'small'
    });
  }).always(function() {
    setTimeout(function() {
      $btn.data('loading', false).prop('disabled', false).removeClass('disabled');
    }, 600);
  });
});

function formatCommentDate(dateString) {
  const date = new Date(dateString);
  if (!isNaN(date)) {
    return date.toLocaleString('vi-VN', { hour12: false });
  }
  return dateString || '';
}

function buildCommentCard(comment, level = 0) {
  const author = comment.author_name ? comment.author_name : '{{ __("Ẩn danh") }}';
  const createdAt = comment.created_at ? formatCommentDate(comment.created_at) : '';
  const initials = author.trim().length ? author.trim().charAt(0).toUpperCase() : 'Ẩ';
  const contentHtml = $('<div>').text(comment.content || '').html().replace(/\n/g, '<br>');
  const commentId = Number(comment.id);
  const likeCount = Math.max(parseInt(comment.likes_count ?? 0, 10) || 0, 0);

  const card = $('<div class="puzzle-comment-card"></div>');
  if (level > 0) {
    card.addClass('reply');
  }
  if (!Number.isNaN(commentId)) {
    card.attr('data-comment-id', commentId);
  }

  const header = $('<div class="comment-header"></div>');
  header.append('<div class="comment-avatar">' + initials + '</div>');
  const userInfo = $('<div></div>');
  userInfo.append('<div class="font-weight-bold">' + $('<div>').text(author).html() + '</div>');
  if (createdAt) {
    userInfo.append('<div class="small text-muted"><i class="far fa-clock"></i> ' + $('<div>').text(createdAt).html() + '</div>');
  }
  header.append(userInfo);
  card.append(header);

  card.append('<div class="comment-body">' + contentHtml + '</div>');

  const actions = $('<div class="comment-actions"></div>');
  const likeAction = $('<span class="comment-action comment-like"></span>');
  if (!Number.isNaN(commentId)) {
    likeAction.attr('data-comment-id', commentId);
  }
  likeAction.append('<i class="far fa-thumbs-up"></i>');
  likeAction.append(' <span class="like-label">{{ __("Thích") }}</span>');
  likeAction.append(' <span class="comment-like-count">' + likeCount + '</span>');
  if (hasLikedComment(commentId)) {
    likeAction.addClass('liked');
  }
  actions.append(likeAction);

  const replyAction = $('<span class="comment-action comment-reply-toggle"></span>');
  if (!Number.isNaN(commentId)) {
    replyAction.attr('data-comment-id', commentId);
  }
  replyAction.append('<i class="far fa-comment"></i>');
  replyAction.append(' {{ __("Trả lời") }}');
  actions.append(replyAction);

  card.append(actions);

  const replyForm = $('<form class="comment-reply-form d-none"></form>');
  if (!Number.isNaN(commentId)) {
    replyForm.attr('data-comment-id', commentId);
  }
  replyForm.append('<div class="form-group mb-2"><input type="text" class="form-control form-control-sm reply-author" maxlength="120" placeholder="{{ __("Tên bạn (không bắt buộc)") }}"></div>');
  replyForm.append('<div class="form-group mb-2"><textarea class="form-control form-control-sm reply-content" rows="2" maxlength="1000" placeholder="{{ __("Phản hồi của bạn...") }}" required></textarea></div>');
  replyForm.append('<small class="reply-feedback d-none"></small>');
  const replyActions = $('<div class="d-flex justify-content-end gap-2"></div>');
  replyActions.append('<button type="button" class="btn btn-outline-light btn-sm comment-reply-cancel">{{ __("Hủy") }}</button>');
  replyActions.append('<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-paper-plane"></i> {{ __("Gửi") }}</button>');
  replyForm.append(replyActions);
  card.append(replyForm);

  if (Array.isArray(comment.replies) && comment.replies.length) {
    const childrenWrapper = $('<div class="puzzle-comment-children"></div>');
    comment.replies.forEach(function(reply) {
      childrenWrapper.append(buildCommentCard(reply, level + 1));
    });
    card.append(childrenWrapper);
  }

  return card;
}

function renderComments(comments) {
  const container = $('#puzzle-comment-list');
  container.empty();

  if (!comments || !comments.length) {
    container.append('<div class="puzzle-empty-comment">{{ __("Chưa có bình luận nào. Hãy là người đầu tiên chia sẻ cảm nhận!") }}</div>');
    return;
  }

  comments.forEach(function(comment) {
    container.append(buildCommentCard(comment));
  });
}

function loadComments() {
  $.get(commentsEndpoint)
    .done(function(response) {
      renderComments(response.comments || []);
    })
    .fail(function() {
      const container = $('#puzzle-comment-list');
      container.empty().append('<div class="puzzle-empty-comment error">{{ __("Không thể tải bình luận. Vui lòng thử lại sau.") }}</div>');
    });
}

$('#puzzle-comment-form').on('submit', function(e) {
  e.preventDefault();
  const author = $('#comment_author').val().trim();
  const content = $('#comment_content').val().trim();
  const feedback = $('#comment-feedback');
  const submitBtn = $('#comment-submit');

  if (!content.length) {
    feedback.removeClass('d-none text-success').addClass('text-danger').text('{{ __("Vui lòng nhập nội dung bình luận.") }}');
    return;
  }

  submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("Đang gửi...") }}');
  feedback.addClass('d-none').text('').removeClass('text-danger text-success');

  $.ajax({
    url: commentsEndpoint,
    method: 'POST',
    data: {
      author_name: author,
      content: content
    },
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  }).done(function() {
    $('#comment_author').val('');
    $('#comment_content').val('');
    feedback.removeClass('d-none text-danger').addClass('text-success').text('{{ __("Cảm ơn bạn! Bình luận đã được gửi.") }}');
    loadComments();
    setTimeout(function() {
      feedback.addClass('d-none').text('').removeClass('text-success');
    }, 4000);
  }).fail(function(xhr) {
    let message = '{{ __("Không thể gửi bình luận, vui lòng thử lại.") }}';
    if (xhr.responseJSON) {
      if (xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
      } else if (xhr.responseJSON.errors) {
        message = Object.values(xhr.responseJSON.errors).flat().join(' ');
      }
    }
    feedback.removeClass('d-none text-success').addClass('text-danger').text(message);
  }).always(function() {
    submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> {{ __("Gửi") }} {{ __("bình luận") }}');
  });
});

$('#puzzle-comment-list').on('click', '.comment-like', function() {
  const $btn = $(this);
  if ($btn.hasClass('loading') || $btn.hasClass('liked')) {
    return;
  }
  const commentId = Number($btn.data('comment-id'));
  if (Number.isNaN(commentId) || commentId <= 0) {
    return;
  }

  $btn.addClass('loading');

  $.ajax({
    url: commentLikeBaseEndpoint + '/' + commentId + '/like',
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  }).done(function(response) {
    const likesCount = Math.max(parseInt(response.likes_count ?? 0, 10) || 0, 0);
    $btn.find('.comment-like-count').text(likesCount);
    $btn.addClass('liked');
    rememberLikedComment(commentId);
  }).fail(function(xhr) {
    let message = '{{ __("Không thể thích bình luận, vui lòng thử lại.") }}';
    if (xhr.responseJSON && xhr.responseJSON.message) {
      message = xhr.responseJSON.message;
    }
    bootbox.alert({
      message: message,
      locale: '{{ __("vi") }}',
      centerVertical: true,
      closeButton: false,
      size: 'small'
    });
  }).always(function() {
    $btn.removeClass('loading');
  });
});

$('#puzzle-comment-list').on('click', '.comment-reply-toggle', function() {
  const form = $(this).closest('.puzzle-comment-card').find('.comment-reply-form').first();
  form.toggleClass('d-none');
  if (!form.hasClass('d-none')) {
    form.find('.reply-content').trigger('focus');
  }
});

$('#puzzle-comment-list').on('click', '.comment-reply-cancel', function() {
  const form = $(this).closest('.comment-reply-form');
  form.addClass('d-none');
  form[0].reset();
  form.find('.reply-feedback').addClass('d-none').text('').removeClass('text-danger text-success');
});

$('#puzzle-comment-list').on('submit', '.comment-reply-form', function(e) {
  e.preventDefault();
  const form = $(this);
  const submitBtn = form.find('button[type="submit"]');
  const feedback = form.find('.reply-feedback');
  const author = form.find('.reply-author').val().trim();
  const content = form.find('.reply-content').val().trim();
  const parentId = form.data('comment-id');

  if (!content.length) {
    feedback.removeClass('d-none text-success').addClass('text-danger').text('{{ __("Vui lòng nhập nội dung phản hồi.") }}');
    return;
  }

  submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("Đang gửi...") }}');
  feedback.addClass('d-none').text('').removeClass('text-danger text-success');

  $.ajax({
    url: commentsEndpoint,
    method: 'POST',
    data: {
      author_name: author,
      content: content,
      parent_id: parentId
    },
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  }).done(function() {
    form[0].reset();
    form.addClass('d-none');
    loadComments();
  }).fail(function(xhr) {
    let message = '{{ __("Không thể gửi phản hồi, vui lòng thử lại.") }}';
    if (xhr.responseJSON) {
      if (xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
      } else if (xhr.responseJSON.errors) {
        message = Object.values(xhr.responseJSON.errors).flat().join(' ');
      }
    }
    feedback.removeClass('d-none text-success').addClass('text-danger').text(message);
  }).always(function() {
    submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> {{ __("Gửi") }}');
  });
});

loadReactions();
loadComments();
document.addEventListener('DOMContentLoaded', function() {
  function setPanelHeight() {
    const board = document.querySelector('.puzzle-layout-board');
    const panel = document.querySelector('.puzzle-layout-panel');
    const sidePanel = document.querySelector('.puzzle-side-panel');

    if (board && panel) {
      if (window.innerWidth > 768) {
        // For larger screens: match heights
        const boardHeight = board.offsetHeight;
        panel.style.height = boardHeight + 'px';
        // Also update side panel height
        if (sidePanel) {
          sidePanel.style.height = boardHeight + 'px';
        }
      } else {
        // For smaller screens: reset to auto
        panel.style.height = 'auto';
        if (sidePanel) {
          sidePanel.style.height = 'auto';
        }
      }
    }
  }

  // Run on DOM ready
  setPanelHeight();

  // Run on window resize
  window.addEventListener('resize', setPanelHeight);

  // Handle images loading
  window.addEventListener('load', setPanelHeight);
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
function solvePuzzle(fenCode) {
  if (!game.validate_fen(fenCode + ' r - - 0 1').valid) {
    bootbox.alert({
    message: '{{ __("Bàn cờ thế") }} {{ __("không hợp lệ") }}',
    locale: '{{ __("vi") }}',
    centerVertical: true,
    closeButton: false,
    buttons: {
      ok: {
        className: 'btn-danger pulse-red'
      }
    }});
  } else {
    // $('#AdSenseModal').attr('data-url', '{{ url(__("/giai-co-the")) }}/' + fenCode + ' r - - 0 1').modal('show');
    window.location.href = '{{ url(__("/giai-co-the")) }}/' + fenCode + ' r - - 0 1';
  }
}
</script>
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
@include('layout.partials.players')
@include('layout.partials.userPuzzles')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
{{-- @include('layout.partials.puzzles') --}}
{{-- @include('layout.partials.books') --}}
@endsection
