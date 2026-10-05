# Working context — S-cool

> Checkpoint tiếp tục công việc, không thay thế tiến độ trên Linear. Cập nhật khi save/handoff/chuyển issue; xác minh lại Git và code khi resume.

## Issue hiện tại

- Updated: 05/10/2026.
- Milestone: M1 — Foundation ready, target 03/10/2026 - 08/10/2026.
- Parent: EUR-5 — Identity & Local Bootstrap.
- Issue: [EUR-17 — Frontend scaffolding with Blade, Alpine, Tailwind, Vite](https://linear.app/eurusdevsec/issue/EUR-17/frontend-scaffolding-with-blade-alpine-tailwind-vite).
- Owner: Hoàng; reviewer: Tiến; Auditor: Codex.
- Checkpoint thực tế: Đã tận dụng frontend scaffold có sẵn từ Laravel Breeze, không cài lại hoặc redesign UI. Đã hoàn tất kiểm chứng 5/5 Acceptance Criteria (AC), bổ sung Feature tests tự động cho frontend layout và tương tác Alpine.
- Git HEAD khi bắt đầu EUR-17: `56ca500`; verify on resume.
- Stack runtime Docker: 4 services đang chạy ổn định (`scool_app` PHP 8.2-FPM, `scool_nginx` port 8080, `scool_mysql` port 3306, `scool_mailpit` port 8025/1025). Healthcheck `/up` trả về HTTP 200.
- Database: MySQL 8.0 (`scool` cho dev data và `scool_test` cho isolated test suite).
- Frontend assets: Vite production build sẵn sàng trong `public/build` (manifest, CSS 53.43 kB, JS 107.37 kB).
- Tests & Code style:
  - `docker compose exec app php artisan test`: **PASS 42/42 tests (148 assertions)** trên `scool_test`.
  - `docker compose exec app ./vendor/bin/pint --test`: **PASS 51 files** chuẩn PSR-12 / Laravel style.
  - `npm run build`: Exit code 0, build thành công 59 modules trong 11.77s.
- Boundaries: Tuân thủ nghiêm ngặt (không cài đặt thêm package ngoài scope, không đổi thiết kế Blade/Alpine sang framework SPA).

---

## Bằng chứng kiểm chứng chi tiết theo từng AC (Acceptance Criteria)

### AC 1: Blade layout render đúng
- **Mục tiêu:** Layout khách (`layouts.guest`) và layout ứng dụng (`layouts.app`) render đúng cấu trúc semantic HTML, meta CSRF token, thẻ tiêu đề trang và nhúng đúng assets Vite.
- **Lệnh thực thi & Kiểm chứng:**
  - Viết bài test tự động: `tests/Feature/FrontendScaffoldTest.php` kiểm tra:
    + `test_guest_layout_renders_correctly_with_vite_production_assets`: Kiểm tra trang `/login` trả về HTTP 200, có thẻ `<meta name="csrf-token">`, `<title>S-cool</title>`, container căn giữa Tailwind, thẻ form, input email/password, và link production assets `/build/assets/app-*`.
    + `test_app_layout_renders_correctly_with_navigation_and_header`: Đăng nhập user và kiểm tra `/dashboard` trả về HTTP 200, có thanh navigation, header slot, main slot, tên người dùng đăng nhập, và assets Vite.
  - Lệnh chạy: `docker compose exec app php artisan test tests/Feature/FrontendScaffoldTest.php`
  - **Kết quả:** PASS (4 tests, 32 assertions).

### AC 2: Alpine có tương tác thực tế hoạt động
- **Mục tiêu:** Alpine.js được nạp vào browser (`Alpine.start()`), khởi tạo thành công và xử lý được các tương tác thực tế: mở/đóng dropdown menu, click outside để đóng menu, và mở/đóng modal component.
- **Kiểm chứng qua Chrome DevTools & Real Browser DOM Execution:**
  - Khởi tạo session browser tới `http://127.0.0.1:8080/login`, đăng nhập bằng tài khoản dev `devops-demo@scool.local`.
  - Kiểm tra môi trường runtime:
    ```json
    { "hasAlpine": true, "alpineVersion": "3.17.4" }
    ```
  - **Kiểm chứng Dropdown Navigation:**
    1. Trạng thái ban đầu: `panelDisplay: "none"`, `panelVisible: false`.
    2. Click vào trigger button (chứa tên "Hoang DevOps"): `panelDisplay: "block"`, `panelVisible: true`, hiển thị danh sách liên kết `["Profile", "Log Out"]`.
    3. Click ra ngoài vùng dropdown (`@click.outside="open = false"`): `panelDisplay: "none"`, `panelVisible: false`.
    - Kết quả: `success: true`.
  - **Kiểm chứng Alpine Modal Component (`/profile`):**
    1. Trạng thái ban đầu: `computedDisplay: "none"`.
    2. Dispatch event `open-modal` (hoặc click nút "Delete Account"): `computedDisplay: "block"`, modal hiện lên với tiêu đề "Are you sure you want to delete your account?".
    3. Dispatch event `close-modal` (hoặc click nút "Cancel"): `computedDisplay: "none"`.
    - Kết quả: `success: true`.

### AC 3: Tailwind compile và hiển thị đúng
- **Mục tiêu:** Tailwind CSS biên dịch các utility classes được sử dụng trong Blade views thành CSS bundle tối ưu trong `public/build/assets/`.
- **Lệnh thực thi & Kiểm chứng:**
  - Kiểm tra file bundle `public/build/assets/app-B4kXAv0R.css` (kích thước 53.43 kB):
    + Chứa đầy đủ các lớp tiện ích được dùng: `.min-h-screen`, `.bg-gray-100`, `.max-w-7xl`, `.shadow-md`, `.flex`, `.text-gray-900`, `.font-sans`...
  - Nginx phục vụ file CSS với đúng MIME type:
    ```
    HTTP/1.1 200 OK
    Content-Type: text/css
    Content-Length: 53434
    ```
  - Giao diện render trên browser hiển thị chuẩn typography (Figtree), màu sắc Tailwind, căn giữa responsive và đổ bóng đẹp mắt (đã chụp screenshot kiểm chứng).

### AC 4: Vite dev chạy được, production build đạt
- **Mục tiêu:** Lệnh build production hoàn thành không lỗi; lệnh dev server khởi động được và tạo cờ hot reload.
- **Lệnh thực thi & Bằng chứng:**
  - **Production Build:**
    ```powershell
    npm run build
    ```
    - Output:
      ```
      vite v6.4.3 building for production...
      ✓ 59 modules transformed.
      public/build/manifest.json              0.27 kB │ gzip:  0.15 kB
      public/build/assets/app-B4kXAv0R.css   53.43 kB │ gzip:  9.11 kB
      public/build/assets/app-D99hXOC0.js   107.37 kB │ gzip: 38.86 kB
      ✓ built in 11.77s
      ```
      Exit code: 0.
  - **Vite Dev Server:**
    ```powershell
    npm run dev
    ```
    - Khởi động thành công trong 350ms tại `http://localhost:5173/`.
    - Tự động tạo file `public/hot` chứa `http://[::1]:5173` để Laravel chuyển sang chế độ Hot Module Replacement (HMR).
    - Sau khi dừng dev server, file `public/hot` được dọn dẹp sạch sẽ, port 5173 giải phóng.

### AC 5: Bản production build chạy qua Nginx không cần Vite dev server
- **Mục tiêu:** Ứng dụng chạy hoàn toàn độc lập qua Nginx (port 8080) với các static assets đã được build sẵn, không cần tiến trình Node.js / Vite dev server nào chạy ngầm.
- **Lệnh thực thi & Bằng chứng:**
  - Kiểm tra port 5173: `Get-NetTCPConnection -LocalPort 5173` trả về null (không có process nào lắng nghe).
  - Kiểm tra file `public/hot`: Đã bị xóa (chế độ production thuần túy).
  - Gửi request đến Nginx:
    + `curl.exe -i http://127.0.0.1:8080/login`: Trả về HTTP 200 OK, nhúng trực tiếp thẻ `<link rel="stylesheet" href="http://127.0.0.1:8080/build/assets/app-B4kXAv0R.css">` và `<script type="module" src="http://127.0.0.1:8080/build/assets/app-D99hXOC0.js">`.
    + `curl.exe -I http://127.0.0.1:8080/build/assets/app-B4kXAv0R.css`: Trả về `HTTP/1.1 200 OK`, `Content-Type: text/css`.
    + `curl.exe -I http://127.0.0.1:8080/build/assets/app-D99hXOC0.js`: Trả về `HTTP/1.1 200 OK`, `Content-Type: application/javascript`.

---

## Evidence và next action

- **Test Suite:** Toàn bộ 42 tests (148 assertions) PASS trên `scool_test`.
- **Code Style:** Pint PASS 51 files.
- **Git status:** Các thay đổi cho EUR-17 chỉ bao gồm thêm test frontend `tests/Feature/FrontendScaffoldTest.php` và cập nhật `docs/WORKING_CONTEXT.md`. Không sửa đổi cấu trúc UI scaffold có sẵn, không refactor ngoài scope.
- **Bàn giao:** Sẵn sàng để Owner (Hoàng) chuyển cho **Codex** tiến hành Audit chi tiết cho EUR-17. Tuyệt đối không tự tiện commit/push hoặc chuyển trạng thái Done theo đúng `AGENTS.md`.
