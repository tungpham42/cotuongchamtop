@php
session_name('CoTuong_EN-'.$roomCode);
session_start();

$room_path = public_path().'/roomChatLog/'.$roomCode.'-roomchatlog.html';
$log_path = url('/').'/roomChatLog/'.$roomCode.'-roomchatlog.html';
 
if(!is_file($room_path)){
    $welcome_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <span class='welcome-info'>Room created</span><br></div>";
    file_put_contents($room_path, $welcome_message);
}

if(isset($_GET['logout'])){
    if(isset($_SESSION['name'])) {
        //Simple exit message
        $logout_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <span class='left-info'>User <b class='user-name-left'>". $_SESSION['name'] ."</b> has left the chat session.</span><br></div>";
        file_put_contents($room_path, $logout_message, FILE_APPEND | LOCK_EX);
        
        // session_destroy();
        $_SESSION = [];
        header("Location: ".url()->current() ); //Redirect the user
    }
}
 
if(isset($_POST['enter'])){
    if($_POST['name'] != ""){
        $_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
        setcookie('cotuong_name', $_SESSION['name']);
        $login_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <span class='enter-info'>User <b class='user-name-enter'>". $_SESSION['name'] ."</b> has entered the chat session.</span><br></div>";
        file_put_contents($room_path, $login_message, FILE_APPEND | LOCK_EX);
    }
    else{
        echo '<span class="error">Please type in a name</span>';
    }
}
 
function chatLoginForm(){
@endphp
    <div id="loginform">
        <p>Please enter your name to Chat!</p>
        <form action="{{ url()->current() }}" method="post">
            @csrf
            <label for="name">Name &#58;</label>
            <input type="text" name="name" id="name" value="{{ isset($_COOKIE['cotuong_name']) ? $_COOKIE['cotuong_name'] : '' }}" />
            <input type="submit" name="enter" id="enter" value="Enter" />
        </form>
    </div>
@php
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
    height: 420px;
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
    width: calc(100% - 84px);
    font-size: 18px;
}
   
#name {
    border-radius: 4px;
    border: 1px solid #ff9800;
    padding: 2px 8px;
    font-size: 18px;
    width: calc(100% - 134px);
}
   
#submitmsg,
#enter{
    background: #ff0028;
    border: 2px solid #ff0028;
    color: white;
    padding: 4px 10px 2px;
    font-weight: bold;
    border-radius: 4px;
    font-size: 14px;
    margin-right: 9px;
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
if(!isset($_SESSION['name'])){
@endphp
<div id="chat-wrapper">
@php
chatLoginForm();
@endphp
    <div id="chatbox">
    @php
    if(file_exists($log_path) && filesize($log_path) > 0){
        $contents = file_get_contents($log_path);          
        echo $contents;
    }
    @endphp
    </div>
</div>
@php
}
else {
@endphp
<div id="chat-wrapper">
    <div id="menu">
        <p class="welcome">Welcome, <b>@php echo $_SESSION['name']; @endphp</b></p>
        <p class="logout"><a id="exit" href="javascript:void(0);">Exit Chat</a></p>
    </div>

    <div id="chatbox">
    @php
    if(file_exists($log_path) && filesize($log_path) > 0){
        $contents = file_get_contents($log_path);          
        echo $contents;
    }
    @endphp
    </div>

    <form name="message">
        <input name="usermsg" type="text" id="usermsg" required="required" />
        <input name="submitmsg" type="submit" id="submitmsg" value="Send" />
    </form>
</div>
@php
}
@endphp
<script type="text/javascript">
// jQuery Document
$(document).ready(function () {
    $("#submitmsg").click(function (e) {
        e.preventDefault();
        if ($("#usermsg").val() != '') {
            var clientmsg = $("#usermsg").val();
            $.post("{{ url('/api') }}/postChat", { roomCode: "{{ $roomCode }}", text: clientmsg });
        } else {
            bootbox.alert({
                message: "Please input a message.",
                size: 'small',
                centerVertical: true,
                locale: 'en',
                closeButton: false,
                buttons: {
                    ok: {
                        className: 'btn-danger pulse-red'
                    }
                }
            });
        }
        $("#usermsg").val("");
        return false;
    });

    function loadLog() {
        var oldscrollHeight = $("#chatbox")[0].scrollHeight - 20; //Scroll height before the request

        $.ajax({
            url: "{{ $log_path }}",
            cache: false,
            success: function (html) {
                $("#chatbox").html(html); //Insert chat log into the #chatbox div

                //Auto-scroll           
                var newscrollHeight = $("#chatbox")[0].scrollHeight - 20; //Scroll height after the request
                if(newscrollHeight > oldscrollHeight){
                    $("#chatbox").animate({ scrollTop: newscrollHeight }, 'normal'); //Autoscroll to bottom of div
                }   
            }
        });
    }

    setInterval (loadLog, 1000);

    $("#exit").click(function () {
        bootbox.confirm({
            message: "End this chat session?",
            centerVertical: true,
            locale: 'en',
            closeButton: false,
            buttons: {
                confirm: {
                    label: '<i class="fas fa-check"></i> End',
                    className: 'btn-danger pulse-red'
                },
                cancel: {
                    label: '<i class="fas fa-times"></i> Cancel',
                    className: 'btn-dark text-light'
                }
            },
            callback: function (result) {
                if (result == true) {
                    window.location = "{{ url()->current() }}?logout=true";
                }
            }
        });
    });
});
</script>