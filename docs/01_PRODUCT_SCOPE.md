# 01 — Product Scope

> Tài liệu sống: vấn đề, actors, golden flow, MVP và ranh giới sản phẩm.  
> Người viết chính: Hoàng  
> Người review: Tiến, Khoa và mentor  
> Trạng thái: Baseline để thiết kế kiến trúc; cập nhật khi scope thay đổi  
> Cập nhật: 2026-09-25

## 1. Các quyết định đã chốt

- `DECISION`: S-cool là nền tảng **multi-community** theo yêu cầu của mentor.
- `DECISION`: Mọi community trong MVP là **private và invite-only**; không có danh mục, discovery, public join hoặc landing page công khai.
- `DECISION`: Creator tạo community riêng cho từng nhóm cụ thể và mời đúng người vào nhóm đó. Đây là biện pháp giới hạn phạm vi truy cập, không phải tuyên bố đã giải quyết toàn bộ nghĩa vụ pháp lý.
- `PROPOSED`: Invitation ràng buộc với email cụ thể, token một lần và có thời hạn. Hoàng cần xác nhận cơ chế này trước khi thiết kế migration.
- `DECISION`: Dùng thuật ngữ **Creator** cho người tạo và sở hữu community.
- `DECISION`: Creator tự vận hành community trong MVP; không có Community Admin hoặc Moderator riêng.
- `DECISION`: Payment giai đoạn đầu dùng **SePay Sandbox**; không xử lý tiền thật. Automated test được phép dùng fake `PaymentGateway`, nhưng luồng demo tích hợp SePay Sandbox.
- `DECISION`: Paid community trong MVP dùng **một mức giá truy cập trả một lần**. Subscription định kỳ được để sau MVP.
- `DECISION`: Giao diện lấy Skool làm tham chiếu cho bố cục `Community / Classroom / Calendar`, nhưng không sao chép thương hiệu, nội dung, hình ảnh hoặc yêu cầu pixel-perfect.
- `DECISION`: Hoàn thiện và ổn định toàn bộ Must scope ở local trước khi chọn hosting.
- `DECISION`: Video trong lesson dùng URL hoặc embed từ nền tảng ngoài; S-cool không tự streaming/transcoding video.
- `DECISION`: Dự án chỉ dùng MySQL; không duy trì compatibility song song với MariaDB.
- `FACT`: Công nghệ bắt buộc gồm Laravel, MySQL, Vite, Tailwind CSS và Alpine.js.

## 2. Product statement

S-cool là nền tảng multi-community riêng tư dành cho creator muốn xây dựng và tự vận hành cộng đồng học tập cho từng nhóm cụ thể mà không phải ghép nhiều hệ thống rời rạc. Trong một community, creator có thể mời đúng thành viên, tổ chức thảo luận, nội dung học, tiến độ lesson, sự kiện và quyền truy cập. Member có thể tham gia nhiều community được mời, học nội dung, trao đổi và theo dõi hoạt động trong cùng một trải nghiệm.

S-cool khác một LMS đơn lẻ ở việc kết hợp learning content với community interaction và membership. S-cool cũng không cố thay thế toàn bộ LMS, mạng xã hội, công cụ họp hoặc nền tảng video chuyên dụng; phiên bản đầu chỉ tập trung vào một golden flow hoàn chỉnh, phân quyền đúng và có thể vận hành ổn định ở local.

## 3. Người dùng và vấn đề

### Người dùng chính: Creator

Creator có thể là giảng viên, mentor, coach, chuyên gia, nhà sáng tạo nội dung hoặc nhóm đào tạo. Họ cần một nơi tập trung để:

- Tạo và cấu hình community.
- Quản lý membership và quyền truy cập.
- Đăng nội dung thảo luận.
- Tổ chức course, lesson và event.
- Theo dõi hoạt động cơ bản của member.
- Mời đúng người vào từng private community.
- Cấp quyền sau một IPN SePay Sandbox hợp lệ đối với paid community.

### Người dùng tham gia: Member

Member cần:

- Nhận và chấp nhận lời mời dành cho đúng email của mình.
- Biết rõ mình được truy cập nội dung nào.
- Học lesson và ghi nhận tiến độ.
- Đăng bài, bình luận và tham gia event.

