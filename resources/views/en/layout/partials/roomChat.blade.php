@php
use App\Models\ChatMessage;

// NOTE: session_start() should typically be handled by Laravel's session middleware,
// but it is included here as it was in the original file.
session_name('CoTuong-'.$roomCode);
session_start();

// Initialize room messages if first time
$messagesCount = ChatMessage::where('room_code', $roomCode)->count();
if ($messagesCount == 0) {
    // Translation: 'Phòng được tạo' -> 'Room created'
    ChatMessage::addMessage($roomCode, 'System', 'Room created', 'system');
}

if (isset($_GET['logout'])) {
    if (isset($_SESSION['name'])) {
        // Translation: 'đã rời khỏi phòng chat' -> 'has left the chat room'
        ChatMessage::addMessage($roomCode, $_SESSION['name'], 'has left the chat room', 'leave');
        $_SESSION = [];
        setcookie('cotuong_name', '', time() - 3600, "/");
    }
}

if (isset($_POST['enter'])) {
    if ($_POST['name'] != "") {
        $_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
        setcookie('cotuong_name', $_SESSION['name'], time() + (86400 * 30), "/");
        // Translation: 'đã vào phòng chat' -> 'has entered the chat room'
        ChatMessage::addMessage($roomCode, $_SESSION['name'], 'has entered the chat room', 'enter');
    }
}

// Get recent messages for display
$chatMessages = ChatMessage::getMessagesForRoom($roomCode);
@endphp

<style>
#loginform form, #chat-wrapper form {
    padding: 9px 0;
    display: block;
    font-size: 14px;
}

#loginform form label, #chat-wrapper form label {
    font-size: 14px;
    font-weight: bold;
    margin-top: 5px;
}

#chat-wrapper {
    margin: 0 0 1rem 0;
    padding-bottom: 0;
    background: #413e3b;
    border: 2px solid #413e3b;
    border-radius: 8px;
    color: #eee;
    height: 350px;
}

#loginform {
    padding-top: 18px;
    text-align: center;
    border: none;
    font-size: 14px;
}

#loginform p {
    padding: 0;
    font-size: 14px;
    font-weight: bold;
}

#chatbox {
    text-align: left;
    margin: 0 auto;
    padding: 10px;
    background: #fff;
    height: calc(100% - 120px);
    width: calc(100% - 20px);
    border: 1px solid #a7a7a7;
    overflow: auto;
    border-radius: 4px;
    border-bottom: 4px solid #a7a7a7;
    font-size: 13px;
}

#loginform + #chatbox {
    margin-bottom: 10px !important;
}

#usermsg {
    border-radius: 4px;
    border: 1px solid #ff9800;
    margin-left: 9px;
    width: calc(100% - 72px);
    font-size: 16px;
    padding: 4px 8px;
}

#name {
    border-radius: 4px;
    border: 1px solid #ff9800;
    padding: 6px 12px;
    font-size: 16px;
    width: calc(100% - 118px);
}

#submitmsg,
#enter {
    background: #ff0028;
    border: 2px solid #ff0028;
    color: white;
    padding: 4px 8px;
    font-weight: bold;
    border-radius: 4px;
    font-size: 12px;
    margin-right: 0;
}

.error {
    color: #ff0000;
    width: 100%;
    text-align: center;
}

#menu {
    padding: 9px;
    display: flex;
    background: #536dfe;
    border-radius: 6px 6px 0 0;
}

#menu p.welcome {
    flex: 1;
    margin: 0;
    font-weight: bold;
}

a#exit {
    color: white;
    background: #c62828;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
    text-decoration: none;
}

.msgln {
    margin: 0 0 5px 0;
    color: #413e3b;
}

.msgln span.welcome-info {
    color: goldenrod;
}

.msgln span.left-info {
    color: orangered;
}

.msgln span.enter-info {
    color: green;
}

.msgln span.chat-time {
    color: #666;
    font-size: 60%;
}

.msgln b.user-name, .msgln b.user-name-left, .msgln b.user-name-enter {
    font-weight: bold;
    background: #546e7a;
    color: white;
    padding: 2px 4px;
    font-size: 90%;
    border-radius: 4px;
    margin: 0 5px 0 0;
}

.msgln b.user-name-left {
    background: orangered;
}

.msgln b.user-name-enter {
    background: green;
}
</style>

