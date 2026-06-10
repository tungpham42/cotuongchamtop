@if(isset($_GET['loai']) && ($_GET['loai'] == 'van-da-dau' || $_GET['loai'] == 'co-the' || $_GET['loai'] == 'the-co' || $_GET['loai'] == 'ky-thu'))
<span style="background-color: transparent; margin-top: -15px;" class="d-block w-100 pb-5 mb-5" id="van-dau"></span>
<div style="background-color: transparent" class="container-fluid puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4">
                <i class="fas fa-trophy-alt" style="color: var(--royal-gold); text-shadow: 0 0 10px var(--royal-gold);"></i> {{ $firstPageBoards->total() }} {{ __('ván cờ') }} <a class="text-light animate-light showPromotion" href="{{ localized_url('user.list') }}">{{ __('đang thi đấu') }}</a>
            </h2>
            {{ $firstPageBoards->links('vendor.pagination.boardVi') }}
            @foreach($firstPageBoards as $board)
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="royal-grid-card h-100 d-flex flex-column">
                    <div class="royal-board-wrapper" style="cursor: pointer;">
                        <div id="board-{{ $board->code }}" style="width: 100%; height: auto;"></div>
                    </div>

                    <div class="text-center py-2" style="background: linear-gradient(90deg, transparent, var(--glass-bg-red), transparent); border-bottom: 1px solid rgba(212, 175, 55, 0.15);">
                        @php
                            $route = localized_url('room.watch', ['code' => $board->code]);
                            if(auth()->id() == $board->host_id && !isset($board->result)) $route = localized_url('room.host', ['code' => $board->code]);
                            if(auth()->id() == $board->guest_id && !isset($board->result)) $route = localized_url('room.guest', ['code' => $board->code]);
                        @endphp
                        <a href="{{ $route }}" target="_blank" class="royal-card-title text-decoration-none" style="font-size: 1.1rem; text-transform: none;">{{ $board->name }}</a>
                    </div>

                    <div class="p-3 text-center d-flex flex-column justify-content-center flex-grow-1" style="background: var(--glass-bg-dark);">
                        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                            <div class="w-45 text-right text-truncate">
                                <span class="host-title" style="color: var(--royal-red-light); font-weight: 800; text-shadow: 0 0 8px rgba(230,57,70,0.6); font-size: 1.1rem;">
                                    {!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->host_id) !!}
                                </span>
                            </div>
                            <div class="w-10">
                                <span class="royal-vs-text">VS</span>
                            </div>
                            <div class="w-45 text-left text-truncate">
                                <span class="guest-title" style="color: var(--royal-gold-light); font-weight: bold; font-size: 1.1rem; text-shadow: 0 0 5px rgba(212, 175, 55, 0.3);">
                                    {!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->guest_id) !!}
                                </span>
                            </div>
                        </div>

                        <div>
                            <span class="badge badge-status badge-offline mb-2" style="font-size: 0.7rem;">
                                <i class="fas fa-clock"></i> {{ $board->modified_at }}
                            </span>
                            <br>
                            <span class="badge badge-status badge-online pulse-gold" style="border-color: var(--royal-gold); box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);">
                                <i class="fas fa-play" style="color: var(--royal-gold); text-shadow: 0 0 8px var(--royal-gold);"></i> <span style="color: var(--royal-gold-light);">{{ __('Tới lượt') }}</span>
                                @if (str_contains($board->fen, ' r '))
                                    <span style="color: var(--royal-red-light); text-shadow: 0 0 5px rgba(230,57,70,0.8); margin-left: 2px;">{{ __('Đỏ') }}</span>
                                @elseif (str_contains($board->fen, ' b '))
                                    <span style="color: #fff; text-shadow: 0 0 5px rgba(255,255,255,0.8); margin-left: 2px;">{{ __('Đen') }}</span>
                                @endif
                                <span style="color: var(--royal-gold-light);">{{ __('đi') }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
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

                $('#board-{{ $board->code }}').parent().on('click auxclick', function(e){
                    e.preventDefault();
                    window.open('{{ $route }}', '_blank');
                });
            </script>
            @endforeach
            {{ $firstPageBoards->links('vendor.pagination.boardVi') }}
        </div>
    </div>
</div>
@else
    @if ( Request::get('page') <= ceil($boards->total() / $boards->perPage()) )
    <span style="background-color: transparent; margin-top: -15px;" class="d-block w-100 pb-5 mb-5" id="van-dau"></span>
    <div style="background-color: transparent" class="container-fluid puzzles px-0">
        <div class="container mx-auto px-3 pt-0">
            <div class="row my-0">
                <h2 class="d-block w-100 text-light ml-3 mb-4">
                    <i class="fas fa-trophy-alt" style="color: var(--royal-gold); text-shadow: 0 0 10px var(--royal-gold);"></i> {{ $boards->total() }} {{ __('ván cờ') }} <a class="text-light animate-light showPromotion" href="{{ localized_url('user.list') }}">{{ __('đang thi đấu') }}</a>
                </h2>
                {{ $boards->links('vendor.pagination.boardVi') }}
                @foreach($boards as $board)
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="royal-grid-card h-100 d-flex flex-column">
                        <div class="royal-board-wrapper" style="cursor: pointer;">
                            <div id="board-{{ $board->code }}" style="width: 100%; height: auto;"></div>
                        </div>

                        <div class="text-center py-2" style="background: linear-gradient(90deg, transparent, var(--glass-bg-red), transparent); border-bottom: 1px solid rgba(212, 175, 55, 0.15);">
                            @php
                                $route = localized_url('room.watch', ['code' => $board->code]);
                                if(auth()->id() == $board->host_id && !isset($board->result)) $route = localized_url('room.host', ['code' => $board->code]);
                                if(auth()->id() == $board->guest_id && !isset($board->result)) $route = localized_url('room.guest', ['code' => $board->code]);
                            @endphp
                            <a href="{{ $route }}" target="_blank" class="royal-card-title text-decoration-none" style="font-size: 1.1rem; text-transform: none;">{{ $board->name }}</a>
                        </div>

                        <div class="p-3 text-center d-flex flex-column justify-content-center flex-grow-1" style="background: var(--glass-bg-dark);">
                            <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                                <div class="w-45 text-right text-truncate">
                                    <span class="host-title" style="color: var(--royal-red-light); font-weight: 800; text-shadow: 0 0 8px rgba(230,57,70,0.6); font-size: 1.1rem;">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->host_id) !!}
                                    </span>
                                </div>
                                <div class="w-10">
                                    <span class="royal-vs-text">VS</span>
                                </div>
                                <div class="w-45 text-left text-truncate">
                                    <span class="guest-title" style="color: var(--royal-gold-light); font-weight: bold; font-size: 1.1rem; text-shadow: 0 0 5px rgba(212, 175, 55, 0.3);">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->guest_id) !!}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <span class="badge badge-status badge-offline mb-2" style="font-size: 0.7rem;">
                                    <i class="fas fa-clock"></i> {{ $board->modified_at }}
                                </span>
                                <br>
                                <span class="badge badge-status badge-online pulse-gold" style="border-color: var(--royal-gold); box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);">
                                    <i class="fas fa-play" style="color: var(--royal-gold); text-shadow: 0 0 8px var(--royal-gold);"></i> <span style="color: var(--royal-gold-light);">{{ __('Tới lượt') }}</span>
                                    @if (str_contains($board->fen, ' r '))
                                        <span style="color: var(--royal-red-light); text-shadow: 0 0 5px rgba(230,57,70,0.8); margin-left: 2px;">{{ __('Đỏ') }}</span>
                                    @elseif (str_contains($board->fen, ' b '))
                                        <span style="color: #fff; text-shadow: 0 0 5px rgba(255,255,255,0.8); margin-left: 2px;">{{ __('Đen') }}</span>
                                    @endif
                                    <span style="color: var(--royal-gold-light);">{{ __('đi') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
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

                    $('#board-{{ $board->code }}').parent().on('click auxclick', function(e){
                        e.preventDefault();
                        window.open('{{ $route }}', '_blank');
                    });
                </script>
                @endforeach
                {{ $boards->links('vendor.pagination.boardVi') }}
            </div>
        </div>
    </div>
    @endif
@endif