### Vấn đề cốt lõi

Creator thường phải quản lý nội dung, thảo luận, sự kiện, thông tin thành viên và quyền truy cập bằng nhiều công cụ. Việc phân mảnh này làm tăng công việc thủ công và nguy cơ sai trạng thái membership hoặc quyền truy cập. Member cũng phải chuyển đổi giữa nhiều nơi để học, trao đổi và theo dõi hoạt động.

S-cool giải quyết bài toán kỹ thuật và trải nghiệm nêu trên trong phạm vi đồ án. Dự án không tuyên bố đã chứng minh product-market fit hoặc nhu cầu trả tiền trên thị trường thật.

## 4. Mục tiêu của phiên bản đầu

1. **Multi-community đúng và an toàn:** một user có thể tạo hoặc tham gia nhiều community, nhưng dữ liệu và quyền của các community phải được cách ly.
2. **Creator tự vận hành được community:** creator có thể cấu hình community, quản lý member, nội dung thảo luận, course, lesson và event mà không cần vai trò trợ lý.
3. **Member hoàn thành được hành trình học tập cơ bản:** tham gia community, xem feed, học lesson, đánh dấu tiến độ và xem event.
4. **Invite-only access có workflow rõ ràng:** free invitation kích hoạt membership sau khi người được mời xác thực; paid invitation chỉ kích hoạt membership sau IPN SePay Sandbox hợp lệ.
5. **MVP chạy ổn định và tái lập ở local:** golden flow, failure flow, authorization tests, migration, seed data và production frontend build đều hoạt động.

## 5. Không phải mục tiêu của phiên bản đầu

- Trở thành LMS hoàn chỉnh với thi cử, chấm điểm, chứng chỉ, SCORM hoặc ngân hàng câu hỏi.
- Tự xây livestream, video conference hoặc hạ tầng streaming/transcoding video.
- Xử lý payment thật, payout, KYC, thuế, chargeback hoặc merchant-of-record.
- Hỗ trợ subscription định kỳ, nhiều pricing tier, coupon, trial, invoice hoặc refund.
- Xây mạng xã hội công khai có recommendation, trending hoặc discovery phức tạp.
- Cho phép tìm kiếm, duyệt danh sách, public landing hoặc tự do xin/join community.
- Xây mobile application.
- Hỗ trợ team nhiều người cùng quản trị một community.
- Chọn hosting, cloud architecture, Kubernetes hoặc microservices trước khi local readiness đạt.
- Chứng minh S-cool có product-market fit hoặc tạo ra kết quả học tập được đảm bảo.

## 6. Actors và quyền hạn

### 6.1 Visitor

Visitor là người chưa đăng nhập hoặc chưa tham gia community.

Được phép:

- Xem trang giới thiệu chung của S-cool.
- Đăng ký hoặc đăng nhập.
- Mở invitation URL, nhưng chỉ tiếp tục nếu đăng nhập bằng email khớp lời mời còn hiệu lực.

Không được phép:

- Duyệt danh sách, tìm kiếm hoặc xem landing page của community.
- Biết community tồn tại chỉ bằng cách đoán slug/ID.
- Xem feed, course, lesson, member list hoặc event nội bộ.
- Đăng bài, bình luận hoặc ghi nhận tiến độ.
- Truy cập trang quản lý của creator hay platform.

### 6.2 Member

Member là user có membership `ACTIVE` trong một community.

Được phép trong community đã tham gia:

- Xem feed và nội dung đã publish.
- Tạo, sửa và xóa bài viết của chính mình.
- Tạo, sửa và xóa bình luận của chính mình.
- Xem course/lesson được publish.
- Đánh dấu hoặc bỏ đánh dấu lesson đã hoàn thành.
- Xem danh sách event và link tham gia khi event cho phép.
- Xem trạng thái membership của chính mình.
- Rời community.

Không được phép:

- Truy cập nội dung của community chưa tham gia.
- Quản lý community, member, course, lesson hoặc event.
- Chỉnh sửa/xóa nội dung của người khác.
- Xem payment hoặc thông tin quản trị của member khác.

### 6.3 Creator

Creator là user tạo và sở hữu community. Một user có thể vừa là creator ở community của mình, vừa là member ở community khác.

Được phép trong community mình sở hữu:

