# S-cool

Private multi-community learning platform. Laravel, MySQL, Blade, Alpine.js,
Tailwind CSS và Vite. Local-first; chưa phải cấu hình production.

## Setup local — PowerShell

Cần Docker Desktop (Linux containers), Docker Compose có `--wait`, Node.js/npm.
Chạy tại root repository. Không cần PHP/MySQL cài trên máy host.

```powershell
# Không ghi đè .env đã có. Các password mẫu chỉ dùng ở local.
if (!(Test-Path .env)) { Copy-Item .env.example .env }
docker compose build app queue
docker compose run --rm --no-deps app composer install
# Không thay key của môi trường đã dùng.
if (Select-String -Path .env -Pattern '^APP_KEY=\s*$' -Quiet) {
    docker compose run --rm --no-deps app php artisan key:generate
}
npm ci
npm run build

# Migration trước khi khởi động worker; không reset dữ liệu đã có.
docker compose up -d --wait mysql
docker compose run --rm --no-deps app php artisan migrate
docker compose up -d --wait
```

Nếu tiếp tục môi trường đã setup, chỉ chạy `docker compose up -d --wait`.
Database test `scool_test` được tạo bởi `docker/mysql/init.sql` khi volume MySQL
được khởi tạo lần đầu. Volume cũ thiếu database này phải xử lý theo EUR-19,
không xóa volume để ép init script chạy lại.

### Seed baseline local

Sau migration, có thể tạo năm tài khoản demo bằng:

```powershell
docker compose exec app php artisan db:seed
```

Demo seeder chỉ chạy với `APP_ENV=local` hoặc `testing`; từ chối staging/production,
kể cả khi dùng `--force`. Tài khoản dùng mật khẩu mẫu chỉ dành cho local.
Tên/email mẫu cố định; timestamp và password hash không phải byte-for-byte fixtures.
Chạy lại chỉ thêm tài khoản còn thiếu, không đổi ID, tên, mật khẩu, trạng thái xác minh
hoặc thuộc tính của tài khoản đã có. Các nhãn Creator/Member/Platform Admin chưa cấp
role/quyền nghiệp vụ; role/community sẽ triển khai trong các issue tiếp theo.
Không chạy seed này trên database có dữ liệu thật hoặc expose tài khoản mẫu ra Internet.

- Web: http://127.0.0.1:8080/login
- Mailpit: http://127.0.0.1:8025 (hộp thư local, không gửi email ra ngoài).
- MySQL: `127.0.0.1:3306`, chỉ để client local; Laravel dùng hostname `mysql`.
- Frontend: `npm run dev` khi cần HMR; tắt Vite rồi `npm run build` để kiểm tra built assets.

## Kiểm tra và vận hành

```powershell
docker compose ps
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose logs --tail=30 queue
docker compose exec app php artisan queue:failed
# Worker giữ code trong RAM; restart sau khi sửa job/config.
docker compose restart queue
docker compose down
```

Tests chỉ được dùng MySQL `scool_test`, có guard trước `RefreshDatabase`.
Không dùng `down -v`, `migrate:fresh`, `queue:clear` hoặc `queue:flush` trong thao tác thường ngày.
Healthcheck là kiểm tra giới hạn (FPM listener, HTTP `/up`, MySQL query,
Mailpit HTTP, worker PID 1); `healthy` không chứng minh toàn bộ nghiệp vụ/queue hoạt động.

Tài liệu chuẩn và quy trình team: [docs/00_README.md](docs/00_README.md).
Issue/checkpoint đang làm: [docs/WORKING_CONTEXT.md](docs/WORKING_CONTEXT.md).
Fresh setup trên hai máy tiếp tục ở EUR-20; CI baseline thuộc EUR-26.
