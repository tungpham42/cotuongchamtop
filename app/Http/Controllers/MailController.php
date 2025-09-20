<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Mail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Http\Controllers\UserController;
use Config;

class MailController extends Controller
{
    public function sendSmtpMail($recipient, $subject, $content, $messages)
    {
        $mail = new PHPMailer(true);                                        // Passing `true` enables exceptions
        try {
            // Email server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = env('MAIL_HOST');                                 //  smtp host
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');                         //  sender username
            $mail->Password = env('MAIL_PASSWORD');                         // sender password
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');                     // encryption - ssl/tls
            $mail->Port = env('MAIL_PORT');                                 // port - 587/465
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($recipient);
            $mail->addReplyTo($recipient, 'Tung Pham');
            $mail->isHTML(true);                                            // Set email content format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $content;
            // $mail->AltBody = plain text version of email body;
            if( !$mail->send() ) {
                echo json_encode(array('message' => $messages[0], 'code' => 0));
                exit();
            }
            else {
                echo json_encode(array('message' => $messages[1], 'code' => 1));
                exit();
            }
        } catch (Exception $e) {
            echo json_encode(array('message' => $messages[2], 'code' => 0));
            exit();
        }
    }
    
    public function send(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');
        $subject = $request->input('subject');
        if (!$request->input('name') || $name === '') {
            print json_encode(array('message' => 'Name cannot be empty', 'code' => 0));
            exit();
        }
        if (!$request->input('email') || $email === '') {
            print json_encode(array('message' => 'Email cannot be empty', 'code' => 0));
            exit();
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            print json_encode(array('message' => 'Email format invalid', 'code' => 0));
            exit();
        }
        if (!$request->input('subject') || $subject === '') {
            print json_encode(array('message' => 'Subject cannot be empty', 'code' => 0));
            exit();
        }
        if (!$request->input('message') || $message === '') {
            print json_encode(array('message' => 'Message cannot be empty', 'code' => 0));
            exit();
        }
        $content = "<p>From: $name</p><p>Email: $email</p><p>Message: $message</p>";
        $recipient = "tung.42@gmail.com";
        $this->sendSmtpMail($recipient, $subject, $content, $messages[] = ['Email not sent', 'Email has been sent', 'Message could not be sent']);
    }
    
    public function sendJa(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');
        $subject = $request->input('subject');
        if (!$request->input('name') || $name === '') {
            print json_encode(array('message' => '名前を空にすることはできません', 'code' => 0));
            exit();
        }
        if (!$request->input('email') || $email === '') {
            print json_encode(array('message' => '電子メールを空にすることはできません', 'code' => 0));
            exit();
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            print json_encode(array('message' => 'メール形式が無効です', 'code' => 0));
            exit();
        }
        if (!$request->input('subject') || $subject === '') {
            print json_encode(array('message' => '件名を空にすることはできません', 'code' => 0));
            exit();
        }
        if (!$request->input('message') || $message === '') {
            print json_encode(array('message' => 'メッセージを空にすることはできません', 'code' => 0));
            exit();
        }
        $content = "<p>From: $name</p><p>Email: $email</p><p>Message: $message</p>";
        $recipient = "tung.42@gmail.com";
        $this->sendSmtpMail($recipient, $subject, $content, $messages[] = ['メールが送信されない', 'メールが送信されました', 'メッセージを送信できませんでした']);
    }
    
    public function sendKo(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');
        $subject = $request->input('subject');
        if (!$request->input('name') || $name === '') {
            print json_encode(array('message' => '이름은 비워 둘 수 없습니다.', 'code' => 0));
            exit();
        }
        if (!$request->input('email') || $email === '') {
            print json_encode(array('message' => '이메일은 비워둘 수 없습니다.', 'code' => 0));
            exit();
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            print json_encode(array('message' => '이메일 형식이 잘못되었습니다.', 'code' => 0));
            exit();
        }
        if (!$request->input('subject') || $subject === '') {
            print json_encode(array('message' => '제목은 비워 둘 수 없습니다.', 'code' => 0));
            exit();
        }
        if (!$request->input('message') || $message === '') {
            print json_encode(array('message' => '메시지는 비워 둘 수 없습니다.', 'code' => 0));
            exit();
        }
        $content = "<p>From: $name</p><p>Email: $email</p><p>Message: $message</p>";
        $recipient = "tung.42@gmail.com";
        $this->sendSmtpMail($recipient, $subject, $content, $messages[] = ['이메일이 전송되지 않음', '이메일이 전송되었습니다.', '메시지를 보내지 못했습니다.']);
    }
    
