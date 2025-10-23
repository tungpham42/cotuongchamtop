<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Translation Lines - Korean
    |--------------------------------------------------------------------------
    |
    | Korean translation file for Xiangqi application
    | Translated from Vietnamese base
    |
    */

    'navigation' => [
        'brand' => '중국 장기',
        'menu' => '메뉴',
        'home' => '홈',
        'compete' => '경쟁',
        'waiting_room' => '대기실',
        'members' => '회원',
        'puzzles' => '퍼즐',
        'competing' => '경쟁 중',
        'leaderboard' => '리더보드',
        'search_players' => '선수 검색',
        'match_history' => '경기 기록',
        'forum' => '포럼',
        'facebook_group' => '페이스북 그룹',
        'about' => '소개',
        'contact' => '연락처',
        'login' => '로그인',
        'register' => '회원가입',
        'logout' => '로그아웃',
        'my_profile' => '내 프로필',
        'change_name' => '이름 변경',
        'change_theme' => '테마 변경',
        'change_password' => '비밀번호 변경',
        
        // Theme selector
        'board_color' => '보드 색상',
        'default_board' => '기본 보드',
        'piece_style' => '말 스타일',
        'default_pieces' => '기본 말',
    ],

    'game' => [
        // Room management
        'room_code' => '방 코드',
        'room_code_label' => '방 코드:',
        
        // Player actions
        'invite_friends' => '친구들과 함께 플레이하도록 초대',
        'invite_link_text' => '아래 링크를 보내서 친구들을 플레이에 초대하세요.',
        'invited_success' => '성공적으로 초대됨',
        
        // Player sides
        'red_player' => '빨간 플레이어',
        'black_player' => '검은 플레이어',
        'red_side' => '빨간 편',
        'black_side' => '검은 편',
        'red_turn' => '빨간편 차례',
        'black_turn' => '검은편 차례',
        
        // Game states
        'timer' => '타이머',
        'start_game' => '시작',
        'end_game' => '게임 종료',
        'waiting' => '대기 중',
        'your_turn' => '당신의 차례',
        'opponent_turn' => '상대편 차례',
        
        // Pieces
        'king' => '장군',
        'advisor' => '사',
        'elephant' => '상',
        'horse' => '마',
        'chariot' => '차',
        'cannon' => '포',
        'soldier' => '졸',
        
        // Piece descriptions (for about page)
        'king_description_1' => '장군은 한 번에 한 점씩 가로 또는 세로로 움직이며, 항상 궁궐 안에 머물러야 합니다. 전투 능력으로는 장군이 가장 약한 말로, 한 점만 움직일 수 있고 궁궐에 국한되어 있습니다.',
        'king_description_2' => '장군은 궁궐에 고정되어 있고 양쪽에 최대 2명의 사와 상이 지키고 있습니다. 이것이 게임을 승부하기 어렵게 만들고, 무승부 가능성을 높입니다.',
        'advisor_description_1' => '사는 각 움직임마다 대각선으로 한 점씩 움직이며 항상 궁궐 안에 머물러야 합니다. 사는 5개의 유효한 교차점을 가지고 있으며 가장 약한 말입니다.',
        'advisor_description_2' => '사는 장군을 보호하는 기능을 합니다. 상대방이 2대의 차를 가지고 있거나 차-마-졸 공격을 사용할 때 사를 잃는 것은 위험하다고 여겨집니다.',
    ],

    'meta' => [
        'site_title' => '장기 온라인 - 2인용',
        'site_description' => '흥미진진한 기능으로 온라인 장기를 플레이하세요: 2인용 모드, 온라인 멀티플레이어, AI와 플레이, 퍼즐 및 랭크 토너먼트!',
        'image_alt' => '장기 2인용',
        'brand_name' => '장기',
        
        // Page-specific titles
        'room_title' => '게임룸',
        'about_title' => '장기 소개',
        'contact_title' => '연락처',
    ],

    'common' => [
        // Buttons & Actions
        'copy' => '복사',
        'share' => '공유',
        'close' => '닫기',
        'save' => '저장',
        'cancel' => '취소',
        'ok' => '확인',
        'yes' => '예',
        'no' => '아니오',
        
        // Status messages
        'loading' => '로딩 중...',
        'success' => '성공',
        'error' => '오류',
        'warning' => '경고',
        
        // Time & Date
        'today' => '오늘',
        'yesterday' => '어제',
        'online' => '온라인',
        'offline' => '오프라인',
    ],

    'content' => [
        // About page content
        'about_title' => '소개',
        'about_intro' => '장기(중국어: 象棋; 병음: xiàngqí)는 중국 장기라고도 불리며, 두 명이 하는 전략 보드 게임입니다.',
        'about_description' => '중국에서 가장 인기 있는 보드 게임 중 하나이며, 서양 체스, 차투랑가, 쇼기, 인도 체스 및 장기와 같은 계열에 속합니다.',
        'game_objective' => '이 게임은 두 군대 간의 전투를 나타내며, 적의 장군을 잡는 것이 목표입니다.',
        'game_features' => '같은 계열의 다른 게임과 비교한 장기의 특징적인 특성은: 말이 사각형이 아닌 교차점에 놓이고, 포는 잡을 때 한 말을 뛰어넘어야 하며, 장군, 사, 상 말을 제한하는 강과 궁궐의 개념입니다.',
        
        // Board sections
        'board_title' => '보드',
        'board_description' => '보드는 직사각형으로, 9개의 수직선과 10개의 수평선이 90개 지점에서 직각으로 교차하여 형성됩니다. 강이라고 불리는 빈 공간이 보드 중앙에 수평으로 놓여 보드를 두 개의 동일한 대칭 부분으로 나눕니다.',
        'palace_description' => '각 편은 각 편의 뒷줄에서 수직선 4, 5, 6에 4개 사각형으로 형성된 정사각형 궁궐을 가지고 있으며, 이 4개 사각형에는 두 개의 대각선이 그어져 있습니다.',
        'board_orientation' => '관례적으로 보드를 정면에서 볼 때, 아래쪽은 흰색(또는 빨간색) 말, 위쪽은 검은색(또는 파란색) 말이 됩니다.',
        
        // Rules & Instructions
        'rules_title' => '규칙',
        'gameplay_description' => '게임은 두 사람 사이에서 진행되며, 한 사람은 흰색(또는 빨간색) 말을, 한 사람은 검은색(또는 파란색) 말을 잡습니다. 각 플레이어의 목표는 규칙에 따라 보드에서 말을 움직여 상대의 장군을 체크메이트하거나 잡는 방법을 찾는 것입니다.',
        'pieces_title' => '게임 말',
        'how_to_play' => '플레이 방법',
        'pieces_movement' => '말 움직임',
    ],

    'ads' => [
        'advertisement' => '광고',
        'sponsored' => '후원',
        'promotion' => '프로모션',
        'close_ad' => '광고 닫기',
    ],
];