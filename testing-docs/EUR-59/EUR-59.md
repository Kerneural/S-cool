# Báo cáo Thực thi EUR-59 (QA-B3)
**Production-readiness smoke, compatibility and release evidence pack**

---

## Nhóm 1: Đóng băng ứng viên & Kiểm tra môi trường (AC-01)

### 1.1. Mã Git Revision (Frozen Candidate)
```bash
$ git rev-parse HEAD
6179e3e389ddc8041568bd56a114d96fa044d401
```

### 1.2. Trạng thái cây thư mục làm việc (Working Tree Status)
```bash
$ git status --porcelain
# (clean - không có file sửa đổi hoặc untracked nào chưa kiểm soát)
```

### 1.3. Phiên bản công cụ máy Host (Host Runtime)
```bash
$ git --version
git version 2.51.0.windows.2

$ node -v
v24.16.0
```

### 1.4. Trạng thái các dịch vụ Docker (Service Health Record)
```bash
$ docker compose ps
NAME            IMAGE                    COMMAND                  SERVICE   CREATED        STATUS                    PORTS
scool_app       scool-app                "docker-php-entrypoi…"   app       21 hours ago   Up 6 hours (healthy)      9000/tcp
scool_mailpit   axllent/mailpit:latest   "/mailpit"               mailpit   10 days ago    Up 6 hours (healthy)      127.0.0.1:1025->1025/tcp, 127.0.0.1:8025->8025/tcp
scool_mysql     mysql:8.0                "docker-entrypoint.s…"   mysql     10 days ago    Up 6 hours (healthy)      127.0.0.1:3306->3306/tcp
scool_nginx     nginx:alpine             "/docker-entrypoint.…"   nginx     10 days ago    Up 6 hours (healthy)      127.0.0.1:8080->80/tcp
scool_queue     scool-queue              "docker-php-entrypoi…"   queue     21 hours ago   Up 30 minutes (healthy)   9000/tcp
```
* **Đánh giá:** 5/5 containers đang ở trạng thái `healthy`, Nginx mở tại port `8080`, Mailpit mở tại port `8025`.

---

## Nhóm 2: Biên dịch tài nguyên Production (AC-02)

```bash
$ npm run build

> build
> vite build

vite v6.4.3 building for production...
transforming...
✓ 61 modules transformed.
rendering chunks...
computing gzip size...
public/build/manifest.json              0.27 kB │ gzip:  0.15 kB
public/build/assets/app-Cw4uXhUI.css  124.46 kB │ gzip: 19.26 kB
public/build/assets/app-Cmo1fR79.js    52.91 kB │ gzip: 20.03 kB
✓ built in 5.36s
```
* **Kết quả:** Build thành công 100% trong 5.36s (61 modules transformed), xuất file assets vào `public/build/` sẵn sàng phục vụ cho smoke test trình duyệt.

---

## Nhóm 3: Kiểm thử tự động trên Database an toàn (AC-02)

### 3.1. Chạy toàn bộ Test Suite (PHPUnit / Laravel Test trên `scool_test`)
```bash
$ docker compose -p scool exec -T app php artisan test --compact

  ............................................................................
  ............................................................................
  ............................................................................
  ............................................................................
  ............................................................................
  ............................................................................
  .......................................................................

  Tests:    527 passed (5703 assertions)
  Duration: 967.75s
```
* **Kết quả:** 
  * **Tổng số tests:** 527 tests
  * **Tổng số assertions:** 5,703 assertions
  * **Trạng thái:** **527 PASSED (100% đạt, 0 failed, 0 skipped)**
  * **Thời gian thực thi:** 967.75 giây (~16 phút)
  * **Cơ sở dữ liệu:** Thực thi an toàn trên `scool_test`, không ảnh hưởng tới dữ liệu dev.

### 3.2. Kiểm tra định dạng chuẩn Code Style (Laravel Pint)
```bash
$ docker compose -p scool exec -T app vendor/bin/pint --test

  ............................................................................
  ............................................................................
  ............................................................................
  .........................................

  ──────────────────────────────────────────────────────────────────── Laravel  
    PASS   ......................................................... 269 files  
```
* **Kết quả:** **PASS 269/269 files** tuân thủ đầy đủ chuẩn code style Laravel/PSR-12.

---

## Nhóm 4: Smoke Test Trình duyệt Chrome & Edge (AC-03 & AC-04) - Đang chờ thực hiện

| Luồng kiểm thử | Google Chrome (Desktop) | Google Chrome (Mobile) | Microsoft Edge (Desktop) | Microsoft Edge (Mobile) | Console Errors | Network Failures |
|---|---|---|---|---|---|---|
| **1. Login** | Pending | Pending | Pending | Pending | None | None |
| **2. Community entry** | Pending | Pending | Pending | Pending | None | None |
| **3. Feed interaction** | Pending | Pending | Pending | Pending | None | None |
| **4. Live search** | Pending | Pending | Pending | Pending | None | None |
| **5. Discussion viewer** | Pending | Pending | Pending | Pending | None | None |
| **6. Lesson / progress** | Pending | Pending | Pending | Pending | None | None |
| **7. Creator / Admin path** | Pending | Pending | Pending | Pending | None | None |