    public function sendZh(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');
        $subject = $request->input('subject');
        if (!$request->input('name') || $name === '') {
            print json_encode(array('message' => '名称不能为空', 'code' => 0));
            exit();
        }
        if (!$request->input('email') || $email === '') {
            print json_encode(array('message' => '电子邮件不能为空', 'code' => 0));
            exit();
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            print json_encode(array('message' => '电子邮件格式无效', 'code' => 0));
            exit();
        }
        if (!$request->input('subject') || $subject === '') {
            print json_encode(array('message' => '主题不能为空', 'code' => 0));
            exit();
        }
        if (!$request->input('message') || $message === '') {
            print json_encode(array('message' => '消息不能为空', 'code' => 0));
            exit();
        }
        $content = "<p>From: $name</p><p>Email: $email</p><p>Message: $message</p>";
        $recipient = "tung.42@gmail.com";
        $this->sendSmtpMail($recipient, $subject, $content, $messages[] = ['未发送电子邮件', '电子邮件已发送', '无法发送消息']);
    }

    public function gui(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');
        $subject = $request->input('subject');
        if (!$request->input('name') || $name === '') {
            print json_encode(array('message' => 'Họ tên không được để trống', 'code' => 0));
            exit();
        }
        if (!$request->input('email') || $email === '') {
            print json_encode(array('message' => 'Email không được để trống', 'code' => 0));
            exit();
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            print json_encode(array('message' => 'Định dạng email sai', 'code' => 0));
            exit();
        }
        if (!$request->input('subject') || $subject === '') {
            print json_encode(array('message' => 'Chủ đề không được để trống', 'code' => 0));
            exit();
        }
        if (!$request->input('message') || $message === '') {
            print json_encode(array('message' => 'Tin nhắn không được để trống', 'code' => 0));
            exit();
        }
        $content = "<p>Từ: $name</p><p>Email: $email</p><p>Tin nhắn: $message</p>";
        $recipient = "tung.42@gmail.com";
        $this->sendSmtpMail($recipient, $subject, $content, $messages[] = ['Email không gửi được', 'Email gửi thành công', 'Tin nhắn không gửi được']);
    }

    public function competeMail(Request $request)
    {
        $roomCode = $request->input('ma-phong');
        $roomName = $request->input('ten-phong');
        $hostId = $request->input('host_id');
        $guestId = $request->input('guest_id');

        $hostName = UserController::getUserName($hostId);
        $guestName = UserController::getUserName($guestId);

        $hostEmail = UserController::getUserEmail($hostId);
        $guestEmail = UserController::getUserEmail($guestId);

        $content = "<p>Chào $guestName,</p>
        <p>Tôi hy vọng bạn đang có một ngày tốt lành!</p>
        <p>Tôi muốn mời bạn tham gia vào một trận cờ tướng thú vị trên trang cotuong.top.</p>
        <p>Tôi đã thấy bạn tham gia vào cộng đồng cờ tướng trực tuyến này và tôi rất muốn có cơ hội thách đấu và học hỏi từ bạn. Chúng ta có thể cùng nhau trải nghiệm niềm vui của trí tuệ và chiến thuật trong một trận đấu đầy kích thích.</p>
        <p>Đường dẫn tới phòng \"$roomName\" tại đây: <a target=\"_blank\" href=\"".URL::to('/phong/')."/".$roomCode."/khach\">".URL::to('/phong/')."/".$roomCode."/khach</a></p>
        <p>Rất mong nhận được phản hồi từ bạn sớm nhất. Cảm ơn bạn đã dành thời gian để đọc email này.</p>        
        <p>Trân trọng,</p>
        <p>$hostName</p>";

        $recipient = $guestEmail;
        $subject = 'Thách Đấu Cờ Tướng Trên cotuong.top: Một Lời Mời Từ "' . $hostName . '"';
        $this->sendSmtpMail($recipient, $subject, $content, $messages[] = ['Lời mời đến "' . $guestName . '" không gửi được', 'Lời mời đến "' . $guestName . '" gửi thành công', 'Tin nhắn không gửi được']);
    }
}