# Working context — S-cool

> Checkpoint tiếp tục công việc, không thay thế tiến độ trên Linear. Cập nhật khi save/handoff/chuyển issue; xác minh lại Git và code khi resume.

## Issue hiện tại

- Updated: 03/10/2026.
- Milestone: M1 — Foundation ready, target 03/10/2026.
- Parent: EUR-5 — Identity & Local Bootstrap.
- Issue: [EUR-16 — Bootstrap Laravel and authentication](https://linear.app/eurusdevsec/issue/EUR-16/bootstrap-laravel-and-authentication).
- Owner: Hoàng; reviewer: Tiến; Auditor: Codex.
- Checkpoint thực tế: Codex đã sửa hai findings còn lại (guard trước migration; SMTP test không xóa hộp thư dùng chung), chạy lại full suite đạt. Chờ Hoàng teach-back và nghiệm thu; chưa commit/chuyển Done.
- Git HEAD khi re-audit: `56ca500`; verify on resume.
- Stack runtime Docker: 4 services đang chạy ổn định (`scool_app` PHP 8.2, `scool_nginx` port 8080, `scool_mysql` port 3306, `scool_mailpit` port 8025/1025). Healthcheck `/up` trả về HTTP 200.
- Database: MySQL 8.0 với 2 databases độc lập: `scool` (dev/demo data) và `scool_test` (isolated test suite).
- Frontend assets: Vite production build hoàn tất (`public/build`).
- Tests/runtime:
  - `docker compose exec -T app php artisan test`: **PASS 38/38 tests (116 assertions)** trên CSDL độc lập `scool_test`, có live SMTP test, không skipped.
  - `docker compose exec -T app ./vendor/bin/pint --test`: **PASS 50 files**.
  - `docker compose exec app composer audit`: **PASS 0 vulnerabilities found** (đã giải quyết sạch 4 CVE).
- Boundaries: Tuân thủ nghiêm ngặt (không có social login/MFA, không thêm Community/Payment).

## Kế hoạch kỹ thuật & Khắc phục Findings từ Codex Audit

### 1. [P1] Cô lập Database Test & Safety Guard chống mất dữ liệu
- **Vấn đề:** Tests dùng trait `RefreshDatabase` gọi `migrate:fresh`, trước đó chạy trực tiếp vào CSDL phát triển `scool`.
- **Giải pháp:**
  - Cấu hình `phpunit.xml` ép buộc `DB_CONNECTION=mysql` và `DB_DATABASE=scool_test`.
  - Thêm `docker/mysql/init.sql` tạo tự động `scool_test` và mount vào `/docker-entrypoint-initdb.d/init.sql:ro`.
  - Guard trong `tests/TestCase.php::setUpTraits()` chạy sau boot application nhưng trước setup traits/migration. Chỉ cho phép MySQL `scool_test`; kiểm tra default và các connection transaction, resolve cả database URL.
  - Guard cũ sau `parent::setUp()` không bảo vệ được migration; thông báo exception cũ không chứng minh dữ liệu an toàn.
  - Regression tests dùng refresh sentinel thay migration thật để chứng minh DB dev/không được phép và URL override bị chặn trước refresh; DB test hợp lệ mới đi tiếp. Không chạy thử destructive test vào `scool`.

### 2. [P1] Nâng cấp Framework giải quyết Security Support EOL
- **Vấn đề:** Laravel 11.57.0 EOL security vào 12/03/2026 (sau mốc thời gian hệ thống 10/2026), `composer audit` có 4 CVE advisories (XSS, CRLF injection).
- **Giải pháp:**
  - Nâng cấp `laravel/framework` lên phiên bản `^12.0` (cụ thể `v12.69.3`), tương thích hoàn toàn PHP 8.2 và Breeze Blade/Alpine.
  - `composer audit` trả về: `No security vulnerability advisories found.`

### 3. [P2] Đồng bộ cấu hình bàn giao (.env.example)
- **Vấn đề:** `.env.example` trỏ về sqlite/mail log và thiếu port 8080 trong `APP_URL`.
- **Giải pháp:**
  - Cập nhật `.env.example` với `APP_URL=http://localhost:8080`, `DB_CONNECTION=mysql`, `DB_HOST=mysql`, `DB_PORT=3306`, `DB_DATABASE=scool`, `MAIL_MAILER=smtp`, `MAIL_HOST=mailpit`, `MAIL_PORT=1025`, `MAIL_FROM_ADDRESS="no-reply@scool.local"`.
  - Sửa lỗi placeholder `[EMAIL_ADDRESS]` vi phạm RFC 2822 trong `.env` thành `"no-reply@scool.local"`.

### 4. [P2] Chứng minh AC Reset Email qua Mailpit SMTP & Kiểm thử toàn diện
- **Vấn đề:** Chưa có bằng chứng gửi SMTP thật tới Mailpit; thiếu validation âm, login lockout, token sai/tái sử dụng.
- **Giải pháp:**
  - `tests/Feature/Auth/MailpitPasswordResetIntegrationTest.php`: SMTP thật đến Mailpit, recipient UUID riêng từng run; tìm email qua API search, không xóa email dùng chung. Parse token, gọi HTTP feature test reset, đổi mật khẩu, kiểm tra mật khẩu cũ bị từ chối/mới được chấp nhận. Đây là application integration test, không phải browser E2E qua Nginx.
  - Thêm negative tests trong `AuthenticationTest.php`: Kiểm tra thiếu trường, sai định dạng email, và **Login Lockout rate-limiting sau 5 lần thử sai** (trigger event `Lockout`).
  - Thêm negative tests trong `PasswordResetTest.php`: Validate email format, token giả mạo/sai, password confirmation không khớp, password quá ngắn, từ chối mật khẩu cũ sau reset, và **chống tái sử dụng token (token reuse prevention)**.

## Evidence và next action

- **Test Suite:** Codex chạy lại 38 passed (116 assertions) trên `scool_test`.
- **Code Style:** Pint PASS 50 files.
- **Security Audit:** 0 advisories (`composer audit` clean).
- **Mailpit Integration:** Application integration test với SMTP thật đã PASS; không thay thế browser E2E qua Nginx.
- **Mailbox preservation:** Codex chạy riêng SMTP test lần nữa: PASS 1 test/15 assertions; hộp thư tăng 2 → 3 email, không mất ID email cũ nào. Không xóa email để cleanup; test email synthetic còn trong Mailpit.
- **Git status:** Giữ nguyên các thay đổi bootstrap của Antigravity; Codex chỉ sửa test harness, SMTP test, thêm guard regression và cập nhật checkpoint. Chưa commit/push hoặc thay đổi Linear.
- **Next action:** Hoàng teach-back guard lifecycle và isolation; nghiệm thu rồi mới commit/cập nhật Linear. Fresh-clone setup và toàn bộ M1 không được coi là hoàn thành từ kết quả EUR-16.

Đọc tiếp: `AI_WORKFLOW.md`, `docs/DEVOPS_CHEATSHEET.md`, `02_DOMAIN_ARCHITECTURE.md`.
