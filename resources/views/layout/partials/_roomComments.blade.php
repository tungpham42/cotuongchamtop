@php
$defaults = [
    'panel_title' => 'Bình luận trận đấu',
    'form_description' => null,
    'author_label' => 'Tên bạn (không bắt buộc)',
    'author_placeholder' => 'Ví dụ: Kỳ thủ A',
    'content_label' => 'Nội dung',
    'content_placeholder' => 'Chia sẻ cảm nhận hoặc bình luận về ván đấu này...',
    'submit_label' => 'Gửi bình luận',
    'sending_label' => 'Đang gửi...',
    'feed_title' => 'Dòng thảo luận',
    'empty_state' => 'Chưa có bình luận nào. Hãy là người đầu tiên chia sẻ!',
    'like_label' => 'Thích',
    'reply_label' => 'Trả lời',
    'reply_placeholder' => 'Phản hồi của bạn...',
    'reply_submit' => 'Gửi',
    'reply_sending' => 'Đang gửi...',
    'reply_cancel' => 'Hủy',
    'success_submit' => 'Cảm ơn bạn! Bình luận đã được gửi.',
    'error_load' => 'Không thể tải bình luận. Vui lòng thử lại sau.',
    'error_submit' => 'Không thể gửi bình luận, vui lòng thử lại.',
    'error_reply' => 'Không thể gửi phản hồi, vui lòng thử lại.',
    'error_like' => 'Không thể thích bình luận, vui lòng thử lại.',
    'error_content_required' => 'Vui lòng nhập nội dung bình luận.',
    'error_reply_required' => 'Vui lòng nhập nội dung phản hồi.',
    'anonymous' => 'Ẩn danh',
    'date_locale' => 'vi-VN',
    'bootbox_locale' => 'vi',
];

$strings = array_merge($defaults, $translations ?? []);
@endphp

