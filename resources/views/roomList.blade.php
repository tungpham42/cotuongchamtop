@extends('layout.mainlayout')
@section('aboveContent')
<div class="container-fluid game px-0">
  <div class="container p-3">
    <div class="row">
    @include('layout.partials.findMatch')
    </div>
    <h2 class="h1-responsivefooter text-center my-4">Sảnh chờ</h2>
    <div class="dropdown mx-auto text-center mb-3">
      <button data-step="1" data-intro="Ấn vào đây để tham gia thi đấu với các kỳ thủ khác" class="btn btn-danger btn-lg dropdown-toggle pulse-red" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span data-toggle="tooltip" data-placement="top" title="Đấu với bạn bè trong phòng"><i class="fad fa-gamepad-alt"></i> Chơi online</span>
      </button>
      <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ URL::to('/') }}/phong/{{ md5(time()) }}">
        @if (!auth()->check())
        <a data-toggle="tooltip" data-placement="bottom" title="Đăng nhập để tham gia thi đấu" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="{{ URL::to('/dang-nhap') }}"><i class="fas fa-sign-in text-dark"></i> Đăng nhập</a>
        @else
        <a id="create-room" data-toggle="tooltip" data-placement="bottom" title="Thi đấu tính điểm và xếp hạng" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="javascript:createRoom();"><i class="fas fa-trophy-alt text-dark"></i> Thi đấu</a>
        @endif
        <a data-toggle="tooltip" data-placement="bottom" title="Chơi cần mật khẩu" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> Riêng tư</a>
        @if ($randomRoom != null)
        <a data-toggle="tooltip" data-placement="bottom" title="Chơi trong phòng Công khai ngẫu nhiên" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ URL::to('/') }}/phong/{{ $randomRoom['code'] }}/ngau-nhien"><i class="fas fa-random text-dark"></i> Ngẫu nhiên</a>
        @endif
      </div>
      @include('common.tourBtn')
    </div>
    <div data-step="2" data-intro="Danh sách tất cả các trận đấu" class="table-responsive">
      <table id="danh-sach-phong" class="table table-bordered table-hover table-striped table-sm">
        <thead class="thead-light">
          <tr>
            <th class="text-center" scope="col">Tên phòng</th>
            <th class="text-center" scope="col">Tới lượt</th>
            <th class="text-center" scope="col">Kết quả</th>
            <th class="text-center" scope="col">Hành động</th>
            <th class="text-center" scope="col">Lần cuối chơi</th>
          </tr>
        </thead>
        <tbody style="background-color: whitesmoke;">
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
@section('belowContent')
<div class="modal fade" id="HoveredBoardModal" tabindex="-1" role="dialog" aria-label="HoveredBoard" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-url="">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 320px; margin: auto;">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title"><i class="far fa-eye"></i> Xem trước "<span></span>"</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <div id="HoveredBoardBody"></div>
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function () {
  console.log('List URL: ' + '{{ route('roomsVi.list') }}');
  var table = $('#danh-sach-phong').DataTable({
    processing: true,
    serverSide: true,
    ordering: true,
    searching: true,
    ajax: {
      url: "{{ route('roomsVi.list') }}"
    },
    deferRender: true,
    columns: [
      {
        data: 'code',
        name: 'code',
        orderable: true,
        searchable: true,
        className: 'text-center room-code'
      },
      {
        data: 'turn',
        name: 'turn',
        orderable: false,
        searchable: false,
        className: 'text-center'
      },
      {
        data: 'result',
        name: 'result',
        orderable: true,
        searchable: false,
        className: 'text-center'
      },
      {
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false,
        className: 'text-center room-action'
      },
      {
        data: 'time',
        name: 'time',
        orderable: true,
        searchable: true,
        className: 'text-right room-time'
      }
    ],
    'language': {
      'url': '{{ URL::to('/') }}/js/TableVn.json?v=1'
    },
    'createdRow': function(row, data, dataIndex) {
      var selectedFen = $(row).find('td.room-code > a').attr('data-fen');
      var selectedName = $(row).find('td.room-code > a').text();
      $(row).attr('data-fen', selectedFen);
      $(row).attr('data-name', selectedName);
    },
    'order': [[ 4, 'desc' ]],
    'drawCallback': function() {
      $('.tooltip').remove();
      $('[data-toggle="tooltip"]').tooltip(function() {
        html : true
      });
      $('#danh-sach-phong .stopPromotion').each(function(){
        $(this).on('click auxclick', function(e){
          e.preventDefault();
          $('#AdSenseModal').attr('data-url', $(this).attr('href')).modal('show');
          $('#adModalCloseBtn').attr('data-original-title', $('#AdSenseModal').attr('data-url'));
          $('#adModalCloseBtn').tooltip();
        });
      });
      $('#danh-sach-phong > tbody > tr').each(function(index){
        var fenCode = $(this).attr('data-fen');
        var roomName = $(this).attr('data-name');
        $(this).children('td.room-action').find('.previewBtn').on('click', function(){
          $('#HoveredBoardModal').on('shown.bs.modal', function() {
            var container = $('#HoveredBoardBody');
            container.empty();
            var boardId = 'hoveredBoardId_' + index;
            var boardDiv = $('<div class="innerBoard">').attr('id', boardId);
            container.html(boardDiv);
            let boardConfig = {
              position: fenCode              
            };
            if (fenCode.includes(' r ')) {
              boardConfig.orientation = 'red';
            } else if (fenCode.includes(' b ')) {
              boardConfig.orientation = 'black';
            }
            var hoveredBoardDiv = Xiangqiboard(boardId, boardConfig);
            $('#HoveredBoardModal .modal-title > span').text(roomName);
          });
          $('#HoveredBoardModal').modal('show');
        });
      });
      $('.watch-btn').each(function() {
        $(this).on('mouseenter mouseleave', function() {
          if ($(this).find('i').hasClass('fa-lock')) {
            $(this).find('i').removeClass('fa-lock').addClass('fa-unlock');
          } else if ($(this).find('i').hasClass('fa-unlock')) {
            $(this).find('i').removeClass('fa-unlock').addClass('fa-lock');
          }
          if ($(this).hasClass('btn-light')) {
            $(this).removeClass('btn-light').addClass('btn-warning');
          } else if ($(this).hasClass('btn-warning')) {
            $(this).removeClass('btn-warning').addClass('btn-light');
          }
          if ($(this).hasClass('text-light')) {
            $(this).removeClass('text-light').addClass('text-warning');
          } else if ($(this).hasClass('text-warning')) {
            $(this).removeClass('text-warning').addClass('text-light');
          }
        });
      });
      $('.room-code, #danh-sach-phong .btn').each(function(){
        $(this).on('mouseenter mouseleave', function() {
          if ($(this).find('i').hasClass('far')) {
            $(this).find('i').removeClass('far').addClass('fas');
          } else if ($(this).find('i').hasClass('fas')) {
            $(this).find('i').removeClass('fas').addClass('far');
          }
        });
      });
    }
  });
  $(window).on('resize', function () {
    table.columns.adjust();
  });
  setInterval( function () {
    table.ajax.reload( null, false ); // user paging is not reset on reload
  }, 60000 );
  $('.dataTables_length').addClass('bs-select');
});
@if (auth()->check())
function createRoom() {
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/hasRoomcode',
    data: {
      'ma-phong': '{{ md5(time()) }}'
    },
    dataType: 'text'
  }).done(function(data){
    if (data == 'no') {
      bootbox.prompt({
        title: "Mời đặt tên cho Phòng thi đấu:",
        locale: 'vi',
        centerVertical: true,
        closeButton: false,
        maxlength: 32,
        buttons: {
          confirm: {
            label: '<i class="fas fa-check"></i> Đặt tên',
            className: 'btn-danger pulse-red'
          }
        },
        callback: function(roomName){
          if (roomName != null) {
            if (roomName.trim().length === 0 || roomName.length === 0) {
              bootbox.alert({
                message: "Vui lòng đặt tên cho phòng!",
                size: 'small',
                locale: 'vi',
                centerVertical: true,
                closeButton: false,
                buttons: {
                ok: {
                  className: 'btn-danger pulse-red'
                }
                },
                callback: function () {
                  $('#create-room').trigger('click');
                }
              });
            } else {
              $.ajax({
                type: "POST",
                url: '{{ url('/api') }}/createRoom',
                data: {
                  'ma-phong': '{{ md5(time()) }}',
                  'ten-phong': roomName,
                  'FEN': '{{ env('INITIAL_FEN') }}',
                  'pass': '',
                  'host_id': '{{ auth()->id() }}'
                },
                dataType: 'text'
              }).done(function() {
                bootbox.alert({
                  message: "Bạn đã tạo phòng thành công.",
                  size: 'small',
                  centerVertical: true,
                  closeButton: false,
                  buttons: {
                    ok: {
                      className: 'btn-danger pulse-red',
                      label: 'Oki'
                    }
                  },
                  callback: function(){
                    window.location.href = '{{ url('/phong/') }}' + '/' + '{{ md5(time()) }}';
                  }
                });
              });
            }
          }
        }
      });
    } else if (data == 'yes') {
      bootbox.alert({
        message: "Mã phòng bị trùng, vui lòng thử lại.",
        size: 'small',
        centerVertical: true,
        closeButton: false,
        buttons: {
          ok: {
            className: 'btn-danger pulse-red',
            label: 'Oki'
          }
        },
        callback: function(){
          setTimeout(() => {
            location.reload();
          }, 500);
        }
      });
    }
  });
}
function joinMatch(roomCode) {
  var hostId = '';
  var guestId = '';
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/getRoomIds',
    data: {
      'ma-phong': roomCode
    },
    dataType: 'json'
  }).done(function(data){
    hostId = data.host_id;
    guestId = data.guest_id;
    if (hostId != '{{ auth()->id() }}' && guestId != '{{ auth()->id() }}') {
      $.ajax({
        type: "POST",
        url: '{{ url('/api') }}/joinRoom',
        data: {
          'ma-phong': roomCode,
          'guest_id': '{{ auth()->id() }}'
        },
        dataType: 'text'
      }).done(function() {
        bootbox.alert({
          message: "Hãy chuẩn bị vào phòng!",
          size: 'small',
          centerVertical: true,
          closeButton: false,
          buttons: {
            ok: {
              className: 'btn-danger pulse-red',
              label: 'Oki'
            }
          },
          callback: function(){
            window.location.href = '{{ url('/phong/') }}' + '/' + roomCode + '/khach';
          }
        });
      });
    } else if (guestId == '{{ auth()->id() }}') {
      bootbox.alert({
        message: "Mời bạn quay lại phòng!",
        size: 'small',
        centerVertical: true,
        closeButton: false,
        buttons: {
          ok: {
            className: 'btn-danger pulse-red',
            label: 'Oki'
          }
        },
        callback: function(){
          window.location.href = '{{ url('/phong/') }}' + '/' + roomCode + '/khach';
        }
      });
    } else if (hostId == '{{ auth()->id() }}') {
      bootbox.alert({
        message: "Mời bạn vào lại phòng của mình!",
        size: 'small',
        centerVertical: true,
        closeButton: false,
        buttons: {
          ok: {
            className: 'btn-danger pulse-red',
            label: 'Oki'
          }
        },
        callback: function(){
          window.location.href = '{{ url('/phong/') }}' + '/' + roomCode;
        }
      });
    }
  });
}
@endif
</script>
<input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ URL::to('/') }}" >
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
{{-- @include('layout.partials.userPuzzles') --}}
@include('layout.partials.players')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
@endsection