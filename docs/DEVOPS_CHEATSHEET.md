# S-COOL — DEVOPS & TROUBLESHOOTING CHEATSHEET
> **Sổ tay thực chiến DevOps dành cho Hoàng (Team Lead / DevOps)**  
> *Tài liệu sống: Cập nhật liên tục các lệnh vận hành, quy trình xử lý sự cố (Troubleshooting) và bài học kỹ thuật thực tế.*  
> *Khởi tạo: 03/10/2026 — Milestone M1 Foundation (Cập nhật EUR-18)*

Đây là quick reference; quyết định vận hành chuẩn nằm trong `04_DEVOPS_OPERATIONS.md`.
Setup thực tế từ clone: xem `../README.md`. Không dùng tài liệu này để khẳng định production readiness.

---

## 🎯 BẢNG LỆNH CỐT LÕI HOÀNG CẦN LUYỆN TẬP (DAILY DRILLS)

> 💡 **Nguyên tắc học DevOps thực chiến:** Tập trung vào **20% câu lệnh cốt lõi mang lại 80% hiệu quả vận hành hàng ngày**. Những lệnh dưới đây Hoàng nên tự tay gõ nhiều lần để hình thành phản xạ tự nhiên (muscle memory).  
> Những cấu hình sâu về buffer Nginx, mã JSON healthcheck hay kiến trúc ảo hóa chỉ lưu lại để **tra cứu khi có sự cố**, **KHÔNG NHẤT THIẾT PHẢI HỌC THUỘC**.

| Nhóm kỹ năng | Câu lệnh Hoàng cần luyện gõ | Ý nghĩa & Thời điểm dùng |
| :--- | :--- | :--- |
| **1. Khởi động & Kiểm tra cụm** | `docker compose up -d`<br>`docker compose ps` | Đầu ngày làm việc: Bật cụm và kiểm tra xem cả 5 container đều `Up (healthy)`. |
| **2. Bắt bệnh qua Log (Troubleshooting)** | `docker compose logs -f nginx`<br>`docker compose logs --tail=30 app`<br>`docker compose logs -f queue` | Khi web đơ, lag hoặc báo lỗi: Xem trực tiếp log web, log PHP, hoặc log worker. |
| **3. Vận hành Queue Worker** | `docker compose restart queue`<br>`docker compose logs --tail=20 queue` | **Rất quan trọng:** Mỗi khi sửa code trong `app/Jobs/`, bắt buộc phải restart worker thì code mới mới có hiệu lực! |
| **4. Kiểm soát chất lượng (Quality Gate)** | `docker compose exec app php artisan test`<br>`docker compose exec app ./vendor/bin/pint --test` | Trước khi bàn giao task hoặc gửi PR: Chạy test và kiểm tra code style. |
| **5. Thao tác CSDL & Dọn dẹp cache** | `docker compose exec app php artisan migrate`<br>`docker compose exec app php artisan optimize:clear` | Khi pull code mới có file migration hoặc khi sửa file cấu hình `.env`. |
| **6. Tắt hệ thống an toàn** | `docker compose down` | Cuối ngày làm việc: Tắt cụm sạch sẽ. (⚠️ **Tuyệt đối không gõ `-v`** để tránh mất dữ liệu). |

---

## 1. QUẢN LÝ VÒNG ĐỜI DOCKER (DOCKER LIFECYCLE)

