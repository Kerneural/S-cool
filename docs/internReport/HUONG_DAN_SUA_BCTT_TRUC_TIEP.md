# Sửa ngay trên Google Docs gốc — không dựng lại Word

Bản cập nhật hướng dẫn: 03/11/2026. Sửa trực tiếp tài liệu gốc; không dựng thêm bản Word mới.

Tài liệu: https://docs.google.com/document/d/11kexfOD0Ljd-ynL2MAqNmX-WeceTRaqjCEpZmiicOIQ/edit?tab=t.0

Dùng Ctrl+F tìm đúng tên mục bên dưới. Giữ bố cục chương 1–3, kết luận/kiến nghị, ý kiến cá nhân, tài liệu tham khảo và phụ lục. Nội dung doanh nghiệp chương 1 giữ nguyên; sửa lại số đề mục đang lặp 1.1 nếu cần.

## 1. Chốt cách mô tả đề tài

Tên đề tài đề xuất: **Xây dựng nền tảng SaaS hỗ trợ quản lý cộng đồng học tập và khóa học trực tuyến S-cool**.

Thêm cuối mục **2.2.1. Bài toán sản phẩm**:

> S-cool được xây dựng theo mô hình SaaS, trong đó dữ liệu, thành viên và quyền truy cập được giới hạn theo từng cộng đồng. Bên cạnh chế độ nền tảng chung, ứng dụng hỗ trợ cấu hình website chuyên gia: một chủ website có thể quản lý nhiều cộng đồng, khóa học và thành viên từ một đầu mối. Học viên mới đăng ký không tự có quyền quản trị hoặc quyền truy cập cộng đồng. Cấu hình này đáp ứng nhu cầu bàn giao cho chuyên gia mà vẫn giữ kiến trúc và các ranh giới dữ liệu của hệ thống SaaS.

Không đổi đề tài thành “một website chỉ có một cộng đồng”.

## 2. Cập nhật ba mục chức năng thay đổi nhiều nhất

**2.5.2 — thay phần mô tả bảng tin/bình luận cũ bằng:**

> Bảng tin cung cấp ô viết bài, bộ lọc danh mục và lựa chọn sắp xếp được bố trí độc lập. Thành viên có thể đăng nội dung có định dạng, ảnh, tệp được cho phép, liên kết, emoji và bình chọn. Bình luận dùng ô nhập gọn; công cụ bổ sung và ảnh xem trước chỉ chiếm diện tích khi cần. Hệ thống hỗ trợ trả lời một cấp, thích, lưu, ghim và báo cáo nội dung theo quyền. Avatar và tên tác giả dẫn tới hồ sơ trong cộng đồng. Tìm kiếm và đọc nội dung tiếp tục được kiểm soát theo quyền truy cập của người dùng.

**2.5.3 — bổ sung/thay phần mô tả giao diện quản lý và học bài:**

> Chủ cộng đồng quản lý khóa học trong không gian gồm cây chương/bài và vùng soạn thảo của bài đang chọn. Trình soạn thảo trực quan hỗ trợ định dạng, liên kết, bảng, chèn ảnh, xem trước và mở rộng vùng viết. Nội dung bài học được lưu bằng Markdown và kết xuất có kiểm soát. Người học sử dụng mục lục, thao tác bắt đầu hoặc học tiếp và đánh dấu hoàn thành; tiến độ được lưu theo từng người học và bài học. Minh chứng thực hành và phản hồi của chủ cộng đồng được quản lý riêng với trạng thái hoàn thành bài học.

**2.5.6 — đổi tiêu đề thành “Quản trị website, hồ sơ và tin nhắn”, cập nhật:**

> Trang Quản trị website tập hợp các cộng đồng theo dạng danh sách có tìm kiếm, trạng thái và lối vào quản lý. Chủ website có thể quản lý nhiều cộng đồng trong phạm vi được cấp quyền. Thông báo lưu trạng thái đọc theo tài khoản; hồ sơ thể hiện đóng góp và lịch hoạt động 365 ngày. Tin nhắn một-một trong cộng đồng được cập nhật định kỳ mà không cần tải lại toàn trang. Cơ chế hiện tại sử dụng polling, không mô tả là hệ thống WebSocket. Các quyền quản trị và truy cập nội dung vẫn được kiểm tra tại máy chủ.

## 3. Cập nhật bằng chứng kiểm thử

Tại **2.6.2**, bảng kết quả CI, **Kết luận 1.1** và phụ lục minh chứng: thay số liệu 585 tests/4.995 assertions bằng đoạn sau khi đang mô tả bản mới nhất:

