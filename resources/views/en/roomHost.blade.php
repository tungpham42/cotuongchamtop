@extends('en.layout.gamelayout')
@section('aboveBoard')
<h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="Playing red">You are the host</h5>
<span id="room-name">Room's name: {{ $room->name }}</span>
@endsection
@section('aboveContent')
<p class="w-100 text-center mt-0 mb-1">
  <i class="fad fa-external-link-alt"></i> Inviting friend to play by sending the link below.
</p>
<div id="copy-url-black" class="input-group my-1 w-50 mx-auto pulse-light" data-toggle="tooltip" data-placement="bottom" data-original-title="Click to copy">
  <div class="input-group-prepend">
    <span class="input-group-text" id="url-addon-black"><i class="fal fa-copy"></i></span>
  </div>
  <input data-step="1" data-intro="Click here to copy the link and invite friends to play" type="text" class="form-control" id="url-black" value="{{ url('/') }}/room/{{ $roomCode }}/guest">
</div>
<p id="room-code" class="w-100 text-center mt-0 mb-1">
  <span data-step="4" data-intro="Use this room code to search for match" class="alert alert-dark d-inline-block" role="alert" data-toggle="tooltip" data-placement="bottom" data-original-title="Copy this room code"><i class="fad fa-trophy-alt"></i> Room code: <strong style="cursor: pointer;">{{ $roomCode }}</strong></span>
  <input type="hidden" id="room-code-input" value="{{ $roomCode }}">
</p>
<div class="text-center mx-auto" style="width: fit-content;" data-step="5" data-intro="Open this page on mobile">
@include('common.qrCode')
</div>
@if ($room['pass'] != null)
<div data-step="2" data-intro="Click here to change room's password" id="change-pass" class="input-group my-1 w-50 mx-auto">
  <label class="m-auto" for="inputPassword">New password</label>
  <input type="password" id="inputPassword" class="form-control mx-2" required />
  <button type="submit" id="changePassword" class="btn btn-dark" onclick="validateForm();">Change</button>
  <div id="status" class="w-100"></div>
</div>
@endif
@endsection
@section('belowContent')
<p class="w-100 text-center">
  <a data-step="3" data-intro="Click here if you are out of clues" id="resign" class="btn btn-dark btn-lg"><i class="fad fa-flag"></i> Resign</a>
</p>
<script>
@if ($room['pass'] != null)
function validateForm() {
  document.getElementById('status').innerHTML = "Processing...";
  formData = {
    'ma-phong': '{{ $roomCode }}',
    'pass': $('#inputPassword').val()
  };
  $.ajax({
    url: "{{ url('/api') }}/changePass",
    type: "POST",
    data : formData,
    dataType: 'json',
    success: function(data, textStatus, jqXHR)
    {
      $('#status').text(data.message);
      console.log(data);
      if (data.code) //If mail was sent successfully, reset the form.
      $('#inputPassword').val("");
    },
    error: function (jqXHR, textStatus, errorThrown)
    {
      $('#status').text(jqXHR);
    }
  });
}
$(document).ready(function() {
  bootbox.prompt({
    title: "Please enter the password for this Room:",
    centerVertical: true,
    locale: 'en',
    closeButton: false,
    buttons: {
      confirm: {
        label: '<i class="fas fa-check"></i> Enter',
        className: 'btn-danger pulse-red'
      },
      cancel: {
        className: 'btn-dark text-light'
      }
    },
    callback: function(password){
      if (password != null) {
        $.ajax({
          type: "GET",
          url: '{{ url('/api') }}/getPass/' + '{{ $roomCode }}',
          dataType: 'text'
        }).done(function(data) {
          if (data != password) {
            bootbox.alert({
              message: "Wrong password! You will be redirected to the Home page",
              size: 'small',
              centerVertical: true,
              locale: 'en',
              closeButton: false,
              buttons: {
                ok: {
                  className: 'btn-danger pulse-red'
                }
              },
              callback: function () {
                window.location.href = '{{ url('/en') }}';
              }
            });
          }
        });
      } else {
        bootbox.alert({
          message: "You clicked Cancel! You will be redirected to the Home page",
          size: 'small',
          centerVertical: true,
          locale: 'en',
          closeButton: false,
          buttons: {
            ok: {
              className: 'btn-danger pulse-red'
            }
          },
          callback: function () {
            window.location.href = '{{ url('/en') }}';
          }
        });
      }
    }
  });
});
@endif
let board = null;
let game = new Xiangqi();
let currentFEN = game.fen();

function updateFenCode(roomCode) {
  board.position(game.fen(), true);
  game.load(game.fen());
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/updateFEN',
    data: {
      'ma-phong': roomCode,
      'FEN': game.fen()
    },
    dataType: 'text'
  });
}

