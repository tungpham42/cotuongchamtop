{{--
    One side's clock plaque (seal + name + per-move bar + main clock).
    Params: $timerSide ('red' | 'black'), $timerMine (bool: this is the viewer's own side)
    Element ids (#red-box, #red-clock, ...) are what the timer script in timer.blade.php drives.
    Height is set by --xq-timer-h (see boardSlot / timer styles), so the plaque scales with the stage.
--}}
<div id="{{ $timerSide }}-box" class="ptimer {{ $timerSide }}-glass paused-offline" data-side="{{ $timerSide }}" data-level="ok">
    <div class="pt-seal" aria-hidden="true"><span>{{ $timerSide === 'red' ? '帥' : '將' }}</span></div>
    <div class="pt-body">
        <div class="pt-head">
            <span class="label">{{ $timerSide === 'red' ? __("Đỏ") : __("Đen") }}</span>
            @if (!empty($timerMine))<span class="pt-you">{{ __("Bạn") }}</span>@endif
            <span class="pt-turn" aria-hidden="true"></span>
        </div>
        <div class="pt-move" title="{{ __('Thời gian mỗi nước') }}">
            <div class="pt-bar"><i id="{{ $timerSide }}-move-bar"></i></div>
            <span id="{{ $timerSide }}-move-clock" class="move-clock">2:00</span>
        </div>
    </div>
    <span id="{{ $timerSide }}-clock" class="clock" role="timer" aria-live="off">10:00</span>
</div>
