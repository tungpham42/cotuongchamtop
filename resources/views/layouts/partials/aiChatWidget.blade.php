<style>
    /* --- Red & Black Xiangqi Theme --- */
    :root {
        --xq-red: #b71c1c;       /* Deep Red */
        --xq-black: #1a1a1a;     /* Soft Black */
        --xq-gray: #f5f5f5;      /* Background Gray */
        --xq-text-on-red: #fff;
    }

    /* Widget Container - Đặt ở góc phải bên trên nút Zalo (bottom: 90px; right: 20px), z-index: 1060 để không bị badge online đè */
    #ai-coach-widget {
        position: fixed;
        bottom: 90px;
        right: 20px;
        z-index: 1060;
        font-family: "Roboto", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    /* Floating Toggle Button */
    #ai-chat-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #b71c1c 0%, #880e4f 100%);
        color: white;
        border: 2px solid #ffd700;
        box-shadow: 0 4px 18px rgba(183, 28, 28, 0.5), 0 0 10px rgba(255, 215, 0, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }
    #ai-chat-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 22px rgba(183, 28, 28, 0.7), 0 0 15px rgba(255, 215, 0, 0.5);
    }
    .ai-btn-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: #ffd700;
        color: #0b0c10;
        font-size: 10px;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        letter-spacing: 0.5px;
    }

    /* Main Chat Box */
    #ai-chat-box {
        position: absolute;
        bottom: 75px;
        right: 0;
        width: 360px;
        height: 500px;
        background: white;
        border-radius: 14px;
        box-shadow: 0 12px 35px rgba(0,0,0,0.4);
        display: none; /* Default hidden */
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #d4af37;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Header: Black to Red Gradient */
    .chat-header {
        background: linear-gradient(135deg, var(--xq-black) 0%, var(--xq-red) 100%);
        color: white;
        padding: 12px 15px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #880e4f;
    }

    .chat-body {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background-color: #fff;
        background-image: radial-gradient(#e0e0e0 1px, transparent 1px);
        background-size: 20px 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* Footer */
    .chat-footer {
        padding: 10px 15px;
        border-top: 1px solid #eee;
        background: white;
        display: flex;
        align-items: center;
    }

    /* Messages */
    .msg {
        max-width: 88%;
        padding: 10px 14px;
        font-size: 13.5px;
        line-height: 1.5;
        position: relative;
    }

    .msg-ai {
        background: #f1f2f6;
        color: #333;
        align-self: flex-start;
        border-radius: 12px 12px 12px 0;
        border-left: 4px solid var(--xq-red);
    }

    .msg-user {
        background: var(--xq-red);
        color: var(--xq-text-on-red);
        align-self: flex-end;
        border-radius: 12px 12px 0 12px;
        box-shadow: 0 2px 5px rgba(183, 28, 28, 0.2);
    }

    /* Quick Actions Area */
    .quick-actions-area {
        background: #fff;
        border-top: 1px solid #eee;
        padding: 8px 10px;
    }

    .quick-btn {
        font-size: 12px;
        margin: 0 4px 4px 0;
        border-radius: 20px;
        transition: all 0.2s;
        border: 1px solid #ddd;
        background: white;
        color: #333;
    }
    .quick-btn:hover {
        border-color: var(--xq-red);
        color: var(--xq-red);
        background: #fff5f5;
    }

    /* Input & Send Button */
    #chat-input {
        border-radius: 20px;
        border: 1px solid #ddd;
        font-size: 13.5px;
    }
    #chat-input:focus {
        border-color: var(--xq-red);
        box-shadow: 0 0 0 0.2rem rgba(183, 28, 28, 0.25);
    }

    #send-chat {
        background: var(--xq-black);
        border: none;
        border-radius: 50%;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 8px;
        color: white;
        transition: background 0.2s;
        flex-shrink: 0;
    }
    #send-chat:hover {
        background: var(--xq-red);
    }

    @media (max-width: 576px) {
        #ai-coach-widget {
            bottom: 80px;
            right: 15px;
        }
        #ai-chat-box {
            width: calc(100vw - 30px);
            right: -5px;
            height: 460px;
        }
    }
</style>

