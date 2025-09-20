@extends('layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title='Bạn đang thi đấu thế cờ "{{ $name }}'>Bạn đang thi đấu thế cờ "{{ $name }}"</h5>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="1" data-intro='Ấn vào đây để tải thế cờ "{{ $name }}" về máy' id="capture" class="btn btn-danger btn-lg text-light" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Lưu thành ảnh nào"><i class="fad fa-download"></i> Tải bàn cờ thế</a>
  @include('common.volumeBtn')
  @include('common.tourBtn')
</p>
@endsection
@section('rightSide')
<p class="w-100 text-center m-0">
  <span class="rounded p-0 d-block" id="game-status"></span>
</p>
<p class="w-100 text-center mx-0 mb-0 mt-2">
  <span class="rounded d-none" id="game-over"><i class="fad fa-flag-checkered"></i> HẾT TRẬN</span>
</p>
<div class="sharethis-inline-reaction-buttons"></div>
<p class="w-50 text-center lead mx-auto my-0"><i class="fas fa-thumbs-up"></i> Đánh giá: <span id="totalRating">0</span></p>
<div class="dropup mx-auto text-center my-1">
  <button class="btn btn-danger btn-lg dropdown-toggle" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span data-toggle="tooltip" data-placement="top" title="Đấu với bạn bè trong phòng"><i class="fad fa-gamepad-alt"></i> Chơi online</span>
  </button>
  @if ( isset($name) && $name != '' )
  <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> Đổi bên</a>
  @endif
  <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ URL::to('/') }}/phong/{{ md5(time()) }}">
    @if (!auth()->check())
    <a data-toggle="tooltip" data-placement="bottom" title="Đăng nhập để tham gia thi đấu" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="{{ URL::to('/dang-nhap') }}"><i class="fas fa-sign-in text-dark"></i> Đăng nhập</a>
    @else
    <a data-toggle="tooltip" data-placement="bottom" title="Thi đấu tính điểm và xếp hạng" id="create-room" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="javascript:createRoom();"><i class="fas fa-trophy-alt text-dark"></i> Thi đấu</a>
    @endif
    <a data-toggle="tooltip" data-placement="bottom" title="Chơi không cần mật khẩu" id="tao-phong-public" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-globe text-dark"></i> Công khai</a>
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
  <a data-step="2" data-intro="Ấn vào đây để xếp thế cờ mới" id="setup" class="w-25 btn btn-dark btn-lg" href="{{ url('/') }}/co-the"><i class="fad fa-plus-hexagon"></i> Xếp ván mới</a>
  <a data-step="3" data-intro="Ấn vào đây để quay lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> Đi lại</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="4" data-intro="Ấn vào đây để cộng điểm thế cờ" id="upvote" class="w-25 btn btn-dark btn-lg"><i class="fad fa-thumbs-up"></i> Thích</a>
  <a data-step="5" data-intro="Ấn vào đây để trừ điểm thế cờ" id="downvote" class="w-25 btn btn-dark btn-lg"><i class="fad fa-thumbs-down"></i> Không thích</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> Mời bạn bè chơi bằng cách gửi liên kết bên dưới.
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto" data-toggle="tooltip" data-placement="bottom" data-original-title="Ấn để sao chép">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="6" data-intro="Ấn vào đây để mời bạn bè cùng chơi" type="text" class="form-control" id="url" value="{{ url('/') }}/the-co/{{ $slug }}">
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

  var moveColor = 'Đỏ'
  if (game.turn() === 'b') {
    moveColor = 'Đen'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' bị chiếu bí'
  }

  // draw?
  else if (game.in_draw()) {
    status = 'Hòa'
  }

  // game still on
  else {
    status = 'Tới lượt ' + moveColor + ' đi'

    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + ' đang bị chiếu'
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
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> Hết trận');
    $('#header-status').html(': '+status+' - Hết trận');
  }
  if (game.fen().includes('resign')) {
    $('#header-status').html(': '+status+' - Đã bỏ cuộc');
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
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> Đã bỏ cuộc');
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
      message: "Bàn cờ thế không hợp lệ",
      locale: 'vi',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger',
          label: 'Xếp lại'
        }
      }
    });
  } else {
    html2canvas(document.getElementById("ban-co"), {
      windowWidth: document.getElementById("ban-co").scrollWidth,
      windowHeight: document.getElementById("ban-co").scrollHeight,
      allowTaint: true,
      useCORS: true,
      onrendered: function(canvas) {
        var context = canvas.getContext('2d');

        // Draw the Watermark
        context.font = '18px sans-serif';
        context.globalCompositeOperation = 'multiply';
        context.fillStyle = '#444422';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText('{{ $name }}', canvas.width / 2, canvas.height / 2);

        canvas.toBlob(function(blob) {
          saveAs(blob, "ban-co-{{ date('Y-m-d h:i:s A') }}.png"); 
        });
      }
    });
  }
});
$('#switch').on('click', board.flip);
function updateTotalRating() {
  $.ajax({
    url: '{{ url('/api') }}/totalRating',
    type: "POST",
    data : {
      'slug': '{{ $slug }}'
    },
    dataType: 'text'
  }).done(function(rating){
    $('span#totalRating').text(rating);
    $('#board-{{ md5($slug) }}').parent('div').attr('data-rating', rating);
    $('#{{ md5($slug) }} span.totalRating').text(rating);
    // $.ajax({
    //   url: '/getUserPuzzlesTemplate',
    //   type: 'GET'
    // }).done(function(response){
    //   $('#userPuzzlesWrapper').html(response);
    //   $('#userPuzzlesWrapper ul.pagination a.page-link').each(function(){
    //     $(this)[0].pathname = window.location.pathname;
    //   });
    // });
  });
}
updateTotalRating();
$('#upvote').on('click', function() {
  $.ajax({
    url: '{{ url('/api') }}/upvote',
    type: "POST",
    data : {
      'slug': '{{ $slug }}'
    },
    dataType: 'text'
  }).done(function(){
    updateTotalRating();
  });
});
$('#downvote').on('click', function() {
  $.ajax({
    url: '{{ url('/api') }}/downvote',
    type: "POST",
    data : {
      'slug': '{{ $slug }}'
    },
    dataType: 'text'
  }).done(function(){
    updateTotalRating();
  });
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
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
@include('layout.partials.players')
@include('layout.partials.userPuzzles')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
{{-- @include('layout.partials.puzzles') --}}
{{-- @include('layout.partials.books') --}}
@endsection