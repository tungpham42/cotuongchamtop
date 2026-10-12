# Kiểm tra timer — 12/10/2026

Đây là báo cáo trước sửa. Script hiện đã chuyển thành test hồi quy cho nhánh
`codex/fix-timer-sync`; kết quả mới ở `timer-sync-test-results.json`.
Chi tiết thay đổi và cách kiểm chứng backend ở `timer-sync-fix.md`.

## Phạm vi và cách chạy

Đọc luồng Laravel RoomController → Room → RoomUpdated và timer.blade.php,
cùng chỗ gọi switchTurn/updateFEN trong room_master.blade.php.
Chạy 8 tình huống trên Chrome headless 154.0.8037.99, dùng chính HTML/CSS
và JavaScript lõi của timer, không viết lại thuật toán cần kiểm tra.
7 tình huống dùng thời gian/snapshot API giả lập; 1 tình huống chạy setInterval thật
trong 3,2 giây. Không chạy phần khởi tạo Echo/presence trong harness.

Chưa chạy được Laravel/E2E hai người chơi: checkout thiếu vendor, .env và node_modules;
PHP hiện có là 8.2, composer.json yêu cầu ^8.3 và Laravel ^13. `php artisan --version`
dừng ở lỗi thiếu vendor/autoload.php. Không đo được DB, Pusher hay latency production.
Không sửa logic sản phẩm hoặc cài PHP toàn máy. Playwright được cài riêng trong /tmp.

Chạy lại từ thư mục dự án (có Google Chrome):

```sh
npm install --prefix /tmp/cotuong-timer-audit playwright --no-audit --no-fund
NODE_PATH=/tmp/cotuong-timer-audit/node_modules node scripts/timer-lag-audit.cjs
```

Kết quả lỗi ban đầu được giữ trong docs/timer-lag-audit-results.json.
Script hiện ghi docs/timer-sync-test-results.json và kiểm tra hành vi đã sửa.

## Kết quả thực nghiệm

| Tình huống | Kỳ vọng | Quan sát |
|---|---|---|
| Đếm 5 giây, không sync | 9:55 | 9:55, đúng |
| Sync sau 1 giây, server trả 599 giây | Giữ 9:59 | Nhảy từ 9:59 về 10:00; đồng hồ nước đi cũng về 2:00 |
| Sync mỗi 500 ms trong 2 giây | Giảm đều | 10:00 → 10:00 → 10:00 → 9:58 |
| paused:red ở 500 giây, chờ 5 giây | Giữ 8:20 | Giảm xuống 8:15 |
| Snapshot đỏ cũ đến sau khi đã chuyển đen | Tiếp tục lượt đen | Quay lại lượt đỏ |
| Snapshot chậm 800 ms | Sau tổng 1,8 giây còn 598,2 | Tính còn 599; chậm 0,8 giây |
| Local hết giờ, server sau đó trả còn 5 giây | Xác minh trước khi kết thúc | Gọi updateResult và bỏ qua snapshot sửa sai |
| Timer thật chạy 3,2 giây | Khoảng 9:57 | 9:57; 32 tick; trung bình 0,056 ms/tick, lớn nhất 0,2 ms |

Chi phí tick chỉ đo timer độc lập trên máy này. Không đại diện toàn bộ trang,
điện thoại hoặc thời gian render/paint; không loại trừ main thread bị nghẽn bởi script khác.

## Nguyên nhân đã xác nhận ở client

1. **Mốc thời gian và số giây không được cập nhật cùng nhau.**
   timer.blade.php:254 chỉ nhận số giây mới nếu đổi người hoặc lệch >1,5 giây,
   nhưng dòng 261 luôn reset localLastUpdate. Ví dụ time.red vẫn là 600 dù
   snapshot là 599; reset elapsed về 0 khiến hiển thị lại 600.
   serverMoveElapsed cũng bị giữ cũ. Đây là nguyên nhân trực tiếp của khựng/nhảy số.

2. **Pause bị biến thành active.** Dòng 247 lấy `red` từ `paused:red`,
   rồi dòng 265 gọi startAll. Server Room::getCalculatedTimes không trừ khi pause,
   nhưng client vẫn trừ, tạo sai lệch và khả năng kết thúc ván sai.

3. **Không có thứ tự snapshot.** syncTimerState chấp nhận mọi dữ liệu đến,
   không có revision, request sequence hoặc hàng rào cho nước đi đang chờ.
   Phản hồi GET cũ có thể ghi đè trạng thái optimistic mới.

4. **Không tính tuổi snapshot.** Dữ liệu được neo vào Date.now ở thời điểm nhận,
   nên thời gian vận chuyển không bị trừ. API chưa có server_now/snapshot_at;
   last_update là mốc lượt đi, không phải thời điểm tính snapshot.

5. **Kết quả ván bị chốt từ clock local.** updateUI gọi updateResult khi số giây
   local chạm 0, rồi isGameOver khiến syncTimerState không nhận chỉnh sửa từ server.

## Rủi ro nhìn thấy trong code, chưa benchmark backend

