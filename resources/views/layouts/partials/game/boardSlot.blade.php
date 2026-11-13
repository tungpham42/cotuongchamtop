{{--
    The board itself. Params: $variant ('game' | 'play').
    - game: renders the full board cell
    - play: renders the empty #ban-co mount point + theme selector
--}}
@if (($variant ?? 'game') === 'play')
    <div id="ban-co" class="mx-auto mr-lg-0 h-auto"></div>
    @include('layouts.partials.themeSelector')
    {{-- @include('layouts.partials.analyzeBtn') --}}
@else
    @include('layouts.partials.boardCell')
@endif
