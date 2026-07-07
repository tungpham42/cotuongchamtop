@php
$localeConfigs = [
    'vi' => ['prefix' => 'CoTuong_VI-', 'folder' => 'phongChatLog', 'suffix' => '-phongchatlog.html', 'endpoint' => '/postChatVi'],
    'en' => ['prefix' => 'CoTuong_EN-', 'folder' => 'roomChatLog', 'suffix' => '-roomchatlog.html', 'endpoint' => '/postChatEn'],
    'ja' => ['prefix' => 'CoTuong_JA-', 'folder' => 'rumuChatLog', 'suffix' => '-rumuchatlog.html', 'endpoint' => '/postChatJa'],
    'ko' => ['prefix' => 'CoTuong_KO-', 'folder' => 'bangChatLog', 'suffix' => '-bangchatlog.html', 'endpoint' => '/postChatKo'],
    'zh' => ['prefix' => 'CoTuong_ZH-', 'folder' => 'fangjianChatLog', 'suffix' => '-fangjianchatlog.html', 'endpoint' => '/postChatZh']
];

$config = $localeConfigs[app()->getLocale()] ?? $localeConfigs['en'];
extract($config); // Extracts to $prefix, $folder, $suffix, $endpoint[cite: 4]

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($prefix . $roomCode);
    session_start();
}

$room_path = public_path("{$folder}/{$roomCode}{$suffix}");
$log_path = url("{$folder}/{$roomCode}{$suffix}");
$initialLog = ''; // Variable to hold existing chat content[cite: 4]

if (!is_file($room_path)) {
    $time = date("H:i");
    $text = __('Phòng được tạo');
    file_put_contents($room_path, "<div class='msgln system-msg'><span class='chat-time'>{$time}</span> <span class='welcome-info'>👋 {$text}</span></div>\n");
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

// Fetch the existing log unconditionally so guests can see it too![cite: 4]
if (is_file($room_path)) {
    $initialLog = file_get_contents($room_path);
}
@endphp

<style>
/* *==========================================================================
   * GIAO DIỆN CHAT HOÀNG GIA - LIQUID GLASS & GUEST MODE
   *========================================================================== */

#chat-wrapper {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 420px;
    height: 550px;

    /* Integrated Liquid Glass Enclosure */
    background: var(--glass-bg-dark); /*[cite: 3] */
    backdrop-filter: var(--glass-blur); /*[cite: 3] */
    -webkit-backdrop-filter: var(--glass-blur); /*[cite: 3] */
    border: 1px solid var(--glass-border); /*[cite: 3] */
    border-top: 2px solid rgba(255, 215, 0, 0.5); /* Enhanced glossy top edge[cite: 3] */
    border-radius: 12px;
    box-shadow: var(--liquid-shadow), inset 0 3px 15px var(--liquid-highlight); /*[cite: 3] */

    overflow: hidden;
    font-family: "Plus Jakarta Sans", "Noto Sans JP", sans-serif; /*[cite: 3] */
    justify-content: space-between;
    align-content: center;
    margin: 0 auto;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); /*[cite: 3] */
}

#chat-wrapper:hover {
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.9), 0 0 30px rgba(212, 175, 55, 0.3), inset 0 2px 20px var(--liquid-highlight); /*[cite: 3] */
}

/* Glassy Chat Header aligned with .card-header */
#menu {
    background: linear-gradient(90deg, transparent, var(--glass-bg-red), transparent); /*[cite: 3] */
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 215, 0, 0.2); /*[cite: 3] */
    z-index: 10;
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
    text-shadow: 0 0 8px rgba(255,215,0,0.4);
}
#menu p.welcome i {
    color: var(--royal-gold);
    margin-right: 6px;
    text-shadow: 0 0 10px rgba(255,215,0,0.5);
}
a#exit {
    color: var(--royal-gold-light);
    background: rgba(11, 12, 16, 0.6);
    backdrop-filter: blur(4px);
    padding: 6px 14px;
    border-radius: 6px;
    font-weight: bold;
    font-size: 13px;
    text-decoration: none;
    text-transform: uppercase;
    border: 1px solid rgba(255,255,255,0.2);
    transition: all 0.3s ease;
}
a#exit:hover {
    background: rgba(183, 34, 34, 0.6);
    border-color: var(--royal-gold);
    color: var(--royal-gold);
    box-shadow: 0 0 10px rgba(255,215,0,0.3);
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

