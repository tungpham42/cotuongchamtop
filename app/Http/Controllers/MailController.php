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
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port = env('MAIL_PORT');
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($recipient);
            $mail->addReplyTo($recipient, 'Tung Pham');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $content;

            if (!$mail->send()) {
                return response()->json(['message' => $messages[0], 'code' => 0]);
            } else {
                return response()->json(['message' => $messages[1], 'code' => 1]);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $messages[2], 'code' => 0]);
        }
    }

    /**
     * Centralized logic for contact form validation and formatting.
     */
    private function processContactForm(Request $request, array $langStrings)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');
        $subject = $request->input('subject');

        if (!$name || $name === '') return response()->json(['message' => $langStrings['name'], 'code' => 0]);
        if (!$email || $email === '') return response()->json(['message' => $langStrings['email'], 'code' => 0]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return response()->json(['message' => $langStrings['email_invalid'], 'code' => 0]);
        if (!$subject || $subject === '') return response()->json(['message' => $langStrings['subject'], 'code' => 0]);
        if (!$message || $message === '') return response()->json(['message' => $langStrings['message'], 'code' => 0]);

        $content = "<p>From: $name</p><p>Email: $email</p><p>Message: $message</p>";
        $recipient = "tung.42@gmail.com";

        return $this->sendSmtpMail($recipient, $subject, $content, $langStrings['smtp']);
    }

    public function sendEn(Request $request) {
        return $this->processContactForm($request, [
            'name' => 'Name cannot be empty', 'email' => 'Email cannot be empty', 'email_invalid' => 'Email format invalid',
            'subject' => 'Subject cannot be empty', 'message' => 'Message cannot be empty',
            'smtp' => ['Email not sent', 'Email has been sent', 'Message could not be sent']
        ]);
    }

    public function sendJa(Request $request) {
        return $this->processContactForm($request, [
            'name' => '名前を空にすることはできません', 'email' => '電子メールを空にすることはできません', 'email_invalid' => 'メール形式が無効です',
            'subject' => '件名を空にすることはできません', 'message' => 'メッセージを空にすることはできません',
            'smtp' => ['メールが送信されない', 'メールが送信されました', 'メッセージを送信できませんでした']
        ]);
    }

    public function sendKo(Request $request) {
        return $this->processContactForm($request, [
            'name' => '이름은 비워 둘 수 없습니다.', 'email' => '이메일은 비워둘 수 없습니다.', 'email_invalid' => '이메일 형식이 잘못되었습니다.',
            'subject' => '제목은 비워 둘 수 없습니다.', 'message' => '메시지는 비워 둘 수 없습니다.',
            'smtp' => ['이메일이 전송되지 않음', '이메일이 전송되었습니다.', '메시지를 보내지 못했습니다.']
        ]);
    }

    public function sendZh(Request $request) {
        return $this->processContactForm($request, [
            'name' => '名称不能为空', 'email' => '电子邮件不能为空', 'email_invalid' => '电子邮件格式无效',
            'subject' => '主题不能为空', 'message' => '消息不能为空',
            'smtp' => ['未发送电子邮件', '电子邮件已发送', '无法发送消息']
        ]);
    }

    public function sendVi(Request $request) {
        return $this->processContactForm($request, [
            'name' => 'Họ tên không được để trống', 'email' => 'Email không được để trống', 'email_invalid' => 'Định dạng email sai',
            'subject' => 'Chủ đề không được để trống', 'message' => 'Tin nhắn không được để trống',
            'smtp' => ['Email không gửi được', 'Email gửi thành công', 'Tin nhắn không gửi được']
        ]);
    }

    public function competeMail(Request $request)
    {
        // Remains unchanged as its logic is unique
        $roomCode = $request->input('ma-phong');
        $roomName = $request->input('ten-phong');
        $hostId = $request->input('host_id');
        $guestId = $request->input('guest_id');

        $hostName = UserController::getUserName($hostId);
        $guestName = UserController::getUserName($guestId);
        $guestEmail = UserController::getUserEmail($guestId);

        $content = "<p>Chào $guestName,</p>
        <p>Tôi hy vọng bạn đang có một ngày tốt lành!</p>
        <p>Tôi muốn mời bạn tham gia vào một trận cờ tướng thú vị trên trang cotuong.top.</p>
        <p>Tôi đã thấy bạn tham gia vào cộng đồng cờ tướng trực tuyến này và tôi rất muốn có cơ hội thách đấu và học hỏi từ bạn.</p>
        <p>Đường dẫn tới phòng \"$roomName\" tại đây: <a target=\"_blank\" href=\"".url('/phong/')."/".$roomCode."/khach\">".url('/phong/')."/".$roomCode."/khach</a></p>
        <p>Trân trọng,</p>
        <p>$hostName</p>";

        $subject = 'Thách Đấu Cờ Tướng Trên cotuong.top: Một Lời Mời Từ "' . $hostName . '"';
        return $this->sendSmtpMail($guestEmail, $subject, $content, ['Lời mời đến "' . $guestName . '" không gửi được', 'Lời mời đến "' . $guestName . '" gửi thành công', 'Tin nhắn không gửi được']);
    }
}
