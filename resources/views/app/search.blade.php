@extends('layout.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Royal Glassmorphism Card -->
            <div class="card shadow-lg mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-search text-gold"></i> {{ __("Tìm kiếm kỳ thủ") }}</h4>
                    @include('layout.partials.app.tourBtn')
                </div>
                <div class="card-body p-0">
                    <div class="p-4 border-bottom" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                        @include('layout.partials.app.createRoom')

                        <h2 data-step="2" data-intro="{{ __("Tìm kiếm kỳ thủ theo tên và email") }}" class="mt-4 mb-3 h4">
                            <i class="fas fa-search text-gold"></i> {{ __("Tìm kiếm kỳ thủ") }} <small class="text-muted">({!! app('App\Http\Controllers\UserController')::renderOnlinePlayers() !!})</small>
                        </h2>

                        <!-- Premium Royal Search Bar -->
                        <form action="{{ localized_url('search') }}" method="GET" class="mt-3 mb-2">
                            <div class="input-group shadow-lg" id="search-form" style="border-radius: 6px; overflow: hidden; border: 2px solid var(--royal-gold); box-shadow: 0 0 15px rgba(212, 175, 55, 0.3) !important;">
                                <input data-step="3" data-intro="{{ __("Điền vào từ khóa cần tìm") }}"
                                       name="query" type="text" class="form-control form-control-lg border-0"
                                       style="background: rgba(11, 12, 16, 0.85); color: var(--royal-wood);"
                                       id="keyword" aria-label="{{ __("Bạn cần tìm ai?") }}" placeholder="{{ __("Bạn cần tìm ai?") }}"
                                       value="{{ isset($_GET['query']) ? $_GET['query'] : '' }}">
                                <div class="input-group-append">
                                    <button data-step="4" data-intro="{{ __("Ấn để bắt đầu tìm kiếm") }}" class="btn btn-danger btn-lg pulse-red" style="border-radius: 0;" type="submit">
                                        <i class="fad fa-search"></i><span> {{ __("Tìm kiếm") }}</span>
                                    </button>
                                    <button data-step="5" data-intro="{{ __("Ấn để quay lại trang mặc định") }}" class="btn btn-dark btn-lg border-left border-warning" style="border-radius: 0;" type="button" onclick="javascript:window.location.href='{{ url('/tim-kiem') }}'">
                                        <i class="fad fa-chevron-left"></i><span> {{ __("Quay lại") }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <script>
                            $(document).ready(function() {
                                $('input#keyword').focus();
                            });
                        </script>
                    </div>

                    @if (isset($results) && count($results) > 0)
                    <div class="p-3">
                        <span class="badge badge-status badge-online p-2 mb-3" style="font-size: 0.9rem;">
                            <i class="fas fa-check-circle"></i> {{ __("Tìm được") }} {{ $results->total() }} {{ __("kỳ thủ") }}
                        </span>
                    </div>

                    <div data-step="6" data-intro="{{ __("Kết quả tìm kiếm") }}" class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="rankingTable">
                            <thead>
                                <tr>
                                    <th class="text-center" scope="col">{{ __("Tên") }}</th>
                                    <th class="text-center" scope="col">Elo</th>
                                    <th class="text-center" scope="col">{{ __("Ngày giờ gia nhập") }}</th>
                                    <th class="text-center" scope="col">{{ __("Lần trực tuyến gần nhất") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results as $result)
                                <tr data-user="{{ $result->id }}">
                                    <td class="text-center name font-weight-bold">{!! app('App\Http\Controllers\UserController')::renderPlayerName($result->id) !!}</td>
                                    <td class="text-center elo text-gold">{!! app('App\Http\Controllers\UserController')::renderElo($result->id) !!}</td>
                                    <td class="text-center room-time">{{ $result->created_at }}</td>
                                    <td class="text-center room-time">{{ $result->last_seen_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-4">
                        <div class="alert alert-dark border-warning lead text-center mb-0" role="alert" style="background: rgba(28, 17, 10, 0.85); color: var(--royal-gold-light);">
                            <i class="fad fa-ghost text-gold fa-2x mb-2"></i><br>
                            {{ __("Không tìm thấy kỳ thủ nào") }}
                        </div>
                    </div>
                    @endif
                </div>

                @if (isset($results) && count($results) > 0 && $results->hasPages())
                <div class="card-footer d-flex justify-content-center pt-3 pb-1 border-top" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                    {{ $results->links('vendor.pagination.match') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
