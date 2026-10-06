# S-cool

Private multi-community learning platform. Laravel, MySQL, Blade, Alpine.js,
Tailwind CSS và Vite. Local-first; chưa phải cấu hình production.

## Setup local — Bash

Cần Bash, Git, Docker Engine/Desktop (Linux containers), Docker Compose có `--wait`,
Node.js/npm và curl. Windows dùng **Git Bash** hoặc WSL2 có Docker integration;
với WSL dùng Node/npm trong WSL, không trộn dependencies giữa hai host runtime.
Không cần PHP/MySQL trên host. Chạy tại root repository.

Checkout mới, chưa có stack/volume S-cool:

```bash
bash scripts/verify-fresh-setup.sh --mode bootstrap
```

Bootstrap kiểm tra checkout sạch/resources, tạo .env/key local, build PHP image,
install Composer/npm từ lockfiles, build assets, chờ MySQL rồi guard database rỗng,
migrate/seed và kiểm tra rerun. Worker chỉ bật sau khi bảng jobs tồn tại.
Sau đó kiểm tra đủ năm service, đúng checkout, HTTP /up, real queue job và quality gates.
Mọi failure trả non-zero; không SkipTests, không tự reset/rollback/xóa volume.

### Setup manual / tiếp tục bootstrap dở dang

Đọc bước lỗi trước khi chạy tiếp; chỉ dùng local data. Các credentials mẫu không dùng
ngoài local. Không ghi đè .env hoặc tạo lại key của môi trường đã sử dụng.

```bash
set -euo pipefail
if [[ ! -f .env ]]; then cp .env.example .env; fi
docker compose -p scool build app queue
docker compose -p scool run --rm --no-deps app composer install --no-interaction --prefer-dist
if grep -Eq '^APP_KEY=[[:space:]]*$' .env; then
    docker compose -p scool run --rm --no-deps app php artisan key:generate --no-interaction
fi
npm ci
npm run build
docker compose -p scool up -d --wait mysql
# Guard local connection/test DB before applying migrations.
docker compose -p scool run --rm --no-deps app php scripts/verify-runtime.php config
docker compose -p scool run --rm --no-deps app php artisan migrate --no-interaction
docker compose -p scool up -d --wait
```

Stack mặc định dùng project `scool`; commands pin `-p scool` để không lệch namespace theo tên folder.
Tiếp tục môi trường đã setup: `docker compose -p scool up -d --wait`.
Database test `scool_test` được init khi volume MySQL được tạo lần đầu.
Volume cũ thiếu test DB cần xử lý riêng, không xóa volume để ép init.sql chạy lại.

### Seed baseline local

```bash
docker compose -p scool exec -T app php artisan db:seed
```

Demo seed chỉ local/testing, từ chối staging/production kể cả --force.
Năm persona dùng mật khẩu mẫu local; nhãn Creator/Member/Admin chưa cấp role.
Seed rerun chỉ thêm tài khoản thiếu, không đổi attributes đã có; hashes/timestamps
giữa hai fresh runs không byte-identical. Không expose dữ liệu/tài khoản mẫu ra Internet.

- Web: http://127.0.0.1:8080/login
- Mailpit: http://127.0.0.1:8025 (SMTP local sink).
- MySQL host: `127.0.0.1:3306`; Laravel dùng hostname `mysql`.
- HMR: `npm run dev` chỉ khi phát triển; kiểm tra built assets không cần Vite server.

## Kiểm tra và vận hành

```bash
docker compose -p scool ps
docker compose -p scool exec -T app composer test       # full test suite
docker compose -p scool exec -T app composer pint:test  # Laravel Pint preset
docker compose -p scool exec -T app composer verify     # style + tests
docker compose -p scool logs --tail=30 queue
docker compose -p scool exec -T app php artisan queue:failed
# Worker giữ code trong RAM: restart sau khi sửa job/config.
docker compose -p scool restart queue
docker compose -p scool down # giữ named volume; không thêm -v
```

Verify môi trường đã setup, **không migrate/seed dev**:

```bash
bash scripts/verify-fresh-setup.sh --mode verify
bash scripts/test-setup-verification.sh
```

Verify luôn build assets mới, kiểm tra health/ownership/HTTP, UUID-marked log-only job,
Composer validate, Pint và full tests trên **scool_test**. Safety guard chạy trước
RefreshDatabase. Dừng Vite trước; nếu public/hot còn stale, xác nhận server đã tắt
trước khi xóa file đó. Tests không xóa shared Mailpit inbox.

### Fresh drill tách biệt trên cùng Docker host

Chỉ từ checkout sạch khác, không dùng clone đang có .env/vendor/node_modules/build:

```bash
bash scripts/verify-fresh-setup.sh --mode bootstrap --project scool-eur20-fresh
bash scripts/verify-fresh-setup.sh --mode verify --project scool-eur20-fresh
```

Overlay `scripts/fixtures/compose.fresh.yml` tái sử dụng base runtime, chỉ đổi names
và loopback ports: web 18080, MySQL 13306, Mailpit UI 18025/SMTP 11025.
Volume tách theo project, không reuse volume dev. Overlay dùng `!override` để
**thay** port list thay vì thêm port dev; cần Compose >= 2.24.4.
Xem [Docker Compose merge rules](https://docs.docker.com/reference/compose-file/merge/#replace-value).
Chỉ chạy một isolated drill cùng lúc do port list cố định.
Khi dừng, dùng đúng cả hai Compose files/project, không dùng down -v:

```bash
docker compose -p scool-eur20-fresh -f docker-compose.yml \
    -f scripts/fixtures/compose.fresh.yml down
```

Healthcheck chỉ kiểm tra giới hạn; healthy không thay thế queue smoke.
Không dùng migrate:fresh/queue:clear/queue:flush/down -v trong vận hành thường ngày.

## Evidence EUR-20

Mỗi run chỉ chứng minh **một host**. Fresh checkout/volume mới trên cùng máy không
trở thành máy thứ hai. Cần evidence **hai máy độc lập**: host alias, OS,
Docker/Compose/Bash/Node/npm versions, HEAD + dirty state, trạng thái ban đầu,
migration/seed/rerun/queue/test counts/build và sanitized output.
Gắn vào EUR-20 và EUR-5; không đưa .env, keys, user rows hoặc raw Mailpit bodies lên issue.
Fresh Bootstrap và Verify là hai loại evidence khác nhau; CI EUR-26 là gate riêng.

Tài liệu chuẩn: [docs/00_README.md](docs/00_README.md).
Handoff: [docs/WORKING_CONTEXT.md](docs/WORKING_CONTEXT.md).
