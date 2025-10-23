<?php

return [
    'navigation' => [
        'brand' => 'Chinese Chess',
        'menu' => 'Menu',
        'home' => 'Home',
        'compete' => 'Compete',
        'waiting_room' => 'Waiting Room',
        'members' => 'Members',
        'puzzles' => 'Puzzles',
        'competing' => 'Competing',
        'leaderboard' => 'Leaderboard',
        'search_players' => 'Search Players',
        'match_history' => 'Match History',
        'forum' => 'Forum',
        'facebook_group' => 'Facebook Group',
        'about' => 'About',
        'contact' => 'Contact',
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
        'my_profile' => 'My Profile',
        'change_name' => 'Change Name',
        'change_theme' => 'Change Theme',
        'change_password' => 'Change Password',
        
        // Theme selector
        'board_color' => 'Board Color',
        'default_board' => 'Default Board',
        'piece_style' => 'Piece Style',
        'default_pieces' => 'Default Pieces',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Translation Lines - English
    |--------------------------------------------------------------------------
    |
    | English translation file for Xiangqi application
    | Translated from Vietnamese base
    |
    */

    'game' => [
        // Room management
        'room_code' => 'Room Code',
        'room_code_label' => 'Room Code:',
        
        // Player actions
        'invite_friends' => 'Invite friends to play',
        'invite_link_text' => 'Invite friends to play by sending the link below.',
        'invited_success' => 'Invited successfully',
        
        // Player sides
        'red_player' => 'RED PLAYER',
        'black_player' => 'BLACK PLAYER',
        'red_side' => 'Red Side',
        'black_side' => 'Black Side',
        'red_turn' => 'Red\'s Turn',
        'black_turn' => 'Black\'s Turn',
        
        // Game states
        'timer' => 'Timer',
        'start_game' => 'Start',
        'end_game' => 'End Game',
        'waiting' => 'Waiting',
        'your_turn' => 'Your Turn',
        'opponent_turn' => 'Opponent Turn',
        'playing_alone' => 'You are playing alone',
        'skill_improvement' => 'Improve your chess skills',
        'game_over' => 'GAME OVER',
        'play_online' => 'Play Online',
        'play_with_friends' => 'Play with friends in room',
        'switch_side' => 'Switch Side',
        
        // Pieces
        'king' => 'General',
        'advisor' => 'Advisor',
        'elephant' => 'Elephant',
        'horse' => 'Horse',
        'chariot' => 'Chariot',
        'cannon' => 'Cannon',
        'soldier' => 'Soldier',
        
        // Piece descriptions (for about page)
        'king_description_1' => 'The General moves one point at a time, horizontally or vertically, and must always stay within the palace. In terms of combat ability, the General is the weakest piece since it can only move one point and is confined to the palace.',
        'king_description_2' => 'The General is secured in the palace with up to 2 Advisors and Elephants guarding on both sides. This makes the game difficult to determine winner, with high chances of draws.',
        'advisor_description_1' => 'The Advisor moves diagonally one point each move and must always stay within the palace. The Advisor has 5 valid intersection points and is the weakest piece.',
        'advisor_description_2' => 'The Advisor functions to protect the General. Losing an Advisor is considered dangerous when the opponent has 2 Chariots or uses Chariot-Horse-Soldier attacks.',
    ],

    'meta' => [
        'site_title' => 'Xiangqi Online - 2 Players',
        'site_description' => 'Play Xiangqi online with exciting features: 2-player mode, online multiplayer, play against AI, puzzles and ranked tournaments!',
        'image_alt' => 'Xiangqi 2 Players',
        'brand_name' => 'Xiangqi',
        
        // Page-specific titles
        'room_title' => 'Game Room',
        'about_title' => 'About Xiangqi',
        'contact_title' => 'Contact',
    ],

    'common' => [
        // Buttons & Actions
        'copy' => 'Copy',
        'share' => 'Share',
        'close' => 'Close',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'ok' => 'OK',
        'yes' => 'Yes',
        'no' => 'No',
        
        // Status messages
        'loading' => 'Loading...',
        'success' => 'Success',
        'error' => 'Error',
        'warning' => 'Warning',
        
        // Time & Date
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'online' => 'Online',
        'offline' => 'Offline',
        'select_language' => 'Select Language',
    ],

    'content' => [
        // About page content
        'about_title' => 'About',
        'about_intro' => 'Xiangqi (Chinese: 象棋; pinyin: xiàngqí), also called Chinese chess, is a strategy board game for two players.',
        'about_description' => 'It is one of the most popular board games in China, and is in the same family as Western chess, chaturanga, shogi, Indian chess and janggi.',
        'game_objective' => 'The game represents a battle between two armies, with the object of capturing the enemy\'s general.',
        'game_features' => 'The distinctive features of Xiangqi compared to other games in the same family are: pieces are placed on intersections rather than squares, the Cannon must jump over one piece when capturing, and the concepts of river and palace that limit the General, Advisor and Elephant pieces.',
        
        // Board sections
        'board_title' => 'Board',
        'board_description' => 'The board is rectangular, formed by 9 vertical lines and 10 horizontal lines intersecting at right angles at 90 points. A blank space called the river lies horizontally in the middle of the board, dividing it into two equal symmetrical parts.',
        'palace_description' => 'Each side has a square palace formed by 4 squares at vertical lines 4, 5, 6 from the back row of each side, with two diagonal lines drawn in these 4 squares.',
        'board_orientation' => 'By convention, when viewing the board from the front, the bottom will be White (or Red) pieces, the top will be Black (or Blue) pieces.',
        
        // Rules & Instructions
        'rules_title' => 'Rules',
        'gameplay_description' => 'The game is played between two people, one holding White (or Red) pieces, one holding Black (or Blue) pieces. Each player\'s goal is to find ways to move pieces on the board according to the rules to checkmate or capture the opponent\'s General.',
        'pieces_title' => 'Game Pieces',
        'how_to_play' => 'How to Play',
        'pieces_movement' => 'Piece Movement',
    ],

    'ads' => [
        'advertisement' => 'Advertisement',
        'sponsored' => 'Sponsored',
        'promotion' => 'Promotion',
        'close_ad' => 'Close Ad',
    ],
];