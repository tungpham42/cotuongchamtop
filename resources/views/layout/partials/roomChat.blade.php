@php
use App\Models\ChatMessage;

session_name('CoTuong_VI-'.$roomCode);
session_start();

// Initialize room messages if first time
$messagesCount = ChatMessage::where('room_code', $roomCode)->count();
if ($messagesCount == 0) {
    ChatMessage::addMessage($roomCode, 'System', 'Phòng được tạo', 'system');
}

if (isset($_GET['logout'])) {
    if (isset($_SESSION['name'])) {
        ChatMessage::addMessage($roomCode, $_SESSION['name'], 'đã rời khỏi phòng chat', 'leave');
        $_SESSION = [];
        setcookie('cotuong_name', '', time() - 3600, "/");
    }
}

if (isset($_POST['enter'])) {
    if ($_POST['name'] != "") {
        $_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
        setcookie('cotuong_name', $_SESSION['name'], time() + (86400 * 30), "/");
        ChatMessage::addMessage($roomCode, $_SESSION['name'], 'đã vào phòng chat', 'enter');
    }
}

// Get recent messages for display
$chatMessages = ChatMessage::getMessagesForRoom($roomCode);
@endphp

<style>
/* Chat wrapper - dark theme style */
#chat-wrapper {
    margin: 0 0 1rem 0; 
    padding: 0;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    border: none;
    border-radius: 12px;
    color: #ecf0f1;
    height: 350px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    overflow: hidden;
}

/* Header/Menu styling */
#menu {
    padding: 12px 16px;
    display: flex;
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border-radius: 12px 12px 0 0;
    box-shadow: inset 0 -2px 4px rgba(0,0,0,0.1);
}

#menu p.welcome {
    flex: 1;
    margin: 0;
    font-weight: 600;
    color: white;
    font-size: 15px;
}

#menu p.welcome b {
    background: rgba(255,255,255,0.2);
    padding: 3px 8px;
    border-radius: 15px;
    color: white;
    font-weight: bold;
}

a#exit {
    color: white;
    background: rgba(255,255,255,0.15);
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.3);
}

a#exit:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-1px);
}

/* Login form styling */
#loginform {
    padding: 24px 16px;
    text-align: center;
    border: none;
    font-size: 14px;
    background: transparent;
}

#loginform p {
    padding: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
    color: #ecf0f1;
    margin: 0;
}

#loginform form {
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
}

#loginform form label {
    display: none; /* Hide label for cleaner look */
}

#name {
    border-radius: 25px;
    border: 2px solid rgba(255,255,255,0.2);
    padding: 12px 20px;
    font-size: 15px;
    width: 220px;
    background: rgba(255,255,255,0.1);
    color: white;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

#name::placeholder {
    color: rgba(255,255,255,0.7);
}

#name:focus {
    outline: none;
    border-color: #e74c3c;
    background: rgba(255,255,255,0.15);
    box-shadow: 0 0 20px rgba(231,76,60,0.3);
}

#enter {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
    color: white;
    padding: 12px 24px;
    font-weight: 600;
    border-radius: 25px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(231,76,60,0.3);
}

#enter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(231,76,60,0.4);
}

/* Chat box styling */
#chatbox {
    text-align: left;
    margin: 8px;
    padding: 12px;
    background: rgba(255,255,255,0.05);
    height: calc(100% - 110px);
    width: calc(100% - 32px);
    border: 1px solid rgba(255,255,255,0.1);
    overflow-y: auto;
    border-radius: 8px;
    font-size: 13px;
    backdrop-filter: blur(10px);
}

#loginform + #chatbox {
    height: calc(100% - 40px);
    margin-bottom: 8px;
}

/* Message input styling */
#message-form {
    padding: 8px 12px 12px 12px;
    display: flex;
    gap: 8px;
    background: transparent;
}

#usermsg {
    border-radius: 25px;
    border: 2px solid rgba(255,255,255,0.2);
    flex: 1;
    font-size: 13px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.1);
    color: white;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

#usermsg::placeholder {
    color: rgba(255,255,255,0.6);
}

#usermsg:focus {
    outline: none;
    border-color: #e74c3c;
    background: rgba(255,255,255,0.15);
    box-shadow: 0 0 15px rgba(231,76,60,0.2);
}

#submitmsg {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
    color: white;
    padding: 8px 16px;
    font-weight: 600;
    border-radius: 25px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(231,76,60,0.3);
}

#submitmsg:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(231,76,60,0.4);
}

/* Error styling */
.error {
    color: #e74c3c;
    width: 100%;
    text-align: center;
    margin-top: 8px;
    font-weight: 500;
}

/* Message styling */
.msgln {
    margin: 0 0 8px 0;
    color: #ecf0f1;
    line-height: 1.4;
    padding: 4px 0;
}

.msgln span.welcome-info {
    color: #f39c12;
    font-style: italic;
}

.msgln span.left-info {
    color: #e67e22;
    font-style: italic;
}

.msgln span.enter-info {
    color: #27ae60;
    font-style: italic;
}

