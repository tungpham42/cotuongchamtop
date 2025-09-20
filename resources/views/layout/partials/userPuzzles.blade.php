@if(isset($_GET['loai']) && ($_GET['loai'] == 'van-da-dau' || $_GET['loai'] == 'van-dau' || $_GET['loai'] == 'co-the' || $_GET['loai'] == 'ky-thu'))
<span style="background-color: transparent" class="d-block w-100 pb-5 mb-5 mt-0" id="the-co"></span>
<div style="background-color: transparent" class="container-fluid userPuzzles puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div id="user-puzzles" class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-puzzle-piece"></i> {{ $firstUserPuzzles->total() }} <a class="text-light animate-light showPromotion" href="{{ url('/') }}/tat-ca-the-co">thế cờ</a>, <a class="text-light animate-light showPromotion" href="{{ url('/') }}/co-the">tạo mới ngay</a></h2>
            {{ $firstUserPuzzles->links('vendor.pagination.userVi') }}
            @foreach($firstUserPuzzles as $userPuzzle)
            <div data-rating="{{ $userPuzzle->rating }}" class="puzzle-div col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div id="board-{{ md5($userPuzzle->slug) }}" class="card shadow-lg rounded border-dark" style="width: 100%; height: auto; cursor: pointer;background-color: transparent;"></div>
                <div class="bg-dark p-2">
                    <h5 class="mx-auto text-light m-0 font-weight-light text-center" style="cursor: pointer;" data-toggle="tooltip" data-placement="top" title='Thế cờ "{{ $userPuzzle->name }}"'>{{ $userPuzzle->name }}</h5>
                </div>
                <div class="row mx-0">
                    <a class="py-2 col-4 btn btn-dark btn-sm text-light solve-puzzle-btn" href="javascript:solvePuzzle('{{ $userPuzzle->fen }}')" data-toggle="tooltip" data-placement="top" title='Giải thế cờ "{{ $userPuzzle->name }}"'><i class="fad fa-mouse"></i></a>
                    <a id="{{ md5($userPuzzle->slug) }}" class="py-2 col-5 btn btn-dark btn-sm text-light" href="javascript:upvote('{{ $userPuzzle->slug }}', '{{ md5($userPuzzle->slug) }}');" data-toggle="tooltip" data-placement="top" title='Lượt thích của thế cờ "{{ $userPuzzle->name }}"'><i class="fad fa-thumbs-up"></i> <span class="totalRating">{{ $userPuzzle->rating }}</span></a>
                    <a class="py-2 col-3 btn btn-dark btn-sm text-light" href="javascript:downvote('{{ $userPuzzle->slug }}', '{{ md5($userPuzzle->slug) }}');" data-toggle="tooltip" data-placement="top" title='Chê thế cờ "{{ $userPuzzle->name }}"'><i class="fad fa-thumbs-down"></i></a>
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
                // $('#AdSenseModal').attr('data-url', '{{ url('/') }}' + '/the-co/' + '{{ $userPuzzle->slug }}').modal('show');
                window.location.href = '{{ url('/') }}' + '/the-co/' + '{{ $userPuzzle->slug }}';
            });
            $('#board-{{ md5($userPuzzle->slug) }} + div h5').on('click auxclick', function(e){
                e.preventDefault();
                // $('#AdSenseModal').attr('data-url', '{{ url('/') }}' + '/the-co/' + '{{ $userPuzzle->slug }}').modal('show');
                window.location.href = '{{ url('/') }}' + '/the-co/' + '{{ $userPuzzle->slug }}';
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
                <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-puzzle-piece"></i> {{ $userPuzzles->total() }} <a class="text-light animate-light showPromotion" href="{{ url('/') }}/tat-ca-the-co">thế cờ</a>, <a class="text-light animate-light showPromotion" href="{{ url('/') }}/co-the">tạo mới ngay</a></h2>
                {{ $userPuzzles->links('vendor.pagination.userVi') }}
                @foreach($userPuzzles as $userPuzzle)
                <div data-rating="{{ $userPuzzle->rating }}" class="puzzle-div col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div id="board-{{ md5($userPuzzle->slug) }}" class="card shadow-lg rounded border-dark" style="width: 100%; height: auto; cursor: pointer;background-color: transparent;"></div>
                    <div class="bg-dark p-2">
                        <h5 class="mx-auto text-light m-0 font-weight-light text-center" style="cursor: pointer;" data-toggle="tooltip" data-placement="top" title='Thế cờ "{{ $userPuzzle->name }}"'>{{ $userPuzzle->name }}</h5>
                    </div>
                    <div class="row mx-0">
                        <a class="py-2 col-4 btn btn-dark btn-sm text-light solve-puzzle-btn" href="javascript:solvePuzzle('{{ $userPuzzle->fen }}')" data-toggle="tooltip" data-placement="top" title='Giải thế cờ "{{ $userPuzzle->name }}"'><i class="fad fa-mouse"></i></a>
                        <a id="{{ md5($userPuzzle->slug) }}" class="py-2 col-5 btn btn-dark btn-sm text-light" href="javascript:upvote('{{ $userPuzzle->slug }}', '{{ md5($userPuzzle->slug) }}');" data-toggle="tooltip" data-placement="top" title='Lượt thích của thế cờ "{{ $userPuzzle->name }}"'><i class="fad fa-thumbs-up"></i> <span class="totalRating">{{ $userPuzzle->rating }}</span></a>
                        <a class="py-2 col-3 btn btn-dark btn-sm text-light" href="javascript:downvote('{{ $userPuzzle->slug }}', '{{ md5($userPuzzle->slug) }}');" data-toggle="tooltip" data-placement="top" title='Chê thế cờ "{{ $userPuzzle->name }}"'><i class="fad fa-thumbs-down"></i></a>
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
                    // $('#AdSenseModal').attr('data-url', '{{ url('/') }}' + '/the-co/' + '{{ $userPuzzle->slug }}').modal('show');
                    window.location.href = '{{ url('/') }}' + '/the-co/' + '{{ $userPuzzle->slug }}';
                });
                $('#board-{{ md5($userPuzzle->slug) }} + div h5').on('click auxclick', function(e){
                    e.preventDefault();
                    // $('#AdSenseModal').attr('data-url', '{{ url('/') }}' + '/the-co/' + '{{ $userPuzzle->slug }}').modal('show');
                    window.location.href = '{{ url('/') }}' + '/the-co/' + '{{ $userPuzzle->slug }}';
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
        message: "Bàn cờ thế không hợp lệ",
        locale: 'vi',
        centerVertical: true,
        closeButton: false,
        buttons: {
            ok: {
            className: 'btn-danger pulse-red'
            }
        }
        });
    } else {
        // $('#AdSenseModal').attr('data-url', '{{ url('/giai-co-the') }}/' + fenCode + ' r - - 0 1').modal('show');
        window.location.href = '{{ url('/giai-co-the') }}/' + fenCode + ' r - - 0 1';
    }
}
function updatePuzzleRating(slug, md5) {
    $.ajax({
        url: '{{ url('/api') }}/totalRating',
        type: "POST",
        data : {
            'slug': slug
        },
        dataType: 'text'
    }).done(function(rating){
        $('span#totalRating').text(rating);
        $('#board-'+ md5).parent('div').attr('data-rating', rating);
        $('#'+ md5 +' span.totalRating').text(rating);
    });
}
function upvote(slug, md5) {
    $.ajax({
        url: '{{ url('/api') }}/upvote',
        type: "POST",
        data : {
            'slug': slug
        },
        dataType: 'text'
    }).done(function(){
        updatePuzzleRating(slug, md5);
    });
}
function downvote(slug, md5) {
    $.ajax({
        url: '{{ url('/api') }}/downvote',
        type: "POST",
        data : {
            'slug': slug
        },
        dataType: 'text'
    }).done(function(){
        updatePuzzleRating(slug, md5);
    });
}
</script>