function manipulateRoom(roomCode) {
  $.ajax({
    type: "GET",
    url: '{{ url('/api') }}/readFEN/' + roomCode,
    dataType: 'text'
  }).done(function(newFEN) {
    if (newFEN != currentFEN) {
      currentFEN = game.fen();
      if (newFEN == game.fen()) {
        // my move
        board.position(newFEN, true);
        game.load(newFEN);
      } else {
        // opponent's move
        board.position(newFEN, true);
        game.load(newFEN);
        nuocCo.play();
      }
    }
    updateStatus()
  });
}

function updateResult(roomCode, result) {
  @if(!isset($room->host_id))
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/updateSideResult',
    data: {
      'ma-phong': roomCode,
      'result': result,
      'lang': 'en',
      'side': 'red'
    },
    dataType: 'json'
  }).done(function(data) {
    bootbox.alert({
      message: data.success,
      size: 'small',
      centerVertical: true,
      closeButton: false,
      locale: 'en',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      },
      callback: function () {
        window.location.href = "{{ url('/rooms') }}";
      }
    });
  });
  @endif
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
  
  if ((board.orientation() == 'red' && game.turn() === 'b') || (board.orientation() == 'black' && game.turn() === 'r')) {
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
  //if (move === null) return 'snapback';
  updateStatus()
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
  nuocCo.play();
  updateFenCode('{{ $roomCode }}');
  // updateStatus();
}

const updateBoard = setInterval(function() {
  manipulateRoom('{{ $roomCode }}');
}, 1000);

function updateStatus () {
  var status = ''

  var moveColor = 'Red'
  if (game.turn() === 'b') {
    moveColor = 'Black'
  }

  // checkmate?
  if (game.in_checkmate()) {
    status = moveColor + ' is in checkmate'
    if (game.turn() === 'b') {
      updateResult('{{ $roomCode }}', '1');
    } else if (game.turn() === 'r') {
      updateResult('{{ $roomCode }}', '-1');
    }
  }

  // draw?
  else if (game.in_draw()) {
    status = 'Drawn position'
    updateResult('{{ $roomCode }}', '0');
  }

  // game still on
  else {
    status = moveColor + "'s turn to move"
    if (game.game_over() && !game.in_draw() && !game.fen().includes('resign')) {
      if (game.turn() === 'b') {
        updateResult('{{ $roomCode }}', '1');
      } else if (game.turn() === 'r') {
        updateResult('{{ $roomCode }}', '-1');
      }
    }
    // check?
    if (game.in_check()) {
      status += ', ' + moveColor + ' is in check'
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
    $('#game-over').removeClass('d-none').addClass('d-inline-block').html('<i class="fad fa-flag-checkered"></i> Game over');
    $('#header-status').html(': '+status+' - Game over');
    // evtSource.close();
    clearInterval(updateBoard);
  }
  if (game.fen().includes('resign')) {
    $('#header-status').html(': '+status+' - Resigned');
    bootbox.alert({
      message: '<i class="fad fa-flag-checkered"></i> Resigned',
      locale: 'en',
      centerVertical: true,
      closeButton: false,
      size: 'small',
      buttons: {
        ok: {
          className: 'btn-danger pulse-red'
        }
      },
      callback: function() {
        updateResult('{{ $roomCode }}', '0');
      }
    });
    $('#game-over').html('<i class="fad fa-flag-checkered"></i> Resigned');
    $('#resign').addClass('disabled').attr('aria-disabled', true);
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
  orientation: "red"
  //pieceTheme: '/static/img/xiangqipieces/traditional/{piece}.svg'

};
board = Xiangqiboard('ban-co', config);
$(window).resize(board.resize);
updateStatus()
// window.onload = function(){
//   if (board.orientation() == 'red' && game.turn() === 'b') {
//     location.href = $('#black-link').attr('href');
//   }
// };
// let evtSource = new EventSource("{{ url('/api') }}/getFEN/{{ $roomCode }}");

// evtSource.onmessage = function (e) {
//   let newFEN = e.data;
//   console.log(newFEN);
//   if (newFEN != currentFEN) {
//     currentFEN = game.fen();
//     $.ajax({
//       type: "POST",
//       url: '{{ url('/api') }}/updateFEN',
//       data: {
//         'ma-phong': '{{ $roomCode }}',
//         'FEN': newFEN
//       },
//       dataType: 'text'
//     });
//     if (newFEN == game.fen()) {
//       // my move
//       board.position(newFEN, true);
//       game.load(newFEN);
//     } else {
//       // opponent's move
//       board.position(newFEN, true);
//       game.load(newFEN);
//       if (!game.fen().includes('resign')) {
//         nuocCo.play();
//       }
//     }
//   }
//   updateStatus();
// };
$('#resign').on('click', function() {
  game.load(game.fen() + ' resign');
  updateFenCode('{{ $roomCode }}');
  updateStatus();
});
</script>
{{-- @include('en.layout.partials.comments') --}}
@endsection