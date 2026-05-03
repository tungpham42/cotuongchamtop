@php
$locale = app()->getLocale();
$sessionPrefix = 'CoTuong_VI-';
$folder = 'phongChatLog';
$suffix = '-phongchatlog.html';
$apiEndpoint = '/dangChat';

if ($locale === 'en') {
    $sessionPrefix = 'CoTuong_EN-';
    $folder = 'roomChatLog';
    $suffix = '-roomchatlog.html';
    $apiEndpoint = '/postChat';
} elseif ($locale === 'ja') {
    $sessionPrefix = 'CoTuong_JA-';
    $folder = 'rumuChatLog';
    $suffix = '-rumuchatlog.html';
    $apiEndpoint = '/postChatJa';
} elseif ($locale === 'ko') {
    $sessionPrefix = 'CoTuong_KO-';
    $folder = 'bangChatLog';
    $suffix = '-bangchatlog.html';
    $apiEndpoint = '/postChatKo';
} elseif ($locale === 'zh') {
    $sessionPrefix = 'CoTuong_ZH-';
    $folder = 'fangjianChatLog';
    $suffix = '-fangjianchatlog.html';
    $apiEndpoint = '/postChatZh';
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($sessionPrefix . $roomCode);
    session_start();
}

$room_path = public_path() . '/' . $folder . '/' . $roomCode . $suffix;
$log_path = url('/') . '/' . $folder . '/' . $roomCode . $suffix;

if (!is_file($room_path)) {
    $welcome_message = "<div class='msgln system-msg'><span class='chat-time'>".date("H:i")."</span> <span class='welcome-info'>👋 ".__('Phòng được tạo')."</span></div>\n";
    file_put_contents($room_path, $welcome_message);
}

if (isset($_GET['logout'])) {
    if (isset($_SESSION['name'])) {
        $logout_message = "<div class='msgln system-msg'><span class='chat-time'>".date("H:i")."</span> <span class='left-info'>".__('Người dùng')." <b>". $_SESSION['name'] ."</b> ".__('đã rời phòng chat.')."</span></div>\n";
        file_put_contents($room_path, $logout_message, FILE_APPEND | LOCK_EX);
        $_SESSION = [];
        setcookie('cotuong_name', '', time() - 3600, "/");
    }
}

if (isset($_POST['enter'])) {
    if ($_POST['name'] != "") {
        $_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
        setcookie('cotuong_name', $_SESSION['name'], time() + (86400 * 30), "/");
        $login_message = "<div class='msgln system-msg'><span class='chat-time'>".date("H:i")."</span> <span class='enter-info'>".__('Người dùng')." <b>". $_SESSION['name'] ."</b> ".__('đã vào phòng chat.')."</span></div>\n";
        file_put_contents($room_path, $login_message, FILE_APPEND | LOCK_EX);
    }
}
@endphp

<style>
/* Modern Chatbox Styles */
#chat-wrapper {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 400px;
    height: 520px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    overflow: hidden;
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    float: left;
    border: 1px solid #e5e7eb;
}

/* Login Form */
#loginform {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100%;
    padding: 2rem;
    text-align: center;
    background: #f9fafb;
}
#loginform p {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1.5rem;
}
#loginform form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
#loginform label {
    display: none;
}
#name {
    width: 100%;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    padding: 10px 14px;
    font-size: 15px;
    transition: all 0.3s ease;
    outline: none;
}
#name:focus {
    border-color: #E94125;
    box-shadow: 0 0 0 3px rgba(233, 65, 37, 0.2);
}
#enter {
    background: #E94125;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s;
}
#enter:hover {
    background: #C8351C;
}
.error {
    color: #ef4444;
    font-size: 13px;
    margin-top: 10px;
}

/* Chat Header */
#menu {
    background: #ffffff;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    z-index: 10;
}
#menu p.welcome {
    margin: 0;
    font-size: 15px;
    color: #374151;
    font-weight: 500;
}
#menu p.welcome b {
    color: #111827;
    font-weight: 700;
}
a#exit {
    color: #4b5563;
    background: #f3f4f6;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s;
}
a#exit:hover {
    background: #e5e7eb;
    color: #1f2937;
}

/* Chat Content Area */
#chatbox {
    flex: 1;
    padding: 20px 15px;
    background: #f3f4f6;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
/* Scrollbar */
#chatbox::-webkit-scrollbar { width: 6px; }
#chatbox::-webkit-scrollbar-track { background: transparent; }
#chatbox::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* Chat Bubbles Container */
.msg-container {
    display: flex;
    flex-direction: column;
    max-width: 80%;
    animation: fadeIn 0.3s ease;
}

