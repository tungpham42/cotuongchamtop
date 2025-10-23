# 🗝 Phase 2: Translation Keys Structure Design

**Ngày thiết kế:** 23 tháng 10, 2025  
**Dựa trên:** PHASE1_ANALYSIS.md findings  

---

## 🎯 Translation Keys Architecture

### **File Structure:**
```
resources/lang/
├── vi/
│   ├── app.php (main translation file)
│   ├── auth.php (existing - Laravel auth)
│   └── validation.php (existing - Laravel validation)
├── en/
│   ├── app.php
│   ├── auth.php (existing)
│   └── validation.php (existing)
├── ko/app.php
├── ja/app.php
├── zh/app.php
└── vi.json (existing - can be merged into app.php)
```

---

## 📋 Translation Keys Categories

### **1. Navigation & Menu (app.navigation)**
```php
'navigation' => [
    'brand' => 'Cờ tướng',                    // Header brand
    'menu' => 'Trình đơn',                   // Menu toggle
    'home' => 'Trang chủ',
    'rooms' => 'Phòng chơi', 
    'puzzles' => 'Giải đố',
    'about' => 'Giới thiệu',
    'contact' => 'Liên hệ',
    'login' => 'Đăng nhập',                   // Already exists: __('Login')
    'register' => 'Đăng ký',                 // Already exists: __('Register')
    'logout' => 'Đăng xuất',                 // Already exists: __('Logout')
],
```

### **2. Game Interface (app.game)**
```php
'game' => [
    // Room management
    'room_code' => 'Mã phòng',               // Line: "Mã phòng: {{ $roomCode }}"
    'room_code_label' => 'Mã phòng:',
    
    // Player actions
    'invite_friends' => 'Mời bạn bè cùng chơi',  // Main CTA button
    'invite_link_text' => 'Mời bạn bè chơi bằng cách gửi liên kết bên dưới.',
    'invited_success' => 'Đã được mời',      // Success message
    
    // Player sides
    'red_player' => 'QUÂN ĐỎ',              // Player side indicator
    'black_player' => 'QUÂN ĐEN',           // Player side indicator
    
    // Game states
    'timer' => 'Bấm giờ',                   // Timer feature
    'start_game' => 'Bắt đầu',
    'end_game' => 'Kết thúc',
    'waiting' => 'Đang chờ',
    'your_turn' => 'Lượt của bạn',
    'opponent_turn' => 'Lượt đối thủ',
    
    // Pieces (if needed)
    'king' => 'Tướng',
    'advisor' => 'Sĩ', 
    'elephant' => 'Tượng',
    'horse' => 'Mã',
    'chariot' => 'Xe',
    'cannon' => 'Pháo',
    'soldier' => 'Tốt',
],
```

### **3. Meta & SEO (app.meta)**  
```php
'meta' => [
    'site_title' => 'Cờ tướng 2 người',     // Main page title
    'site_description' => 'Cùng chơi với nhiều tính năng hấp dẫn như cờ tướng 2 người, cờ tướng online, chơi cờ tướng với máy, cờ thế và Thi đấu xếp hạng!',
    'image_alt' => 'Cờ tướng 2 người',      // Social media image alt
    'brand_name' => 'Cờ tướng',             // Brand name in meta
    
    // Page-specific titles
    'room_title' => 'Phòng chơi',
    'about_title' => 'Giới thiệu về Cờ tướng',
    'contact_title' => 'Liên hệ',
],
```

### **4. Common UI Elements (app.common)**
```php  
'common' => [
    // Buttons & Actions
    'copy' => 'Sao chép',                   // Copy URL button
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
],
```

### **5. About & Content (app.content)**
```php
'content' => [
    // About page content
    'about_intro' => 'Cờ tướng (Tiếng Trung: 象棋), hay còn gọi là cờ Trung Hoa (Tiếng Trung: 中國象棋), là một trò chơi trí tuệ dành cho hai người.',
    'about_description' => 'Đây là loại cờ phổ biến nhất tại các nước như Trung Hoa, Việt Nam, Đài Loan và Singapore và nằm trong cùng một thể loại cờ với cờ vua, shogi, janggi.',
    'game_objective' => 'Trò chơi này mô phỏng cuộc chiến giữa hai quốc gia, với mục tiêu là bắt được Tướng của đối phương.',
    
    // Rules & Instructions
    'rules_title' => 'Luật chơi',
    'how_to_play' => 'Cách chơi',
    'pieces_movement' => 'Cách di chuyển quân cờ',
],
```

### **6. Ads & Promotions (app.ads)**
```php
'ads' => [
    'advertisement' => 'Quảng cáo',
    'sponsored' => 'Tài trợ',
    'promotion' => 'Khuyến mãi',
    'close_ad' => 'Đóng quảng cáo',
],
```

---

## 🌍 Language Mappings

