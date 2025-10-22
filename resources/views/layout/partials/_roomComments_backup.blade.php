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
    </style>

    <div class="room-comment-wrapper">
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