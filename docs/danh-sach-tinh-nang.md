# TỔNG QUAN TÍNH NĂNG HỆ THỐNG CỜ TƯỚNG (COTUONGCHAMTOP)

> **Cập nhật mới nhất:** 12/11/2026
> **Nền tảng:** Laravel 13 (PHP 8.3), Laravel Octane / Reverb / Pusher, Vite & Laravel Mix, Pikafish NNUE Engine Pool, Groq LLM AI.
> **Điểm nhấn:** Giao diện, đường dẫn, SEO, nội dung và trò chuyện được bản địa hóa đầy đủ cho **9 ngôn ngữ** (Việt, Anh, Nhật, Hàn, Trung, Đức, Pháp, Tây Ban Nha, Nga).

## MỤC LỤC

1. [Đa ngôn ngữ – 9 thứ tiếng](#1-đa-ngôn-ngữ--9-thứ-tiếng-i18n)
2. [Thi đấu trực tuyến](#2-thi-đấu-trực-tuyến-online-multiplayer)
3. [Chơi với máy & động cơ cờ tướng](#3-chơi-với-máy--động-cơ-cờ-tướng-ai--engine)
4. [Cờ thế & bàn cờ nghiên cứu](#4-cờ-thế--bàn-cờ-nghiên-cứu-puzzles--board-editor)
5. [Hệ thống giải đấu](#5-hệ-thống-giải-đấu-tournament-system)
6. [Kỳ phổ & thư viện ván cờ](#6-kỳ-phổ--thư-viện-ván-cờ-games-library)
7. [Thách đấu trực tiếp](#7-thách-đấu-trực-tiếp-challenge-system)
8. [Thành viên, Elo & Gamification](#8-hệ-thống-thành-viên-elo--gamification-gems--tiers)
9. [Gói hội viên VIP & thanh toán PayOS / VietQR](#9-gói-hội-viên-vip--cổng-thanh-toán-payos--vietqr)
10. [Trang quản trị](#10-trang-quản-trị-toàn-diện-admin-dashboard)
11. [Đăng nhập đa kênh](#11-đăng-nhập-đa-kênh-authentication--social-logins)
12. [Bài viết, tin tức & kiến thức cờ](#12-bài-viết-tin-tức--kiến-thức-cờ-articles--blog)
13. [Tối ưu kỹ thuật, SEO & hiệu năng](#13-tối-ưu-hóa-kỹ-thuật-seo--hiệu-năng)

---

## 1. ĐA NGÔN NGỮ – 9 THỨ TIẾNG (I18N)

Toàn bộ hệ thống vận hành trên **một luồng xử lý dùng chung** (một controller, một view, một khai báo route) kết hợp với **cấu hình và từ điển theo từng ngôn ngữ**. Thêm một ngôn ngữ mới chủ yếu là thêm cấu hình và bản dịch, không phải sao chép route, controller hay view.

### 1.1. Danh sách ngôn ngữ hỗ trợ

| Mã   | Ngôn ngữ (tên bản địa)                    | Mã cờ | Trang chủ | Thư mục lưu chat phòng |
| ---- | ----------------------------------------- | ----- | --------- | ---------------------- |
| `vi` | 🇻🇳 Tiếng Việt – _mặc định, không tiền tố_ | `vn`  | `/`       | `phongChatLog`         |
| `en` | 🇬🇧 English                                | `us`  | `/en`     | `roomChatLog`          |
| `ja` | 🇯🇵 日本語                                 | `jp`  | `/ja`     | `rumuChatLog`          |
| `ko` | 🇰🇷 한국어                                 | `kr`  | `/ko`     | `bangChatLog`          |
| `zh` | 🇨🇳 中文                                   | `cn`  | `/zh`     | `fangjianChatLog`      |
| `de` | 🇩🇪 Deutsch                                | `de`  | `/de`     | `raumChatLog`          |
| `fr` | 🇫🇷 Français                               | `fr`  | `/fr`     | `salonChatLog`         |
| `es` | 🇪🇸 Español                                | `es`  | `/es`     | `salaChatLog`          |
| `ru` | 🇷🇺 Русский                                | `ru`  | `/ru`     | `komnataChatLog`       |

> Tiếng Việt là ngôn ngữ mặc định (`locales.default`) và cũng là `x-default` trong thẻ hreflang. Các ngôn ngữ còn lại dùng tiền tố mã ngôn ngữ ở trang chủ.

### 1.2. Kiến trúc quốc tế hóa

- **Nguồn cấu hình duy nhất `config/locales.php`:**
    - `supported`: danh sách 9 ngôn ngữ đang bật; mọi vòng lặp sinh route, sitemap, hreflang, bộ chọn ngôn ngữ đều đọc từ đây.
    - `labels` và `flags`: tên hiển thị bản địa và mã cờ (`flag-icons`) cho bộ chọn ngôn ngữ.
    - `chat`: nơi lưu và phiên PHP riêng cho khung chat của từng ngôn ngữ (xem mục 1.7).
    - `paths`: **71 khóa đường dẫn**, mỗi khóa có đủ đường dẫn cho 9 ngôn ngữ (trang chủ, phòng, cờ thế, AI, tài khoản, đăng nhập, giải đấu, bài viết, thư viện ván cờ…).
- **Middleware `SetLocale` (`locale:{mã}`):**
    - Xác định ngôn ngữ từ tham số middleware, phiên (`session`) hoặc đường dẫn; ngôn ngữ không hợp lệ tự quay về mặc định.
    - Gọi `app()->setLocale()`, lưu vào phiên và chia sẻ cho mọi view: `locale`, `supportedLocales`, `localeLabels`, `localeFlags`.
- **Dịch vụ `LocalizedUrlService` và các hàm trợ giúp:**
    - `localized_path()`, `localized_url()`: sinh đường dẫn/URL theo khóa và ngôn ngữ.
    - `localized_alternate_paths()`: đường dẫn tương đương của trang hiện tại ở mọi ngôn ngữ.
    - `localized_page_data()`: gói sẵn `canonicalUrl`, `alternateUrls`, `langUrls` cho view.
    - Hỗ trợ tham số **dịch theo từng ngôn ngữ** (ví dụ slug bài viết khác nhau mỗi ngôn ngữ) qua `alternatePathsForLocales()`, tránh sinh liên kết sai gây lỗi 404.
    - `detectLocaleFromPath()` nhận diện ngôn ngữ qua tiền tố (`/en`, `/de`…) hoặc đoạn đường dẫn cũ của phòng (`phong`, `room`, `rumu`, `bang`, `fangjian`, `raum`, `salon`, `sala`, `komnata`).
- **Sinh route theo vòng lặp ngôn ngữ:**
    - Mỗi trang được đăng ký một lần cho từng ngôn ngữ trong `supported` kèm middleware `locale:{mã}`.
    - Tên route tiếng Việt giữ nguyên để không phá các thành phần Blade sẵn có; các ngôn ngữ khác có tiền tố `{mã}.` (ví dụ `de.login`).
    - Tiêu đề trang (`headTitle`) khai báo đủ 9 ngôn ngữ cho từng trang; thiếu bản dịch sẽ tự lùi về ngôn ngữ mặc định thay vì lỗi.
    - Các điểm cuối dữ liệu bảng cũng sinh theo ngôn ngữ: `/puzzles/{mã}`, `/users/{mã}`, `/rooms/{mã}`.

### 1.3. Đường dẫn thân thiện theo từng ngôn ngữ

Mỗi ngôn ngữ có bộ đường dẫn riêng, đọc được và nhất quán, đồng thời **giữ nguyên các URL cũ đã được lập chỉ mục** (Việt, Anh, Nhật, Hàn, Trung) để không mất thứ hạng SEO.

| Mã   | Trang chủ | Danh sách phòng     | Phòng (chủ phòng)  | Xếp hạng         | Bài viết     |
| ---- | --------- | ------------------- | ------------------ | ---------------- | ------------ |
| `vi` | `/`       | `/sanh-cho`         | `/phong/{code}`    | `/bang-xep-hang` | `/bai-viet`  |
| `en` | `/en`     | `/lobby`            | `/room/{code}`     | `/ranking`       | `/articles`  |
| `ja` | `/ja`     | `/heya-ichiran`     | `/rumu/{code}`     | `/rankingu`      | `/kiji`      |
| `ko` | `/ko`     | `/bang-moglog`      | `/bang/{code}`     | `/sun-wi`        | `/gisa`      |
| `zh` | `/zh`     | `/fangjianliebiao`  | `/fangjian/{code}` | `/paiming`       | `/wenzhang`  |
| `de` | `/de`     | `/raumliste`        | `/raum/{code}`     | `/rangliste`     | `/artikel`   |
| `fr` | `/fr`     | `/liste-des-salons` | `/salon/{code}`    | `/classement`    | `/article`   |
| `es` | `/es`     | `/lista-de-salas`   | `/sala/{code}`     | `/clasificacion` | `/articulos` |
| `ru` | `/ru`     | `/spisok-komnat`    | `/komnata/{code}`  | `/reyting`       | `/stati`     |

- Phòng chơi có đủ biến thể theo vai trò ở **cả 9 ngôn ngữ**: chủ phòng, khách, ngẫu nhiên, theo dõi, bên Đỏ, bên Đen (ví dụ tiếng Đức: `/raum/{code}/gast`, `/raum/{code}/zuschauen`, `/raum/{code}/rot`, `/raum/{code}/schwarz`).
- Mọi nhóm trang đều được bản địa hóa đường dẫn: trang tĩnh (giới thiệu, liên hệ, điều khoản, bảo mật), chơi một mình, cờ thế, các cấp độ AI, tài khoản, đăng nhập/đăng ký/quên mật khẩu, giải đấu, bài viết và thư viện ván cờ.
- **Không chuyển hướng chéo ngôn ngữ:** truy cập `/room/{code}` luôn hiển thị nội dung tiếng Anh, không bị đẩy sang `/phong/{code}`.

### 1.4. Bộ chọn ngôn ngữ trên giao diện

- Menu thả xuống ở thanh đầu trang liệt kê **đủ 9 ngôn ngữ** kèm cờ quốc gia (`flag-icons`) và tên bản địa (Tiếng Việt, English, 日本語, 한국어, 中文, Deutsch, Français, Español, Русский).
- **Giữ nguyên ngữ cảnh trang:** đổi ngôn ngữ dẫn tới đúng trang tương đương (cùng phòng, cùng thế cờ, cùng bài viết) thay vì quay về trang chủ; trang chưa khai báo sẽ lùi về trang chủ của ngôn ngữ đó.
- Ngôn ngữ đang dùng được đánh dấu nổi bật; lựa chọn được ghi nhớ trong phiên làm việc.
- Tiêu đề `<h1>` ẩn dành cho trình đọc màn hình/SEO luôn khớp với tiêu đề trang đã dịch.

### 1.5. Từ điển và bản dịch giao diện

- **Mỗi ngôn ngữ một tệp JSON** trong `resources/lang/`: `vi.json`, `en.json`, `ja.json`, `ko.json`, `zh.json`, `de.json`, `fr.json`, `es.json`, `ru.json`.
- Mỗi tệp có **khoảng 974 chuỗi giao diện**; `en`, `de`, `fr`, `es`, `ru` đồng bộ hoàn toàn bộ khóa với nhau.
- Mã nguồn dùng hàm `__('...')` với **chuỗi tiếng Việt gốc làm khóa** (ví dụ `__('Tin nhắn')` → `Nachricht` / `Message` / `Mensaje`), nên văn bản chưa dịch vẫn hiển thị được thay vì lộ khóa kỹ thuật.
- Bản dịch bao phủ toàn bộ giao diện: bàn cờ, thông báo trận đấu (ví dụ "Bạn là quân Đen"), nút bấm, biểu mẫu, thông báo lỗi và xác thực, tên cấp độ AI, tiêu đề SEO.
- Gói thông báo của Laravel (`auth`, `passwords`, `validation`, `pagination`) có sẵn cho tiếng Việt và tiếng Anh.
- Thư điện tử (thách đấu, liên hệ) và các phản hồi biểu mẫu đều đi qua hàm dịch, nên người dùng nhận nội dung đúng ngôn ngữ đang dùng.

### 1.6. Huấn luyện viên AI theo ngôn ngữ

- Toàn bộ lời nhắc (prompt), quy tắc ký hiệu và tiêu đề bản đồ bàn cờ của **Groq AI Coach** lấy từ **17 khóa `chess_coach.*` có đủ ở cả 9 ngôn ngữ**.
- Chuỗi dịch được chọn theo ngôn ngữ hiện tại của người dùng; việc thay biến (`:tên`) được xử lý một lượt để nội dung do người dùng nhập không bị thay thế lần hai.
- Tên cấp độ AI (Dễ nhất → Kiện tướng) được dịch ở cả 9 ngôn ngữ (ví dụ cấp 8: _Kiện tướng_ / _Grandmaster_ / _マスター_ / _마스터_ / _大师级_ / _Meister_ / _Maître_ / _Maestro_ / _Мастер_).

### 1.7. Phòng chơi và trò chuyện theo ngôn ngữ

- Mỗi ngôn ngữ có **khu vực lưu nhật ký chat và phiên PHP riêng** (`CoTuong_{VI|EN|JA|KO|ZH|DE|FR|ES|RU}-{mã phòng}`), quản lý tập trung bởi `ChatLogService`.
- Các ngôn ngữ cũ (vi/en/ja/ko/zh) giữ nguyên tên thư mục và tệp để khớp với dữ liệu chat đã có; các ngôn ngữ mới (de/fr/es/ru) đặt theo đoạn đường dẫn phòng của ngôn ngữ đó.
- Khi chuyển phòng sang chế độ xem (phòng đã kết thúc), hệ thống chuyển hướng tới **đúng trang theo dõi của cùng ngôn ngữ**.

### 1.8. SEO đa ngôn ngữ

- **Thẻ `canonical`** trỏ về URL của ngôn ngữ đang xem (mỗi bản dịch là một trang có nội dung riêng).
- **Thẻ `hreflang` cho đủ 9 ngôn ngữ** và `x-default` trỏ về tiếng Việt, sinh tự động từ `alternateUrls`/`langUrls`.
- Sơ đồ trang web gồm `sitemap_index.xml`, `sitemap.xml` (mọi route công khai có tên, không tham số) và các sitemap thế cờ/bài viết theo từng ngôn ngữ (`sitemap-puzzles-{mã}.xml`, `sitemap-articles-{mã}.xml`).
- Tiêu đề, mô tả và từ khóa (`meta_keywords`, `meta_desc`) được dịch theo từng ngôn ngữ; dữ liệu có cấu trúc Schema.org dùng chung.

### 1.9. Nội dung động nhiều ngôn ngữ

- **Bài viết (`Article` + `ArticleTranslation`):**
    - Một bài viết có nhiều bản dịch, mỗi bản có **tiêu đề, slug và nội dung riêng** cho từng ngôn ngữ; ảnh đại diện dùng chung.
    - Tìm kiếm ở ngôn ngữ hiện tại **và** ngôn ngữ mặc định để không bỏ sót bài; hiển thị bản dịch phù hợp nhất.
    - Liên kết đổi ngôn ngữ và hreflang trỏ đúng slug của từng bản dịch.
    - Trang quản trị cho phép nhập bản dịch cho mọi ngôn ngữ trong `supported`.
- **Bảng dữ liệu (DataTables):** thế cờ, thành viên, phòng chơi có điểm cuối riêng theo ngôn ngữ và nạp tệp ngôn ngữ bảng tương ứng.
- **Số đếm bằng chữ** ("N thế cờ đặc sắc") có hàm riêng cho tiếng Việt, Anh, Nhật, Hàn, Trung.

### 1.10. Kiểm thử đa ngôn ngữ

- `LocalizedUrlServiceTest`: kiểm tra việc sinh đường dẫn, URL, hreflang và nhận diện ngôn ngữ.
- `ChatLogServiceTest`: bắt buộc **mọi ngôn ngữ trong `supported` đều phải có cấu hình chat** – thiếu một ngôn ngữ là test thất bại.
- Kiểm thử E2E Playwright cho luồng ghép trận nhanh.
- Quy ước phát triển: mọi chuỗi hiển thị mới phải dùng `__('khóa')` và cập nhật `resources/lang/*.json` cho các ngôn ngữ được hỗ trợ.

### 1.11. Quy trình thêm một ngôn ngữ mới

1. Thêm mã vào `supported`, cùng `labels` và `flags` trong `config/locales.php`.
2. Khai báo đường dẫn cho ngôn ngữ mới ở **mọi khóa** trong `paths` (71 khóa) và một mục trong `chat`.
3. Tạo `resources/lang/{mã}.json` (gồm cả 17 khóa `chess_coach.*`) và bổ sung tiêu đề trang trong các khai báo route.
4. Bổ sung tệp ngôn ngữ cho bảng dữ liệu và (nếu cần) hàm số đếm bằng chữ.
5. Chạy kiểm thử; sitemap, hreflang, bộ chọn ngôn ngữ và trang quản trị bài viết tự nhận ngôn ngữ mới.

### 1.12. Hạng mục còn đang hoàn thiện cho 9 ngôn ngữ

Phần cốt lõi (route, giao diện, từ điển, hreflang, chat, AI Coach, bài viết) đã chạy đủ 9 ngôn ngữ. Theo mã nguồn hiện tại, một số hạng mục phụ vẫn mới dừng ở 5 ngôn ngữ đầu:

| Hạng mục                                                                   | Hiện trạng                                                                                  | Việc cần làm                                           |
| -------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| Sitemap thế cờ và bài viết (`SitemapController::SITEMAP_LOCALES`)          | Chỉ gồm `vi, en, ja, ko, zh`; `/sitemap-puzzles-de.xml` và tương tự (fr/es/ru) hiện trả 404 | Đọc danh sách từ `config('locales.supported')`         |
| Mẫu hreflang dự phòng (`common/url.blade.php`)                             | Nhánh dự phòng chỉ phát 5 thẻ hreflang                                                      | Dùng chung logic với `common/head.blade.php` (đã đủ 9) |
| Tệp ngôn ngữ DataTables (`public/js/TablePuzzle*.json`, `TableUser*.json`) | Có `Vi, En, Ja, Ko, Zh`; chưa có `De, Fr, Es, Ru`                                           | Bổ sung tệp cho 4 ngôn ngữ mới                         |
| Hàm số đếm bằng chữ (`numberToWords*`)                                     | Có `Vi, En, Ja, Ko, Zh`                                                                     | Bổ sung De, Fr, Es, Ru                                 |
| Độ phủ từ điển                                                             | `ja`, `ko`, `zh` lệch nhẹ so với `en` (thiếu lần lượt 1, 1 và 6 khóa)                       | Đồng bộ khóa                                           |
| Tài liệu `docs/multilanguage-refactor-plan.md`                             | Vẫn ghi 5 ngôn ngữ                                                                          | Cập nhật theo cấu hình hiện tại                        |

---

## 2. THI ĐẤU TRỰC TUYẾN (ONLINE MULTIPLAYER)

- **Tạo phòng thi đấu linh hoạt:**
    - Tùy chỉnh tên phòng, tạo mã phòng duy nhất (`code`).
    - Đặt mật khẩu phòng riêng tư (`pass`) để giao lưu bạn bè hoặc phòng mở tự do.
- **Ghép trận thông minh (Matchmaking):**
    - Cơ chế **Ghép nhanh (Quick Match / Find Match)** hỗ trợ cả khách vãng lai (Guest) lẫn thành viên đăng nhập (Auth).
    - API kiểm tra và xếp hàng chờ ghép trận tự động (`/match/find`, `/match/status`).
    - Thông báo trạng thái ghép trận hiển thị đúng ngôn ngữ người chơi.
- **Nhiều vai trò tham gia phòng (có đường dẫn riêng ở cả 9 ngôn ngữ):**
    - **Chủ phòng (`room.host`)** / **Khách (`room.guest`)**.
    - Chọn phe chủ động: **Bên Đỏ (`room.red`)**, **Bên Đen (`room.black`)**, hoặc **Ngẫu nhiên (`room.random`)**.
    - **Chế độ Khán giả (`room.watch`):** Cho phép người ngoài theo dõi trận đấu trực tiếp mà không can thiệp vào ván cờ.
- **Đồng bộ thời gian thực & chống gian lận:**
    - Đồng bộ trạng thái bàn cờ qua **Server-Sent Events (SSE)** (`/getFEN/{code}`) và **WebSocket** (Laravel Reverb / Pusher) qua sự kiện `RoomUpdated`.
    - Lưu trữ biên bản ván đấu (`moves`) và chuỗi trạng thái bàn cờ FEN chuẩn quốc tế.
    - **Đồng hồ thi đấu kép (Chess Clock):**
        - Kiểm soát thời gian độc lập cho Đỏ (`red_time`) và Đen (`black_time`).
        - Giới hạn thời gian suy nghĩ tối đa cho mỗi nước đi (120 giây/nước).
        - Cơ chế chuyển lượt nguyên tử (`switchTurn`), tự động tính thời gian trôi qua và xử thua khi hết giờ (`processTimeout`).
    - **Cơ chế chống can thiệp:**
        - Chặn đổi phe hoặc vào sai phe bằng cách thao tác URL.
        - Tự động chuyển hướng sang chế độ xem (`room.watch`) của cùng ngôn ngữ khi truy cập các phòng đã kết thúc.
- **Tương tác trong trận đấu:**
    - Gửi tín hiệu nhanh (`/room/{code}/signal`): cầu hòa, xin hoãn, đầu hàng (Resign), xin đi lại (Undo).
    - Khung chat thời gian thực giữa người chơi và khán giả, sao lưu nhật ký chat theo từng ngôn ngữ (mục 1.7).

---

## 3. CHƠI VỚI MÁY & ĐỘNG CƠ CỜ TƯỚNG (AI & ENGINE)

- **Động cơ Pikafish NNUE chuyên nghiệp:**
    - Tích hợp binary `pikafish_vps` kết hợp mạng nơ-ron đánh giá vị trí `pikafish.nnue`.
    - **Kiến trúc Engine Worker Pool:** duy trì các tiến trình Pikafish luôn chạy ngầm (warm worker), loại bỏ độ trễ khởi động ~2s+ mỗi lượt đi.
    - Lệnh Artisan quản trị pool: `php artisan xiangqi:pool:ensure`.
    - **Bảng điều khiển ngầm (Secret Ops Dashboard):** truy cập an toàn qua URL bảo mật `/_ops/{secret}/xiangqi-pool` để giám sát worker và khởi động lại pool.
- **7 cấp độ thách đấu AI** (có trang và tên riêng ở cả 9 ngôn ngữ):
    1. _Dễ nhất_ (Easiest – Level 1)
    2. _Mới chơi_ (Newbie – Level 1)
    3. _Dễ_ (Easy – Level 2)
    4. _Bình thường_ (Normal – Level 3)
    5. _Khó_ (Hard – Level 4)
    6. _Khó nhất_ (Hardest – Level 5)
    7. _Kiện tướng_ (Grandmaster – Level 8)
- **Tính năng AI hỗ trợ người chơi:**
    - Tìm nước đi tối ưu (`/api/xiangqi/best-move`).
    - Gợi ý nước đi khi bế tắc (`/api/xiangqi/hint`).
    - Đánh giá điểm lợi thế vị trí bàn cờ (`/api/xiangqi/analyze`).
- **Huấn luyện viên AI thông minh (Groq LLM AI Coach):**
    - Tích hợp các mô hình ngôn ngữ lớn tốc độ cao qua Groq API (`llama-3.3-70b-versatile`, `gpt-oss-120b`, `gpt-oss-20b`…), tự động thử lần lượt các mô hình dự phòng.
    - Tự động chuyển chuỗi FEN thành bản đồ trực quan ASCII (Visual Board) để AI hiểu bố cục quân cờ.
    - Đóng vai **Huấn luyện viên Cờ Tướng**: nhận xét hóm hỉnh, phân tích chiến thuật ngắn gọn.
    - Tuân thủ quy tắc ký hiệu cờ tướng: `[Tên Quân] [Cột Đứng 1-9] [Tấn/Thoái/Bình] [Tham số]`.
    - **Lời nhắc và quy tắc của Coach được bản địa hóa theo 9 ngôn ngữ** (mục 1.6).
    - API phân tích và đàm thoại chiến thuật: `/api/chess/analyze`, `/api/chess/chat`.
- **Chơi một mình (Solo Play):** chế độ tự luyện tập đi cả 2 bên trên một bàn cờ (`human.play`), có đường dẫn riêng cho từng ngôn ngữ.

---

## 4. CỜ THẾ & BÀN CỜ NGHIÊN CỨU (PUZZLES & BOARD EDITOR)

- **Xếp cờ thế (Puzzle Setup):** giao diện trực quan đặt quân cờ lên bàn cờ trống để tạo thế cờ tàn cuộc, sát cục.
- **Đăng tải và quản lý thế cờ:**
    - Tạo mới thế cờ với tên gọi, mô tả chi tiết, kiểm tra trùng tên theo thời gian thực (`checkUniqueName`).
    - Tùy chọn trạng thái công khai (`is_public`) hoặc lưu nội bộ.
- **Giải cờ thế (Puzzle Solving):**
    - Giải hàng ngàn thế cờ do cộng đồng đóng góp, danh sách có bảng dữ liệu theo ngôn ngữ (`/puzzles/{mã}`).
    - Chế độ **Nhờ AI giải thế cờ (`puzzle.ai.solve`)**: động cơ máy tính phân tích và đi tiếp thế cờ hóc búa.
    - Bàn cờ tự giải (`board.self`): bàn cờ mở, di chuyển thử các biến thể.
- **Tương tác cộng đồng trên thế cờ:**
    - Biểu cảm đa chiều (Reactions): Like (Thích), Hard (Thế cờ khó), Unsolved (Chưa giải được).
    - Đánh giá sao và Upvote / Downvote thế cờ (`puzzle.rating`).
    - Bình luận thảo luận đa cấp (Reply lồng nhau) và thích bình luận.
- **Đa ngôn ngữ:** trang thế cờ, trang giải bằng AI và bàn cờ các cấp độ đều có đường dẫn theo từng ngôn ngữ (sitemap thế cờ hiện mới chạy cho 5 ngôn ngữ đầu, xem mục 1.12).

---

## 5. HỆ THỐNG GIẢI ĐẤU (TOURNAMENT SYSTEM)

- **Tổ chức giải đấu quy mô:**
    - Tạo giải với tiêu đề, đường dẫn thân thiện (slug), ảnh bìa, lịch trình bắt đầu/kết thúc.
    - Phân quyền Trưởng ban tổ chức (`tournament.creator`) để chỉnh sửa, cập nhật hoặc hủy giải.
- **Đăng ký thi đấu:** người dùng đăng nhập ghi danh tham gia giải bằng một thao tác.
- **Tự động sinh bảng đấu loại trực tiếp (Single Elimination Bracket):**
    - Tự động xếp cặp và vẽ sơ đồ nhánh đấu.
    - Tự động liên kết các phòng thi đấu theo từng vòng (`tournament_round`) và đưa đấu thủ thắng vào vòng trong (`next_room_code`).
    - Quản lý danh sách đấu thủ từng giải từ Admin Panel.
- **Đa ngôn ngữ:** danh sách, chi tiết, tham gia và tạo bảng đấu có đường dẫn riêng cho cả 9 ngôn ngữ (ví dụ `/giai-dau`, `/tournaments`, `/turniere`, `/tournois`, `/torneos`, `/turniry`).

---

## 6. KỲ PHỔ & THƯ VIỆN VÁN CỜ (GAMES LIBRARY)

- **Thư viện ván cờ chia sẻ:**
    - Danh mục các ván cờ kinh điển và ván đấu đỉnh cao của danh thủ (`games.library`).
    - Xem chi tiết từng nước đi, tua lại từng bước cờ (`games.show`).
- **Quản lý ván đấu:** CRUD ván cờ từ trang quản trị, cập nhật điểm đánh giá ván cờ.
- **Đa ngôn ngữ:** thư viện và trang ván cờ có đường dẫn ở cả 9 ngôn ngữ (ví dụ `/thu-vien`, `/library`, `/bibliothek`, `/bibliotheque`, `/biblioteca`, `/biblioteka`).

---

## 7. THÁCH ĐẤU TRỰC TIẾP (CHALLENGE SYSTEM)

- **Thách đấu qua link bàn cờ:** tạo link thách đấu trực tiếp theo bất kỳ thế cờ nào: `/thach-dau/{board}`.
- **Mời thách đấu qua Email:** gửi thư điện tử mời đối thủ vào bàn quyết chiến (`competeMail`), tiêu đề và nội dung thư dùng khóa dịch.

---

## 8. HỆ THỐNG THÀNH VIÊN, ELO & GAMIFICATION (GEMS & TIERS)

- **Hệ thống tính điểm Elo chuẩn:**
    - Thuật toán Elo tự động tính sau mỗi trận thắng, hòa, thua (`EloRatingService`).
    - Bảng xếp hạng kỳ thủ trực tuyến (`app.ranking`) cập nhật theo điểm số và hiệu số đối đầu.
- **Hồ sơ kỳ thủ chi tiết (`app.player`, `app.profile`):**
    - Thống kê: điểm Elo, số trận thắng/hòa/thua, lịch sử trận đã chơi (`app.history`).
    - Trạng thái trực tuyến thời gian thực (chấm xanh hoạt động trong 5 phút qua `last_seen_at`).
    - Đổi tên hiển thị, đổi mật khẩu, đổi giao diện bàn cờ (`app.ui`).
    - Tải lên ảnh đại diện tùy chỉnh hoặc tự sinh Avatar chữ cái (`laravolt/avatar`).
- **Gamification – Điểm Karma & Danh hiệu Ngọc (Gems Tier):**
    - Tích lũy ngọc qua hoạt động (`KarmaLog`): đăng nhập mỗi ngày, hoàn thành trận đấu, giành chiến thắng.
    - **6 cấp bậc huân chương & khung viền Avatar động (Avatar Frame):**
        1. **Đồng (Bronze):** huy hiệu Khiên, viền đồng metallic.
        2. **Bạc (Silver):** huy hiệu Ngôi sao, viền thép sáng bóng.
        3. **Vàng (Gold):** huy hiệu Huy chương, viền vàng kim loại lấp lánh (shimmer).
        4. **Bạch Kim (Platinum):** huy hiệu Cúp, viền băng thanh ngọc phát sáng.
        5. **Kim Cương (Diamond):** huy hiệu Kim cương, viền tím hào quang kép đa tầng.
        6. **Huyền Thoại (Legendary):** huy hiệu Vương miện, vòng lửa rực cháy xoay tròn 360 độ (`conic-gradient` spin).
- **Đa ngôn ngữ:** hồ sơ, lịch sử, xếp hạng, đổi tên/mật khẩu/giao diện, ảnh đại diện đều có đường dẫn và nhãn dịch cho cả 9 ngôn ngữ.

---

## 9. GÓI HỘI VIÊN VIP & CỔNG THANH TOÁN (PAYOS / VIETQR)

- **Gói hội viên Standard (VIP):**
    - Nâng cấp tài khoản để loại bỏ toàn bộ quảng cáo (`ads_removed`).
    - Quản lý kỳ hạn thuê bao (`subscription_started_at`, `subscription_ends_at`).
    - Trang giới thiệu gói VIP có đường dẫn theo ngôn ngữ (`/vip`, `/en/vip`, `/de/vip`…).
- **Cổng thanh toán tự động PayOS:**
    - Tích hợp chuẩn **VietQR** của ngân hàng Việt Nam.
    - Đối soát đơn hàng tự động qua Webhook bảo mật (`payos.webhook`), có lệnh đồng bộ giao dịch treo (`SyncPendingPaymentsCommand`).
    - Trang kết quả thanh toán thành công / hủy giao dịch tự động kích hoạt gói VIP.
    - Lưu lịch sử giao dịch chi tiết (`PayosPayment`).

---

## 10. TRANG QUẢN TRỊ TOÀN DIỆN (ADMIN DASHBOARD)

- **Bảo mật phân quyền:** middleware `IsAdmin` kiểm tra tài khoản quản trị viên.
- **Thống kê & Marketing:**
    - Tích hợp Google Analytics 4 (GA4 Data API) hiển thị người dùng, lượt xem, nguồn traffic ngay trong CMS (`AdminMarketingController`).
    - Tổng quan số lượng kỳ thủ, ván cờ, phòng thi đấu, thế cờ.
- **Quản lý tài nguyên hệ thống (CRUD):**
    - Người dùng (`AdminUserController`), phòng chơi (`AdminRoomController`), thế cờ (`AdminPuzzleController`).
    - Giải đấu và danh sách đấu thủ (`AdminTournamentController`, `AdminTournamentParticipantController`).
    - **Bài viết đa ngôn ngữ (`AdminArticleController`):** biểu mẫu nhập bản dịch cho mọi ngôn ngữ đang bật.
    - Thư viện ván cờ (`AdminGameController`).
    - Công cụ kiểm tra sức khỏe động cơ Pikafish (`testEngine`).

---

## 11. ĐĂNG NHẬP ĐA KÊNH (AUTHENTICATION & SOCIAL LOGINS)

- Đăng nhập, đăng ký tiêu chuẩn với xác thực mã hóa; quên mật khẩu và đặt lại qua email.
- Toàn bộ trang xác thực (đăng nhập, đăng ký, quên/tạo/đặt lại mật khẩu, đăng xuất) có **đường dẫn và nội dung theo 9 ngôn ngữ**.
- **Google One-Tap:** đăng nhập một chạm không cần chuyển trang.
- **Mạng xã hội (Laravel Socialite):** Google, Facebook, Zalo, GitHub, GitLab, Bitbucket, LinkedIn.
- Hỗ trợ RESTful API Tokens qua Laravel Passport & Sanctum.

---

## 12. BÀI VIẾT, TIN TỨC & KIẾN THỨC CỜ (ARTICLES / BLOG)

- Hệ thống bài viết hướng dẫn chiến thuật, cờ thế, tin tức giải đấu (`Article`).
- **Bản dịch riêng cho từng ngôn ngữ (`ArticleTranslation`):** tiêu đề, slug, nội dung độc lập; có cơ chế lùi về ngôn ngữ mặc định khi chưa có bản dịch.
- Danh sách và chi tiết bài viết có đường dẫn theo ngôn ngữ (`/bai-viet`, `/articles`, `/kiji`, `/gisa`, `/wenzhang`, `/artikel`, `/article`, `/articulos`, `/stati`); liên kết đổi ngôn ngữ và hreflang trỏ đúng slug của từng bản dịch.
- Trình soạn thảo bài viết phong phú kèm ảnh đại diện dùng chung (`featured_image`).

---

## 13. TỐI ƯU HÓA KỸ THUẬT, SEO & HIỆU NĂNG

- **SEO nâng cao:**
    - Sitemap XML: `sitemap_index.xml`, `sitemap.xml`, `sitemap-puzzles-{mã}.xml`, `sitemap-articles-{mã}.xml` (hiện chạy cho 5 ngôn ngữ đầu, xem mục 1.12).
    - Schema.org Structured Data (`spatie/schema-org`) chuẩn Google Rich Snippets.
    - Thẻ `canonical` theo ngôn ngữ và thẻ `hreflang` đủ 9 ngôn ngữ cùng `x-default`.
- **Hiệu năng & tốc độ tải trang:**
    - Tương thích **Laravel Octane** và máy chủ LiteSpeed với bộ nhớ đệm `lscache-laravel`.
    - Nén tài nguyên tự động bằng `laravel-page-speed`.
    - Cache Redis phân tán và Atomic Locks tránh race condition khi xử lý thời gian thi đấu và điều khiển Engine Pool.
    - Sitemap được cache 6 giờ.
- **Hạ tầng kiểm thử:**
    - E2E giao diện và luồng trận đấu với **Playwright**.
    - PHPUnit cho đồng hồ và luật cờ (`TimerFlowTest`, `RoomSwitchTurnTest`, `FullMatchFlowTest`), luồng sự kiện (`EventStreamTest`), phân quyền kết quả (`UpdateResultAuthorizationTest`), thanh toán (`PayosNotificationServiceTest`) và đa ngôn ngữ (`LocalizedUrlServiceTest`, `ChatLogServiceTest`).
