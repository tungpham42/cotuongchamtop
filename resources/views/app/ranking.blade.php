@extends('layout.app')

@section('content')
<div class="container">
    <div class="row justify-content-center text-center mb-4">
        <div class="col-12">
            <!-- CO_res -->
            <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-3585118770961536"
            data-ad-slot="7831723879"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
            <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-star"></i> Bảng xếp hạng
                    @include('layout.partials.app.tourBtn')
                </div>
                <div class="card-body">
                    @include('layout.partials.app.createRoom')
                    <span style="background-color: #ffffff; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="result-board"></span>
                    <h2 data-step="2" data-intro="Danh sách xếp hạng đầy đủ" class="mt-3"><i class="fas fa-star"></i> Bảng xếp hạng của {{ $users->total() }} kỳ thủ ({!! app('App\Http\Controllers\UserController')::renderOnlinePlayers() !!})</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="rankingTable">
                            <thead>
                                <tr>
                                    <th scope="col">Hạng</th>
                                    <th scope="col">Tên</th>
                                    <th scope="col">Elo</th>
                                    <th scope="col">Ngày giờ gia nhập</th>
                                    <th scope="col">Lần trực tuyến gần nhất</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{ $users->links('vendor.pagination.match') }}
                                @foreach($users as $user)
                                <tr data-user="{{ $user->id }}">
                                    <th scope="row">{!! app('App\Http\Controllers\UserController')::renderUserRank($user->id) !!}</th>
                                    <td class="name">{!! app('App\Http\Controllers\UserController')::renderPlayerName($user->id) !!}</td>
                                    <td class="elo">{!! app('App\Http\Controllers\UserController')::renderElo($user->id) !!}</td>
                                    <td class="room-time">{{ $user->created_at }}</td>
                                    <td class="room-time">{{ $user->last_seen_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layout.partials.app.fb')
@endsection