| Mục đích | Câu lệnh Bash | Ý nghĩa kỹ thuật |
| :--- | :--- | :--- |
| **Khởi động toàn bộ cụm** | `docker compose up -d` | Bật cả 5 container (`app`, `nginx`, `mysql`, `mailpit`, `queue`) chạy ngầm (`-d` = detached mode). |
| **Kiểm tra trạng thái** | `docker compose ps` | Xem container nào đang `Up (healthy)`, `Exit` (sập), và các cổng mạng (ports) đang mở. |
| **Build lại image khi sửa Dockerfile** | `docker compose build app queue` | Hai service build cùng Dockerfile; recreate bằng `docker compose up -d --wait` để dùng image mới. |
| **Dừng toàn bộ hệ thống** | `docker compose down` | Dừng và xóa container, giữ nguyên volume CSDL (an toàn). |
| **Dừng và XÓA SẠCH dữ liệu CSDL** | `docker compose down -v` | ⚠️ **CỰC KỲ NGUY HIỂM**: Cờ `-v` sẽ xóa sạch cả Named Volume của MySQL. Chỉ dùng khi muốn reset trắng tinh từ đầu. |
| **Khởi động lại một service cụ thể** | `docker compose restart nginx` | Khởi động lại riêng Nginx khi sửa file cấu hình `default.conf` mà không làm tắt các container khác. |

---

## 2. [BẮT BUỘC LUYỆN] KIỂM THỬ TỰ ĐỘNG & CHUẨN HÓA CODE (TESTING & QUALITY GATES)

Đây là các lệnh kiểm tra local và là mục tiêu cho GitHub Actions; chưa có CI evidence trong EUR-18.

### 2.1. Chạy Automated Tests (PHPUnit / Pest)
```bash
# Chạy toàn bộ các bài test trong dự án (Lệnh cần thuộc)
docker compose exec app php artisan test

# Chạy riêng 1 file test cụ thể (rất hữu ích khi đang code dở một tính năng)
docker compose exec app php artisan test tests/Feature/Auth/AuthenticationTest.php

# Chạy test và lọc theo tên hàm
docker compose exec app php artisan test --filter=users_can_authenticate
```
🔍 **Mẹo đọc kết quả:**
- `Unit Test`: Kiểm tra logic hàm thuần túy, không chạm CSDL, chạy cực nhanh (tính bằng mili-giây).
- `Feature Test`: Giả lập request HTTP thực tế, đi qua Route $\rightarrow$ Middleware $\rightarrow$ Controller $\rightarrow$ CSDL $\rightarrow$ trả về View/JSON.
- `Assertions`: Số điều kiện kiểm tra logic bắt buộc phải đúng (Status 200, redirect đúng trang, session tồn tại...).

### 2.2. Kiểm tra & Định dạng Code Style (Laravel Pint / PSR-12)
```bash
# Chế độ kiểm tra (Dry-run / CI mode): Báo lỗi nếu code viết ẩu, KHÔNG tự sửa
docker compose exec app ./vendor/bin/pint --test

# Chế độ tự động sửa (Format mode): Tự động sửa lại code cho đẹp và chuẩn
docker compose exec app ./vendor/bin/pint
```
Trong CI dùng `--test` để trả exit code lỗi khi style không đạt. GitHub chỉ chặn merge khi check đó được cấu hình required trong branch protection/ruleset.

---

## 3. [BẮT BUỘC LUYỆN] KỸ NĂNG VÀNG: TROUBLESHOOTING & ĐỌC LOG KHI SẬP HỆ THỐNG

Một DevOps giỏi được định giá bằng **tốc độ tìm ra nguyên nhân gốc rễ (Root Cause)** khi hệ thống báo lỗi. Tuyệt đối không đoán mò, hãy đọc log theo thứ tự sau:

### 3.1. Các lệnh đọc Log thời gian thực
```bash
# Xem 30 dòng log mới nhất của Nginx
docker compose logs --tail=30 nginx

# Bật chế độ "theo dõi log sống" của Nginx (bấm F5 trên web là log nhảy theo)
docker compose logs -f nginx

# Xem log của container PHP-FPM (xem lỗi crash hoặc tiến trình xử lý request)
docker compose logs --tail=30 app

# Xem log của Queue Worker (xem tiến trình xử lý job nền)
docker compose logs -f queue

# Xem log của MySQL (xem CSDL có khởi động được không, có bị khóa bảng không)
docker compose logs --tail=30 mysql
```

