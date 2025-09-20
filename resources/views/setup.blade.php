@extends('layout.gamelayout')
@section('aboveContent')
<h3 class="text-center my-2">Đang xếp bàn cờ thế</h3>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-4">
  <a id="new-board" class="w-25 btn btn-dark btn-lg"><i class="fad fa-save"></i> Lưu bàn cờ</a>
  <a id="undo" class="w-25 btn btn-danger btn-lg"><i class="fad fa-undo"></i> Xếp lại</a>
</p>
@if ($board != '')
<p class="w-100 text-center mt-2">
  <i class="fad fa-external-link-alt"></i> Mời bạn bè chơi bằng cách gửi liên kết.
</p>
<div id="copy-url" class="input-group mb-2 w-50 mx-auto" data-toggle="tooltip" data-placement="bottom" data-original-title="Ấn để sao chép">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input type="text" class="form-control" id="url" value="{{ url('/') }}/co-the/{{ $board }}">
</div>
<script>
$('#copy-url').on('click', function() {
  copyToClipboard('#url');
  selectText('#url')
});
</script>
@endif
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js" integrity="sha512-OqcrADJLG261FZjar4Z6c4CfLqd861A3yPNMb+vRQ2JwzFT49WT4lozrh3bcKxHxtDTgNiqgYbEUStzvZQRfgQ==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.svg.min.js" integrity="sha512-cX+p7MRIKvgo59Ap3QDj2ymdc7XFFCEJ71X+nWPT+3UxNylm/ztqgDJTbko2atIo4jiozj0dUpYb+xfv1bCl8g==" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.2/dist/FileSaver.min.js" integrity="sha256-u/J1Urdrk3nCYFefpoeTMgI5viU1ujCDu2fXXoSJjhg=" crossorigin="anonymous"></script>
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
$(window).resize(board.resize);
$('#new-board').on('click', function(){
  window.location.href = "{{ URL::to('/co-the/') }}/" + board.fen();
});
$('#undo').on('click', undo);
$("#capture").on('click', function() {
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
});
</script>
@endsection