@extends('amp.en.layout.mainlayout')
@section('aboveContent')
<div class="container-fluid game px-0">
  <div class="container p-3">
    <h2 class="h1-responsivefooter text-center my-4">Rooms</h2>
    <p class="w-100 text-center my-1">
      <a id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ url('/') }}/room/{{ md5(time()) }}" class="btn btn-success btn-lg"><i class="fad fa-plus-circle"></i> Host a room</a>
    </p>
    <div class="table-responsive">
      <table id="danh-sach-phong" class="table table-bordered table-hover table-striped table-sm">
        <thead class="thead-light">
          <tr>
            <th class="text-center" scope="col">Room code</th>
            <th class="text-center" scope="col">Status</th>
            <th class="text-center no-sort" scope="col" data-sort-method="none">Watch</th>
            <th class="text-center" scope="col">Last played</th>
          </tr>
        </thead>
        <tbody style="background-color: whitesmoke;">
  @for ($i = 0; $i < count($rooms); ++$i)
          <tr>
            <td class="text-center room-code"><a href="{{ URL::to('/') }}/room/{{ $rooms[$i]['code'] }}">{{ $rooms[$i]['code'] }}</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-key text-dark" data-toggle="tooltip" data-placement="bottom" data-original-title="{{ $rooms[$i]['pass'] }}"></i>
              @if ($rooms[$i]['fen'] == 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1')
                <span class="text-right badge badge-warning">New</span>
              @else
                <span class="text-right badge badge-dark text-light">Old</span>
              @endif
            </td>
            <td class="text-center">
              <a target="_blank" class="btn btn-success text-light" href="{{ url('/') }}/room/{{ $rooms[$i]['code'] }}/invited">Join</a>
            </td>
            <td class="text-center" data-sort-method="none">
              <a target="_blank" class="btn btn-danger" href="{{ URL::to('/') }}/room/{{ $rooms[$i]['code'] }}/red">RED</a>
              <a target="_blank" class="btn btn-dark" href="{{ URL::to('/') }}/room/{{ $rooms[$i]['code'] }}/black">BLACK</a>
            </td>
            <td class="text-right" data-order="{{ strtotime($rooms[$i]['modified_at']) }}">{{ date('Y-m-d | g:i a', strtotime($rooms[$i]['modified_at']) + (420*60)) }}</td>
          </tr>
  @endfor
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
@section('belowContent')
<script>
$(document).ready(function () {
  $('#danh-sach-phong').DataTable({
    'language': {
      'url': '{{ URL::to('/') }}/js/TableEn.json'
    },
    'order': [[ 3, 'desc' ]]
  });
  $('.dataTables_length').addClass('bs-select');
});
</script>
@include('en.layout.partials.rules')
@endsection