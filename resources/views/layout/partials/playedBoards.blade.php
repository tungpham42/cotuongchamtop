@if(isset($_GET['loai']) && ($_GET['loai'] == 'co-the' || $_GET['loai'] == 'van-dau' || $_GET['loai'] == 'the-co' || $_GET['loai'] == 'ky-thu'))
<span style="background-color: transparent; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="van-dau"></span>
<div style="background-color: transparent" class="container-fluid puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4">
                <i class="fas fa-archive"></i> {{ $firstPagePlayedBoards->total() }} {{ __("ván cờ") }} <a class="text-light animate-light stopPromotion" href="{{ localized_url('app.history') }}">{{ __("đã đấu xong") }}</a>
            </h2>
            {{ $firstPagePlayedBoards->links('vendor.pagination.playedBoardVi') }}
            @foreach($firstPagePlayedBoards as $board)
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div id="board-{{ $board->code }}" class="card shadow-lg rounded border-dark" style="cursor: pointer;background-color: transparent;">
                </div>
                <div class="bg-dark p-2 text-center">
                    <a href="{{ url(__('/phong/')) }}/{{ $board->code }}{{ __('/theo-doi') }}" target="_blank" class="py-1 text-light animate-light w-100 text-center stopPromotion">{{ $board->name }}</a>
                </div>
                <div class="bg-dark row mx-0">
                    <span class="py-1 col-12 text-light text-center host-title">{!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->host_id) !!}</span>
                    <span class="py-1 col-12 text-light text-center guest-title">{!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->guest_id) !!}</span>
                    <span class="py-1 col-12 text-light text-center">{{ $board->modified_at }}</span>
                    <span class="py-1 col-12 text-light text-center ">
                        {{ __('Tới lượt') }}
                        @if (str_contains($board->fen, ' r '))
                        {{ __('Đỏ') }}
                        @elseif (str_contains($board->fen, ' b '))
                        {{ __('Đen') }}
                        @endif
                        {{ __('đi') }}
                    </span>
                    <span class="py-1 col-12 text-light text-center">
                        @switch ($board->result)
                            @case('-1')
                                {{ __('Đen thắng') }}
                                @break
                            @case('0')
                                {{ __('Hòa') }}
                                @break
                            @case('1')
                                {{ __('Đỏ thắng') }}
                                @break
                        @endswitch
                    </span>
                </div>
            </div>
            <style>
            #board-{{ $board->code }} {
                background-color: #e1bd86 !important;
            }
            #board-{{ $board->code }} .xiangqiboard-8ddcb {
                margin: auto !important;
                background-color: #e1bd86 !important;
            }
            #board-{{ $board->code }} .xiangqiboard-8ddcb .board-1ef78 {
                box-shadow: none !important;
            }
            </style>
            <script>
                let board{{ $board->code }}Config = {
                    position: '{{ $board->fen }}',
                    @if (str_contains($board->fen, ' r '))
                    orientation: 'red'
                    @elseif (str_contains($board->fen, ' b '))
                    orientation: 'black'
                    @endif
                }
                const board{{ $board->code }} = Xiangqiboard('board-{{ $board->code }}', board{{ $board->code }}Config);
                board{{ $board->code }}.resize();
                $(window).resize(board{{ $board->code }}.resize);
                $('#board-{{ $board->code }}').on('click auxclick', function(e){
                    e.preventDefault();
                    window.location.href = '{{ url(__('/phong/')) }}' + '/' + '{{ $board->code }}' + '{{ __('/theo-doi') }}';
                });
            </script>
            @endforeach
            {{ $firstPagePlayedBoards->links('vendor.pagination.playedBoardVi') }}
        </div>
    </div>
</div>
@else
    @if ( Request::get('page') <= ceil($playedBoards->total() / $playedBoards->perPage()) )
    <span style="background-color: transparent; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="van-da-dau"></span>
    <div style="background-color: transparent" class="container-fluid puzzles px-0">
        <div class="container mx-auto px-3 pt-0">
            <div class="row my-0">
                <h2 class="d-block w-100 text-light ml-3 mb-4">
                    <i class="fas fa-archive"></i> {{ $playedBoards->total() }} {{ __("ván cờ") }} <a class="text-light animate-light stopPromotion" href="{{ localized_url('app.history') }}">{{ __("đã đấu xong") }}</a>
                </h2>
                {{ $playedBoards->links('vendor.pagination.playedBoardVi') }}
                @foreach($playedBoards as $board)
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div id="board-{{ $board->code }}" class="card shadow-lg rounded border-dark" style="cursor: pointer;background-color: transparent;">
                    </div>
                    <div class="bg-dark p-2 text-center">
                        <a href="{{ url(__('/phong/')) }}/{{ $board->code }}{{ __('/theo-doi') }}" target="_blank" class="py-1 text-light animate-light w-100 text-center stopPromotion">{{ $board->name }}</a>
                    </div>
                    <div class="bg-dark row mx-0">
                        <span class="py-1 col-12 text-light text-center host-title">{!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->host_id) !!}</span>
                        <span class="py-1 col-12 text-light text-center guest-title">{!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->guest_id) !!}</span>
                        <span class="py-1 col-12 text-light text-center">{{ $board->modified_at }}</span>
                        <span class="py-1 col-12 text-light text-center ">
                            {{ __('Tới lượt') }}
                            @if (str_contains($board->fen, ' r '))
                            {{ __('Đỏ') }}
                            @elseif (str_contains($board->fen, ' b '))
                            {{ __('Đen') }}
                            @endif
                            {{ __('đi') }}
                        </span>
                        <span class="py-1 col-12 text-light text-center">
                            @switch ($board->result)
                                @case('-1')
                                    {{ __('Đen thắng') }}
                                    @break
                                @case('0')
                                    {{ __('Hòa') }}
                                    @break
                                @case('1')
                                    {{ __('Đỏ thắng') }}
                                    @break
                            @endswitch
                        </span>
                    </div>
                </div>
                <style>
                #board-{{ $board->code }} {
                    background-color: #e1bd86 !important;
                }
                #board-{{ $board->code }} .xiangqiboard-8ddcb {
                    margin: auto !important;
                    background-color: #e1bd86 !important;
                }
                #board-{{ $board->code }} .xiangqiboard-8ddcb .board-1ef78 {
                    box-shadow: none !important;
                }
                </style>
                <script>
                    let board{{ $board->code }}Config = {
                        position: '{{ $board->fen }}',
                        @if (str_contains($board->fen, ' r '))
                        orientation: 'red'
                        @elseif (str_contains($board->fen, ' b '))
                        orientation: 'black'
                        @endif
                    }
                    const board{{ $board->code }} = Xiangqiboard('board-{{ $board->code }}', board{{ $board->code }}Config);
                    board{{ $board->code }}.resize();
                    $(window).resize(board{{ $board->code }}.resize);
                    $('#board-{{ $board->code }}').on('click auxclick', function(e){
                        e.preventDefault();
                        window.location.href = '{{ url(__('/phong/')) }}' + '/' + '{{ $board->code }}' + '{{ __('/theo-doi') }}';
                    });
                </script>
                @endforeach
                {{ $playedBoards->links('vendor.pagination.playedBoardVi') }}
            </div>
        </div>
    </div>
    @endif
@endif
