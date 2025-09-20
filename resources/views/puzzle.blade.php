@extends('layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="Bàn cờ thế">Bạn đang xếp<span id="puzzle-title"></span></h5>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="1" data-intro="Ấn vào đây để tải bàn cờ về khi đã xếp xong" id="capture" class="btn btn-danger btn-lg text-light" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Lưu thành ảnh nào"><i class="fad fa-download"></i> Tải bàn cờ thế</a>
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
<div class="dropup mx-auto text-center my-1">
  <button class="btn btn-danger btn-lg dropdown-toggle" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span data-toggle="tooltip" data-placement="top" title="Đấu với bạn bè trong phòng"><i class="fad fa-gamepad-alt"></i> Chơi online</span>
  </button>
  <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad fa-sync"></i> Đổi bên</a>
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
  <a data-step="2" data-intro="Ấn vào đây để vào trang giải thế cờ" id="solve-puzzle" class="w-25 btn btn-dark btn-lg" href="{{ url('/giai-co-the') }}"><i class="fad fa-abacus"></i> Giải cờ thế</a>
  <a data-step="3" data-intro="Ấn vào đây để đặt tên cho thế cờ" id="name-puzzle" class="w-25 btn btn-lg btn-dark" href="javascript:void(0);"><i class="fad fa-save"></i> Lưu thế cờ</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="4" data-intro="Ấn vào đây để lưu trạng thái bàn cờ" id="new-board" class="w-25 btn btn-dark btn-lg"><i class="fad fa-chess-board"></i> Lưu bàn cờ</a>
  <a data-step="5" data-intro="Ấn vào đây để xếp lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo"></i> Xếp lại</a>
</p>
@if ($board != '')
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> Mời bạn bè chơi bằng cách gửi liên kết bên dưới.
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto" data-toggle="tooltip" data-placement="bottom" data-original-title="Ấn để sao chép">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="6" data-intro="Ấn vào đây để mời bạn bè cùng chơi" type="text" class="form-control" id="url" value="{{ url('/') }}/co-the/{{ $board }}">
</div>
<script>
$('#copy-url').on('click', function() {
  copyToClipboard('#url');
  selectText('#url');
  $(this).tooltip('update');
});
</script>
@endif
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js" integrity="sha512-OqcrADJLG261FZjar4Z6c4CfLqd861A3yPNMb+vRQ2JwzFT49WT4lozrh3bcKxHxtDTgNiqgYbEUStzvZQRfgQ==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.svg.min.js" integrity="sha512-cX+p7MRIKvgo59Ap3QDj2ymdc7XFFCEJ71X+nWPT+3UxNylm/ztqgDJTbko2atIo4jiozj0dUpYb+xfv1bCl8g==" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.2/dist/FileSaver.min.js" integrity="sha256-u/J1Urdrk3nCYFefpoeTMgI5viU1ujCDu2fXXoSJjhg=" crossorigin="anonymous"></script>
@include('common.volume')
<script>
@if ($board != '')
let history = ['{{ $board }}'];
@else
let history = ['9/9/9/9/9/9/9/9/9/9'];
@endif
function onSnapEnd () {
  if (board.fen() != history[history.length - 1]){
    history.push(board.fen());
  }
  nuocCo.play();
  console.log(history);
}
function undo () {
  if (history.length > 1) {
    history.pop();
    board.position(history[history.length - 1]);
  }
  console.log(history);
}
const board = Xiangqiboard('ban-co', {
  draggable: true,
  dropOffBoard: 'trash',
  sparePieces: true,
  @if ($board != '')
  position: '{{ $board }}',
  @endif
  showNotation: true,
  onSnapEnd: onSnapEnd
});
let game = new Xiangqi(board.fen() + ' r - - 0 1');
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
    $('#header-status').html(': '+status+' - Hết trận');
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> Hết trận');
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
    $('#resign, #switch').addClass('disabled').attr('aria-disabled', true);
    config.draggable = false;
  }
}
const ratio = $('#ban-co').height() / $('#ban-co').width();
function adjustBoard() {
  const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
  width = ($(window).height() - 195) / ratio;
  width = Math.min(width, $('header > .container').width());
  height = width * ratio;
  $('#ban-co').css({'width': width});
  board.resize();
}
// adjustBoard();
// $(window).on('load resize', adjustBoard);
// $(document).ready(adjustBoard);
$(window).resize(board.resize);
$('#new-board').on('click auxclick', function(e){
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
    // if (removeTrailingSlash("{{ URL::to('/co-the/') }}/" + board.fen()) !== removeTrailingSlash(window.location.href)) {
    //   e.preventDefault();
    //   $('#AdSenseModal').attr('data-url', "{{ URL::to('/co-the/') }}/" + board.fen()).modal('show');
    // } else {
      window.location.href = "{{ URL::to('/co-the/') }}/" + board.fen();
    // }
  }
});
$('#undo').on('click', undo);
$('#name-puzzle').on('click auxclick', function(e) {
  e.preventDefault();
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
    bootbox.prompt({
      title: "Mời đặt tên cho Thế cờ:",
      locale: 'vi',
      centerVertical: true,
      closeButton: false,
      maxlength: 32,
      buttons: {
        confirm: {
          label: '<i class="fas fa-check"></i> Đặt tên',
          className: 'btn-danger'
        },
        cancel: {
          className: 'btn-dark text-light'
        }
      },
      callback: function(puzzleName){
        if (puzzleName != null) {
          let puzzleSlug = slugify(puzzleName, {
						lowercase: true,
						separator: "-",
					});
          if (puzzleName.trim().length === 0 || puzzleName.length === 0) {
            bootbox.alert({
              message: "Vui lòng đặt tên cho Thế cờ!",
              size: 'small',
              locale: 'vi',
              centerVertical: true,
              closeButton: false,
              buttons: {
                ok: {
                  className: 'btn-danger'
                }
              },
              callback: function () {
                $('#name-puzzle').trigger('click');
              }
            });
          } else {
            $.ajax({
              url: "{{ url('/api') }}/checkUniqueName",
              type: "POST",
              data : {
                'name': puzzleName,
                'slug': puzzleSlug,
                'fen': board.fen()
              },
              dataType: 'json'
            }).done(function(data){
              if (data.code == '1') {
                $.ajax({
                  url: '{{ url('/api') }}/createPuzzle',
                  type: "POST",
                  data : {
                    'name': puzzleName,
                    'slug': puzzleSlug,
                    'fen': board.fen(),
                    'rating': '0'
                  },
                  dataType: 'text'
                }).done(function(){
                  // $('#AdSenseModal').attr('data-url', '{{ url('/the-co/') }}' + '/' + puzzleSlug).modal('show');
                  // $('#adModalCloseBtn').attr('data-original-title', $('#AdSenseModal').attr('data-url'));
                  // $('#adModalCloseBtn').tooltip();
                  window.location.href = '{{ url('/the-co/') }}' + '/' + puzzleSlug;
                });
              } else {
                bootbox.alert({
                  message: data.message,
                  locale: 'vi',
                  size: 'small',
                  centerVertical: true,
                  closeButton: false,
                  buttons: {
                    ok: {
                      className: 'btn-danger',
                      label: 'Xếp lại'
                    }
                  },
                  callback: function () {
                    $('#name-puzzle').trigger('click');
                  }
                });
              }
            });
          }
        }
      }
    });
  }
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
    html2canvas(document.getElementsByClassName("board-1ef78")[0], {
      windowWidth: document.getElementsByClassName("board-1ef78")[0].scrollWidth,
      windowHeight: document.getElementsByClassName("board-1ef78")[0].scrollHeight,
      allowTaint: true,
      useCORS: true,
      onrendered: function(canvas) {
        var context = canvas.getContext('2d');

        // Draw the Watermark
        context.font = '25px cursive';
        context.globalCompositeOperation = 'multiply';
        context.fillStyle = '#444422';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText('COTUONG.TOP', canvas.width / 2, canvas.height / 2);

        canvas.toBlob(function(blob) {
          saveAs(blob, "ban-co-{{ date('Y-m-d h:i:s A') }}.png"); 
        });
      }
    });
  }
});
$('#solve-puzzle').on('click auxclick', function(e){
  e.preventDefault();
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
    // $('#AdSenseModal').attr('data-url', $(this).attr('href') + '/' + board.fen() + ' r - - 0 1').modal('show');
    window.location.href = $(this).attr('href') + '/' + board.fen() + ' r - - 0 1';
  }
});
$('#switch').on('click', board.flip);
$('#compete').on('click auxclick', function(e){
  e.preventDefault();
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
    window.location.href = $(this).attr('href') + '/' + board.fen();
  }
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