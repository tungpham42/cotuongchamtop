# TỔNG QUAN TÍNH NĂNG HỆ THỐNG CỜ TƯỚNG (COTUONGCHAMTOP)

> **Cập nhật mới nhất:** 17/10/2026  
> **Nền tảng:** Laravel 13 (PHP 8.3), Laravel Octane / Reverb / Pusher, Vite & Laravel Mix, Pikafish NNUE Engine Pool, Groq LLM AI.

---

## 1. THI ĐẤU TRỰC TUYẾN (ONLINE MULTIPLAYER)
- **Tạo phòng thi đấu linh hoạt:**
  - Tùy chỉnh tên phòng, tạo mã phòng duy nhất (`code`).
  - Đặt mật khẩu phòng riêng tư (`pass`) để giao lưu bạn bè hoặc phòng mở tự do.
- **Ghép trận thông minh (Matchmaking):**
  - Cơ chế **Ghép nhanh (Quick Match / Find Match)** hỗ trợ cả khách vãng lai (Guest) lẫn thành viên đăng nhập (Auth).
  - API kiểm tra và xếp hàng chờ ghép trận tự động (`/match/find`, `/match/status`).
- **Nhiều vai trò tham gia phòng:**
  - **Chủ phòng (`room.host`)** / **Khách (`room.guest`)**.
  - Chọn phe chủ động: **Bên Đỏ (`room.red`)**, **Bên Đen (`room.black`)**, hoặc **Ngẫu nhiên (`room.random`)**.
  - **Chế độ Khán giả (`room.watch`):** Cho phép người ngoài theo dõi trận đấu trực tiếp mà không can thiệp vào ván cờ.
- **Đồng bộ thời gian thực & Chống gian lận (Real-time & Anti-cheat):**
  - Đồng bộ trạng thái bàn cờ qua **Server-Sent Events (SSE)** (`/getFEN/{code}`) và **WebSocket** (Laravel Reverb / Pusher) qua sự kiện `RoomUpdated`.
  - Lưu trữ biên bản ván đấu (`moves`) và chuỗi trạng thái bàn cờ FEN chuẩn quốc tế.
  - **Đồng hồ thi đấu kép (Chess Clock):**
    - Kiểm soát thời gian độc lập cho Đỏ (`red_time`) và Đen (`black_time`).
    - Giới hạn thời gian suy nghĩ tối đa cho mỗi nước đi (120 giây/nước).
    - Cơ chế chuyển lượt nguyên tử (`switchTurn`), tự động tính toán thời gian trôi qua và xử thua khi hết giờ (`processTimeout`).
  - **Cơ chế chống can thiệp (Anti-cheat):**
    - Chặn đổi phe hoặc vào sai phe bằng cách thao tác URL.
    - Tự động chuyển hướng sang chế độ xem (`room.watch`) khi truy cập các phòng đã kết thúc.
- **Tương tác trong trận đấu:**
  - Gửi tín hiệu nhanh (`/room/{code}/signal`): Cầu hòa, xin hoãn, đầu hàng (Resign), xin đi lại (Undo).
  - Khung chat thời gian thực trong phòng giữa người chơi và khán giả, tự động sao lưu nhật ký chat (`public/*ChatLog`).

---

## 2. CHƠI VỚI MÁY & ĐỘNG CƠ CỜ TƯỚNG (AI & ENGINE)
- **Động cơ Pikafish NNUE chuyên nghiệp:**
  - Tích hợp binary `pikafish_vps` kết hợp mạng nơ-ron đánh giá vị trí `pikafish.nnue`.
  - **Kiến trúc Engine Worker Pool:** Duy trì các tiến trình Pikafish luôn chạy ngầm (warm worker), loại bỏ hoàn toàn độ trễ khởi động ~2s+ trên mỗi lượt đi.
  - Lệnh Artisan quản trị pool: `php artisan xiangqi:pool:ensure`.
  - **Bảng điều khiển ngầm (Secret Ops Dashboard):** Truy cập an toàn qua URL bảo mật `/_ops/{secret}/xiangqi-pool` để giám sát trạng thái worker trực tiếp và điều khiển khởi động lại pool.
