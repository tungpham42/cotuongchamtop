@php
    $isFiltered = isset($_GET['loai']) && in_array($_GET['loai'], ['co-the', 'van-dau', 'the-co', 'ky-thu']);
    $boardCollection = $isFiltered ? $firstPagePlayedBoards : $playedBoards;
    $shouldDisplay = $isFiltered || (Request::get('page') <= ceil($playedBoards->total() / max($playedBoards->perPage(), 1)));
    $anchorId = $isFiltered ? 'van-dau' : 'van-da-dau';
@endphp

@if($shouldDisplay)
<span style="background-color: transparent; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="{{ $anchorId }}"></span>
<div style="background-color: transparent" class="container-fluid puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4">
                <i class="fas fa-archive" style="color: var(--royal-gold); text-shadow: 0 0 10px var(--royal-gold);"></i> {{ $boardCollection->total() }} {{ __("ván cờ") }} <a class="text-light animate stopPromotion" href="{{ localized_url('app.history') }}">{{ __("đã đấu xong") }}</a>
            </h2>
            {{ $boardCollection->links('vendor.pagination.playedBoardVi') }}

            @foreach($boardCollection as $board)
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="royal-grid-card h-100 d-flex flex-column">
                    <div class="royal-board-wrapper" style="cursor: pointer;">
                        <div id="board-{{ $board->code }}" style="width: 100%; height: auto;"></div>
                    </div>

                    <div class="text-center py-2" style="background: linear-gradient(90deg, transparent, var(--glass-bg-red), transparent); border-bottom: 1px solid rgba(212, 175, 55, 0.15);">
                        @php
                            $route = localized_url('room.watch', ['code' => $board->code]);
                        @endphp
                        <a href="{{ $route }}" target="_blank" class="royal-card-title text-decoration-none" style="font-size: 1.1rem; text-transform: none;">{{ $board->name }}</a>
                    </div>

                    <div class="p-3 text-center d-flex flex-column justify-content-center flex-grow-1" style="background: var(--glass-bg-dark);">
                        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                            <div class="w-45 text-right text-truncate">
                                <span class="host-title" style="color: var(--royal-red-light); font-weight: 800; text-shadow: 0 0 8px rgba(230,57,70,0.6); font-size: 1.1rem;">
                                    {!! $userPresenter->renderPlayerName($board->host_id, true) !!}
                                </span>
                            </div>
                            <div class="w-10">
                                <span class="royal-vs-text">VS</span>
                            </div>
                            <div class="w-45 text-left text-truncate">
                                <span class="guest-title" style="color: var(--royal-gold-light); font-weight: bold; font-size: 1.1rem; text-shadow: 0 0 5px rgba(212, 175, 55, 0.3);">
                                    {!! $userPresenter->renderPlayerName($board->guest_id, true) !!}
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
                                    <span class="badge badge-status pulse-dark" style="background: var(--glass-bg-dark); color: var(--royal-gold-light); border: 1px solid var(--royal-wood); box-shadow: inset 0 0 5px rgba(255,255,255,0.1);"><i class="fas fa-flag"></i> {{ __('Đen thắng') }}</span>
                                    @break
                                @case('0')
                                    <span class="badge badge-status badge-offline"><i class="fas fa-handshake"></i> {{ __('Hòa') }}</span>
                                    @break
                                @case('1')
                                    <span class="badge badge-status pulse-red" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); border: 1px solid var(--royal-gold); color: var(--royal-gold); box-shadow: 0 0 10px rgba(138, 21, 21, 0.5);"><i class="fas fa-trophy"></i> {{ __('Đỏ thắng') }}</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                </div>
            </div>
            <script>
                let board{{ $board->code }}Config = {
                    position: '{{ $board->fen }}',
                    orientation: '{{ str_contains($board->fen, ' r ') ? 'red' : 'black' }}'
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

            {{ $boardCollection->links('vendor.pagination.playedBoardVi') }}
        </div>
    </div>
</div>
@endif
