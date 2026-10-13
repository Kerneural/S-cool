# 03 — Delivery Plan

> Tài liệu sống: cách team ba người biến scope thành các lát cắt có thể chạy và kiểm thử.  
> Người viết chính: Hoàng  
> Người review: Tiến và Khoa  
> Trạng thái: Recovery baseline từ 03/10/2026 đến 08/10/2026; cập nhật hằng ngày theo tiến độ thật  
> Cập nhật: 2026-10-03

## 1. Delivery objective

Giai đoạn đầu kết thúc khi toàn bộ Must scope trong `01_PRODUCT_SCOPE.md` chạy ổn định ở local trên máy của cả ba thành viên. Team phải demo được private invitation flow, SePay Sandbox paid flow, learning flow và các failure case tenant isolation từ một database được tạo mới bằng migrations/seeds.

Hosting không phải điều kiện của giai đoạn này. Build pass đơn thuần cũng không đủ: application phải có authorization tests, payment idempotency, production frontend assets và hướng dẫn setup có thể tái lập.

## 2. Planning assumptions

### Thông tin đã chốt và còn mở

- Thời lượng recovery implementation: **6 ngày lịch liên tục**.
- Ngày bắt đầu: **03/10/2026**.
- Ngày kết thúc và demo nội bộ: **08/10/2026**.
- Số giờ mỗi thành viên có thể đóng góp: `OPEN — từng người cam kết trước ngày bắt đầu`.

Đây là lịch phục hồi đã nén mạnh. Hoàng trực tiếp hoàn thành M1 và M2 để nắm foundation, multi-community core và các quyết định quan trọng trước khi mở công việc song song cho Tiến và Khoa. Nếu mốc trượt, team phải giảm UI polish và capability phụ; không được khai báo milestone hoàn thành khi chưa có code, test hoặc demo evidence.

### Baseline để đánh giá scope

- Kế hoạch tham chiếu: 03/10–08/10/2026; ngày cuối chỉ dành cho tích hợp, sửa lỗi và demo.
- Hoàng làm M1 và M2 trước; ba thành viên chỉ triển khai song song từ M3 khi foundation và private core đã đạt exit criteria.
- Mỗi người tối đa một vertical slice đang làm tại một thời điểm.
- Không thêm capability after-MVP trong recovery timebox.
- UI chỉ cần rõ ràng, responsive cơ bản và đủ chạy golden flow; không đầu tư design system hoặc animation.
- Nếu tiến độ trượt, cắt pixel-level Skool styling, tiện ích và màn hình quản trị nâng cao trước; không cắt private-community boundary, invitation correctness, tenant isolation, authorization, migration correctness hoặc payment idempotency.
- Nếu hết 04/10 private core chưa đạt, Hoàng phải re-scope ngay thay vì dồn toàn bộ rủi ro sang các ngày cuối.

## 3. Team profile và nguyên tắc phân công

| Thành viên | Năng lực và chuyên môn chính | Vai trò đề xuất | Không nên trở thành điểm nghẽn duy nhất |
|---|---|---|---|
| Hoàng | Kiến trúc hệ thống, backend Laravel, điều phối kỹ thuật và DevOps | Phụ trách M1 Foundation và M2 Private Core; sau đó phụ trách Billing, local/CI và kiểm soát tích hợp | M1/M2 vẫn cần có reviewer; payment và setup môi trường cần có backup |
| Tiến | Phát triển fullstack (web application, Blade/Alpine UI, CSS) | Hỗ trợ review M1; sau M2 phụ trách Community Feed, Events, UI tương tác và responsive integration | Không một mình sở hữu toàn bộ frontend |
| Khoa | An toàn thông tin (cybersecurity), rà soát lỗ hổng và phát triển fullstack | Learning/Events, Platform Admin, threat review và security tests | Rà soát bảo mật tiến hành liên tục, không dồn vào cuối dự án |

Hoàng điều phối phạm vi kỹ thuật, kiến trúc và thứ tự ưu tiên; trực tiếp triển khai M1 Foundation và M2 Private Core. Mọi quyết định kỹ thuật đều tuân thủ quy trình review chéo: Tiến review foundation, Khoa review multi-tenancy/authorization và an toàn hệ thống; thành viên phụ trách mỗi vertical slice chịu trách nhiệm toàn diện từ migration, policy, backend, UI đến kiểm thử tự động.

## 4. Delivery principles

1. Làm theo vertical slice có hành vi người dùng quan sát được.
2. Xây foundation vừa đủ cho slice kế tiếp; không tạo framework nội bộ lớn trước feature.
3. Authorization và tenant boundary được thiết kế cùng feature, không bổ sung sau.
4. Hoàn tất một golden-flow segment trước khi mở thêm scope.
5. PR nhỏ, có mục tiêu đơn và có test phù hợp.
6. Feature rủi ro cao như payment, multi-tenancy và platform privilege cần Hoàng và Khoa cùng review.
7. Không merge code chỉ vì chạy được trên máy tác giả.
8. Tài liệu sống cập nhật trong cùng PR nếu decision hoặc workflow thay đổi.
9. Main luôn phải build/test được; branch tồn tại ngắn.
10. Không dùng after-MVP work để né việc sửa lỗi Must scope.

## 5. Milestones local-first từ 03/10 đến 08/10

Milestone là outcome gate, không phải danh sách việc đã dự định làm. Một milestone chỉ hoàn thành khi exit criteria có evidence. Hoàng trực tiếp sở hữu M1 và M2; các slice M3 chỉ được mở sau khi private core ổn định.

| Mốc | Hạn chót | Owner chính | Outcome demo được | Exit criteria |
|---|---|---|---|---|
| M1 — Foundation ready | 03/10/2026 | Hoàng; Tiến review | Compose, Laravel, MySQL, auth, production asset build và CI baseline chạy | Fresh setup chạy trên ít nhất hai máy; migrations/tests/build và CI EUR-26 có evidence |
| M2 — Private core ready | 04/10/2026 | Hoàng; Khoa review | Creator tạo private community và email invitation; user ngoài nhóm bị chặn | Ownership, invitation/membership states, policies và isolation tests đạt |
| M3 — Core features ready | 05–06/10/2026 | Tiến + Khoa; Hoàng tích hợp | Feed, Classroom, lesson progress và Calendar chạy ở mức tối thiểu | CRUD/publish/progress/timezone rules và tests chính đạt |
| M4 — Payment & security ready | 07/10/2026 | Hoàng + Khoa; Tiến hỗ trợ UI | SePay Sandbox, member control và threats trọng yếu được test | IPN transaction/idempotency, state transitions và security checks đạt |
| M5 — Demo ready | 08/10/2026 | Cả team | Golden/failure flows chạy từ fresh setup | Critical/High blockers được xử lý; smoke test, docs, seed và production build đạt |

### Daily control points

- Cuối 03/10: M1 phải có setup/build/test evidence; nếu chưa đạt thì M2 chưa được đánh dấu `In Progress` toàn phần.
- Cuối 04/10: community ownership, membership và isolation phải ổn định; nếu chưa đạt thì re-scope M3 ngay.
- Cuối 06/10: đóng việc thêm chức năng core; phần chưa bắt đầu phải có quyết định giữ/cắt rõ ràng.
- Cuối 07/10: feature freeze; chỉ sửa integration, security và demo blockers.
- Ngày 08/10: không nhận scope mới dưới bất kỳ hình thức nào.

## 6. Vertical slices

| Slice | User outcome | Bao gồm | Không bao gồm | Dependency | Driver | Reviewer/backup |
|---|---|---|---|---|---|---|
| VS-01 Identity & local bootstrap | User đăng ký/đăng nhập/reset password; team setup giống nhau | Laravel skeleton, auth, profile, Mailpit, MySQL, Vite build | Social login, MFA | Không | Hoàng | Tiến |
| VS-02 Creator creates private community | Creator tạo nhiều community private bằng slug nội bộ và access mode | Community model, creator ownership, private dashboard, protected cover image | Discovery/search/public landing, ownership transfer | VS-01 | Hoàng | Khoa |
| VS-03 Invitation & isolation | Creator mời email cụ thể; đúng invitee vào đúng tenant | Invitation lifecycle, email-bound token, membership, scoped routes, policies, isolation tests | Shared invite link, approval queue | VS-02 | Hoàng | Khoa |
| VS-04 Community feed | Active member đăng post/comment; Creator moderation | CRUD, authorship, pagination, soft delete | Reaction, reporting workflow, realtime | VS-03 | Tiến | Khoa |
| VS-05 Classroom publishing | Creator publish course/section/lesson; member xem | Draft/published, order, text/embed URL | File lesson upload, quiz, drip content | VS-03 | Khoa | Tiến |
| VS-06 Lesson progress | Member đánh dấu hoàn thành và thấy lại tiến độ | Unique progress, access checks, UI state | Certificate, analytics | VS-05 | Khoa | Hoàng |
| VS-07 Events | Creator tạo/hủy event; Member xem lịch/link | UTC storage, IANA timezone, external URL | RSVP, reminder, native live | VS-03 | Tiến | Khoa |
| VS-08 SePay Sandbox access | Verified SePay IPN kích hoạt đúng invited membership | Payment attempts, SePay adapter, HTTPS tunnel/IPN, idempotency | Production payment, recurring, refund/payout | VS-03 | Hoàng | Khoa |
| VS-09 Creator member management | Creator suspend/reactivate/remove member | Member list, state transitions, authorization | Bulk import, admin delegation | VS-03 | Hoàng | Tiến |
| VS-10 Platform administration | Platform Admin suspend/reactivate community | Admin role, minimal listing, reason/audit log | Edit content as admin, analytics dashboard | VS-02 | Khoa | Hoàng |
| VS-11 Hardening & demo | Golden/failure flows chạy end-to-end | Smoke test, seed personas, error/empty states, docs | New features | VS-01…10 | Cả team | Cross-review |

## 7. Must backlog map

| Product capability | Delivery slice | Verification chính | Priority |
|---|---|---|---|
| Account & Authentication | VS-01 | Feature tests + Mailpit manual check | Must |
| Community Management | VS-02 | Creator ownership tests | Must |
| Multi-community Membership | VS-03 | Cross-community authorization tests | Must |
| Private Access Modes | VS-02/03/08 | Free/paid invitation state tests | Must |
| Community Feed | VS-04 | Authorship/moderation tests | Must |
| Classroom | VS-05 | Publish/access tests | Must |
| Lesson Progress | VS-06 | Unique/access tests | Must |
| Events | VS-07 | Timezone/access tests | Must |
| Invitation Management | VS-03 | Email/expiry/revoke/idempotency tests | Must |
| SePay Sandbox | VS-08 | Success/failure/cancel/duplicate IPN integration tests | Must |
| Creator Member Management | VS-09 | State transition/policy tests | Must |
| Platform Administration | VS-10 | Privilege and audit tests | Must |
| Private Community Boundary | VS-02/03 | Enumeration and unauthorized metadata tests | Must |
| Tenant Isolation | Xuyên suốt | Negative authorization suite | Must |

## 8. Ownership và review matrix

| Area | Driver | Required reviewer | Backup | Rủi ro chính |
|---|---|---|---|---|
| Product scope/architecture | Hoàng | Khoa và Tiến | Khoa | Quyết định kiến trúc thiếu tài liệu hóa (rủi ro bus factor) |
| Identity/Foundation | Hoàng | Tiến | Khoa | Auth/session regression hoặc local setup chỉ chạy trên một máy |
| Multi-tenancy/RBAC | Hoàng | Khoa | Tiến | Cross-community data leak |
| Community Feed/UI | Tiến | Khoa | Hoàng | Authorization và XSS |
| Learning/Progress | Khoa | Tiến | Hoàng | Publish/access inconsistency |
| Events | Tiến | Khoa | Hoàng | Timezone và invalid URL |
| SePay Sandbox | Hoàng | Khoa bắt buộc | Tiến | IPN spoof/replay/duplicate và tunnel config |
| Platform Administration | Khoa | Hoàng | Tiến | Privilege escalation |
| Local/CI/operations | Hoàng | Tiến | Tiến | Sai lệch môi trường (chỉ chạy trên một máy cục bộ) |
| Demo/stabilization | Cả team | Cross-review | Cả team | Lỗi tích hợp muộn |

