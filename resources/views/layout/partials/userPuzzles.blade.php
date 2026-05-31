@if(isset($_GET['loai']) && ($_GET['loai'] == 'van-da-dau' || $_GET['loai'] == 'van-dau' || $_GET['loai'] == 'co-the' || $_GET['loai'] == 'ky-thu'))
<span style="background-color: transparent" class="d-block w-100 pb-5 mb-5 mt-0" id="the-co"></span>
<div style="background-color: transparent" class="container-fluid userPuzzles puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div id="user-puzzles" class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-puzzle-piece"></i> {{ $firstUserPuzzles->total() }} <a class="text-light animate-light showPromotion" href="{{ url('/') }}/tat-ca-the-co">{{ __('thế cờ') }}</a>, <a class="text-light animate-light showPromotion" href="{{ url(__('/co-the')) }}">{{ __('tạo mới ngay') }}</a></h2>
            {{ $firstUserPuzzles->links('vendor.pagination.userVi') }}
            @foreach($firstUserPuzzles as $userPuzzle)
            @php
                $puzzleMd5 = md5($userPuzzle->slug);
            @endphp
            <div data-likes="{{ $userPuzzle->likes_count }}" data-hard="{{ $userPuzzle->hard_count }}" data-unsolved="{{ $userPuzzle->unsolved_count }}" class="puzzle-div col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div id="board-{{ md5($userPuzzle->slug) }}" class="card shadow-lg rounded border-dark" style="width: 100%; height: auto; cursor: pointer;background-color: transparent;"></div>
                <div class="bg-dark p-2">
                    <h5 class="mx-auto text-light m-0 font-weight-light text-center" style="cursor: pointer;" data-toggle="tooltip" data-placement="top" title='{{ __("Thế cờ") }} "{{ $userPuzzle->name }}"'>{{ $userPuzzle->name }}</h5>
                </div>
                <div class="row mx-0">
                    <a class="py-2 col-3 btn btn-dark btn-sm text-light solve-puzzle-btn" href="javascript:solvePuzzle('{{ $userPuzzle->fen }}')" data-toggle="tooltip" data-placement="top" title='{{ __("Giải") }} {{ __("thế cờ") }} "{{ $userPuzzle->name }}"'><i class="fad fa-mouse"></i></a>
                    <a class="py-2 col-3 btn btn-dark btn-sm text-light puzzle-reaction-btn" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title='{{ __("Đánh giá") }} "{{ $userPuzzle->name }}" {{ __("là hay") }}' data-reaction="like" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}"><i class="fas fa-thumbs-up"></i> <span class="puzzle-reaction-count" id="reaction-like-{{ $puzzleMd5 }}">{{ $userPuzzle->likes_count }}</span></a>
                    <a class="py-2 col-3 btn btn-dark btn-sm text-light puzzle-reaction-btn" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title='{{ __("Đánh giá") }} "{{ $userPuzzle->name }}" {{ __("là khó") }}' data-reaction="hard" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}"><i class="fas fa-heart"></i> <span class="puzzle-reaction-count" id="reaction-hard-{{ $puzzleMd5 }}">{{ $userPuzzle->hard_count }}</span></a>
                    <a class="py-2 col-3 btn btn-dark btn-sm text-light puzzle-reaction-btn" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title='{{ __("Tôi chưa giải được") }} "{{ $userPuzzle->name }}"' data-reaction="unsolved" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}"><i class="fas fa-question-circle"></i> <span class="puzzle-reaction-count" id="reaction-unsolved-{{ $puzzleMd5 }}">{{ $userPuzzle->unsolved_count }}</span></a>
                </div>
            </div>
            <style>
            #board-{{ md5($userPuzzle->slug) }} {
                background-color: #e1bd86 !important;
            }
            #board-{{ md5($userPuzzle->slug) }} .xiangqiboard-8ddcb {
                margin: auto !important;
                background-color: #e1bd86 !important;
            }
            #board-{{ md5($userPuzzle->slug) }} .xiangqiboard-8ddcb .board-1ef78 {
                box-shadow: none !important;
            }
            </style>
            <script>
            var board{{ md5($userPuzzle->slug) }} = Xiangqiboard('board-{{ md5($userPuzzle->slug) }}', '{{ $userPuzzle->fen }}');
            $('#board-{{ md5($userPuzzle->slug) }}').resize();
            $(window).resize(board{{ md5($userPuzzle->slug) }}.resize);
            $('#board-{{ md5($userPuzzle->slug) }}').on('click auxclick', function(e){
                e.preventDefault();
                window.location.href = '{{ url('/') }}' + '{{ __("/the-co/") }}' + '{{ $userPuzzle->slug }}';
            });
            $('#board-{{ md5($userPuzzle->slug) }} + div h5').on('click auxclick', function(e){
                e.preventDefault();
                window.location.href = '{{ url('/') }}' + '{{ __("/the-co/") }}' + '{{ $userPuzzle->slug }}';
            });
            </script>
            @endforeach
            {{ $firstUserPuzzles->links('vendor.pagination.userVi') }}
        </div>
    </div>
