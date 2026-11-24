<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<div id="kypho-panel" class="kypho-panel">
    <div class="kypho-header">
        <h5 class="text-center my-2"><i class="fad fa-list"></i> {{ __("Kỳ phổ") }}</h5>
    </div>
    <div class="kypho-controls text-center mb-2">
        <button id="kypho-prev" type="button" class="btn btn-dark btn-sm mx-1" title="Nước trước">
            <i class="fad fa-step-backward"></i> {{ __("Trước") }}
        </button>
        <button id="kypho-next" type="button" class="btn btn-dark btn-sm mx-1" title="Nước sau">
            <i class="fad fa-step-forward"></i> {{ __("Sau") }}
        </button>
        <button id="kypho-copy" type="button" class="btn btn-danger btn-sm mx-1" title="{{ __("Sao chép kỳ phổ") }}">
            <i class="fad fa-copy"></i> {{ __("Sao chép") }}
        </button>
        <button id="kypho-video" type="button" class="btn btn-danger btn-sm mx-1" title="{{ __("Tải Video (9:16)") }}" disabled>
            <i class="fad fa-video"></i> {{ __("Video") }}
        </button>
    </div>
    <div class="kypho-body">
        <div id="kypho-list" class="kypho-moves" tabindex="0" role="region" aria-label="{{ __('Danh sách nước đi') }}"></div>
    </div>
