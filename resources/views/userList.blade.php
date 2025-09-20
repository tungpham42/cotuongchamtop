@extends('layout.mainlayout')
@section('aboveContent')
<div class="container-fluid game px-0">
  <div class="container p-3">
    <div class="row">
    @include('layout.partials.findMatch')
    </div>
    <h2 class="h1-responsivefooter text-center my-4">Thành viên</h2>
    <div class="table-responsive">
      <table id="danh-sach-ky-thu" class="table table-bordered table-hover table-striped table-sm">
        <thead class="thead-light">
          <tr>
            <th class="text-center" scope="col">Xếp hạng</th>
            <th class="text-center" scope="col">Kỳ thủ</th>
            <th class="text-center" scope="col">Elo</th>
            <th class="text-center" scope="col">Hành động</th>
            <th class="text-center" scope="col">Thời điểm tham gia</th>
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
<script>
$(document).ready(function () {
  console.log('List URL: ' + '{{ route('usersVi.list') }}');
  var table = $('#danh-sach-ky-thu').DataTable({
    processing: true,
    serverSide: true,
    ordering: true,
    searching: true,
    ajax: {
      url: "{{ route('usersVi.list') }}"
    },
    deferRender: true,
    columns: [
      {
        data: 'rank',
        name: 'rank',
        orderable: false,
        searchable: false,
        className: 'text-center'
      },
      {
        data: 'name',
        name: 'name',
        orderable: true,
        searchable: true,
        className: 'text-center room-code'
      },
      {
        data: 'elo',
        name: 'elo',
        orderable: true,
        searchable: true,
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
      'url': '{{ URL::to('/') }}/js/TableUserVn.json'
    },
    'order': [[ 2, 'desc' ]],
    'drawCallback': function() {
      $('.tooltip').remove();
      $('[data-toggle="tooltip"]').tooltip(function() {
        html : true
      });
      $('#danh-sach-ky-thu .stopPromotion').each(function(){
        $(this).on('click auxclick', function(e){
          e.preventDefault();
          $('#AdSenseModal').attr('data-url', $(this).attr('href')).modal('show');
          $('#adModalCloseBtn').attr('data-original-title', $('#AdSenseModal').attr('data-url'));
          $('#adModalCloseBtn').tooltip();
        });
      });
      $('.room-code, #danh-sach-ky-thu .btn').each(function(){
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
function compete(guestId) {
  var maPhong = '{{ md5(time()) }}';
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/hasRoomcode',
    data: {
      'ma-phong': maPhong
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
            className: 'btn-danger'
          },
          cancel: {
            className: 'btn-dark text-light'
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
                  className: 'btn-danger'
                }
                },
                callback: function () {
                  $('#create-room').trigger('click');
                }
              });
            } else {
              $.ajax({
                type: "POST",
                url: '{{ url('/api') }}/compete',
                data: {
                  'ma-phong': maPhong,
                  'ten-phong': roomName,
                  'FEN': '{{ env('INITIAL_FEN') }}',
                  'pass': '',
                  'host_id': '{{ auth()->id() }}',
                  'guest_id': guestId
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
                      className: 'btn-danger',
                      label: 'Oki'
                    }
                  },
                  callback: function(){
                    $.ajax({
                      type: "POST",
                      url: '{{ url('/api') }}/competeMail',
                      data: {
                        'ma-phong': maPhong,
                        'ten-phong': roomName,
                        'host_id': '{{ auth()->id() }}',
                        'guest_id': guestId
                      },
                      dataType: 'json'
                    }).done(function(mailData) {
                      bootbox.alert({
                        message: mailData.message,
                        size: 'small',
                        centerVertical: true,
                        closeButton: false,
                        buttons: {
                          ok: {
                            className: 'btn-danger',
                            label: 'Oki'
                          }
                        },
                        callback: function(){
                          window.location.href = '{{ url('/phong/') }}' + '/' + maPhong;
                        }
                      });
                    });
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
            className: 'btn-danger',
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
@endif
</script>
<input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ URL::to('/') }}" >
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
{{-- @include('layout.partials.userPuzzles') --}}
@include('layout.partials.players')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
@endsection