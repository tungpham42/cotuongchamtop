<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="{{ $bodyClass }}" data-layout="game">
    @include('common.afterBody')
    @include('common.adsenseTop')
    @include('layouts.partials.header')
    @include('layouts.partials.analysisModal')
    @include('layouts.partials.flashMessages')

    <main>
        {{-- @include('common.xiangqiBanner') --}}
        <span id="checkmateText">{{ __('Chiếu!') }}</span>

        <div class="container-fluid game dashboard px-0" itemscope itemtype="http://schema.org/Game">
            <div class="container {{ isset($board) ? 'px-3 pb-0 pt-3' : 'p-3' }}">
                {{-- Empty, mutable elements only: the volume toggle targets <audio> tags.
                     Real playback happens through XiangqiSound (AudioContext). --}}
                <audio id="nuoc-co" style="display:none"></audio>
                <audio id="het-tran" style="display:none"></audio>
                @include('common.xiangqiSound')
                {{-- Room URL helpers used by the shared game partials --}}
                <script>
                    const ROOM_BASE_URL = '{{ url(__('/phong/')) }}/';
                    const xqHostRoomUrl  = (code) => ROOM_BASE_URL + code;
                    const xqGuestRoomUrl = (code) => ROOM_BASE_URL + code + '{{ __('/khach') }}';
                </script>

                {{-- @include('common.hero') --}}
                {{-- @include('common.articleCarousel', ['articles' => $articles ?? collect()]) --}}
                <div class="row">
                    @include('layouts.partials.findMatch')
                </div>

                <div class="row">
                    <div class="col-12 text-center mb-3">
                        @yield('aboveBoard')
                    </div>
                </div>

                @include('layouts.partials.game.boardArea', ['variant' => 'game'])
                @include('common.adsenseBetween')

                <div class="row">
                    <div class="col-12">
                        @include('layouts.partials.game.roomActions')

                        <div class="mt-3">
                            @include('common.ads')
                        </div>

                        @yield('aboveContent')
                        <div class="row">
                            <input type="hidden" name="FEN" id="FEN">
                            <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}">
                            @include('common.themes')
                            @include('layouts.partials.scripts')
                            @if (!isset($board))
                                @include('layouts.partials.rules')
                            @endif
                            @yield('belowContent')
                            @if (!isset($board))
                                @include('layouts.partials.game.boardScripts')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- @include('layouts.partials.fb') --}}
    </main>

    @include('layouts.partials.aiChatWidget')
    @include('layouts.partials.footer')
    @include('common.contactBtn')
    @include('common.onlineCounter')
    @include('layouts.partials.karmaToast')
</body>
</html>