### 3.2. Truy tìm log chi tiết bên trong Laravel
- **Vị trí file log:** `storage/logs/laravel.log`
- Khi người dùng gặp lỗi `500 Server Error`, mọi stack trace, câu lệnh SQL bị chết, và dòng code gây lỗi đều được ghi lại chi tiết trong file này.

### 3.3. "Chui vào trong" Container để kiểm tra (Interactive Shell)
Khi cần gõ lệnh trực tiếp trong môi trường Linux của container:
```bash
# Chui vào container app (PHP-FPM)
docker compose exec app sh

# Kiểm tra thử mạng từ bên trong container
ping mysql
nc -zv mysql 3306
```

---

## 4. [TRA CỨU KHI GẶP SỰ CỐ - KHÔNG CẦN HỌC THUỘC] TỔNG HỢP CÁC BÀI HỌC SỰ CỐ THỰC TẾ (POST-MORTEMS)

### 📌 Bài học 1: Lỗi HTTP 500 do thiếu bảng `sessions` trong CSDL
- **Hiện tượng:** Mới dựng web xong, mở lên bị lỗi HTTP 500 ngay lập tức.
- **Cách chẩn đoán:** Đọc file `storage/logs/laravel.log`, thấy dòng:  
  `SQLSTATE[42S02]: Table 'scool.sessions' doesn't exist`.
- **Nguyên nhân gốc:** File `.env` cấu hình `SESSION_DRIVER=database`, nghĩa là Laravel muốn lưu phiên đăng nhập của người dùng vào bảng `sessions` trong MySQL, nhưng ta chưa chạy migration tạo bảng.
- **Cách khắc phục:** 
  ```bash
  docker compose exec app php artisan migrate
  ```

---

### 📌 Bài học 2: Nghẽn tốc độ (Latency Bottleneck) giữa Windows NTFS và Docker WSL2
- **Hiện tượng:** Mở web trên Windows load mất 3 – 5 giây cho mỗi trang.
- **Giả thuyết cần đo, chưa có profiling chứng minh nguyên nhân:**
  1. *I/O Bridge Latency:* Dự án nằm trên ổ cứng Windows (`R:\...`), Docker chạy trên Linux WSL2. Mỗi request PHP phải đọc hơn 800 file qua cầu nối ảo hóa 9P/VirtioFS.
  2. *IPv6 DNS Stall:* Gõ `localhost` trên Windows bị khựng lại 1-2s vì thử kết nối IPv6 (`::1`) trước khi rơi về IPv4.
  3. *Nginx FastCGI Buffering:* HTML trả về lớn hơn buffer mặc định khiến Nginx phải ghi tạm ra ổ cứng máy ảo.
- **Giải pháp tối ưu hóa:**
  - Truy cập bằng IP trực tiếp: **`http://127.0.0.1:8080`** (bỏ qua bước phân giải tên miền chậm chạp).
  - Tối ưu bộ đệm trong `docker/nginx/default.conf`:
    ```nginx
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
    fastcgi_busy_buffers_size 256k;
    ```
  - `listen [::]:80;` chỉ thêm listener bên trong container; host mapping hiện chỉ là IPv4 `127.0.0.1`, không chứng minh đã sửa độ trễ IPv6.

---

### 📌 Bài học 3: Bảo mật Git & Vệ sinh Bí mật (Secrets Hygiene)
- **Quy tắc bất biến:** Tuyệt đối không bao giờ để file **`.env`**, thư mục **`vendor/`** (PHP) hoặc **`node_modules/`** (JS) lọt vào Git commit.
- **Cách kiểm tra an toàn:** Luôn chạy `git status -s` trước khi commit. Nếu thấy có tên file `.env` xuất hiện, phải lập tức kiểm tra lại `.gitignore`.

---

