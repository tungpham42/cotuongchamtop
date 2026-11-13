<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="{{ $bodyClass }}" data-layout="play">
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
                {{-- Locale-aware room URLs (config('locales.paths')), valid for all languages.
                     Named localizedRoomUrl because findMatch.blade.php already defines roomUrl(). --}}
                <script>
                    const LOCALIZED_ROOM_URLS = {
                        host:  @json(localized_url('room.host',  ['code' => '__ROOM_CODE__'])),
                        guest: @json(localized_url('room.guest', ['code' => '__ROOM_CODE__']))
                    };
                    function localizedRoomUrl(kind, roomCode) {
                        return LOCALIZED_ROOM_URLS[kind].replace('__ROOM_CODE__', encodeURIComponent(roomCode));
                    }
                    // Adapters used by the shared game partials
                    const xqHostRoomUrl  = (code) => localizedRoomUrl('host', code);
                    const xqGuestRoomUrl = (code) => localizedRoomUrl('guest', code);
                </script>

                @if (!isset($room->tournament_id))
                    <div class="row">
                        @include('layouts.partials.findMatch')
                    </div>
                @endif

                <div class="row">
                    <div class="col-12 text-center mb-3">
                        @yield('aboveBoard')
                    </div>
                </div>

                @include('layouts.partials.game.boardArea', ['variant' => 'play'])

                <div class="row">
                    <div class="col-12">
                        @include('layouts.partials.game.roomActions', ['skipJoinForTournament' => true])

                        <div class="mt-3">
                            @include('common.ads')
                        </div>

                        @yield('aboveContent')
                        <div class="row">
                            <input type="hidden" name="FEN" id="FEN">
                            <input type="hidden" name="piecesUrl" id="piecesUrl" value="{{ url('/') }}">
                            @include('common.themes')
                            @include('layouts.partials.scripts')
                            @yield('belowContent')
                            @if (!isset($board))
                                @include('layouts.partials.game.boardScripts')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('layouts.partials.playFooter')
    @include('common.onlineCounter')
    @include('layouts.partials.karmaToast')
</body>
</html>