- **7 cấp độ thách đấu AI đa dạng:**
  1. *Dễ nhất* (Easiest - Level 1)
  2. *Mới chơi* (Newbie - Level 1)
  3. *Dễ* (Easy - Level 2)
  4. *Bình thường* (Normal - Level 3)
  5. *Khó* (Hard - Level 4)
  6. *Khó nhất* (Hardest - Level 5)
  7. *Kiện tướng* (Master - Level 8)
- **Tính năng AI hỗ trợ người chơi:**
  - Tìm nước đi tối ưu (`/api/xiangqi/best-move`).
  - Gợi ý nước đi khi bế tắc (`/api/xiangqi/hint`).
  - Đánh giá điểm lợi thế vị trí bàn cờ (`/api/xiangqi/analyze`).
- **Huấn luyện viên AI thông minh (Groq LLM AI Coach):**
  - Tích hợp các mô hình ngôn ngữ lớn tốc độ cao qua Groq API (`llama-3.3-70b-versatile`, `gpt-oss-120b`,...).
  - Tự động chuyển đổi chuỗi FEN sang bản đồ trực quan ASCII (Visual Board) để AI hiểu bố cục quân cờ.
  - Đóng vai trò **Huấn luyện viên Cờ Tướng Việt Nam**: Đưa ra nhận xét hóm hỉnh, phân tích chiến thuật ngắn gọn (< 100 từ).
  - Tuân thủ 100% quy tắc ký hiệu cờ tướng Việt Nam: `[Tên Quân] [Cột Đứng 1-9] [Tấn/Thoái/Bình] [Tham số]`.
  - API phân tích và đàm thoại chiến thuật: `/api/chess/analyze`, `/api/chess/chat`.
- **Chơi một mình (Solo Play):**
  - Chế độ tự luyện tập đi cả 2 bên trên một bàn cờ (`human.play`).

---

## 3. CỜ THẾ & BÀN CỜ NGHIÊN CỨU (PUZZLES & BOARD EDITOR)
- **Xếp cờ thế (Puzzle Setup):**
  - Giao diện trực quan đặt quân cờ lên bàn cờ trống để tạo các thế cờ tàn cuộc, sát cục.
- **Đăng tải và Quản lý thế cờ:**
  - Tạo mới thế cờ với tên gọi, mô tả chi tiết, kiểm tra trùng tên theo thời gian thực (`checkUniqueName`).
  - Tùy chọn trạng thái công khai (`is_public`) hoặc lưu nội bộ.
- **Giải cờ thế (Puzzle Solving):**
  - Trải nghiệm giải hàng ngàn thế cờ do cộng đồng đóng góp.
  - Chế độ **Nhờ AI giải thế cờ (`puzzle.ai.solve`)**: Động cơ máy tính phân tích và đi tiếp thế cờ hóc búa.
  - Bàn cờ tự giải (`board.self`): Bàn cờ mở tự do di chuyển thử nghiệm các biến thể.
- **Tương tác cộng đồng trên thế cờ:**
  - Hệ thống biểu cảm đa chiều (Reactions): Like (Thích), Hard (Thế cờ khó), Unsolved (Chưa giải được).
  - Đánh giá sao & Upvote / Downvote thế cờ.
  - Bình luận thảo luận đa cấp (hỗ trợ Reply lồng nhau) và tính năng thích bình luận.

---

## 4. HỆ THỐNG GIẢI ĐẤU (TOURNAMENT SYSTEM)
- **Tổ chức giải đấu quy mô:**
  - Tạo giải đấu với tiêu đề, đường dẫn thân thiện (slug), ảnh bìa (cover photo), lịch trình bắt đầu/kết thúc.
  - Phân quyền Trưởng ban tổ chức giải (`tournament.creator`) để chỉnh sửa, cập nhật hoặc hủy giải.
- **Đăng ký thi đấu:** Người dùng đăng nhập có thể ghi danh tham gia giải với một thao tác.
- **Tự động sinh bảng đấu loại trực tiếp (Single Elimination Bracket Generator):**
  - Tự động xếp cặp và vẽ sơ đồ nhánh đấu (Knockout bracket).
  - Tự động liên kết các phòng thi đấu theo từng vòng (`tournament_round`) và luân chuyển đấu thủ chiến thắng vào vòng trong (`next_room_code`).
  - Quản lý danh sách đấu thủ trong từng giải từ Admin Panel.