### 📌 Bài học 4: Thảm họa mất dữ liệu do thiếu Database Isolation trong Test
- **Hiện tượng & Rủi ro:** Khi chạy `php artisan test`, các file test sử dụng trait `RefreshDatabase` sẽ tự động thực thi `migrate:fresh` (xóa sạch toàn bộ bảng và dữ liệu để tạo mới từ đầu). Nếu không chỉ định CSDL test riêng trong `phpunit.xml`, bài test sẽ **XÓA TRẮNG DỮ LIỆU ĐANG DÙNG ĐỂ DEMO HOẶC PHÁT TRIỂN** (`scool`)!
- **Giải pháp chuẩn DevOps:**
  1. Luôn khai báo database độc lập `scool_test` trong `phpunit.xml`.
  2. Tạo script `docker/mysql/init.sql` để MySQL tự động tạo database `scool_test` khi khởi tạo cụm.
  3. Cài đặt **Safety Guard** ngay trong `tests/TestCase.php`: Nếu phát hiện kết nối đang trỏ vào database dev `scool`, lập tức ném Exception và hủy toàn bộ tiến trình test.

---

### 📌 Bài học 5: Quản trị lỗ hổng bảo mật Package & Vòng đời Framework (Supply Chain Security)
- **Vấn đề:** Các framework và thư viện mã nguồn mở có vòng đời hỗ trợ (End of Life - EOL). Khi hết hạn hỗ trợ bảo mật, các lỗ hổng (CVE) mới được phát hiện sẽ không còn bản vá.
- **Thực hành DevOps:**
  - Định kỳ chạy lệnh quét lỗ hổng bảo mật:
    ```bash
    docker compose exec app composer audit
    ```
  - Khi phát hiện cảnh báo EOL hoặc CVE (như XSS, CRLF injection), tiến hành phân tích độ tương thích và nâng cấp framework lên phiên bản LTS/hỗ trợ mới nhất (ví dụ: nâng lên Laravel 12.69+ sạch hoàn toàn 0 lỗ hổng).

---

### 📌 Bài học 6: Sự cố chuẩn RFC 2822 khi gửi Email thật qua SMTP
- **Hiện tượng:** Khi chuyển từ môi trường giả lập (array/log mailer) sang SMTP thật với Mailpit, hệ thống báo lỗi HTTP 500 hoặc test fail timeout 30 giây:
  `Symfony\Component\Mime\Exception\RfcComplianceException: Email "[EMAIL_ADDRESS]" does not comply with addr-spec of RFC 2822`.
- **Nguyên nhân:** File `.env` chứa chuỗi placeholder `[EMAIL_ADDRESS]`. Bộ parser RFC 2822 của thư viện Mime từ chối địa chỉ không đúng định dạng chuẩn (`user@domain`).
- **Khắc phục:** Luôn cấu hình địa chỉ người gửi hợp lệ: `MAIL_FROM_ADDRESS="no-reply@scool.local"`.
- **Kiểm tra hộp thư Mailpit qua REST API bằng Bash:**
  ```bash
  # Xem danh sách email đã nhận trong hộp thư Mailpit
  curl http://localhost:8025/api/v1/messages
  ```

---

## 5. QUẢN LÝ QUEUE WORKER & CONTAINER HEALTHCHECKS (EUR-18)

### 5.1. Vì sao tách riêng Queue Worker thành container độc lập?
- **Nguyên lý Separation of Concerns (Tách biệt trách nhiệm):**
  + Container `scool_app` chạy PHP-FPM: Phục vụ các HTTP request ngắn hạn từ người dùng (Web/API) dưới 1–2 giây rồi giải phóng worker.
  + Container `scool_queue` chạy PHP CLI `artisan queue:work database`: Tiến trình nền xử lý email/side effect nhỏ. Không có nén video trong MVP; cập nhật payment/membership từ IPN vẫn đồng bộ trong transaction, không đưa vào queue.
  + Nếu gộp chung hoặc để web request làm việc nặng, server sẽ cạn FPM worker pool $\rightarrow$ người dùng bị treo web và nhận lỗi `504 Gateway Timeout`.