</div>
@else
    @if ( Request::get('page') <= ceil($userPuzzles->total() / $userPuzzles->perPage()) )
    <span style="background-color: transparent" class="d-block w-100 pb-5 mb-5 mt-0" id="the-co"></span>
    <div style="background-color: transparent" class="container-fluid userPuzzles puzzles px-0">
        <div class="container mx-auto px-3 pt-0">
            <div id="user-puzzles" class="row my-0">
                <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-puzzle-piece"></i> {{ $userPuzzles->total() }} <a class="text-light animate-light showPromotion" href="{{ url('/') }}/tat-ca-the-co">{{ __('thế cờ') }}</a>, <a class="text-light animate-light showPromotion" href="{{ url(__('/co-the')) }}">{{ __('tạo mới ngay') }}</a></h2>
                {{ $userPuzzles->links('vendor.pagination.userVi') }}
                @foreach($userPuzzles as $userPuzzle)
                @php
                    $puzzleMd5 = md5($userPuzzle->slug);
                @endphp
                <div data-likes="{{ $userPuzzle->likes_count }}" data-hard="{{ $userPuzzle->hard_count }}" data-unsolved="{{ $userPuzzle->unsolved_count }}" class="puzzle-div col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div id="board-{{ md5($userPuzzle->slug) }}" class="card shadow-lg rounded border-dark" style="width: 100%; height: auto; cursor: pointer;background-color: transparent;"></div>
                    <div class="bg-dark p-2">
                        <h5 class="mx-auto text-light m-0 font-weight-light text-center" style="cursor: pointer;" data-toggle="tooltip" data-placement="top" title='{{ __("Thế cờ") }} "{{ $userPuzzle->name }}"'>{{ $userPuzzle->name }}</h5>
                    </div>
                    <div class="row mx-0">
                        <a class="py-2 col-3 btn btn-dark btn-sm text-light solve-puzzle-btn" href="javascript:solvePuzzle('{{ $userPuzzle->fen }}')" data-toggle="tooltip" data-placement="top" title='{{ __("Giải") }} {{ __("thế cờ") }} "{{ $userPuzzle->name }}"'><i class="fad fa-mouse"></i></a>
                        <a class="py-2 col-3 btn btn-dark btn-sm text-light puzzle-reaction-btn" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title='{{ __("Đánh giá") }} "{{ $userPuzzle->name }}" {{ __("là hay") }}' data-reaction="like" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}"><i class="fas fa-thumbs-up"></i> <span class="puzzle-reaction-count" id="reaction-like-{{ $puzzleMd5 }}">{{ $userPuzzle->likes_count }}</span></a>
                        <a class="py-2 col-3 btn btn-dark btn-sm text-light puzzle-reaction-btn" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title='{{ __("Đánh giá") }} "{{ $userPuzzle->name }}" {{ __("là khó") }}' data-reaction="hard" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}"><i class="fas fa-heart"></i> <span class="puzzle-reaction-count" id="reaction-hard-{{ $puzzleMd5 }}">{{ $userPuzzle->hard_count }}</span></a>
                        <a class="py-2 col-3 btn btn-dark btn-sm text-light puzzle-reaction-btn" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title='{{ __("Tôi chưa giải được") }} "{{ $userPuzzle->name }}"' data-reaction="unsolved" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}"><i class="fas fa-question-circle"></i> <span class="puzzle-reaction-count" id="reaction-unsolved-{{ $puzzleMd5 }}">{{ $userPuzzle->unsolved_count }}</span></a>
                    </div>
                </div>
                <style>
                #board-{{ md5($userPuzzle->slug) }} {
                    background-color: #e1bd86 !important;
                }
                #board-{{ md5($userPuzzle->slug) }} .xiangqiboard-8ddcb {
                    margin: auto !important;
                    background-color: #e1bd86 !important;
                }
                #board-{{ md5($userPuzzle->slug) }} .xiangqiboard-8ddcb .board-1ef78 {
                    box-shadow: none !important;
                }
                </style>
                <script>
                var board{{ md5($userPuzzle->slug) }} = Xiangqiboard('board-{{ md5($userPuzzle->slug) }}', '{{ $userPuzzle->fen }}');
                $('#board-{{ md5($userPuzzle->slug) }}').resize();
                $(window).resize(board{{ md5($userPuzzle->slug) }}.resize);
                $('#board-{{ md5($userPuzzle->slug) }}').on('click auxclick', function(e){
                    e.preventDefault();
                    window.location.href = '{{ url('/') }}' + '{{ __("/the-co/") }}' + '{{ $userPuzzle->slug }}';
                });
                $('#board-{{ md5($userPuzzle->slug) }} + div h5').on('click auxclick', function(e){
                    e.preventDefault();
                    window.location.href = '{{ url('/') }}' + '{{ __("/the-co/") }}' + '{{ $userPuzzle->slug }}';
                });
                </script>
                @endforeach
                {{ $userPuzzles->links('vendor.pagination.userVi') }}
            </div>
        </div>
    </div>
    @endif
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
            ok: {
            className: 'btn-danger pulse-red'
            }
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
    if ($btn.data('loading')) {
        return;
    }
    const slug = $btn.data('slug');
    const md5 = $btn.data('md5');
    const type = $btn.data('reaction');

    $btn.data('loading', true).addClass('disabled');

    $.ajax({
        url: '{{ url('/api/puzzles') }}/' + slug + '/reactions',
        method: 'POST',
        data: {
            type: type
        },
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }).done(function(response) {
        $('#reaction-like-' + md5).text(response.likes ?? 0);
        $('#reaction-hard-' + md5).text(response.hard ?? 0);
        $('#reaction-unsolved-' + md5).text(response.unsolved ?? 0);
    }).fail(function(xhr) {
        let message = '{{ __("Không thể ghi nhận phản hồi, vui lòng thử lại.") }}';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        bootbox.alert({
            message: message,
            locale: '{{ __("vi") }}',
            centerVertical: true,
            closeButton: false,
            size: 'small'
        });
    }).always(function() {
        setTimeout(function() {
            $btn.data('loading', false).removeClass('disabled');
        }, 600);
    });
});
</script>