> Lượt CI của PR #89 ghi nhận 761 kiểm thử PHP đạt với 9.951 assertions và 85 kiểm thử JavaScript đạt. Bằng chứng này gắn với cây mã nguồn được tích hợp tại revision 8a0982b; kết quả tự động không thay thế nghiệm thu trực tiếp trên máy chủ bàn giao.

Liên kết CI: https://github.com/Kerneural/S-cool/actions/runs/41479275887

PR: https://github.com/Kerneural/S-cool/pull/89

Ảnh CI mới đã có tại `assets/report-update-20261103/ci-761-tests.jpg`; ảnh merge tại `pr-89-merged.jpg` trong cùng thư mục. Nếu giữ số liệu cũ để minh họa lịch sử thì phải ghi rõ là mốc cũ, không trộn với kết quả mới.

## 4. Thay toàn bộ mục 2.8 về Azure

Đổi **2.8. Phương án triển khai và vận hành trên Azure** thành **2.8. Triển khai ứng dụng và chuẩn bị bàn giao trên CloudPanel**.

Giữ năm mục con, đổi tên và thay nội dung như sau:

### 2.8.1. Môi trường triển khai

> Ứng dụng được triển khai tại tên miền lophoc.phung.vn trên máy chủ do đơn vị cung cấp và quản lý qua CloudPanel. Website sử dụng Nginx, PHP-FPM và cơ sở dữ liệu MySQL. Thư mục phục vụ web trỏ tới public của ứng dụng Laravel, tách khỏi các tệp mã nguồn và cấu hình còn lại. Ảnh cấu hình CloudPanel minh họa tên miền và document root đã kiểm tra thực tế.

### 2.8.2. Cập nhật mã nguồn và phụ thuộc

> Bản phát hành được lấy từ main sau khi pull request hoàn tất review và CI. Nhóm đồng bộ dependency bằng lockfile Composer và npm, build CSS/JavaScript và cập nhật tài nguyên Livewire theo hướng dẫn triển khai. Môi trường bàn giao phục vụ tài nguyên đã build, không phụ thuộc Vite development server.

### 2.8.3. Cập nhật schema và dữ liệu

> Migration cập nhật schema theo phiên bản, không reset database. Dữ liệu cộng đồng, khóa học, thành viên và ảnh không tự đồng bộ bằng git pull; việc chuyển dữ liệu được thực hiện riêng với bản sao lưu và phạm vi rõ ràng. APP_KEY, cấu hình database, mail và các giá trị bí mật được giữ theo môi trường nhận, không chép nguyên cấu hình local đè lên host.

### 2.8.4. Tiến trình nền và cấu hình ứng dụng

> Queue cần chạy cùng bản mã nguồn và cấu hình với tiến trình web. Khi cập nhật, nhóm làm sạch và tạo lại cache bị ảnh hưởng, khởi động lại worker theo cơ chế của máy chủ và kiểm tra công việc lỗi. Mailpit chỉ phục vụ kiểm tra local; cấu hình SMTP của môi trường bàn giao được quản lý riêng và không đưa thông tin đăng nhập vào báo cáo.

### 2.8.5. Kiểm tra sau triển khai

> Sau phát hành, nhóm kiểm tra revision đang chạy, migration, tài nguyên giao diện và hành trình sử dụng của chủ cộng đồng và thành viên. Kiểm tra bao gồm đăng nhập, mở cộng đồng, quản lý khóa học, đọc bài học, thảo luận và hiển thị ảnh. HTTP 200 hoặc truy cập được CloudPanel chỉ xác nhận một phần tình trạng hệ thống; kết luận bàn giao cần dựa trên kết quả kiểm tra trực tiếp ở website nhận.

Thay **Hình 2.11** sơ đồ cloud bằng ảnh `host-cloudpanel-domain-root.jpg`. Chú thích: **Hình 2.11. Cấu hình tên miền và thư mục phục vụ ứng dụng Laravel trên CloudPanel**.

Hai bảng thuộc mục 2.8: bỏ hàng Azure/Terraform/Doppler/Datadog; thay bằng Nginx/PHP-FPM, MySQL, frontend build, queue, cấu hình môi trường và kiểm tra sau cập nhật.

Tại câu cuối mục 2.7.3, thay câu dẫn sang Azure bằng: **Quy trình phát hành lên môi trường CloudPanel được trình bày ở mục tiếp theo.**

Chèn NHẬN XÉT Google Docs vào tiêu đề 2.8.5 (bôi đen tiêu đề → Ctrl+Alt+M):

