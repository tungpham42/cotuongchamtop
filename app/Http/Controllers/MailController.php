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
        $roomCode = $request->input('ma-phong');
        $roomName = $request->input('ten-phong');
        $lang     = $request->input('lang', 'vi'); // Default to vi

        // Set the application locale so helpers like localized_url() use the correct language
        app()->setLocale($lang);

        // Consolidate user data retrieval
        $hostId     = $request->input('host_id');
        $guestId    = $request->input('guest_id');
        $hostName   = UserController::getUserName($hostId);
        $guestName  = UserController::getUserName($guestId);
        $guestEmail = UserController::getUserEmail($guestId);

        // DRY: Build the URL once using string interpolation
        // localized_url will now respect the locale set above
        $roomUrl = localized_url('room.guest', ['code' => $roomCode]);

        // DRY: Delegate template and translation logic to a helper
        $emailData = $this->getCompeteEmailContent($lang, $hostName, $guestName, $roomName, $roomUrl);

        return $this->sendSmtpMail(
            $guestEmail,
            $emailData['subject'],
            $emailData['content'],
            $emailData['smtp_messages']
        );
    }

    /**
     * Centralized translation dictionary for the compete email.
     * Supports Vietnamese, English, Japanese, Korean, and Chinese.
     */
    private function getCompeteEmailContent(string $lang, string $hostName, string $guestName, string $roomName, string $roomUrl): array
    {
        $translations = [
            'en' => [
                'subject' => "Xiangqi Challenge on cotuong.top: An Invitation from \"{$hostName}\"",
                'content' => "<p>Hi {$guestName},</p>
                              <p>I hope you are having a good day!</p>
                              <p>I would like to invite you to an exciting game of Xiangqi on cotuong.top.</p>
                              <p>I've seen you participate in this online community and would love the chance to challenge and learn from you.</p>
                              <p>Link to room \"{$roomName}\" here: <a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>Best regards,</p>
                              <p>{$hostName}</p>",
                'smtp_messages' => [
                    "Invitation to \"{$guestName}\" failed",
                    "Invitation to \"{$guestName}\" sent successfully",
                    "Message could not be sent" //[cite: 1]
                ]
            ],

            'vi' => [
                'subject' => "Thách Đấu Cờ Tướng Trên cotuong.top: Một Lời Mời Từ \"{$hostName}\"",
                'content' => "<p>Chào {$guestName},</p>
                              <p>Tôi hy vọng bạn đang có một ngày tốt lành!</p>
                              <p>Tôi muốn mời bạn tham gia vào một trận cờ tướng thú vị trên trang cotuong.top.</p>
                              <p>Tôi đã thấy bạn tham gia vào cộng đồng cờ tướng trực tuyến này và tôi rất muốn có cơ hội thách đấu và học hỏi từ bạn.</p>
                              <p>Đường dẫn tới phòng \"{$roomName}\" tại đây: <a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>Trân trọng,</p>
                              <p>{$hostName}</p>",
                'smtp_messages' => [
                    "Lời mời đến \"{$guestName}\" không gửi được",
                    "Lời mời đến \"{$guestName}\" gửi thành công",
                    "Tin nhắn không gửi được" //[cite: 1]
                ]
            ],

            'ja' => [
                'subject' => "cotuong.topでの将棋（シャンチー）の対局：「{$hostName}」からの招待",
                'content' => "<p>{$guestName}さん、こんにちは。</p>
                              <p>良い一日をお過ごしのことと思います！</p>
                              <p>cotuong.topでのエキサイティングな将棋（シャンチー）の対局にあなたを招待したいと思います。</p>
                              <p>このオンラインコミュニティに参加されているのをお見かけし、ぜひ対局してあなたから学ぶ機会をいただきたいと思いました。</p>
                              <p>ルーム「{$roomName}」へのリンクはこちらです：<a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>よろしくお願いします。</p>
                              <p>{$hostName}</p>",
                'smtp_messages' => [
                    "「{$guestName}」への招待を送信できませんでした",
                    "「{$guestName}」への招待が送信されました",
                    "メッセージを送信できませんでした" //[cite: 1]
                ]
            ],

            'ko' => [
                'subject' => "cotuong.top 샹치 대결: \"{$hostName}\"님의 초대",
                'content' => "<p>안녕하세요 {$guestName}님,</p>
                              <p>좋은 하루 보내고 계시길 바랍니다!</p>
                              <p>cotuong.top에서 흥미진진한 샹치 게임에 초대하고 싶습니다.</p>
                              <p>이 온라인 커뮤니티에서 활동하시는 모습을 보았으며, 기회가 된다면 대결을 통해 많은 것을 배우고 싶습니다.</p>
                              <p>\"{$roomName}\" 방 링크는 다음과 같습니다: <a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>감사합니다.</p>
                              <p>{$hostName}</p>",
                'smtp_messages' => [
                    "\"{$guestName}\"님에게 초대를 보내지 못했습니다.",
                    "\"{$guestName}\"님에게 초대가 성공적으로 전송되었습니다.",
                    "메시지를 보내지 못했습니다." //[cite: 1]
                ]
            ],

            'zh' => [
                'subject' => "cotuong.top 上的象棋挑战：“{$hostName}”的邀请",
                'content' => "<p>你好 {$guestName}，</p>
                              <p>祝你今天过得愉快！</p>
                              <p>我想邀请你在 cotuong.top 上进行一场精彩的象棋比赛。</p>
                              <p>我看到你参与了这个在线社区，希望能有机会与你切磋并向你学习。</p>
                              <p>房间“{$roomName}”的链接在这里：<a target=\"_blank\" href=\"{$roomUrl}\">{$roomUrl}</a></p>
                              <p>顺祝商祺，</p>
                              <p>{$hostName}</p>",
                'smtp_messages' => [
                    "无法向“{$guestName}”发送邀请",
                    "已成功向“{$guestName}”发送邀请",
                    "无法发送消息" //[cite: 1]
                ]
            ],
        ];

        // Fallback to Vietnamese if the requested language is missing or invalid
        return $translations[$lang] ?? $translations['vi'];
    }
}
