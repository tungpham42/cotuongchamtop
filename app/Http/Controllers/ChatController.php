<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Asika\Autolink\AutolinkStatic;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    public function dang(Request $request) {
        $roomCode = $request->input('roomCode');
        $text = $request->input('text');
        session_name('CoTuong_VI-'.$roomCode);
        session_start();
        if (isset($_SESSION['name'])){
            $text = htmlspecialchars($text);
            $text = AutolinkStatic::convert($text);
            $text = AutolinkStatic::convertEmail($text);
            
            // Save to database instead of file
            ChatMessage::addMessage(
                $roomCode, 
                $_SESSION['name'], 
                stripslashes($text), 
                'message', 
                $request->ip()
            );
            
            // Clean old messages periodically
            ChatMessage::cleanOldMessages($roomCode);
        }
    }

    // Get messages for a room from database
    public function getMessages(Request $request) {
        try {
            $roomCode = $request->input('roomCode');
            $messages = ChatMessage::getMessagesForRoom($roomCode);
            
            // Format messages for frontend
            $formattedMessages = $messages->map(function($msg) {
                return [
                    'id' => $msg->id,
                    'username' => $msg->username,
                    'message' => $msg->message,
                    'type' => $msg->type,
                    'created_at' => $msg->created_at,
                    'formatted_date' => $msg->created_at->format('Y-m-d | H:i:s')
                ];
            });
            
            return response()->json([
                'success' => true,
                'messages' => $formattedMessages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Send message to database
    public function sendMessage(Request $request) {
        try {
            $roomCode = $request->input('roomCode');
            $text = $request->input('text');
            $username = $request->input('username');
            
            if (empty($username) || empty($text)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Username and message are required'
                ], 400);
            }
            
            $text = htmlspecialchars($text);
            $text = AutolinkStatic::convert($text);
            $text = AutolinkStatic::convertEmail($text);
            
            $message = ChatMessage::addMessage(
                $roomCode, 
                $username, 
                stripslashes($text), 
                'message', 
                $request->ip()
            );
            
            // Clean old messages periodically
            ChatMessage::cleanOldMessages($roomCode);
            
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'username' => $message->username,
                    'message' => $message->message,
                    'type' => $message->type,
                    'formatted_date' => $message->created_at->format('Y-m-d | H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function post(Request $request) {
        $roomCode = $request->input('roomCode');
        $text = $request->input('text');
        session_name('CoTuong_EN-'.$roomCode);
        session_start();
        if (isset($_SESSION['name'])){
            $text = htmlspecialchars($text);
            $text = AutolinkStatic::convert($text);
            $text = AutolinkStatic::convertEmail($text);
            $text_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <b class='user-name'>".$_SESSION['name']."</b> ".stripslashes($text)."<br></div>";
            file_put_contents( public_path().'/roomChatLog/'.$roomCode.'-roomchatlog.html' , $text_message, FILE_APPEND | LOCK_EX);
        }
    }
    public function postJa(Request $request) {
        $roomCode = $request->input('roomCode');
        $text = $request->input('text');
        session_name('CoTuong_JA-'.$roomCode);
        session_start();
        if (isset($_SESSION['name'])){
            $text = htmlspecialchars($text);
            $text = AutolinkStatic::convert($text);
            $text = AutolinkStatic::convertEmail($text);
            $text_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <b class='user-name'>".$_SESSION['name']."</b> ".stripslashes($text)."<br></div>";
            file_put_contents( public_path().'/rumuChatLog/'.$roomCode.'-rumuchatlog.html' , $text_message, FILE_APPEND | LOCK_EX);
        }
    }
    public function postKo(Request $request) {
        $roomCode = $request->input('roomCode');
        $text = $request->input('text');
        session_name('CoTuong_KO-'.$roomCode);
        session_start();
        if (isset($_SESSION['name'])){
            $text = htmlspecialchars($text);
            $text = AutolinkStatic::convert($text);
            $text = AutolinkStatic::convertEmail($text);
            $text_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <b class='user-name'>".$_SESSION['name']."</b> ".stripslashes($text)."<br></div>";
            file_put_contents( public_path().'/bangChatLog/'.$roomCode.'-bangchatlog.html' , $text_message, FILE_APPEND | LOCK_EX);
        }
    }
    public function postZh(Request $request) {
        $roomCode = $request->input('roomCode');
        $text = $request->input('text');
        session_name('CoTuong_ZH-'.$roomCode);
        session_start();
        if (isset($_SESSION['name'])){
            $text = htmlspecialchars($text);
            $text = AutolinkStatic::convert($text);
            $text = AutolinkStatic::convertEmail($text);
            $text_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <b class='user-name'>".$_SESSION['name']."</b> ".stripslashes($text)."<br></div>";
            file_put_contents( public_path().'/fangjianChatLog/'.$roomCode.'-fangjianchatlog.html' , $text_message, FILE_APPEND | LOCK_EX);
        }
    }
}
