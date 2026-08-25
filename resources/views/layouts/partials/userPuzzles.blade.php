@php
    $isFiltered = isset($_GET['loai']) && in_array($_GET['loai'], ['van-da-dau', 'van-dau', 'co-the', 'ky-thu']);
    $puzzleCollection = $isFiltered ? $firstUserPuzzles : $userPuzzles;
    $shouldDisplay = $isFiltered || (Request::get('page') <= ceil($userPuzzles->total() / max($userPuzzles->perPage(), 1)));
@endphp

@if($shouldDisplay)
<span style="background-color: transparent" class="d-block w-100 pb-5 mb-5 mt-0" id="the-co"></span>

<style>
    .glass-action-btn {
        background: transparent;
        color: var(--royal-gold-light);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-decoration: none !important;
    }
    .glass-action-btn:hover {
        background: rgba(138, 21, 21, 0.35); /* Translucent red glass hover */
        color: var(--royal-gold) !important;
        box-shadow: inset 0 2px 10px var(--liquid-highlight), 0 0 10px rgba(212, 175, 55, 0.2);
        text-shadow: 0 0 8px rgba(212, 175, 55, 0.8);
    }
    .glass-action-btn i {
        transition: transform 0.3s ease;
    }
    .glass-action-btn:hover i {
        transform: scale(1.15);
    }
</style>

<div style="background-color: transparent" class="container-fluid userPuzzles puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div id="user-puzzles" class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-puzzle-piece" style="color: var(--royal-gold); text-shadow: 0 0 10px var(--royal-gold);"></i> {{ $puzzleCollection->total() }} <a class="text-light animate showPromotion" href="{{ localized_url('puzzle.list') }}">{{ __('thế cờ') }}</a>, <a class="text-light animate showPromotion" href="{{ localized_url('puzzle.setup') }}">{{ __('tạo mới ngay') }}</a></h2>
            {{ $puzzleCollection->links('vendor.pagination.userVi') }}

            @foreach($puzzleCollection as $userPuzzle)
            @php
                $puzzleMd5 = md5($userPuzzle->slug);
            @endphp
            <div data-likes="{{ $userPuzzle->likes_count }}" data-hard="{{ $userPuzzle->hard_count }}" data-unsolved="{{ $userPuzzle->unsolved_count }}" class="puzzle-div col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="royal-grid-card h-100 d-flex flex-column">
                    <div class="royal-board-wrapper" style="cursor: pointer;">
                        <div id="board-{{ $puzzleMd5 }}" style="width: 100%; height: auto;"></div>
                    </div>

                    <div class="p-3 text-center flex-grow-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(to top, var(--glass-bg-dark), transparent);">
                        <h5 class="royal-card-title m-0" style="cursor: pointer; font-size: 1.1rem;" data-toggle="tooltip" data-placement="top" title='{{ __("Thế cờ:") }} "{{ $userPuzzle->name }}"'>
                            {{ $userPuzzle->name }}
                        </h5>
                    </div>

                    <div class="d-flex border-top" style="border-color: var(--glass-border) !important; background: var(--glass-bg-dark); backdrop-filter: var(--glass-blur);">
                        <a class="flex-fill text-center py-2 rounded-0 border-0 glass-action-btn solve-puzzle-btn" href="javascript:solvePuzzle('{{ $userPuzzle->fen }}')" data-toggle="tooltip" title='{{ __("Giải") }} {{ __("thế cờ") }} "{{ $userPuzzle->name }}"'>
                            <i class="fas fa-play" style="color: var(--royal-gold);"></i>
                        </a>
                        <a class="flex-fill text-center py-2 rounded-0 border-0 border-left glass-action-btn puzzle-reaction-btn" style="border-color: rgba(212, 175, 55, 0.1) !important;" href="javascript:void(0);" data-reaction="like" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}" data-toggle="tooltip" title='{{ __("Hay") }}'>
                            <i class="fas fa-thumbs-up" style="color: #4ade80;"></i> <span class="puzzle-reaction-count text-light" id="reaction-like-{{ $puzzleMd5 }}">{{ $userPuzzle->likes_count }}</span>
                        </a>
                        <a class="flex-fill text-center py-2 rounded-0 border-0 border-left glass-action-btn puzzle-reaction-btn" style="border-color: rgba(212, 175, 55, 0.1) !important;" href="javascript:void(0);" data-reaction="hard" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}" data-toggle="tooltip" title='{{ __("Khó") }}'>
                            <i class="fas fa-fire" style="color: var(--royal-red-light);"></i> <span class="puzzle-reaction-count text-light" id="reaction-hard-{{ $puzzleMd5 }}">{{ $userPuzzle->hard_count }}</span>
                        </a>
                        <a class="flex-fill text-center py-2 rounded-0 border-0 border-left glass-action-btn puzzle-reaction-btn" style="border-color: rgba(212, 175, 55, 0.1) !important;" href="javascript:void(0);" data-reaction="unsolved" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}" data-toggle="tooltip" title='{{ __("Chưa giải được") }}'>
                            <i class="fas fa-question-circle" style="color: #3b82f6;"></i> <span class="puzzle-reaction-count text-light" id="reaction-unsolved-{{ $puzzleMd5 }}">{{ $userPuzzle->unsolved_count }}</span>
                        </a>
                    </div>
                </div>
            </div>
            <script>
            var board{{ $puzzleMd5 }} = Xiangqiboard('board-{{ $puzzleMd5 }}', '{{ $userPuzzle->fen }}');
            $('#board-{{ $puzzleMd5 }}').resize();
            $(window).resize(board{{ $puzzleMd5 }}.resize);

            $('#board-{{ $puzzleMd5 }}').parent().add($('#board-{{ $puzzleMd5 }}').closest('.royal-grid-card').find('h5')).on('click auxclick', function(e){
                e.preventDefault();
                window.location.href = '{{ url('/') }}' + '{{ __("/the-co/") }}' + '{{ $userPuzzle->slug }}';
            });
            </script>
            @endforeach

            {{ $puzzleCollection->links('vendor.pagination.userVi') }}
        </div>
    </div>
