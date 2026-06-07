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
                <div class="royal-grid-card h-100 d-flex flex-column">
                    <div class="royal-board-wrapper" style="cursor: pointer;">
                        <div id="board-{{ $board->code }}" style="width: 100%; height: auto;"></div>
                    </div>
                    
                    <div class="text-center py-2" style="background: linear-gradient(90deg, transparent, rgba(138, 21, 21, 0.6), transparent);">
                        @php
                            $route = localized_url('room.watch', ['code' => $board->code]);
                        @endphp
                        <a href="{{ $route }}" target="_blank" class="royal-card-title text-decoration-none" style="font-size: 1.1rem; text-transform: none;">{{ $board->name }}</a>
                    </div>
                    
                    <div class="p-3 text-center d-flex flex-column justify-content-center flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                            <div class="w-45 text-right text-truncate">
                                <span class="host-title" style="color: #ff3333; font-weight: 800; text-shadow: 0 0 5px rgba(255,0,0,0.4); font-size: 1.1rem;">
                                    {!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->host_id) !!}
                                </span>
                            </div>
                            <div class="w-10">
                                <span class="royal-vs-text">VS</span>
                            </div>
                            <div class="w-45 text-left text-truncate">
                                <span class="guest-title" style="color: var(--royal-gold-light); font-weight: bold; font-size: 1.1rem;">
                                    {!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->guest_id) !!}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <span class="badge badge-status badge-offline mb-2" style="font-size: 0.7rem;">
                                <i class="fas fa-clock"></i> {{ $board->modified_at }}
                            </span>
                            <br>
                            @switch ($board->result)
                                @case('-1')
                                    <span class="badge badge-status" style="background: #111; color: #fff; border: 1px solid #444;"><i class="fas fa-flag"></i> {{ __('Đen thắng') }}</span>
                                    @break
                                @case('0')
                                    <span class="badge badge-status badge-offline"><i class="fas fa-handshake"></i> {{ __('Hòa') }}</span>
                                    @break
                                @case('1')
                                    <span class="badge badge-status badge-online"><i class="fas fa-trophy"></i> {{ __('Đỏ thắng') }}</span>
                                    @break
                            @endswitch
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
                    <div class="royal-grid-card h-100 d-flex flex-column">
                        <div class="royal-board-wrapper" style="cursor: pointer;">
                            <div id="board-{{ $board->code }}" style="width: 100%; height: auto;"></div>
                        </div>
                        
                        <div class="text-center py-2" style="background: linear-gradient(90deg, transparent, rgba(138, 21, 21, 0.6), transparent);">
                            @php
                                $route = localized_url('room.watch', ['code' => $board->code]);
                            @endphp
                            <a href="{{ $route }}" target="_blank" class="royal-card-title text-decoration-none" style="font-size: 1.1rem; text-transform: none;">{{ $board->name }}</a>
                        </div>
                        
                        <div class="p-3 text-center d-flex flex-column justify-content-center flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                                <div class="w-45 text-right text-truncate">
                                    <span class="host-title" style="color: #ff3333; font-weight: 800; text-shadow: 0 0 5px rgba(255,0,0,0.4); font-size: 1.1rem;">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->host_id) !!}
                                    </span>
                                </div>
                                <div class="w-10">
                                    <span class="royal-vs-text">VS</span>
                                </div>
                                <div class="w-45 text-left text-truncate">
                                    <span class="guest-title" style="color: var(--royal-gold-light); font-weight: bold; font-size: 1.1rem;">
                                        {!! app('App\Http\Controllers\UserController')::renderPlayerNameRoom($board->guest_id) !!}
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <span class="badge badge-status badge-offline mb-2" style="font-size: 0.7rem;">
                                    <i class="fas fa-clock"></i> {{ $board->modified_at }}
                                </span>
                                <br>
                                @switch ($board->result)
                                    @case('-1')
                                        <span class="badge badge-status" style="background: #111; color: #fff; border: 1px solid #444;"><i class="fas fa-flag"></i> {{ __('Đen thắng') }}</span>
                                        @break
                                    @case('0')
                                        <span class="badge badge-status badge-offline"><i class="fas fa-handshake"></i> {{ __('Hòa') }}</span>
                                        @break
                                    @case('1')
                                        <span class="badge badge-status badge-online"><i class="fas fa-trophy"></i> {{ __('Đỏ thắng') }}</span>
                                        @break
                                @endswitch
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
                {{ $playedBoards->links('vendor.pagination.playedBoardVi') }}
            </div>
        </div>
    </div>
    @endif
@endif