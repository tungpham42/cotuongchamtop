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
/* *==========================================================================
   * GIAO DIỆN CHAT CUNG ĐÌNH HUẾ - ROYAL THEME
   *========================================================================== */

#chat-wrapper {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 420px;
    height: 550px;
    background-color: var(--royal-bg);
    background-image: radial-gradient(circle at center, #2a1910 0%, #1c110a 100%);
    border: var(--royal-border);
    border-radius: 6px;
    box-shadow: 0 15px 25px rgba(0, 0, 0, 0.8), inset 0 0 15px rgba(212, 175, 55, 0.1);
    overflow: hidden;
    font-family: "Plus Jakarta Sans", "Noto Sans JP", sans-serif;
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
    background: transparent;
}
#loginform p {
    font-family: "Texturina", serif;
    font-size: 18px;
    font-weight: bold;
    color: var(--royal-gold);
    text-transform: uppercase;
    margin-bottom: 2rem;
    letter-spacing: 1px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
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
    background-color: var(--royal-gold-light);
    border: 1px solid var(--royal-wood);
    color: var(--royal-bg);
    padding: 14px 18px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 4px;
    transition: all 0.3s ease-in-out;
    outline: none;
}
#name::placeholder { color: var(--royal-wood); opacity: 0.7; }
#name:focus {
    border-color: var(--royal-gold);
    box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
}

#enter {
    background: linear-gradient(to bottom, #8a1515, #5c0a0a);
    color: var(--royal-gold);
    border: 1px solid var(--royal-gold);
    padding: 14px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.6);
    transition: all 0.3s ease;
}
#enter:hover {
    background: linear-gradient(to bottom, #b72222, #8a1515);
    box-shadow: 0 0 10px rgba(212, 175, 55, 0.5);
}
.error {
    color: var(--royal-gold);
    background: rgba(138, 21, 21, 0.8);
    padding: 5px 10px;
    border: 1px solid var(--royal-gold);
    border-radius: 4px;
    font-size: 14px;
    margin-top: 12px;
    font-weight: bold;
}

/* Chat Header */
#menu {
    background: linear-gradient(to bottom, #8a1515, #5c0a0a);
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid var(--royal-gold);
    z-index: 10;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.8);
}
#menu p.welcome {
    margin: 0;
    font-size: 15px;
    color: var(--royal-gold-light);
    font-weight: 600;
}
#menu p.welcome b {
    color: var(--royal-gold);
    font-weight: bold;
    text-transform: uppercase;
}
a#exit {
    color: var(--royal-gold-light);
    background-color: #333;
    padding: 6px 14px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 13px;
    text-decoration: none;
    text-transform: uppercase;
    border: 1px solid #555;
    transition: all 0.2s;
}
a#exit:hover {
    background-color: var(--royal-red-light);
    border-color: var(--royal-gold);
    color: var(--royal-gold);
}

/* Chat Content Area */
#chatbox {
    flex: 1;
    padding: 20px 16px;
    background: transparent;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Custom Scrollbar */
#chatbox::-webkit-scrollbar { width: 8px; }
#chatbox::-webkit-scrollbar-track { background: var(--royal-bg); }
#chatbox::-webkit-scrollbar-thumb { background: var(--royal-wood); border: 1px solid var(--royal-gold); border-radius: 4px; }
#chatbox::-webkit-scrollbar-thumb:hover { background: var(--royal-gold); }

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
    background: #2a1910;
    color: var(--royal-gold-light);
    border: 1px solid var(--royal-wood);
    border-radius: 4px 12px 12px 12px;
    padding: 10px 14px;
    font-size: 15px;
    line-height: 1.4;
    box-shadow: 0 4px 6px rgba(0,0,0,0.5);
}
.message-theirs .msg-meta {
    font-size: 12px;
    color: #aa8c4a;
    margin-bottom: 4px;
    margin-left: 4px;
    font-weight: bold;
}
.message-theirs .msg-meta b { color: var(--royal-gold); }

/* Current Player (Right) */
.message-mine {
    align-self: flex-end;
}
.message-mine .msg-content {
    background: linear-gradient(to bottom, #8a1515, #5c0a0a);
    color: var(--royal-gold);
    border: 1px solid var(--royal-gold);
    border-radius: 12px 4px 12px 12px;
    padding: 10px 14px;
    font-size: 15px;
    line-height: 1.4;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.6);
}
.message-mine .msg-meta {
    font-size: 12px;
    color: #aa8c4a;
    margin-bottom: 4px;
    margin-right: 4px;
    text-align: right;
    font-weight: bold;
}

/* System Messages - Proverb Style */
.message-system {
    align-self: center;
    background: rgba(138, 21, 21, 0.2);
    border-left: 4px solid var(--royal-gold);
    border-right: 4px solid var(--royal-gold);
    color: var(--royal-gold);
    font-family: "Texturina", serif;
    font-style: italic;
    font-weight: bold;
    font-size: 14px;
    padding: 8px 20px;
    border-radius: 2px;
    margin: 10px 0;
    max-width: 90%;
    text-align: center;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}
