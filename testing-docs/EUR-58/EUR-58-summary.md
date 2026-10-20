> Corrective artifact review — 2026-10-20. This is a partial historical sample on 6179e3e389ddc8041568bd56a114d96fa044d401. Seven submitted observations remain PASS; draft-content search is N/T because the screenshot is community discovery. No product-wide security verdict, confirmed fix, replay/concurrency guarantee or server-validation result follows from these screenshots.

# Báo cáo Tổng hợp Kiểm thử EUR-58 (QA-B2)
**Authorization, tenant isolation and failure-recovery validation**

---

## 1. Thông tin tổng quan (Execution Overview)
* **Mã Issue:** [EUR-58](https://linear.app/eurusdevsec/issue/EUR-58) (QA-B2)
* **Issue cha:** [EUR-53](https://linear.app/eurusdevsec/issue/EUR-53) (QA Track B)
* **Evidence gallery:** [EUR-58.md](EUR-58.md); personal .agent files are not a shared prerequisite.
* **Môi trường & Git Candidate:**
  * **Git Revision:** `6179e3e389ddc8041568bd56a114d96fa044d401`
  * **Hệ điều hành:** Windows (Local Docker Compose stack)
  * **Endpoint:** `http://127.0.0.1:8080`
* **Mục tiêu:** Kiểm thử các trường hợp phủ định (Negative testing): phân quyền (RBAC), cô lập giữa các Tenant (cross-tenant), chống rò rỉ siêu dữ liệu (metadata leakage prevention), bảo vệ nội dung nháp (Draft isolation), xử lý thao tác trùng lặp (Idempotency / Double-click) và khả năng khôi phục sau lỗi (Failure recovery).

---

## 2. Bảng thống kê kết quả (Execution Metrics)
* **Tổng số kịch bản:** 8
* **PASS:** 7 (historical submitted observations)
* **FAIL:** 0
* **BLOCKED:** 0
* **N/T (Not Tested):** 1 (tenant draft-content search not exercised)

*(Tuân thủ quy chuẩn: Số lượng các ca PASS, FAIL, BLOCKED, N/T được tách biệt độc lập).*

---

## 3. Bảng kịch bản kiểm thử tổng hợp (Scenario Matrix)

| Scenario ID | actor/state | preconditions | numbered steps | expected result | actual result | PASS/FAIL/BLOCKED/N/T | evidence | defect ID |
|---|---|---|---|---|---|---|---|---|
| **SC-EUR58-01** | Member (Tenant khác) | Đã đăng nhập, không có quyền thành viên tại `ai-premium` | 1. Nhập trực tiếp URL `http://127.0.0.1:8080/communities/ai-premium`<br>2. Nhấn Enter gửi request | Hệ thống từ chối truy cập (404 Not Found theo cơ chế denyAsNotFound), không để lộ metadata | Trả về màn hình 404 Not Found sạch sẽ, bảo vệ hoàn toàn thông tin cộng đồng | **PASS** | `image.png` | - |
| **SC-EUR58-02** | Member (Tenant khác) | Đã đăng nhập, không có quyền tại `ai-premium` | 1. Nhập URL tài nguyên lồng nhau `http://127.0.0.1:8080/communities/ai-premium/classroom`<br>2. Nhấn Enter gửi request | Trả về lỗi 404 Not Found, không để lộ danh sách khóa học nội bộ của Community khác | Trả về màn hình 404 Not Found an toàn, cô lập hoàn toàn tài nguyên học tập | **PASS** | `image-1.png` | - |
| **SC-EUR58-03** | Creator | Đăng nhập tài khoản Creator của `ai-newbie` | 1. Vào trang quản lý khóa học<br>2. Khởi tạo khóa học mới "Prompt Engineer" ở trạng thái `Draft` | Khóa học được tạo với nhãn `Draft` rõ ràng trong giao diện quản trị | Khóa học hiển thị badge `Draft` màu vàng/nổi bật, sẵn sàng test cô lập | **PASS** | `image-2.png` | - |
| **SC-EUR58-04** | Member | Existing known draft course in its tenant | 1. Open tenant content search<br>2. Search draft title<br>3. Check post/course/lesson suggestions and full results | Draft title/metadata absent; positive published control present | Submitted image-3.png is Communities discovery, not tenant content search; the intended boundary was not tested | **N/T** | [wrong-surface evidence](image-3.png) | Test coverage defect, not a demonstrated application fix |
| **SC-EUR58-05** | Member | Đang đăng nhập tài khoản Member | 1. Nhập trực tiếp URL khóa học nháp `http://127.0.0.1:8080/communities/ai-newbie/courses/2`<br>2. Gửi request | Hệ thống từ chối truy cập (404 Not Found), học viên không được xem khóa học chưa xuất bản | Trả về màn hình 404 Not Found, ngăn chặn xem trước nội dung nháp | **PASS** | `image-4.png` | - |
| **SC-EUR58-06** | Member | Đang mở modal thảo luận bình luận bài viết | 1. Nhập nội dung bình luận kiểm thử<br>2. Nhấn click nhanh liên tiếp 2 lần (Double-click) vào nút `Reply` | Nút chuyển sang trạng thái pending (`Posting...`) và xử lý bảo toàn tính idempotency; chỉ tạo đúng 1 bình luận | Nút hiển thị `Posting...`, chỉ tạo duy nhất 1 bình luận (count 2), không duplicate dữ liệu | **PASS** | `image-5.png` | - |
| **SC-EUR58-07** | Member | Mở form cập nhật thông tin cá nhân (Profile) | 1. Nhập Bio hợp lệ<br>2. Cố tình để trống trường `Name`<br>3. Nhấn `Save` để kích hoạt validation fail | Báo lỗi validation ở trường Name, nhưng trường Bio đã nhập trước đó không bị mất dữ liệu | Hiển thị cảnh báo tại ô Name, dữ liệu trường Bio vẫn được giữ nguyên vẹn | **PASS** | `image-6.png` | - |
| **SC-EUR58-08** | Member | Đang ở form Profile sau khi cập nhật thành công | 1. Điền đầy đủ Name, Bio hợp lệ<br>2. Nhấn `Save` lưu thành công<br>3. Nhấn F5 Refresh hoặc Back/Forward trên trình duyệt | Hệ thống áp dụng mẫu Post/Redirect/Get (PRG), không xuất hiện cảnh báo gửi lại form (Resubmission) | Tải lại trang bình thường, dữ liệu mới được hiển thị ổn định, không bị gửi lại dữ liệu | **PASS** | `image-7.png` | - |

---

## 4. Chi tiết từng kịch bản kiểm thử (Detailed Scenario Cards)

### SC-EUR58-01 / Chặn truy cập chéo Root Community qua URL (Cross-Tenant Root Denial)
* **Severity:** High
* **Candidate and environment:** `6179e3e389ddc8041568bd56a114d96fa044d401`, Windows, Docker Local (`http://127.0.0.1:8080`)
* **Actor, tenant and resource state:** Member (thuộc Community `ai-newbie`), mục tiêu: Community `ai-premium` (ID riêng tư)
* **Minimal reproduction steps:**
  1. Đăng nhập với tài khoản Member không thuộc quyền sở hữu hay hội viên của `ai-premium`.
  2. Gõ trực tiếp URL `http://127.0.0.1:8080/communities/ai-premium` vào thanh địa chỉ trình duyệt.
  3. Nhấn Enter để gửi request.
* **Expected result:** Hệ thống từ chối truy cập và phản hồi mã lỗi `404 | NOT FOUND` (theo quy ước `denyAsNotFound`), không tiết lộ sự tồn tại của cộng đồng hoặc bất kỳ thông tin nào (tên, avatar, mô tả).
* **Actual result:** Trả về màn hình `404 | NOT FOUND` trống rỗng, bảo vệ siêu dữ liệu an toàn.
* **Frequency:** 1/1 (100% nhất quán).
* **Data/security/user impact:** Đảm bảo tính riêng tư tuyệt đối giữa các cộng đồng trên nền tảng.
* **Sanitized evidence links:** `image.png`
* **Retest scope and status:** Historical submitted PASS; no prior defect/fix established

---

### SC-EUR58-02 / Chặn truy cập chéo tài nguyên Classroom qua URL (Cross-Tenant Classroom Denial)
* **Severity:** High
* **Candidate and environment:** `6179e3e389ddc8041568bd56a114d96fa044d401`, Windows, Docker Local (`http://127.0.0.1:8080`)
* **Actor, tenant and resource state:** Member (không thuộc `ai-premium`), mục tiêu: `/communities/ai-premium/classroom`
* **Minimal reproduction steps:**
  1. Đăng nhập tài khoản Member của `ai-newbie`.
  2. Gõ đường dẫn trực tiếp tới phòng học của cộng đồng khác: `http://127.0.0.1:8080/communities/ai-premium/classroom`.
  3. Nhấn Enter gửi request.
* **Expected result:** Hệ thống từ chối truy cập và phản hồi `404 | NOT FOUND`, không làm lộ danh mục khóa học, số lượng bài học hay tên bài giảng.
* **Actual result:** Trả về giao diện lỗi `404 | NOT FOUND` chuẩn, không rò rỉ bất kỳ thông tin nhạy cảm nào.
* **Frequency:** 1/1 (100% nhất quán).
* **Data/security/user impact:** Ngăn chặn đánh cắp sở hữu trí tuệ và bài giảng trả phí giữa các tenant.
* **Sanitized evidence links:** `image-1.png`
* **Retest scope and status:** Historical submitted PASS; no prior defect/fix established

---

### SC-EUR58-03 / Tạo và định danh khóa học bản nháp (Draft Course Creation)
* **Severity:** Medium
* **Candidate and environment:** `6179e3e389ddc8041568bd56a114d96fa044d401`, Windows, Docker Local (`http://127.0.0.1:8080`)
* **Actor, tenant and resource state:** Creator, Community `ai-newbie`, Khóa học mới "Prompt Engineer" (0 lessons)
* **Minimal reproduction steps:**
  1. Đăng nhập tài khoản Creator.
  2. Truy cập tab Classroom của Community `ai-newbie`.
  3. Tạo khóa học mới "Prompt Engineer" nhưng chưa xuất bản (để trạng thái `Draft`).
* **Expected result:** Khóa học xuất hiện trong bảng điều khiển của Creator với nhãn định danh trạng thái `Draft`.
* **Actual result:** Khóa học hiển thị đúng nhãn `Draft` màu vàng/nổi bật, phục vụ làm tiền đề kiểm thử các trường hợp cách ly phía học viên.
* **Frequency:** 1/1.
* **Data/security/user impact:** Giúp Creator chuẩn bị nội dung khóa học an toàn trước khi công bố.
* **Sanitized evidence links:** `image-2.png`
* **Retest scope and status:** Historical submitted PASS; no prior defect/fix established

---

### SC-EUR58-04 / Draft-search evidence correction

Result: N/T for the intended content-search boundary. The evidence shows the
Communities page and "No matching communities", which searches community names.
It cannot establish that a draft course is excluded from tenant content search.
Retest with a known draft and a published positive control in the same tenant,
then check typeahead, full content search, direct URL and private media separately.

---

### SC-EUR58-05 / Chặn truy cập khóa học Draft qua URL trực tiếp (Draft Course Direct URL Denial)
* **Severity:** High
* **Candidate and environment:** `6179e3e389ddc8041568bd56a114d96fa044d401`, Windows, Docker Local (`http://127.0.0.1:8080`)
* **Actor, tenant and resource state:** Member ("Demo Member"), Khóa học `courses/2` (Draft)
* **Minimal reproduction steps:**
  1. Đăng nhập tài khoản Member.
  2. Cố tình nhập trực tiếp URL khóa học nháp: `http://127.0.0.1:8080/communities/ai-newbie/courses/2`.
  3. Gửi request.
* **Expected result:** Policy của hệ thống từ chối và trả về mã lỗi `404 | NOT FOUND` (denyAsNotFound).
* **Actual result:** Trả về trang `404 | NOT FOUND` an toàn, học viên không thể xem bài học bên trong.
* **Frequency:** 1/1.
* **Data/security/user impact:** Bảo vệ nội dung khóa học độc quyền khi chưa đến thời điểm phát hành chính thức.
* **Sanitized evidence links:** `image-4.png`
* **Retest scope and status:** Historical submitted PASS; no prior defect/fix established

---

### SC-EUR58-06 / Kiểm soát thao tác lặp & Tính Idempotency khi Double-click bình luận
* **Severity:** Medium
* **Candidate and environment:** `6179e3e389ddc8041568bd56a114d96fa044d401`, Windows, Docker Local (`http://127.0.0.1:8080`)
* **Actor, tenant and resource state:** Member ("Demo Member"), Hộp thoại bình luận bài viết Feed
* **Minimal reproduction steps:**
  1. Mở modal chi tiết bài viết trên Feed.
  2. Nhập nội dung bình luận: `[TEST] Does this feature pass double-lick test`.
  3. Thực hiện thao tác double-click cực nhanh vào nút `Reply`.
* **Expected result:** Nút bấm lập tức chuyển trạng thái chờ (`Posting...`) và tạm thời vô hiệu hóa; server áp dụng cơ chế xử lý chống trùng lặp, chỉ có đúng 1 bình luận được thêm vào hệ thống.
* **Actual result:** Nút phản hồi hiển thị `Posting...`, tổng số bình luận cập nhật chính xác (chỉ tăng 1 từ 1 lên 2), thông báo "Comment posted successfully", không xuất hiện bình luận rác trùng lặp.
* **Frequency:** 1/1.
* **Data/security/user impact:** Bảo toàn tính toàn vẹn dữ liệu, chống spam và tránh lãng phí tài nguyên cơ sở dữ liệu.
* **Sanitized evidence links:** `image-5.png`
* **Retest scope and status:** Historical submitted PASS; no prior defect/fix established

---

### SC-EUR58-07 / Bảo toàn dữ liệu người dùng khi Validation thất bại (User Input Retention on Failure)
* **Severity:** Low / Medium
* **Candidate and environment:** `6179e3e389ddc8041568bd56a114d96fa044d401`, Windows, Docker Local (`http://127.0.0.1:8080`)
* **Actor, tenant and resource state:** Member (`member@scool.local`), Form Profile Information
* **Minimal reproduction steps:**
  1. Vào trang cập nhật hồ sơ cá nhân.
  2. Nhập nội dung dài vào trường Bio: `Hi guys, im 4th year IT student, i love cybersecurity`.
  3. Cố tình xóa trống trường bắt buộc `Name`.
  4. Nhấn nút `Save`.
* **Expected result:** Hệ thống bắt lỗi validation tại trường Name ("Please fill out this field."), nhưng giữ lại nguyên vẹn nội dung của trường Bio in the existing DOM; server old-input retention was not exercised.
* **Actual result:** Xuất hiện tooltip cảnh báo tại trường Name, toàn bộ chuỗi văn bản trong Bio vẫn còn nguyên vẹn, không bắt người dùng nhập lại từ đầu.
* **Frequency:** 1/1.
* **Data/security/user impact:** Nâng cao trải nghiệm người dùng (UX), tránh ức chế mất dữ liệu khi điền form phức tạp.
* **Sanitized evidence links:** `image-6.png`
* **Retest scope and status:** Historical submitted PASS; no prior defect/fix established

---

### SC-EUR58-08 / Chống gửi lại dữ liệu form khi Refresh/Điều hướng (Form Resubmission Prevention)
* **Severity:** Medium
* **Candidate and environment:** `6179e3e389ddc8041568bd56a114d96fa044d401`, Windows, Docker Local (`http://127.0.0.1:8080`)
* **Actor, tenant and resource state:** Member (`member@scool.local`), Form Profile Information
* **Minimal reproduction steps:**
  1. Nhập đầy đủ thông tin hợp lệ Name: `[DEMO] Member`, Bio và Email.
  2. Nhấn nút `Save` để lưu thành công.
  3. Nhấn F5 (Refresh) hoặc nút Back/Forward trên trình duyệt.
* **Expected result:** Áp dụng mô hình Post/Redirect/Get (PRG), trang được tải lại mà không bật hộp thoại cảnh báo "Confirm Form Resubmission" của trình duyệt.
* **Actual result:** Form hiển thị nội dung mới nhất đã lưu thành công, không xuất hiện cảnh báo gửi lại dữ liệu, trạng thái ổn định.
* **Frequency:** 1/1.
* **Data/security/user impact:** Ngăn chặn việc gửi lại dữ liệu ngoài ý muốn khi người dùng tải lại trang.
* **Sanitized evidence links:** `image-7.png`
* **Retest scope and status:** Historical submitted PASS; no prior defect/fix established

---

## 5. Coverage and recommendation

Totals for the submitted eight-case sample: 7 historical PASS, 0 confirmed FAIL,
0 BLOCKED, 1 N/T. Draft creation is fixture setup, not a security boundary test.
A 404 screenshot requires a known existing target/positive control before it
proves cross-tenant isolation; these two root/classroom screenshots do not cover
nested resource-ID substitution in every module.

| Contract boundary without submitted runtime evidence | Result |
|---|---|
| Cross-tenant Feed/Event/directory/message/order/payment nested IDs | N/T |
| Guest/inactive/removed/expired actors and stale-page mutation denial | N/T |
| Draft tenant search, contextual links and private media | N/T |
| Like/save/progress/payment replay or concurrency | N/T |
| Network interruption, false-success rollback and slow-request recovery | N/T |
| Payment redirects/polling without authenticated provider confirmation | N/T |
| Server-rejected validation with old input retained | N/T; screenshot is native required-field validation |

The observed double-click Reply/pending state is one UI observation. It is not
proof of arbitrary HTTP replay safety or concurrent transaction correctness.
The native required-field tooltip does not submit the form, so it cannot prove
server validation or old() behavior. PRG feedback is a submitted historical
observation, not coverage of every failure/recovery path.

Recommendation: partial boundary evidence; do not claim "100% secure" or blanket
metadata-leak prevention. Use current guarded automated boundary tests and
explicit fresh browser negatives as separate supplementary evidence. Retest
affected boundaries on any new candidate and keep unexecuted rows N/T.

## 6. Current-candidate supplement (2026-10-20)

Candidate and final checks: [B3 current run](../EUR-59/EUR-59.md#current-candidate-corrective-run-2026-10-20).
Positive control: Creator created the isolated FREE community `qa-b-mvg61wpi`
and could open its management/invitation UI. The existing synthetic Member was
not a member of that community. No policies or database access records were
changed to obtain a denial.

| Scenario ID | Actor/state and preconditions | Numbered steps | Expected | Actual | Result | Evidence | Defect |
|---|---|---|---|---|---|---|---|
| B2-C01 | Existing Member; known existing other tenant | 1. Open `/communities/qa-b-mvg61wpi` directly | No private tenant metadata | Rendered 404 / Not Found only | PASS | Local sanitized browser record; positive control below | None |
| B2-C02 | Same nonmember and tenant | 1. Open `/communities/qa-b-mvg61wpi/classroom` directly | No course metadata | Rendered 404 / Not Found only | PASS | Local sanitized browser record | None |
| B2-C03 | Same nonmember and tenant | 1. Open `/communities/qa-b-mvg61wpi/manage` directly | No Creator controls/metadata | Rendered 404 / Not Found only | PASS | Local sanitized browser record | None |
| B2-C04 | Same nonmember and tenant | 1. Open `/communities/qa-b-mvg61wpi/invitations` directly | No invitation metadata | Rendered 404 / Not Found only | PASS | Local sanitized browser record | None |
| B2-C05 | Matching verified invitee; original fragment already scrubbed | 1. Reload the bare invitation page | Missing token cannot authorize acceptance | Both mutation controls disabled | PASS | [Invitation regression/retest](../EUR-57/EUR-57-summary.md#4-invitation-finding-correction) | None |

Fresh sample totals: **5 PASS, 0 FAIL, 0 BLOCKED, 0 N/T** in these five cases only.
This is not a percentage of the full security contract. Creator positive control:
[accepted invitation table](../EUR-59/current-evidence/chrome-creator-desktop.png).
The new invitee later received approved membership through the real invitation
flow, not through an access bypass.

The final guarded suite includes existing authorization, entitlement, stale-state,
payment, concurrency and recovery regressions; its result is automated local
evidence. AC-01 through AC-06 still lack a complete current manual boundary
matrix, and remain partial. No new Critical/High security defect was confirmed
in this bounded sample. Keep In Review; do not infer product-wide security
acceptance or close missing negative scenarios from unrelated screenshots.
