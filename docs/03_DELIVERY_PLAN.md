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

| Thành viên | Thông tin hiện có | Vai trò đề xuất | Không nên trở thành điểm nghẽn duy nhất |
|---|---|---|---|
| Hoàng | Chốt product/architecture, điều phối và theo dõi các module trọng yếu; muốn phát triển DevOps | Trực tiếp làm M1 Foundation và M2 Private Core; sau đó phụ trách Billing, local/CI và integration control | M1/M2 vẫn phải có reviewer; payment và setup phải có backup |
| Tiến | Chuyên fullstack | Hỗ trợ review M1; sau M2 phụ trách Community Feed, Events, Blade/Alpine UI và responsive integration | Không một mình sở hữu toàn bộ frontend |
| Khoa | Chuyên cyber, làm fullstack được; tư duy nhạy theo đánh giá của Hoàng | Learning/Events, Platform Admin, threat review và security tests | Security không chỉ review cuối dự án |

Hoàng là người giữ quyền quyết định cuối cùng về scope, architecture, thứ tự ưu tiên và readiness; đồng thời là driver trực tiếp của M1 và M2. Quyền quyết định không thay thế review kỹ thuật: Tiến review foundation, Khoa review multi-tenancy/authorization; owner của slice vẫn chịu trách nhiệm end-to-end gồm migration, policy, backend, UI và test.

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
| M1 — Foundation ready | 03/10/2026 | Hoàng; Tiến review | Compose, Laravel, MySQL, auth và production asset build chạy | Fresh setup chạy trên ít nhất hai máy; migrations/tests/build đạt |
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
| Product scope/architecture | Hoàng | Khoa và Tiến | Khoa | Decision chỉ nằm trong đầu Hoàng |
| Identity/Foundation | Hoàng | Tiến | Khoa | Auth/session regression hoặc local setup chỉ chạy trên một máy |
| Multi-tenancy/RBAC | Hoàng | Khoa | Tiến | Cross-community data leak |
| Community Feed/UI | Tiến | Khoa | Hoàng | Authorization và XSS |
| Learning/Progress | Khoa | Tiến | Hoàng | Publish/access inconsistency |
| Events | Tiến | Khoa | Hoàng | Timezone và invalid URL |
| SePay Sandbox | Hoàng | Khoa bắt buộc | Tiến | IPN spoof/replay/duplicate và tunnel config |
| Platform Administration | Khoa | Hoàng | Tiến | Privilege escalation |
| Local/CI/operations | Hoàng | Tiến | Tiến | Chỉ chạy trên máy Hoàng |
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
| EUR-8 | VS-04 Community Feed | M3 | Chờ mời Tiến vào workspace | Khoa | Backlog |
| EUR-10 | VS-05 Classroom Publishing | M3 | Chờ mời Khoa vào workspace | Tiến | Backlog |
| EUR-9 | VS-06 Lesson Progress | M3 | Chờ mời Khoa vào workspace | Hoàng | Backlog |
| EUR-11 | VS-07 Community Events | M3 | Chờ mời Tiến vào workspace | Khoa | Backlog |
| EUR-12 | VS-08 SePay Sandbox Access | M4 | Hoàng | Khoa | Backlog |
| EUR-13 | VS-09 Creator Member Management | M4 | Hoàng | Tiến | Backlog |
| EUR-14 | VS-10 Platform Administration | M4 | Chờ mời Khoa vào workspace | Hoàng | Backlog |
| EUR-15 | VS-11 Hardening & Local Demo | M5 | Chờ đủ team | Cross-review | Backlog |

Tiến và Khoa chưa xuất hiện trong workspace Linear tại thời điểm setup, vì vậy các issue dự kiến giao cho họ đang để unassigned; intended owner/reviewer đã được ghi trong issue description và project comment.

Chỉ tạo sub-issue khi phần việc có owner hoặc acceptance độc lập. Trước mắt chỉ phân rã VS-01 và VS-02; không nhập hàng chục sub-issue cho M3–M5 khi dependency chưa sẵn sàng.

#### Sub-issues Hoàng làm trước

VS-01:

- Bootstrap Laravel và authentication.
- Configure Blade, Alpine.js, Tailwind CSS và Vite.
- Tạo Docker Compose cho app/nginx/MySQL/Mailpit/worker theo architecture đã chốt.
- Configure database queue, `.env.example`, migrations và seed baseline.
- Thêm test/build command và kiểm tra fresh setup trên hai máy.

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

## Scope
- Bao gồm:
- Không bao gồm:

## Acceptance criteria
- [ ] Luồng thành công hoạt động
- [ ] Validation và failure state hoạt động
- [ ] Authorization và tenant boundary đúng
- [ ] Automated tests đạt
- [ ] Reviewer xác nhận

## Dependencies
- Depends on:
- Blocks:

## Evidence
- Pull request:
- Test result:
- Screenshot/demo:

## Reviewer
```

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
