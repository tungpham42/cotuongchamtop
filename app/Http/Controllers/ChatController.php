<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Asika\Autolink\AutolinkStatic;

class ChatController extends Controller
{
    private function processChatLog(Request $request, string $langCode, string $folder, string $suffix)
    {
        $roomCode = $request->input('roomCode');
        $text = $request->input('text');

        session_name("CoTuong_{$langCode}-{$roomCode}");
        session_start();

        if (isset($_SESSION['name'])) {
            $text = AutolinkStatic::convertEmail(AutolinkStatic::convert(htmlspecialchars($text)));
            $time = date("Y-m-d | H:i:s");
            $userName = $_SESSION['name'];
            $text_message = "<div class='msgln'><span class='chat-time'>{$time}</span> <b class='user-name'>{$userName}</b> ".stripslashes($text)."<br></div>";

            $filePath = public_path("/{$folder}/{$roomCode}-{$suffix}.html");
            file_put_contents($filePath, $text_message, FILE_APPEND | LOCK_EX);
        }
    }

    public function postVi(Request $request) { $this->processChatLog($request, 'VI', 'phongChatLog', 'phongchatlog'); }
    public function postEn(Request $request) { $this->processChatLog($request, 'EN', 'roomChatLog', 'roomchatlog'); }
    public function postJa(Request $request) { $this->processChatLog($request, 'JA', 'rumuChatLog', 'rumuchatlog'); }
    public function postKo(Request $request) { $this->processChatLog($request, 'KO', 'bangChatLog', 'bangchatlog'); }
    public function postZh(Request $request) { $this->processChatLog($request, 'ZH', 'fangjianChatLog', 'fangjianchatlog'); }
}
