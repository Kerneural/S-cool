> Corrective artifact review — 2026-10-20. Sections 1–3 are historical submitted results on 6179e3e389ddc8041568bd56a114d96fa044d401, not CI or a new run on the current dirty candidate. The original 527-test/269-file/61-module figures must not be reused as current proof. Browser/version/start-end times, queue backlog/migration evidence, blocker triage and a release recommendation were missing. Edge is explicitly N/T by the owner's 2026-10-20 direction to finish the remaining scope; that is not an Edge PASS or release-wide compatibility acceptance.

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
| **1. Login** | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not observed) | N/T (not observed) |
| **2. Community entry** | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not observed) | N/T (not observed) |
| **3. Feed interaction** | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not observed) | N/T (not observed) |
| **4. Live search** | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not observed) | N/T (not observed) |
| **5. Discussion viewer** | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not observed) | N/T (not observed) |
| **6. Lesson / progress** | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not observed) | N/T (not observed) |
| **7. Creator / Admin path** | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not supplied) | N/T (not observed) | N/T (not observed) |

## Corrected release recommendation

Conditional / incomplete evidence. Browser smoke cells above were not executed
in the submitted artifact; None was not a measured console/network result.
Current-candidate execution and remaining blockers are recorded separately in
the corrective Track B checkpoint. No deployment, provider, independent-host,
full-browser compatibility, CI, merge or owner acceptance is inferred.

The existing PR #48 is reversed: base testing-docs, head main. It therefore does
not submit these reports to main. A correct report PR would use base main and
head testing-docs (or a corrected report branch). Replacing/closing PRs and
publishing corrections require explicit current authorization.

## Evidence gallery

[Manual-test.md](Manual-test.md) links to the original B1 screenshots using
portable relative paths. Those screenshots remain historical; their names do
not establish Chrome/Edge versions, measured mobile emulation or new execution.

## Current-candidate corrective run (2026-10-20)

### Candidate and provenance

Runtime checkout: `eur-51-shell-contributions`, HEAD
`9e6baddd556b27b4503d7c96109f54ef001f8251`, plus the nine source files in
[source-manifest.sha256](current-evidence/source-manifest.sha256).
SHA-256 of that LF-delimited UTF-8 manifest:
`05d2bf9f06f4a1c02239985592a86f41500cae26cde6a5b10b40f298d9bb7001`.
The seven pre-existing Track A Blade fixes were preserved. This audit adds only
`resources/views/layouts/guest.blade.php` and the regression in
`tests/Feature/FrontendScaffoldTest.php`. Documentation/checkpoint edits and
unrelated untracked `docs/templates/` are outside the source manifest.

The corrected report worktree is based on historical report commit
`d55518316b782560b88113ce8bfb725f6e0a48cd`. Its older runtime is **not** the
checkout on which the 585-test run below executed. The two invitation files are
copied there for review. There is no tested merge, new CI run or published
correction. PR #48 still needs replacement with base `main`, head a corrected
report branch; publishing/closing PRs requires owner authorization.

Environment observed: Windows `10.0.26300`; PHP `8.2.34`; Chrome
`155.0.8059.85`; local app `127.0.0.1:8080`. App, MySQL, Nginx, Mailpit and queue
services were healthy. Read-only `migrate:status` showed all 44 listed migrations
Ran through `2026_10_16_160002_add_accepted_comment_to_posts_table`.
`queue:failed` reported no failed jobs; backlog/throughput is N/T. No migration,
reset, reseed, queue flush or inbox clearing was performed.

Synthetic fixture changes: one approved QA account, its verification, an isolated
FREE community, and an accepted invitation/member relation. No real identity or
payment was used. Verification/invitation tokens, cookies and passwords are
excluded from evidence. Bookmark/progress probes restored the original states.
The browser observation window below is 04:27:31Z–04:40:48Z (11:27–11:40 +07).
Exact start/end timestamps for the earlier terminal runs were not captured;
their command durations are recorded below rather than fabricated timestamps.

### Final automated checks

The `tests/TestCase.php` guard was inspected before execution: resolved database
must be MySQL `scool_test`, including URL-derived targets, before RefreshDatabase.

| Command | Final result | Duration / limits |
|---|---|---|
| `docker compose exec -T app php artisan test --compact --fail-on-skipped --fail-on-empty-test-suite` | Exit 0; **585 passed / 5765 assertions** | 511.36s; final nine-file source candidate |
| `docker compose exec -T app vendor/bin/pint --test` | Exit 0; **286 files PASS** | 9.53s |
| `node --test tests/js/*.test.mjs` | Exit 0; **13 passed / 0 failed / 0 skipped** | Final repeat 143.8632ms |
| `npm run build` | Exit 0; **63 modules** | 2.73s; CSS `app-DUqtcd7n.css`, JS `app-DI5mQRAi.js` |
| `bash scripts/verify-agent-contract.sh` | Exit 0; 11 shared files PASS | Structural/privacy check, not acceptance |
| `git diff --check` | Exit 0 | Runtime source/checkpoint at handoff |

The invitation regression was red before the fix (one failed test, four
assertions, 14.27s: zero Livewire runtime scripts where one was required).
The earlier 584-test run is historical after adding this fix and regression.
The final suite includes existing role/tenant, payment, membership, concurrency
and recovery tests. This is local automated evidence, not manual coverage of
every AC or provider confirmation.

The build retains twelve existing font-path resolution warnings. Font files
exist under public/fonts and the rendered Chrome font check was true. No new
dependency or font change was made; warnings are not evidence of missing runtime
fonts. The fixture's external YouTube embed reports "This video is unavailable";
lesson navigation/progress passed, provider playback acceptance is N/T.

### Current Chrome smoke

