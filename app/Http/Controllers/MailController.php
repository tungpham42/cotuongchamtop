<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Actions\User\GetUserQueriesAction;

class MailController extends Controller
{
    public function sendSmtpMail($recipient, $subject, $content, array $messages)
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
            }
            return response()->json(['message' => $messages[1], 'code' => 1]);
        } catch (Exception $e) {
            return response()->json(['message' => $messages[2], 'code' => 0]);
        }
    }

    /**
     * Unified contact form submission handler using Laravel validation and translations.
     */
    public function sendContact(Request $request)
    {
        $lang = $request->input('lang', app()->getLocale());
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'name'    => 'required',
            'email'   => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ], [
            'name.required'    => __('Name cannot be empty'),
            'email.required'   => __('Email cannot be empty'),
            'email.email'      => __('Email format invalid'),
            'subject.required' => __('Subject cannot be empty'),
            'message.required' => __('Message cannot be empty'),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 0]);
        }

        $name    = $request->input('name');
        $email   = $request->input('email');
        $message = $request->input('message');
        $subject = $request->input('subject');

        $content   = "<p>From: {$name}</p><p>Email: {$email}</p><p>Message: {$message}</p>";
        $recipient = "tung.42@gmail.com";

        $smtpMessages = [
            __('Email not sent'),
            __('Email has been sent'),
            __('Message could not be sent')
        ];

        return $this->sendSmtpMail($recipient, $subject, $content, $smtpMessages);
    }

    public function competeMail(Request $request)
    {
        $roomCode = $request->input('ma-phong');
        $roomName = $request->input('ten-phong');
        $lang     = $request->input('lang', app()->getLocale());

        app()->setLocale($lang);

        $hostId     = $request->input('host_id');
        $guestId    = $request->input('guest_id');
        $userQuery  = app(GetUserQueriesAction::class);
        $hostName   = $userQuery->getUserName($hostId);
        $guestName  = $userQuery->getUserName($guestId);
        $guestEmail = $userQuery->getUserEmail($guestId);

        $roomUrl = localized_url('room.guest', ['code' => $roomCode]);

        return $this->sendSmtpMail(
            $guestEmail,
            __('Xiangqi Challenge on cotuong.top: An Invitation from ":host"', ['host' => $hostName]),
            __('compete_email_body', [
                'guest' => $guestName,
                'room'  => $roomName,
                'url'   => $roomUrl,
                'host'  => $hostName
            ]),
            [
                __('Invitation to ":guest" failed', ['guest' => $guestName]),
                __('Invitation to ":guest" sent successfully', ['guest' => $guestName]),
                __('Message could not be sent')
            ]
        );
    }
}