- Tạo, xem và cập nhật thông tin community.
- Chọn access mode `FREE` hoặc `PAID` khi tạo community; visibility luôn là private trong MVP.
- Mời member theo email, thu hồi lời mời chưa dùng và gửi lại lời mời khi cần.
- Với paid community, thiết lập một mức giá truy cập một lần qua SePay Sandbox.
- Xem danh sách member và trạng thái membership.
- Kích hoạt, suspend hoặc remove member trong phạm vi cho phép.
- Tạo, sửa, publish, unpublish và xóa course, section/module, lesson.
- Tạo, sửa và hủy event.
- Đăng bài, bình luận và xóa nội dung vi phạm trong community.
- Xem các payment SePay Sandbox thuộc community của mình.

Không được phép:

- Truy cập hoặc quản lý community của creator khác.
- Thay đổi user hoặc dữ liệu cấp platform.
- Thay đổi access mode sau khi community đã có membership trong MVP.
- Tự đánh dấu payment SePay Sandbox thành công bằng trang quản trị creator.
- Xử lý tiền thật, payout hoặc refund thật.
- Chuyển quyền sở hữu community trong MVP.

### 6.4 Platform Admin

Platform Admin vận hành toàn bộ nền tảng, không phải trợ lý của creator.

Quyền tối thiểu trong MVP:

- Đăng nhập bằng account có role platform admin được cấu hình trước.
- Xem danh sách user và community.
- Xem trạng thái tổng quát cần cho xử lý sự cố.
- Suspend hoặc reactivate community vi phạm/lỗi.

Không được phép trong workflow thông thường:

- Tạo hoặc chỉnh sửa course/lesson thay creator.
- Tạo bài hoặc bình luận dưới danh nghĩa member.
- Sửa payment SePay Sandbox để cấp quyền tùy ý.
- Đọc secret hoặc credential của user/payment provider.

### 6.5 SePay Sandbox

SePay Sandbox là external actor mô phỏng checkout/IPN mà không phát sinh tiền thật. Domain Billing giữ `PaymentGateway` contract để code nghiệp vụ không phụ thuộc trực tiếp vào SDK hoặc URL môi trường.

Trách nhiệm:

- Nhận yêu cầu checkout one-time bằng VND.
- Trả user về success/cancel URL để phục vụ trải nghiệm.
- Gửi IPN tới HTTPS endpoint công khai của S-cool.
- Cho phép kiểm thử success, cancel/failure và duplicate delivery trong sandbox.

SePay Sandbox không quyết định trực tiếp quyền truy cập; S-cool chỉ cập nhật membership sau khi xác minh IPN, invoice/reference, amount, currency và trạng thái hợp lệ.

## 7. Quy tắc multi-community

1. Một user có thể sở hữu nhiều community và tham gia nhiều community khác.
2. Mỗi community có đúng một creator trong MVP.
3. Creator tự động có toàn quyền trong community mình tạo; không cần community admin trung gian.
4. Mỗi cặp `user + community` chỉ có tối đa một membership hiện hành.
5. Membership, post, comment, course, lesson, progress, event và payment record phải thuộc đúng một community.
6. User chỉ được xem nội dung nội bộ khi có membership `ACTIVE`, trừ creator của chính community và Platform Admin trong phạm vi xử lý sự cố được cho phép.
7. Biết ID hoặc URL của resource không đồng nghĩa có quyền truy cập resource đó.
8. Creator của community A không có quyền creator trong community B.
9. Member bị `SUSPENDED`, `REMOVED` hoặc đã `LEFT` không còn quyền truy cập nội dung nội bộ.
10. Platform Admin có quyền cấp platform nhưng không được xem như creator mặc định của mọi community.
11. Community bị platform suspend không cho creator/member thực hiện hoạt động mới cho đến khi được reactivate.
12. Slug community là duy nhất trên toàn platform trong MVP.
13. Community không được liệt kê công khai; unauthorized request không được làm lộ tên, member hoặc nội dung của community.
14. Invitation được ràng buộc với community và email cụ thể, có hạn dùng, token một lần và có thể bị creator thu hồi.

## 8. Mô hình truy cập community

### 8.1 Private free community

