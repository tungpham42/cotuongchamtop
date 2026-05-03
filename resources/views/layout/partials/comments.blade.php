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
/* * BOLD, DARK & CATCHY CHAT UI
 * Color Palette: Deep Space Navy, Neon Crimson, Soft Ash
 */

#chat-wrapper {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 420px;
    height: 550px;
    background: #13131A; /* Deep dark background */
    border-radius: 20px;
    box-shadow: 0 16px 40px rgba(0,0,0,0.6), 0 0 0 1px #2A2A35;
    overflow: hidden;
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    justify-content: space-between;
    align-content: center;
    margin: 0 auto;
}

/* Login Form */
#loginform {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100%;
    padding: 2.5rem;
    text-align: center;
    background: radial-gradient(circle at center, #1C1C24 0%, #13131A 100%);
}
#loginform p {
    font-size: 18px;
    font-weight: 700;
    color: #FFFFFF;
    margin-bottom: 2rem;
    letter-spacing: 0.5px;
}
#loginform form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
#loginform label { display: none; }

#name {
    width: 100%;
    border-radius: 12px;
    border: 2px solid #2A2A35;
    background: #1C1C24;
    color: #FFFFFF;
    padding: 14px 18px;
    font-size: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
}
#name::placeholder { color: #6C6C80; }
#name:focus {
    border-color: #FF473A;
    background: #22222D;
    box-shadow: 0 0 15px rgba(255, 71, 58, 0.15);
}

#enter {
    background: linear-gradient(135deg, #FF473A, #FF2A5F);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 16px;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 71, 58, 0.3);
}
#enter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 71, 58, 0.5);
}
.error {
    color: #FF473A;
    font-size: 14px;
    margin-top: 12px;
    font-weight: 600;
}

/* Chat Header */
#menu {
    background: #1C1C24;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #2A2A35;
    z-index: 10;
}
#menu p.welcome {
    margin: 0;
    font-size: 15px;
    color: #A0A0B0;
    font-weight: 500;
}
#menu p.welcome b {
    color: #FFFFFF;
    font-weight: 700;
    letter-spacing: 0.5px;
}
a#exit {
    color: #FF473A;
    background: rgba(255, 71, 58, 0.1);
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none;
    text-transform: uppercase;
    transition: all 0.2s;
    border: 1px solid rgba(255, 71, 58, 0.2);
}
a#exit:hover {
    background: #FF473A;
    color: #FFFFFF;
    box-shadow: 0 0 10px rgba(255, 71, 58, 0.4);
}

/* Chat Content Area */
#chatbox {
    flex: 1;
    padding: 20px 16px;
    background: #13131A;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Custom Scrollbar for Dark UI */
#chatbox::-webkit-scrollbar { width: 6px; }
#chatbox::-webkit-scrollbar-track { background: transparent; }
#chatbox::-webkit-scrollbar-thumb { background: #2A2A35; border-radius: 10px; }
#chatbox::-webkit-scrollbar-thumb:hover { background: #3F3F50; }

/* Chat Bubbles Container */
.msg-container {
    display: flex;
    flex-direction: column;
    max-width: 85%;
    animation: slideUpFade 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Other Player (Left) */
.message-theirs {
    align-self: flex-start;
}
.message-theirs .msg-content {
    background: #22222D;
    color: #E2E2E9;
    border: 1px solid #2A2A35;
    border-radius: 18px 18px 18px 4px;
    padding: 12px 16px;
    font-size: 15px;
    line-height: 1.4;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.message-theirs .msg-meta {
    font-size: 11px;
    color: #6C6C80;
    margin-bottom: 6px;
    margin-left: 6px;
    font-weight: 500;
}
.message-theirs .msg-meta b { color: #A0A0B0; }

/* Current Player (Right) */
.message-mine {
    align-self: flex-end;
}
.message-mine .msg-content {
    background: linear-gradient(135deg, #FF473A, #FF2A5F);
    color: #FFFFFF;
    border-radius: 18px 18px 4px 18px;
    padding: 12px 16px;
    font-size: 15px;
    line-height: 1.4;
    box-shadow: 0 4px 10px rgba(255, 71, 58, 0.25);
}
.message-mine .msg-meta {
    font-size: 11px;
    color: #6C6C80;
    margin-bottom: 6px;
    margin-right: 6px;
    text-align: right;
    font-weight: 500;
}

/* System Messages */
.message-system {
    align-self: center;
    background: #1C1C24;
    border: 1px solid #2A2A35;
    color: #8C8C9E;
    font-size: 12.5px;
    padding: 6px 16px;
    border-radius: 20px;
    margin: 10px 0;
    max-width: 90%;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.message-system .welcome-info { color: #FFB340; font-weight: 600; }
.message-system .enter-info { color: #00E676; font-weight: 600; }
.message-system .left-info { color: #FF473A; font-weight: 600; }

/* Input Area */
#message-form {
    display: flex;
    padding: 16px;
    background: #1C1C24;
    border-top: 1px solid #2A2A35;
    align-items: center;
    gap: 12px;
}
#usermsg {
    flex: 1;
    border-radius: 24px;
    border: 1px solid #2A2A35;
    background: #13131A;
    color: #FFFFFF;
    padding: 14px 20px;
    font-size: 15px;
    outline: none;
    transition: all 0.3s ease;
}
#usermsg::placeholder { color: #5C5C70; }
#usermsg:focus {
    border-color: #FF473A;
    background: #181822;
    box-shadow: inset 0 0 0 1px rgba(255, 71, 58, 0.3);
}
#submitmsg {
    background: linear-gradient(135deg, #FF473A, #FF2A5F);
    color: white;
    border: none;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 10px rgba(255, 71, 58, 0.3);
}
#submitmsg:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 6px 15px rgba(255, 71, 58, 0.4);
}
#submitmsg i { font-size: 16px; }

/* Animations */
@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(15px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
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
    let renderedMessageCount = 0; // Track how many messages we've shown

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

                // Only process if there are more messages than we've already rendered
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