/* Royal Custom Scrollbar */
#chatbox::-webkit-scrollbar { width: 8px; } /*[cite: 3] */
#chatbox::-webkit-scrollbar-track { background: var(--royal-bg); border-left: 1px solid rgba(212, 175, 55, 0.2); border-radius: 4px; } /*[cite: 3] */
#chatbox::-webkit-scrollbar-thumb { background: linear-gradient(180deg, var(--royal-red), #5c0a0a); border: 1px solid var(--royal-gold); border-radius: 6px; } /*[cite: 3] */
#chatbox::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, #d4af37, #b89020); border-color: #fff; } /*[cite: 3] */

/* Chat Bubbles Container */
.msg-container {
    display: flex;
    flex-direction: column;
    max-width: 85%;
    animation: slideUpFade 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Other Player (Obsidian Glass Bubbles) */
.message-theirs { align-self: flex-start; }
.message-theirs .msg-content {
    background: var(--glass-bg-dark); /*[cite: 3] */
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: var(--royal-gold-light);
    border: 1px solid rgba(255, 215, 0, 0.15);
    border-radius: 4px 12px 12px 12px;
    padding: 10px 14px;
    font-size: 15px;
    line-height: 1.4;
    box-shadow: inset 0 2px 5px rgba(255,255,255,0.1), 0 4px 10px rgba(0,0,0,0.4);
}
.message-theirs .msg-meta {
    font-size: 12px;
    color: #aa8c4a;
    margin-bottom: 4px;
    margin-left: 4px;
    font-weight: bold;
}
.message-theirs .msg-meta b { color: var(--royal-gold); }

/* Current Player (Ruby Glass Bubbles) */
.message-mine { align-self: flex-end; }
.message-mine .msg-content {
    background: var(--glass-bg-red); /*[cite: 3] */
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #fff;
    border: 1px solid rgba(255, 215, 0, 0.3);
    border-radius: 12px 4px 12px 12px;
    padding: 10px 14px;
    font-size: 15px;
    line-height: 1.4;
    box-shadow: inset 0 2px 5px rgba(255,255,255,0.15), 0 4px 10px rgba(0,0,0,0.4);
}
.message-mine .msg-meta {
    font-size: 12px;
    color: #aa8c4a;
    margin-bottom: 4px;
    margin-right: 4px;
    text-align: right;
    font-weight: bold;
}

/* System Messages - Proverb Style Integration */
.message-system {
    align-self: center;
    background: rgba(138, 21, 21, 0.2); /*[cite: 3] */
    border-left: 2px solid var(--royal-gold); /*[cite: 3] */
    border-right: 2px solid var(--royal-gold); /*[cite: 3] */
    backdrop-filter: blur(4px);
    color: var(--royal-gold);
    font-family: "Texturina", serif; /*[cite: 3] */
    font-style: italic;
    font-weight: bold;
    font-size: 14px;
    padding: 8px 20px;
    border-radius: 4px;
    margin: 10px 0;
    max-width: 90%;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.4);
}
.message-system .welcome-info { color: var(--royal-gold-light); }
.message-system .enter-info { color: var(--royal-gold); text-shadow: 0 0 5px rgba(255,215,0,0.5); }
.message-system .left-info { color: #ff6b6b; }

/* Input Footer Actions (Guest & User) */
#footer-action {
    background: rgba(11, 12, 16, 0.5);
    border-top: 1px solid rgba(255, 215, 0, 0.2);
    z-index: 10;
    padding: 15px;
}

/* Guest Inline Form */
#guest-action {
    display: flex;
    flex-direction: column;
}
#login-form-inline {
    display: flex;
    gap: 12px;
    align-items: center;
    width: 100%;
}

/* Glossy Inputs */
#name, #usermsg {
    flex: 1;
    background: rgba(11, 12, 16, 0.6);
    backdrop-filter: blur(4px);
    border: 1px solid var(--royal-wood); /*[cite: 3] */
    color: var(--royal-gold-light); /*[cite: 3] */
    padding: 14px 18px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 8px;
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.5);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    outline: none;
}
#name::placeholder, #usermsg::placeholder { color: var(--royal-gold-light); opacity: 0.5; }
#name:focus, #usermsg:focus {
    border-color: var(--royal-gold); /*[cite: 3] */
    box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25), inset 0 2px 5px rgba(0,0,0,0.5); /*[cite: 3] */
    background: rgba(11, 12, 16, 0.8);
}

