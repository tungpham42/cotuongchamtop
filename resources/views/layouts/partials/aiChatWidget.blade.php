<style>
    /* ==========================================================================
       AI COACH WIDGET - LIQUID GLASS + CUNG ĐÌNH HUẾ (GLOSSY / TRANSLUCENT)
       Dùng chung token với index_new.css (--royal-*, --glass-*, --liquid-*).
       Mọi selector đều nằm trong #ai-coach-widget để không đụng .btn / .form-control
       / .card toàn site. Có fallback nếu biến site chưa được nạp.

       Ý tưởng thiết kế:
         - Nền trong suốt, để hình nền bàn cờ của site xuyên qua (blur + saturate).
         - Mọi bề mặt là "kính": lớp nền tint mỏng + vệt phản chiếu (gloss) nửa trên
           + viền sáng mảnh (specular) + bóng đổ mềm.
         - Chữ vẫn đủ tương phản nhờ lớp tint tối nhẹ phía sau bong bóng chat.
       ========================================================================== */
    #ai-coach-widget {
        --aic-red: var(--royal-red, #8a1515);
        --aic-red-bright: var(--royal-red-dark, #b72222);
        --aic-red-light: var(--royal-red-light, #e63946);
        --aic-red-deep: #5c0a0a;
        --aic-gold: var(--royal-gold, #ffd700);
        --aic-gold-soft: var(--royal-gold-light, #fff2cc);
        --aic-ink: var(--royal-bg, #0b0c10);
        --aic-wood: var(--royal-wood, #4a2511);

        /* Kính: alpha thấp = trong hơn */
        --aic-glass: rgba(16, 9, 9, 0.42);
        --aic-glass-strong: rgba(16, 9, 9, 0.58);
        --aic-blur: blur(22px) saturate(170%);
        --aic-border: var(--glass-border, rgba(255, 215, 0, 0.55));
        --aic-line: rgba(255, 215, 0, 0.2);
        --aic-edge: rgba(255, 255, 255, 0.55);   /* viền sáng phía trên */
        --aic-gloss: linear-gradient(180deg, rgba(255, 255, 255, 0.34) 0%, rgba(255, 255, 255, 0.08) 48%, rgba(255, 255, 255, 0) 52%);
        --aic-ease: cubic-bezier(0.175, 0.885, 0.32, 1.275);

        /* Góc DƯỚI BÊN TRÁI: tách khỏi cụm Zalo / Online Counter / To-top ở góc phải */
        position: fixed;
        bottom: 25px;
        left: 25px;
        z-index: 1060;
        font-family: "Plus Jakarta Sans", "Noto Sans JP", "Noto Sans KR", "Noto Sans SC", sans-serif;
    }

    #ai-coach-widget *,
    #ai-coach-widget *::before,
    #ai-coach-widget *::after {
        box-sizing: border-box;
    }

    /* Site đặt margin-right cho mọi icon FA; widget tự canh khoảng cách bằng gap */
    #ai-coach-widget .fas {
        margin-right: 0 !important;
    }

    #ai-coach-widget :focus-visible {
        outline: 2px solid var(--aic-gold);
        outline-offset: 2px;
    }

    /* --------------------------------------------------------------------------
       LAUNCHER - viên ngọc ruby trong suốt, phản chiếu như giọt kính
       -------------------------------------------------------------------------- */
    #ai-chat-btn {
        appearance: none;
        -webkit-appearance: none;
        padding: 0;
        margin: 0;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 62px;
        height: 62px;
        border-radius: 50%;
        border: 1.5px solid rgba(255, 215, 0, 0.75);
        border-top-color: rgba(255, 255, 255, 0.85);
        color: var(--aic-gold-soft);
        font-size: 25px;
        cursor: pointer;
        background:
            radial-gradient(circle at 50% 120%, rgba(255, 215, 0, 0.35) 0%, rgba(255, 215, 0, 0) 55%),
            linear-gradient(145deg, rgba(230, 57, 70, 0.62) 0%, rgba(138, 21, 21, 0.58) 55%, rgba(92, 10, 10, 0.7) 100%);
        backdrop-filter: blur(14px) saturate(180%);
        -webkit-backdrop-filter: blur(14px) saturate(180%);
        box-shadow:
            0 10px 28px rgba(0, 0, 0, 0.55),
            0 0 22px rgba(255, 215, 0, 0.3),
            inset 0 2px 6px rgba(255, 255, 255, 0.55),
            inset 0 -8px 14px rgba(0, 0, 0, 0.3),
            inset 0 0 18px rgba(255, 215, 0, 0.18);
        transition: transform 0.4s var(--aic-ease), box-shadow 0.4s ease;
    }
    #ai-chat-btn:hover {
        transform: translateY(-3px) scale(1.08);
        box-shadow:
            0 16px 34px rgba(0, 0, 0, 0.65),
            0 0 30px rgba(255, 215, 0, 0.6),
            inset 0 2px 8px rgba(255, 255, 255, 0.7),
            inset 0 -8px 14px rgba(0, 0, 0, 0.3),
            inset 0 0 22px rgba(255, 215, 0, 0.28);
    }
    #ai-chat-btn:active {
        transform: scale(0.95);
    }

    /* Mặt kính cong: vệt sáng nửa trên + quét ngang khi hover */
    #ai-chat-btn .aic-orb-sheen {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        overflow: hidden;
        pointer-events: none;
        background:
            radial-gradient(ellipse 70% 42% at 50% 14%, rgba(255, 255, 255, 0.7) 0%, rgba(255, 255, 255, 0) 100%);
    }
    #ai-chat-btn .aic-orb-sheen::after {
        content: "";
        position: absolute;
        top: 0;
        left: -130%;
        width: 90%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
        transform: skewX(-20deg);
        transition: left 0.7s ease;
    }
    #ai-chat-btn:hover .aic-orb-sheen::after {
        left: 140%;
    }
    #ai-chat-btn .aic-orb-icon {
        position: relative;
        z-index: 1;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.55)) drop-shadow(0 0 6px rgba(255, 215, 0, 0.35));
    }

    /* Vòng xung vàng - chỉ chạy tới khi khách mở chat lần đầu */
    #ai-chat-btn.is-attn::after {
        content: "";
        position: absolute;
        inset: -2px;
        border-radius: 50%;
        pointer-events: none;
        animation: aicPulse 2.4s ease-out infinite;
    }
    @keyframes aicPulse {
        0%   { box-shadow: 0 0 0 0 rgba(255, 215, 0, 0.6); }
        70%  { box-shadow: 0 0 0 18px rgba(255, 215, 0, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 215, 0, 0); }
    }

    .ai-btn-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        z-index: 2;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.65) 0%, rgba(255, 255, 255, 0) 55%),
            linear-gradient(135deg, rgba(255, 242, 204, 0.92) 0%, rgba(255, 215, 0, 0.9) 55%, rgba(217, 164, 0, 0.9) 100%);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        color: var(--aic-red-deep);
        font-size: 10px;
        font-weight: 800;
        line-height: 1;
        padding: 3px 7px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.45), 0 0 10px rgba(255, 215, 0, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.85);
        letter-spacing: 0.5px;
    }

    /* --------------------------------------------------------------------------
       PANEL - tấm kính trong mờ, viền sáng, vệt phản chiếu phía trên
       -------------------------------------------------------------------------- */
    #ai-chat-box {
        position: absolute;
        bottom: 78px;
        left: 0;
        width: 372px;
        height: 540px;
        max-height: calc(100vh - 125px);
        display: none; /* JS bật flex */
        flex-direction: column;
        overflow: hidden;
        isolation: isolate;
        border-radius: 20px;
        background:
            linear-gradient(155deg, rgba(255, 255, 255, 0.14) 0%, rgba(255, 255, 255, 0.03) 30%, rgba(255, 255, 255, 0) 55%),
            radial-gradient(120% 70% at 0% 0%, rgba(138, 21, 21, 0.3), transparent 60%),
            radial-gradient(100% 60% at 100% 100%, rgba(255, 215, 0, 0.1), transparent 60%),
            var(--aic-glass);
        backdrop-filter: var(--aic-blur);
        -webkit-backdrop-filter: var(--aic-blur);
        border: 1px solid rgba(255, 215, 0, 0.42);
        border-top: 1.5px solid var(--aic-edge);
        box-shadow:
            0 12px 40px rgba(0, 0, 0, 0.55),
            0 28px 70px rgba(0, 0, 0, 0.4),
            0 0 30px rgba(255, 215, 0, 0.1),
            inset 0 2px 18px rgba(255, 255, 255, 0.22),
            inset 0 -1px 0 rgba(255, 215, 0, 0.25);
        transform-origin: bottom left;
        animation: aicOpen 0.4s var(--aic-ease);
    }
    /* Vệt phản chiếu cong phía trên (như mặt kính bóng) */
    #ai-chat-box::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 34%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 100%);
        border-radius: 20px 20px 60% 60% / 20px 20px 22% 22%;
        pointer-events: none;
        z-index: 0;
    }
    #ai-chat-box > * {
        position: relative;
        z-index: 1;
    }
    @keyframes aicOpen {
        from { opacity: 0; transform: translateY(18px) scale(0.92); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Header: ruby kính + gloss */
    #ai-coach-widget .chat-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        background:
            var(--aic-gloss),
            linear-gradient(135deg, rgba(183, 34, 34, 0.62) 0%, rgba(92, 10, 10, 0.55) 100%);
        border-bottom: 1px solid rgba(255, 215, 0, 0.4);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45), inset 0 -6px 14px rgba(255, 215, 0, 0.1);
        flex-shrink: 0;
    }
    #ai-coach-widget .chat-header > * {
        position: relative;
    }

    .aic-title {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .aic-avatar {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: var(--aic-gold);
        background:
            radial-gradient(ellipse 70% 40% at 50% 15%, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0)),
            rgba(11, 12, 16, 0.55);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1.5px solid rgba(255, 215, 0, 0.85);
        box-shadow: 0 0 14px rgba(255, 215, 0, 0.45), inset 0 1px 4px rgba(255, 255, 255, 0.35);
    }
    .aic-title-text {
        font-family: "Texturina", "Noto Sans JP", "Noto Sans KR", "Noto Sans SC", serif;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.2;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--aic-gold);
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.75), 0 0 12px rgba(255, 215, 0, 0.4);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .aic-vip-tag {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        padding: 3px 8px;
        border-radius: 999px;
        color: var(--aic-red-deep);
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0) 55%),
            linear-gradient(135deg, rgba(255, 242, 204, 0.92), rgba(255, 215, 0, 0.9) 55%, rgba(217, 164, 0, 0.9));
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4), 0 0 10px rgba(255, 215, 0, 0.3), inset 0 1px 2px rgba(255, 255, 255, 0.85);
    }

    #close-chat {
        appearance: none;
        -webkit-appearance: none;
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid rgba(255, 215, 0, 0.45);
        background: var(--aic-gloss), rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        color: var(--aic-gold-soft);
        font-size: 14px;
        cursor: pointer;
        box-shadow: inset 0 1px 3px rgba(255, 255, 255, 0.3);
        transition: all 0.3s var(--aic-ease);
    }
    #close-chat:hover {
        color: var(--aic-red-deep);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0) 55%), var(--aic-gold);
        border-color: #fff;
        transform: rotate(90deg);
    }

    /* Banner dùng thử / nâng cấp VIP */
    #ai-coach-widget .ai-trial-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 7px 14px;
        font-size: 11.5px;
        font-weight: 500;
        color: var(--aic-gold-soft);
        background: linear-gradient(90deg, rgba(255, 215, 0, 0.16), rgba(255, 215, 0, 0.03));
        border-bottom: 1px solid var(--aic-line);
        flex-shrink: 0;
    }
    #ai-coach-widget .ai-trial-bar > span {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    #ai-coach-widget .ai-trial-bar .fa-gift {
        color: var(--aic-gold);
    }

    /* --------------------------------------------------------------------------
       BUTTONS - nút kính bóng (gloss nửa trên). Không dùng .btn của site.
       -------------------------------------------------------------------------- */
    #ai-coach-widget .aic-btn {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 999px;
        font-family: inherit;
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.2;
        text-decoration: none !important;
        cursor: pointer;
        border: 1px solid transparent;
        backdrop-filter: blur(10px) saturate(160%);
        -webkit-backdrop-filter: blur(10px) saturate(160%);
        transition: transform 0.3s var(--aic-ease), box-shadow 0.3s ease, color 0.2s ease;
    }
    #ai-coach-widget .aic-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -150%;
        width: 120%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
        transform: skewX(-20deg);
        transition: left 0.6s ease;
        pointer-events: none;
    }
    #ai-coach-widget .aic-btn:hover::before {
        left: 150%;
    }
    #ai-coach-widget .aic-btn:hover {
        transform: translateY(-2px);
    }
    #ai-coach-widget .aic-btn-block {
        display: flex;
        width: 100%;
    }

    #ai-coach-widget .aic-btn-ruby {
        color: var(--aic-gold-soft) !important;
        background: var(--aic-gloss), linear-gradient(135deg, rgba(183, 34, 34, 0.66), rgba(92, 10, 10, 0.6));
        border-color: rgba(255, 215, 0, 0.7);
        border-top-color: rgba(255, 255, 255, 0.6);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4), inset 0 1px 6px rgba(255, 215, 0, 0.22);
    }
    #ai-coach-widget .aic-btn-ruby:hover {
        color: #fff !important;
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.5), 0 0 18px rgba(255, 215, 0, 0.4), inset 0 1px 10px rgba(255, 255, 255, 0.3);
    }

    #ai-coach-widget .aic-btn-gold {
        color: var(--aic-red-deep) !important;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.55) 0%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0) 52%),
            linear-gradient(135deg, rgba(255, 242, 204, 0.88) 0%, rgba(255, 215, 0, 0.85) 50%, rgba(217, 164, 0, 0.85) 100%);
        border-color: rgba(255, 255, 255, 0.65);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4), 0 0 14px rgba(255, 215, 0, 0.3), inset 0 1px 3px rgba(255, 255, 255, 0.85);
        font-weight: 800;
    }
    #ai-coach-widget .aic-btn-gold:hover {
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.5), 0 0 22px rgba(255, 215, 0, 0.6), inset 0 1px 3px rgba(255, 255, 255, 0.95);
    }

    #ai-coach-widget .aic-btn-ghost {
        color: var(--aic-gold) !important;
        background: var(--aic-gloss), rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 215, 0, 0.5);
        border-top-color: rgba(255, 255, 255, 0.5);
        box-shadow: inset 0 1px 5px rgba(255, 255, 255, 0.15);
    }
    #ai-coach-widget .aic-btn-ghost:hover {
        background: var(--aic-gloss), rgba(255, 215, 0, 0.16);
        border-color: var(--aic-gold);
        box-shadow: 0 0 16px rgba(255, 215, 0, 0.32), inset 0 1px 8px rgba(255, 255, 255, 0.25);
    }

    /* Nút nâng cấp nhỏ trong banner */
    #ai-coach-widget .btn-upgrade-pill {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 10.5px;
        font-weight: 800;
        text-decoration: none !important;
        color: var(--aic-red-deep) !important;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0) 55%),
            linear-gradient(135deg, rgba(255, 242, 204, 0.9) 0%, rgba(255, 215, 0, 0.88) 50%, rgba(217, 164, 0, 0.88) 100%);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.85);
        transition: transform 0.3s var(--aic-ease), box-shadow 0.3s ease;
    }
    #ai-coach-widget .btn-upgrade-pill:hover {
        transform: scale(1.06);
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.5), 0 0 14px rgba(255, 215, 0, 0.55), inset 0 1px 2px rgba(255, 255, 255, 0.95);
    }

    /* --------------------------------------------------------------------------
       MESSAGES - vùng chat gần như trong suốt, bong bóng là các mảnh kính nhỏ
       -------------------------------------------------------------------------- */
    #ai-coach-widget .chat-body {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 16px 14px;
        overflow-y: auto;
        /* Lưới mờ gợi bàn cờ, rất nhẹ để nền site vẫn thấy rõ */
        background:
            linear-gradient(rgba(255, 215, 0, 0.035) 1px, transparent 1px) 0 0 / 28px 28px,
            linear-gradient(90deg, rgba(255, 215, 0, 0.035) 1px, transparent 1px) 0 0 / 28px 28px,
            linear-gradient(180deg, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.18));
        scrollbar-width: thin;
        scrollbar-color: rgba(183, 34, 34, 0.8) transparent;
    }
    #ai-coach-widget .chat-body::-webkit-scrollbar {
        width: 8px;
    }
    #ai-coach-widget .chat-body::-webkit-scrollbar-track {
        background: transparent;
        box-shadow: none;
        border: 0;
    }
    #ai-coach-widget .chat-body::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(183, 34, 34, 0.85), rgba(92, 10, 10, 0.85));
        border: 1px solid rgba(255, 215, 0, 0.55);
        border-radius: 6px;
    }

    #ai-coach-widget .msg {
        position: relative;
        max-width: 88%;
        padding: 10px 14px;
        font-size: 13.5px;
        line-height: 1.55;
        word-wrap: break-word;
        backdrop-filter: blur(12px) saturate(160%);
        -webkit-backdrop-filter: blur(12px) saturate(160%);
        animation: aicMsgIn 0.3s ease-out;
    }
    @keyframes aicMsgIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* AI: kính khói + vạch vàng bên trái + viền sáng phía trên */
    #ai-coach-widget .msg-ai {
        align-self: flex-start;
        color: #fbf0d6;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.55);
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0.03) 45%, rgba(255, 255, 255, 0) 100%),
            rgba(14, 10, 10, 0.4);
        border: 1px solid rgba(255, 215, 0, 0.28);
        border-top-color: rgba(255, 255, 255, 0.4);
        border-left: 3px solid rgba(255, 215, 0, 0.85);
        border-radius: 14px 14px 14px 4px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35), inset 0 1px 8px rgba(255, 255, 255, 0.1);
    }
    #ai-coach-widget .msg-ai strong,
    #ai-coach-widget .msg-ai b {
        color: var(--aic-gold);
    }
    #ai-coach-widget .msg-ai a:not(.aic-btn) {
        color: var(--aic-gold);
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    #ai-coach-widget .msg-ai a:not(.aic-btn):hover {
        color: #fff;
    }
    #ai-coach-widget .msg-ai ul,
    #ai-coach-widget .msg-ai ol {
        margin: 6px 0;
        padding-left: 18px;
    }
    #ai-coach-widget .msg-ai p {
        margin: 0 0 6px;
    }
    #ai-coach-widget .msg-ai p:last-child {
        margin-bottom: 0;
    }
    #ai-coach-widget .msg-ai code {
        padding: 1px 6px;
        border-radius: 6px;
        font-size: 12.5px;
        color: var(--aic-gold);
        background: rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(255, 215, 0, 0.25);
    }

    /* Người dùng: ruby kính bóng, viền vàng mảnh */
    #ai-coach-widget .msg-user {
        align-self: flex-end;
        color: #fff;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.34) 0%, rgba(255, 255, 255, 0.06) 48%, rgba(255, 255, 255, 0) 52%),
            linear-gradient(135deg, rgba(230, 57, 70, 0.6) 0%, rgba(138, 21, 21, 0.58) 55%, rgba(92, 10, 10, 0.62) 100%);
        border: 1px solid rgba(255, 215, 0, 0.55);
        border-top-color: rgba(255, 255, 255, 0.55);
        border-radius: 14px 14px 4px 14px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4), inset 0 1px 8px rgba(255, 215, 0, 0.22);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.45);
    }

    /* Thẻ chào khách chưa đăng nhập / yêu cầu VIP */
    #ai-coach-widget .msg-card {
        max-width: 100%;
        width: 100%;
        align-self: stretch;
        text-align: center;
        padding: 18px 16px;
        border-left-width: 1px;
        border-left-color: rgba(255, 215, 0, 0.28);
        border-radius: 16px;
        border-top: 1.5px solid rgba(255, 255, 255, 0.5);
    }
    .aic-medal {
        position: relative;
        width: 54px;
        height: 54px;
        margin: 0 auto 10px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: var(--aic-gold);
        background:
            radial-gradient(ellipse 70% 40% at 50% 14%, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0)),
            radial-gradient(circle at 50% 110%, rgba(255, 215, 0, 0.25), transparent 60%),
            rgba(92, 10, 10, 0.5);
        border: 2px solid rgba(255, 215, 0, 0.85);
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.4), inset 0 2px 6px rgba(255, 255, 255, 0.3);
    }
    .aic-card-title {
        display: block;
        font-family: "Texturina", serif;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--aic-gold);
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7), 0 0 10px rgba(255, 215, 0, 0.3);
    }
    .aic-card-text {
        margin: 6px 0 14px;
        font-size: 12.5px;
        line-height: 1.55;
        color: rgba(255, 242, 204, 0.85);
    }
    .aic-card-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .aic-inline-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }

    /* Gợi ý nâng cấp dưới câu trả lời */
    .aic-upsell {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid rgba(255, 215, 0, 0.25);
        font-size: 12px;
        color: rgba(255, 242, 204, 0.78);
    }
    .aic-upsell .fa-bolt {
        color: var(--aic-gold);
    }

    /* Đang suy nghĩ */
    .aic-typing {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        color: rgba(255, 242, 204, 0.85);
    }
    .aic-dots {
        display: inline-flex;
        gap: 4px;
    }
    .aic-dots i {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: radial-gradient(circle at 35% 30%, #fff2cc, var(--aic-gold) 60%, #b88600);
        box-shadow: 0 0 8px rgba(255, 215, 0, 0.7);
        animation: aicDot 1.1s ease-in-out infinite;
    }
    .aic-dots i:nth-child(2) { animation-delay: 0.15s; }
    .aic-dots i:nth-child(3) { animation-delay: 0.3s; }
    @keyframes aicDot {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.55; }
        30%           { transform: translateY(-5px); opacity: 1; }
    }

    /* --------------------------------------------------------------------------
       QUICK ACTIONS - viên thuốc kính
       -------------------------------------------------------------------------- */
    #ai-coach-widget .quick-actions-area {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 9px 12px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.07), rgba(0, 0, 0, 0.15));
        border-top: 1px solid rgba(255, 215, 0, 0.22);
        flex-shrink: 0;
    }
    #ai-coach-widget .quick-btn {
        appearance: none;
        -webkit-appearance: none;
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        color: var(--aic-gold-soft);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        background: var(--aic-gloss), rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 215, 0, 0.38);
        border-top-color: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        box-shadow: inset 0 1px 5px rgba(255, 255, 255, 0.12);
        cursor: pointer;
        transition: all 0.3s var(--aic-ease);
    }
    #ai-coach-widget .quick-btn .fas {
        color: var(--aic-gold);
        font-size: 11px;
    }
    #ai-coach-widget .quick-btn:hover {
        color: var(--aic-gold);
        background: var(--aic-gloss), rgba(255, 215, 0, 0.16);
        border-color: var(--aic-gold);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4), 0 0 14px rgba(255, 215, 0, 0.3), inset 0 1px 8px rgba(255, 255, 255, 0.2);
    }

    /* --------------------------------------------------------------------------
       FOOTER: input + send
       -------------------------------------------------------------------------- */
    #ai-coach-widget .chat-footer {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px 12px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(0, 0, 0, 0.22));
        border-top: 1px solid rgba(255, 215, 0, 0.3);
        flex-shrink: 0;
    }

    /* Ô nhập: kính kem vàng trong mờ, vẫn đọc rõ chữ tối */
    #ai-coach-widget #chat-input {
        flex: 1;
        min-width: 0;
        height: 40px;
        padding: 0 16px;
        border-radius: 999px;
        font-family: inherit;
        font-size: 13.5px;
        color: #2a1508;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.5) 0%, rgba(255, 255, 255, 0) 55%),
            rgba(255, 242, 204, 0.82) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.6) !important;
        box-shadow: inset 0 2px 5px rgba(74, 37, 17, 0.22), 0 2px 8px rgba(0, 0, 0, 0.25);
        transition: box-shadow 0.25s ease, border-color 0.25s ease;
    }
    #ai-coach-widget #chat-input::placeholder {
        color: rgba(74, 37, 17, 0.6);
    }
    #ai-coach-widget #chat-input:focus {
        outline: none;
        border-color: var(--aic-gold) !important;
        box-shadow: 0 0 0 0.22rem rgba(212, 175, 55, 0.35), 0 0 18px rgba(255, 215, 0, 0.4) !important;
    }

    #ai-coach-widget #send-chat {
        appearance: none;
        -webkit-appearance: none;
        position: relative;
        overflow: hidden;
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.65);
        font-size: 14px;
        color: var(--aic-red-deep);
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0) 52%),
            linear-gradient(145deg, rgba(255, 242, 204, 0.9) 0%, rgba(255, 215, 0, 0.88) 50%, rgba(201, 151, 10, 0.88) 100%);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.4), 0 0 14px rgba(255, 215, 0, 0.3), inset 0 1px 3px rgba(255, 255, 255, 0.9);
        cursor: pointer;
        transition: transform 0.3s var(--aic-ease), box-shadow 0.3s ease;
    }
    #ai-coach-widget #send-chat:hover {
        transform: scale(1.1) rotate(-8deg);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5), 0 0 22px rgba(255, 215, 0, 0.6), inset 0 1px 3px rgba(255, 255, 255, 1);
    }
    #ai-coach-widget #send-chat:active {
        transform: scale(0.95);
    }

    /* Footer cho khách */
    #ai-coach-widget .chat-footer-guest {
        justify-content: center;
        font-size: 12px;
        color: rgba(255, 242, 204, 0.85);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    }
    #ai-coach-widget .chat-footer-guest .fa-lock {
        color: var(--aic-gold);
    }
    #ai-coach-widget .chat-footer-guest a {
        color: var(--aic-gold);
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    #ai-coach-widget .chat-footer-guest a:hover {
        color: #fff;
    }

    /* --------------------------------------------------------------------------
       FALLBACK: trình duyệt không hỗ trợ backdrop-filter -> tăng độ đặc để dễ đọc
       -------------------------------------------------------------------------- */
    @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
        #ai-chat-box {
            background-color: rgba(16, 9, 9, 0.9);
        }
        #ai-coach-widget .msg-ai {
            background-color: rgba(30, 20, 20, 0.85);
        }
    }

    /* --------------------------------------------------------------------------
       RESPONSIVE + REDUCED MOTION
       -------------------------------------------------------------------------- */
    @media (max-width: 576px) {
        #ai-coach-widget {
            bottom: 16px;
            left: 14px;
        }
        #ai-chat-box {
            width: calc(100vw - 28px);
            height: min(540px, calc(100vh - 110px));
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #ai-chat-btn.is-attn::after,
        #ai-chat-box,
        #ai-coach-widget .msg,
        .aic-dots i {
            animation: none;
        }
        #ai-chat-btn,
        #close-chat,
        #ai-coach-widget .aic-btn,
        #ai-coach-widget .quick-btn,
        #ai-coach-widget #send-chat {
            transition: none;
        }
    }