### 5.2. [BẮT BUỘC LUYỆN] Các lệnh DevOps giám sát và vận hành Queue
```bash
# Xem log trực tiếp của queue worker (theo dõi tiến trình đang bốc job)
docker compose logs -f queue

# Bắn thử một job vào hàng đợi để kiểm chứng
docker compose exec app php artisan tinker --execute="App\Jobs\TestQueueJob::dispatch('Test from DevOps Cheatsheet');"

# Khởi động lại worker (bắt buộc khi có thay đổi code trong thư mục app/Jobs/)
docker compose restart queue

# Kiểm tra backlog theo connection:queue
docker compose exec app php artisan queue:monitor database:default

# Xem lỗi để chẩn đoán; không xóa evidence bằng queue:flush
docker compose exec app php artisan queue:failed
```

### 5.3. [TRA CỨU KHI CẦN - KHÔNG CẦN HỌC THUỘC] Giám sát Healthcheck chuyên sâu
Trong local Compose, `healthy` chỉ chứng minh probe cụ thể đạt; không phải toàn bộ nghiệp vụ hoạt động. Docker không tự restart chỉ vì container unhealthy; `restart: unless-stopped` áp dụng khi process thoát.
```bash
# Xem trạng thái sức khỏe tổng quan của 5 container (Lệnh này nên nhớ)
docker compose ps

# Soi chi tiết kết quả healthcheck của một container cụ thể (JSON output - Tra cứu khi debug sâu)
docker inspect --format='{{json .State.Health}}' scool_queue
docker inspect --format='{{json .State.Health}}' scool_mysql
docker inspect --format='{{json .State.Health}}' scool_nginx

# Cú pháp healthcheck đã cấu hình sẵn trong docker-compose.yml (Hệ thống tự chạy ngầm, không cần gõ tay):
# - mysql:   authenticated SELECT 1 trên database scool (không dùng mysqladmin ping)
# - mailpit: wget -q --spider http://127.0.0.1:8025/api/v1/info || exit 1
# - nginx:   wget -q --spider http://127.0.0.1/up || exit 1
# - app:     nc -z 127.0.0.1 9000 (FPM listener; nginx /up kiểm tra response Laravel)
# - queue:   đọc /proc/1/cmdline để kiểm tra worker PID 1; không chứng minh job đang được xử lý
```