- Creator tạo invitation cho một email cụ thể.
- Invitee đăng ký/đăng nhập bằng đúng email và chấp nhận invitation còn hiệu lực.
- Hệ thống đánh dấu invitation `ACCEPTED` và tạo membership `ACTIVE` một cách idempotent.
- Không có public join, shareable invitation không giới hạn hoặc approval queue trong MVP.

### 8.2 Private paid community qua SePay Sandbox

- Community có đúng một mức giá one-time bằng VND.
- Creator gửi invitation cho một email cụ thể.
- Invitee xác thực đúng email, chấp nhận lời mời và bắt đầu checkout; hệ thống tạo membership `PENDING_PAYMENT` cùng payment `PENDING`.
- Success page không tự cấp quyền.
- Chỉ IPN SePay Sandbox hợp lệ mới chuyển payment sang `SUCCEEDED` và membership sang `ACTIVE`.
- Payment `FAILED` hoặc `CANCELLED` không cấp quyền.
- IPN gửi lặp không tạo payment hoặc membership trùng.
- Refund, recurring renewal, proration và expiration theo chu kỳ nằm ngoài MVP.

## 9. Golden flows

### 9.1 Creator tạo và chuẩn bị community

1. Creator đăng ký/đăng nhập → hệ thống xác thực user.
2. Creator chọn tạo community → nhập tên, slug nội bộ, mô tả, ảnh đại diện và access mode `FREE` hoặc `PAID`.
3. Nếu chọn `PAID` → creator nhập một mức giá one-time bằng VND cho SePay Sandbox.
4. Hệ thống kiểm tra dữ liệu và slug → tạo community thuộc creator.
5. Creator tạo course, section/module và lesson → lưu ở trạng thái draft.
6. Creator publish nội dung sẵn sàng → member hợp lệ có thể xem.
7. Creator tạo event với thời gian, mô tả và external meeting URL.
8. Creator tạo invitation cho email của member thử nghiệm và kiểm tra community từ góc nhìn member.

Kết quả: private community có nội dung học, event và invitation tối thiểu để đúng nhóm được tham gia.

### 9.2 Member tham gia private free community

1. Creator tạo invitation `FREE` cho email của member.
2. Invitee mở invitation URL và đăng ký/đăng nhập bằng đúng email.
3. User chọn `Accept invitation` → hệ thống kiểm tra token, email, expiry, community state và access mode.
4. Hệ thống đánh dấu invitation accepted và tạo/kích hoạt membership `ACTIVE` trong transaction.
5. User được chuyển vào community → xem feed, classroom và event.

Kết quả: chỉ đúng người được mời có một membership active trong community.

### 9.3 Member tham gia private paid community qua SePay Sandbox

1. Creator tạo invitation cho email của member trong paid community.
2. Invitee xác thực đúng email và chấp nhận → S-cool tạo membership `PENDING_PAYMENT` cùng payment `PENDING`.
3. User được chuyển tới SePay Sandbox và hoàn thành hoặc hủy checkout.
4. SePay gửi IPN → S-cool xác minh secret/signature theo cấu hình, invoice/reference, amount, currency và trạng thái.
5. Nếu hợp lệ và thành công → payment thành `SUCCEEDED`, membership thành `ACTIVE` trong cùng workflow nhất quán.
6. Nếu thất bại/hủy → payment thành `FAILED`/`CANCELLED`, membership không được active.
7. Nếu IPN được gửi lại → hệ thống trả `200` an toàn nhưng không tạo dữ liệu trùng.

Kết quả: quyền truy cập yêu cầu cả invitation hợp lệ và kết quả SePay Sandbox đã xác minh, không dựa vào redirect phía trình duyệt.

### 9.4 Member học và tương tác

1. Member active mở community → hệ thống kiểm tra membership và tenant context.
2. Member đọc feed, tạo post hoặc comment → nội dung được gắn đúng community và author.
3. Member mở course/lesson đã publish → hệ thống kiểm tra quyền truy cập.
4. Member đánh dấu lesson hoàn thành → progress được lưu theo user, lesson và community.
5. Member mở event → xem thời gian và external meeting URL.
6. Member quay lại community → thấy đúng nội dung và tiến độ của mình.

Kết quả: hoạt động và progress không xuất hiện ở community khác.

### 9.5 Creator vận hành community

