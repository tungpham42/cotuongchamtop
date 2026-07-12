@extends('layout.mainlayout')
@inject('userPresenter', 'App\Presenters\UserPresenter')
@section('aboveContent')
<div class="container-fluid game px-0">
  <div class="container p-3">
    <div class="row">
    @include('layout.partials.findMatch')
    </div>
    <h2 class="h1-responsivefooter text-center my-4">{{ __('Thành viên') }}</h2>
    <div class="card shadow-lg mb-4">
      <div class="card-body p-0">
        <table id="danh-sach-ky-thu" class="table table-hover table-sm mb-0 dt-responsive nowrap w-100">
          <thead>
            <tr>
              <th class="text-center" scope="col">{{ __('Xếp hạng') }}</th>
              <th class="text-center" scope="col">{{ __('Kỳ thủ') }}</th>
              <th class="text-center" scope="col">{{ __('Elo') }}</th>
              <th class="text-center" scope="col">{{ __('Hành động') }}</th>
              <th class="text-center" scope="col">{{ __('Thời điểm tham gia') }}</th>
            </tr>
          </thead>
          <tbody>
            <!-- DataTables will populate this natively utilizing the theme's text colors -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
@section('belowContent')
<script>
$(document).ready(function () {
  console.log('List URL: ' + '{{ route('users' . ucfirst(app()->getLocale()) . '.list') }}');
  var table = $('#danh-sach-ky-thu').DataTable({
    processing: true,
    responsive: true,
    serverSide: true,
    ordering: true,
    searching: true,
    ajax: {
      url: "{{ route('users' . ucfirst(app()->getLocale()) . '.list') }}"
    },
    deferRender: true,
    columnDefs: [
      { responsivePriority: 1, targets: 1 }, // Kỳ thủ
      { responsivePriority: 2, targets: 3 }, // Hành động
      { responsivePriority: 3, targets: 2 }, // Elo
      { responsivePriority: 4, targets: 0 }, // Xếp hạng
      { responsivePriority: 5, targets: 4 }  // Tham gia
    ],
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
      'url': '{{ url('/') }}/js/TableUser{{ ucfirst(app()->getLocale()) }}.json',
      processing: '{{ __("Đang tải") }} <span class="indicator"></span><span class="indicator"></span><span class="indicator"></span>'
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
        title: "{{ __('Mời đặt tên cho Phòng thi đấu:') }}",
        locale: '{{ app()->getLocale() }}',
        centerVertical: true,
        closeButton: false,
        maxlength: 32,
        buttons: {
          confirm: {
            label: '<i class="fas fa-check"></i> {{ __('Đặt tên') }}',
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
                message: "{{ __('Vui lòng đặt tên cho phòng!') }}",
                size: 'small',
                locale: '{{ app()->getLocale() }}',
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
                  message: "{{ __('Bạn đã tạo phòng thành công.') }}",
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
                        'guest_id': guestId,
                        'lang': '{{ app()->getLocale() }}'
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
                          window.location.href = '{{ url(__('/phong/')) }}' + '/' + maPhong;
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
        message: "{{ __('Mã phòng bị trùng, vui lòng thử lại.') }}",
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
<input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}" >
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
{{-- @include('layout.partials.userPuzzles') --}}
@include('layout.partials.players')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
@endsection