@if (!empty($roomCode ?? null))
    <style>
    .room-comment-wrapper {
        background-color: rgba(23, 25, 27, 0.85);
        border-radius: 12px;
        padding: 16px;
        color: #f8f9fa;
        margin-bottom: 20px;
    }
    
    .social-share-section {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 16px;
    }
    
    .social-share-buttons .btn {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
    }
    
    .btn-facebook {
        background-color: #1877f2;
        border-color: #1877f2;
        color: white;
    }
    .btn-facebook:hover {
        background-color: #166fe5;
        border-color: #166fe5;
        color: white;
    }
    
    .btn-twitter {
        background-color: #000000;
        border-color: #000000;
        color: white;
        font-size: 18px;
        font-weight: bold;
    }
    .btn-twitter:hover {
        background-color: #333333;
        border-color: #333333;
        color: white;
        transform: scale(1.05);
    }
    
    .btn-zalo {
        background-color: #0068ff;
        border-color: #0068ff;
        color: white;
    }
    .btn-zalo:hover {
        background-color: #0056d6;
        border-color: #0056d6;
        color: white;
    }
    
    .btn-telegram {
        background-color: #0088cc;
        border-color: #0088cc;
        color: white;
    }
    .btn-telegram:hover {
        background-color: #0077b3;
        border-color: #0077b3;
        color: white;
    }
    
    .btn-copy {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }
    .btn-copy:hover {
        background-color: #5a6268;
        border-color: #5a6268;
        color: white;
    }
    
    .btn-copy.copied {
        background-color: #28a745;
        border-color: #28a745;
    }
    </style>

    <div class="room-comment-wrapper">
        <!-- Social Share Buttons -->
        <div class="social-share-section mb-3">
            <h6 class="mb-2"><i class="fas fa-share-alt text-primary"></i> Chia sẻ</h6>
            <div class="social-share-buttons d-flex gap-2">
                <button type="button" class="btn btn-facebook" onclick="shareToFacebook()" title="Chia sẻ lên Facebook" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="fab fa-facebook-f"></i>
                </button>
                <button type="button" class="btn btn-twitter" onclick="shareToTwitter()" title="Chia sẻ lên X (Twitter)" data-bs-toggle="tooltip" data-bs-placement="top">
                    <strong>𝕏</strong>
                </button>
                <button type="button" class="btn btn-zalo" onclick="shareToZalo()" title="Chia sẻ qua Zalo" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="fas fa-comments"></i>
                </button>
                <button type="button" class="btn btn-telegram" onclick="shareToTelegram()" title="Chia sẻ qua Telegram" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="fab fa-telegram-plane"></i>
                </button>
                <button type="button" class="btn btn-copy" onclick="copyToClipboard()" title="Copy link" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>

        <div class="room-comment-form-card">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-comments text-danger"></i> {{ $strings['panel_title'] }}</h5>
                <form id="room-comment-form" autocomplete="off">
                    <div class="form-group">
                        <label for="room_comment_author">{{ $strings['author_label'] }}</label>
                        <input type="text" class="form-control" id="room_comment_author" maxlength="120" placeholder="{{ $strings['author_placeholder'] }}">
                    </div>
                    <div class="form-group">
                        <label for="room_comment_content">{{ $strings['content_label'] }}</label>
                        <textarea class="form-control" id="room_comment_content" rows="3" maxlength="1000" placeholder="{{ $strings['content_placeholder'] }}" required></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="d-none text-danger" id="room-comment-feedback"></small>
                        <button type="submit" class="btn btn-danger" id="room-comment-submit"><i class="fas fa-paper-plane"></i> {{ $strings['submit_label'] }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="room-comment-feed">
            <h6 class="mb-3"><i class="fas fa-comment-dots text-danger"></i> {{ $strings['feed_title'] }}</h6>
            <div id="room-comment-list" class="room-comments"></div>
        </div>
    </div>

    <script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        // Bootstrap 5 tooltip initialization
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        // Fallback for Bootstrap 4 or jQuery
        else if (typeof $ !== 'undefined' && $.fn.tooltip) {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Social Share Functions
    function getCurrentPageUrl() {
        return window.location.href;
    }
    
    function getShareTitle() {
        return document.title || 'Ván cờ tướng hay - CoTuongChamTop.com';
    }
    
    function shareToFacebook() {
        const url = encodeURIComponent(getCurrentPageUrl());
        const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        window.open(shareUrl, 'facebook-share', 'width=600,height=400,scrollbars=yes,resizable=yes');
        
        // Analytics tracking (nếu có)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'share', {
                method: 'facebook',
                content_type: 'xiangqi_game',
                item_id: '{{ $roomCode }}'
            });
        }
    }
    
    function shareToTwitter() {
        const url = encodeURIComponent(getCurrentPageUrl());
        const text = encodeURIComponent(getShareTitle() + ' #cờtướng #xiangqi');
        const shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
        window.open(shareUrl, 'x-share', 'width=600,height=400,scrollbars=yes,resizable=yes');
        
        if (typeof gtag !== 'undefined') {
            gtag('event', 'share', {
                method: 'x_twitter',
                content_type: 'xiangqi_game',
                item_id: '{{ $roomCode }}'
            });
        }
    }
    
    function shareToZalo() {
        const url = encodeURIComponent(getCurrentPageUrl());
        // Zalo share API (nếu có app) hoặc fallback to copy
        if (navigator.share && /mobile|android|iphone|ipad/i.test(navigator.userAgent)) {
            navigator.share({
                title: getShareTitle(),
                url: getCurrentPageUrl()
            }).catch(() => {
                copyToClipboard();
            });
        } else {
            // Fallback: copy to clipboard
            copyToClipboard();
        }
        
        if (typeof gtag !== 'undefined') {
            gtag('event', 'share', {
                method: 'zalo',
                content_type: 'xiangqi_game',
                item_id: '{{ $roomCode }}'
            });
        }
    }
    
    function shareToTelegram() {
        const url = encodeURIComponent(getCurrentPageUrl());
        const text = encodeURIComponent(getShareTitle());
        const shareUrl = `https://t.me/share/url?url=${url}&text=${text}`;
        window.open(shareUrl, 'telegram-share', 'width=600,height=400,scrollbars=yes,resizable=yes');
        
        if (typeof gtag !== 'undefined') {
            gtag('event', 'share', {
                method: 'telegram',
                content_type: 'xiangqi_game',
                item_id: '{{ $roomCode }}'
            });
        }
    }
    
    function copyToClipboard() {
        const url = getCurrentPageUrl();
        const button = event.target.closest('.btn-copy');
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(() => {
                showCopySuccess(button);
            }).catch(() => {
                fallbackCopyTextToClipboard(url, button);
            });
        } else {
            fallbackCopyTextToClipboard(url, button);
        }
        
        if (typeof gtag !== 'undefined') {
            gtag('event', 'share', {
                method: 'copy_link',
                content_type: 'xiangqi_game',
                item_id: '{{ $roomCode }}'
            });
        }
    }
    
    function fallbackCopyTextToClipboard(text, button) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess(button);
        } catch (err) {
            console.error('Fallback: Could not copy text: ', err);
            alert('Không thể copy link. Vui lòng copy thủ công: ' + text);
        }
        
        document.body.removeChild(textArea);
    }
    
    function showCopySuccess(button) {
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.add('copied');
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('copied');
        }, 2000);
    }

    window.__ROOM_COMMENT_CONFIG__ = {
        code: '{{ $roomCode }}',
        csrfToken: '{{ csrf_token() }}',
        endpoints: {
            list: '{{ url('/api/rooms/' . $roomCode . '/comments') }}',
            store: '{{ url('/api/rooms/' . $roomCode . '/comments') }}',
            likeBase: '{{ url('/api/rooms/' . $roomCode . '/comments') }}'
        },
        storageKey: 'room_comment_likes_{{ $roomCode }}',
        texts: {
            'anonymous': '{{ $strings['anonymous'] }}',
            'likeLabel': '{{ $strings['like_label'] }}',
            'replyLabel': '{{ $strings['reply_label'] }}',
            'replyPlaceholder': '{{ $strings['reply_placeholder'] }}',
            'replySubmit': '{{ $strings['reply_submit'] }}',
            'replySending': '{{ $strings['reply_sending'] }}',
            'replyCancel': '{{ $strings['reply_cancel'] }}',
            'successSubmit': '{{ $strings['success_submit'] }}',
            'errorLoad': '{{ $strings['error_load'] }}',
            'errorSubmit': '{{ $strings['error_submit'] }}',
            'errorReply': '{{ $strings['error_reply'] }}',
            'errorLike': '{{ $strings['error_like'] }}',
            'errorContentRequired': '{{ $strings['error_content_required'] }}',
            'errorReplyRequired': '{{ $strings['error_reply_required'] }}',
            'emptyState': '{{ $strings['empty_state'] }}',
            'sendingLabel': '{{ $strings['sending_label'] }}',
            'feedTitle': '{{ $strings['feed_title'] }}',
            'panelTitle': '{{ $strings['panel_title'] }}',
            'authorLabel': '{{ $strings['author_label'] }}',
            'authorPlaceholder': '{{ $strings['author_placeholder'] }}',
            'contentLabel': '{{ $strings['content_label'] }}',
            'contentPlaceholder': '{{ $strings['content_placeholder'] }}',
            'submitLabel': '{{ $strings['submit_label'] }}'
        },
        locale: '{{ $strings['date_locale'] }}',
        bootboxLocale: '{{ $strings['bootbox_locale'] }}'
    };
    </script>
    <script src="{{ asset('js/room-comments.js') }}" defer></script>
@endif