1. Creator mở dashboard community mình sở hữu → hệ thống kiểm tra ownership.
2. Creator xem member và trạng thái membership.
3. Creator publish/unpublish nội dung hoặc cập nhật event.
4. Creator xóa post/comment vi phạm nếu cần.
5. Creator suspend/remove member → user mất quyền nội bộ theo trạng thái mới.
6. Creator xem payment SePay Sandbox của community nhưng không thể tự sửa kết quả thanh toán.

Kết quả: creator tự vận hành được community mà không cần Community Admin/Moderator.

### 9.6 Platform Admin xử lý community

1. Platform Admin đăng nhập → hệ thống xác nhận platform-level role.
2. Admin xem danh sách community và trạng thái tổng quát.
3. Admin suspend community → creator/member không thể tạo hoạt động mới hoặc xem nội dung nội bộ theo policy đã định.
4. Admin reactivate community → quyền truy cập trở lại theo membership hiện có.

Kết quả: nền tảng có cơ chế kiểm soát tối thiểu mà không can thiệp vào vận hành nội dung hằng ngày.

## 10. Failure flows tối thiểu

| Tình huống | Hành vi mong muốn | Trạng thái sau cùng | Kiểm thử bắt buộc |
|---|---|---|---|
| User đoán slug/ID community private | Trả `404` hoặc phản hồi không làm lộ community | Không lộ metadata/nội dung | Privacy/feature test |
| Invitation sai email, hết hạn, đã thu hồi hoặc đã dùng | Từ chối với thông báo an toàn | Không tạo membership | Invitation integration test |
| Invitation được submit lặp | Trả membership hiện có nếu cùng user hợp lệ | Một invitation acceptance và một membership | Idempotency test |
| Member community A dùng URL/ID của community B | Từ chối ở server-side | Không thay đổi dữ liệu | Tenant-isolation test |
| Creator A sửa resource của Creator B | Từ chối dù request được tạo thủ công | Resource B không đổi | Authorization test |
| SePay Sandbox checkout thất bại/hủy | Hiển thị kết quả; không cấp quyền | Payment `FAILED`/`CANCELLED`; membership không active | Integration test |
| User chỉ truy cập success URL | Không cấp quyền nếu chưa có event hợp lệ | Payment vẫn `PENDING` | Feature test |
| SePay IPN gửi lặp | Nhận diện transaction/order đã xử lý | Không trùng payment/membership | Idempotency test |
| IPN sai secret/signature/reference/amount/currency | Từ chối và ghi log an toàn | Không cấp quyền | Security/integration test |
| Member bị suspend/remove | Chặn nội dung nội bộ | Membership không active | Authorization test |
| Creator unpublish lesson | Member không còn thấy lesson | Progress cũ được giữ | Feature test |
| Creator xóa post/comment của member | Nội dung không còn hiển thị | Có trạng thái xóa theo thiết kế | Feature test |
| Community bị platform suspend | Chặn hoạt động theo policy | Dữ liệu được giữ nguyên | End-to-end test |

## 11. MVP capabilities và acceptance criteria

| ID | Capability | Priority | Acceptance criteria tóm tắt |
|---|---|---|---|
| MVP-01 | Account & Authentication | Must | User đăng ký, đăng nhập, đăng xuất, khôi phục mật khẩu và cập nhật profile cơ bản; route riêng tư yêu cầu authentication. |
| MVP-02 | Community Management | Must | Creator tạo nhiều community với slug duy nhất, cập nhật thông tin và xem dashboard chỉ của community mình sở hữu. |
| MVP-03 | Multi-community Membership | Must | User tham gia nhiều community; mỗi community có membership và trạng thái riêng; không truy cập chéo dữ liệu. |
| MVP-04 | Private Access Modes | Must | Mọi community là private; creator chọn `FREE` hoặc `PAID`; không public join và không đổi mode sau khi đã có membership. |
| MVP-05 | Community Feed | Must | Active member tạo/sửa/xóa post và comment của mình; creator xóa nội dung trong community mình. |
| MVP-06 | Classroom | Must | Creator quản lý course → section/module → lesson với draft/published; active member chỉ xem nội dung published. |
| MVP-07 | Lesson Progress | Must | Active member đánh dấu hoàn thành/bỏ hoàn thành lesson; progress tách theo user và community. |
| MVP-08 | Events | Must | Creator tạo/sửa/hủy event với thời gian và external URL; active member xem event của community. |
| MVP-09 | Invitation Management | Must | Creator mời email cụ thể, revoke invitation chưa dùng; chỉ đúng email chấp nhận invitation hợp lệ; xử lý lặp không tạo membership trùng. |
| MVP-10 | SePay Sandbox | Must | Paid checkout tạo payment pending; verified IPN success kích hoạt membership; failure/cancel không cấp quyền; duplicate IPN không tạo dữ liệu trùng. |
| MVP-11 | Creator Member Management | Must | Creator xem member và có thể suspend/remove; thay đổi có hiệu lực với quyền truy cập. |
| MVP-12 | Platform Administration tối thiểu | Must | Platform Admin xem user/community và suspend/reactivate community; không sửa nội dung thay creator. |
| MVP-13 | Private Community Boundary | Must | Community không xuất hiện trong public list/search; user không được mời không xem metadata hoặc nội dung bằng slug/ID. |
| MVP-14 | Authorization & Tenant Isolation | Must | Các policy/guard quan trọng có automated tests cho member, creator và platform admin. |