---

## 5. KỲ PHỔ & THƯ VIỆN VÁN CỜ (GAMES LIBRARY)
- **Thư viện ván cờ chia sẻ:**
  - Danh mục lưu trữ các ván cờ kinh điển, các ván đấu đỉnh cao của danh thủ (`games.library`).
  - Xem chi tiết từng nước đi, tua lại từng bước cờ (`games.show`).
- **Quản lý ván đấu:** CRUD ván cờ từ trang quản trị, cập nhật điểm đánh giá ván cờ.

---

## 6. THÁCH ĐẤU TRỰC TIẾP (CHALLENGE SYSTEM)
- **Thách đấu qua link bàn cờ:** Tạo link thách đấu trực tiếp theo bất kỳ hình cờ nào: `/thach-dau/{board}`.
- **Mời thách đấu qua Email:** Gửi thư điện tử mời đối thủ vào bàn quyết chiến (`competeMail`).

---

## 7. HỆ THỐNG THÀNH VIÊN, ELO & GAMIFICATION (GEMS & TIERS)
- **Hệ thống tính điểm Elo chuẩn:**
  - Thuật toán Elo tự động tính toán sau mỗi trận thắng, hòa, thua (`EloRatingService`).
  - Bảng xếp hạng kỳ thủ trực tuyến (`app.ranking`, `rankTableHtml`) cập nhật theo điểm số và hiệu số đối đầu.
- **Hồ sơ kỳ thủ chi tiết (`app.player`, `app.profile`):**
  - Thống kê chi tiết: Điểm Elo, số trận thắng/hòa/thua, lịch sử các trận đấu đã chơi (`app.history`).
  - Trạng thái trực tuyến thời gian thực (chấm xanh hoạt động trong vòng 5 phút qua `last_seen_at`).
  - Đổi tên hiển thị, đổi mật khẩu, đổi giao diện bàn cờ (`app.ui`).
  - Tải lên ảnh đại diện tùy chỉnh hoặc tự động sinh Avatar chữ cái nghệ thuật (`laravolt/avatar`).
- **Gamification - Điểm Karma & Danh hiệu Ngọc (Gems Tier):**
  - Tích lũy ngọc qua các hoạt động (`KarmaLog`): Đăng nhập mỗi ngày, hoàn thành trận đấu, giành chiến thắng.
  - **6 cấp bậc huân chương & Khung viền Avatar động (Avatar Frame):**
    1. **Đồng (Bronze):** Huy hiệu Khiên, viền đồng metallic.
    2. **Bạc (Silver):** Huy hiệu Ngôi sao, viền thép sáng bóng.
    3. **Vàng (Gold):** Huy hiệu Huy chương, viền vàng kim loại lấp lánh (shimmer).
    4. **Bạch Kim (Platinum):** Huy hiệu Cúp, viền băng thanh ngọc phát sáng.
    5. **Kim Cương (Diamond):** Huy hiệu Kim cương, viền tím hào quang kép đa tầng.
    6. **Huyền Thoại (Legendary):** Huy hiệu Vương miện, vòng lửa rực cháy xoay tròn chuyển động 360 độ (`conic-gradient` spin).

---

## 8. GÓI HỘI VIÊN VIP & CỔNG THANH TOÁN (PAYOS / VIETQR)
- **Gói hội viên Standard (VIP):**
  - Nâng cấp tài khoản loại bỏ toàn bộ quảng cáo trên trang web (`ads_removed`).
  - Quản lý kỳ hạn thuê bao (`subscription_started_at`, `subscription_ends_at`).
- **Cổng thanh toán tự động PayOS:**
  - Tích hợp chuẩn thanh toán **VietQR** ngân hàng Việt Nam.
  - Tự động đối soát đơn hàng qua Webhook bảo mật (`payos.webhook`).
  - Trang xử lý kết quả thanh toán thành công / hủy giao dịch tự động kích hoạt gói VIP cho người dùng.
  - Ghi nhận lịch sử giao dịch thanh toán chi tiết (`PayosPayment`).

---

