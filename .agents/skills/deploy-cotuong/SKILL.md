---
name: deploy-cotuong
description: >-
  Quy trình deploy mã nguồn dự án cotuongchamtop lên server production (cotuong.top).
  QUAN TRỌNG: TUYỆT ĐỐI KHÔNG tự động deploy. CHỈ thực hiện deploy khi người dùng có lệnh rõ ràng (ví dụ: "deploy đi", "deploy lên server", "triển khai code").
---

# Deploy Cờ Tướng Production (cotuong.top)

Kỹ năng này hướng dẫn quy trình deploy an toàn mã nguồn từ repo local lên máy chủ production `cotuong.top`.

> [!CAUTION]
> **QUY TẮC BẮT BUỘC:**
> - **CODE NÀO DEPLOY LÊN PRODUCTION BẮT BUỘC PHẢI ĐƯỢC GHI VÀO GIT**: Toàn bộ thay đổi phải được commit sạch sẽ và push lên `master` trước khi deploy. Tuyệt đối không deploy khi còn uncommitted changes hoặc sửa code trực tiếp trên server.
> - **KHÔNG ĐƯỢC** tự động deploy sau khi viết code, sửa lỗi hoặc kiểm thử xong.
> - **CHỈ THỰC HIỆN** khi người dùng ra lệnh trực tiếp (ví dụ: *"deploy đi"*, *"deploy lên server"*, *"triển khai code"*).

---

## 1. Thông tin hạ tầng Server
- **Host Alias:** `cotuong` (được cấu hình qua jump host trong `~/.ssh/config`)
- **Server IP:** `45.251.112.84` (hostname: `mail.cungrao.net`)
- **Đường dẫn web root trên server:** `/home/cotuong/domains/cotuong.top/public_html`
- **Khóa SSH:** `~/.ssh/hoanganh.pem`

---

## 2. Các bước chuẩn bị trước khi Deploy

1. **Kiểm tra trạng thái Git local:**
   - Đảm bảo các thay đổi đã được commit đầy đủ.
   - Đảm bảo đã push lên remote GitHub:
     ```bash
     git push origin master
     ```
2. **Build frontend assets (nếu có chỉnh sửa CSS/JS):**
   - Nếu có chỉnh sửa trong thư mục `resources/`, chạy build asset trước:
     ```bash
     npm run build
     ```
     Sau đó commit và push các file build trong `public/build` (nếu cần).

---

## 3. Thực thi Deploy

### Cách 1: Sử dụng script tự động (Khuyên dùng)
Chạy script helper đã được cấu hình sẵn:
```bash
./.agents/skills/deploy-cotuong/scripts/deploy.sh
```

### Cách 2: Chạy trực tiếp qua SSH
```bash
ssh cotuong "cd /home/cotuong/domains/cotuong.top/public_html && git pull origin master && php artisan migrate --force && php artisan optimize:clear && php artisan optimize"
```

Nếu có cập nhật liên quan đến Engine AI Pikafish hoặc WebSocket Reverb:
```bash
ssh cotuong "cd /home/cotuong/domains/cotuong.top/public_html && php artisan xiangqi:pool:ensure"
```

---

## 4. Xác minh sau Deploy (Verification)
Sau khi deploy xong, kiểm tra HTTP response của website để đảm bảo web hoạt động bình thường:
```bash
curl -I -k https://cotuong.top
```
Kỳ vọng kết quả trả về `HTTP/2 200` (hoặc `301`/`302`).