/* Other Player (Left) */
.message-theirs {
    align-self: flex-start;
}
.message-theirs .msg-content {
    background: #ffffff;
    color: #1f2937;
    border: 1px solid #e5e7eb;
    border-radius: 16px 16px 16px 4px;
    padding: 10px 14px;
    font-size: 14.5px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.message-theirs .msg-meta {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 4px;
    margin-left: 4px;
}

/* Current Player (Right) */
.message-mine {
    align-self: flex-end;
}
.message-mine .msg-content {
    background: #E94125;
    color: #ffffff;
    border-radius: 16px 16px 4px 16px;
    padding: 10px 14px;
    font-size: 14.5px;
    box-shadow: 0 1px 2px rgba(233, 65, 37, 0.2);
}
.message-mine .msg-meta {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 4px;
    margin-right: 4px;
    text-align: right;
}

/* System Messages */
.message-system {
    align-self: center;
    background: rgba(0,0,0,0.05);
    color: #6b7280;
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 12px;
    margin: 8px 0;
    max-width: 90%;
    text-align: center;
}
.message-system .welcome-info { color: #d97706; font-weight: 500; }
.message-system .enter-info { color: #059669; font-weight: 500;}
.message-system .left-info { color: #dc2626; font-weight: 500;}

/* Input Area */
#message-form {
    display: flex;
    padding: 12px 15px;
    background: #ffffff;
    border-top: 1px solid #e5e7eb;
    align-items: center;
    gap: 10px;
}
#usermsg {
    flex: 1;
    border-radius: 20px;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    padding: 10px 16px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
}
#usermsg:focus {
    border-color: #E94125;
    background: #ffffff;
}
#submitmsg {
    background: #E94125;
    color: white;
    border: none;
    border-radius: 50%;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s, background 0.2s;
}
#submitmsg:hover {
    background: #C8351C;
    transform: scale(1.05);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div id="chat-wrapper">
    @php if (!isset($_SESSION['name'])) { @endphp
        <div id="loginform">
            <p>{{ __('Vui lòng nhập tên để bắt đầu chat!') }}</p>
            <form id="login-form" method="post" action="{{ url()->current() }}">
                @csrf
                <input type="text" name="name" id="name" placeholder="{{ __('Tên') }}..." value="{{ Auth::check() ? Auth::user()->name : (isset($_COOKIE['cotuong_name']) ? $_COOKIE['cotuong_name'] : '') }}" />
                <input type="submit" name="enter" id="enter" value="{{ __('Nhập') }}" />
            </form>
            <div id="login-error" class="error"></div>
        </div>
        <div id="chatbox" style="display:none;"></div>
    @php } else { @endphp
        <div id="menu">
            <p class="welcome">{{ __('Chào bạn') }}, <b>@php echo $_SESSION['name']; @endphp</b></p>
            <a id="exit" href="javascript:void(0);">{{ __("Thoát") }}</a>
        </div>
        <div id="chatbox"></div>
        <form name="message" id="message-form">
            <input name="usermsg" type="text" id="usermsg" placeholder="{{ __('Nhập tin nhắn...') }}" required="required" autocomplete="off" />
            <button name="submitmsg" type="submit" id="submitmsg">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    @php } @endphp
</div>