</div>
@endif

<script>
function solvePuzzle(fenCode) {
    if (!game.validate_fen(fenCode + ' r - - 0 1').valid) {
        bootbox.alert({
            message: '{{ __("Bàn cờ thế không hợp lệ") }}',
            locale: '{{ __("vi") }}',
            centerVertical: true,
            closeButton: false,
            buttons: {
                ok: { className: 'btn-danger pulse-red' }
            }
        });
    } else {
        window.location.href = '{{ url(__("/giai-co-the")) }}/' + fenCode + ' r - - 0 1';
    }
}

function updatePuzzleReactions(slug, md5) {
    $.get('{{ url('/api/puzzles') }}/' + slug + '/reactions')
        .done(function(response) {
            $('#reaction-like-' + md5).text(response.likes ?? 0);
            $('#reaction-hard-' + md5).text(response.hard ?? 0);
            $('#reaction-unsolved-' + md5).text(response.unsolved ?? 0);
        });
}

$('.puzzle-reaction-btn').on('click', function() {
    const $btn = $(this);
    if ($btn.data('loading')) return;

    const slug = $btn.data('slug');
    const md5 = $btn.data('md5');
    const type = $btn.data('reaction');

    $btn.data('loading', true).addClass('disabled');

    $.ajax({
        url: '{{ url('/api/puzzles') }}/' + slug + '/reactions',
        method: 'POST',
        data: { type: type },
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).done(function(response) {
        $('#reaction-like-' + md5).text(response.likes ?? 0);
        $('#reaction-hard-' + md5).text(response.hard ?? 0);
        $('#reaction-unsolved-' + md5).text(response.unsolved ?? 0);
    }).fail(function(xhr) {
        let message = '{{ __("Không thể ghi nhận phản hồi, vui lòng thử lại.") }}';
        if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;

        bootbox.alert({
            message: message,
            locale: '{{ __("vi") }}',
            centerVertical: true,
            closeButton: false,
            size: 'small'
        });
    }).always(function() {
        setTimeout(function() { $btn.data('loading', false).removeClass('disabled'); }, 600);
    });
});
</script>
