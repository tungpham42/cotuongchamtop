@extends('layout.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Royal Glassmorphism Card -->
            <div class="card shadow-lg mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-archive text-gold"></i> {{ __("Lịch sử thi đấu") }}</h4>
                    @include('layout.partials.app.tourBtn')
                </div>
                <div class="card-body p-0">
                    <div class="p-3 border-bottom" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                        @include('layout.partials.app.createRoom')
                        <h2 data-step="2" data-intro="{{ __("Danh sách các ván đấu đã hoàn tất") }}" class="mt-3 mb-0 h4">
                            <i class="fas fa-archive"></i> {{ __("Lịch sử thi đấu") }} <small class="text-muted">({{ $playedRooms->total() }} {{ __("trận") }}, {!! app('App\Http\Controllers\UserController')::renderOnlinePlayers() !!})</small>
                        </h2>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="results-table">
                            <thead>
                                <tr>
                                    <th class="text-center" scope="col">{{ __("Tên phòng") }}</th>
                                    <th class="text-center" scope="col">{{ __("Chủ phòng") }}</th>
                                    <th class="text-center" scope="col">{{ __("Khách") }}</th>
                                    <th class="text-center" scope="col">{{ __("Kết quả") }}</th>
                                    <th class="text-center" scope="col">{{ __("Lần cuối chơi") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($playedRooms as $room)
                                <tr data-code="{{ $room->code }}" data-fen="{{ $room->fen }}">
                                    <td class="text-center room-code">
                                        <span><a class="animate text-gold" href="{{ url('/phong/') }}/{{ $room->code }}/theo-doi">{{ ((isset($room->name) && $room->name != '') ? $room->name: $room->code) }}</a></span>
                                    </td>
                                    <td class="text-center host-name">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerName($room->host_id) !!}
                                    </td>
                                    <td class="text-center guest-name">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerName($room->guest_id) !!}
                                    </td>
                                    <td class="text-center">
                                        @if ($room->result == '1')
                                            <span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> {{ __("Chủ phòng thắng") }}</span>
                                        @elseif ($room->result == '0')
                                            <span class="badge badge-status badge-offline"><i class="fas fa-handshake"></i> {{ __("Hòa") }}</span>
                                        @elseif ($room->result == '-1')
                                            <span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> {{ __("Khách thắng") }}</span>
                                        @else
                                            <span class="text-muted"><i class="fas fa-hourglass-half"></i> {{ __("Chưa có kết quả") }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center room-time">{{ $room->modified_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($playedRooms->hasPages())
                <div class="card-footer d-flex justify-content-center pt-3 pb-1 border-top" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                    {{ $playedRooms->links('vendor.pagination.match') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
