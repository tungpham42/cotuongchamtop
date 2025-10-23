<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Translation Lines - Chinese
    |--------------------------------------------------------------------------
    |
    | Chinese translation file for Xiangqi application
    | Translated from Vietnamese base
    |
    */

    'navigation' => [
        'brand' => '中国象棋',
        'menu' => '菜单',
        'home' => '首页',
        'compete' => '比赛',
        'waiting_room' => '等候室',
        'members' => '成员',
        'puzzles' => '棋谱',
        'competing' => '比赛中',
        'leaderboard' => '排行榜',
        'search_players' => '搜索棋手',
        'match_history' => '比赛历史',
        'forum' => '论坛',
        'facebook_group' => 'Facebook群组',
        'about' => '关于',
        'contact' => '联系',
        'login' => '登录',
        'register' => '注册',
        'logout' => '退出',
        'my_profile' => '我的档案',
        'change_name' => '更改姓名',
        'change_theme' => '更改主题',
        'change_password' => '更改密码',
        
        // Theme selector
        'board_color' => '棋盘颜色',
        'default_board' => '默认棋盘',
        'piece_style' => '棋子样式',
        'default_pieces' => '默认棋子',
    ],

    'game' => [
        // Room management
        'room_code' => '房间代码',
        'room_code_label' => '房间代码：',
        
        // Player actions
        'invite_friends' => '邀请朋友一起下棋',
        'invite_link_text' => '通过发送下面的链接邀请朋友下棋。',
        'invited_success' => '成功邀请',
        
        // Player sides
        'red_player' => '红方',
        'black_player' => '黑方',
        'red_side' => '红方',
        'black_side' => '黑方',
        'red_turn' => '红方回合',
        'black_turn' => '黑方回合',
        
        // Game states
        'timer' => '计时器',
        'start_game' => '开始',
        'end_game' => '结束游戏',
        'waiting' => '等待中',
        'your_turn' => '你的回合',
        'opponent_turn' => '对手回合',
        
        // Pieces
        'king' => '将',
        'advisor' => '士',
        'elephant' => '象',
        'horse' => '马',
        'chariot' => '车',
        'cannon' => '炮',
        'soldier' => '兵',
        
        // Piece descriptions (for about page)
        'king_description_1' => '将一次移动一个点，水平或垂直移动，并且必须始终留在宫内。就战斗能力而言，将是最弱的棋子，因为它只能移动一个点并被限制在宫内。',
        'king_description_2' => '将被固定在宫内，两侧有多达2个士和象守护。这使得游戏难以确定胜负，平局的可能性很高。',
        'advisor_description_1' => '士每次移动都沿对角线移动一个点，必须始终留在宫内。士有5个有效的交叉点，是最弱的棋子。',
        'advisor_description_2' => '士的功能是保护将。当对手有2辆车或使用车-马-兵攻击时，失去士被认为是危险的。',
    ],

    'meta' => [
        'site_title' => '象棋在线 - 双人',
        'site_description' => '享受令人兴奋的功能在线象棋：双人模式、在线多人游戏、与AI对战、棋谱和排名锦标赛！',
        'image_alt' => '象棋双人',
        'brand_name' => '象棋',
        
        // Page-specific titles
        'room_title' => '游戏房间',
        'about_title' => '关于象棋',
        'contact_title' => '联系',
    ],

    'common' => [
        // Buttons & Actions
        'copy' => '复制',
        'share' => '分享',
        'close' => '关闭',
        'save' => '保存',
        'cancel' => '取消',
        'ok' => '确定',
        'yes' => '是',
        'no' => '否',
        
        // Status messages
        'loading' => '加载中...',
        'success' => '成功',
        'error' => '错误',
        'warning' => '警告',
        
        // Time & Date
        'today' => '今天',
        'yesterday' => '昨天',
        'online' => '在线',
        'offline' => '离线',
    ],

    'content' => [
        // About page content
        'about_title' => '关于',
        'about_intro' => '象棋（中文：象棋；拼音：xiàngqí），也称为中国象棋，是一种两人策略棋类游戏。',
        'about_description' => '它是中国最受欢迎的棋类游戏之一，与西方象棋、恰图兰卡、将棋、印度象棋和韩国将棋属于同一家族。',
        'game_objective' => '该游戏代表两军之间的战斗，目标是捕获敌方的将。',
        'game_features' => '与同家族其他游戏相比，象棋的独特特性是：棋子放在交叉点而不是方格上，炮在吃子时必须跳过一个棋子，以及限制将、士、象棋子的河和宫的概念。',
        
        // Board sections
        'board_title' => '棋盘',
        'board_description' => '棋盘是矩形的，由9条竖线和10条横线在90个点上直角相交形成。一个称为河的空白区域水平位于棋盘中央，将棋盘分为两个相等的对称部分。',
        'palace_description' => '每一方都有一个由4个方格组成的方形宫，位于每方后排的竖线4、5、6处，这4个方格中画有两条对角线。',
        'board_orientation' => '按照惯例，从正面观看棋盘时，下方将是白色（或红色）棋子，上方将是黑色（或蓝色）棋子。',
        
        // Rules & Instructions
        'rules_title' => '规则',
        'gameplay_description' => '游戏在两人之间进行，一人持白色（或红色）棋子，一人持黑色（或蓝色）棋子。每个玩家的目标是找到根据规则在棋盘上移动棋子的方法，以将死或捕获对手的将。',
        'pieces_title' => '游戏棋子',
        'how_to_play' => '如何下棋',
        'pieces_movement' => '棋子移动',
    ],

    'ads' => [
        'advertisement' => '广告',
        'sponsored' => '赞助',
        'promotion' => '促销',
        'close_ad' => '关闭广告',
    ],
];