> Corrective artifact review — 2026-10-20. Historical report source: testing-docs commit d55518316b782560b88113ce8bfb725f6e0a48cd, runtime 6179e3e389ddc8041568bd56a114d96fa044d401. Browser version, run times and B1 dirty-diff identity were not supplied. The 18 PASS rows below are submitted historical observations, not independent current-candidate proof. Valid invitation acceptance is N/T, payment execution is BLOCKED, and uncovered contract areas are listed explicitly. Do not claim a confirmed High/Critical invitation defect from the missing-token screenshot.

# Báo cáo Tổng hợp Kiểm thử EUR-57 (QA-B1)
**Cross-role end-to-end journey and persistence regression**

---

## 1. Thông tin tổng quan (Execution Overview)
* **Mã Issue:** [EUR-57](https://linear.app/eurusdevsec/issue/EUR-57) (QA-B1)
* **Issue cha:** [EUR-53](https://linear.app/eurusdevsec/issue/EUR-53) (QA Track B)
* **Evidence gallery:** [EUR-57.md](EUR-57.md); personal .agent files are not a shared prerequisite.
* **Mục tiêu:** Kiểm tra toàn bộ hành trình trải nghiệm của 4 vai trò (Guest, Member, Creator, Platform Admin), tính bền vững của dữ liệu sau khi F5 refresh và đăng nhập lại, cùng tính năng xác thực và phân quyền cơ bản.

---

## 2. Bảng thống kê kết quả (Execution Metrics)
* **Tổng số kịch bản:** 20
* **PASS:** 18 (historical submitted observations, not an independent current-candidate rerun)
* **FAIL:** 0 confirmed product defects from the submitted artifacts
* **BLOCKED:** 1 (payment execution was not performed)
* **N/T (Not Tested):** 1 (valid invitation acceptance was not demonstrated)

*(Tuân thủ quy chuẩn: Số lượng các ca PASS, FAIL, BLOCKED, N/T được tách biệt rõ ràng, không ẩn số ca BLOCKED vào tỷ lệ phần trăm).*

---

## 3. Bảng kịch bản kiểm thử chi tiết (Scenario Matrix)

| Scenario ID | actor/state | preconditions | numbered steps | expected result | actual result | PASS/FAIL/BLOCKED/N/T | evidence | defect ID |
|---|---|---|---|---|---|---|---|---|
| **SC-EUR57-01** | Guest (Chưa xác thực) | Trình duyệt mở trang `/register`, chưa có phiên đăng nhập | 1. Nhập thông tin đăng ký (Tên, Email, Mật khẩu)<br>2. Nhấn Submit Đăng ký<br>3. Kiểm tra Mailpit nhận email xác nhận<br>4. Đăng nhập vào hệ thống | Đăng ký tài khoản thành công, Mailpit nhận email xác nhận, đăng nhập thành công vào trang chủ | Đăng ký thành công, nhận mail, đăng nhập chuyển hướng vào hệ thống chính xác | **PASS** | `image.png`, `image-1.png`, `image-2.png` | - |
| **SC-EUR57-02** | Authenticated User / Member | Đang đăng nhập trong hệ thống | 1. Nhấn nút Đăng xuất (Logout)<br>2. Nhấn nút Back trên trình duyệt để quay lại trang trước | Phiên làm việc kết thúc hoàn toàn; khi Back lại không truy cập được phiên cũ mà chuyển hướng về Homepage | Sau khi đăng xuất, nhấn Back chuyển hướng về lại Homepage an toàn | **PASS** | `image-3.png` | - |
| **SC-EUR57-03** | Creator (Chủ cộng đồng) | Đã đăng nhập tài khoản Creator, đang ở trang quản lý Community | 1. Nhập email của Member1 vào ô gửi lời mời<br>2. Nhấn nút Gửi lời mời gia nhập | Giao diện hiển thị thông báo mời thành công, hệ thống sinh token và gửi email mời tới Mailpit | Giao diện báo mời thành công, Mailpit nhận được email lời mời kèm liên kết | **PASS** | `image-4.png`, `image-5.png`, `image-6.png` | - |
| **SC-EUR57-04** | Verified invitee | Valid original invitation email required | 1. Sign in with matching verified email<br>2. Reopen original email link once<br>3. Accept without reloading the token-stripped URL | Valid token permits acceptance; missing token denies | image-7.png shows missing/malformed token and disabled controls. It does not demonstrate execution with the original valid token | **N/T** | [missing-token screenshot](image-7.png) | No confirmed product defect |
| **SC-EUR57-05** | Member | Đã gia nhập Community, đang ở trang Feed | 1. Nhập nội dung bài viết vào khung soạn thảo<br>2. Nhấn nút Đăng bài (Post) | Bài viết mới xuất hiện ngay trên dòng thời gian (Feed) của Community | Bài viết đăng thành công và hiển thị trên giao diện Feed | **PASS** | `image-8.png` | - |
| **SC-EUR57-06** | Member | Bài viết đã hiển thị trên Feed | 1. Nhập nội dung bình luận vào bài viết<br>2. Nhấn gửi bình luận<br>3. Nhấn nút Thả React (Like) vào bài viết | Bình luận xuất hiện ngay dưới bài viết, trạng thái React cập nhật chính xác | Bình luận và thả React thành công trên bài viết | **PASS** | `image-9.png` | - |
| **SC-EUR57-07** | Member | Đã đăng bài viết và bình luận trên Feed | 1. Đang ở giao diện Feed vừa đăng bài/bình luận<br>2. Nhấn phím F5 (Refresh) tải lại trang | Bài viết, bình luận và phản hồi vừa tạo vẫn còn nguyên vẹn, dữ liệu đã lưu vào database | Nhấn F5 tải lại, bài viết và bình luận vẫn còn nguyên vẹn | **PASS** | `image-10.png` | - |
| **SC-EUR57-08** | Member | Đang ở trang tìm kiếm / khóa học | 1. Nhập từ khóa tìm kiếm khóa học vào ô Search<br>2. Quan sát kết quả hiển thị | Danh sách khóa học được lọc và hiển thị theo thời gian thực (Live Search) | Gõ từ khóa tìm kiếm khóa học hiển thị kết quả chính xác | **PASS** | `image-11.png` | - |
| **SC-EUR57-09** | Member | Khóa học đã được Creator xuất bản (Published) | 1. Nhấp chọn vào khóa học đã xuất bản<br>2. Vào xem bài học<br>3. Nhấn nút Hoàn thành bài học | Giao diện bài học mở bình thường, thao tác hoàn thành bài học thành công, tiến độ cập nhật | Vào khóa học thành công, giao diện ghi nhận hoàn thành bài học chính xác | **PASS** | `image-12.png`, `image-13.png` | - |
| **SC-EUR57-10** | Member | Đã hoàn thành bài học trong khóa học | 1. Nhấn F5 (Refresh) tải lại trang bài học<br>2. Đăng xuất và đăng nhập lại vào tài khoản Member | Tiến độ học tập vẫn giữ nguyên (100% / hoàn thành) sau cả 2 thao tác F5 và Re-login | Tiến độ học tập vẫn được giữ nguyên sau F5 và sau khi đăng nhập lại | **PASS** | `image-14.png` | - |
| **SC-EUR57-11** | Member | Community có sự kiện đã được lên lịch | 1. Mở trang Sự kiện (Events) của Community<br>2. Xem danh sách các sự kiện | Giao diện Events hiển thị đầy đủ danh sách các sự kiện sắp diễn ra | Giao diện Event hiển thị đầy đủ, đúng bố cục | **PASS** | `image-15.png` | - |
| **SC-EUR57-12** | Member | Đang ở trang danh sách sự kiện | 1. Nhấp vào sự kiện cụ thể để xem chi tiết lịch trình và thời gian | Trang chi tiết sự kiện hiển thị thời gian, múi giờ và nội dung sự kiện rõ ràng | Xem chi tiết lịch sự kiện thành công | **PASS** | `image-16.png` | - |
| **SC-EUR57-13** | Creator | Đăng nhập tài khoản Creator, vào trang Cài đặt Community | 1. Đổi tên Community từ "AI Newbie" thành "AI Beginner"<br>2. Nhấn Lưu thay đổi<br>3. Nhấn F5 Refresh lại trang | Tên mới được cập nhật trong cơ sở dữ liệu và giữ nguyên sau khi F5 | Đổi tên Community thành "AI Beginner" thành công, sau F5 trạng thái được bảo toàn | **PASS** | `image-17.png`, `image-18.png` | - |
| **SC-EUR57-14** | Creator | Đang ở trang quản lý thành viên Community | 1. Nhấp vào mục Thành viên (Members) của Community | Danh sách thành viên hiển thị đầy đủ, chính xác các tài khoản tham gia | Xem danh sách thành viên Community thành công | **PASS** | `image-19.png` | - |
| **SC-EUR57-15** | Creator | Có lời mời đang ở trạng thái `PENDING` | 1. Mở danh sách lời mời đã gửi<br>2. Nhấn nút Thu hồi (Revoke) lời mời chưa kích hoạt | Trạng thái lời mời được cập nhật từ `PENDING` sang `REVOKED` trong database | Thu hồi lời mời thành công, trạng thái chuyển từ `PENDING` sang `REVOKED` | **PASS** | `image-20.png`, `image-21.png` | - |
| **SC-EUR57-16** | Eligible payer | Valid invitation / Test Mode setup | 1. Open original invitation<br>2. Choose Test Mode checkout<br>3. Verify provider-backed result separately | Only verified eligible payment grants the intended access | Payment execution was not performed in the submitted run; the alleged invitation defect is unconfirmed | **BLOCKED** | [historical gallery](EUR-57.md) | Evidence/setup gap, not confirmed payment failure |
| **SC-EUR57-17** | Platform Admin | Đăng nhập tài khoản Platform Admin | 1. Truy cập vào trang Quản trị toàn sàn (`/admin`)<br>2. Xem danh sách tất cả các Community | Toàn bộ danh sách Community trên sàn được hiển thị đầy đủ kèm trạng thái | Xem danh sách Community toàn sàn thành công | **PASS** | `image-22.png` | - |
| **SC-EUR57-18** | Platform Admin | Đang ở danh sách Community trong trang Admin | 1. Nhấn nút Tạm khóa (Suspend) Community<br>2. Thử truy cập vào Community vừa bị khóa | Community chuyển sang trạng thái tạm khóa; người dùng không thể truy cập vào Community bị khóa | Tạm khóa thành công; truy cập vào Community báo màn hình khóa/từ chối | **PASS** | `image-24.png`, `image-23.png` | - |
| **SC-EUR57-19** | Platform Admin | Community đang ở trạng thái tạm khóa (Suspended) | 1. Nhấn nút Kích hoạt lại (Reactivate) Community trong trang Admin | Community khôi phục về trạng thái hoạt động bình thường, người dùng truy cập lại được | Kích hoạt lại Community thành công, trạng thái mở lại bình thường | **PASS** | `image-25.png` | - |
| **SC-EUR57-20** | Member (Không có quyền Admin) | Đang đăng nhập tài khoản Member thông thường | 1. Cố tình gõ trực tiếp URL `/admin` trên thanh địa chỉ | Hệ thống từ chối truy cập (403 Forbidden / chuyển hướng an toàn), không để lộ giao diện admin | Hệ thống chặn và từ chối Member truy cập trang Admin | **PASS** | `image-26.png` | - |

---

## 4. Invitation finding correction

The original screenshot does not independently prove DEF-EUR57-01. It visibly says
"Invitation token missing or malformed" and explains that reloading removes the
token. Disabled acceptance is the expected security behavior in that state.

Expected acceptance requires the original email link, matching verified email,
and valid pending invitation. Preserve the token boundary; do not bypass it or
change APP_URL, policies or token handling merely to make this screenshot pass.
The earlier "High / Critical", "1/1 continuously" and guessed URL/configuration
root cause were unsupported by that artifact. One observation is not a repeat reproduction.

The fresh 2026-10-20 audit subsequently reproduced a real valid-token failure
twice on the current local candidate: the guest layout omitted the Alpine
runtime required by invitationAcceptance. This is a High onboarding blocker,
with a different evidence basis from the missing-token screenshot. A focused
regression failed with zero runtime script inclusions. Adding the existing
single Livewire/Alpine runtime to the guest layout enabled the valid-token form.
The matching verified synthetic invitee accepted the original Mailpit link;
membership survived refresh and direct navigation, and the Creator's table
showed ACCEPTED and Email delivered. A bare reload after fragment scrubbing
remained disabled. Status: verified fixed locally; publication/release separate.

### DEF-EUR57-C01 / Valid invitation acceptance has no Alpine runtime

- Severity: High.
- Candidate/environment: [current nine-file candidate and local environment](../EUR-59/EUR-59.md#candidate-and-provenance).
- Actor/state: matching verified synthetic invitee, original pending FREE invitation
  in the isolated QA community; original email contains a valid 64-character token
  fragment. Token values are omitted.
- Minimal reproduction: 1. Verify/sign in as the matching invitee. 2. Open the
  original Mailpit invitation link. 3. Allow the page to settle without a bare
  reload. 4. Observe both acceptance/checkout controls.
- Expected: a valid original token enables the eligible acceptance action.
- Actual before fix: both controls remain disabled because invitationAcceptance
  never initializes without the Alpine runtime.
- Frequency: two failures / two pre-fix attempts.
- Impact: eligible new users cannot complete normal invitation onboarding; no
  security bypass or provider failure is inferred.
- Evidence: [before](../EUR-59/current-evidence/invitation-before-fix.png),
  [after](../EUR-59/current-evidence/invitation-accepted-after.png), red regression
  and final check totals in B3.
- Fix/retest: existing runtime inclusion only; original link accepted, Member
  access persisted after refresh/direct entry, Creator table ACCEPTED, bare-link
  reload denied. Verified fixed locally; integrated CI/publication pending.

Retest: use a unique synthetic Mailpit recipient; sign in and verify it, reopen
the original invitation email without reloading the token-stripped landing page,
accept, refresh and open the community directly. Missing/wrong/revoked/expired
tokens remain negative cases. Provider-backed payment confirmation is separate.

## 5. Coverage and recommendation

Historical matrix totals: 20 rows, 18 submitted PASS, 0 confirmed FAIL,
1 BLOCKED and 1 N/T. These totals do not represent the whole contract.

| Required area absent or incomplete in the submitted matrix | Result |
|---|---|
| Valid invitation acceptance and persistence | N/T; SC-EUR57-04 corrected above |
| SePay checkout/status and provider-backed completion | BLOCKED; SC-EUR57-16 |
| Saved-post persistence | N/T |
| Messages positive and empty/terminal states | N/T |
| Notifications positive and empty/terminal states | N/T |
| Account access/expiry and payment-history positive/terminal states | N/T |
| Creator order/report/referral workflows | N/T |
| Password recovery and direct-link persistence across each major module | N/T |
| Later F-24 behavior on the new local candidate | N/T in this historical run |

Recommendation: incomplete/conditional evidence, not release acceptance.
Existing submitted positive screenshots remain useful within their recorded
actions. The current-candidate corrective audit/checkpoint must distinguish
new browser observations, guarded automated coverage and remaining N/T; it must
not relabel these historical rows as a fresh complete end-to-end run.

## 6. Current-candidate supplement (2026-10-20)

The [current B3 evidence pack](../EUR-59/EUR-59.md#current-candidate-corrective-run-2026-10-20)
records the exact local candidate, final guarded checks and fourteen Chrome
desktop/mobile observations. New synthetic registration, email verification,
Creator community creation, original valid invitation acceptance and Member
refresh/direct-entry persistence were executed. Saved-post and lesson-progress
states persisted after refresh and were restored to their original states.

The invitation defect above is **Verified fixed locally**. This is supplementary
evidence for those actions only. Current manual positive/terminal coverage for
messages, notifications, access/payment history, complete Creator operations and
an Admin state-action journey is still N/T. The green automated suite is reported
separately; it does not turn every missing browser journey into a manual PASS.

AC disposition: AC-01/02/03/04/05 remain partial across the historical and fresh
samples; AC-06/07 have corrected defect records and explicit totals. Keep this
issue In Review pending complete evidence and review of the corrected artifact.