.message-system .welcome-info { color: var(--royal-gold-light); }
.message-system .enter-info { color: var(--royal-gold); }
.message-system .left-info { color: var(--royal-red-light); }

/* Input Area */
#message-form {
    display: flex;
    padding: 15px;
    background: #2a1910;
    border-top: 1px solid var(--royal-gold);
    align-items: center;
    gap: 12px;
}
#usermsg {
    flex: 1;
    background-color: var(--royal-gold-light);
    border: 1px solid var(--royal-wood);
    color: var(--royal-bg);
    border-radius: 4px;
    padding: 12px 16px;
    font-size: 15px;
    font-weight: 600;
    outline: none;
    transition: all 0.3s ease;
}
#usermsg::placeholder { color: var(--royal-wood); opacity: 0.8;}
#usermsg:focus {
    border-color: var(--royal-gold);
    box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
}
#submitmsg {
    background: linear-gradient(to bottom, #8a1515, #5c0a0a);
    color: var(--royal-gold);
    border: 1px solid var(--royal-gold);
    border-radius: 4px;
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.6);
}
#submitmsg:hover {
    background: linear-gradient(to bottom, #b72222, #8a1515);
    box-shadow: 0 0 10px rgba(212, 175, 55, 0.5);
}
#submitmsg i { font-size: 18px; }

/* Animations */
@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(10px) scale(0.98); }
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
            <div id="login-error" class="error" style="display:none;"></div>
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
    let renderedMessageCount = 0;
    let chatInterval = null;

    if (typeof $ === 'undefined') {
        console.error("jQuery is not loaded. Please ensure jQuery is included.");
        return;
    }

    function renderLoginForm() {
        let defaultName = "{{ Auth::check() ? Auth::user()->name : (isset($_COOKIE['cotuong_name']) ? $_COOKIE['cotuong_name'] : '') }}";

        $("#chat-wrapper").html(`
            <div id="loginform">
                <p>{{ __('Vui lòng nhập tên để bắt đầu chat!') }}</p>
                <form id="login-form" method="post" action="{{ url()->current() }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="text" name="name" id="name" placeholder="{{ __('Tên') }}..." value="${defaultName}" />
                    <input type="submit" name="enter" id="enter" value="{{ __('Nhập') }}" />
                </form>
                <div id="login-error" class="error" style="display:none;"></div>
            </div>
            <div id="chatbox" style="display:none;"></div>
        `);
    }

    function parseAndRenderChat($newElements) {
        let formattedHtml = '';

        $newElements.each(function() {
            let $this = $(this);

            if ($this.hasClass('system-msg') || $this.find('.welcome-info, .left-info, .enter-info').length > 0) {
                $this.addClass('msg-container message-system');
                formattedHtml += $this.prop('outerHTML');
                return;
            }

            let $user = $this.find('.user-name');
            if ($user.length) {
                let userName = $user.text().trim();
                let rawTime = $this.find('.chat-time').text().trim();
                let timeOnly = rawTime.split('|').pop().trim();

                let $clone = $this.clone();
                $clone.find('.chat-time, .user-name').remove();
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

                let $temp = $('<div>').html(html);
                let $allMessages = $temp.find('.msgln');

                if ($allMessages.length > renderedMessageCount) {
                    let $newMessages = $allMessages.slice(renderedMessageCount);
                    let newFormattedHtml = parseAndRenderChat($newMessages);

                    $("#chatbox").append(newFormattedHtml);
                    renderedMessageCount = $allMessages.length;

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

    if (currentUser !== "") {
        loadLog(true);
        chatInterval = setInterval(() => loadLog(false), 1500);
    }

    $(document).on("submit", "#login-form", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const name = $("#name").val().trim();
        if (name === "") {
            $("#login-error").text("{{ __('Vui lòng điền tên') }}").show();
            return false;
        }

        currentUser = name;
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
                if (chatInterval) clearInterval(chatInterval);
                chatInterval = setInterval(() => loadLog(false), 1500);
            },
            error: function () {
                renderLoginForm();
                $("#login-error").text("{{ __('Đã xảy ra lỗi khi đăng nhập. Vui lòng thử lại.') }}").show();
            }
        });
        return false;
    });

    $("#chat-wrapper").on("submit", "#message-form", function (e) {
        e.preventDefault();
        const clientmsg = $("#usermsg").val().trim();
        if (clientmsg === "") return false;

        $.post("{{ url('/api') }}{{ $apiEndpoint }}", {
            roomCode: "{{ $roomCode }}",
            text: clientmsg,
            _token: "{{ csrf_token() }}"
        }, function() {
            loadLog(true);
        });

        $("#usermsg").val("");
        return false;
    });

    $("#chat-wrapper").on("click", "#exit", function (e) {
        e.preventDefault();
        bootbox.confirm({
            message: "{{ __('Thoát') }} {{ __('khỏi phòng chat?') }}",
            centerVertical: true,
            locale: '{{ __("vi") }}',
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
                            if (chatInterval) {
                                clearInterval(chatInterval);
                                chatInterval = null;
                            }
                            currentUser = "";
                            renderedMessageCount = 0;
                            renderLoginForm();
                        }
                    });
                }
            }
        });
    });
});
</script>
