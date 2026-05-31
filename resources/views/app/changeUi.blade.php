change.ui@extends('layout.app')

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
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <div>
                        <i class="fas fa-palette mr-2"></i> {{ __("Thay đổi giao diện") }}
                    </div>
                    <div class="ml-auto">
                        @include('layout.partials.app.tourBtn')
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-4 mb-md-0 border-right">
                            <form method="POST" action="{{ route('change.ui') }}">
                                @csrf
                                <input type="hidden" value="{{ auth()->user()->id ?? '' }}" name="current_id">

                                <div class="form-group">
                                    <label for="board_theme" class="font-weight-bold"><i class="fas fa-chess-board text-muted"></i> {{ __("Bàn cờ") }}</label>
                                    <select data-step="1" data-intro="{{ __("Chọn giao diện bàn cờ") }}" class="form-control form-control-lg" name="board_theme" id="board_theme">
                                        <option value="xiangqi-board" @if((auth()->user()->board_theme ?? '') === 'xiangqi-board') selected @endif>{{ __("Bàn cờ mặc định") }}</option>
                                        <option value="ban-co-go" @if((auth()->user()->board_theme ?? '') === 'ban-co-go') selected @endif>{{ __("Gỗ nhạt") }}</option>
                                        <option value="wood-board" @if((auth()->user()->board_theme ?? '') === 'wood-board') selected @endif>{{ __("Gỗ đậm") }}</option>
                                        <option value="ban-co" @if((auth()->user()->board_theme ?? '') === 'ban-co') selected @endif>{{ __("Vàng chói") }}</option>
                                        <option value="banco" @if((auth()->user()->board_theme ?? '') === 'banco') selected @endif>{{ __("Sáng") }}</option>
                                        <option value="chess-board" @if((auth()->user()->board_theme ?? '') === 'chess-board') selected @endif>{{ __("Cam nhạt") }}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="pieces_theme" class="font-weight-bold"><i class="fas fa-chess-knight text-muted"></i> {{ __("Quân cờ") }}</label>
                                    <select data-step="2" data-intro="{{ __("Chọn giao diện quân cờ") }}" class="form-control form-control-lg" name="pieces_theme" id="pieces_theme">
                                        <option value="wiki" @if((auth()->user()->pieces_theme ?? '') === 'wiki') selected @endif>{{ __("Quân cờ mặc định") }}</option>
                                        <option value="tung" @if((auth()->user()->pieces_theme ?? '') === 'tung') selected @endif>{{ __("Đặc biệt") }}</option>
                                        <option value="do-den" @if((auth()->user()->pieces_theme ?? '') === 'do-den') selected @endif>{{ __("Đỏ đen") }}</option>
                                        <option value="graphic" @if((auth()->user()->pieces_theme ?? '') === 'graphic') selected @endif>{{ __("Phương Tây") }}</option>
                                        <option value="co" @if((auth()->user()->pieces_theme ?? '') === 'co') selected @endif>{{ __("Cam") }}</option>
                                        <option value="wikimedia" @if((auth()->user()->pieces_theme ?? '') === 'wikimedia') selected @endif>{{ __("Vàng đậm") }}</option>
                                        <option value="quan" @if((auth()->user()->pieces_theme ?? '') === 'quan') selected @endif>{{ __("Sáng") }}</option>
                                        <option value="traditional" @if((auth()->user()->pieces_theme ?? '') === 'traditional') selected @endif>{{ __("Truyền thống") }}</option>
                                    </select>
                                </div>

                                <button data-step="3" data-intro="{{ __("Ấn vào đây để đổi giao diện") }}" type="submit" class="btn btn-lg btn-danger mt-3 w-100">
                                    <i class="fad fa-palette"></i> {{ __("Đổi giao diện") }}
                                </button>
                            </form>
                        </div>

                        <div class="col-md-6 d-flex flex-column align-items-center justify-content-center bg-light rounded p-4">
                            <h6 class="mb-3 font-weight-bold text-secondary">{{ __("Xem trước giao diện") }}</h6>

                            <div id="ui-preview-board" class="shadow-sm" style="width: 100%; max-width: 280px; aspect-ratio: 9/10; position: relative; background-size: 100% 100%; background-repeat: no-repeat; background-position: center; transition: background-image 0.3s ease; border-radius: 4px;">

                                <img id="ui-preview-piece-red" alt="Tướng Đỏ" style="position: absolute; bottom: 5%; left: 45%; width: 11%; transform: translateX(-50%); opacity: 1; transition: opacity 0.2s ease; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.4));">
                                <img id="ui-preview-piece-black" alt="Tướng Đen" style="position: absolute; top: 5%; left: 45%; width: 11%; transform: translateX(-50%); opacity: 1; transition: opacity 0.2s ease; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.4));">

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // FIXED: rtrim prevents issues where url('/') sometimes adds a trailing slash, resulting in double slashes (//) in the final URL.
        const baseUrl = "{{ rtrim(url('/'), '/') }}";

        // DOM Elements
        const boardSelect = document.getElementById('board_theme');
        const pieceSelect = document.getElementById('pieces_theme');
        const previewBoard = document.getElementById('ui-preview-board');
        const previewPieceRed = document.getElementById('ui-preview-piece-red');
        const previewPieceBlack = document.getElementById('ui-preview-piece-black');

        function updatePreview() {
            const selectedBoard = boardSelect.value;
            const selectedPiece = pieceSelect.value;

            // Reset opacity to 1 immediately to bypass the caching onload bug
            previewPieceRed.style.opacity = 1;
            previewPieceBlack.style.opacity = 1;

            // Update Board Background
            previewBoard.style.backgroundImage = `url('${baseUrl}/img/xiangqiboards/${selectedBoard}.svg')`;

            // Update Pieces
            previewPieceRed.src = `${baseUrl}/img/xiangqipieces/${selectedPiece}/rK.svg`;
            previewPieceBlack.src = `${baseUrl}/img/xiangqipieces/${selectedPiece}/bK.svg`;

            // Only hide them if they actually throw an error (e.g., file doesn't exist)
            previewPieceRed.onerror = () => previewPieceRed.style.opacity = 0;
            previewPieceBlack.onerror = () => previewPieceBlack.style.opacity = 0;
        }

        // Listen for user changes
        boardSelect.addEventListener('change', updatePreview);
        pieceSelect.addEventListener('change', updatePreview);

        // Initialize preview on page load
        updatePreview();
    });
</script>
@endpush
@endsection
