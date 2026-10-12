# Sửa đồng bộ timer

Nhánh: `codex/fix-timer-sync`.

## Thay đổi

- Nhận số giây, thời gian nước đi và mốc tính elapsed trong cùng một lần sync.
  Renderer không reset mốc thời gian. Pause giữ cả clock tổng và clock nước đi.
- Dùng performance.now cho elapsed local. Ước lượng offset server qua HTTP,
  rồi tính tuổi snapshot của event; RTT/2 là ước lượng, không quyết định thắng thua.
- Thêm timer_revision, request sequence và generation của nước đi để loại snapshot
  cũ. Khi request đang chờ, không nhận event của FEN khác làm đảo optimistic turn.
- API updateFEN của client mới lưu FEN/moves/timer trong một transaction có row lock,
  kiểm tra expected_fen/lượt/hết giờ. Retry cùng FEN không trừ giờ hoặc broadcast lại.
  Endpoint switchTurn cũ còn hoạt động nhưng kiểm tra lượt dưới row lock.
- RoomUpdated có snapshot timer để bỏ GET bổ sung và debounce 200 ms trên mỗi event.
  Fallback 15 giây, reconnect, online và visibilitychange đồng bộ lại khi mất event.
- Timer local chạm 0 chỉ yêu cầu sync. Chỉ snapshot server xác nhận hết giờ mới
  gọi ghi kết quả; endpoint ghi timeout kiểm tra lại trong transaction. Nếu ghi
  thất bại, client mở lại timer, có backoff để tránh lặp request liên tục.
- HTTP snapshot khôi phục bàn cờ nếu move bị từ chối hoặc phản hồi bị mất.
  Phiên bản board cũng loại event cũ. Nước cuối được lưu trước khi chốt chiếu bí/hòa.
- Resume đặt last_update về hiện tại; startMatch idempotent và đợi đủ hai phía.
  getRoomIds trả cờ readiness cho khách ẩn danh, không trả mã session mới.
- Migration dùng decimal(12,3), timestamp(3) và timer_revision để giữ mili giây.
  Text DOM chỉ ghi khi nội dung thay đổi.

## Kiểm chứng

Test trình duyệt chạy trực tiếp JavaScript/DOM timer từ Blade, phần khởi tạo Echo
và handler lưu/khôi phục bàn cờ; API, Echo và thời gian được giả lập trong các ca
hồi quy. Bài baseline setInterval chạy thời gian thật. Đây không phải E2E backend.

```sh
npm install --prefix /tmp/cotuong-timer-audit playwright --no-audit --no-fund
NODE_PATH=/tmp/cotuong-timer-audit/node_modules node scripts/timer-lag-audit.cjs
```

Kết quả: docs/timer-sync-test-results.json. Có thêm tests backend cho start/resume,
snapshot, mili giây, atomic move/retry, stale move, hết giờ và xác nhận kết quả.
Đã kiểm tra cú pháp PHP và git diff --check.
18 tình huống trình duyệt đã qua. Sau npm ci cũng có thể chạy npm run test:timer;
trên Linux dùng Chromium của Playwright hoặc đặt CHROME_PATH cho browser có sẵn.

Đã chạy thực tế ngày 2026-10-12 bằng PHP 8.4.26, Laravel 13.31.0,
SQLite local biệt lập và Reverb local (không dùng Docker):

- Tất cả migration đã chạy trên `/tmp/cotuong-timer-local.sqlite`.
- PHPUnit: **21 tests passed, 95 assertions**; DB test dùng SQLite `:memory:`.
- Chrome: hai phiên cookie riêng trên 127.0.0.1 và localhost, ghép bằng nút
  TÌM TRẬN vào phòng `423fd10c08fd11b207c4e7c67d4f5467` (blue moon).
- Đi thật 10 nước: a3a4, a6a5, c3c4, c6c5, e3e4, e6e5, g3g4, g6g5,
  i3i4, i6i5. Không gọi game.load thủ công để giả lập đối thủ. Cả hai tab
  hiển thị cùng bàn cờ và kỳ phổ; DB có đủ 10 nước, revision 11, tới lượt đỏ.
- Reload tab đỏ giữ thời gian còn lại và bàn cờ. Restart Reverb kiểm tra reconnect;
  hai tab tự kết nối lại và nhận hai nước cuối qua sự kiện room.updated.
- Ván đầu để hết 120 giây đã ghi kết quả đen thắng và hiện thông báo đỏ thua.
- Test tích hợp phát hiện timer include bị comment trong room_master; đã bật lại
  cho cả người chơi và người xem. Thêm cấu hình host/port/scheme tùy chọn cho
  WebSocket local; mặc định production vẫn dùng TLS.

SQLite đã bật WAL cho bài test nhiều worker; ban đầu có một lỗi database locked
khi hai tab cùng startMatch. Không kết luận hiệu năng/row lock MySQL hoặc Pusher
production từ SQLite local. Chưa mô phỏng mạng chậm trong bài Chrome thật.
Ảnh minh chứng: `/tmp/cotuong-red-player.jpg`, `/tmp/cotuong-black-player.jpg`.

## Áp dụng và kiểm tra trên môi trường Laravel đầy đủ

Chạy migration trước khi bật code mới: code mới ghi cột timer_revision.
Không áp dụng production migration trong lượt làm việc này.
Migration down chuyển decimal về integer nên sẽ mất phần giây lẻ.

Trên môi trường test có PHP đúng yêu cầu, dependencies và cấu hình DB/cache:

```sh
php artisan migrate
php artisan test --filter='TimerFlowTest|RoomTimerTest'
```

Feature tests dùng RefreshDatabase: chỉ chạy trên DB test biệt lập.
Tiếp theo chạy E2E hai người chơi + người xem với mạng chậm, chuyển tab,
mất WebSocket, pause/resume và các nước đi sát giới hạn 120 giây; kiểm tra
chiếu bí/hòa/thua giờ, ghi kết quả, ELO và giải đấu trên cấu hình thực tế.

Broadcast vẫn đồng bộ theo ShouldBroadcastNow; chưa chuyển queue vì chưa có
benchmark/worker configuration để chứng minh thay đổi đó cải thiện độ trễ.
API FEN-only cũ vẫn được giữ cho resignation/caller cũ; transaction gộp nước đi
và timer được dùng cho nước đi của trang phòng mới.