</div>
<style>
    #kypho-panel:not(:has(.kypho-move)) {
        display: none;
    }

    /* ---- Kỳ phổ: "cuốn sổ ghi nước đi" — dark (lantern-lit) palette ---- */
    .kypho-panel {
        --kp-surface: linear-gradient(160deg, rgba(74, 40, 28, 0.82), rgba(38, 20, 14, 0.9));
        --kp-border: rgba(255, 200, 97, 0.38);
        --kp-shadow: 0 14px 34px rgba(10, 4, 2, 0.5), 0 0 30px rgba(255, 170, 70, 0.12);
        --kp-title: #ffd98a;
        --kp-well: rgba(20, 9, 5, 0.45);
        --kp-well-border: rgba(255, 200, 97, 0.2);
        --kp-chip: rgba(255, 241, 214, 0.07);
        --kp-chip-border: rgba(255, 200, 97, 0.14);
        --kp-chip-hover: rgba(255, 200, 97, 0.18);
        --kp-num: #c9a679;
        --kp-red: #ff8f7a;
        --kp-red-dot: #ee5a45;
        --kp-black: #f1e4cf;
        --kp-black-dot: #1b1b1f;
        --kp-black-ring: #cdb893;
        --kp-cur-bg: linear-gradient(180deg, #ffe3a0, #f2ad45);
        --kp-cur-text: #3a1f14;
        --kp-cur-glow: rgba(255, 190, 90, 0.65);
        --kp-fade: rgba(38, 20, 14, 0.95);

        position: relative;
        margin: 10px auto 0;
        max-width: 520px;
        padding: 10px 12px 12px;
        border: 1px solid var(--kp-border);
        border-radius: 20px;
        background: var(--kp-surface);
        box-shadow: var(--kp-shadow), inset 0 1px 0 rgba(255, 236, 200, 0.18);
        -webkit-backdrop-filter: blur(14px) saturate(1.2);
        backdrop-filter: blur(14px) saturate(1.2);
        animation: kyphoRise 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    /* thin honey line across the top, like lantern light on lacquer */
    .kypho-panel::before {
        content: "";
        position: absolute;
        top: -1px;
        left: 18%;
        right: 18%;
        height: 2px;
        border-radius: 2px;
        background: linear-gradient(90deg, transparent, var(--kp-title), transparent);
        opacity: 0.8;
        pointer-events: none;
    }

    .kypho-header h5 {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin: 0 0 8px !important;
        font-size: 1.05rem;
        letter-spacing: 0.3px;
        color: var(--kp-title);
        text-shadow: 0 2px 8px rgba(20, 8, 4, 0.5);
    }
    .kypho-header h5 i {
        display: inline-grid;
        place-items: center;
        width: 28px;
        height: 28px;
        margin: 0 !important;
        font-size: 0.8rem;
        border-radius: 50%;
        color: var(--kp-cur-text);
        background: var(--kp-cur-bg);
        box-shadow: 0 0 14px var(--kp-cur-glow);
    }

    /* ---- Controls: friendly pill buttons ---- */
    .kypho-controls {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
        margin-bottom: 10px !important;
    }
    .kypho-panel .kypho-controls .btn {
        margin: 0 !important;
        padding: 0.32rem 0.8rem;
        font-size: 0.8rem;
        line-height: 1.3;
        border-radius: 999px;
        white-space: nowrap;
    }
    .kypho-panel .kypho-controls .btn i {
        margin-right: 4px !important;
    }
    /* navigation = cocoa glass with honey text */
    .kypho-panel .kypho-controls .btn-dark {
        background: rgba(255, 241, 214, 0.08) !important;
        border: 1px solid rgba(255, 200, 97, 0.45) !important;
        color: #ffe3a8 !important;
        box-shadow: inset 0 1px 0 rgba(255, 236, 200, 0.18) !important;
    }
    .kypho-panel .kypho-controls .btn-dark:hover {
        background: rgba(255, 200, 97, 0.22) !important;
        border-color: #ffd27a !important;
        color: #fff !important;
        box-shadow: 0 6px 16px rgba(10, 4, 2, 0.4), 0 0 16px rgba(255, 190, 90, 0.3) !important;
    }
    /* actions = terracotta lacquer */
    .kypho-panel .kypho-controls .btn-danger {
        background: linear-gradient(180deg, #e0765a, #b04630) !important;
        border: 1px solid rgba(255, 214, 160, 0.6) !important;
        color: #fff6e4 !important;
        box-shadow: 0 6px 14px rgba(168, 64, 44, 0.4), inset 0 1px 0 rgba(255, 236, 200, 0.45) !important;
    }
    .kypho-panel .kypho-controls .btn-danger:hover {
        background: linear-gradient(180deg, #ee866a, #bf5039) !important;
        box-shadow: 0 8px 20px rgba(168, 64, 44, 0.5), 0 0 18px rgba(255, 170, 90, 0.4), inset 0 1px 0 rgba(255, 236, 200, 0.55) !important;
    }
    .kypho-controls .btn[disabled],
    .kypho-controls .btn.disabled {
        opacity: 0.4;
        filter: saturate(0.6);
        pointer-events: none;
    }

    /* ---- Move list ---- */
    .kypho-body {
        position: relative;
    }
    .kypho-moves {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        align-content: flex-start;
        box-sizing: border-box;
        /* Three 28px rows, two 6px gaps, padding and border. */
        max-height: 122px;
        overflow-y: auto;
        overscroll-behavior-y: contain;
        scroll-behavior: smooth;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 200, 97, 0.6) transparent;
        padding: 10px;
        border: 1px solid var(--kp-well-border);
        border-radius: 14px;
        background: var(--kp-well);
        box-shadow: inset 0 3px 10px rgba(0, 0, 0, 0.25);
    }
    .kypho-moves:focus-visible {
        outline: 2px solid var(--kp-title);
        outline-offset: 2px;
    }

    .kypho-move {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 2px 10px 2px 8px;
        border-radius: 999px;
        border: 1px solid var(--kp-chip-border);
        background: var(--kp-chip);
        font-size: 0.82rem;
        font-weight: 600;
        line-height: 22px;
        height: 28px;
        box-sizing: border-box;
        flex-shrink: 0;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1),
            background 0.2s ease, box-shadow 0.25s ease;
    }
    /* piece-colour dot: red side / black side at a glance */
    .kypho-move::before {
        content: "";
        flex: none;
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }
    .kypho-move.red {
        color: var(--kp-red);
    }
    .kypho-move.red::before {
        background: var(--kp-red-dot);
        box-shadow: 0 0 6px var(--kp-red-dot);
    }
    .kypho-move.black {
        color: var(--kp-black);
    }
    .kypho-move.black::before {
        background: var(--kp-black-dot);
        box-shadow: 0 0 0 1.5px var(--kp-black-ring);
    }
    .kypho-move:hover {
        background: var(--kp-chip-hover);
        transform: translateY(-1px);
    }
    .kypho-move-number {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--kp-num);
        margin-right: 0;
    }
    .kypho-move.kypho-current {
        background: var(--kp-cur-bg);
        border-color: rgba(255, 246, 228, 0.7);
        color: var(--kp-cur-text);
        font-weight: 800;
        transform: scale(1.06);
        box-shadow: 0 0 0 3px rgba(255, 200, 97, 0.28), 0 4px 14px var(--kp-cur-glow);
        animation: kyphoGlow 2.2s ease-in-out infinite;
    }
    .kypho-move.kypho-current .kypho-move-number {
        color: rgba(58, 31, 20, 0.7);
    }
    .kypho-move.kypho-current::before {
        box-shadow: 0 0 0 1.5px rgba(58, 31, 20, 0.45);
    }

    @keyframes kyphoRise {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: none; }
    }
    @keyframes kyphoGlow {
        0%, 100% { box-shadow: 0 0 0 3px rgba(255, 200, 97, 0.25), 0 4px 12px var(--kp-cur-glow); }
        50% { box-shadow: 0 0 0 5px rgba(255, 200, 97, 0.12), 0 4px 20px var(--kp-cur-glow); }
    }

    @media (max-width: 575.98px) {
        .kypho-panel {
            padding: 8px 8px 10px;
            border-radius: 16px;
        }
        .kypho-panel .kypho-controls .btn {
            padding: 0.3rem 0.65rem;
            font-size: 0.76rem;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .kypho-panel,
        .kypho-move.kypho-current {
            animation: none;
        }
        .kypho-moves {
            scroll-behavior: auto;
        }
        .kypho-move,
        .kypho-move:hover,
        .kypho-move.kypho-current {
            transition: none;
            transform: none;
        }
    }
</style>