## 12. Quy tắc sản phẩm chi tiết

### Community

- Creator có thể tạo nhiều community.
- Community có slug duy nhất, tên, mô tả, ảnh đại diện, access mode và trạng thái; visibility cố định là private trong MVP.
- Trạng thái tối thiểu: `ACTIVE`, `SUSPENDED`, `ARCHIVED`.
- Chỉ `ACTIVE` community cho phép accept invitation và hoạt động bình thường.
- Community chỉ xuất hiện trong danh sách của creator hoặc member/invitee hợp lệ; không có public directory.
- Creator có thể archive community; khôi phục hoặc xóa vĩnh viễn để sau MVP nếu làm tăng rủi ro dữ liệu.

### Membership

- Invitation có trạng thái `PENDING`, `ACCEPTED`, `REVOKED`, `EXPIRED`; invitation không tự tạo quyền member.
- Membership có trạng thái tối thiểu: `PENDING_PAYMENT`, `ACTIVE`, `SUSPENDED`, `REMOVED`, `LEFT`.
- Free invitation hợp lệ tạo/kích hoạt membership `ACTIVE`.
- Paid invitation hợp lệ tạo membership `PENDING_PAYMENT` và chỉ chuyển sang `ACTIVE` sau IPN SePay thành công hợp lệ.
- Creator có thể chuyển `ACTIVE` sang `SUSPENDED` hoặc `REMOVED`.
- Member có thể chuyển membership của mình sang `LEFT`.
- Việc tái tham gia sau `REMOVED` cần creator cho phép; chi tiết kỹ thuật được chốt trong Domain & Architecture.

### Feed

- Post/comment thuộc đúng một community và một author.
- Member chỉ sửa/xóa nội dung của mình.
- Creator có thể xóa nội dung trong community mình.
- Reaction, report queue và moderation workflow nhiều cấp để sau MVP.

### Classroom

- Course chứa nhiều section/module; section/module chứa nhiều lesson.
- Creator quản lý thứ tự bằng trường order rõ ràng.
- Lesson hỗ trợ văn bản và external URL/video embed.
- Chỉ nội dung published hiển thị cho member.
- Progress chỉ được ghi cho member active và lesson published mà họ được phép xem.

### Events

- Event có tiêu đề, mô tả, thời gian bắt đầu/kết thúc, timezone, external URL và trạng thái.
- Creator tạo, cập nhật hoặc hủy event.
- Member xem event của community; đăng ký RSVP riêng không thuộc Must scope.

### Invitation

- Invitation thuộc đúng một community và một email đã normalize.
- Token được sinh ngẫu nhiên, chỉ lưu dạng hash, có `expires_at`, `accepted_at` và `revoked_at`.
- Chỉ user đã xác thực email khớp invitation mới được accept.
- Creator chỉ xem và revoke invitation trong community mình sở hữu.
- Không có invitation link dùng chung hoặc không giới hạn người sử dụng trong MVP.

### SePay Sandbox

