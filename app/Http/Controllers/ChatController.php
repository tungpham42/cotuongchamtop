<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Asika\Autolink\AutolinkStatic;

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
            $text_message = "<div class='msgln'><span class='chat-time'>".date("Y-m-d | H:i:s")."</span> <b class='user-name'>".$_SESSION['name']."</b> ".stripslashes($text)."<br></div>";
            file_put_contents( public_path().'/phongChatLog/'.$roomCode.'-phongchatlog.html' , $text_message, FILE_APPEND | LOCK_EX);
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