> Ngày 03/11/2026 đã kiểm tra trực tiếp trên host: Owner tạo và xuất bản khóa/bài học; Member nhận thư mời thật, tham gia, hoàn thành bài học và giữ tiến độ sau tải lại; hai vai trao đổi qua bài viết/bình luận và ghi nhận điểm từ lượt thích. Member bị chặn truy cập quản trị. Dữ liệu nghiệm thu đã dọn theo phạm vi được duyệt. Luồng đăng ký mới, quên mật khẩu, thanh toán thật và DRM chưa được nghiệm thu trong lượt này. Thư mời đến Gmail Spam; không mô tả là đã bảo đảm vào Inbox.

## 5. Gom công nghệ chưa thực hiện vào hướng phát triển

Tại **3.4.2**, chỉ giữ MỘT đoạn:

> Trong hướng phát triển, nhóm có thể nghiên cứu triển khai trên Azure, tự động hóa hạ tầng bằng Terraform và bổ sung quan sát vận hành bằng Datadog khi có nhu cầu và tiêu chí đánh giá cụ thể. Bảo vệ video bằng DRM tiếp tục là nội dung nghiên cứu. Các hướng này không được tính vào kết quả triển khai đã hoàn thành của đề tài.

Ctrl+F lần lượt Azure, Terraform, Datadog, Doppler. Ngoài đoạn hướng phát triển trên, bỏ các câu nhận là nhiệm vụ/kết quả triển khai của dự án; xóa Doppler khỏi phạm vi dự án. Không tự xóa thông tin doanh nghiệp có nguồn riêng chỉ vì trùng tên công nghệ.

Sửa cả **2.9.2**, **Kết luận 1.2**, **Ý kiến bản thân / 3. Định hướng phát triển cá nhân** để không còn hứa “triển khai cloud trong cuối tháng 10”. Thay bằng định hướng tiếp tục nâng cao kỹ năng backend, triển khai, kiểm thử và vận hành sau thực tập.

Trong bảng chữ viết tắt: bỏ các hàng riêng cho công nghệ cloud đã loại khỏi phạm vi nếu không dùng nữa. Giữ SaaS, CI/CD, SMTP, RBAC, DRM... có sử dụng trong báo cáo; không gọi quy trình phát hành thủ công là CD tự động.

## 6. Thay hình ngay trên tài liệu gốc

Thư mục ảnh: `assets/report-update-20261103/` (đi kèm hướng dẫn trên cùng nhánh Git).

| Vị trí gốc | Cách sửa | Chú thích mới |
|---|---|---|
| Hình 2.5, bảng tin màn hình hẹp | Thay bằng `local-community-feed.jpg` | Bảng tin và bộ lọc thảo luận của The AI Advantage (môi trường local) |
| Hình 2.6, mục lục 320px | Thay bằng `host-classroom.jpg` | Danh mục khóa học và thao tác bắt đầu học trong The AI Advantage |
| Hình 2.9, bài viết/bình luận fixture | Thay bằng `local-discussion.jpg` | Bài viết và bình luận trong The AI Advantage (môi trường local) |
| Hình 2.10, community QA sau khôi phục | Thay bằng `host-website-hub.jpg`; chuyển tới 2.5.6 | Quản trị website với danh sách cộng đồng và thao tác quản lý |
| Hình 2.11, sơ đồ Azure | Thay bằng `host-cloudpanel-domain-root.jpg` | Cấu hình tên miền và thư mục phục vụ ứng dụng Laravel trên CloudPanel |
| Ảnh bài học/tiến độ khác trong thân bài và phụ lục | Dùng `host-lesson.jpg`: bài Step 1, khóa Get Started with Claude | Giao diện đọc bài học và mục lục khóa học trên CloudPanel |
| Ảnh PR/CI cũ | Dùng `pr-89-merged.jpg` và `ci-761-tests.jpg` | Ghi rõ PR #89; CI 761 PHP/9.951 assertions, 85 JavaScript |

Cách thay: **bấm ảnh cũ → chuột phải → Thay thế hình ảnh → Tải lên từ máy tính**. Sửa chú thích ngay dưới ảnh. Muốn thêm ảnh: **Chèn → Hình ảnh → Tải lên từ máy tính**. Chọn **Cùng dòng với văn bản**, canh giữa, rộng khoảng 15 cm, giữ tỷ lệ.

Giữ logo/sơ đồ doanh nghiệp và sơ đồ kiến trúc/ERD còn đúng. Xóa các ảnh QA/fixture lặp lại, trạng thái rỗng không giúp giải thích chức năng và ảnh chứa nội dung không chuyên nghiệp. Không cần đưa đủ mọi màn hình vào báo cáo.

Dữ liệu AI Advantage là nội dung tham khảo minh họa tính năng; không mô tả là giáo trình do nhóm sáng tác hoặc đã nghiệm thu toàn bộ nội dung. Giao diện tiếng Việt và nội dung bài học tiếng Anh là hai phạm vi khác nhau.

