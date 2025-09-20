@extends('layout.mainlayout')
@section('aboveContent')
<div class="container-fluid game px-0">
  <div class="container p-3">
    <div class="row">
    @include('layout.partials.findMatch')
    </div>
    <h2 class="h1-responsivefooter text-center my-4">Tất cả thế cờ</h2>
    <div class="dropdown mx-auto text-center mb-3">
      <a data-step="1" data-intro="Ấn vào đây để xếp thế cờ mới" id="setup" class="btn btn-danger btn-lg" href="{{ url('/') }}/co-the"><i class="fad fa-plus-hexagon"></i> Xếp ván mới</a>
      @include('common.tourBtn')
    </div>
    <div data-step="2" data-intro="Danh sách tất cả các thế cờ" class="table-responsive">
      <table id="danh-sach-the-co" class="table table-bordered table-hover table-striped table-sm">
        <thead class="thead-light">
          <tr>
            <th class="text-center" scope="col">Xếp hạng</th>
            <th class="text-center" scope="col">Tên thế cờ</th>
            <th class="text-center" scope="col">Đánh giá</th>
            <th class="text-center" scope="col">Hành động</th>
            <th class="text-center" scope="col">Thời gian cập nhật</th>
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
  console.log('List URL: ' + '{{ route('puzzlesVi.list') }}');
  var table = $('#danh-sach-the-co').DataTable({
    processing: true,
    serverSide: true,
    ordering: true,
    searching: true,
    ajax: {
      url: "{{ route('puzzlesVi.list') }}"
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
        data: 'rating',
        name: 'rating',
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
      'url': '{{ URL::to('/') }}/js/TablePuzzleVn.json'
    },
    'createdRow': function(row, data, dataIndex) {
      var selectedFen = $(row).find('td.room-code > a').attr('data-fen');
      var selectedName = $(row).find('td.room-code > a').text();
      $(row).attr('data-fen', selectedFen);
      $(row).attr('data-name', selectedName);
    },
    'order': [[ 2, 'desc' ]],
    'drawCallback': function() {
      $('.tooltip').remove();
      $('[data-toggle="tooltip"]').tooltip(function() {
        html : true
      });
      $('#danh-sach-the-co .stopPromotion').each(function(){
        $(this).on('click auxclick', function(e){
          e.preventDefault();
          $('#AdSenseModal').attr('data-url', $(this).attr('href')).modal('show');
          $('#adModalCloseBtn').attr('data-original-title', $('#AdSenseModal').attr('data-url'));
          $('#adModalCloseBtn').tooltip();
        });
      });
      $('#danh-sach-the-co > tbody > tr').each(function(index){
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
      $('.room-code, #danh-sach-the-co .btn').each(function(){
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
</script>
<input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ URL::to('/') }}" >
{{-- @include('layout.partials.userPuzzlesWrapper') --}}
{{-- @include('layout.partials.userPuzzles') --}}
@include('layout.partials.players')
@include('layout.partials.boards')
@include('layout.partials.playedBoards')
@endsection