@extends('ja.layout.mainlayout')
@section('aboveContent')
<div class="container-fluid game px-0">
  <div class="container p-3">
    <div class="row">
      @include('ja.layout.partials.findMatch')
    </div>
    <h2 class="h1-responsivefooter text-center my-4">部屋一覧</h2>
    <div class="dropdown mx-auto text-center mb-3">
      <button data-step="1" data-intro="他のプレイヤーと競争するにはここをクリックしてください" class="btn btn-danger btn-lg dropdown-toggle pulse-red" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span data-toggle="tooltip" data-placement="top" title="部屋で誰かと遊ぶ"><i class="fad fa-gamepad-alt"></i> オンラインでプレイ</span>
      </button>
      <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ URL::to('/') }}/rumu/{{ md5(time()) }}">
        <a data-toggle="tooltip" data-placement="bottom" title="パスワードで遊ぶ" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> 民間</a>
        @if ($randomRoom != null)
        <a data-toggle="tooltip" data-placement="bottom" title="ランダムな公開ルームでプレイ" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ URL::to('/') }}/rumu/{{ $randomRoom['code'] }}/randamu"><i class="fas fa-random text-dark"></i> ランダム</a>
        @endif
      </div>
      @include('common.tourBtn')
    </div>
    <div data-step="2" data-intro="すべての試合のリスト" class="table-responsive">
      <table id="danh-sach-phong" class="table table-bordered table-hover table-striped table-sm">
        <thead class="thead-light">
          <tr>
            <th class="text-center" scope="col">ルーム名</th>
            <th class="text-center" scope="col">プレイヤーの番</th>
            <th class="text-center" scope="col">結果</th>
            <th class="text-center" scope="col">行動</th>
            <th class="text-center" scope="col">最後にプレイした</th>
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
<div class="text-center mx-auto" style="width: fit-content;" data-step="3" data-intro="このページをモバイルで開いてください">
@include('common.qrCode')
</div>
<div class="modal fade" id="HoveredBoardModal" tabindex="-1" role="dialog" aria-label="HoveredBoard" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-url="">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 320px; margin: auto;">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title"><i class="far fa-eye"></i> 「<span></span>」のプレビュー</h5>
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
  console.log('List URL: ' + '{{ route('roomsJa.list') }}');
  var table = $('#danh-sach-phong').DataTable({
    processing: true,
    serverSide: true,
    ordering: true,
    searching: true,
    ajax: {
      url: "{{ route('roomsJa.list') }}"
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
        orderable: false,
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
      'url': '{{ URL::to('/') }}/js/TableJa.json'
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
      $('#danh-sach-phong .showPromotion').each(function(){
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
</script>
<input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ URL::to('/') }}" >
@include('ja.layout.partials.rules')
@endsection