## 7. Tài liệu tham khảo và chốt bố cục

- Thay tài liệu Datadog [1] bằng League CommonMark — Security: https://commonmark.thephpleague.com/2.7/security/ ; chỉ giữ trích dẫn [1] tại nội dung Markdown/an toàn kết xuất.
- Thay Terraform [6] bằng Tiptap Editor: https://tiptap.dev/docs/editor/introduction ; chỉ giữ [6] tại đoạn editor.
- Thay Azure [11] bằng OWASP File Upload Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html ; chỉ giữ [11] ở kiểm tra tệp/upload.
- Thêm CloudPanel — Laravel: https://www.cloudpanel.io/docs/v2/php/applications/laravel/ ; dẫn ở mục 2.8.
- Cập nhật liên kết CI/PR ở mục 3 của hướng dẫn này; bỏ đường dẫn ảnh/checkpoint QA cũ không còn dùng trong phụ lục.
- Sửa mục lục, danh mục bảng và danh mục hình cho khớp tiêu đề/chú thích mới. Mục lục tự động: bấm mục lục → nút cập nhật; nếu là gõ tay thì sửa cả tiêu đề và số trang sau khi chốt ảnh.
- Không chèn mật khẩu, khóa API, nội dung .env hoặc thông tin SMTP vào báo cáo.

## Kết quả dọn local ngày 03/11/2026

Đã sao lưu và xóa đúng 13 community theo yêu cầu, gồm hai community nghiệm thu. Giữ nguyên users và dữ liệu của năm community: The AI Advantage, Clief Notes, AI Automation Society, AI Automation Agency Hub, Max Business School. Trên CloudPanel cũng đã xóa đúng 11 community cũ; kiểm kê giữ lại cùng năm community nêu trên. Hai community nghiệm thu chỉ có ở local. Git pull không mang các thao tác xóa DB này lên host; không chạy lại seeder catalog khi đang muốn giữ bộ dữ liệu đã dọn vì có thể tạo lại community mẫu.

## Bộ ảnh mới chụp lại ngày 03/11/2026 — dùng bộ này

Toàn bộ ảnh trong `assets/report-update-20261103/` được chụp lại trong lượt cập nhật này; không dùng ảnh QA cũ hoặc bộ ảnh local đã chuẩn bị trước đó.

| Ảnh | Nguồn và cách dùng |
|---|---|
| [Lớp học](assets/report-update-20261103/host-classroom.jpg) | Host: danh mục khóa học The AI Advantage |
| [Bài học](assets/report-update-20261103/host-lesson.jpg) | Host: bài Step 1 trong khóa Get Started with Claude; thay ảnh bài học cũ có Markdown thừa |
| [Giới thiệu](assets/report-update-20261103/host-community-about.jpg) | Host: ảnh bìa và lối vào lớp học; có thể thêm vào mục chức năng cộng đồng |
| [Quản trị website](assets/report-update-20261103/host-website-hub.jpg) | Host: đúng năm cộng đồng sau khi dọn catalog |
| [CloudPanel](assets/report-update-20261103/host-cloudpanel-domain-root.jpg) | Cấu hình thực tế: tên miền và document root; đã tránh vùng thông tin đăng nhập/IP quản trị |
| [Bảng tin](assets/report-update-20261103/local-community-feed.jpg) | Local 127.0.0.1:8080: minh họa giao diện bảng tin với nội dung mẫu |
| [Bình luận](assets/report-update-20261103/local-discussion.jpg) | Local 127.0.0.1:8080: hộp thảo luận và ô bình luận gọn |
| [PR đã merge](assets/report-update-20261103/pr-89-merged.jpg) | Chụp lại PR #89 đã merge; không nhận là PR mới phát sinh ngày chụp |
| [Kết quả CI](assets/report-update-20261103/ci-761-tests.jpg) | Chụp lại lượt CI PR #89: 761 PHP / 9.951 assertions; không nhận là chạy lại test ngày chụp |

- Bảng tin AI Advantage trên host hiện chưa có bài sau khi dọn dữ liệu nghiệm thu. Hai ảnh thảo luận dùng dữ liệu local và phải chú thích đúng môi trường; không nhận là bài viết đang có trên host.
- Các ảnh minh họa UI không thay thế bằng chứng nghiệm thu Owner–Member trên host.
- Nội dung AI Advantage là tài liệu tham khảo phục vụ nghiên cứu/minh họa báo cáo; không nhận là nhóm sáng tác hoặc là tài sản thương mại được bàn giao.
- Giữ một tài liệu Google Docs gốc. Không đưa Word cũ, thông tin đăng nhập, log thô hoặc ảnh email cá nhân vào gói chia sẻ.