- Một paid community có một mức giá one-time bằng VND tại một thời điểm.
- Payment record không được chỉnh trực tiếp bởi creator.
- Redirect success không phải bằng chứng thanh toán.
- IPN phải được xác thực và có transaction/order reference để xử lý idempotent.
- Không lưu thông tin thẻ hoặc credential nhạy cảm trong S-cool.

## 13. Sau MVP

Chỉ bắt đầu khi toàn bộ Must scope đạt local readiness gate.

| Capability | Giá trị | Điều kiện xem xét |
|---|---|---|
| Community Admin/Moderator | Chia sẻ vận hành với creator | Có nhu cầu thật từ community lớn |
| Reaction và content reporting | Tăng tương tác và moderation | Feed core ổn định |
| Gamification: point/level/leaderboard | Khuyến khích tương tác | Có quy tắc chống abuse và bằng chứng cần thiết |
| In-app/email notifications | Nhắc hoạt động quan trọng | Queue và preference model rõ |
| Community discovery/search | Giúp user tìm community | Chỉ xem xét nếu product/legal decision cho phép public visibility |
| Shared invite link/approval queue | Mời nhóm lớn nhanh hơn | Có policy chống chia sẻ ngoài nhóm và audit phù hợp |
| File/document upload | Nội dung phong phú hơn | Storage, authorization và malware/size policy rõ |
| Course access rules | Drip, level lock, private course | Classroom core ổn định |
| RSVP và event reminder | Quản lý tham dự | Event core ổn định |
| Analytics/dashboard | Hỗ trợ creator đánh giá hoạt động | Metrics và mục đích đo rõ |
| Recurring subscription | Mô hình membership thực tế hơn | Payment thật, cancellation và reconciliation được thiết kế |
| Ownership transfer | Giảm phụ thuộc một creator | Audit và security workflow được thiết kế |
| Hosting/staging/production | Chạy ngoài local | Local readiness gate đạt |

## 14. Out of scope của MVP

| Hạng mục | Lý do loại | Điều kiện xem xét lại |
|---|---|---|
| Payment production | Chưa cần xử lý tiền và nghĩa vụ thật | Sau khi SePay Sandbox workflow ổn định |
| Recurring subscription, refund, payout | Tăng mạnh state và rủi ro tài chính | Giai đoạn production billing |
| Community Admin/Moderator | Creator tự vận hành đủ cho MVP | Có bằng chứng creator cần chia sẻ quyền |
| Native video streaming/transcoding | Tốn storage, bandwidth và processing | Có nhu cầu và hạ tầng phù hợp |
| Native video conference | Không phải năng lực cốt lõi | External meeting URL không đáp ứng |
| Full LMS exam/certificate/SCORM | Vượt mục tiêu learning content nhẹ | Mentor hoặc user yêu cầu rõ |
| Realtime chat | Tăng hạ tầng và moderation | Async feed không đáp ứng |
| Mobile app | Web responsive đủ cho MVP | Web ổn định và có nhu cầu mobile |
| Affiliate, tax, KYC, merchant-of-record | Nghiệp vụ tài chính/pháp lý lớn | Giai đoạn thương mại thật |
| AI recommendation/content generation | Không phục vụ golden flow cốt lõi | Có dữ liệu, use case và đánh giá riêng |
| Public community directory/landing/join | Trái với quyết định private group hiện tại | Mentor/product owner thay đổi rõ privacy/legal boundary |
| Kubernetes/microservices | Không phù hợp quy mô team và local-first | Có tải hoặc tổ chức buộc phải tách |

## 15. Demo scenario chuẩn

Một lần demo MVP thành công phải thể hiện được:

1. Creator đăng nhập và tạo hai community để chứng minh multi-community.
2. Creator cấu hình một private free community và một private paid community.
3. Creator tạo/publish course, lesson, post và event trong từng community.
4. Creator mời Member A theo email; A chấp nhận free invitation và truy cập đúng nội dung.
5. Creator mời Member B; B hoàn thành SePay Sandbox checkout và chỉ được cấp quyền sau IPN hợp lệ.
6. Invitation sai email/hết hạn bị từ chối; payment failure không cấp quyền; IPN lặp không tạo membership trùng.
7. Member hoàn thành lesson và quay lại vẫn thấy progress.
8. Member/Creator cố truy cập resource của community khác và bị từ chối.
9. Creator suspend một member và quyền truy cập bị thu hồi.
10. Platform Admin suspend/reactivate một community mà không chỉnh nội dung thay creator.

