# ♟️ AI Development & Deployment Guidelines (Học hỏi từ chuẩn 300apps)

Tài liệu này định nghĩa các quy tắc phát triển, kiểm thử, quản lý phiên bản và triển khai dành cho các AI Agent khi làm việc trên codebase **cotuongchamtop**.

---

## 🛑 1. QUY TẮC BẮT BUỘC VỀ GIT VÀ DEPLOY PRODUCTION (HARD RULE)

> [!CAUTION]
> **CODE NÀO DEPLOY LÊN PRODUCTION THÌ BẮT BUỘC PHẢI ĐƯỢC GHI VÀO GIT:**
> 1. **Tuyệt đối không sửa code "chay" trực tiếp trên server production** mà không thông qua Git.
> 2. **Toàn bộ thay đổi code muốn đưa lên server production PHẢI được commit vào Git** trên local với commit message rõ ràng, chuẩn ngữ nghĩa.
> 3. **Trước khi thực thi deploy**: Thư mục làm việc Git local (`working tree`) phải hoàn toàn sạch sẽ (`clean`), không có uncommitted changes hay unstaged modifications. Đã `git push origin master` thành công.
> 4. Nếu phát hiện còn thay đổi chưa commit vào Git khi nhận lệnh deploy -> **HỆ THỐNG PHẢI TỰ ĐỘNG CHẶN (HARD STOP)**, thông báo danh sách file chưa commit và yêu cầu hoàn tất commit vào Git trước.

---

## 🛡️ 2. PREFLIGHT & CHẤT LƯỢNG CODE TRƯỚC KHI COMMIT / DEPLOY (QUALITY GATES)

Học hỏi từ quy trình **Automated Preflight Quality Gate** của 300apps:

1. **Kiểm tra cú pháp (Syntax Preflight)**:
   - Các file PHP chỉnh sửa phải được kiểm tra cú pháp không có lỗi biên dịch (`php -l <file>`).
   - Kiểm tra không để sót các hàm debug chết người: `dd()`, `dump()`, `var_dump()`, `die;`, hoặc `console.log` spam trong code production.
2. **Build Assets Frontend**:
   - Nếu có can thiệp vào tài nguyên frontend (SCSS, Blade, JS trong `resources/`): bắt buộc phải chạy build asset (`npm run build` hoặc `npm run prod` tương ứng) và kiểm tra output trước khi commit.
3. **Bảo toàn đa ngôn ngữ**:
   - Khi thêm mới bất kỳ chuỗi hiển thị nào, phải dùng helper `__('key')` và cập nhật từ điển trong `resources/lang/*.json` cho các ngôn ngữ được hỗ trợ.

---

## 🚀 3. QUY TRÌNH DEPLOY AN TOÀN (DEPLOYMENT PROTOCOL)

1. **Quyền hạn Deploy**:
   - **TUYỆT ĐỐI KHÔNG TỰ Ý DEPLOY**. Chỉ thực hiện khi người dùng ra lệnh trực tiếp (ví dụ: *"deploy đi"*, *"deploy lên server"*, *"triển khai code"*).
2. **Các bước tuần tự**:
   - **Bước 1**: Kiểm tra Git local: `git status --porcelain`. Nếu còn file chưa commit -> Dừng ngay.
   - **Bước 2**: Đảm bảo local đã push lên remote: `git push origin master`.
   - **Bước 3**: Kích hoạt deploy trên server thông qua SSH (`./.agents/skills/deploy-cotuong/scripts/deploy.sh` hoặc SSH kéo `git pull origin master`).
   - **Bước 4**: Chạy migration (nếu có DB change), xóa và tối ưu cache (`php artisan optimize:clear && php artisan optimize`).
   - **Bước 5**: Đảm bảo pool AI Engine và Reverb WebSocket còn sống (`php artisan xiangqi:pool:ensure`).
   - **Bước 6**: **Post-Deploy Verification**: Kiểm tra HTTP response thực tế (`curl -I -k https://cotuong.top`) để chắc chắn web phản hồi `200 OK`.

---

## 🧠 4. VÒNG LẶP TỰ HỌC & RÚT KINH NGHIỆM (SELF-LEARNING LOOP)

Kế thừa nguyên tắc phát triển từ **300apps**:
1. Khi gặp sự cố kỹ thuật, bug phức tạp hoặc lỗi cấu hình hệ thống:
   - Xác định nguyên nhân gốc rễ (Root Cause Analysis).
   - Đúc kết thành bài học kinh nghiệm.
   - Tự động ghi nhận và cập nhật vào `AGENTS.md` hoặc các file skill tương ứng trong `.agents/skills/` để các lượt làm việc tiếp theo không bao giờ lặp lại lỗi cũ.