### 5.4. [NGUYÊN TẮC BẢO MẬT] Quy tắc Loopback IP (127.0.0.1)
- **Sai lầm phổ biến:** Để `"3306:3306"` hoặc `"8025:8025"`. Docker mặc định sẽ bind cổng vào `0.0.0.0:3306`, mở toang database ra toàn bộ mạng LAN wifi cơ quan, trường học hoặc quán cà phê.
- **Baseline local:** Publish trên `127.0.0.1`, không phải mọi interface. Đây không phải bảo đảm tuyệt đối trước port forwarding/tunnel/cấu hình host khác; không dùng credentials mẫu khi đưa lên Internet. Docker Engine < 28 có caveat về cùng mạng L2: [Docker port publishing](https://docs.docker.com/engine/network/port-publishing/).

---

## 6. CÁC LỆNH ARTISAN THƯỜNG DÙNG TRONG CONTAINER

```bash
# Chạy migration CSDL
docker compose exec app php artisan migrate

# Tạo một Controller mới chuẩn Laravel
docker compose exec app php artisan make:controller CommunityController

# Tạo một Model kèm Migration
docker compose exec app php artisan make:model Community -m

# Tạo một Job xử lý nền (Queued Job)
docker compose exec app php artisan make:job SendWelcomeEmail

# Tạo một Policy phân quyền
docker compose exec app php artisan make:policy CommunityPolicy --model=Community

# Xóa cache cấu hình khi sửa file config hoặc .env
docker compose exec app php artisan optimize:clear
```

---

## 7. BÍ QUYẾT PHÒNG CHỐNG "WORKS ON MY MACHINE" & KIỂM CHỨNG TRÊN MÁY THỨ HAI (EUR-20)

### 7.1. Bốn nguyên nhân cốt lõi khiến code "chạy máy tôi nhưng lỗi máy bạn":
1. **Quên commit file cấu hình mẫu hoặc thiếu biến môi trường:** Máy mình đã có `.env` đầy đủ, nhưng `.env.example` thiếu biến hoặc chứa giá trị mặc định sai.
2. **Race condition khi khởi tạo CSDL lần đầu:** Máy cũ đã có sẵn volume MySQL, máy mới volume rỗng nên MySQL cần 10–15s để chạy `init.sql` và nạp buffer. Nếu app chạy `migrate` quá sớm sẽ crash kết nối $\rightarrow$ Luôn dùng cờ `--wait` (`docker compose up -d --wait mysql`).
3. **Quên build static assets:** Máy mình chạy `npm run dev` nên có asset HMR, máy bạn không bật dev server và chưa chạy `npm run build` $\rightarrow$ Nginx trả về lỗi 404 hoặc thiếu CSS.
4. **Lệch chuẩn OS (Path delimiters & Line endings):** Windows dùng `\` và `CRLF`, Linux trong Docker dùng `/` và `LF`. Luôn dùng Docker làm runtime chuẩn để loại bỏ hoàn toàn khác biệt OS.

### 7.2. Lệnh tắt chuẩn hóa Quality Gate (Command Contracts)
Thay vì phải nhớ và gõ nhiều lệnh dài, hãy dùng các alias trong `composer.json`:
```bash
# Chạy 1 lệnh gộp: Vừa kiểm tra chuẩn code style vừa chạy full test suite
docker compose exec app composer verify

# Hoặc chạy riêng lẻ khi cần:
docker compose exec app composer test        # Chạy full test suite
docker compose exec app composer pint:test   # Kiểm tra Laravel Pint preset
```

### 7.3. Script 1-Click kiểm chứng toàn diện môi trường (Fresh Setup Drill)
Checkout sạch trên máy chưa có stack/volume S-cool (chi tiết và recovery trong README):
```bash
bash scripts/verify-fresh-setup.sh --mode bootstrap
```
Script bootstrap dependencies/key/build, chờ MySQL, migrate/seed trước khi bật worker,
kiểm tra đủ service và job thực tế rồi chạy quality gates. Không cam kết thời gian cố định.
Môi trường đã setup dùng `--mode verify` (không migrate/seed); mỗi lần chỉ chứng minh một host.
Evidence **hai máy độc lập** phải ghi riêng trên EUR-20/EUR-5; hiện chưa có evidence đó.

---

## 8. LỘ TRÌNH LUYỆN TẬP TIẾP THEO
- [x] **Trụ cột 1:** Containerization hoàn chỉnh (Docker, Nginx, PHP 8.2 Alpine, MySQL 8, Mailpit) (EUR-18).
- [x] **Trụ cột 2:** Cấu hình Background Queue Worker và Healthcheck đa container (EUR-18).
- [x] **Trụ cột 3:** Database Queue, Migrations và Deterministic Seed Baseline (EUR-19).
- [ ] **Trụ cột 4:** Command contracts đã có; fresh setup trên hai máy còn cần evidence (EUR-20).
- [ ] **Trụ cột 5:** Viết GitHub Actions CI Workflow tự động chạy `pint` và `test` khi có Pull Request (EUR-26).
- [ ] **Trụ cột 6:** Thiết lập Cloudflare / Ngrok HTTPS Tunnel để test Webhook IPN từ cổng thanh toán SePay.
- [ ] **Trụ cột 7:** Triển khai bản Staging lên Cloud VPS và cài SSL Let's Encrypt tự động.


