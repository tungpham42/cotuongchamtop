<style>
    /* --- Red & Black Xiangqi Theme --- */
    :root {
        --xq-red: #b71c1c;       /* Deep Red */
        --xq-black: #1a1a1a;     /* Soft Black */
        --xq-gray: #f5f5f5;      /* Background Gray */
        --xq-text-on-red: #fff;
    }

    /* Widget Container */
    #ai-coach-widget {
        position: fixed;
        bottom: 20px;
        right: 80px;
        z-index: 1050;
        font-family: "Roboto", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    /* Floating Toggle Button */
    #ai-chat-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: var(--xq-red);
        color: white;
        border: 2px solid var(--xq-black);
        box-shadow: 0 4px 15px rgba(183, 28, 28, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    #ai-chat-btn:hover {
        transform: scale(1.1);
        background-color: #d32f2f;
        box-shadow: 0 6px 20px rgba(0,0,0,0.4);
    }

    /* Main Chat Box */
    #ai-chat-box {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 360px;
        height: 500px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        display: none; /* Default hidden */
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #ddd;
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
        padding: 15px;
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
        background-size: 20px 20px; /* Subtle dots pattern */
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
        max-width: 85%;
        padding: 10px 14px;
        font-size: 14px;
        line-height: 1.5;
        position: relative;
    }

    /* AI Message: Light with Black Accent */
    .msg-ai {
        background: #f1f2f6;
        color: #333;
        align-self: flex-start;
        border-radius: 12px 12px 12px 0;
        border-left: 4px solid var(--xq-black);
    }

    /* User Message: Red */
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
        padding: 10px;
    }

    .quick-btn {
        font-size: 12px;
        margin: 0 5px 5px 0;
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
    }
    #chat-input:focus {
        border-color: var(--xq-red);
        box-shadow: 0 0 0 0.2rem rgba(183, 28, 28, 0.25);
    }

    #send-chat {
        background: var(--xq-black);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 8px;
        color: white;
        transition: background 0.2s;
    }
    #send-chat:hover {
        background: var(--xq-red);
    }
</style>

<div id="ai-coach-widget">
    <div id="ai-chat-box">
        <div class="chat-header">
            <span><i class="fas fa-chess-knight me-2"></i>Trợ lý Cờ Tướng</span>
            <button type="button" class="btn-close btn-close-white shadow-none" id="close-chat" style="opacity: 0.8;"></button>
        </div>

        <div class="chat-body" id="chat-messages">
            <div class="msg msg-ai">
                <i class="fas fa-robot text-danger me-1"></i> Xin chào! Tôi là AI Coach. Bạn cần gợi ý nước đi hay phân tích {{ __("thế cờ") }}?
            </div>
        </div>

        <div class="quick-actions-area">
            <div class="d-flex flex-wrap">
                <button class="btn btn-sm quick-btn" onclick="askAI('Gợi ý nước đi tiếp theo')">
                    <i class="fas fa-lightbulb text-warning"></i> Gợi ý nước đi
                </button>
                <button class="btn btn-sm quick-btn" onclick="askAI('Đánh giá {{ __("thế cờ") }} này')">
                    <i class="fas fa-balance-scale text-primary"></i> Đánh giá
                </button>
                <button class="btn btn-sm quick-btn" onclick="askAI('Bên nào đang ưu thế?')">
                    <i class="fas fa-flag text-danger"></i> Ai đang thắng?
                </button>
            </div>
        </div>

        <div class="chat-footer">
            <input type="text" id="chat-input" class="form-control" placeholder="Nhập câu hỏi..." autocomplete="off">
            <button id="send-chat" class="shadow-sm"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <div id="ai-chat-btn" title="Chat với AI">
        <i class="fas fa-comments"></i>
    </div>
</div>

<script>
    // Toggle Chat Box
    const chatBox = document.getElementById('ai-chat-box');
    const chatBtn = document.getElementById('ai-chat-btn');
    const closeChat = document.getElementById('close-chat');
    const messagesContainer = document.getElementById('chat-messages');

    chatBtn.addEventListener('click', () => {
        if (chatBox.style.display === 'flex') {
            chatBox.style.display = 'none';
        } else {
            chatBox.style.display = 'flex';
            document.getElementById('chat-input').focus();
        }
    });

    closeChat.addEventListener('click', () => chatBox.style.display = 'none');

    // Gửi tin nhắn
    document.getElementById('send-chat').addEventListener('click', () => {
        const input = document.getElementById('chat-input');
        const msg = input.value.trim();
        if(msg) {
            askAI(msg);
            input.value = '';
        }
    });

    document.getElementById('chat-input').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') document.getElementById('send-chat').click();
    });

    async function askAI(message) {
        // 1. Hiển thị tin nhắn người dùng
        appendMessage(message, 'user');

        // 2. Kiểm tra FEN
        if (typeof game === 'undefined') {
            appendMessage("⚠️ Lỗi: Không tìm thấy bàn cờ (Game object undefined).", 'ai');
            return;
        }
        const currentFen = game.fen();

        // 3. Hiển thị loading
        const loadingId = appendMessage('<i class="fas fa-circle-notch fa-spin text-danger"></i> Đang suy nghĩ...', 'ai');

        try {
            // 4. Gọi API
            const response = await fetch('/api/chess/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    fen: currentFen,
                    message: message
                })
            });
            const data = await response.json();

            // 5. Cập nhật câu trả lời
            removeMessage(loadingId); // Xóa loading
            if(data.success) {
                appendMessage(data.reply, 'ai');
            } else {
                appendMessage("Xin lỗi, tôi gặp chút trục trặc khi kết nối.", 'ai');
            }

        } catch (error) {
            removeMessage(loadingId);
            appendMessage("Lỗi kết nối server.", 'ai');
            console.error(error);
        }
    }

    function appendMessage(html, sender) {
        const div = document.createElement('div');
        div.className = `msg msg-${sender}`;
        div.innerHTML = html;
        div.id = 'msg-' + Date.now();
        messagesContainer.appendChild(div);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        return div.id;
    }

    function removeMessage(id) {
        const el = document.getElementById(id);
        if(el) el.remove();
    }
</script>