## 16. Tiêu chí MVP hoàn thành

### Product

- [ ] Tất cả capability `Must` đạt acceptance criteria.
- [ ] Demo scenario chuẩn chạy từ đầu đến cuối bằng fresh seed/local setup.
- [ ] UI đủ dùng trên desktop và mobile browser cơ bản; không cần native app.
- [ ] Empty state, validation error và permission denied có phản hồi dễ hiểu.

### Security và data isolation

- [ ] Không truy cập chéo dữ liệu giữa community bằng UI hoặc request thủ công.
- [ ] Creator chỉ quản lý community mình sở hữu.
- [ ] Member chỉ sửa/xóa nội dung của mình.
- [ ] User không được mời không xem được metadata hoặc nội dung community.
- [ ] Invitation sai email/hết hạn/thu hồi không tạo membership.
- [ ] SePay Sandbox không cấp quyền từ redirect phía client.
- [ ] Duplicate/untrusted IPN không làm sai membership.

### Local readiness

- [ ] Cả ba thành viên có thể setup ứng dụng từ tài liệu.
- [ ] Migration và seed/demo data tái lập được.
- [ ] Automated tests quan trọng đạt.
- [ ] Production frontend build chạy mà không cần Vite dev server.
- [ ] Queue/SePay Sandbox IPN và local HTTPS tunnel có cách chạy/debug rõ ràng.
- [ ] Không có secret thật trong repository.
- [ ] Không còn lỗi nghiêm trọng đã biết trong golden flow.

## 17. Trade-offs được chấp nhận

| Quyết định | Lợi ích | Đánh đổi |
|---|---|---|
| Creator tự vận hành | Giảm role, UI và test | Chưa hỗ trợ community team lớn |
| Một creator/community | Ownership đơn giản | Chưa chuyển giao hoặc đồng sở hữu |
| Private invite-only | Giới hạn community theo đúng nhóm | Không có organic discovery/public acquisition |
| Một giá SePay Sandbox | Billing flow rõ và nhỏ | Chưa có tier/subscription |
| Payment một lần | Kiểm thử entitlement dễ hơn | Chưa mô phỏng recurring membership đầy đủ |
| Video embed | Tránh hạ tầng media nặng | Phụ thuộc nền tảng video ngoài |
| External meeting URL | Event đủ dùng | Không có live call native |
| Không public landing/discovery | Giảm lộ thông tin và scope search/ranking | Creator phải chủ động mời đúng người |
| Local-first | Tập trung hoàn thiện nghiệp vụ | Phát hiện vấn đề hosting muộn hơn |
| Modular monolith được đề xuất | Phù hợp team nhỏ | Chưa tách scale độc lập theo service |

## 18. Quyết định liên tài liệu và câu hỏi còn mở

| Nội dung | Trạng thái | Tài liệu chịu trách nhiệm |
|---|---|---|
| Database chuẩn | `ACCEPTED`: MySQL; không support song song MariaDB | `02_DOMAIN_ARCHITECTURE.md` |
| Community visibility | `ACCEPTED`: private, không public discovery/join. `PROPOSED`: email-bound invitation | `02_DOMAIN_ARCHITECTURE.md` |
| Payment integration | `ACCEPTED`: SePay Sandbox sau `PaymentGateway` contract; fake adapter chỉ dùng automated test | `02_DOMAIN_ARCHITECTURE.md`, `04_DEVOPS_OPERATIONS.md` |
| Ngày bắt đầu và capacity | Thời lượng đã chốt 2 tuần; Hoàng xác nhận ngày bắt đầu với mentor và giờ cam kết của từng thành viên | `03_DELIVERY_PLAN.md` |
| CI build frontend | `PROPOSED`: CI và local đều build; hosting không chạy Vite server | `04_DEVOPS_OPERATIONS.md` |
| Hosting | `DEFERRED`: chỉ chọn sau local readiness gate | `04_DEVOPS_OPERATIONS.md` |

Nếu mentor thay đổi một quyết định làm ảnh hưởng Must scope, nhóm cập nhật tài liệu này trước khi thay đổi backlog hoặc database design.
