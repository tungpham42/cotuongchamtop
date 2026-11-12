<?php

namespace App\Http\Controllers;

use App\Services\ChatLogService;
use Asika\Autolink\AutolinkStatic;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatLogService $chatLogs)
    {
    }

    /**
     * Consolidated chat message handler for every supported locale.
     *
     * Per-locale log folder, file suffix and session name come from
     * config('locales.chat') via ChatLogService.
     */
    public function postChat(Request $request)
    {
        $locale   = strtolower((string) $request->input('lang', app()->getLocale()));
        $roomCode = $this->chatLogs->normalizeRoomCode($request->input('roomCode'));
        $text     = (string) $request->input('text');

        if ($roomCode === null) {
            return response()->json(['status' => 'error', 'message' => 'Invalid room code'], 422);
        }

        session_name($this->chatLogs->sessionName($roomCode, $locale));
        session_start();

        if (isset($_SESSION['name'])) {
            $text = AutolinkStatic::convertEmail(AutolinkStatic::convert(htmlspecialchars($text)));
            $time = date("Y-m-d | H:i:s");
            $userName = $_SESSION['name'];
            $textMessage = "<div class='msgln'><span class='chat-time'>{$time}</span> <b class='user-name'>{$userName}</b> ".stripslashes($text)."<br></div>";

            $this->chatLogs->append($roomCode, $textMessage, $locale);
        }

        return response()->json(['status' => 'success']);
    }
}
