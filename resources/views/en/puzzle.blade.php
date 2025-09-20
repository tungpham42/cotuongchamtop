@extends('en.layout.gamelayout')
@section('aboveBoard')
@if ($board != '')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="This is a created puzzle">Puzzle</h5>
@else
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="Improve your chess puzzle skills">You are setting up the puzzle</h5>
@endif
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="1" data-intro="Click here to download the puzzle after setting up" id="capture" class="btn btn-danger btn-lg text-light pulse-red" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Save as image now"><i class="fad fa-download"></i> Download the puzzle</a>
  @include('common.tourBtn')
</p>
@endsection
@section('belowContent')
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="2" data-intro="Click here to solve puzzle" id="solve-puzzle" class="btn btn-danger btn-lg pulse-red" href="{{ url('/solve-puzzle') }}"><i class="fad fa-abacus"></i> Solve puzzle</a>
  @include('common.volumeBtn')
</p>
<p class="w-100 text-center mt-0 mb-1">
  <a data-step="3" data-intro="Click here to save the puzzle board" id="new-board" class="w-25 btn btn-dark btn-lg"><i class="fad fa-save"></i> Save puzzle</a>
  <a data-step="4" data-intro="Click here if you want to go back to previous move" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo"></i> Undo</a>
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="5" data-intro="Open this page on mobile">
@include('common.qrCode')
</div>
@if ($board != '')
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> Inviting friend to play by sending the link below.
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto pulse-light" data-toggle="tooltip" data-placement="bottom" data-original-title="Click to copy">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="6" data-intro="Click here to copy the link and invite friends to play" type="text" class="form-control" id="url" value="{{ url('/') }}/puzzle/{{ $board }}">
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
  showNotation: false,
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
      message: "Invalid puzzle",
      locale: 'en',
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
    if (removeTrailingSlash("{{ URL::to('/puzzle/') }}/" + board.fen()) !== removeTrailingSlash(window.location.href)) {
      e.preventDefault();
      $('#AdSenseModal').attr('data-url', "{{ URL::to('/puzzle/') }}/" + board.fen()).modal('show');
    } else {
      window.location.href = "{{ URL::to('/puzzle/') }}/" + board.fen();
    }
  }
});
$('#switch').on('click', board.flip);
$('#undo').on('click', undo);
$("#capture").on('click', function() {
  if (!game.validate_fen(board.fen() + ' r - - 0 1').valid) {
    bootbox.alert({
      message: "Invalid puzzle",
      locale: 'en',
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
      message: "Invalid puzzle",
      locale: 'en',
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
@include('en.layout.partials.puzzles')
@endsection