.msgln span.chat-time {
    color: #95a5a6;
    font-size: 11px;
    margin-right: 8px;
}

.msgln b.user-name, .msgln b.user-name-left, .msgln b.user-name-enter {
    font-weight: bold;
    background: #3498db;
    color: white;
    padding: 2px 8px;
    font-size: 12px;
    border-radius: 12px;
    margin: 0 6px 0 0;
    display: inline-block;
}

.msgln b.user-name-left {
    background: #e67e22;
}

.msgln b.user-name-enter {
    background: #27ae60;
}

/* Scrollbar styling for webkit browsers */
#chatbox::-webkit-scrollbar {
    width: 6px;
}

#chatbox::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
}

#chatbox::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

#chatbox::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}
</style>

@php
if (!isset($_SESSION['name'])) {
@endphp
<div id="chat-wrapper">
    <div id="loginform">
        <p>Nhập tên của bạn để chat!</p>
        <form id="login-form" method="post" action="{{ url()->current() }}">
            @csrf
            <label for="name">Tên &#58;</label>
            <input type="text" name="name" id="name" value="{{ isset($_COOKIE['cotuong_name']) ? $_COOKIE['cotuong_name'] : '' }}" />
            <input type="submit" name="enter" id="enter" value="Vào" />
        </form>
        <div id="login-error" class="error"></div>
    </div>
    <div id="chatbox">
        @foreach($chatMessages as $msg)
            <div class='msgln'>
                <span class='chat-time'>{{ $msg->created_at->format('Y-m-d | H:i:s') }}</span>
                @if($msg->type == 'system')
                    <span class='welcome-info'>{{ $msg->message }}</span>
                @elseif($msg->type == 'enter')
                    <span class='enter-info'>Người dùng <b class='user-name-enter'>{{ $msg->username }}</b> {{ $msg->message }}</span>
                @elseif($msg->type == 'leave') 
                    <span class='left-info'>Người dùng <b class='user-name-left'>{{ $msg->username }}</b> {{ $msg->message }}</span>
                @else
                    <b class='user-name'>{{ $msg->username }}</b>: {{ $msg->message }}
                @endif
                <br>
            </div>
        @endforeach
    </div>
</div>
@php
} else {
@endphp
<div id="chat-wrapper">
    <div id="menu">
        <p class="welcome">Chào, <b>@php echo $_SESSION['name']; @endphp</b></p>
        <p class="logout"><a id="exit" href="javascript:void(0);">Thoát chat</a></p>
    </div>
    <div id="chatbox">
        @foreach($chatMessages as $msg)
            <div class='msgln'>
                <span class='chat-time'>{{ $msg->created_at->format('Y-m-d | H:i:s') }}</span>
                @if($msg->type == 'system')
                    <span class='welcome-info'>{{ $msg->message }}</span>
                @elseif($msg->type == 'enter')
                    <span class='enter-info'>Người dùng <b class='user-name-enter'>{{ $msg->username }}</b> {{ $msg->message }}</span>
                @elseif($msg->type == 'leave') 
                    <span class='left-info'>Người dùng <b class='user-name-left'>{{ $msg->username }}</b> {{ $msg->message }}</span>
                @else
                    <b class='user-name'>{{ $msg->username }}</b>: {{ $msg->message }}
                @endif
                <br>
            </div>
        @endforeach
    </div>
    <form name="message" id="message-form">
        <input name="usermsg" type="text" id="usermsg" required="required" placeholder="Nhập tin nhắn..." />
        <input name="submitmsg" type="submit" id="submitmsg" value="Gửi" />
    </form>
</div>
@php
}
@endphp

