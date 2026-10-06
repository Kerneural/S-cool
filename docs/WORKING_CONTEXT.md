# Working context — S-cool

> Checkpoint không thay thế Linear/Git/runtime evidence. Đọc lại HEAD/status/code khi resume.

## Issue hiện tại — 06/10/2026

- [EUR-20](https://linear.app/eurusdevsec/issue/EUR-20/add-testbuild-commands-and-verify-fresh-setup-on-two-machines), parent EUR-5, M1. Đọc live cuối: **In Review**; không tự ghi Done.
- Nhánh publication `codex/eur-20-fresh-setup`, tạo từ main `661d878ca323af5b164e0d9d703e51eb15714738` (EUR-19 đã merge). Base tree giống HEAD đã dùng cho fresh drill; URL PR/commit evidence cập nhật trên Linear.
- Đã cho phép commit, push, tạo PR và comment EUR-20. Không merge/Done, đổi dependency/dates hoặc triển khai CI EUR-26.
- Code + local Verify + fresh isolated Bootstrap đã đạt; **AC hai máy độc lập vẫn còn mở**.

## Implementation / flow

- Bash entrypoint `scripts/verify-fresh-setup.sh`: --mode bootstrap|verify, mặc định verify. Helpers `setup-verification.sh`; regression `test-setup-verification.sh`.
- Script cũ được thay thế, README/DEVOPS_CHEATSHEET chuyển lệnh sang Bash. PHP helper giữ Laravel configuration/DB/seed/queue guards; không chuyển business checks sang shell.
- Bootstrap: guard checkout/resources → env/key/image/lockfile install → production build → MySQL ready → actual empty local DB guard → migrate → seed/rerun preservation → worker/stack ready → exact service/checkout/HTTP/queue → Composer validate/style/full tests.
- Verify không migrate/seed dev; tests chỉ scool_test, safety guard trước RefreshDatabase. Real queue smoke chỉ ghi log với UUID marker.
- Native failure/nonzero, thiếu service/health/checkout mismatch hoặc timeout đều fail-closed; không SkipTests/stale manifest false green; không automatic reset/flush/volume deletion.
- Isolated --project scool-eur20-NAME reuse Compose base với fixtures/compose.fresh.yml, chỉ đổi names/loopback ports và volume namespace. !override thay port list, Compose >= 2.24.4. Git Bash path normalization/MSYS Docker paths/native curl đã kiểm chứng.
- Composer contracts test/pint/pint:test/verify; bỏ boilerplate SQLite. Lockfiles/package versions không đổi.

## Evidence tự chạy

- Git Bash 5.2.26; Docker Engine 29.7.2, Compose 5.3.1; Node 22.23.2/npm 10.9.8; Vite 6.4.3.
- Bash syntax + **19 regression checks PASS**; Bootstrap trên checkout đang dùng bị chặn trước ghi vì .env có sẵn.
- Bash Verify stack dev exit 0: Composer validate --strict PASS, Pint **57 files PASS**, **52 tests / 215 assertions PASS**; live Mailpit SMTP integration không skip; production build và daemon smoke PASS.
- **Fresh Bootstrap thật PASS** trên local clone HEAD + exact EUR-20 script patch. Ban đầu không env/vendor/node_modules/build; new volume, không reuse dev data.
- Fresh clone: `R:/_Projects/Eurus_Workspace/scool/.setup-drills/eur20-87d60d2c` (gitignored). Project `scool-eur20-audit`, volume `scool-eur20-audit_scool_mysql_data`.
- PHP image build, 112 Composer packages install, npm ci, key, empty DB guard, 3 migrations, 5 personas + unchanged rerun, đủ 5 healthy services/ownership, HTTP /up đều đạt.
- Fresh queue marker `EUR20-smoke-df2beafc-657f-4240-9604-bc2a18c45da8`: pending=0, failed=0, handler=1.
- Fresh full tests **52 PASS / 215 assertions**, Pint **57 PASS**, production build PASS; bootstrap exit 0, 239s (không cam kết thời gian cố định).
- Entry script SHA256 `7A95B3CB2973432414B8EE1A349167A99CD20AAE0796FEACC69B2D654ADD6FFA`; source dirty, không phải immutable commit/CI.
- Evidence comments: EUR-20 `4aca27fa-d9f0-464b-8782-e1684f202dc5`; parent EUR-5 `db013a37-76d3-4e65-8dff-80cf24bb1113`. AC setup/tests/build/parent evidence tick; AC hai máy giữ unchecked.

## Security / limits

- Full npm audit exit 1: **5 high + 2 moderate**, dependency tree Tailwind (braces/chokidar/fast-glob/micromatch/tailwindcss; postcss-nested/selector-parser). Major-upgrade remediation chưa áp dụng; không audit fix --force.
- Đã ghi vào EUR-26 comment `712baa0d-628a-4468-9948-dcc2c6ba3ce7`; phải sửa hoặc approved exception trước security gate. Omit-dev audit = 0 không chứng minh compiled frontend an toàn.
- **Một host**, dù có clean clone/isolated stack. Không gọi là máy thứ hai. Cần thêm host độc lập chạy đúng source + sanitized evidence: OS/tools, SHA/dirty, initial state, migration/seed/queue/test/build.
- Runtime queue single-file log probe không phải guarantee exactly-once delivery chung. Timeout giữ job để chẩn đoán, không clear queue.
- Git Bash đã chạy thực tế; Linux/WSL chưa có runtime evidence. Không tự cài thêm runtime/scanner.
- Isolated stack được dừng bằng đúng project/files, giữ checkout/volume/images để tra cứu; dev stack giữ nguyên. Không down -v hoặc xóa shared inbox.

## Diff / next action

EUR-20: .gitignore (chỉ thêm .setup-drills/), composer.json, README.md, scripts/*.sh, scripts/verify-runtime.php, scripts/fixtures/compose.fresh.yml; cập nhật đoạn liên quan cheatsheet/checkpoint/operations.
Giữ các dirty edits trước đó ở Nginx, docs/00_README.md, docs/01_PRODUCT_SCOPE.md, docs/03_DELIVERY_PLAN.md, docs/AI_WORKFLOW.md và phần cheatsheet khác. docs/04_DEVOPS_OPERATIONS.md vẫn ignored, không force-add.

Publication chỉ gồm .gitignore, README.md, composer.json, scripts/ và checkpoint này. Cheatsheet chưa tracked và các dirty edits khác giữ ngoài commit; operations vẫn ignored.

Next: máy độc lập thứ hai checkout đúng commit của PR, chạy `bash scripts/verify-fresh-setup.sh --mode bootstrap` và gửi sanitized evidence vào EUR-20/EUR-5. Chờ review/required checks trước merge. Chưa Done/M1 complete; CI thuộc EUR-26.