</style>

<div id="ai-coach-widget">
    <div id="ai-chat-box" role="dialog" aria-label="{{ __('Trợ lý Cờ Tướng AI') }}">
        <div class="chat-header">
            <div class="aic-title">
                <span class="aic-avatar"><i class="fas fa-chess-knight"></i></span>
                <span class="aic-title-text">{{ __('Trợ lý Cờ Tướng AI') }}</span>
                @auth
                    @if(auth()->user()->isVip())
                        <span class="aic-vip-tag"><i class="fas fa-crown"></i> VIP</span>
                    @endif
                @endauth
            </div>
            <button type="button" id="close-chat" aria-label="{{ __('Đóng') }}"><i class="fas fa-times"></i></button>
        </div>

        @auth
            @if(!auth()->user()->isVip())
                <div class="ai-trial-bar">
                    <span><i class="fas fa-gift"></i> {{ __('Dùng thử miễn phí: 2 lượt/ngày') }}</span>
                    <a href="{{ localized_url('app.vip') ?? '/vip' }}" class="btn-upgrade-pill">
                        <i class="fas fa-crown"></i> {{ __('Nâng cấp VIP') }}
                    </a>
                </div>
            @endif
        @endauth

        <div class="chat-body" id="chat-messages" role="log" aria-live="polite">
            @guest
                <div class="msg msg-ai msg-card">
                    <div class="aic-medal"><i class="fas fa-chess-knight"></i></div>
                    <strong class="aic-card-title">{{ __('Xin chào bạn!') }}</strong>
                    <p class="aic-card-text">
                        {{ __('Vui lòng đăng nhập để sử dụng Trợ lý AI Coach phân tích thế trận và gợi ý nước đi.') }}
                    </p>
                    <div class="aic-card-actions">
                        <a href="{{ localized_url('login') }}?redirect={{ urlencode(url()->current()) }}" class="aic-btn aic-btn-ruby aic-btn-block">
                            <i class="fas fa-sign-in-alt"></i> {{ __('Đăng nhập ngay') }}
                        </a>
                        <a href="{{ localized_url('app.vip') ?? '/vip' }}" class="aic-btn aic-btn-ghost aic-btn-block">
                            <i class="fas fa-crown"></i> {{ __('Xem quyền lợi VIP') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="msg msg-ai">
                    @if(auth()->user()->isVip())
                        {{ __('Xin chào Kỳ thủ VIP! Tôi là Trợ lý AI. Bạn cần gợi ý nước đi hay đánh giá thế trận?') }}
                    @else
                        {{ __('Xin chào! Bạn có 2 lượt hỏi AI miễn phí mỗi ngày. Nâng cấp VIP để sử dụng không giới hạn.') }}
                    @endif
                </div>
            @endguest
        </div>

        @auth
            <div class="quick-actions-area">
                <button type="button" class="quick-btn" onclick="askAI('{{ __('Gợi ý nước đi') }}')">
                    <i class="fas fa-lightbulb"></i> {{ __('Gợi ý nước đi') }}
                </button>
                <button type="button" class="quick-btn" onclick="askAI('{{ __('Đánh giá thế cờ') }}')">
                    <i class="fas fa-balance-scale"></i> {{ __('Đánh giá thế cờ') }}
                </button>
                <button type="button" class="quick-btn" onclick="askAI('{{ __('Bên nào đang ưu thế?') }}')">
                    <i class="fas fa-flag"></i> {{ __('Bên nào đang ưu thế?') }}
                </button>
            </div>

            <div class="chat-footer">
                <input type="text" id="chat-input" placeholder="{{ __('Nhập câu hỏi...') }}" autocomplete="off" aria-label="{{ __('Nhập câu hỏi...') }}">
                <button type="button" id="send-chat" aria-label="{{ __('Gửi') }}"><i class="fas fa-paper-plane"></i></button>
            </div>
        @else
            <div class="chat-footer chat-footer-guest">
                <span><i class="fas fa-lock"></i> <a href="{{ localized_url('login') }}?redirect={{ urlencode(url()->current()) }}">{{ __('Đăng nhập') }}</a> {{ __('để mở khóa chat') }}</span>
            </div>
        @endauth
    </div>

    <button type="button" id="ai-chat-btn" class="is-attn" title="{{ __('Trợ lý AI Coach') }}" aria-label="{{ __('Trợ lý AI Coach') }}" aria-expanded="false">
        <span class="aic-orb-sheen"></span>
        <i class="fas fa-chess-knight aic-orb-icon"></i>
        <span class="ai-btn-badge">AI</span>
    </button>
</div>

<script>
    (function() {
        const isUserAuth = {{ auth()->check() ? 'true' : 'false' }};
        const isUserVip = {{ (auth()->check() && auth()->user()->isVip()) ? 'true' : 'false' }};
        const vipUpgradeUrl = '{{ localized_url("app.vip") ?? "/vip" }}';
        const loginRedirectUrl = '{{ localized_url("login") }}?redirect=' + encodeURIComponent(window.location.href);

        const chatBox = document.getElementById('ai-chat-box');
        const chatBtn = document.getElementById('ai-chat-btn');
        const closeChat = document.getElementById('close-chat');
        const messagesContainer = document.getElementById('chat-messages');

        if (!chatBtn || !chatBox) return;

        function setOpen(open) {
            chatBox.style.display = open ? 'flex' : 'none';
            chatBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                chatBtn.classList.remove('is-attn'); // dừng vòng xung sau lần mở đầu tiên
                const inputEl = document.getElementById('chat-input');
                if (inputEl) inputEl.focus();
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        chatBtn.addEventListener('click', () => setOpen(chatBox.style.display !== 'flex'));

        if (closeChat) {
            closeChat.addEventListener('click', () => setOpen(false));
        }

        const widgetRoot = document.getElementById('ai-coach-widget');
        const isOpen = () => chatBox.style.display === 'flex';

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isOpen()) setOpen(false);
        });

        // Bấm ra ngoài widget thì đóng chat.
        // Dùng pointerdown (chạy trước click) + composedPath() để vẫn nhận đúng khi
        // phần tử bên trong bị gỡ khỏi DOM ngay sau đó (vd: tin nhắn "đang suy nghĩ").
        document.addEventListener('pointerdown', (e) => {
            if (!isOpen()) return;
            const path = e.composedPath ? e.composedPath() : [];
            const inside = path.length ? path.includes(widgetRoot) : widgetRoot.contains(e.target);
            if (!inside) setOpen(false);
        });

        const sendBtn = document.getElementById('send-chat');
        const inputField = document.getElementById('chat-input');

        if (sendBtn && inputField) {
            sendBtn.addEventListener('click', () => {
                const msg = inputField.value.trim();
                if (msg) {
                    askAI(msg);
                    inputField.value = '';
                }
            });

            inputField.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendBtn.click();
            });
        }

        window.askAI = async function(message) {
            if (!isUserAuth) {
                appendMessage(
                    '⚠️ {{ __("Vui lòng đăng nhập để tiếp tục trò chuyện.") }}' +
                    '<div class="aic-inline-actions">' +
                        '<a href="' + loginRedirectUrl + '" class="aic-btn aic-btn-ruby"><i class="fas fa-sign-in-alt"></i> {{ __("Đăng nhập ngay") }}</a>' +
                        '<a href="' + vipUpgradeUrl + '" class="aic-btn aic-btn-ghost"><i class="fas fa-crown"></i> {{ __("Gói VIP") }}</a>' +
                    '</div>',
                    'ai'
                );
                return;
            }

            appendMessage(escapeHtml(message), 'user');

            if (typeof game === 'undefined') {
                appendMessage("⚠️ {{ __('Không tìm thấy bàn cờ.') }}", 'ai');
                return;
            }

            const currentFen = game.fen();
            const loadingId = appendMessage(
                '<span class="aic-typing"><span class="aic-dots"><i></i><i></i><i></i></span> {{ __("Đang suy nghĩ...") }}</span>',
                'ai'
            );

            try {
                const response = await fetch('/api/chess/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        fen: currentFen,
                        message: message
                    })
                });
                const data = await response.json();
                removeMessage(loadingId);

                if (data.success) {
                    let replyHtml = data.reply;
                    if (!isUserVip) {
                        replyHtml += '<div class="aic-upsell"><i class="fas fa-bolt"></i><span><a href="' + vipUpgradeUrl + '">{{ __("Nâng cấp VIP (Chỉ 19k)") }}</a> {{ __("để hỏi AI không giới hạn!") }}</span></div>';
                    }
                    appendMessage(replyHtml, 'ai');
                } else if (data.require_vip) {
                    appendMessage(
                        '<div class="aic-medal"><i class="fas fa-crown"></i></div>' +
                        '<strong class="aic-card-title" style="font-size:14px;">' + data.reply + '</strong>' +
                        '<div class="aic-card-actions" style="margin-top:12px;">' +
                            '<a href="' + vipUpgradeUrl + '" class="aic-btn aic-btn-gold aic-btn-block"><i class="fas fa-bolt"></i> {{ __("Nâng cấp VIP (Chỉ 19k)") }}</a>' +
                        '</div>',
                        'ai',
                        'msg-card'
                    );
                } else if (data.require_login) {
                    appendMessage(
                        data.reply +
                        '<div class="aic-inline-actions"><a href="' + loginRedirectUrl + '" class="aic-btn aic-btn-ruby"><i class="fas fa-sign-in-alt"></i> {{ __("Đăng nhập") }}</a></div>',
                        'ai'
                    );
                } else {
                    appendMessage(data.reply || "{{ __('Xin lỗi, tôi gặp chút trục trặc khi kết nối.') }}", 'ai');
                }
            } catch (error) {
                removeMessage(loadingId);
                appendMessage("{{ __('Lỗi kết nối server.') }}", 'ai');
                console.error(error);
            }
        };

        function escapeHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function appendMessage(html, sender, extraClass) {
            const div = document.createElement('div');
            div.className = `msg msg-${sender}` + (extraClass ? ' ' + extraClass : '');
            div.innerHTML = html;
            div.id = 'msg-' + Date.now() + '-' + Math.random().toString(36).substr(2, 4);
            messagesContainer.appendChild(div);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            return div.id;
        }

        function removeMessage(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }
    })();
</script>