/* User Chat Form */
#message-form {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Ruby Glass Buttons aligned with .btn-danger */
#enter, #submitmsg {
    position: relative;
    overflow: hidden;
    background: var(--glass-bg-red); /*[cite: 3] */
    color: var(--royal-gold); /*[cite: 3] */
    border: 1px solid rgba(255, 215, 0, 0.4);
    border-radius: 8px;
    box-shadow: var(--liquid-shadow), inset 0 2px 8px rgba(255, 215, 0, 0.2); /*[cite: 3] */
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 1;
}
#enter i, #submitmsg i { filter: drop-shadow(0 0 2px rgba(0,0,0,0.5)); }

#enter::before, #submitmsg::before {
    content: "";
    position: absolute;
    top: 0;
    left: -150%;
    width: 150%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent); /*[cite: 3] */
    transform: skewX(-20deg);
    transition: all 0.6s ease;
    z-index: -1;
}
#enter:hover::before, #submitmsg:hover::before {
    left: 150%; /*[cite: 3] */
}
#enter:hover, #submitmsg:hover {
    background: rgba(183, 34, 34, 0.6);
    box-shadow: 0 0 15px rgba(212, 175, 55, 0.5), inset 0 2px 10px rgba(255, 215, 0, 0.4); /*[cite: 3] */
    transform: translateY(-2px);
}
.error {
    color: #fff;
    background: rgba(183, 34, 34, 0.8);
    backdrop-filter: blur(4px);
    padding: 8px 12px;
    border: 1px solid rgba(255, 215, 0, 0.5);
    border-radius: 8px;
    font-size: 13px;
    margin-top: 10px;
    font-weight: bold;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.5);
}