## 9. TRANG QUẢN TRỊ TOÀN DIỆN (ADMIN DASHBOARD)
- **Bảo mật phân quyền quản trị:** Middleware `IsAdmin` kiểm tra tài khoản quản trị viên.
- **Thống kê & Marketing thông minh:**
  - Tích hợp Google Analytics 4 (GA4 Data API) hiển thị báo cáo người dùng, lượt xem, nguồn traffic ngay trong CMS (`AdminMarketingController`).
  - Tổng quan số lượng kỳ thủ, ván cờ, phòng thi đấu, thế cờ.
- **Quản lý tài nguyên hệ thống (CRUD):**
  - Quản lý người dùng (`AdminUserController`).
  - Quản lý phòng chơi (`AdminRoomController`).
  - Quản lý thế cờ (`AdminPuzzleController`).
  - Quản lý giải đấu & danh sách đấu thủ (`AdminTournamentController`, `AdminTournamentParticipantController`).
  - Quản lý bài viết tin tức (`AdminArticleController`).
  - Quản lý thư viện ván cờ (`AdminGameController`).
  - Công cụ kiểm tra sức khỏe động cơ Pikafish (`testEngine`).

---

## 10. ĐĂNG NHẬP ĐA KÊNH (AUTHENTICATION & SOCIAL LOGINS)
- Đăng nhập, đăng ký tài khoản tiêu chuẩn với xác thực mã hóa.
- Quên mật khẩu và gửi link đặt lại mật khẩu qua email.
- **Google One-Tap:** Đăng nhập 1 chạm tiện lợi không cần chuyển trang.
- **Mạng xã hội đa dạng (Laravel Socialite):**
  - Google
  - Facebook
  - Zalo
  - GitHub
  - GitLab
  - Bitbucket
  - LinkedIn
- Hỗ trợ RESTful API Tokens qua Laravel Passport & Sanctum.

---

## 11. ĐA NGÔN NGỮ QUỐC TẾ HÓA (I18N)
- Hỗ trợ đầy đủ **5 ngôn ngữ**:
  - 🇻🇳 Tiếng Việt (`vi` - mặc định)
  - 🇬🇧 Tiếng Anh (`en`)
  - 🇯🇵 Tiếng Nhật (`ja`)
  - 🇰🇷 Tiếng Hàn (`ko`)
  - 🇨🇳 Tiếng Trung (`zh`)
- Cơ chế Localized Routing thông minh (`localized_path`, `localized_page_data`), tự động đồng bộ hóa URL thân thiện cho từng ngôn ngữ.
- Bản địa hóa toàn bộ giao diện: Bàn cờ, tên thế cờ, tiêu đề SEO, danh mục giải đấu, bảng xếp hạng DataTables.

---

## 12. BÀI VIẾT, TIN TỨC & KIẾN THỨC CỜ (ARTICLES / BLOG)
- Hệ thống bài viết hướng dẫn chiến thuật, cờ thế, tin tức giải đấu (`Article`).
- Hỗ trợ nội dung dịch đa ngôn ngữ cho từng bài viết (`ArticleTranslation`).
- Tích hợp trình soạn thảo bài viết phong phú kèm ảnh đại diện (`featured_image`).

---

## 13. TỐI ƯU HÓA KỸ THUẬT, SEO & HIỆU NĂNG
- **Tối ưu hóa SEO nâng cao:**
  - Tự động sinh XML Sitemaps theo từng ngôn ngữ: `sitemap_index.xml`, `sitemap.xml`, `sitemap-puzzles-{locale}.xml`, `sitemap-articles-{locale}.xml`.
  - Tích hợp Schema.org Structured Data (`spatie/schema-org`) chuẩn Google Rich Snippets.
  - Tự động tối ưu thẻ Canonical và thẻ hreflang.
- **Hiệu năng & Tốc độ tải trang:**
  - Tương thích **Laravel Octane** và máy chủ web LiteSpeed với bộ nhớ đệm `lscache-laravel`.
  - Bộ nén tài nguyên tự động `laravel-page-speed`.
  - Cache Redis phân tán và Atomic Locks tránh race condition khi xử lý thời gian thi đấu và điều khiển Engine Pool.
- **Hạ tầng kiểm thử toàn diện:**
  - Kiểm thử E2E giao diện và luồng trận đấu với **Playwright**.
  - Kiểm thử đồng bộ thời gian và luật cờ với PHPUnit (`TimerFlowTest`, `RoomTimerTest`).
