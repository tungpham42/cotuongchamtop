<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Asika\Autolink\AutolinkStatic;

class ChatController extends Controller
{
    /**
     * Map supported locales to their respective folder and suffix parameters.
     */
    private const LOCALE_CONFIGS = [
        'vi' => ['lang' => 'VI', 'folder' => 'phongChatLog',     'suffix' => 'phongchatlog'],
        'en' => ['lang' => 'EN', 'folder' => 'roomChatLog',      'suffix' => 'roomchatlog'],
        'ja' => ['lang' => 'JA', 'folder' => 'rumuChatLog',      'suffix' => 'rumuchatlog'],
        'ko' => ['lang' => 'KO', 'folder' => 'bangChatLog',      'suffix' => 'bangchatlog'],
        'zh' => ['lang' => 'ZH', 'folder' => 'fangjianChatLog',  'suffix' => 'fangjianchatlog'],
    ];

    /**
     * Consolidated chat message handler.
     */
    public function postChat(Request $request)
    {
        $langKey = strtolower($request->input('lang', app()->getLocale()));
        $config = self::LOCALE_CONFIGS[$langKey] ?? self::LOCALE_CONFIGS['en'];

        $roomCode = $request->input('roomCode');
        $text     = $request->input('text');

        session_name("CoTuong_{$config['lang']}-{$roomCode}");
        session_start();

        if (isset($_SESSION['name'])) {
            $text = AutolinkStatic::convertEmail(AutolinkStatic::convert(htmlspecialchars($text)));
            $time = date("Y-m-d | H:i:s");
            $userName = $_SESSION['name'];
            $textMessage = "<div class='msgln'><span class='chat-time'>{$time}</span> <b class='user-name'>{$userName}</b> ".stripslashes($text)."<br></div>";

            $filePath = public_path("/{$config['folder']}/{$roomCode}-{$config['suffix']}.html");
            file_put_contents($filePath, $textMessage, FILE_APPEND | LOCK_EX);
        }

        return response()->json(['status' => 'success']);
    }
}