- room_master.blade.php:220–235 gửi switchTurn và updateFEN độc lập.
  Cả hai controller đều phát RoomUpdated; client còn debounce 200 ms rồi GET getTime.
  Độ trễ người xem/đối thủ gồm xử lý request, broadcast, 200 ms và request GET bổ sung.
  Hai request cập nhật cùng room có thể tạo trạng thái FEN/timer không đồng nhất.
- RoomUpdated implements ShouldBroadcastNow: gửi broadcast đồng bộ ngay trong request.
  Chưa đo nên chưa khẳng định Pusher gây chậm, nhưng cần tách timing DB/broadcast/HTTP.
- Room::switchTurn không khóa row, không kiểm tra current_player trùng lượt hiện tại,
  không có move ID chống lặp. Request lặp/đồng thời có thể trừ thời gian sai.
- Cột red_time/black_time là integer, last_update timestamp không khai báo phần
  thập phân, trong khi tính elapsed bằng milliseconds. Cần kiểm tra DB thực tế
  và độ chính xác lưu trữ trước khi quyết định migration.
- startTimer resume không đặt lại last_update; Room không cast last_update thành
  datetime. Cần kiểm thử resume backend và định dạng last_update trả về.
- getRoomIds chỉ select host_id/guest_id nhưng client kiểm tra thêm host_session/
  guest_session; fallback khởi động cho khách ẩn danh có thể không hoạt động.
- Không có sync định kỳ sau khi bắt đầu trận, hoặc xử lý reconnect/visibilitychange.
  Mất sự kiện WebSocket có thể giữ sai lượt tới lần nhận sự kiện tiếp theo.
- TimerFlowTest hiện kỳ vọng startTimer khởi động từ null và gọi pauseTimer;
  implementation chỉ resume paused, còn route pauseTimer không tồn tại.
  Suite này cần cập nhật trước khi dùng làm bằng chứng cho luồng timer hiện tại.

## Đề xuất sửa theo thứ tự

### 1. Sửa logic đồng bộ client trước

- Với snapshot hợp lệ/mới nhất, luôn cập nhật đồng thời red_time, black_time,
  move_elapsed và mốc tính elapsed. Bỏ điều kiện lệch 1,5 giây ở dữ liệu nền.
  Nếu cần làm mượt, chỉ làm mượt phần hiển thị; không giữ số giây nền cũ rồi reset mốc.
- Tách trạng thái running/paused/waiting; paused chỉ đánh dấu bên đang chơi và
  hiển thị thời gian đứng yên. Dùng performance.now cho khoảng thời gian local.
- Thêm request sequence để loại GET cũ và ngăn snapshot trước nước đi đang chờ
  ghi đè optimistic state. Server revision là giải pháp bền vững cho nhiều client.
- Khi local chạm 0, yêu cầu server xác minh timeout; chỉ chốt kết quả khi server
  xác nhận. Controller xử lý kết quả phải tự kiểm tra thời gian/lượt, không tin
  kết quả timeout do trình duyệt gửi.

### 2. Gộp nước đi và đổi timer thành một thao tác server

- Một API nhận move/FEN, expected revision và move ID; transaction + lockForUpdate
  kiểm tra người chơi, lượt, kết quả và timeout, rồi lưu FEN/timer cùng lúc.
- Trả snapshot gồm remaining times, move_elapsed, status, result, revision,
  snapshot_at/server_now; broadcast cùng snapshot sau khi commit.
- Người chơi và người xem nhận snapshot trực tiếp từ event, bỏ GET bổ sung và
  debounce 200 ms ở luồng cập nhật timer.
- Ước lượng độ lệch giờ server/độ trễ từ các lần đồng bộ để tính tuổi snapshot.
  RTT/2 chỉ là ước lượng; không dùng nó làm thẩm quyền quyết định thắng thua.
- Đo broadcast trước khi quyết định chuyển sang queue; nếu queue, theo dõi độ trễ
  worker và giữ revision để xử lý event đến sai thứ tự.

### 3. Khôi phục kết nối và tối ưu phụ

- Sync khi reconnect, tab trở lại foreground; fallback định kỳ có giới hạn,
  tránh nhiều request chồng nhau. Hiển thị trạng thái mất kết nối rõ ràng.
- Render chỉ khi số giây/class thay đổi, dùng textContent; không cần ghi DOM 10 lần/s
  cho đồng hồ chỉ hiện giây. Đây là tối ưu phụ, chưa phải nguyên nhân lag đã đo.
- Kiểm tra migration để lưu milliseconds nhất quán, cập nhật tests backend hiện có.

## Kiểm chứng sau sửa

Chạy lại các tình huống trên với kỳ vọng đúng, rồi chạy E2E hai người + một người xem:
nước đi nhanh, request lặp, GET đảo thứ tự, mạng chậm 100/300/800 ms, pause/resume,
WebSocket mất/kết nối lại, chuyển tab và timeout sát giới hạn 120 giây.
Ghi request p50/p95, query time, broadcast time, thời gian từ move tới snapshot đối thủ,
sai lệch đồng hồ giữa các client. Chỉ kết luận về bottleneck backend sau các phép đo này.
