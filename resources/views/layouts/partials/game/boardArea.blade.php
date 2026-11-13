{{--
    Board + side panel, in the three shapes used by game/play pages.
    Params: $variant ('game' | 'play'), $roomCode, optional $slug
--}}
@php($isPlay = ($variant ?? 'game') === 'play')

@if (!$roomCode && !isset($slug))
    {{-- Free play / setup --}}
    <div class="row">
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 my-1">
            @include('layouts.partials.game.boardSlot')
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-auto mt-0 mt-sm-0 mt-md-0 mt-lg-5">
            @yield('rightSide')
        </div>
    </div>
@elseif (!$roomCode && isset($slug))
    {{-- Puzzle --}}
    <div class="puzzle-layout-wrapper">
        <div class="puzzle-layout-board">
            @include('layouts.partials.game.boardSlot')
            @yield('belowBoardExtras')
        </div>
        <div class="puzzle-layout-panel">
            @yield('rightSide')
        </div>
    </div>
@else
    {{-- Room --}}
    <div class="row">
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
            @include('layouts.partials.game.boardSlot')
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 {{ $isPlay ? 'mt-0' : 'mt-lg-0 mt-md-5 mt-sm-5 mt-xs-5' }}">
            @include($isPlay ? 'layouts.partials.chat' : 'layouts.partials.comments')
            {{-- @include('common.sideAds') --}}
        </div>
    </div>
@endif