Measured CSS viewports were **1280×900 desktop** and **374×900 mobile emulation**,
DPR approximately 1.10. These are measured widths, not a claim of 375px or real
mobile hardware. The browser viewport override was reset after the run.

| Critical path | Chrome desktop | Chrome mobile | Edge desktop | Edge mobile | Observed result |
|---|---|---|---|---|---|
| Login | [PASS](current-evidence/chrome-login-desktop-after.png) | [PASS](current-evidence/chrome-login-mobile.png) | N/T | N/T | Guest form and successful Member session; [desktop form](current-evidence/chrome-login-desktop.png) |
| Community entry | [PASS](current-evidence/chrome-community-desktop.png) | [PASS](current-evidence/chrome-community-mobile.png) | N/T | N/T | Direct joined-tenant About entry |
| Feed interaction | [PASS](current-evidence/chrome-feed-desktop.png) | [PASS](current-evidence/chrome-feed-mobile.png) | N/T | N/T | Saved state persisted after refresh; native Return activation, then restored |
| Live search | [PASS](current-evidence/chrome-search-desktop.png) | [PASS](current-evidence/chrome-search-mobile.png) | N/T | N/T | DNS returned scoped Discussion and Lesson suggestions |
| Discussion viewer | [PASS](current-evidence/chrome-discussion-desktop.png) | [PASS](current-evidence/chrome-discussion-mobile.png) | N/T | N/T | Title/comments loaded, Close control and Escape available |
| Lesson/progress | [PASS](current-evidence/chrome-lesson-desktop.png) | [PASS](current-evidence/chrome-lesson-mobile.png) | N/T | N/T | Completed/100% persisted after refresh; restored Incomplete |
| Creator path | [PASS](current-evidence/chrome-creator-desktop.png) | [PASS](current-evidence/chrome-creator-mobile.png) | N/T | N/T | Accepted invitation/delivery state in Creator table |

Totals: **14 Chrome PASS, 0 FAIL, 0 BLOCKED; 14 Edge N/T**. This is the smoke set,
not the full B1/B2 scenario denominator. Edge was not connected; the owner
explicitly instructed recording Edge N/T and finishing other available work.
No Edge pass or two-browser compatibility acceptance is inferred.

Captured warning/error console entries were zero in the twelve Member captures.
The Creator captures did not include a separate console sample. A complete
network-request log was not exposed by the available browser API: request-status
coverage is N/T, not "no failed requests". No quantitative route/interaction
performance measurement was captured; timing/SLA is N/T. Existing iframe fixture
failure is recorded above. No production/load or independent-host claim follows.
An off-screen navigation frame and an unsettled progress frame were excluded;
their final screenshots were replaced only after the expected route/state was
visible. Earlier inconclusive automation activations are not product failures or
PASS evidence.

### Defect and blocker disposition

| Finding | Severity / disposition | Minimum retest |
|---|---|---|
| Valid original invitation leaves both controls disabled because guest layout lacks Alpine runtime | High; **Verified fixed locally**, two pre-fix reproductions, red regression then green final suite | Matching verified invitee opens original email, fragment scrub, accept, refresh/direct entry; bare link remains denied |
| Historical invitation screenshot was missing-token denial; B1 totals wrong | Corrected report interpretation/counts | Review 20 rows: 18 historical PASS, 1 BLOCKED, 1 N/T |
| B2 discovery screenshot mislabeled tenant search; native validation/replay claims overstated | Corrected; missing boundaries remain N/T | Complete boundary matrix on recorded revision |
| B3 unexecuted browser cells/errors and six broken gallery paths | Corrected; new Chrome results separate from history | Link check and current matrix above |
| PR #48 wrong base/head | Open publication blocker; replacement prepared locally | Correct PR diff, review and required CI after authorized publication |
| Track A five root defects | Locally fixed and prior bounded retest reported in EUR-52/54/55/56 | Changed UI consumers on any new source revision; do not infer owner visual acceptance |

Invitation evidence: [before fix](current-evidence/invitation-before-fix.png),
[accepted Member after fix](current-evidence/invitation-accepted-after.png), and
Creator accepted table in the matrix. Source runtime change is the single
`@livewireScripts` inclusion; no token, email, membership, payment or policy
contract was weakened.

### Recommendation and remaining AC

**Conditional / incomplete evidence; retain In Review.** The confirmed application
defect is fixed and the available current checks pass. This bounded audit does
not complete every literal AC: B1 complete role/module positive+terminal browser
journeys, B2 full role/state/nested-resource/recovery matrix, Edge smoke, complete
network/timing measurements and publication/review of the corrected artifacts
remain outstanding. B3 AC-01/03/04/07 remain partial; AC-02 has the final local
checks above, and AC-05/06 have explicit triage/retest limitations.

Minimum next gate: publish the reviewed correction in the proper PR direction
when authorized, run required checks on that exact integrated candidate, complete
the listed missing browser/boundary evidence or obtain explicit scoped acceptance
exceptions, then reassess child AC before parent EUR-53 closure. Do not mark
EUR-46/M11 Done or treat this recommendation as merge/release authority.

### Authorized publication preparation (2026-10-20)

The owner subsequently approved posting the four corrective Linear comments,
commit/push, a replacement PR against main and closing reversed PR #48. All four
comments were posted. The neutral correction branch is now rebased onto main
45ef4129db2edc74e917de65ca693c20c77b04fd, retaining both existing EUR-51 and new
EUR-53 checkpoint sections. Only the guest runtime inclusion and its regression
change application source relative to main; the separate seven Track A edits
remain in the primary checkout. The earlier nine-file runtime evidence remains
qualified as such. Publication and exact-PR checks are recorded separately;
there is no merge or Done authorization.