<script>
$(document).ready(function () {
    let currentUser = "{{ isset($_SESSION['name']) ? addslashes($_SESSION['name']) : '' }}";
    let lastScrollHeight = 0;
    let renderedMessageCount = 0; // NEW: Track how many messages we've shown

    if (typeof $ === 'undefined') {
        console.error("jQuery is not loaded. Please ensure jQuery is included.");
        return;
    }

    // Pass only the NEW messages to this function
    function parseAndRenderChat($newElements) {
        let formattedHtml = '';

        $newElements.each(function() {
            let $this = $(this);

            // Check if it's a system message
            if ($this.hasClass('system-msg') || $this.find('.welcome-info, .left-info, .enter-info').length > 0) {
                $this.addClass('msg-container message-system');
                formattedHtml += $this.prop('outerHTML');
                return; // Acts as continue in $.each
            }

            // Extract data for player messages
            let $user = $this.find('.user-name');
            if ($user.length) {
                let userName = $user.text().trim();
                let rawTime = $this.find('.chat-time').text().trim();
                let timeOnly = rawTime.split('|').pop().trim(); // Just get H:i:s

                // Clone to safely remove structural elements and grab just the text
                let $clone = $this.clone();
                $clone.find('.chat-time, .user-name').remove();
                // Remove the trailing <br> generated by the backend
                $clone.find('br').last().remove();
                let msgText = $clone.html().trim();

                let isMine = (userName === currentUser);
                let bubbleClass = isMine ? 'message-mine' : 'message-theirs';
                let metaText = isMine ? timeOnly : `<b>${userName}</b> • ${timeOnly}`;

                formattedHtml += `
                    <div class="msg-container ${bubbleClass}">
                        <div class="msg-meta">${metaText}</div>
                        <div class="msg-content">${msgText}</div>
                    </div>
                `;
            }
        });

        return formattedHtml;
    }

    function loadLog(forceScroll = false) {
        if ($("#chatbox").length === 0 || $("#chatbox").is(":hidden")) return;

        $.ajax({
            url: "{{ $log_path }}",
            cache: false,
            success: function (html) {
                let chatbox = $("#chatbox")[0];
                let isScrolledToBottom = chatbox.scrollHeight - chatbox.clientHeight <= chatbox.scrollTop + 50;

                // Load HTML into a temporary element and extract the lines
                let $temp = $('<div>').html(html);
                let $allMessages = $temp.find('.msgln');

                // NEW: Only process if there are more messages than we've already rendered
                if ($allMessages.length > renderedMessageCount) {
                    let $newMessages = $allMessages.slice(renderedMessageCount);
                    let newFormattedHtml = parseAndRenderChat($newMessages);

                    // APPEND instead of replacing everything to prevent flickering
                    $("#chatbox").append(newFormattedHtml);

                    // Update our count
                    renderedMessageCount = $allMessages.length;

                    // Auto-scroll logic
                    if (forceScroll || isScrolledToBottom || lastScrollHeight === 0) {
                        chatbox.scrollTop = chatbox.scrollHeight;
                    }
                    lastScrollHeight = chatbox.scrollHeight;
                }
            },
            error: function (xhr, status, error) {
                console.error("Error loading chat log:", xhr, status, error);
            }
        });
    }

    // Initial log load
    if (currentUser !== "") {
        loadLog(true);
        setInterval(() => loadLog(false), 1500); // Polling
    }

    // Login Handler
    $(document).on("submit", "#login-form", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const name = $("#name").val().trim();
        if (name === "") {
            $("#login-error").text("{{ __('Vui lòng điền tên') }}");
            return false;
        }

        currentUser = name;
        // Reset rendered count when dynamically mounting the chat interface
        renderedMessageCount = 0;

        $("#chat-wrapper").html(`
            <div id="menu">
                <p class="welcome">{{ __('Chào bạn') }}, <b>${name}</b></p>
                <a id="exit" href="javascript:void(0);">{{ __("Thoát") }}</a>
            </div>
            <div id="chatbox"></div>
            <form name="message" id="message-form">
                <input name="usermsg" type="text" id="usermsg" placeholder="{{ __('Nhập tin nhắn...') }}" required="required" autocomplete="off" />
                <button name="submitmsg" type="submit" id="submitmsg">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        `);

        $.ajax({
            url: "{{ url()->current() }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                enter: "{{ __('Nhập') }}"
            },
            success: function () {
                loadLog(true);
                setInterval(() => loadLog(false), 1500);
            },
            error: function () {
                $("#login-error").text("{{ __('Đã xảy ra lỗi khi đăng nhập. Vui lòng thử lại.') }}");
                location.reload();
            }
        });
        return false;
    });

    // Send Message Handler
    $("#chat-wrapper").on("submit", "#message-form", function (e) {
        e.preventDefault();
        const clientmsg = $("#usermsg").val().trim();
        if (clientmsg === "") return false;

        $.post("{{ url('/api') }}{{ $apiEndpoint }}", {
            roomCode: "{{ $roomCode }}",
            text: clientmsg,
            _token: "{{ csrf_token() }}"
        }, function() {
            loadLog(true); // Force scroll on own message send
        });

        $("#usermsg").val("");
        return false;
    });

    // Exit/Logout Handler
    $("#chat-wrapper").on("click", "#exit", function (e) {
        e.preventDefault();
        bootbox.confirm({
            message: "{{ __('Thoát') }} {{ __('khỏi phòng chat?') }}",
            centerVertical: true,
            locale: 'vi',
            closeButton: false,
            buttons: {
                confirm: {
                    label: '<i class="fas fa-check"></i> {{ __("Thoát") }}',
                    className: 'btn-danger pulse-red'
                },
                cancel: {
                    label: '<i class="fas fa-times"></i> {{ __("Hủy") }}',
                    className: 'btn-dark text-light'
                }
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        url: "{{ url()->current() }}?logout=true",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            logout: true
                        },
                        success: function () {
                            location.reload();
                        }
                    });
                }
            }
        });
    });
});
</script>
