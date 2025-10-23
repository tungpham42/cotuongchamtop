<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Translation Lines - Japanese
    |--------------------------------------------------------------------------
    |
    | Japanese translation file for Xiangqi application
    | Translated from Vietnamese base
    |
    */

    'navigation' => [
        'brand' => '中国象棋',
        'menu' => 'メニュー',
        'home' => 'ホーム',
        'compete' => '競争',
        'waiting_room' => '待機室',
        'members' => 'メンバー',
        'puzzles' => 'パズル',
        'competing' => '競争中',
        'leaderboard' => 'リーダーボード',
        'search_players' => 'プレイヤー検索',
        'match_history' => '試合履歴',
        'forum' => 'フォーラム',
        'facebook_group' => 'Facebookグループ',
        'about' => '概要',
        'contact' => '連絡先',
        'login' => 'ログイン',
        'register' => '登録',
        'logout' => 'ログアウト',
        'my_profile' => 'マイプロフィール',
        'change_name' => '名前変更',
        'change_theme' => 'テーマ変更',
        'change_password' => 'パスワード変更',
        
        // Theme selector
        'board_color' => 'ボード色',
        'default_board' => 'デフォルトボード',
        'piece_style' => '駒スタイル',
        'default_pieces' => 'デフォルト駒',
    ],

    'game' => [
        // Room management
        'room_code' => 'ルームコード',
        'room_code_label' => 'ルームコード：',
        
        // Player actions
        'invite_friends' => '友達を一緒にプレイに招待',
        'invite_link_text' => '下のリンクを送って友達をプレイに招待してください。',
        'invited_success' => '正常に招待されました',
        
        // Player sides
        'red_player' => '赤プレイヤー',
        'black_player' => '黒プレイヤー',
        'red_side' => '赤サイド',
        'black_side' => '黒サイド',
        'red_turn' => '赤の番',
        'black_turn' => '黒の番',
        
        // Game states
        'timer' => 'タイマー',
        'start_game' => '開始',
        'end_game' => 'ゲーム終了',
        'waiting' => '待機中',
        'your_turn' => 'あなたの番',
        'opponent_turn' => '相手の番',
        
        // Pieces
        'king' => '将軍',
        'advisor' => '士',
        'elephant' => '象',
        'horse' => '馬',
        'chariot' => '車',
        'cannon' => '砲',
        'soldier' => '兵',
        
        // Piece descriptions (for about page)
        'king_description_1' => '将軍は一度に一つのポイントずつ水平または垂直に移動し、常に宮殿内に留まらなければなりません。戦闘能力において、将軍は一つのポイントしか移動できず宮殿に限定されているため、最も弱い駒です。',
        'king_description_2' => '将軍は宮殿に固定されており、両側に最大2つの士と象が守っています。これがゲームを勝敗を決めるのを困難にし、引き分けの可能性を高めます。',
        'advisor_description_1' => '士は各移動で斜めに一つのポイントずつ移動し、常に宮殿内に留まらなければなりません。士は5つの有効な交差点を持ち、最も弱い駒です。',
        'advisor_description_2' => '士は将軍を守る機能を持ちます。相手が2台の車を持っているか、車-馬-兵攻撃を使用する場合、士を失うことは危険と考えられます。',
    ],

    'meta' => [
        'site_title' => 'シャンチー オンライン - 2人用',
        'site_description' => 'エキサイティングな機能でオンラインシャンチーをプレイ：2人用モード、オンラインマルチプレイヤー、AIと対戦、パズル、ランク付きトーナメント！',
        'image_alt' => 'シャンチー 2人用',
        'brand_name' => 'シャンチー',
        
        // Page-specific titles
        'room_title' => 'ゲームルーム',
        'about_title' => 'シャンチーについて',
        'contact_title' => '連絡先',
    ],

    'common' => [
        // Buttons & Actions
        'copy' => 'コピー',
        'share' => 'シェア',
        'close' => '閉じる',
        'save' => '保存',
        'cancel' => 'キャンセル',
        'ok' => 'OK',
        'yes' => 'はい',
        'no' => 'いいえ',
        
        // Status messages
        'loading' => '読み込み中...',
        'success' => '成功',
        'error' => 'エラー',
        'warning' => '警告',
        
        // Time & Date
        'today' => '今日',
        'yesterday' => '昨日',
        'online' => 'オンライン',
        'offline' => 'オフライン',
    ],

    'content' => [
        // About page content
        'about_title' => '概要',
        'about_intro' => 'シャンチー（中国語：象棋；ピンイン：xiàngqí）は、中国象棋とも呼ばれ、2人用の戦略ボードゲームです。',
        'about_description' => '中国で最も人気のあるボードゲームの一つで、西洋チェス、チャトランガ、将棋、インドのチェス、ジャンギと同じファミリーに属します。',
        'game_objective' => 'このゲームは2つの軍隊間の戦いを表し、敵の将軍を捕獲することが目的です。',
        'game_features' => '同じファミリーの他のゲームと比較したシャンチーの特徴的な特性は：駒が四角ではなく交点に置かれること、砲が捕獲する際に1つの駒を飛び越えなければならないこと、将軍、士、象の駒を制限する川と宮殿の概念です。',
        
        // Board sections
        'board_title' => 'ボード',
        'board_description' => 'ボードは長方形で、9本の縦線と10本の横線が90の点で直角に交差して形成されます。川と呼ばれる空白スペースがボードの中央に水平に位置し、ボードを2つの等しい対称部分に分割します。',
        'palace_description' => '各側は、各側の後列から縦線4、5、6に4つの四角で形成された四角い宮殿を持ち、これら4つの四角には2つの対角線が描かれています。',
        'board_orientation' => '慣例により、ボードを正面から見たとき、下側は白（または赤）の駒、上側は黒（または青）の駒になります。',
        
        // Rules & Instructions
        'rules_title' => 'ルール',
        'gameplay_description' => 'ゲームは2人の間で行われ、1人が白（または赤）の駒を、1人が黒（または青）の駒を持ちます。各プレイヤーの目標は、ルールに従ってボード上で駒を動かし、相手の将軍をチェックメイトまたは捕獲する方法を見つけることです。',
        'pieces_title' => 'ゲーム駒',
        'how_to_play' => 'プレイ方法',
        'pieces_movement' => '駒の動き',
    ],

    'ads' => [
        'advertisement' => '広告',
        'sponsored' => 'スポンサー',
        'promotion' => 'プロモーション',
        'close_ad' => '広告を閉じる',
    ],
];