<script>
// jQuery Document
$(document).ready(function () {
    // Store current username in JavaScript for client-side use
    let currentUser = "{{ isset($_SESSION['name']) ? addslashes($_SESSION['name']) : '' }}";

    // Ensure jQuery is loaded
    if (typeof $ === 'undefined') {
        console.error("jQuery is not loaded. Please ensure jQuery is included.");
        return;
    }

    // Handle login form submission
    $(document).on("submit", "#login-form", function (e) {
        e.preventDefault(); // Prevent default form submission
        e.stopImmediatePropagation(); // Stop any other handlers

        const name = $("#name").val().trim();
        if (name === "") {
            $("#login-error").text("Vui lòng nhập tên");
            return false;
        }

        // Update UI immediately
        currentUser = name;
        $("#chat-wrapper").html(`
            <div id="menu">
                <p class="welcome">Chào, <b>${name}</b></p>
                <p class="logout"><a id="exit" href="javascript:void(0);">Thoát chat</a></p>
            </div>
            <div id="chatbox"></div>
            <form name="message" id="message-form">
                <input name="usermsg" type="text" id="usermsg" required="required" placeholder="Nhập tin nhắn..." />
                <input name="submitmsg" type="submit" id="submitmsg" value="Gửi" />
            </form>
        `);

        // Submit login to server via AJAX
        $.ajax({
            url: "{{ url()->current() }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                enter: "Vào"
            },
            success: function (response) {
                console.log("Login successful:", response);
                loadLog(); // Load chat log after successful login
            },
            error: function (xhr, status, error) {
                console.error("Login error:", xhr, status, error);
                $("#login-error").text("Lỗi khi đăng nhập. Vui lòng thử lại.");
                // Revert UI if login fails
                $("#chat-wrapper").html(`
                    <div id="loginform">
                        <p>Nhập tên của bạn để chat!</p>
                        <form id="login-form" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <label for="name">Tên &#58;</label>
                            <input type="text" name="name" id="name" value="${name}" />
                            <input type="submit" name="enter" id="enter" value="Vào" />
                        </form>
                        <div id="login-error" class="error">Lỗi khi đăng nhập. Vui lòng thử lại.</div>
                    </div>
                    <div id="chatbox"></div>
                `);
            }
        });

        return false;
    });

    // Handle message submission
    $("#chat-wrapper").on("submit", "#message-form", function (e) {
        e.preventDefault();
        const clientmsg = $("#usermsg").val().trim();
        if (clientmsg === "") {
            bootbox.alert({
                message: "Vui lòng nhập tin nhắn.",
                size: 'small',
                centerVertical: true,
                locale: 'vi',
                closeButton: false,
                buttons: {
                    ok: {
                        className: 'btn-danger pulse-red'
                    }
                }
            });
            return false;
        }

        // Submit message to server using new database API
        $.post("{{ url('/api') }}/sendChatMessage", {
            roomCode: "{{ $roomCode }}",
            text: clientmsg,
            username: currentUser,
            _token: "{{ csrf_token() }}"
        }).done(function(response) {
            if (response.success) {
                loadLog(); // Reload messages immediately after sending
            }
        }).fail(function(xhr, status, error) {
            console.error("Error sending message:", xhr, status, error);
        });
        $("#usermsg").val("");
        return false;
    });

    // Handle logout
    $("#chat-wrapper").on("click", "#exit", function (e) {
        e.preventDefault();
        bootbox.confirm({
            message: "Kết thúc phiên chat này?",
            centerVertical: true,
            locale: 'vi',
            closeButton: false,
            buttons: {
                confirm: {
                    label: '<i class="fas fa-check"></i> Thoát',
                    className: 'btn-danger pulse-red'
                },
                cancel: {
                    label: '<i class="fas fa-times"></i> Hủy',
                    className: 'btn-dark text-light'
                }
            },
            callback: function (result) {
                if (result) {
                    // Update UI immediately
                    currentUser = "";
                    $("#chat-wrapper").html(`
                        <div id="loginform">
                            <p>Nhập tên của bạn để chat!</p>
                            <form id="login-form" method="post">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <label for="name">Tên &#58;</label>
                                <input type="text" name="name" id="name" value="" />
                                <input type="submit" name="enter" id="enter" value="Vào" />
                            </form>
                            <div id="login-error" class="error"></div>
                        </div>
                        <div id="chatbox"></div>
                    `);

                    // Submit logout to server via AJAX
                    $.ajax({
                        url: "{{ url()->current() }}?logout=true",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            logout: true
                        },
                        success: function (response) {
                            console.log("Logout successful:", response);
                            loadLog(); // Load chat log after successful logout
                        },
                        error: function (xhr, status, error) {
                            console.error("Logout error:", xhr, status, error);
                            $("#login-error").text("Lỗi khi thoát. Vui lòng thử lại.");
                        }
                    });
                }
            }
        });
    });

    // Load chat log from database
    function loadLog() {
        var oldscrollHeight = $("#chatbox")[0].scrollHeight - 20; // Scroll height before the request
        $.ajax({
            url: "{{ url('/api') }}/getChatMessages",
            type: "POST",
            data: {
                roomCode: "{{ $roomCode }}",
                _token: "{{ csrf_token() }}"
            },
            cache: false,
            success: function (data) {
                if (data.success) {
                    let html = '';
                    data.messages.forEach(function(msg) {
                        html += "<div class='msgln'>";
                        html += "<span class='chat-time'>" + msg.formatted_date + "</span> ";
                        
                        if (msg.type === 'system') {
                            html += "<span class='welcome-info'>" + msg.message + "</span>";
                        } else if (msg.type === 'enter') {
                            html += "<span class='enter-info'>Người dùng <b class='user-name-enter'>" + msg.username + "</b> " + msg.message + "</span>";
                        } else if (msg.type === 'leave') {
                            html += "<span class='left-info'>Người dùng <b class='user-name-left'>" + msg.username + "</b> " + msg.message + "</span>";
                        } else {
                            html += "<b class='user-name'>" + msg.username + "</b>: " + msg.message;
                        }
                        html += "<br></div>";
                    });
                    
                    $("#chatbox").html(html); // Insert chat log into the #chatbox div
                    var newscrollHeight = $("#chatbox")[0].scrollHeight - 20; // Scroll height after the request
                    if (newscrollHeight > oldscrollHeight) {
                        $("#chatbox").animate({ scrollTop: newscrollHeight }, 'normal'); // Autoscroll to bottom
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error("Error loading chat log:", xhr, status, error);
            }
        });
    }

    setInterval(loadLog, 1000);
});
</script>