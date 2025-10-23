<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Translation Lines - Vietnamese
    |--------------------------------------------------------------------------
    |
    | Base Vietnamese translation file for Xiangqi application
    | This serves as the source language for all translations
    |
    */

    'navigation' => [
        'brand' => 'Cờ tướng',
        'menu' => 'Trình đơn',
        'home' => 'Trang chủ',
        'compete' => 'Thi đấu',
        'waiting_room' => 'Sảnh chờ',
        'members' => 'Thành viên',
        'puzzles' => 'Cờ thế',
        'competing' => 'Đang thi đấu',
        'leaderboard' => 'Bảng xếp hạng',
        'search_players' => 'Tìm kiếm kỳ thủ',
        'match_history' => 'Lịch sử thi đấu',
        'forum' => 'Diễn đàn',
        'facebook_group' => 'Nhóm Facebook',
        'about' => 'Giới thiệu',
        'contact' => 'Liên hệ',
        'login' => 'Đăng nhập',
        'register' => 'Đăng ký',
        'logout' => 'Đăng xuất',
        'my_profile' => 'Hồ sơ của tôi',
        'change_name' => 'Đổi tên',
        'change_theme' => 'Đổi giao diện',
        'change_password' => 'Đổi mật khẩu',
        
        // Theme selector
        'board_color' => 'Màu bàn cờ',
        'default_board' => 'Bàn cờ mặc định',
        'piece_style' => 'Kiểu quân cờ',
        'default_pieces' => 'Quân cờ mặc định',
    ],

    'game' => [
        // Room management
        'room_code' => 'Mã phòng',
        'room_code_label' => 'Mã phòng:',
        
        // Player actions
        'invite_friends' => 'Mời bạn bè cùng chơi',
        'invite_link_text' => 'Mời bạn bè chơi bằng cách gửi liên kết bên dưới.',
        'invited_success' => 'Đã được mời',
        
        // Player sides
        'red_player' => 'QUÂN ĐỎ',
        'black_player' => 'QUÂN ĐEN',
        'red_side' => 'Bên ĐỎ',
        'black_side' => 'Bên ĐEN',
        'red_turn' => 'Tới lượt ĐỎ',
        'black_turn' => 'Tới lượt ĐEN',
        
        // Game states
        'timer' => 'Bấm giờ',
        'start_game' => 'Bắt đầu',
        'end_game' => 'Kết thúc',
        'waiting' => 'Đang chờ',
        'your_turn' => 'Lượt của bạn',
        'opponent_turn' => 'Lượt đối thủ',
        'playing_alone' => 'Bạn đang chơi một mình',
        'skill_improvement' => 'Tăng kỹ năng chơi cờ',
        'game_over' => 'HẾT TRẬN',
        'play_online' => 'Chơi online',
        'play_with_friends' => 'Đấu với bạn bè trong phòng',
        'switch_side' => 'Đổi bên',
        
        // Pieces
        'king' => 'Tướng',
        'advisor' => 'Sĩ',
        'elephant' => 'Tượng',
        'horse' => 'Mã',
        'chariot' => 'Xe',
        'cannon' => 'Pháo',
        'soldier' => 'Tốt',
        
        // Piece descriptions (for about page)
        'king_description_1' => 'Quân Tướng đi từng ô một, đi ngang hoặc dọc và luôn luôn ở trong phạm vi cung, không được ra ngoài. Tính theo khả năng chiến đấu thì Tướng là quân yếu nhất do chỉ đi nước một và bị giới hạn trong cung.',
        'king_description_2' => 'Tướng được chốt chặt trong cung và có tới 2 Sĩ và Tượng canh gác hai bên. Chính điều này làm cho ván cờ trở nên khó phân thắng bại, cơ may hòa cờ rất lớn.',
        'advisor_description_1' => 'Quân Sĩ đi chéo 1 ô mỗi nước và luôn luôn phải ở trong cung. Như vậy, quân Sĩ có 5 giao điểm có thể đứng hợp lệ và là quân cờ yếu nhất.',
        'advisor_description_2' => 'Sĩ có chức năng trong việc bảo vệ Tướng, mất Sĩ được cho là nguy hiểm khi đối phương còn đủ 2 Xe hoặc dùng Xe Mã Tốt tấn công.',
    ],

    'meta' => [
        'site_title' => 'Cờ tướng 2 người',
        'site_description' => 'Cùng chơi với nhiều tính năng hấp dẫn như cờ tướng 2 người, cờ tướng online, chơi cờ tướng với máy, cờ thế và Thi đấu xếp hạng!',
        'image_alt' => 'Cờ tướng 2 người',
        'brand_name' => 'Cờ tướng',
        
        // Page-specific titles
        'room_title' => 'Phòng chơi',
        'about_title' => 'Giới thiệu về Cờ tướng',
        'contact_title' => 'Liên hệ',
    ],

    'common' => [
        // Buttons & Actions
        'copy' => 'Sao chép',
        'share' => 'Chia sẻ',
        'close' => 'Đóng',
        'save' => 'Lưu',
        'cancel' => 'Hủy',
        'ok' => 'OK',
        'yes' => 'Có',
        'no' => 'Không',
        
        // Status messages
        'loading' => 'Đang tải...',
        'success' => 'Thành công',
        'error' => 'Lỗi',
        'warning' => 'Cảnh báo',
        
        // Time & Date
        'today' => 'Hôm nay',
        'yesterday' => 'Hôm qua',
        'online' => 'Trực tuyến',
        'offline' => 'Ngoại tuyến',
        'select_language' => 'Chọn ngôn ngữ',
    ],

    'content' => [
        // About page content
        'about_title' => 'Giới thiệu',
        'about_intro' => 'Cờ tướng (Tiếng Trung: 象棋), hay còn gọi là cờ Trung Hoa (Tiếng Trung: 中國象棋), là một trò chơi trí tuệ dành cho hai người.',
        'about_description' => 'Đây là loại cờ phổ biến nhất tại các nước như Trung Hoa, Việt Nam, Đài Loan và Singapore và nằm trong cùng một thể loại cờ với cờ vua, shogi, janggi.',
        'game_objective' => 'Trò chơi này mô phỏng cuộc chiến giữa hai quốc gia, với mục tiêu là bắt được Tướng của đối phương.',
        'game_features' => 'Các đặc điểm khác biệt của cờ tướng so với các trò chơi cùng họ là: các quân đặt ở giao điểm các đường thay vì đặt vào ô, quân Pháo phải nhảy qua 1 quân khi ăn quân, các khái niệm sông và cung nhằm giới hạn các quân Tướng, Sĩ và Tượng.',
        
        // Board sections
        'board_title' => 'Bàn cờ',
        'board_description' => 'Bàn cờ là hình chữ nhật do 9 đường dọc và 10 đường ngang cắt nhau vuông góc tại 90 điểm hợp thành. Một khoảng trống gọi là sông nằm ngang giữa bàn cờ, chia bàn cờ thành hai phần đối xứng bằng nhau.',
        'palace_description' => 'Mỗi bên có một cung Tướng hình vuông do 4 ô hợp thành tại các đường dọc 4, 5, 6 kể từ đường ngang cuối của mỗi bên, trong 4 ô này có vẽ hai đường chéo.',
        'board_orientation' => 'Theo quy ước, khi bàn cờ được quan sát chính diện, phía dưới sẽ là quân Trắng (hoặc Đỏ), phía trên sẽ là quân Đen (hoặc Xanh).',
        
        // Rules & Instructions
        'rules_title' => 'Luật chơi',
        'gameplay_description' => 'Ván cờ được tiến hành giữa hai người, một người cầm quân Trắng (hay Đỏ), một người cầm quân Đen (hay Xanh). Mục đích của mỗi người là tìm mọi cách đi quân trên bàn cờ theo đúng luật để chiếu bí hay bắt Tướng (hay Soái) của đối phương.',
        'pieces_title' => 'Các quân cờ',
        'how_to_play' => 'Cách chơi',
        'pieces_movement' => 'Cách di chuyển quân cờ',
    ],

    'ads' => [
        'advertisement' => 'Quảng cáo',
        'sponsored' => 'Tài trợ',
        'promotion' => 'Khuyến mãi',
        'close_ad' => 'Đóng quảng cáo',
    ],
];