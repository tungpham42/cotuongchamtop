@php
    $locale = app()->getLocale();
    $locale = in_array($locale, ['en', 'ja', 'ko', 'zh'], true) ? $locale : 'en';

    $copy = [
        'en' => [
            'item' => 'puzzles',
            'list' => 'all puzzles',
            'create' => 'create a new one',
            'solve' => 'Solve puzzle',
            'like' => 'Mark as useful',
            'hard' => 'Mark as hard',
            'unsolved' => 'I could not solve it',
            'setupUrl' => '/puzzle',
            'solveUrl' => '/solve-puzzle',
            'pagination' => 'vendor.pagination.en',
        ],
        'ja' => [
            'item' => 'パズル',
            'list' => 'すべてのパズル',
            'create' => '新しく作成',
            'solve' => 'パズルを解く',
            'like' => '良いパズルとして評価',
            'hard' => '難しいとして評価',
            'unsolved' => '解けませんでした',
            'setupUrl' => '/pazuru',
            'solveUrl' => '/pazuru-o-toku',
            'pagination' => 'vendor.pagination.ja',
        ],
        'ko' => [
            'item' => '퍼즐',
            'list' => '모든 퍼즐',
            'create' => '새로 만들기',
            'solve' => '퍼즐 풀기',
            'like' => '좋은 퍼즐로 평가',
            'hard' => '어려움으로 평가',
            'unsolved' => '풀지 못했습니다',
            'setupUrl' => '/peojeul',
            'solveUrl' => '/pojeureul-pulda',
            'pagination' => 'vendor.pagination.ko',
        ],
        'zh' => [
            'item' => '谜题',
            'list' => '所有谜题',
            'create' => '创建新的',
            'solve' => '解决难题',
            'like' => '标记为有用',
            'hard' => '标记为困难',
            'unsolved' => '我还没有解出',
            'setupUrl' => '/mi',
            'solveUrl' => '/jiejuenanti',
            'pagination' => 'vendor.pagination.zh',
        ],
    ];

    $text = $copy[$locale];
    $puzzlesPage = $userPuzzles ?? App\Http\Controllers\PuzzleController::getUserPuzzles();
@endphp

@if (Request::get('page') <= ceil($puzzlesPage->total() / $puzzlesPage->perPage()))
<span style="background-color: transparent" class="d-block w-100 pb-5 mb-5 mt-0" id="puzzles"></span>
<div style="background-color: transparent" class="container-fluid userPuzzles puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div id="user-puzzles" class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4">
                <i class="fas fa-puzzle-piece"></i>
                {{ $puzzlesPage->total() }}
                <a class="text-light animate-light showPromotion" href="{{ url('/tat-ca-the-co') }}">{{ $text['list'] }}</a>,
                <a class="text-light animate-light showPromotion" href="{{ url($text['setupUrl']) }}">{{ $text['create'] }}</a>
            </h2>
            {{ $puzzlesPage->links($text['pagination']) }}

            @foreach($puzzlesPage as $userPuzzle)
            @php
                $puzzleMd5 = md5($locale.$userPuzzle->slug);
                $solveHref = url($text['solveUrl']).'/'.$userPuzzle->fen.' r - - 0 1';
            @endphp
            <div data-likes="{{ $userPuzzle->likes_count }}" data-hard="{{ $userPuzzle->hard_count }}" data-unsolved="{{ $userPuzzle->unsolved_count }}" class="puzzle-div col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div id="board-{{ $puzzleMd5 }}" class="card shadow-lg rounded border-dark" style="width: 100%; height: auto; cursor: pointer;background-color: transparent;"></div>
                <div class="bg-dark p-2">
                    <h5 class="mx-auto text-light m-0 font-weight-light text-center" style="cursor: pointer;" data-toggle="tooltip" data-placement="top" title='{{ $userPuzzle->name }}'>{{ $userPuzzle->name }}</h5>
                </div>
                <div class="row mx-0">
                    <a class="py-2 col-3 btn btn-dark btn-sm text-light solve-puzzle-btn" href="{{ $solveHref }}" data-toggle="tooltip" data-placement="top" title='{{ $text['solve'] }} "{{ $userPuzzle->name }}"'><i class="fad fa-mouse"></i></a>
                    <a class="py-2 col-3 btn btn-dark btn-sm text-light puzzle-reaction-btn" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title='{{ $text['like'] }} "{{ $userPuzzle->name }}"' data-reaction="like" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}"><i class="fas fa-thumbs-up"></i> <span class="puzzle-reaction-count" id="reaction-like-{{ $puzzleMd5 }}">{{ $userPuzzle->likes_count }}</span></a>
                    <a class="py-2 col-3 btn btn-dark btn-sm text-light puzzle-reaction-btn" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title='{{ $text['hard'] }} "{{ $userPuzzle->name }}"' data-reaction="hard" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}"><i class="fas fa-heart"></i> <span class="puzzle-reaction-count" id="reaction-hard-{{ $puzzleMd5 }}">{{ $userPuzzle->hard_count }}</span></a>
                    <a class="py-2 col-3 btn btn-dark btn-sm text-light puzzle-reaction-btn" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title='{{ $text['unsolved'] }} "{{ $userPuzzle->name }}"' data-reaction="unsolved" data-slug="{{ $userPuzzle->slug }}" data-md5="{{ $puzzleMd5 }}"><i class="fas fa-question-circle"></i> <span class="puzzle-reaction-count" id="reaction-unsolved-{{ $puzzleMd5 }}">{{ $userPuzzle->unsolved_count }}</span></a>
                </div>
            </div>
            <style>
            #board-{{ $puzzleMd5 }},
            #board-{{ $puzzleMd5 }} .xiangqiboard-8ddcb {
                background-color: #e1bd86 !important;
            }
            #board-{{ $puzzleMd5 }} .xiangqiboard-8ddcb {
                margin: auto !important;
            }
            #board-{{ $puzzleMd5 }} .xiangqiboard-8ddcb .board-1ef78 {
                box-shadow: none !important;
            }
            </style>
            <script>
            var board{{ $puzzleMd5 }} = Xiangqiboard('board-{{ $puzzleMd5 }}', '{{ $userPuzzle->fen }}');
            $('#board-{{ $puzzleMd5 }}').resize();
            $(window).resize(board{{ $puzzleMd5 }}.resize);
            $('#board-{{ $puzzleMd5 }}, #board-{{ $puzzleMd5 }} + div h5').on('click auxclick', function(e){
                e.preventDefault();
                window.location.href = '{{ url('/') }}' + '{{ __("/the-co/") }}' + '{{ $userPuzzle->slug }}';
            });
            </script>
            @endforeach

            {{ $puzzlesPage->links($text['pagination']) }}
        </div>
    </div>
</div>

<script>
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
    }).always(function() {
        setTimeout(function() {
            $btn.data('loading', false).removeClass('disabled');
        }, 600);
    });
});
</script>
@endif