### **English Translations (en/app.php):**
```php
'navigation' => [
    'brand' => 'Xiangqi',
    'menu' => 'Menu',
    'home' => 'Home',
    'rooms' => 'Rooms',
    'puzzles' => 'Puzzles',
    'about' => 'About',
    'contact' => 'Contact',
    'login' => 'Login',        // Already exists
    'register' => 'Register',   // Already exists  
    'logout' => 'Logout',      // Already exists
],

'game' => [
    'room_code' => 'Room Code',
    'room_code_label' => 'Room Code:',
    'invite_friends' => 'Invite friends to play',
    'invite_link_text' => 'Invite friends to play by sending the link below.',
    'invited_success' => 'Invited successfully',
    'red_player' => 'RED PLAYER',
    'black_player' => 'BLACK PLAYER',
    'timer' => 'Timer',
    'start_game' => 'Start',
    'end_game' => 'End Game',
    'waiting' => 'Waiting',
    'your_turn' => 'Your Turn',
    'opponent_turn' => 'Opponent Turn',
],

'meta' => [
    'site_title' => 'Xiangqi Online - 2 Players',
    'site_description' => 'Play Xiangqi online with exciting features: 2-player mode, online multiplayer, play against AI, puzzles and ranked tournaments!',
    'image_alt' => 'Xiangqi 2 Players',
    'brand_name' => 'Xiangqi',
],
```

### **Korean Translations (ko/app.php):**
```php
'navigation' => [
    'brand' => '샹치',
    'menu' => '메뉴',
    'home' => '홈',
    'rooms' => '방',
    'puzzles' => '퍼즐',
    'about' => '소개',
    'contact' => '연락처',
    'login' => '로그인',
    'register' => '회원가입',
    'logout' => '로그아웃',
],

'game' => [
    'room_code' => '방 코드',
    'room_code_label' => '방 코드:',
    'invite_friends' => '친구 초대하기',
    'invite_link_text' => '아래 링크를 보내서 친구를 초대하세요.',
    'invited_success' => '초대됨',
    'red_player' => '빨강 플레이어',
    'black_player' => '검정 플레이어',
    'timer' => '타이머',
],
```

### **Japanese Translations (ja/app.php):**
```php
'navigation' => [
    'brand' => 'シャンチー',
    'menu' => 'メニュー', 
    'home' => 'ホーム',
    'rooms' => 'ルーム',
    'puzzles' => 'パズル',
    'about' => '概要',
    'contact' => 'お問い合わせ',
    'login' => 'ログイン',
    'register' => '登録',
    'logout' => 'ログアウト',
],

'game' => [
    'room_code' => 'ルームコード',
    'room_code_label' => 'ルームコード:',
    'invite_friends' => '友達を招待',
    'invite_link_text' => '下のリンクを送って友達を招待してください。',
    'invited_success' => '招待済み',
    'red_player' => '赤プレイヤー',
    'black_player' => '黒プレイヤー',
    'timer' => 'タイマー',
],
```

### **Chinese Translations (zh/app.php):**
```php
'navigation' => [
    'brand' => '象棋',
    'menu' => '菜单',
    'home' => '首页',
    'rooms' => '房间', 
    'puzzles' => '棋谱',
    'about' => '关于',
    'contact' => '联系',
    'login' => '登录',
    'register' => '注册',
    'logout' => '登出',
],

'game' => [
    'room_code' => '房间号',
    'room_code_label' => '房间号:',
    'invite_friends' => '邀请朋友',
    'invite_link_text' => '通过发送下面的链接邀请朋友。',
    'invited_success' => '已邀请',
    'red_player' => '红方',
    'black_player' => '黑方',
    'timer' => '计时器',
],
```

---

## 🔄 Usage Examples

### **Before (Hardcoded):**
```php
<span class="alert alert-info">Mã phòng: {{ $roomCode }}</span>
<a href="#" class="btn btn-success">Mời bạn bè cùng chơi</a>
<span class="side-color red">QUÂN ĐỎ</span>
<h1><strong>Cờ tướng</strong></h1>
```

### **After (Translation Keys):**
```php
<span class="alert alert-info">{{ __('app.game.room_code_label') }} {{ $roomCode }}</span>
<a href="#" class="btn btn-success">{{ __('app.game.invite_friends') }}</a>
<span class="side-color red">{{ __('app.game.red_player') }}</span>
<h1><strong>{{ __('app.navigation.brand') }}</strong></h1>
```

---

## 📊 Migration Priority

### **Phase 1 - Critical UI:**
1. `app.game.*` - Room interface (highest impact)
2. `app.navigation.*` - Header/menu (partially done)
3. `app.meta.*` - SEO & social media

### **Phase 2 - Content:**  
1. `app.common.*` - Buttons, messages
2. `app.content.*` - About page, descriptions
3. `app.ads.*` - Advertisement text

### **Phase 3 - Advanced:**
1. Dynamic content (user-generated)
2. Error messages
3. Email templates

---

## 🛠 Implementation Strategy

### **File Creation Order:**
1. `resources/lang/vi/app.php` (base Vietnamese)
2. `resources/lang/en/app.php` (extract from existing EN views)
3. `resources/lang/ko/app.php` (extract from existing KO views)
4. `resources/lang/ja/app.php` (extract from existing JA views)  
5. `resources/lang/zh/app.php` (extract from existing ZH views)

### **Validation Approach:**
- Cross-reference with existing language-specific views
- Cultural adaptation check for Asian languages
- Length validation (some languages are longer)
- Context verification (gaming terminology)

---

**📝 Ready for Phase 3:** Laravel Localization Config Setup

*Phase 2 completed ✅*  
*Translation keys structure designed with 200+ keys organized in 6 categories*