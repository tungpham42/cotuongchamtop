{{--
    The board itself. Params: $variant ('game' | 'play').
    - game: renders the full board cell
    - play: renders the "stage": [boardTop slot] + #ban-co + [boardBottom slot], sized together so the
            whole stack (opponent clock + board + own clock) fits the viewport height.
--}}
@if (($variant ?? 'game') === 'play')
    @php
        // Number of clock plaques the page put around the board (room_master defines both or neither).
        // They are subtracted from the height budget; pages without clocks get the full height.
        $xqTimerCount = $__env->hasSection('boardTop') ? 2 : 0;
    @endphp

    {{-- Optional slots filled by the page (e.g. room_master puts the opponent's clock above the
         board and the player's own clock below it). Empty on pages that don't define them. --}}
    <div id="xq-stage" class="xq-stage" style="--xq-timer-count: {{ $xqTimerCount }};">
        @yield('boardTop')
        <div id="ban-co" class="h-auto"></div>
        @yield('boardBottom')
    </div>
    @include('layouts.partials.themeSelector')
    {{-- @include('layouts.partials.analyzeBtn') --}}

    <style>
        /* ==========================================================================
           FIT-TO-VIEWPORT STAGE
           Goal: opponent clock + board + own clock are fully visible in one screen.

           The board's height follows from its width (9 files x 10 ranks), so we solve for the
           width that makes the whole stack exactly as tall as the space below the sticky header:

             stack height  = N*(timer + gap)  +  board chrome  +  (W - 12px) * 10/9
             => W          = (available - N*(timer + gap) - chrome) * 9/10

           and clamp it between a readable minimum and the original 380px maximum.
           Everything inside the stage is 100% of the stage, so board and plaques always line up.
           ========================================================================== */
        .xq-stage {
            /* Tunables */
            --xq-vh: 100vh;                  /* upgraded to 100dvh below (mobile browser bars) */
            --xq-header: 56px;               /* sticky header; measured by the script below */
            --xq-breathing: 12px;            /* space kept under the stack */
            --xq-gap: 8px;                   /* between plaque and board */
            --xq-timer-h: 64px;              /* height of one clock plaque */
            --xq-board-chrome: 40px;         /* board padding+border beyond the 9:10 squares (~39px with notation) */
            --xq-board-max: 380px;           /* the original max-width of #ban-co */
            --xq-board-min: 240px;           /* never shrink below this: scroll instead */

            --xq-avail: calc(var(--xq-vh) - var(--xq-header) - var(--xq-breathing));
            --xq-reserved: calc(var(--xq-timer-count, 0) * (var(--xq-timer-h) + var(--xq-gap)) + var(--xq-board-chrome));
            --xq-board-w: clamp(
                var(--xq-board-min),
                calc((var(--xq-avail) - var(--xq-reserved)) * 9 / 10),
                var(--xq-board-max)
            );

            display: flex;
            flex-direction: column;
            gap: var(--xq-gap);
            width: 100%;
            max-width: var(--xq-board-w);
            margin: 0 auto;
            scroll-margin-top: calc(var(--xq-header) + 6px);
        }
        @supports (height: 100dvh) {
            .xq-stage { --xq-vh: 100dvh; }
        }

        /* Compact clocks on short screens (landscape phones, small laptops) */
        @media (max-height: 700px) {
            .xq-stage { --xq-timer-h: 56px; --xq-gap: 6px; }
        }
        @media (max-height: 560px) {
            .xq-stage { --xq-timer-h: 46px; --xq-gap: 4px; --xq-breathing: 6px; }
        }

        /* The board fills the stage. These win over ban-co.css / index.css, which pin
           width, max-width and margin-bottom (15px, !important) on #ban-co. */
        .xq-stage #ban-co {
            width: 100% !important;
            max-width: none;
            margin: 0 !important;
            flex: none;
        }
    </style>

    <script>
        // Keep --xq-header equal to the real sticky header height (it differs per locale/breakpoint).
        (function () {
            var stage = document.getElementById('xq-stage');
            if (!stage) return;
            function measure() {
                var header = document.querySelector('header.site-header');
                var px = header ? Math.round(header.getBoundingClientRect().height) : 0;
                if (px <= 0) return false;
                var next = px + 'px';
                if (stage.style.getPropertyValue('--xq-header') === next) return false;
                stage.style.setProperty('--xq-header', next);
                return true;
            }
            measure();
            // Registered before the board's own resize handler, so the CSS is already up to date when it runs.
            window.addEventListener('resize', measure);
            // Web fonts can change the header height after first paint: let the board re-measure itself.
            window.addEventListener('load', function () {
                if (measure()) window.dispatchEvent(new Event('resize'));
            });
        })();
    </script>
@else
    @include('layouts.partials.boardCell')
@endif
