# Quy tắc Git và Triển khai Production (cotuongchamtop)

## BẮT BUỘC: MỌI CODE DEPLOY LÊN PRODUCTION PHẢI ĐƯỢC GHI VÀO GIT

1. **Không sửa code trực tiếp trên server production.** Mọi code phải đi từ Git repository (`origin/master`).
2. **Commit và Push đầy đủ trước khi Deploy:**
   - Không được deploy khi local còn uncommitted changes.
   - Phải commit rõ ràng và push lên `master` trước khi chạy script deploy.
3. **Chỉ Deploy khi có lệnh trực tiếp từ Người Dùng:**
   - Tuyệt đối không tự động deploy sau khi viết code hay debug.
4. **Kiểm tra sau Deploy (Post-deploy Check):**
   - Phải curl kiểm tra `https://cotuong.top` trả về HTTP 200/301/302.
