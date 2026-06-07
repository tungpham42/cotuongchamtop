@extends('layout.app')

@section('content')
<div class="container">
    @if ($showAds ?? true)
    <div class="row justify-content-center text-center mb-4">
        <div class="col-12">
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
    @endif
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-search"></i> {{ __("Tìm kiếm kỳ thủ") }}
                    @include('layout.partials.app.tourBtn')
                </div>
                <div class="card-body">
                    @include('layout.partials.app.createRoom')
                    <h2 data-step="2" data-intro="{{ __("Tìm kiếm kỳ thủ theo tên và email") }}"><i class="fas fa-search"></i> {{ __("Tìm kiếm kỳ thủ") }} ({!! app('App\Http\Controllers\UserController')::renderOnlinePlayers() !!})</h2>
                    <form action="{{ localized_url('search') }}" method="GET">
                        @csrf
                        <div class="input-group mb-3" id="search-form">
                            <input data-step="3" data-intro="{{ __("Điền vào từ khóa cần tìm") }}" name="query" type="text" class="form-control form-control-lg" id="keyword" aria-label="{{ __("Bạn cần tìm ai?") }}" placeholder="{{ __("Bạn cần tìm ai?") }}" value="{{ isset($_GET['query']) ? $_GET['query'] : '' }}">
                            <button data-step="4" data-intro="{{ __("Ấn để bắt đầu tìm kiếm") }}" class="btn btn-danger btn-lg" type="submit"><i class="fad fa-search"></i><span> {{ __("Tìm kiếm") }}</span></button>
                            <button data-step="5" data-intro="{{ __("Ấn để quay lại trang mặc định") }}" class="btn btn-dark btn-lg" type="button" onclick="javascript:window.location.href='{{ url('/tim-kiem') }}'"><i class="fad fa-chevron-left"></i><span> {{ __("Quay lại") }}</span></button>
                        </div>
                    </form>
                    <script>
                        $(document).ready(function() {
                            $('input#keyword').focus();
                        });
                    </script>
                    @if (isset($results) && count($results) > 0)
                    <span class="lead">{{ __("Tìm được") }} {{ $results->total() }} {{ __("kỳ thủ") }}</span>
                    <div data-step="6" data-intro="{{ __("Kết quả tìm kiếm") }}" class="table-responsive">
                        <table class="table table-striped table-hover" id="rankingTable">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __("Tên") }}</th>
                                    <th scope="col">Elo</th>
                                    <th scope="col">{{ __("Ngày giờ gia nhập") }}</th>
                                    <th scope="col">{{ __("Lần trực tuyến gần nhất") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{ $results->links('vendor.pagination.match') }}
                                @foreach ($results as $result)
                                <tr data-user="{{ $result->id }}">
                                    <th scope="row" class="name">{!! app('App\Http\Controllers\UserController')::renderPlayerName($result->id) !!}</th>
                                    <td class="elo">{!! app('App\Http\Controllers\UserController')::renderElo($result->id) !!}</td>
                                    <td class="room-time">{{ $result->created_at }}</td>
                                    <td class="room-time">{{ $result->last_seen_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-dark border-dark lead" role="alert">
                        {{ __("Không tìm thấy kỳ thủ nào") }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
{{-- @include('layout.partials.app.fb') --}}
@endsection