/* Animations */
@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(15px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<!-- Hidden div to hold initial payload for immediate render (Works for Guests too) -->
<div id="raw-chat-log" style="display:none;">{!! $initialLog !!}</div>

<div id="chat-wrapper">
    <div id="menu">
        @php if (!isset($_SESSION['name'])) { @endphp
            <p class="welcome"><i class="fas fa-eye"></i></p>
        @php } else { @endphp
            <p class="welcome">{{ __('Chào bạn') }}, <b>@php echo $_SESSION['name']; @endphp</b></p>
            <a id="exit" href="javascript:void(0);">{{ __("Thoát") }}</a>
        @php } @endphp
    </div>

    <!-- Chatbox is always visible -->
    <div id="chatbox"></div>

    <!-- The Action Footer swaps based on auth state -->
    <div id="footer-action">
        @php if (!isset($_SESSION['name'])) { @endphp
            <div id="guest-action">
                <form id="login-form-inline" method="post" action="{{ url()->current() }}">
                    @csrf
                    <input type="text" name="name" id="name" placeholder="{{ __('Danh xưng của ngài...') }}" value="{{ Auth::check() ? Auth::user()->name : (isset($_COOKIE['cotuong_name']) ? $_COOKIE['cotuong_name'] : '') }}" required />
                    <button type="submit" name="enter" id="enter" class="pulse-gold" title="{{ __('Báo danh') }}">
                        <i class="fas fa-sign-in-alt"></i>
                    </button>
                </form>
                <div id="login-error" class="error" style="display:none;"></div>
            </div>
        @php } else { @endphp
            <form name="message" id="message-form">
                <input name="usermsg" type="text" id="usermsg" placeholder="{{ __('Nhập tin nhắn...') }}" required="required" autocomplete="off" />
                <button name="submitmsg" type="submit" id="submitmsg">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        @php } @endphp
    </div>
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

    // Dynamic state rendering without destroying the chat history[cite: 4]
    function renderGuestState() {
        let defaultName = "{{ Auth::check() ? Auth::user()->name : (isset($_COOKIE['cotuong_name']) ? $_COOKIE['cotuong_name'] : '') }}";

        $("#menu").html(`<p class="welcome"><i class="fas fa-eye"></i></p>`);

        $("#footer-action").html(`
            <div id="guest-action">
                <form id="login-form-inline" method="post" action="{{ url()->current() }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="text" name="name" id="name" placeholder="{{ __('Danh xưng của ngài...') }}" value="${defaultName}" required />
                    <button type="submit" name="enter" id="enter" class="pulse-gold" title="{{ __('Báo danh') }}">
                        <i class="fas fa-sign-in-alt"></i>
                    </button>
                </form>
                <div id="login-error" class="error" style="display:none;"></div>
            </div>
        `);
    }

    function renderUserState(name) {
        $("#menu").html(`
            <p class="welcome">{{ __('Chào bạn') }}, <b>${name}</b></p>
            <a id="exit" href="javascript:void(0);">{{ __("Thoát") }}</a>
        `);

        $("#footer-action").html(`
            <form name="message" id="message-form">
                <input name="usermsg" type="text" id="usermsg" placeholder="{{ __('Nhập tin nhắn...') }}" required="required" autocomplete="off" />
                <button name="submitmsg" type="submit" id="submitmsg">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
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

                // If currentUser is empty (Guest), all messages become "theirs" naturally.
                let isMine = (currentUser !== "") && (userName === currentUser);
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

    // Immediate Chat Initialization (Works for logged out users too)[cite: 4]
    function initializeChat() {
        let rawHtml = $("#raw-chat-log").html();
        if (rawHtml && rawHtml.trim() !== '') {
            let $temp = $('<div>').html(rawHtml);
            let $allMessages = $temp.find('.msgln');

            if ($allMessages.length > 0) {
                let newFormattedHtml = parseAndRenderChat($allMessages);
                $("#chatbox").append(newFormattedHtml);
                renderedMessageCount = $allMessages.length;

                let chatbox = $("#chatbox")[0];
                chatbox.scrollTop = chatbox.scrollHeight;
                lastScrollHeight = chatbox.scrollHeight;
            }
        }
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

    // Always start observing the room immediately
    initializeChat();
    chatInterval = setInterval(() => loadLog(false), 1500);

    // Dynamic Login Form Submission (Intercepted from #footer-action)[cite: 4]
    $("#chat-wrapper").on("submit", "#login-form-inline", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const name = $("#name").val().trim();
        if (name === "") {
            $("#login-error").text("{{ __('Vui lòng điền tên') }}").show();
            return false;
        }

        currentUser = name;

        // Wipe local variables, reload entire chat using the new identity mapping
        renderedMessageCount = 0;
        $("#chatbox").empty();

        renderUserState(name);

        $.ajax({
            url: "{{ url()->current() }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                enter: "{{ __('Nhập') }}"
            },
            success: function () {
                initializeChat();
                loadLog(true);
            },
            error: function () {
                currentUser = "";
                renderGuestState();
                $("#login-error").text("{{ __('Lỗi đăng nhập. Xin thử lại.') }}").show();
            }
        });
        return false;
    });

    // Submitting Chat Message
    $("#chat-wrapper").on("submit", "#message-form", function (e) {
        e.preventDefault();
        const clientmsg = $("#usermsg").val().trim();
        if (clientmsg === "") return false;

        $.post("{{ url('/api') }}{{ $endpoint }}", {
            roomCode: "{{ $roomCode }}",
            text: clientmsg,
            _token: "{{ csrf_token() }}"
        }, function() {
            loadLog(true);
        });

        $("#usermsg").val("");
        return false;
    });

    // Exiting Chat (Reverts gracefully to Observer mode)[cite: 4]
    $("#chat-wrapper").on("click", "#exit", function (e) {
        e.preventDefault();
        bootbox.confirm({
            message: "{{ __('Rời khỏi cuộc đàm đạo?') }}",
            centerVertical: true,
            locale: '{{ __("vi") }}',
            closeButton: false,
            buttons: {
                confirm: {
                    label: '<i class="fas fa-check"></i> {{ __("Rời đi") }}',
                    className: 'btn-danger pulse-red'
                },
                cancel: {
                    label: '<i class="fas fa-times"></i> {{ __("Ở lại") }}',
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
                            currentUser = "";
                            renderedMessageCount = 0;
                            $("#chatbox").empty();
                            renderGuestState();
                            initializeChat(); // Read chatlog as a guest again
                            loadLog(true);
                        }
                    });
                }
            }
        });
    });
});
</script>