## 9. Definition of Ready

Một backlog item chỉ bắt đầu khi:

- [ ] Actor và user outcome rõ.
- [ ] Acceptance criteria có thể kiểm thử.
- [ ] Dependency và affected module đã biết.
- [ ] Tenant/authorization impact được ghi rõ.
- [ ] State transition và failure path liên quan đã xác định.
- [ ] Migration/data impact đủ rõ.
- [ ] Driver và reviewer đã nhận việc.
- [ ] Không còn câu hỏi làm thay đổi bản chất slice.

## 10. Definition of Done

- [ ] Acceptance criteria đạt ở local.
- [ ] Server-side policy và tenant scope đúng.
- [ ] Migration, factory và seed được cập nhật nếu cần.
- [ ] Unit/feature/integration tests phù hợp đạt.
- [ ] Negative authorization tests được thêm cho resource mới.
- [ ] Validation, empty state và failure feedback đủ hiểu.
- [ ] Frontend production build thành công.
- [ ] Không chứa secret, debug dump hoặc sensitive test data.
- [ ] Query list có pagination và không có N+1 rõ ràng.
- [ ] Reviewer khác driver đã review.
- [ ] Tài liệu sống được cập nhật nếu decision thay đổi.
- [ ] Golden flow liên quan vẫn chạy.

## 11. Test strategy

| Mức | Mục tiêu | Phần bắt buộc | Trách nhiệm |
|---|---|---|---|
| Unit | Business rule/state transition thuần | Invitation/payment state, SePay signature/auth helpers | Feature owner |
| Feature | HTTP + validation + policy + database | CRUD, invitations, memberships, publish, admin actions | Feature owner |
| Integration | Boundary nhiều component | Invitation email, SePay IPN/payment transaction, constraints | Hoàng + Khoa review |
| Tenant/security | Chứng minh request trái quyền thất bại | Enumeration, invitation token replay, IDOR, cross-community, platform privilege, IPN replay | Khoa dẫn; mỗi owner bổ sung |
| Smoke/end-to-end | Golden flow từ góc nhìn demo | Free invite, paid invite + SePay, lesson progress, suspend flows | Cả team trước milestone exit |
| Manual exploratory | UX/error state khó tự động hóa | Browser responsive, invalid inputs, back/refresh, empty states | Driver + reviewer |

Testing pyramid không được hiểu là “mọi method phải có unit test”. Ưu tiên feature/integration tests tại policy và transaction boundary có rủi ro cao.

## 12. Team workflow

- Branch strategy: GitHub Flow với branch ngắn từ `main`; không commit trực tiếp vào protected `main`.
- Branch naming: `feature/<short-name>`, `fix/<short-name>`, `docs/<short-name>`.
- Pull request: một mục tiêu rõ, mô tả outcome, cách test, migration/security impact và screenshot khi UI thay đổi.
- Review: tối thiểu một reviewer; payment, tenancy và platform privilege cần Hoàng + Khoa.
- Merge: squash merge để lịch sử chính gọn; không merge khi required checks fail.
- Sync: 15–20 phút ba lần/tuần; tập trung done/next/blocker/decision.
- Blocker: báo trong ngày, ghi rõ điều đã thử và input cần từ ai.
- Backlog: Linear được đề xuất làm nguồn theo dõi công việc duy nhất; mỗi issue liên kết slice/capability và acceptance criteria. GitHub chỉ giữ source code, pull request và CI evidence.
- Scope change: cập nhật Product Scope + impact lên milestone trước khi đưa vào backlog.
- WIP limit: tối đa một active slice/người; ưu tiên giúp unblock/review trước khi mở việc mới.

### 12.1 Linear setup — nguồn vận hành duy nhất

Không tạo Initiative hoặc nhiều Project cho recovery timebox này. Linear là nguồn trạng thái công việc; GitHub là nguồn code, pull request và CI evidence.

#### Project

| Field | Value |
|---|---|
| Team | `EurusDevSec` |
| Project | `S-cool MVP — Local` |
| Project URL | `https://linear.app/eurusdevsec/project/s-cool-mvp-local-1d6991ed5de2` |
| Linear runbook | `https://linear.app/eurusdevsec/document/s-cool-linear-workflow-and-milestone-runbook-a875737d9e3c` |
| Lead | Hoàng |
| Members | Hoàng, Tiến, Khoa |
| Status ban đầu | `Planned`; chuyển `Started` khi VS-01 bắt đầu |
| Start date | 03/10/2026 |
| Target date | 08/10/2026 |
| Priority | `High` |

Không cần tạo Cycle riêng sáu ngày. Tại thời điểm setup 03/10/2026, team chưa có current Cycle nên issues chưa được gắn Cycle; Project và Milestone là mốc giao hàng chính.

#### Workflow

`Backlog → Ready → In Progress → In Review → Done`

- `Backlog`: nằm trong kế hoạch nhưng dependency hoặc acceptance chưa đủ rõ.
- `Ready`: đủ Definition of Ready và có thể bắt đầu ngay.
- `In Progress`: owner đang thực hiện; mỗi người tối đa một active issue.
- `In Review`: đã có PR hoặc demo evidence để reviewer kiểm tra.
- `Done`: đạt Definition of Done; không dùng cho kế hoạch hoặc code chưa kiểm thử.
- Dùng label `blocked`, không tạo thêm status `Blocked`.

#### Milestones phải tạo

1. `M1 — Foundation ready` — 03/10/2026.
2. `M2 — Private core ready` — 04/10/2026.
3. `M3 — Core features ready` — 06/10/2026.
4. `M4 — Payment & security ready` — 07/10/2026.
5. `M5 — Demo ready` — 08/10/2026.

#### Parent issues và milestone mapping

| Linear ID | Parent issue | Milestone | Assignee | Reviewer | Trạng thái khởi tạo |
|---|---|---|---|---|---|
| EUR-5 | VS-01 Identity & Local Bootstrap | M1 | Hoàng | Tiến | In Progress |
| EUR-6 | VS-02 Creator Creates Private Community | M2 | Hoàng | Khoa | Todo |
| EUR-7 | VS-03 Invitation & Tenant Isolation | M2 | Hoàng | Khoa | Backlog |
| EUR-8 | VS-04 Community Feed | M3 | Tiến | Khoa | Backlog |
| EUR-10 | VS-05 Classroom Publishing | M3 | Khoa | Tiến | Backlog |
| EUR-9 | VS-06 Lesson Progress | M3 | Khoa | Hoàng | Backlog |
| EUR-11 | VS-07 Community Events | M3 | Tiến | Khoa | Backlog |
| EUR-12 | VS-08 SePay Sandbox Access | M4 | Hoàng | Khoa | Backlog |
| EUR-13 | VS-09 Creator Member Management | M4 | Hoàng | Tiến | Backlog |
| EUR-14 | VS-10 Platform Administration | M4 | Khoa | Hoàng | Backlog |
| EUR-15 | VS-11 Hardening & Local Demo | M5 | Hoàng (điều phối tích hợp) | Cross-review | Backlog |

Ngày 05/10/2026 đã xác minh tài khoản và assign trực tiếp trên Linear theo bảng: Tiến (`2224802010556`), Khoa (`hadangkhoasss`). Các trạng thái ở bảng là baseline khởi tạo; đọc Linear để biết trạng thái hiện tại. Không ghi phân công vào description kỹ thuật.

#### Triển khai song song sau private core

| Luồng | Assignee | Thứ tự ưu tiên | Dependency kỹ thuật |
|---|---|---|---|
| Content & Events | Tiến | EUR-8 → EUR-11 | Cả hai chỉ chờ EUR-7; Events không chờ Feed |
| Learning & Platform | Khoa | EUR-10 → EUR-9 → EUR-14 | Progress chờ Classroom; Admin chỉ chờ EUR-6 |
| Billing & Member control | Hoàng | EUR-12 → EUR-13 | Cả hai chờ EUR-7; Member control không chờ Payment |
| Integration & Demo | Hoàng điều phối; cả team xử lý module tương ứng | EUR-15 | Nghiệm thu toàn bộ sau các slice; chuẩn bị smoke/evidence từ sớm |

- Mũi tên trong cột thứ tự là lịch sử dụng nhân lực, không tạo `blocked by` giả. Có thể chọn issue đủ điều kiện khác trong cùng luồng khi issue ưu tiên đang block.
- Mỗi người tối đa một issue đang code; không chia một feature thành backend/UI do hai người làm rồi phải chờ nhau. Một slice chịu trách nhiệm migrations, backend, UI, authorization và tests trong đúng scope.
- EUR-7 bàn giao baseline M2 đã merge: community routes, ownership, membership/community state access, policies, factories/fixtures hai tenant; layout/navigation tái sử dụng từ EUR-6. Không mở feature bằng cách bypass authorization hoặc giả vờ M2 đã hoàn thành.
- Shared schema/policy/layout không được tự thay đổi ngoài scope; cần ghi impact và thống nhất trước. Thay đổi routes/shared files phải tối thiểu, không sửa migration đã merge. PR nhỏ gắn đúng issue và có evidence.
- Review diễn ra theo PR, không chờ hoàn thành module của reviewer. Nếu reviewer đang block lịch nghiệm thu, dùng backup phù hợp trong ownership matrix; không tự bỏ review hoặc security gates.
- Không đợi hết M3 mới mở M4 nếu dependency kỹ thuật đã đạt; milestone là outcome gate, không phải dependency tự động. Trong lúc chờ M2 có thể setup local, đọc AC và review foundation, chưa triển khai feature trên contract chưa chốt.

Chỉ tạo sub-issue khi phần việc có owner hoặc acceptance độc lập. Trước mắt chỉ phân rã VS-01 và VS-02; không nhập hàng chục sub-issue cho M3–M5 khi dependency chưa sẵn sàng.

#### Sub-issues Hoàng làm trước

VS-01:

- Bootstrap Laravel và authentication.
- Configure Blade, Alpine.js, Tailwind CSS và Vite.
- Tạo Docker Compose cho app/nginx/MySQL/Mailpit/worker theo architecture đã chốt.
- Configure database queue, `.env.example`, migrations và seed baseline.
- Thêm test/build command và kiểm tra fresh setup trên hai máy.
- [EUR-26 — CI baseline: GitHub Actions, quality gates & failure evidence](https://linear.app/eurusdevsec/issue/EUR-26/ci-baseline-github-actions-quality-gates-and-failure-evidence): thuộc EUR-5/M1, bổ sung ngày 05/10/2026. Triển khai sau EUR-19, related EUR-20; CI không thay thế fresh setup hai máy. Ngày mục tiêu 05/10; các mốc milestone khác giữ nguyên. Assignee, AC và status quản lý trên Linear. CD deployment deferred tới sau local readiness và quyết định hosting.

VS-02/VS-03:

- Tạo Community model, creator ownership và protected routes.
- Tạo Membership/Invitation state baseline.
- Thực hiện email-bound invitation accept flow.
- Thêm policies, community-scoped queries và route binding.
- Viết negative tests cho guessed slug/ID và cross-community access.

#### Labels tối thiểu

`area:identity`, `area:community`, `area:learning`, `area:events`, `area:payment`, `area:platform`, `area:devops`, `security`, `bug`, `blocked`.

#### Issue description template

```markdown
## Outcome

<Actor + observable result + resource/context; explain how completion is demonstrated.>

## Scope

- Includes: <end-to-end slice: data, backend, UI and tests as applicable.>
- Excludes: <explicit adjacent features and non-goals.>

## Workflow and contracts

- Entry point -> permitted action -> persisted state -> visible result.
- Existing contracts/docs: <link, section and actual class/action/policy where available.>
- Resource hierarchy, role/state rules, fields and validation behavior.
- UI: required screens, navigation, empty/error states and responsive behavior.
- Shared-file/module boundaries and coordination needed before edits.
- Decisions: <CONFIRMED / PROPOSED / OPEN; who or what decision is required, not invented approval.>

## Acceptance criteria

- [ ] AC-01: Given <actor/state>, when <action>, then <observable result>.
- [ ] AC-02: Invalid input <specific case> produces <feedback> without <unwanted mutation>.
- [ ] AC-03: Wrong role/tenant/state <specific cases> cannot read or mutate the resource.
- [ ] AC-04: Refresh/repeated/concurrent request <as relevant> preserves <invariant>.
- [ ] AC-05: Required UI states and relevant regression/negative tests are demonstrated.
- [ ] Review and applicable acceptance checks are evidenced; exceptions need explicit scoped authorization.

## Dependencies

- Blocked by: <actual required issue/merged contract, or None.>
- Blocks: <downstream consumer and exact handoff needed, or None.>
- Related: <coordination only; not a false blocking dependency.>

## Security and failure cases

- Authorization/tenant boundary, ownership, untrusted input and sensitive data.
- Invalid/terminal state, rollback/retry/concurrency and retention behavior as relevant.
- Migration/shared-contract impact and explicit implementation boundary.

## Verification plan

- Automated: <exact command once test names exist; before that, cases to add, not fictional existing tests.>
- Manual: <happy path, negative case, responsive/direct-link checks and expected results.>
- Integration/build: <applicable checks and isolated runtime requirements.>

## Evidence

- Commit / PR: Pending.
- Automated result: Pending; record exact command, exit/result, revision and environment.
- Manual result / sanitized screenshot: Pending.
- Review / CI: Pending, or N/A with a reason. Local success is not CI success.
- Unverified items / blockers / exceptions: <explicit, or None after verification.>
```

#### Issue quality gate

- Use this template for every new issue/sub-issue and material scope change. Small bugs/chores may keep sections short and use `N/A` with a reason, but must still define outcome, scope, testable AC and evidence.
- Write technical issue prose in English and objectively. No personal names, agent identities or Owner/Reviewer narratives; assignments belong in metadata.
- Before Ready, verify Linear metadata: correct project/milestone, assignee, priority, estimate, labels and actual dependency relations; set a due date for time-bound work and a cycle only when the team uses one. Resolve missing or conflicting values instead of guessing or silently rescheduling.
- Replace placeholders before Ready. Record unapproved behavior as `PROPOSED`/`OPEN`; it is not an accepted requirement. If it affects implementation or acceptance, resolve it before coding that part. Do not invent evidence or pre-check AC.
- AC must specify an actor/state, action and result. Separate product decisions from implementation choices; name concrete negative cases instead of saying only "authorization works".
- Preserve unrelated description content and metadata. Relations must match the Dependencies section; execution order/WIP is not a technical dependency.
- Evidence records the exact tested revision/environment and remaining gaps. Review/CI/build requirements are explicit; a previous acceptance exception does not apply automatically.
- At handoff, link the merged contract needed by downstream work. Only create sub-issues with independent ownership or acceptance; do not split backend/UI purely to create more tickets.
- `AGENTS.md` and `AI_WORKFLOW.md` route all participants here. This Markdown contract is a required workflow, not proof that a human or IDE agent complied. Native Linear form templates are optional; keep this section canonical rather than creating another roadmap.

#### Nhịp cập nhật hằng ngày

1. Đầu ngày: Hoàng xác nhận milestone hiện tại, issue `Ready` và blocker.
2. Trong ngày: mỗi người chỉ giữ một issue `In Progress`; blocker phải được gắn label và mô tả điều đã thử.
3. Khi code xong: chuyển `In Review`, gắn PR/test/screenshot; chưa có evidence thì không chuyển `Done`.
4. Cuối ngày: đối chiếu exit criteria milestone, ghi phần đạt/chưa đạt và quyết định giữ/cắt scope ngày sau.
5. Agent chỉ hỗ trợ tạo/cập nhật issue hoặc tổng hợp tiến độ khi Hoàng yêu cầu; không tự đổi scope, priority hoặc đánh dấu `Done`.

## 13. Risk register

| Risk | Dấu hiệu sớm | Tác động | Giảm thiểu | Owner |
|---|---|---|---|---|
| Scope tăng liên tục | After-MVP item xuất hiện trong sprint hiện tại | Golden flow không hoàn tất | Must/After-MVP gate; mọi scope change có impact | Hoàng |
| Rò dữ liệu giữa community | Query bằng ID trực tiếp; thiếu negative test | Security failure nghiêm trọng | Community-first query, policies, constraints, isolation suite | Hoàng + Khoa |
| SePay integration chặn local | Chưa có sandbox credential hoặc HTTPS IPN URL trước 07/10 | Paid flow không tích hợp kịp | Xin credential sớm; tunnel runbook; fake gateway chỉ cho automated tests | Hoàng |
| Private flow bị biến thành public | Có public listing/landing/join hoặc invite link dùng chung | Sai yêu cầu và tăng exposure | Email-bound invitation, enumeration tests, scope review | Hoàng + Khoa |
| Kiến thức tập trung một người | PR lớn, không reviewer hiểu được | Bus factor cao | Driver/reviewer/backup; walkthrough cuối slice | Cả team |
| Frontend hoàn thiện chậm | Backend xong nhưng flow không demo được | Tích hợp muộn | Vertical slice; Blade skeleton sớm; UI đủ dùng trước polish | Tiến |
| Security review quá muộn | Khoa chỉ review khi sang M4 | Rework lớn | Threat checklist trong DoR/DoD từng slice | Khoa |
| Local environment lệch | “Chỉ chạy máy tôi”, manual setup khác nhau | Mất thời gian debug | Compose, lockfiles, setup script/docs, fresh setup test | Hoàng + Tiến |
| Migration phá dữ liệu demo | Reset DB thường xuyên hoặc migration sửa lịch sử | Mất dữ liệu/test không tin cậy | Immutable merged migrations, seed deterministic, backup trước destructive change | Hoàng |
| Recovery timebox bị trượt | M1/M2 không đạt đúng ngày; nhiều issue cùng `In Progress` | Không hoàn thành golden flow | Daily milestone review; WIP limit; Hoàng cắt polish/capability phụ ngay tại checkpoint | Hoàng |

## 14. Local readiness gate

Chỉ mở quyết định hosting khi:

- [ ] Tất cả capability Must đạt Definition of Done.
- [ ] Demo scenario trong Product Scope chạy từ fresh setup.
- [ ] Free invitation và paid SePay Sandbox success/failure/cancel/duplicate đạt.
- [ ] User không được mời không tìm thấy hoặc xem metadata/nội dung community.
- [ ] Tenant-isolation, authorship và platform privilege tests đạt.
- [ ] Fresh clone/setup chạy được trên ít nhất hai máy khác nhau ngoài máy Hoàng.
- [ ] Migration và deterministic seed tạo đủ demo personas/data.
- [ ] Production frontend build chạy không cần Vite dev server.
- [ ] Queue worker, Mailpit, SePay Sandbox IPN và HTTPS tunnel có hướng dẫn debug.
- [ ] Dependency/security checks không có finding nghiêm trọng chưa xử lý.
- [ ] Threat review của Khoa được ghi nhận.
- [ ] Không còn lỗi Critical/High đã biết trong golden flow.
- [ ] Mentor đã xem demo local hoặc acceptance evidence tương đương.

## 15. Decision và change log

| Ngày | Quyết định | Lý do | Tác động |
|---|---|---|---|
| 2026-09-23 | Multi-community | Yêu cầu mentor | Tenant/RBAC là requirement nền tảng |
| 2026-09-23 | Payment sandbox trước | Tránh tiền/nghĩa vụ thật | Real billing deferred |
| 2026-09-23 | Local-first | Tập trung hoàn thiện chức năng | Hosting deferred |
| 2026-09-23 | Creator tự vận hành | Giảm role/scope | Community Admin/Moderator after MVP |
| 2026-09-25 | Delivery bằng vertical slice | Giảm tích hợp muộn | Owner chịu trách nhiệm end-to-end |
| 2026-09-25 | Timebox implementation 2 tuần | Baseline ban đầu của dự án | Được thay thế bởi recovery schedule ngày 03/10/2026 |
| 2026-09-25 | Hoàng giữ decision và progress control | Một đầu mối chịu trách nhiệm về scope, architecture và module trọng yếu | Decision được ghi trong tài liệu/Linear; implementation vẫn review chéo |
| 2026-09-25 | Linear là công cụ backlog đề xuất | Nhẹ hơn cho team ba người và dễ tích hợp agent | Chỉ có hiệu lực sau khi team chốt workspace/workflow |
| 2026-09-25 | Community private; email-bound invitation là baseline đề xuất | Yêu cầu sau họp mentor; mỗi nhóm có community riêng | Loại public landing/discovery/free join; Hoàng xác nhận cơ chế invite trước migration |
| 2026-09-25 | SePay Sandbox cho paid flow | Yêu cầu sau họp mentor | Thay local simulator trong demo; cần credential và HTTPS tunnel |
| 2026-09-25 | UI lấy Skool làm tham chiếu vừa phải | Mentor muốn trải nghiệm dễ nhận biết | Giữ Community/Classroom/Calendar; không pixel-perfect hoặc sao chép brand asset |
| 2026-10-03 | Chuyển sang recovery schedule 03/10–08/10 | Implementation chưa khởi động đúng kế hoạch | Năm milestone có ngày cụ thể; Hoàng trực tiếp làm M1 và M2 trước khi mở M3 |

## 16. Immediate next actions

1. Hoàng tạo/cập nhật Linear project với start date 03/10 và target date 08/10.
2. Tạo năm milestones và 11 parent issues theo bảng 12.1; không tạo Initiative hoặc Cycle mới chỉ cho sáu ngày.
3. Hoàng đưa VS-01 vào `In Progress`, phân rã sub-issues M1 và gắn Tiến làm reviewer.
4. Chỉ mở VS-02/VS-03 khi foundation có setup/build/test evidence; Khoa review tenant và authorization.
5. Tiến và Khoa chuẩn bị issue M3 ở `Backlog`, chưa bắt đầu implementation trước khi M2 đạt exit criteria.

## 17. Functional completion release — implementation handoff

### 17.1 Purpose, evidence and execution boundary

Deliver a functional learning-community product comparable to the mentor reference, starting from the implemented MVP. Complete user workflows before a separate visual redesign. This section is the canonical completion backlog draft; Linear remains the source of issue status, assignments, estimates, dates and AC after issue creation. Sections 1–16 retain the original MVP plan/history and do not schedule this release.

Baseline inspected on 2026-10-13: `main` at `2996aa6a24e4f8821d9351c0beb6460b554b8d6e`, clean before planning edits. Current routes/models/controllers include private community ownership/membership, invitations, covers, Feed, Classroom/progress, Events, SePay transfer-webhook Test Mode, creator member control and Platform Admin suspension/audit. This is source evidence; no new runtime, CI or independent-host acceptance is claimed by the plan. Open MVP acceptance evidence must remain visible separately.

Reference evidence: Product Scope section 19 records the 05/10 mentor UI survey. A menu, screenshot, metric card or price is evidence of a displayed surface, not proof of the reference's backend correctness. No credentials belong in this handoff.

Execution constraints:

- Laravel modular monolith; MySQL only; Blade/Alpine/Tailwind/Vite; database queue. Use current dependencies. Package additions, Redis, websockets, hosting and real payments require a separate decision.
- Keep private, email-invited multi-community access. The URL selects the community; each server request authorizes current access. Never use session/current-community state as the only access check.
- Creator operates its own communities. A reference community admin workspace maps to Creator tools, not a new privileged actor. Platform Admin remains a separate moderation role with no content bypass.
- Necessary functional UI is in scope: controls, navigation, errors, loading and empty states, and mobile usability. Use existing styles/components. No broad visual redesign, React conversion or replacing views to resolve conflicts.
- F-01…F-19 are local draft identifiers, NOT Linear issue IDs. Create only the next workable batch in Linear using the section 12.1 template. Do not assign fictional EUR numbers, pre-check AC or record planned behavior as complete.
- New table/class/route/test names below are proposals. Existing anchors are listed in section 17.4. Recheck source once per active issue; do not reinvestigate the whole repository for every edit.
- All draft Evidence sections start Pending. Nothing here authorizes commit/push/PR/merge/Done. Apply the shared publication/review contract when an issue is implemented.

### 17.2 Reference coverage and honest parity

| Reference surface | Evidence level | Current baseline/gap | Completion slice | Parity boundary |
|---|---|---|---|---|
| Multi-community switcher | Observed switching header/feed | Dashboard lists permitted groups; no contextual dropdown | F-01 | Private permitted groups only |
| Community/Classroom/Calendar tabs | Observed | Core routes exist; workspace navigation is fragmented | F-01 | Preserve current content and direct links |
| Members and member profile | Tab/profile menu observed; detail workflow unverified | Creator-only management list; account settings are basic | F-02, F-14 | Separate member directory from creator controls |
| About and community settings | Tab/admin menu observed | Metadata/cover/edit exist; no dedicated About/rules workflow | F-03 | About is private, not a public sales landing page |
| Feed/categories/community information | Screenshot reference | Posts/comments exist; categories, pinning and sidebar context missing | F-04 | Plain text; no arbitrary HTML editor |
| Reactions and saved posts | Proposed community improvement | Missing | F-05 | Not claimed observed on mentor backend |
| Header search | Screenshot reference; execution unverified | Missing | F-06 | Search only authorized content in selected community |
| Calendar month/list | Observed | Paginated chronological event list exists | F-07 | Keep UTC, IANA zones, cancellation privacy |
| Notifications | Icon in screenshot; workflow unverified | No product notification center | F-08 | Database notifications first; no push/realtime claim |
| Welcome/onboarding tasks | Screenshot reference | Missing | F-09 | Persist verified actions; video acknowledgment is not watch proof |
| Levels/points/leaderboard | Tab/card reference; scoring not audited | Missing | F-10 | Explicit local scoring contract, not a copied unknown formula |
| Community admin overview/statistics | Observed cards/charts | Platform Admin status counts exist, not creator business analytics | F-11 | Real queries; Test Mode amounts labelled simulated |
| Orders and member purchase history | Admin menu observed; order workflow unverified | Payment attempts/status exist; no history/creator list | F-12 | Immutable payment evidence, no manual success button |
| Content/reports | Admin menu observed; moderation workflow unverified | Creator can delete content; no report queue | F-13 | Creator handles tenant content; platform privilege unchanged |
| Profile/settings/help | Member menu observed | Name/email/password settings exist; help/preferences limited | F-02, F-14 | Extend existing account workflow |
| Individual course purchase | Course purchase button observed; transaction unverified | Current payment grants community access only | F-15 | New entitlement/payment purpose needs a decision first |
| Monthly community price | Discovery card observed; charging unverified | One-time membership payment only | F-16 | Term renewal is not automatic recurring billing |
| Affiliate | Member/admin menus observed; attribution/payout unverified | Missing | F-17 | Test accounting only; no real payout or public access |
| Messaging | Icon in screenshot; functionality not surveyed | Missing | F-18 | Proposed asynchronous tenant chat; optional pending decision |
| Discover/explore | Observed two public listing cards | Public listing conflicts with confirmed private scope | F-01 private variant | Search/filter My Communities; full public parity requires scope reversal |
| End-to-end functional acceptance | Our release requirement | Per-module MVP baseline exists | F-19 | Prove integrated behavior, not only number of menus |

The target is a complete set of agreed workflows, not artificial parity claims. Public discovery, automatic subscriptions, real affiliate payouts and messaging cannot be represented as implemented merely by drawing a button. Resolve the explicit choices below; this plan accounts for them without silently dropping them.

### 17.3 Delivery batches, dependencies and scheduling

Keep M1–M5 intact. Proposed additional milestones are C1–C5, with outcome gates below. No start/target dates are invented: schedule from actual remaining capacity and the agreed submission date. Begin with F-01 and F-02; open only enough issues for current WIP.

| Gate | Exit outcome | Draft issues |
|---|---|---|
| C1 — Complete community workspace | A user switches groups, sees the correct tabs/directory/About and organized feed | F-01…F-04 |
| C2 — Member engagement and learning experience | Search, reactions, calendar, notifications, onboarding and leaderboard operate with tenant isolation | F-05…F-10 |
| C3 — Creator operations | Statistics, orders, reports and member account/help workflows use persisted data | F-11…F-14 |
| C4 — Agreed commercial extensions | Every selected commerce workflow passes its own entitlement/accounting contract | F-15…F-17; F-18 only if selected |
| C5 — Functional completion accepted | End-to-end demo, regression/CI and independent setup evidence close the chosen coverage matrix | F-19 |

Suggested metadata, not live assignments or elapsed-time promises: Priority High for F-01/02/04/08/10/12/13/19; Medium for F-03/05/06/07/09/11/14. Commercial slices are unestimated until their decisions are closed. Use S/M/L for planning (approximately 2/3/5 points if the team uses points), not hours. Actual metadata is set in Linear. Labels reuse existing `area:*`; add a release label only when creating the backlog. Do not change cycles or old milestone dates here.

| Draft | Size | Technical blocked-by | Exact downstream handoff |
|---|---|---|---|
| F-01 | M | Merged MVP contracts | Shared workspace component and route map for new member/creator pages |
| F-02 | M | F-01 | Authorized directory/profile contract for search and messaging |
| F-03 | S | F-01 | About/rules/onboarding settings for F-09 |
| F-04 | M | F-01 | Category/pin queries and introduction category for F-09 |
| F-05 | M | Existing Feed | Transactional like facts for F-10; save list is independent |
| F-06 | M | F-02, F-04 | Directory/category-aware search; existing Classroom stays read-only |
| F-07 | M | Existing Events | Month/list route and timezone contract |
| F-08 | L | Existing Feed/Learning/Events | Database notification delivery/read service reused by F-13; unrelated new producers need not block it |
| F-09 | M | F-03, F-04 | Persisted onboarding steps and automatic comment/introduction checks |
| F-10 | L | F-05 | Idempotent point ledger and tenant ranking |
| F-11 | M | Existing Feed/Learning/Payments | Defined aggregate queries reused by overview; no artificial wait for F-10 |
| F-12 | M | Existing Payments | Read-only order/history views; no artificial wait for F-11 |
| F-13 | L | Existing Feed, F-08 | Creator report queue and safe report notification |
| F-14 | S | F-08 | Account notification preferences and help entry |
| F-15 | L | Decision D-01, F-12 | Course entitlement and payment-purpose contract |
| F-16 | L | Decision D-02, F-12 | Term expiry/renewal access contract and real scheduler behavior |
| F-17 | L | Decision D-03, F-12; F-15/16 only if their purchases are commissionable | Referral attribution and simulated commission ledger |
| F-18 | L | Decision D-04, F-02 | Conversation/message isolation and inbox contract |
| F-19 | M | All selected slices merged and their AC evidenced | Release acceptance with the final coverage matrix |

Execution order is not always a technical dependency. For a single implementation agent, a convenient order is F-01 → F-02 → F-03 → F-04 → F-05 → F-07 → F-08 → F-09 → F-10 → F-06 → F-12 → F-11 → F-13 → F-14, then selected commercial/chat work, then F-19. Audit each coherent slice before building consumers of its changed contract. Do not wait for the entire release to audit.

If multiple developers work, separate Community/navigation (F-01…04), Member interaction (F-05/08/09/10) and Creator operations (F-07/11/12) once their actual prerequisites merge. Coordinate `routes/web.php`, models and layout edits; do not split one issue into unrelated backend and UI owners. A sub-issue is warranted only for independent acceptance, not for every file edit. F-10's ledger and leaderboard, or F-15's approved payment-purpose contract and course checkout, may be separate accepted sub-issues if the parent AC remains intact.

### 17.4 Source map and shared implementation contracts

Existing anchors verified in this checkout:

| Concern | Existing source to reuse |
|---|---|
| Route hierarchy | `routes/web.php`: community slug and scoped nested resources; account, invitation, payment and platform routes |
| Accessible dashboard | `app/Http/Controllers/CommunityController.php`, `app/Models/Community.php::scopeAccessibleTo()` |
| Community access | `app/Policies/CommunityPolicy.php::view/update`; ACTIVE tenant plus creator/ACTIVE membership |
| Page shell | `resources/views/layouts/navigation.blade.php`, `resources/views/communities/show.blade.php`, existing `resources/views/components/` |
| Feed writes | `app/Actions/CommunityContent/FeedMutation.php::run`, `PostController`, `Post`, `Comment` |
| Member management | `CommunityMemberController`, `app/Actions/Communities/TransitionCommunityMembership.php`; creator-only membership list/actions |
| Learning | `ClassroomController`, `Course`, `LessonProgress`, `app/Services/LessonProgressMutation.php::run` |
| Calendar | `EventController`, `Event`, `app/Support/EventWallTime.php`; existing chronological list and IANA-timezone validation |
| Payments | `app/Actions/Payments/InitiatePaidCheckout.php`, `app/Http/Controllers/Payment/`, `app/Services/Payment/SePayGateway.php`, `Payment` |
| Account | `ProfileController`, `ProfileUpdateRequest`; current name/email/password flow and retention safeguards |
| Operations | README, `scripts/sync-local.sh`, `scripts/verify-fresh-setup.sh`, `.github/workflows/ci.yml` |

Implementation rules for every slice:

1. Resolve the community from the route, authorize it, scope children from that community, then authorize the resource. Member-denied tenant/resource URLs return 404 consistently; do not disclose draft/private names in suggestions, counts, notifications or errors.
2. For writes susceptible to access revocation, re-read and lock current state inside the transaction. Preserve existing lock order in the affected flow. Feed currently locks community → membership → post → comment; payment/learning use their own established ordering. New dependent rows come after the parent locks. Do not invent one global lock order by rewriting all modules.
3. New tenant tables have a `community_id`, relevant foreign keys, query indexes and uniqueness for replay/concurrency. Mutating endpoints use CSRF and applicable throttling; never change state through GET.
4. Use forward migrations. Never edit an applied migration or use `migrate:fresh` on development data. Add factories and synthetic demo records only where needed, using the existing additive/idempotent seeder pattern. Durable financial/moderation/audit records must not disappear through profile deletion.
5. Bind user IDs, community IDs, recipients, amount, points and privilege fields on the server. Reject or ignore unauthorized submitted fields explicitly. Blade escapes text; no raw HTML, unvalidated iframe or arbitrary URL fetch/proxy.
6. Paginate lists, cap search/filter inputs, select necessary columns and avoid per-card queries. Prefer MySQL aggregates and simple indexed relations over a new search/cache service. Add optimization only for a measured query problem.
7. New screens use the workspace component from F-01. No tab links to absent routes. Every new surface has an empty state and a visible failure state. Use semantic controls/keyboard focus on mobile and desktop.
8. Transactions establish core facts. Notification/other optional side effects happen after commit and cannot turn a successful payment into a failed entitlement. Queue jobs store safe record IDs, not secrets or rich snapshots of private content; retries need a dedupe key and fresh access checks.
9. Update Product Scope when capability scope changes, Domain & Architecture when a shared data/access contract changes, and the active Working Context section with technical progress. Do not copy this entire plan into checkpoints or create a second roadmap.

### 17.5 Common verification and evidence contract

Every issue below inherits this section AND the section 12.1 issue template; do not omit its sections when creating an issue in Linear. Suggested new test names are not existing tests or evidence. After implementation, substitute the actual test class in the targeted command.

```bash
# During the current slice: actual test class, guarded MySQL scool_test only.
docker compose -p scool exec -T app php artisan test --filter=ActualFeatureTest
docker compose -p scool exec -T app composer pint:test
npm run build

# Before integrated acceptance/publication, following README prerequisites.
bash scripts/verify-fresh-setup.sh --mode verify
```

Use the required CI checks on the published head; the verify script does not replace dependency/security/static-analysis gates in CI. For documentation-only planning, use agent-contract and diff checks; no application rebuild or database writes are needed.

Automated cases for EVERY new resource: permitted actor success; invalid input without mutation; different tenant/creator/author; guest/unverified where applicable; PENDING_PAYMENT/SUSPENDED/REMOVED/LEFT member; SUSPENDED/ARCHIVED community; spoofed request IDs; relevant rollback/replay/concurrency. Test revocation during a mutation when the slice adds a transactional access-sensitive write. Reuse existing fixtures and safety guards rather than resetting the real database.

Manual script for every UI slice: list its happy path, validation/empty state, direct unauthorized URL, reload/back behavior and a small-screen check with expected results. Supply one consolidated checklist per PR so acceptance does not become a sequence of undocumented follow-up questions. Two sessions/tabs test tenant context; duplicate submissions test stateful writes. Use synthetic identities; do not share full inboxes, invitation fragments, cookies, webhook payloads or credentials.

Each issue's Evidence section contains: Pending implementation commit/PR; Pending targeted result and exact revision; Pending integrated/build/CI result; Pending manual result/screenshots; Pending review; remaining gaps/approved exceptions. A passing earlier issue is not new feature evidence. Changes to the PR head invalidate affected evidence. Keep frontend behavior and backend authorization evidence separate.

### 17.6 Issue drafts — community workspace

#### F-01 — Contextual community workspace and switcher

**Outcome:** Creator/Member switches between permitted communities and opens Feed/Classroom/Calendar from a consistent workspace with the correct community context.

**Scope:** Includes shared workspace navigation, current community label/cover, permitted-community dropdown and a searchable/paginated My Communities view. Excludes public discovery, a persistent client-only tenant selector, or a new design system.

**Workflow/contracts:** Extend the existing accessible dashboard; create a reusable workspace Blade component. `/communities/{slug}` remains the existing overview entry point; do not silently change its route meaning. Explicit Feed/Classroom/Events URLs remain valid. Choose another community → open its Feed; do not carry a course/post/event ID to the new tenant. ACTIVE owned/joined communities appear in the switcher. Inactive owned communities may remain visible with status on My Communities but never yield active workspace access. Filter dashboard by owned/joined/all permitted groups and name; creator+member overlap must not duplicate a card.

**AC:**

- [x] AC-01: User in A and B selects B; label, links and Feed use B; reload/back retain the URL-selected tenant.
- [x] AC-02: Non-member C appears neither in the dropdown nor search results/counts; direct C URL returns 404.
- [x] AC-03: Membership removed after page load prevents access on the next request; cached dropdown state never grants access.
- [x] AC-04: A and B open in separate tabs work independently; inactive owned groups show status only on the dashboard.
- [x] AC-05: Existing overview, cover, invitations, member-management and edit links still work; empty dashboard/mobile menu are usable.

**Dependencies:** Blocked by merged MVP only; blocks shared navigation integration for F-02/03/04. Related to F-07/12, not an artificial backend blocker for them.

**Security/failure cases:** Query from `accessibleTo`, then apply workspace eligibility; authorize every destination independently. No global session tenant controlling a second tab.

**Implementation/verification:** Implemented `<x-community-workspace>` component, switcher dropdown, and dashboard filtering/search in `CommunityController@index`. Tests: `tests/Feature/CommunityWorkspaceSwitcherTest.php` (6 tests / 38 assertions PASS). Evidence: candidate branch `f01-f02-workspace-directory`, manual and automated verification complete.

#### F-02 — Member directory and community-visible profiles

**Outcome:** An active member sees who belongs to its community and opens a safe profile; Creator retains its separate management tools.

**Scope:** Includes paginated ACTIVE-member directory, name search, creator identification, display name/bio/avatar profile and account editing of those profile fields. Excludes emails, billing/access audit details in member responses, online presence claims and a global public user directory.

**Workflow/contracts:** Keep existing `/members` creator management route/semantics. Proposed separate member-facing `/directory` and `/directory/{user}` use F-01 navigation. Creator can appear without manufacturing a membership. Directory profiles require the target to be creator or ACTIVE member of the same tenant. Global bio/avatar edits belong to the authenticated user; changing them affects all communities and the form says so. Bio plain text, max 500 characters; avatar JPEG/PNG/WebP max 2 MB, generated filename, validated dimensions/MIME. Serve avatars through an authorized user/community route, not a public storage URL. Reuse cover validation/cleanup principles, not its tenant-specific stored paths.

**AC:**

- [x] AC-01: A's member sees A's creator and ACTIVE members only; same names in B do not leak through search/profile IDs.
- [x] AC-02: Member cannot see emails, pending/removed membership rows, reasons or management actions in HTML/JSON.
- [x] AC-03: User updates its bio/avatar; reload displays it; invalid image/oversize/spoofed user or role input changes nothing.
- [x] AC-04: Removed target disappears; removed viewer cannot fetch profile/avatar even via an old URL.
- [x] AC-05: Existing creator management remains functional; no membership is created for the creator merely to display a profile.

**Dependencies:** Blocked by F-01 for workspace integration; blocks directory search in F-06 and messaging identity checks in F-18.

**Security/failure cases:** Add dedicated directory/profile authorization; never weaken creator-only CommunityMemberController to serve members. Preserve email verification/password behavior. File replacement cleans old images only after commit; no remote avatar fetching.

**Implementation/verification:** Added forward migration for bio/avatar_path; implemented `DirectoryController` (`index`, `show`, `avatar`), `ProfileUpdateRequest`, and `ProfileController@update` with transactional file cleanup and `private, no-store` caching. Tests: `tests/Feature/MemberDirectoryProfileTest.php` (8 tests / 94 assertions PASS). Evidence: candidate branch `f01-f02-workspace-directory`, manual and automated verification complete.

#### F-03 — About, rules and functional community settings

**Outcome:** Members read a community's purpose/rules; Creator maintains that information and onboarding configuration through existing settings.

**Scope:** Dedicated About view, plain-text rules and optional validated introduction video URL. Reuse name/description/cover/access-mode/price settings. No public landing page, membership-plan system, branding editor or ownership transfer.

**Workflow/contracts:** Proposed `/about` is tenant-authorized. Creator edits `rules` (max 5,000 characters) and optional YouTube/Vimeo introduction URL through the existing edit form; reuse VideoEmbedService validation and generated provider embeds. Description keeps its existing length contract. Show active member count and creator identity without emails; omit fake online metrics. Add forward nullable columns; old communities remain valid. Access-mode/price edits must preserve payment amount snapshots and current entitlements.

**AC:**

- [x] AC-01: Creator saves rules; member reads the same escaped text after reload.
- [x] AC-02: Outsider/removed member cannot read About, cover or introduction embed metadata.
- [x] AC-03: Invalid provider URL/raw HTML/overlong rules produce visible feedback and no partial update.
- [x] AC-04: Existing communities without rules/video show helpful empty states; existing cover/payment settings keep their behavior.
- [x] AC-05: Counts match active persisted membership data; no invented online/revenue values appear.

**Dependencies:** Blocked by F-01; blocks onboarding settings in F-09. Related to F-15/16 pricing decisions only.

**Security/failure cases:** Creator-only settings; do not mass-assign ownership/visibility/state. No URL fetch/iframe HTML. Do not modify pending payment amounts when changing prices.

**Implementation/verification:** Added forward migration for `rules` and `intro_video_url`; implemented `communities.about` route/view, `CommunityController@about`, rules validation and `VideoEmbedService` embed rendering. Tests: `tests/Feature/CommunityAboutSettingsTest.php` (5 tests / 41 assertions PASS). Evidence: candidate branch `f01-f02-workspace-directory`, manual and automated verification complete.

#### F-04 — Feed categories, filters and pinned posts

**Outcome:** Members find discussions by category and see creator-pinned announcements without losing existing post/comment behavior.

**Scope:** Creator-managed categories, category assignment/filter, pin/unpin and newest/most-commented sort. No rich HTML, attachments, scheduled posts or reaction score sorting in this slice.

**Workflow/contracts:** Proposed PostCategory table is tenant-scoped; posts gain nullable category and pinned timestamp via forward migrations. Category name max 60, unique normalized name per community; default categories General, Announcements, Questions and Introductions are created for a new community, with additive setup for existing ones. Any poster selects a category; Announcements is creator-write-only. No category is required for old posts. Deleting a category assigns its posts to uncategorized; do not delete posts. Pin/unpin is creator-only and idempotent. Sort pinned first, then chosen order with ID tie-break; pagination preserves category/sort. Edit via FeedMutation with fresh parent/category authorization.

**AC:**

- [x] AC-01: Posts in Questions appear in that filter; unassigned old posts remain accessible under All/Uncategorized.
- [x] AC-02: B category ID submitted in A is rejected without a post/category change; member cannot post an Announcement or pin.
- [x] AC-03: Pin repeats create no extra state; pinned posts remain first across pagination and filters.
- [x] AC-04: Delete category preserves post/comments; edit/delete author and creator permissions remain unchanged.
- [x] AC-05: Unsupported sort/category and duplicate names return defined validation feedback, not an unbounded/raw SQL query.

**Dependencies:** Blocked by F-01; blocks F-06 category-aware search and F-09 introduction task. F-05 can implement likes independently.

**Security/failure cases:** Reuse FeedMutation and soft-delete/policy contracts. Never trust category ownership from form options; parameterize ordering through an allowlist.

**Implementation/verification:** Added forward migration for `post_categories` and `posts.category_id`/`pinned_at`; implemented `PostCategoryController`, seeded default categories, read-only GET feed, locked community guards, announcements creator-write restriction, and creator category management UI. Tests: `tests/Feature/CommunityFeedCategoriesTest.php` (8 tests / 75 assertions PASS). Evidence: candidate branch `f01-f02-workspace-directory`, manual and automated verification complete.

### 17.7 Issue drafts — member engagement and learning experience

#### F-05 — Idempotent likes and personal saved posts

**Outcome:** Creator/Member likes a visible post and privately saves it for later; repeated requests do not inflate counts.

**Scope:** A single Like reaction on posts, like count and a personal saved-post list within the community. No comment reactions, emoji palette or points in this issue.

**Workflow/contracts:** Proposed PostLike/PostBookmark rows carry tenant/post/user and unique `(post_id, user_id)` constraints. Explicit Like/Unlike and Save/Unsave actions replace a blind toggle, so retries are idempotent. Keep a Like row's identity and original timestamp across Unlike/re-like using an active flag; counts include active rows only. This preserves F-10's one-contribution identity. No self-like; self-bookmark allowed. Saved posts remain personal, paginated and filtered by current resource visibility. Post deletion or access revocation hides saved entries; saved membership never grants access. Persist the like fact transactionally for later F-10 integration; do not implement points before its contract.

**AC:**

- [x] AC-01: First Like increments by one; replay/concurrent Like leaves one row/count; Unlike repeats remain zero.
- [x] AC-02: Self-like is rejected without count change; another user's ID cannot be used as liker/bookmarker.
- [x] AC-03: Saved list shows only the requesting user's visible posts; another member cannot enumerate it.
- [x] AC-04: Deleted post, suspended tenant or removed member denies mutations and removes content from visible saved results.
- [x] AC-05: Counts and pressed-button state survive reload; stale UI failure shows feedback rather than pretending success.

**Dependencies:** Existing Feed only; blocks F-10 scoring. Related F-08 can add notifications later without redesigning reactions.

**Security/failure cases:** Scoped post binding; fresh transaction access checks; unique constraint is the concurrency backstop. Avoid double optimistic increments.

**Implementation/verification:** Implemented PostReactionController (`like`, `unlike`, `save`, `unsave`, `saved`), PostLike, PostBookmark, scoped eager-loading (`likes` loads only viewer active like, `active_likes_count` counts all active likes), and button/saved views. Tests: `tests/Feature/CommunityPostReactionsTest.php` (11 tests / 101 assertions PASS). Evidence: candidate branch `f01-f02-workspace-directory`, manual and automated verification complete.

#### F-06 — Community-scoped search

**Outcome:** A member searches posts, published courses/lessons and permitted member profiles in the selected community.

**Scope:** One search page with Posts/Courses/Lessons/Members result tabs and pagination. No external search engine, cross-platform discovery or search of private financial/messages data.

**Workflow/contracts:** Proposed `/search?q=...&type=...`; query 2–100 trimmed characters, allowed type, bounded page size. Start with parameterized MySQL LIKE on selected fields; escape literal `%`, `_` and the escape character. Search at most the selected result type per page. Members see only published course/lesson ancestry; Creator may search its drafts with explicit draft badges. Snippets are escaped, length-capped plain text. Counts use the same permission filters as results. Preserve q/type across pagination.

**AC:**

- [x] AC-01: A query returns matching A post/course/lesson/member and correct direct links; B content and counts are absent.
- [x] AC-02: Member sees neither draft course/lesson result nor a snippet from unpublished content; Creator sees its own draft badge.
- [x] AC-03: SQL-like strings/wildcards, invalid type and too-short/long q cannot widen scope or run raw SQL.
- [x] AC-04: Revoked user cannot search; a result unpublished/deleted after rendering is denied when opened.
- [x] AC-05: Pagination/zero-result states work; no per-result query loop or loading every table into memory.

**Dependencies:** F-02 directory/profile and F-04 category contracts; no dependency on payments/points.

**Security/failure cases:** Authorization on both query and destination; profile field allowlist; no global search fallback.

**Implementation/verification:** Implemented SearchController (`index`), route `communities.search`, tabbed view `communities.search`, and workspace navigation tab. Tests: `tests/Feature/CommunitySearchTest.php` (5 tests / 62 assertions PASS). Evidence: candidate branch `f01-f02-workspace-directory`, manual and automated verification complete.

#### F-07 — Calendar month and list navigation

**Outcome:** Member views upcoming events as a month grid or chronological list and opens the existing event details.

**Scope:** Month navigation, explicit display timezone, month/list choice and cancelled-state labels. Existing create/edit/cancel remains. RSVP, reminders, recurrence and native calls are separate proposals, not hidden additions here.

**Workflow/contracts:** Reuse existing Event records and event routes. Add allowlisted `view=month|list`, strict `month=YYYY-MM` and validated IANA `display_timezone` (default Asia/Ho_Chi_Minh; retain it in links). Month buckets use that displayed timezone; date range converts to UTC before bounded queries. Label the calendar zone and each event's original timezone. Include an event if its interval overlaps the displayed month; cap month grid payload and offer a paginated list when dense. Cancelled events stay labelled and omit member meeting links. No browser-local silent conversion or UTC reinterpretation.

**AC:**

- [x] AC-01: Event near UTC midnight appears on the correct day in the selected display zone; details show its original zone.
- [x] AC-02: Next/previous month and list switching preserve tenant/timezone; reload produces the same range.
- [x] AC-03: End-of-month/overlapping event is included once with defined start/end rendering; invalid month/zone is rejected.
- [x] AC-04: Cancelled event remains visible without member meeting URL; outsider/removed member sees no grid/data.
- [x] AC-05: Existing DST validation and creator event actions pass; small-screen list remains usable.

**Dependencies:** Existing Events; shared navigation integration uses F-01 when available. No need to wait for notifications.

**Security/failure cases:** Server-authorized range queries; no client dump of all events/meeting URLs. Preserve existing EventWallTime conversion and cancellation semantics.

**Implementation/verification:** Updated `EventController@index`, `Event` model (`startsAtInTimezone`, `endsAtInTimezone`), and `resources/views/communities/events/index.blade.php` with month grid, list navigation, bounded range queries, and timezone conversion. Tests: `tests/Feature/CommunityEventCalendarTest.php` (4 tests / 24 assertions PASS), `tests/Feature/CommunityEventTest.php`, and `tests/Feature/CommunityEventRegressionTest.php` (28 tests total / 147 assertions PASS). Evidence: candidate branch `f01-f02-workspace-directory`, manual and automated verification complete.

#### F-08 — In-app notification center

**Outcome:** A member sees new replies relevant to its posts; Creator sees new membership activity; users mark their own notifications read.

**Scope:** Database notification inbox, unread badge, individual/read-all actions and safe deep links. Producers: reply to own post (excluding self), accepted/activated member to creator, newly published course to current active members. No realtime/push, SMS, bulk email or per-lesson notification flood.

**Workflow/contracts:** Proposed database notification table and record-ID-based producer actions. Reply/member facts enqueue notification creation after commit; course producer fires on DRAFT→PUBLISHED, not every edit. Persist a dedupe key for `(event/source revision, recipient, notification type)` so retries do not duplicate notifications; publish/re-activation transitions need a persisted transition ID/sequence rather than a timestamp invented during delivery. A fan-out job rechecks eligible recipients and tenant state, processes bounded chunks and uses the existing database queue. Store safe type/source IDs; render summaries after fresh access checks. Hide unavailable tenant/content notices and derive the visible unread count from the same filters. Stale deep links show an unavailable state without leaking names. Mark-read accepts only the authenticated user's notification; read-all is bounded to its visible inbox.

**AC:**

- [x] AC-01: Another member replies; author receives one notice after commit; own reply sends none.
- [x] AC-02: Delivery retry/duplicate source emits one notification per recipient; rolled-back write emits none.
- [x] AC-03: Mark-read/read-all cannot change another user's notification; visible badge updates and persists after reload.
- [x] AC-04: Removed member, unpublished course, deleted post or suspended community yields no private notice/snippet/unread-count leak.
- [x] AC-05: Course editing while already Published does not repeat publish notification; retry handles only eligible recipients.
- [x] AC-06: Queue stopped leaves persisted business facts valid; restarting processes queued work without duplicates.

**Dependencies:** Existing Feed/Learning/membership transactions; blocks F-13 report notification and F-14 preferences. New category/calendar views are unrelated.

**Security/failure cases:** No raw content/tokens/email snapshots in job payload. Optional side effects cannot roll back completed payment or grant access. New inbox routes authorize ownership, not only UUID secrecy.

**Implementation/verification:** Created forward migration for `notifications` table with unique `dedupe_key` and `courses.published_revision`; implemented `AppNotification` model (availability filter, summaries, target URL, and deduplicated creation), queued jobs (`SendPostReplyNotification`, `SendMemberActivatedNotification`, `SendCoursePublishedNotification`), `NotificationController` (`index`, `markAsRead`, `markAllAsRead`, `follow`), inbox view, and workspace notification tab with unread count badge. Producers wired into `CommentController@store`, `AcceptInvitation@handle`, `TransitionCommunityMembership@handle`, `SePayWebhookController`, and `CourseController` (`store`/`update`). Tests: `tests/Feature/CommunityNotificationTest.php` (7 tests / 59 assertions PASS, including real database queue worker probe). Evidence: candidate branch `f01-f02-workspace-directory`, manual and automated verification complete.

#### F-09 — Persistent community onboarding

**Outcome:** A member sees a welcome checklist and its completed steps survive reload/login; Creator controls whether it is enabled.

**Scope:** Acknowledged introduction video, first qualifying comment and introduction post. No badge system, forced tutorial, AI-generated onboarding or reward points in this issue.

**Workflow/contracts:** Use F-03 introduction URL/settings and F-04 stable introduction-category reference, not a mutable category name. Proposed unique `(community_id,user_id,step_key)` completion rows. Video step is explicitly labelled acknowledgment via a POST button; do not claim playback completion. Automatically mark first non-empty comment and first introduction post from persisted successful writes; steps are one-time historical facts and do not reset when a post/comment is deleted. Missing video hides the video step and recalculates the denominator. If introduction category is deleted, display an unavailable task and exclude it until Creator selects a replacement. Re-enable onboarding retains prior progress. No write from client-submitted completion percentages.

**AC:**

- [ ] AC-01: Fresh active member sees relevant incomplete steps; successful actions complete only matching steps.
- [ ] AC-02: Refresh/new login and repeated actions preserve one completion per step; another tenant's comment cannot complete A.
- [ ] AC-03: Invalid/rolled-back comment creates no completion; deleted source does not erase historical completion.
- [ ] AC-04: Disabled/missing-video/deleted-category settings have defined display/denominator behavior without dead links.
- [ ] AC-05: Outsider/removed member cannot read or mark tasks; Creator preview does not silently create membership.

**Dependencies:** F-03 settings and F-04 introduction category. Related F-10 but no points awarded for this checklist.

**Security/failure cases:** Derive automatic completions server-side after successful facts; enforce tenant/user uniqueness and fresh access. No forced content mutations merely to populate demo progress.

**Implementation/verification:** Proposed OnboardingStepCompletion model/action plus a Feed welcome partial; minimal producer hooks. Test action-to-step mapping/settings/replay. Manual: acknowledge → comment → introduction → reload/relogin → second community. Evidence: Pending.

#### F-10 — Points, levels and community leaderboard

**Outcome:** Members see their tenant-specific points/level and a ranking derived from persisted engagement facts.

**Scope:** Simple received-post-like scoring, lifetime/current-month leaderboard, visible scoring explanation and personal progress to next level. No arbitrary points API, posting-spam rewards, course unlocks, reward currency or external scoring service.

**Workflow/contracts:** Recommended fixed rule: one point to a post author per distinct valid non-self Like; Unlike removes that Like's contribution; re-like restores the same contribution, not a fresh reward. Deleted post, ineligible liker/author or inactive membership excludes that contribution; reactivation restores it if the Like remains active. A community creator is eligible without a membership. Proposed PointContribution ledger has unique `(source_type,source_id)` and tenant/beneficiary/actor IDs; derived totals include only active source Likes, current eligible actors and non-deleted visible sources. Like/unlike updates the source/contribution in one transaction; one-time backfill uses existing valid Like rows without double counting. Store the source Like's original timestamp; monthly totals use contribution timestamps within UTC month boundaries labelled UTC. Levels use fixed thresholds 0/5/15/30/60 (levels 1–5); no creator-editable thresholds yet. Lifetime points determine level. Rank by points DESC then user ID ASC; exclude ineligible members before computing rank. Profile/leaderboard shows name/avatar only.

**AC:**

- [ ] AC-01: Valid Like yields exactly +1 for the correct A author, no points for liker and no B contribution.
- [ ] AC-02: Duplicate/concurrent Like, Unlike and re-like preserve one contribution and the expected 1→0→1 total.
- [ ] AC-03: Self-like/forged point value cannot award points; direct client total/level input has no effect.
- [ ] AC-04: Delete post or remove liker/author excludes its contribution; reactivation restores eligibility without duplicate rewards.
- [ ] AC-05: Lifetime/month boundaries, stable ties, zero points, thresholds and final-level state render correctly.
- [ ] AC-06: Backfill run twice preserves exact totals; ranking pagination and personal rank do not leak B/removed users.

**Dependencies:** F-05 valid Like fact and uniqueness contract. F-09 is related, not required. Blocks only gamification display in final acceptance.

**Security/failure cases:** Explain that querying eligibility avoids unsafe mass-reversal writes when membership changes. Add indexes and bounded SQL aggregation; do not loop through all users/posts in PHP. Immutable source identity prevents replay awards. Record this shared contract in Domain & Architecture with its implementation.

**Implementation/verification:** Proposed contribution model/action/query and Leaderboard page; extend reaction transaction minimally. Test lifecycle, replay, backfill and eligibility concurrency. Manual: A likes B's post → author's level/progress → Unlike/re-like → remove liker → switch tenant → rankings. Evidence: Pending.

### 17.8 Issue drafts — creator operations and account support

#### F-11 — Creator overview and truthful statistics

**Outcome:** Creator sees its own community's member, content, learning and Test Mode payment statistics with defined formulas.

**Scope:** Per-community overview cards, 7/30-day activity/payment table or simple existing-style chart, and learning-completion aggregates. No platform-wide data, forecasting, fake online counts, external analytics package or unbounded export.

**Workflow/contracts:** Proposed creator `/manage` overview links to existing Classroom, member management, invitations/settings and new Orders/Reports only after their routes exist. Counts: ACTIVE memberships (creator shown separately); non-deleted posts; published courses; successful Test Mode payment amounts for the selected date range. Payment total is labelled simulated successful receipts, not profit/real revenue. Completion formula: completed published lessons divided by ACTIVE member × published-lesson pairs in Published courses; no denominator returns 0 plus a no-data label. Exclude creator unless it has ACTIVE membership. Date filters use UTC inclusive start/exclusive next-day end, show zone, max 90 days. Aggregate without loading lesson bodies/payments into memory. Table is acceptable until chart polish is scheduled.

**AC:**

- [ ] AC-01: Fixed fixture with active/pending/removed members, drafts/deleted posts and success/cancel attempts yields exact stated counts.
- [ ] AC-02: Pending/cancelled/failed payments never contribute to successful receipts; currency/Test Mode label is explicit.
- [ ] AC-03: Draft/unpublished lessons and ineligible members do not affect the completion ratio; zero denominator never divides by zero.
- [ ] AC-04: A Creator cannot read B statistics; member/Platform Admin role alone cannot read this workspace.
- [ ] AC-05: Date boundaries/max range, empty state and pagination/table links work without per-member N+1 queries.

**Dependencies:** Existing modules only; F-01 integration when available. F-12/13 add workspace links as each merges; do not render premature routes.

**Security/failure cases:** Creator-only aggregate query; no raw provider payload or member private data in metrics. Additional commercial purposes must update metric definitions explicitly, not silently count new money as old membership receipts.

**Implementation/verification:** Proposed CreatorOverviewController/query/view; no migration unless demonstrated necessary. Tests assert exact formulas from synthetic data. Manual: compare cards to known records → range filter → member denial → B URL. Evidence: Pending.

#### F-12 — Creator orders and personal payment history

**Outcome:** Creator traces payment attempts within its community; payer sees its own purchase history and opens existing checkout/status when applicable.

**Scope:** Paginated creator orders/filter and own payment history/detail, read-only safe reference/state/amount/currency/date/purpose. No refund/payout, payment editing, manual success, raw provider data or accounting export.

**Workflow/contracts:** Treat each Payment row as an attempt; retries remain separate, explicitly related by invitation when available. Show successful receipts separately from pending/cancelled attempts. Existing purpose is community admission, even if a provider constant retains a historical Sandbox name. Proposed creator `/orders`; account `/payment-history`. Tenant details require active community+creator; payer may retain safe historical receipt access for its own payment after removal, but no community content/meeting link or private unrelated profile is exposed. Use stored amount snapshot, not community's current price. Pending checkout links honor current invitation/access/state rules; history never reactivates membership.

**AC:**

- [ ] AC-01: Creator sees only A attempts; filters/pagination by status/date preserve tenant scope.
- [ ] AC-02: Payer sees only its own attempts; a guessed reference/ID from another payer returns 404.
- [ ] AC-03: Price change leaves past amount unchanged; cancelled retry remains a separate attempt and successful payment counts once.
- [ ] AC-04: Safe history after removal exposes no raw payload/secrets/other payer data and grants no community access.
- [ ] AC-05: All order views are read-only; query params/client POST cannot modify status, amount or entitlement.

**Dependencies:** Existing payment source/authorization; blocks F-15/16/17 history integration. Related F-11 only.

**Security/failure cases:** Explicit response column allowlist; no `Payment::toArray()` dump of payload. Ensure creator historical access and payer receipt policy are different from member content access.

**Implementation/verification:** Proposed OrderController/PaymentHistoryController/views/policies or bounded reuse. Existing SePayPaymentTest and PaymentEntitlementRegressionTest remain required regressions for affected access logic. Manual: two users → own history → creator orders → filter/retry → other payer URL → removal. Evidence: Pending.

#### F-13 — Content reports and creator moderation queue

**Outcome:** Member reports an accessible post/comment; Creator resolves its community's reports with a reason and retained history.

**Scope:** Post/comment reports, OPEN/RESOLVED/DISMISSED queue, creator remove-or-dismiss action and safe report notification. No automated takedown, file scanning, global content visibility or delegated moderator role.

**Workflow/contracts:** Proposed ContentReport has tenant, reporter, target type/ID, reason/category, state and resolution metadata. Target type is an allowlist; target must belong to the route tenant and be visible. Reason 10–1,000 plain-text characters. One report per reporter/target is enforced by a unique constraint; replay returns existing state, never reopens a resolved report. Creator sees a paginated private queue; ordinary members do not see other reports/reporter identities. Resolve/remove soft-deletes target through existing author/creator policy and FeedMutation plus report transaction; dismiss changes only report state. Lock/recheck current state; retained target ID/type and audit reason survive content soft deletion. Concurrent resolution creates one terminal resolution. Platform Admin gets no tenant content bypass.

**AC:**

- [ ] AC-01: Report A content creates one OPEN record and one deduplicated creator notification; duplicate does not multiply rows.
- [ ] AC-02: Report B target, invisible/deleted source, empty reason or spoofed reporter is rejected without mutation.
- [ ] AC-03: A Creator removes/dismisses its report with reason; member cannot resolve/read queue and B Creator cannot act.
- [ ] AC-04: Remove retains report/history and existing soft-delete behavior; repeated/conflicting resolution cannot overwrite terminal outcome.
- [ ] AC-05: Removed reporter/closed target has defined unavailable display; sensitive report text is escaped and not leaked via notifications.

**Dependencies:** Existing Feed plus F-08 delivery service. Related F-11 workspace navigation.

**Security/failure cases:** Durable moderation records need restrictive retention and a clear profile-deletion guard where appropriate; do not add cascading deletion of history. Throttle report submissions. Do not create a generic unguarded polymorphic target lookup.

**Implementation/verification:** Proposed report model/actions/policy/controller/views and forward migrations; integrate safe F-08 producer. Test target ancestry, duplicate/terminal races, rollback, retention and moderation permissions. Manual: report → creator queue → dismiss one/remove another → re-submit → outsider/Platform Admin denial. Evidence: Pending.

#### F-14 — Account notification preferences and contextual help

**Outcome:** User controls optional in-app notification types and can find instructions for joining, learning, events and Test Mode payment.

**Scope:** Account notification preferences and a concise contextual help page linked from profile/workspace; reuse existing account/password settings and F-02 profile fields. No support-ticket SaaS, marketing email, push notifications or cosmetic theme system.

**Workflow/contracts:** Proposed per-user preference fields/record for reply and new-course notifications, default enabled. Creator membership/report operational notices remain enabled and described as required. Preferences affect future optional deliveries; existing notifications remain subject to normal access filters. Settings identify which options are global across communities. Help describes invite-only access, course publish rules, event zones, private groups and SePay Test Mode with no real transfer. Links use named existing routes; no credentials, tunnel URLs or raw tokens in the page.

**AC:**

- [ ] AC-01: User disables replies; future reply notice is not delivered, while another user's preference is unchanged.
- [ ] AC-02: Re-enable applies to future events without backfilling every historical reply; saved preference persists after login.
- [ ] AC-03: Required creator operational notices cannot be silently disabled; settings explain the distinction.
- [ ] AC-04: Invalid/spoofed user/preference fields cannot change account ownership/roles or bypass existing email verification.
- [ ] AC-05: Help links work in the user's role, mobile navigation is usable and payment instructions explicitly prohibit real transfer in Test Mode.

**Dependencies:** F-08; F-02 profile integration is related. Does not block unrelated functional modules.

**Security/failure cases:** Allowlisted preferences; no private support logs/emails dumped into help. Existing password/email/deletion retention tests remain valid.

**Implementation/verification:** Minimal ProfileUpdateRequest/ProfileController/settings and delivery preference check; forward schema if needed. Test default/off/on and role-field rejection. Manual: disable → produce new reply → check inbox → re-enable → help. Evidence: Pending.

### 17.9 Decision-gated issue drafts — complete commerce and messaging coverage

These are represented because the reference contains their surfaces. They are not Ready until the decision recorded below is resolved and the full contract is moved into Domain & Architecture/Linear. Core completion work may continue independently. Do not implement a fake feature in place of the unresolved contract.

| Decision | Recommended bounded contract | What must be explicitly selected |
|---|---|---|
| D-01 — Individual course sale | Active community membership remains required; courses are INCLUDED or ONE_TIME purchase; paid entitlement is course-specific and current publish/community/member state still gates reading | Adopt this contract and payment-purpose/retention changes, or explicitly exclude separate course sales from release acceptance |
| D-02 — Monthly access | If needed, use time-limited access plus manual SePay Test Mode renewal; display renew-by/expiry clearly | Term duration, whether existing one-time members are grandfathered, grace period (recommended none), and whether manual renewal is acceptable. Automatic recurring billing is a separate provider decision |
| D-03 — Affiliate | Creator enables test-only referral tracking and a fixed commission rate; attribution applies only to valid private invitations and verified successful test payment | Attribution window/last-or-first model, rate, eligible purchase purposes, self-referral/cancellation rules; real payouts remain deferred |
| D-04 — Messaging | Community-scoped two-person asynchronous messages with database storage; fetch on page refresh, no realtime or attachments | Include messaging in this release or leave it explicitly outside the reference-completion commitment, since the mentor functionality was not surveyed |

#### F-15 — Course-specific one-time purchase

**Outcome:** Active member buys a paid course in SePay Test Mode and receives only that course's entitlement after a verified transaction.

**Scope:** INCLUDED/ONE_TIME course access and whole-VND price, payment purpose/target, unique course entitlement, checkout/history and locked-course preview. No subscriptions, bundles, coupons, guest purchase, entitlement transfer or production money.

**Workflow/contracts (PROPOSED, blocked by D-01):** Active member sees published course title/price but no paid lesson body/embed/sidebar until entitlement exists. Owning creator can manage/preview drafts without buying. Payment distinguishes COMMUNITY_ACCESS and COURSE_ACCESS with explicit target validation and immutable amount/purpose snapshot; do not reuse invitation-based checkout by forging an invitation. Verified success issues unique `(community_id,course_id,user_id)` entitlement in the same payment transaction. Membership and course entitlement are separate facts. Admission payment never unlocks paid course; course payment never creates/reactivates community membership. Unpublish/revoke blocks reads but retains entitlement/progress. Price/mode changes preserve successful historical entitlements; mode ONE_TIME→INCLUDED permits current active members, INCLUDED→ONE_TIME requires explicit configured access without inventing paid rows.

**AC:**

- [ ] AC-01: Included course works unchanged; paid course card is visible but lessons/embed are locked before verified success.
- [ ] AC-02: Successful exact-reference/amount/currency test webhook unlocks exactly one course; duplicate delivery creates no duplicate entitlement.
- [ ] AC-03: Forged client price/purpose/course/user and wrong-tenant target never unlock; existing admission flow still passes.
- [ ] AC-04: Cancel/retry and delayed success follow immutable attempt rules; no client redirect grants entitlement.
- [ ] AC-05: Removed member or draft course blocks access despite entitlement; reactivation/republish restores retained eligible access.
- [ ] AC-06: Order/history shows purchase purpose and stored price; neither endpoint exposes another payer's course/payment data.

**Dependencies:** D-01 closed, F-12; affects course/lesson policies, progress, search, notifications, analytics and paid provider reconciliation. Blocks commercial acceptance, not basic Classroom.

**Security/failure cases:** Significant shared authorization/financial contract; explicit impact review and purpose-specific dispatch. Do not add course access by weakening community authorization. Preserve financial records on deletion. Document unsupported delayed/refunded states honestly.

**Implementation/verification:** Forward schema and purpose-specific actions behind current gateway; update every reading/progress/search path affected. Automated admission+course negative matrix, webhook replay/cancel races; real provider-backed Test Mode success for this new purpose. Manual: card lock → pay test → unlock → duplicate → suspend member → denial → reactivate. Evidence: Pending.

#### F-16 — Time-limited membership and manual renewal

**Outcome:** Member sees an access expiry and renews a selected membership term through SePay Test Mode, if D-02 is adopted.

**Scope:** Creator-configured term duration/price, access expiry, renewal attempt and visible expired/renewable state. Excludes automatic bank charging and inventing a subscription provider API.

**Workflow/contracts (PROPOSED, blocked by D-02):** Preserve existing one-time admission unless an explicit grandfathering/migration decision changes it. Recommended term: 30 calendar days as UTC instants; successful renewal extends from `max(now, current_expiry)` by the purchased immutable term. One paid attempt/event extends once. Expired is an access calculation (`expires_at <= now`), not permission to overwrite SUSPENDED/REMOVED/LEFT. Creator suspension stays authoritative; renewal must never restore a removed/suspended membership. Define whether late payment against an ineligible attempt is recorded as paid-without-access requiring review; do not silently reactivate. Rejoin still follows valid invitation policy. Every content/media/search/notification/directory path evaluates current expiry even if scheduler is late/stopped. Scheduler is for reminders/housekeeping only; a new runtime scheduler must be documented and tested. Pending notifications recheck expiry at delivery.

**AC:**

- [ ] AC-01: At exact expiry instant member loses internal access; safe renewal/status page remains available without content disclosure.
- [ ] AC-02: Valid renewal extends once from the correct base instant; duplicate/concurrent delivery never adds a second term.
- [ ] AC-03: Suspended/removed/left states are not reactivated by payment, scheduler or stale browser session.
- [ ] AC-04: Existing grandfathered one-time memberships retain explicitly agreed behavior; migration rerun is non-destructive.
- [ ] AC-05: Term/price edits do not change pending snapshots or old receipts; boundary/time-travel tests cover month/day cases.
- [ ] AC-06: Scheduler/queue offline cannot prolong unauthorized content access; Test Mode renewal and expiry feedback work on web.

**Dependencies:** D-02 closed, F-12; F-15 only if one unified payment-purpose schema is adopted first. Blocks final term-access acceptance.

**Security/failure cases:** Broadest tenant-access impact: add one reviewed access predicate and update all consumers, not scattered expiry checks. No fake recurring success from cron. Record billing semantics in an ADR before implementation.

**Implementation/verification:** Forward membership/attempt fields; approved purpose handling; expiry-aware policy/action/query integration and scheduler setup only if needed. Automated clock-boundary/provider replay/revocation tests; a live manual renewal test and offline-scheduler access probe. Manual: active term → expiry → denial → test renewal → restored eligible access → suspend → renewal cannot restore. Evidence: Pending.

#### F-17 — Private referral tracking and simulated affiliate ledger

**Outcome:** Eligible referrer sees attributed Test Mode commissions; Creator reviews its community's referral results without opening public community access.

**Scope:** Optional creator-enabled affiliate rate, opaque referral code, attribution via email-bound invitation and read-only commission ledger. No mass mailing, shared public invite acceptance, real payout/refund/tax workflow or arbitrary commission edits.

**Workflow/contracts (PROPOSED, blocked by D-03):** Recommended first valid referral attribution for 30 days, fixed percentage 0–30% configured by Creator. A referrer requests an invitation for a target email; Creator approves using the existing invitation contract before any delivery/access. Referral code alone never grants membership or reveals group metadata to outsiders. Persist inviter/referrer attribution at approved invitation time; successful payment snapshots the rate/beneficiary. Only verified successful eligible purpose generates one commission per payment; cancelled/failed/replayed attempts generate none. Self-referral and same payer/beneficiary are ineligible. Store whole-VND commission using floor(amount × rate / 100); do not use float money. Label all values simulated/unpaid. Define exclusion of zero-rate/free invitations explicitly.

**AC:**

- [ ] AC-01: Approved attributed invitation plus eligible successful test payment creates one exact rounded commission.
- [ ] AC-02: Referral code without valid invitation cannot discover/join; wrong email and self-referral yield no commission/access.
- [ ] AC-03: Replay/cancel/failed payment yields no extra commission; changing rate does not rewrite historical rows.
- [ ] AC-04: Referrer sees only its safe totals/records; Creator sees only own tenant; member cannot edit attribution/rate/status.
- [ ] AC-05: No UI claims payout completed or real earnings; retention survives account/content lifecycle without deleting ledger history.

**Dependencies:** D-03 closed and F-12; other payment-purpose slices only if their purposes are selected as eligible. Existing invitation delivery/security remains required.

**Security/failure cases:** Throttle invitation requests; creator approval prevents using the feature to spam arbitrary emails. No raw invite token/payment payload/payer email in affiliate views. Attribution is a financial fact, not a browser-local variable.

**Implementation/verification:** Proposed ReferralAttribution/Commission models/actions and creator/member pages, forward migrations and restrictive financial retention. Test approval/email binding/window/rate rounding/unique payment. Manual: request → approve → invited email accepts/pays test → ledger → replay → unrelated tenant. Evidence: Pending.

#### F-18 — Community-scoped asynchronous messaging

**Outcome:** Two currently eligible users in one community exchange private plain-text messages, if D-04 is selected.

**Scope:** One-to-one tenant conversation, inbox, paginated messages, send/read and refresh-based retrieval. No realtime/websocket, attachments, group chat, global contact lookup or online-status indicator.

**Workflow/contracts (PROPOSED, blocked by D-04):** Start conversation from F-02 directory. Community ID plus canonical sorted participant pair identifies a conversation with a unique constraint. Both participants must currently be active members or owning creator of the ACTIVE tenant. Same participants in A/B have separate conversations. Message body 1–2,000 escaped plain-text characters; client send ID is bound to sender/conversation and unique to avoid duplicate network retries. Only participants read; Creator/Platform Admin have no blanket access to others' chats. Revocation hides content and blocks send; retained messages do not authorize access. No body preview/unread counts for inaccessible conversations. No retention/purge claim without a separate decision.

**AC:**

- [ ] AC-01: A/B participants exchange and reload ordered paginated messages; same pair in a second tenant sees a separate inbox.
- [ ] AC-02: Non-participant/other tenant/Platform Admin role alone cannot read, send or enumerate conversation metadata.
- [ ] AC-03: Duplicate/concurrent first-conversation and repeated send ID produce one conversation/message, not duplicated chat.
- [ ] AC-04: Remove participant/suspend tenant blocks reads/sends and hides inbox previews/counts; failure never appears sent.
- [ ] AC-05: Oversize/HTML/spoofed sender and invalid recipient fail without mutation; throttled sends produce understandable feedback.

**Dependencies:** D-04 and F-02; F-08 only if chat notifications are separately selected, otherwise not a blocker.

**Security/failure cases:** Fresh participant/state recheck under locks; directory eligibility does not replace conversation authorization. Sensitive content must not be published in logs or audit screenshots.

**Implementation/verification:** Proposed Conversation/Message models/policies/actions/controller/views. Targeted participant/tenant/replay tests and two-session browser flow. Manual: start → reply → refresh → other participant denial → remove access. Evidence: Pending.

### 17.10 F-19 — Functional completion acceptance

**Outcome:** The selected reference-completion feature set works as one coherent product from a fresh setup, with explicit coverage and remaining differences.

**Scope:** Integrated regression, forward-upgrade/fresh-setup evidence, additive synthetic demo data, consolidated manual script and release handoff. Visual redesign remains a future release; this issue does not hide missing functionality behind attractive screenshots.

**Workflow/contracts:** Update the section 17.2 coverage matrix to implemented/accepted/explicitly excluded with decision links in canonical docs/Linear. Seed multiple tenants and synthetic roles without altering real data. Use existing setup/sync scripts; do not fork a new setup procedure. Update README demo instructions only for actual new workflows and runtime prerequisites. Commercial fixtures cannot fabricate provider success; use verified SePay Test Mode evidence or identify the provider check as unverified. A historical same-host clone does not count as an independent second machine.

**AC:**

- [ ] AC-01: Every selected F-xx issue has mapped AC, current implementation/PR, CI and manual evidence; unresolved commercial choices are explicitly resolved or excluded by a product decision.
- [ ] AC-02: Creator creates/maintains two private communities; switcher, directory/About, categories/search and all deep links remain tenant-correct in two tabs/back/reload.
- [ ] AC-03: Member onboarding/notifications/likes/leaderboard/learning/calendar operate together and survive login/retry; revoked/suspended access blocks every new surface.
- [ ] AC-04: Creator overview/order/report data agrees with persisted fixtures; account preferences/help and creator/member/Platform Admin permissions remain distinct.
- [ ] AC-05: Selected commerce workflows pass exact-purpose/amount/authentication/duplicate/delayed-state tests and provider-backed Test Mode success; no fabricated revenue/online metrics.
- [ ] AC-06: Guarded MySQL suite, style, production asset build and configured CI/security checks pass on final revision; no known Critical/High release blocker remains.
- [ ] AC-07: Fresh clone and setup pass on a second physical machine; upgrade from existing MVP data preserves content/payment/audit history and uses forward migrations.
- [ ] AC-08: README provides one consolidated manual checklist with expected results; required review evidence is linked and remaining UI-design work is labelled separately.

**Dependencies:** All selected completion issues and applicable outstanding baseline acceptance evidence. Related to existing EUR-15, but do not rewrite old evidence/status to imply this release already exists.

**Security/failure cases:** Recheck tenant isolation across new search, aggregates, avatars, notices, ranks, orders, reports and chosen chat/commerce. Apply data safety and privacy constraints to setup/demo artifacts. Do not disable CI, broaden admin policies or edit old test assertions to force acceptance.

**Verification/evidence:** Run one authoritative integrated Verify plus required current-head CI after final relevant changes; targeted issue tests run during iteration. Independent host and review are separate evidence. Pending commit/PR/results/manual/review; report exclusions/provider/setup gaps candidly.

### 17.11 Implementation-agent handoff and audit loop

Use a tool-neutral issue-sized prompt; shared files remain in English and do not depend on any personal IDE configuration:

For efficient reading, load shared sections 17.1/17.4/17.5/17.11 and the selected issue only; consult 17.2/17.3 for coverage/dependencies. Do not reread all other issue drafts before each implementation.

```text
Implement F-01 from docs/03_DELIVERY_PLAN.md section 17.6 only.
Read AGENTS.md, docs/AI_WORKFLOW.md (including Execution efficiency), and
the relevant docs/WORKING_CONTEXT.md section. Verify current branch/HEAD/status.
Use the live Linear issue contract once created; otherwise report this draft
as the supplied contract without inventing live metadata or acceptance.
Report readiness, then implement this one vertical slice using existing
Laravel/Blade/Alpine components. Preserve unrelated edits and current design.
Do not implement the other F-xx issues or introduce packages/runtime services.
Resolve only the current slice's approved schema/access contract; if a genuine
product decision is open, stop that part and identify the exact choice.
Add meaningful tenant/role/state tests on guarded MySQL scool_test.
Run targeted checks, production build and required acceptance checks; record
the actual results. Update only this slice's technical checkpoint.
Hand off changed files/contracts, migration impact, tests/revision, one complete
manual checklist with expected results, and unverified items. Publication and
Done follow the repository authorization/review contract.
```

Replace F-01/section with the selected slice. The receiving agent should not independently redesign the plan or implement all drafts in one prompt. During implementation, a small per-issue checklist is enough; do not create another planning directory or rewrite unrelated context sections.

Audit handoff should contain the exact base/head and dirty diff, issue contract, actual touched paths, proposed-vs-implemented decisions, targeted/full/CI results, migration/runtime impact, screenshot/manual script and evidence limits. Review findings use severity and a concrete reproduction. Fix confirmed in-scope defects, then rerun affected checks; do not repeat complete discovery/tests after documentation-only edits. Audit priorities: access/tenant consistency → transactional correctness → data/financial retention → user flow/error feedback → query limits → cosmetic concerns only if in scope.

The next implementation slice is F-01. Its dependencies are already in source; establish its Linear contract/metadata and branch before coding. The rest remains ordered backlog rather than simultaneous In Progress work.