<div id="ai-coach-widget">
    <div id="ai-chat-box">
        <div class="chat-header">
            <span>
                <i class="fas fa-chess-knight me-1 text-warning"></i>
                {{ __('Trợ lý Cờ Tướng AI') }}
                @auth
                    @if(auth()->user()->isVip())
                        <span class="badge badge-warning text-dark ml-1" style="font-size: 10px;"><i class="fas fa-crown"></i> VIP</span>
                    @endif
                @endauth
            </span>
            <button type="button" class="btn-close btn-close-white shadow-none text-white border-0 bg-transparent font-weight-bold" id="close-chat" style="font-size: 18px; line-height: 1;">&times;</button>
        </div>

        <div class="chat-body" id="chat-messages">
            @guest
                <div class="msg msg-ai text-center p-3">
                    <i class="fas fa-robot text-danger fa-2x mb-2 d-block"></i>
                    <strong>{{ __('Xin chào bạn!') }}</strong>
                    <p class="small text-muted mt-1 mb-3">
                        {{ __('Vui lòng đăng nhập để sử dụng Trợ lý AI Coach phân tích thế trận và gợi ý nước đi.') }}
                    </p>
                    <a href="{{ localized_url('login') }}?redirect={{ urlencode(url()->current()) }}" class="btn btn-danger btn-sm btn-block text-white font-weight-bold" style="border-radius: 20px;">
                        <i class="fas fa-sign-in-alt"></i> {{ __('Đăng nhập ngay') }}
                    </a>
                </div>
            @else
                <div class="msg msg-ai">
                    <i class="fas fa-robot text-danger me-1"></i>
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
                <div class="d-flex flex-wrap">
                    <button class="btn btn-sm quick-btn" onclick="askAI('{{ __('Gợi ý nước đi') }}')">
                        <i class="fas fa-lightbulb text-warning"></i> {{ __('Gợi ý nước đi') }}
                    </button>
                    <button class="btn btn-sm quick-btn" onclick="askAI('{{ __('Đánh giá thế cờ') }}')">
                        <i class="fas fa-balance-scale text-primary"></i> {{ __('Đánh giá thế cờ') }}
                    </button>
                    <button class="btn btn-sm quick-btn" onclick="askAI('{{ __('Bên nào đang ưu thế?') }}')">
                        <i class="fas fa-flag text-danger"></i> {{ __('Bên nào đang ưu thế?') }}
                    </button>
                </div>
            </div>

            <div class="chat-footer">
                <input type="text" id="chat-input" class="form-control" placeholder="{{ __('Nhập câu hỏi...') }}" autocomplete="off">
                <button id="send-chat" class="shadow-sm"><i class="fas fa-paper-plane"></i></button>
            </div>
        @else
            <div class="chat-footer text-center justify-content-center p-2 bg-light">
                <small class="text-muted"><i class="fas fa-lock"></i> <a href="{{ localized_url('login') }}?redirect={{ urlencode(url()->current()) }}" class="text-danger font-weight-bold">{{ __('Đăng nhập') }}</a> {{ __('để mở khóa chat') }}</small>
            </div>
        @endauth
    </div>

    <div id="ai-chat-btn" title="{{ __('Trợ lý AI Coach') }}">
        <i class="fas fa-robot"></i>
        <span class="ai-btn-badge">AI</span>
    </div>
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

        chatBtn.addEventListener('click', () => {
            if (chatBox.style.display === 'flex') {
                chatBox.style.display = 'none';
            } else {
                chatBox.style.display = 'flex';
                const inputEl = document.getElementById('chat-input');
                if (inputEl) inputEl.focus();
            }
        });

        if (closeChat) {
            closeChat.addEventListener('click', () => chatBox.style.display = 'none');
        }

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
                    '⚠️ {{ __("Vui lòng đăng nhập để tiếp tục trò chuyện.") }} <br><a href="' + loginRedirectUrl + '" class="btn btn-sm btn-danger mt-2 font-weight-bold"><i class="fas fa-sign-in-alt"></i> {{ __("Đăng nhập ngay") }}</a>',
                    'ai'
                );
                return;
            }

            appendMessage(message, 'user');

            if (typeof game === 'undefined') {
                appendMessage("⚠️ {{ __('Không tìm thấy bàn cờ.') }}", 'ai');
                return;
            }

            const currentFen = game.fen();
            const loadingId = appendMessage('<i class="fas fa-circle-notch fa-spin text-danger"></i> {{ __("Đang suy nghĩ...") }}', 'ai');

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
                    appendMessage(data.reply, 'ai');
                } else if (data.require_vip) {
                    appendMessage(
                        '<div class="text-center p-2"><i class="fas fa-crown text-warning fa-2x mb-2 d-block"></i><strong>' + data.reply + '</strong><br><a href="' + vipUpgradeUrl + '" class="btn btn-warning btn-sm font-weight-bold text-dark mt-2 shadow" style="border-radius: 20px;"><i class="fas fa-bolt"></i> {{ __("Nâng cấp VIP (Chỉ 19k)") }}</a></div>',
                        'ai'
                    );
                } else if (data.require_login) {
                    appendMessage(
                        data.reply + '<br><a href="' + loginRedirectUrl + '" class="btn btn-danger btn-sm font-weight-bold mt-2"><i class="fas fa-sign-in-alt"></i> {{ __("Đăng nhập") }}</a>',
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

        function appendMessage(html, sender) {
            const div = document.createElement('div');
            div.className = `msg msg-${sender}`;
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