@php
if (!isset($_SESSION['name'])) {
@endphp
<div id="chat-wrapper">
    <div id="loginform">
        <p>Enter your name to chat!</p> {{-- 'Nhập tên của bạn để chat!' -> 'Enter your name to chat!' --}}
        <form id="login-form" method="post" action="{{ url()->current() }}">
            @csrf
            <label for="name">Name :</label> {{-- 'Tên &#58;' -> 'Name :' --}}
            <input type="text" name="name" id="name" value="{{ isset($_COOKIE['cotuong_name']) ? $_COOKIE['cotuong_name'] : '' }}" />
            <input type="submit" name="enter" id="enter" value="Enter" /> {{-- 'Vào' -> 'Enter' --}}
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
                    <span class='enter-info'>User <b class='user-name-enter'>{{ $msg->username }}</b> {{ $msg->message }}</span> {{-- 'Người dùng' -> 'User' --}}
                @elseif($msg->type == 'leave')
                    <span class='left-info'>User <b class='user-name-left'>{{ $msg->username }}</b> {{ $msg->message }}</span> {{-- 'Người dùng' -> 'User' --}}
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
        <p class="welcome">Hello, <b>@php echo $_SESSION['name']; @endphp</b></p> {{-- 'Chào,' -> 'Hello,' --}}
        <p class="logout"><a id="exit" href="javascript:void(0);">Exit chat</a></p> {{-- 'Thoát chat' -> 'Exit chat' --}}
    </div>
    <div id="chatbox">
        @foreach($chatMessages as $msg)
            <div class='msgln'>
                <span class='chat-time'>{{ $msg->created_at->format('Y-m-d | H:i:s') }}</span>
                @if($msg->type == 'system')
                    <span class='welcome-info'>{{ $msg->message }}</span>
                @elseif($msg->type == 'enter')
                    <span class='enter-info'>User <b class='user-name-enter'>{{ $msg->username }}</b> {{ $msg->message }}</span> {{-- 'Người dùng' -> 'User' --}}
                @elseif($msg->type == 'leave')
                    <span class='left-info'>User <b class='user-name-left'>{{ $msg->username }}</b> {{ $msg->message }}</span> {{-- 'Người dùng' -> 'User' --}}
                @else
                    <b class='user-name'>{{ $msg->username }}</b>: {{ $msg->message }}
                @endif
                <br>
            </div>
        @endforeach
    </div>
    <form name="message" id="message-form">
        <input name="usermsg" type="text" id="usermsg" required="required" placeholder="Enter message..." /> {{-- 'Nhập tin nhắn...' -> 'Enter message...' --}}
        <input name="submitmsg" type="submit" id="submitmsg" value="Send" /> {{-- 'Gửi' -> 'Send' --}}
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
            // Translation: 'Vui lòng nhập tên' -> 'Please enter a name'
            $("#login-error").text("Please enter a name");
            return false;
        }

        // Update UI immediately (with English text)
        currentUser = name;
        $("#chat-wrapper").html(`
            <div id="menu">
                <p class="welcome">Hello, <b>${name}</b></p>
                <p class="logout"><a id="exit" href="javascript:void(0);">Exit chat</a></p>
            </div>
            <div id="chatbox"></div>
            <form name="message" id="message-form">
                <input name="usermsg" type="text" id="usermsg" required="required" placeholder="Enter message..." />
                <input name="submitmsg" type="submit" id="submitmsg" value="Send" />
            </form>
        `);

        // Submit login to server via AJAX
        $.ajax({
            url: "{{ url()->current() }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                enter: "Enter"
            },
            success: function (response) {
                console.log("Login successful:", response);
                loadLog(); // Load chat log after successful login
            },
            error: function (xhr, status, error) {
                console.error("Login error:", xhr, status, error);
                // Translation: 'Lỗi khi đăng nhập. Vui lòng thử lại.' -> 'Error during login. Please try again.'
                $("#login-error").text("Error during login. Please try again.");
                // Revert UI if login fails (with English text)
                $("#chat-wrapper").html(`
                    <div id="loginform">
                        <p>Enter your name to chat!</p>
                        <form id="login-form" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <label for="name">Name :</label>
                            <input type="text" name="name" id="name" value="${name}" />
                            <input type="submit" name="enter" id="enter" value="Enter" />
                        </form>
                        <div id="login-error" class="error">Error during login. Please try again.</div>
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
            // Translation: 'Vui lòng nhập tin nhắn.' -> 'Please enter a message.'
            bootbox.alert({
                message: "Please enter a message.",
                size: 'small',
                centerVertical: true,
                locale: 'en', // Set locale to English for Bootbox
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
        // Translation: 'Kết thúc phiên chat này?' -> 'End this chat session?'
        bootbox.confirm({
            message: "End this chat session?",
            centerVertical: true,
            locale: 'en', // Set locale to English for Bootbox
            closeButton: false,
            buttons: {
                confirm: {
                    // Translation: 'Thoát' -> 'Exit'
                    label: '<i class="fas fa-check"></i> Exit',
                    className: 'btn-danger pulse-red'
                },
                cancel: {
                    // Translation: 'Hủy' -> 'Cancel'
                    label: '<i class="fas fa-times"></i> Cancel',
                    className: 'btn-dark text-light'
                }
            },
            callback: function (result) {
                if (result) {
                    // Update UI immediately (with English text)
                    currentUser = "";
                    $("#chat-wrapper").html(`
                        <div id="loginform">
                            <p>Enter your name to chat!</p>
                            <form id="login-form" method="post">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <label for="name">Name :</label>
                                <input type="text" name="name" id="name" value="" />
                                <input type="submit" name="enter" id="enter" value="Enter" />
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
                            // Translation: 'Lỗi khi thoát. Vui lòng thử lại.' -> 'Error during exit. Please try again.'
                            $("#login-error").text("Error during exit. Please try again.");
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
                            // Translation: 'Người dùng' -> 'User'
                            html += "<span class='enter-info'>User <b class='user-name-enter'>" + msg.username + "</b> " + msg.message + "</span>";
                        } else if (msg.type === 'leave') {
                            // Translation: 'Người dùng' -> 'User'
                            html += "<span class='left-info'>User <b class='user-name-left'>" + msg.username + "</b> " + msg.message + "</span>";
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

    // Poll for new messages every 1 second
    setInterval(loadLog, 1000);
});
</script>
