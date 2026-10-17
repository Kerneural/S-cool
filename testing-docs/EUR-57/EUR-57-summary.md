# Báo cáo Tổng hợp Kiểm thử EUR-57 (QA-B1)
**Cross-role end-to-end journey and persistence regression**

---

## 1. Thông tin tổng quan (Execution Overview)
* **Mã Issue:** [EUR-57](https://linear.app/eurusdevsec/issue/EUR-57) (QA-B1)
* **Issue cha:** [EUR-53](https://linear.app/eurusdevsec/issue/EUR-53) (QA Track B)
* **Tài liệu nguồn:** `c:\Users\hadan\scool\.agent\EUR-57.md`
* **Mục tiêu:** Kiểm tra toàn bộ hành trình trải nghiệm của 4 vai trò (Guest, Member, Creator, Platform Admin), tính bền vững của dữ liệu sau khi F5 refresh và đăng nhập lại, cùng tính năng xác thực và phân quyền cơ bản.

---

## 2. Bảng thống kê kết quả (Execution Metrics)
* **Tổng số kịch bản:** 17
* **PASS:** 15
* **FAIL:** 1 (DEF-EUR57-01)
* **BLOCKED:** 1 (Chặn bởi DEF-EUR57-01)
* **N/T (Not Tested):** 0

*(Tuân thủ quy chuẩn: Số lượng các ca PASS, FAIL, BLOCKED, N/T được tách biệt rõ ràng, không ẩn số ca BLOCKED vào tỷ lệ phần trăm).*

---

## 3. Bảng kịch bản kiểm thử chi tiết (Scenario Matrix)

| Scenario ID | actor/state | preconditions | numbered steps | expected result | actual result | PASS/FAIL/BLOCKED/N/T | evidence | defect ID |
|---|---|---|---|---|---|---|---|---|
| **SC-EUR57-01** | Guest (Chưa xác thực) | Trình duyệt mở trang `/register`, chưa có phiên đăng nhập | 1. Nhập thông tin đăng ký (Tên, Email, Mật khẩu)<br>2. Nhấn Submit Đăng ký<br>3. Kiểm tra Mailpit nhận email xác nhận<br>4. Đăng nhập vào hệ thống | Đăng ký tài khoản thành công, Mailpit nhận email xác nhận, đăng nhập thành công vào trang chủ | Đăng ký thành công, nhận mail, đăng nhập chuyển hướng vào hệ thống chính xác | **PASS** | `image.png`, `image-1.png`, `image-2.png` | - |
| **SC-EUR57-02** | Authenticated User / Member | Đang đăng nhập trong hệ thống | 1. Nhấn nút Đăng xuất (Logout)<br>2. Nhấn nút Back trên trình duyệt để quay lại trang trước | Phiên làm việc kết thúc hoàn toàn; khi Back lại không truy cập được phiên cũ mà chuyển hướng về Homepage | Sau khi đăng xuất, nhấn Back chuyển hướng về lại Homepage an toàn | **PASS** | `image-3.png` | - |
| **SC-EUR57-03** | Creator (Chủ cộng đồng) | Đã đăng nhập tài khoản Creator, đang ở trang quản lý Community | 1. Nhập email của Member1 vào ô gửi lời mời<br>2. Nhấn nút Gửi lời mời gia nhập | Giao diện hiển thị thông báo mời thành công, hệ thống sinh token và gửi email mời tới Mailpit | Giao diện báo mời thành công, Mailpit nhận được email lời mời kèm liên kết | **PASS** | `image-4.png`, `image-5.png`, `image-6.png` | - |
| **SC-EUR57-04** | Guest / Invited User | Email lời mời đã nằm trong hộp thư Mailpit | 1. Mở email lời mời trong Mailpit<br>2. Nhấn vào liên kết mời (Invitation link) để xác nhận gia nhập | Trình duyệt mở trang tiếp nhận lời mời, xác thực token hợp lệ và cấp quyền thành viên Community | Invitation link không hoạt động, không thể hoàn tất xác nhận gia nhập | **FAIL** | `image-7.png` | `DEF-EUR57-01` |
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
| **SC-EUR57-16** | Member / Creator | Cần tài khoản Member đã gia nhập để thanh toán | 1. Mở luồng thanh toán SePay Test Mode<br>2. Tiến hành xác nhận thanh toán thử nghiệm | Đơn hàng tạo thành công, giao dịch ở chế độ Test Mode được xác nhận | Bị chặn (Blocked) do lời mời qua Mailpit bị lỗi (SC-EUR57-04), không thể đưa Member vào luồng thanh toán | **BLOCKED** | Ghi nhận tại `.agent/EUR-57.md` dòng 77-78 | `DEF-EUR57-01` |
| **SC-EUR57-17** | Platform Admin | Đăng nhập tài khoản Platform Admin | 1. Truy cập vào trang Quản trị toàn sàn (`/admin`)<br>2. Xem danh sách tất cả các Community | Toàn bộ danh sách Community trên sàn được hiển thị đầy đủ kèm trạng thái | Xem danh sách Community toàn sàn thành công | **PASS** | `image-22.png` | - |
| **SC-EUR57-18** | Platform Admin | Đang ở danh sách Community trong trang Admin | 1. Nhấn nút Tạm khóa (Suspend) Community<br>2. Thử truy cập vào Community vừa bị khóa | Community chuyển sang trạng thái tạm khóa; người dùng không thể truy cập vào Community bị khóa | Tạm khóa thành công; truy cập vào Community báo màn hình khóa/từ chối | **PASS** | `image-24.png`, `image-23.png` | - |
| **SC-EUR57-19** | Platform Admin | Community đang ở trạng thái tạm khóa (Suspended) | 1. Nhấn nút Kích hoạt lại (Reactivate) Community trong trang Admin | Community khôi phục về trạng thái hoạt động bình thường, người dùng truy cập lại được | Kích hoạt lại Community thành công, trạng thái mở lại bình thường | **PASS** | `image-25.png` | - |
| **SC-EUR57-20** | Member (Không có quyền Admin) | Đang đăng nhập tài khoản Member thông thường | 1. Cố tình gõ trực tiếp URL `/admin` trên thanh địa chỉ | Hệ thống từ chối truy cập (403 Forbidden / chuyển hướng an toàn), không để lộ giao diện admin | Hệ thống chặn và từ chối Member truy cập trang Admin | **PASS** | `image-26.png` | - |

---

## 4. Chi tiết lỗi cản trở (Defect Record)

### `DEF-EUR57-01`: Invitation link trong email Mailpit không hoạt động
* **Mức độ nghiêm trọng (Severity):** High / Critical (Gây tắc nghẽn luồng tiếp nhận thành viên và chặn luồng kiểm thử thanh toán SePay).
* **Ứng viên & Môi trường (Candidate & Environment):**
  * Môi trường: Local Docker development stack.
  * Mailpit: `http://localhost:8025`.
* **Đối tượng & Trạng thái tài nguyên (Actor, Tenant & Resource state):**
  * Actor: Guest / Member1 được mời.
  * Resource: Lời mời tham gia Community "AI Beginner" (hoặc AI Newbie).
* **Các bước tái hiện tối thiểu (Minimal reproduction steps):**
  1. Đăng nhập tài khoản Creator, mở trang gửi lời mời của Community.
  2. Gửi lời mời tới địa chỉ email của Member1.
  3. Mở Mailpit tại `http://localhost:8025`, bấm vào email lời mời vừa nhận.
  4. Nhấp vào đường dẫn mời gia nhập (Invitation link) đính kèm trong email.
* **Kết quả mong đợi (Expected result):**
  * Trình duyệt điều hướng tới trang tiếp nhận lời mời của S-cool với mã token mời hợp lệ, cho phép xác nhận gia nhập Community.
* **Kết quả thực tế (Actual result):**
  * Đường dẫn mời không hoạt động (lỗi điều hướng / không tìm thấy trang hoặc URL sinh ra không đúng cấu hình cổng/domain).
* **Tần suất (Frequency):** 1/1 (Xảy ra liên tục).
* **Mức độ tác động (Impact):**
  * Người dùng mới không thể gia nhập cộng đồng qua thư mời.
  * Chặn trực tiếp kịch bản kiểm thử tính năng **SePay Test Mode** (SC-EUR57-16).
* **Bằng chứng đã khử thông tin nhạy cảm (Sanitized evidence):** `.agent/image-7.png`.
* **Phạm vi & Trạng thái kiểm thử lại (Retest scope & Status):**
  * Trạng thái: `Open`.
  * Phạm vi retest: Kiểm tra cấu hình `APP_URL`, route xử lý lời mời, logic băm/đối soát token trong controller tiếp nhận lời mời. Sau khi dev sửa xong, retest lại SC-EUR57-04 và SC-EUR57-16.

---

## 5. Kết luận & Khuyến nghị (QA Conclusion)
* **Trạng thái:** `Conditional / Blocked on DEF-EUR57-01`.
* **Đánh giá:** Các tính năng cốt lõi về Đăng nhập/Đăng ký, Feed (bài viết, comment, like), Live Search, Classroom & lưu trữ tiến độ (F5/Re-login), Lịch Events, Cài đặt Community, Quản lý thành viên, Thu hồi lời mời và Quản trị toàn sàn (Suspend/Reactivate/RBAC) đều hoạt động rất tốt và dữ liệu được lưu bền vững (Persistence).
* **Điểm nghẽn cần xử lý ngay:** Sửa lỗi liên kết mời trong Mailpit (`DEF-EUR57-01`) để hoàn thành kịch bản mời thành viên và mở khóa kiểm thử tính năng thanh toán SePay Test Mode.
