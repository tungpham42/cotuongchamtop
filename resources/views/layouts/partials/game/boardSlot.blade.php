{{--
    The board itself. Params: $variant ('game' | 'play').
    - game: renders the full board cell
    - play: renders the empty #ban-co mount point + theme selector
--}}
@if (($variant ?? 'game') === 'play')
    {{-- Optional slots filled by the page (e.g. room_master puts the opponent's clock above the
         board and the player's own clock below it). Empty on pages that don't define them. --}}
    @yield('boardTop')
    <div id="ban-co" class="mx-auto mr-lg-0 h-auto"></div>
    @yield('boardBottom')
    @include('layouts.partials.themeSelector')
    {{-- @include('layouts.partials.analyzeBtn') --}}
@else
    @include('layouts.partials.boardCell')
@endif
