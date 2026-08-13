@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Royal Glassmorphism Card -->
            <div class="card shadow-lg mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-star text-gold"></i> {{ __("Bảng xếp hạng") }}</h4>
                    @include('layouts.partials.app.tourBtn')
                </div>
                <div class="card-body p-0">
                    <div class="p-3 border-bottom" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                        @include('layouts.partials.app.createRoom')
                        <h2 data-step="2" data-intro="{{ __("Danh sách xếp hạng đầy đủ") }}" class="mt-4 mb-0 h4">
                            <i class="fas fa-star text-gold"></i> {{ __("Bảng xếp hạng của") }} {{ $users->total() }} {{ __("kỳ thủ") }} <small class="text-muted">({!! $userPresenter->renderOnlinePlayersCount() !!})</small>
                        </h2>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="rankingTable">
                            <thead>
                                <tr>
                                    <th class="text-center" scope="col">{{ __("Hạng") }}</th>
                                    <th class="text-center" scope="col">{{ __("Tên") }}</th>
                                    <th class="text-center" scope="col">Elo</th>
                                    <th class="text-center" scope="col">{{ __("Ngày giờ gia nhập") }}</th>
                                    <th class="text-center" scope="col">{{ __("Lần trực tuyến gần nhất") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr data-user="{{ $user->id }}">
                                    <td class="text-center font-weight-bold">
                                        <span class="badge badge-status" style="background: linear-gradient(to bottom, #d4af37, #b89020); color: #0b0c10; box-shadow: 0 0 5px rgba(212, 175, 55, 0.6);"><i class="fas fa-trophy"></i> {!! $userPresenter->renderUserRank($user->id) !!}</span>
                                    </td>
                                    <td class="text-center name">{!! $userPresenter->renderPlayerName($user->id) !!}</td>
                                    <td class="text-center elo text-gold font-weight-bold" style="font-size: 1.1em;">{!! $userPresenter->renderElo($user->id) !!}</td>
                                    <td class="text-center room-time">{{ $user->created_at }}</td>
                                    <td class="text-center room-time">{{ $user->last_seen_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($users->hasPages())
                <div class="card-footer d-flex justify-content-center pt-3 pb-1 border-top" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                    {{ $users->links('vendor.pagination.match') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
