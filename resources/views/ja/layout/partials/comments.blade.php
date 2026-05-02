@php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('CoTuong_JA-'.$roomCode);
    session_start();
}

$room_path = public_path().'/rumuChatLog/'.$roomCode.'-rumuchatlog.html';
$log_path = url('/').'/rumuChatLog/'.$roomCode.'-rumuchatlog.html';

if (!is_file($room_path)) {
    $welcome_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <span class='welcome-info'>ルームが作成されました</span><br></div>";
    file_put_contents($room_path, $welcome_message);
}

if (isset($_GET['logout'])) {
    if (isset($_SESSION['name'])) {
        $logout_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <span class='left-info'>ユーザー <b class='user-name-left'>". $_SESSION['name'] ."</b> がチャット セッションから退出しました。</span><br></div>";
        file_put_contents($room_path, $logout_message, FILE_APPEND | LOCK_EX);
        $_SESSION = [];
        setcookie('cotuong_name', '', time() - 3600, "/");
    }
}

if (isset($_POST['enter'])) {
    if ($_POST['name'] != "") {
        $_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
        setcookie('cotuong_name', $_SESSION['name'], time() + (86400 * 30), "/");
        $login_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <span class='enter-info'>ユーザー <b class='user-name-enter'>". $_SESSION['name'] ."</b> がチャット セッションに参加しました。</span><br></div>";
        file_put_contents($room_path, $login_message, FILE_APPEND | LOCK_EX);
    }
}
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
    margin: 0;
    padding-bottom: 0;
    background: #413e3b;
    max-width: calc(100% - 150px);
    border: 2px solid #413e3b;
    border-radius: 4px;
    color: #eee;
    height: 460px;
    float: left;
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
    border-bottom: 4px solid #a7a7a7
}

#loginform + #chatbox {
    margin-bottom: 10px !important;
}

#usermsg {
    border-radius: 4px;
    border: 1px solid #ff9800;
    margin-left: 9px;
    width: calc(100% - 72px);
    font-size: 18px;
}

#name {
    border-radius: 4px;
    border: 1px solid #ff9800;
    padding: 2px 8px;
    font-size: 18px;
    width: calc(100% - 118px);
}

#submitmsg,
#enter {
    background: #ff0028;
    border: 2px solid #ff0028;
    color: white;
    padding: 4px 10px 2px;
    font-weight: bold;
    border-radius: 4px;
    font-size: 14px;
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
}

#menu p.welcome {
    flex: 1;
}

a#exit {
    color: white;
    background: #c62828;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
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
        <p>チャットに名前を入力してください!</p>
        <form id="login-form" method="post" action="{{ url()->current() }}">
            @csrf
            <label for="name">名前 &#58;</label>
            <input type="text" name="name" id="name" value="{{ isset($_COOKIE['cotuong_name']) ? $_COOKIE['cotuong_name'] : '' }}" />
            <input type="submit" name="enter" id="enter" value="入る" />
        </form>
        <div id="login-error" class="error"></div>
    </div>
    <div id="chatbox">
        @php
        if (file_exists($log_path) && filesize($log_path) > 0) {
            $contents = file_get_contents($log_path);
            echo $contents;
        }
        @endphp
    </div>
</div>
@php
} else {
@endphp
<div id="chat-wrapper">
    <div id="menu">
        <p class="welcome">いらっしゃいませ、<b>@php echo $_SESSION['name']; @endphp</b></p>
        <p class="logout"><a id="exit" href="javascript:void(0);">チャットを終了</a></p>
    </div>
    <div id="chatbox">
        @php
        if (file_exists($log_path) && filesize($log_path) > 0) {
            $contents = file_get_contents($log_path);
            echo $contents;
        }
        @endphp
    </div>
    <form name="message" id="message-form">
        <input name="usermsg" type="text" id="usermsg" required="required" />
        <input name="submitmsg" type="submit" id="submitmsg" value="送信" />
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
            $("#login-error").text("名前を入力してください");
            return false;
        }

        // Update UI immediately
        currentUser = name;
        $("#chat-wrapper").html(`
            <div id="menu">
                <p class="welcome">いらっしゃいませ、<b>${name}</b></p>
                <p class="logout"><a id="exit" href="javascript:void(0);">チャットを終了</a></p>
            </div>
            <div id="chatbox"></div>
            <form name="message" id="message-form">
                <input name="usermsg" type="text" id="usermsg" required="required" />
                <input name="submitmsg" type="submit" id="submitmsg" value="送信" />
            </form>
        `);

        // Submit login to server via AJAX
        $.ajax({
            url: "{{ url()->current() }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                enter: "入る"
            },
            success: function (response) {
                console.log("Login successful:", response);
                loadLog(); // Load chat log after successful login
            },
            error: function (xhr, status, error) {
                console.error("Login error:", xhr, status, error);
                $("#login-error").text("ログイン中にエラーが発生しました。もう一度お試しください。");
                // Revert UI if login fails
                $("#chat-wrapper").html(`
                    <div id="loginform">
                        <p>チャットに名前を入力してください!</p>
                        <form id="login-form" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <label for="name">名前 &#58;</label>
                            <input type="text" name="name" id="name" value="${name}" />
                            <input type="submit" name="enter" id="enter" value="入る" />
                        </form>
                        <div id="login-error" class="error">ログイン中にエラーが発生しました。もう一度お試しください。</div>
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
                message: "メッセージを入力してください。",
                size: 'small',
                centerVertical: true,
                locale: 'ja',
                closeButton: false,
                buttons: {
                    ok: {
                        className: 'btn-danger pulse-red'
                    }
                }
            });
            return false;
        }

        // Submit message to server
        $.post("{{ url('/api') }}/postChatJa", {
            roomCode: "{{ $roomCode }}",
            text: clientmsg,
            _token: "{{ csrf_token() }}"
        });
        $("#usermsg").val("");
        return false;
    });

    // Handle logout
    $("#chat-wrapper").on("click", "#exit", function (e) {
        e.preventDefault();
        bootbox.confirm({
            message: "このチャット セッションを終了しますか?",
            centerVertical: true,
            locale: 'ja',
            closeButton: false,
            buttons: {
                confirm: {
                    label: '<i class="fas fa-check"></i> 終わり',
                    className: 'btn-danger pulse-red'
                },
                cancel: {
                    label: '<i class="fas fa-times"></i> キャンセル',
                    className: 'btn-dark text-light'
                }
            },
            callback: function (result) {
                if (result) {
                    // Update UI immediately
                    currentUser = "";
                    $("#chat-wrapper").html(`
                        <div id="loginform">
                            <p>チャットに名前を入力してください!</p>
                            <form id="login-form" method="post">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <label for="name">名前 &#58;</label>
                                <input type="text" name="name" id="name" value="" />
                                <input type="submit" name="enter" id="enter" value="入る" />
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
                            $("#login-error").text("ログアウト中にエラーが発生しました。もう一度お試しください。");
                        }
                    });
                }
            }
        });
    });

    // Load chat log
    function loadLog() {
        var oldscrollHeight = $("#chatbox")[0].scrollHeight - 20; // Scroll height before the request
        $.ajax({
            url: "{{ $log_path }}",
            cache: false,
            success: function (html) {
                $("#chatbox").html(html); // Insert chat log into the #chatbox div
                var newscrollHeight = $("#chatbox")[0].scrollHeight - 20; // Scroll height after the request
                if (newscrollHeight > oldscrollHeight) {
                    $("#chatbox").animate({ scrollTop: newscrollHeight }, 'normal'); // Autoscroll to bottom
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
