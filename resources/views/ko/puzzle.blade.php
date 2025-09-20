@extends('ko.layout.gamelayout')
@section('aboveBoard')
@if ($board != '')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="이것은 만들어진 퍼즐이다">퍼즐</h5>
@else
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="퍼즐 기술 향상">퍼즐을 설정하고 있습니다</h5>
@endif
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="1" data-intro="세팅을 마친 후 퍼즐을 다운로드하려면 여기를 클릭하세요" id="capture" class="btn btn-danger btn-lg text-light pulse-red" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="지금 이미지로 저장"><i class="fad fa-download"></i> 퍼즐 다운로드</a>
  @include('common.tourBtn')
</p>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="2" data-intro="퍼즐을 풀려면 여기를 클릭하세요" id="solve-puzzle" class="btn btn-danger btn-lg pulse-red" href="{{ url('/pojeureul-pulda') }}"><i class="fad fa-abacus"></i> 퍼즐을 풀다</a>
  @include('common.volumeBtn')
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="3" data-intro="퍼즐 보드를 저장하려면 여기를 클릭하세요" id="new-board" class="w-25 btn btn-dark btn-lg"><i class="fad fa-save"></i> 퍼즐 저장</a>
  <a data-step="4" data-intro="이전 동작으로 돌아가고 싶으면 여기를 클릭하세요" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo"></i> 실행 취소</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="5" data-intro="이 페이지를 모바일에서 열어주세요">
@include('common.qrCode')
</div>
@if ($board != '')
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> 아래 링크를 전송하여 친구를 초대하여 게임을 진행합니다.
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto pulse-light" data-toggle="tooltip" data-placement="bottom" data-original-title="복사하려면 클릭">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="6" data-intro="여기를 클릭하여 링크를 복사하고 친구를 초대하여 함께 플레이하세요" type="text" class="form-control" id="url" value="{{ url('/') }}/pojeul/{{ $board }}">
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
let game = new Xiangqi();
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
      message: "잘못된 퍼즐",
      locale: 'ko',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      }
    });
  } else {
    if (removeTrailingSlash("{{ URL::to('/peojeul/') }}/" + board.fen()) !== removeTrailingSlash(window.location.href)) {
      e.preventDefault();
      $('#AdSenseModal').attr('data-url', "{{ URL::to('/peojeul/') }}/" + board.fen()).modal('show');
    } else {
      window.location.href = "{{ URL::to('/peojeul/') }}/" + board.fen();
    }
  }
});
$('#undo').on('click', undo);
$("#capture").on('click', function() {
  if (!game.validate_fen(board.fen() + ' r - - 0 1').valid) {
    bootbox.alert({
      message: "잘못된 퍼즐",
      locale: 'ko',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
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
      message: "잘못된 퍼즐",
      locale: 'ko',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      }
    });
  } else {
    $('#AdSenseModal').attr('data-url', $(this).attr('href') + '/' + board.fen() + ' r - - 0 1').modal('show');
  }
});
</script>
@include('ko.layout.partials.puzzles')
@endsection