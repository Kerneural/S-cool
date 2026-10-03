# S-cool — Tổng quan dự án

> Tên `S-cool` hiện là tên làm việc lấy theo thư mục dự án.  
> Loại tài liệu: Project Overview  
> Trạng thái: Bản nền để cả nhóm cùng hiểu sản phẩm; chưa thay thế requirements hoặc technical design.  
> Cập nhật: 2026-09-25

## 1. Tóm tắt điều hành

S-cool là dự án xây dựng một nền tảng cộng đồng học tập và chia sẻ kiến thức, lấy cảm hứng từ mô hình của Skool. Nền tảng hướng đến những cá nhân hoặc tổ chức muốn tạo một không gian học tập riêng nhưng không muốn tự ghép và vận hành nhiều hệ thống rời rạc cho thảo luận, nội dung khóa học, lịch sự kiện và quản lý thành viên.

Theo yêu cầu đã xác nhận với mentor, S-cool là nền tảng multi-community riêng tư: creator tạo community riêng cho từng nhóm cụ thể và mời đúng người vào; nhóm khác sử dụng community khác. Một member có thể tham gia nhiều community đã được mời. Community có thể miễn phí hoặc trả phí một lần qua SePay Sandbox trong giai đoạn đầu.

S-cool không đặt mục tiêu sao chép toàn bộ Skool. Mục tiêu phù hợp với team ba người là xây một **Skool-like MVP** với bố cục và chức năng Community/Classroom/Calendar ở mức vừa phải, không sao chép thương hiệu hoặc pixel-perfect. Sản phẩm phải có luồng nghiệp vụ xuyên suốt, private access đúng và có thể vận hành được.

## 2. Bối cảnh

Một người muốn xây cộng đồng học tập thường phải kết hợp nhiều công cụ:

- Mạng xã hội hoặc diễn đàn để đăng bài và thảo luận.
- Nền tảng LMS hoặc video để lưu và sắp xếp nội dung học.
- Công cụ lịch và họp trực tuyến để tổ chức workshop, livestream hoặc coaching.
- Cổng thanh toán hoặc quy trình thủ công để thu phí thành viên.
- Bảng tính, email hoặc công cụ quản trị khác để theo dõi người tham gia.

Việc sử dụng nhiều công cụ có thể làm trải nghiệm bị phân mảnh. Creator phải quản lý tài khoản, quyền truy cập và dữ liệu ở nhiều nơi; member phải chuyển đổi giữa nhiều nền tảng để học, hỏi đáp và tham gia sự kiện.

Mô hình của Skool cho thấy có thể gom các nhu cầu chính vào một sản phẩm gồm Community, Classroom, Calendar, membership và gamification. S-cool nghiên cứu mô hình này để xây một phiên bản nhỏ hơn, phù hợp với mục tiêu học tập, năng lực team và thời gian của dự án.

Các nhận định trên hiện là **bối cảnh nghiên cứu**. Nhóm vẫn cần phỏng vấn hoặc quan sát người dùng để xác định vấn đề nào thực sự quan trọng nhất; không nên coi toàn bộ nhận định là sự thật đã được kiểm chứng.

## 3. Vấn đề cấp cao

### Vấn đề của creator

Creator, mentor, giảng viên hoặc nhóm đào tạo cần một nơi để quản lý nội dung, thảo luận, thành viên và hoạt động học tập. Nếu phải tự tích hợp nhiều công cụ, họ có thể gặp khó khăn trong việc quản lý quyền truy cập, duy trì tương tác và theo dõi hoạt động của cộng đồng.

### Vấn đề của member

Member cần một nơi tập trung để tìm nội dung, theo dõi hoạt động học, đặt câu hỏi và tham gia sự kiện. Trải nghiệm phân tán có thể làm họ khó theo dõi lộ trình và giảm động lực tham gia.

### Vấn đề mà nền tảng muốn giải quyết

S-cool hướng tới việc cung cấp một không gian chung cho ba hoạt động:

1. **Kết nối:** đăng bài, bình luận và trao đổi trong community.
2. **Học tập:** tổ chức course, lesson, tài liệu và tiến độ.
3. **Tham gia:** quản lý event, membership và quyền truy cập.

Problem statement dùng cho phạm vi triển khai được duy trì trong `01_PRODUCT_SCOPE.md`.

## 4. Tầm nhìn sản phẩm

> Giúp creator tạo và vận hành một cộng đồng học tập tập trung, đồng thời giúp member học, trao đổi và tham gia hoạt động trong cùng một nền tảng.

Tầm nhìn này dẫn tới các nguyên tắc:

- Community là đơn vị trung tâm của sản phẩm.
- Nội dung học và thảo luận phải liên kết với community cụ thể.
- Quyền truy cập phải rõ ràng và không rò rỉ giữa các community.
- Creator cần quản lý được community mà không cần kiến thức kỹ thuật sâu.
- Member cần tìm được nội dung và hoạt động quan trọng với ít bước nhất.
- Mỗi tính năng trong MVP phải hỗ trợ trực tiếp golden flow hoặc khả năng vận hành tối thiểu.

## 5. Định hướng sản phẩm đang đề xuất

Hướng có giá trị học tập cao nhất là một **nền tảng đa community**:

- Một user có thể tạo hoặc quản lý community.
- Một user cũng có thể tham gia nhiều community khác nhau.
- Mỗi community có nội dung, thành viên, vai trò và cấu hình riêng.
- Dữ liệu của community này không được lộ sang community khác.
- Không có public directory, discovery, landing hoặc self-join; baseline đề xuất là invitation theo email cụ thể.

Đây là mô hình multi-tenant ở mức ứng dụng. Định hướng ban đầu là dùng chung một ứng dụng và một database, trong đó dữ liệu nghiệp vụ được ràng buộc bằng `community_id`, kết hợp authorization policy và test cách ly dữ liệu.

> **Đã chốt ngày 2026-09-25:** Mentor yêu cầu multi-community theo từng nhóm riêng. Vì vậy private boundary, invitation, tenant isolation và kiểm thử chống enumeration/truy cập chéo là yêu cầu nền tảng, không phải tính năng tùy chọn. Cách tiếp cận này giới hạn exposure nhưng không tự động giải quyết toàn bộ nghĩa vụ pháp lý.

## 6. Người tham gia

### Visitor

Người chưa đăng nhập. Họ chỉ xem trang chung, đăng ký/đăng nhập hoặc mở invitation URL; họ không được duyệt hay xem metadata community.

### Member

Người tham gia community để đọc nội dung, học lesson, thảo luận và tham dự sự kiện trong phạm vi quyền của mình.

### Creator

Người tạo và chịu trách nhiệm vận hành một community: cấu hình thông tin, xuất bản nội dung, quản lý thành viên và quyết định hình thức truy cập.

Trong MVP, creator tự vận hành community. Community Admin/Moderator chỉ được xem xét sau MVP nếu xuất hiện nhu cầu chia sẻ công việc quản lý.

### Platform administrator

Người vận hành toàn bộ nền tảng S-cool. Đây không phải admin của một community. Platform administrator xử lý các vấn đề toàn hệ thống như tài khoản vi phạm, cấu hình nền tảng và sự cố vận hành.

### External systems

Các hệ thống có thể tích hợp gồm payment gateway, email service, object storage và nền tảng họp trực tuyến. Chỉ tích hợp hệ thống thực sự cần cho golden flow.

Danh sách actor ở mức sản phẩm được hoàn thiện trong `01_PRODUCT_SCOPE.md`; ma trận authorization kỹ thuật được duy trì trong `02_DOMAIN_ARCHITECTURE.md`.

## 7. Các khu vực chức năng

### 7.1 Identity và Account

- Đăng ký, đăng nhập và đăng xuất.
- Khôi phục mật khẩu.
- Quản lý hồ sơ cơ bản.
- Xác định một user đang tham gia hoặc quản lý community nào.

### 7.2 Community và Membership

- Tạo community.
- Thiết lập tên, slug, mô tả và hình ảnh.
- Creator mời một email cụ thể; invitation có hạn dùng, revoke và one-time acceptance.
- Quản lý thành viên, trạng thái membership và vai trò.
- Kiểm tra quyền truy cập ở cấp community.

### 7.3 Community Feed

- Đăng bài trong community.
- Bình luận và phản hồi cơ bản.
- Reaction nếu nằm trong MVP.
- Báo cáo hoặc moderation nội dung ở mức tối thiểu.

### 7.4 Classroom

- Tạo course.
- Sắp xếp nội dung thành section/module và lesson.
- Lesson có thể chứa văn bản, liên kết, tài liệu hoặc video nhúng.
- Theo dõi tiến độ học cơ bản.
- Kiểm soát quyền truy cập vào nội dung.

Classroom của MVP không được mặc định mở rộng thành LMS hoàn chỉnh. Thi cử, chấm điểm, chứng chỉ, SCORM hoặc ngân hàng câu hỏi chỉ được bổ sung nếu mentor yêu cầu rõ ràng và nhóm điều chỉnh phạm vi.

### 7.5 Calendar và Events

- Tạo sự kiện có thời gian, mô tả và link tham gia.
- Hiển thị lịch hoạt động của community.
- Cho phép member xem hoặc đăng ký tham dự nếu cần.

MVP không tự xây hạ tầng livestream hoặc video conference. Sự kiện có thể liên kết tới công cụ bên ngoài.

### 7.6 Access và Payment

- Community miễn phí vẫn yêu cầu invitation hợp lệ, không có public join.
- Payment trong giai đoạn đầu dùng SePay Sandbox; payment production được xem xét sau.
- Hệ thống phải tiếp nhận và xác minh SePay IPN trước khi cấp quyền.
- IPN lặp lại không được tạo nhiều membership hoặc payment trùng.
- Trạng thái payment, subscription và quyền truy cập phải được tách biệt rõ.

Payout cho creator, KYC, thuế, chargeback và merchant-of-record là nghiệp vụ tài chính lớn. Chúng không nên được tuyên bố là đã giải quyết nếu dự án chỉ mô phỏng hoặc dùng sandbox.

### 7.7 Gamification

Điểm, level và leaderboard có thể giúp tăng tương tác, nhưng không phải nền móng của hệ thống. Nhóm chỉ bắt đầu phần này sau khi community, classroom và authorization hoạt động ổn định.

### 7.8 Notifications

Thông báo trong ứng dụng hoặc email có thể dùng cho lời mời, sự kiện hoặc hoạt động quan trọng. Queue nên được dùng cho tác vụ không cần hoàn thành ngay trong HTTP request.

## 8. Golden flow cấp cao

Golden flow dự kiến dùng để kiểm tra toàn bộ sản phẩm:

1. Creator đăng ký và tạo một community.
2. Creator cấu hình community và xuất bản ít nhất một nội dung học hoặc sự kiện.
3. Creator mời email cụ thể; member đăng ký/đăng nhập đúng email và chấp nhận invitation.
4. Nếu community trả phí, member hoàn tất SePay Sandbox checkout và hệ thống nhận IPN hợp lệ.
5. Membership được kích hoạt và member nhận đúng quyền truy cập.
6. Member xem nội dung, tham gia thảo luận và ghi nhận tiến độ học.
7. Creator tự quản lý hoạt động trong phạm vi community.

Đây mới là flow cấp cao. Golden flow, failure path và acceptance criteria được nhóm tự hoàn thiện trong `01_PRODUCT_SCOPE.md`; state machine kỹ thuật được duy trì trong `02_DOMAIN_ARCHITECTURE.md`.

## 9. Giá trị mang lại

### Đối với creator

- Giảm số lượng công cụ phải quản lý.
- Tập trung nội dung, thành viên và hoạt động tại một nơi.
- Dễ xây trải nghiệm học có cộng đồng hỗ trợ.
- Có cơ sở triển khai community miễn phí hoặc trả phí.

### Đối với member

- Nội dung và thảo luận nằm trong cùng một community.
- Dễ theo dõi lesson và hoạt động sắp tới.
- Có nơi đặt câu hỏi và kết nối với người cùng mục tiêu.
- Quyền truy cập minh bạch theo membership.

### Đối với nhóm phát triển

- Thực hành xây modular monolith bằng Laravel.
- Giải quyết bài toán multi-tenancy và authorization thực tế.
- Làm việc với queue, webhook, storage và database migration.
- Xây pipeline CI/CD, monitoring, backup và rollback có bằng chứng.

## 10. Mô hình kinh doanh tham chiếu

Có hai tầng doanh thu cần phân biệt:

### Creator kiếm tiền từ member

- Phí thành viên định kỳ.
- Quyền truy cập trả một lần.
- Khóa học, workshop hoặc coaching nâng cao.
- Community miễn phí làm đầu vào cho sản phẩm/dịch vụ trả phí.

### Chủ nền tảng kiếm tiền từ creator hoặc giao dịch

- Phí sử dụng nền tảng theo tháng/năm.
- Phí phần trăm hoặc phí cố định trên giao dịch.
- Gói nâng cấp theo tính năng hoặc quy mô.

Mô hình tham chiếu của Skool hiện kết hợp subscription SaaS và transaction fee. Đây là dữ liệu nghiên cứu đối thủ, không có nghĩa S-cool phải triển khai toàn bộ mô hình tài chính trong MVP.

## 11. Công nghệ bắt buộc

Các ràng buộc sau đến từ mentor:

| Thành phần | Công nghệ | Vai trò |
|---|---|---|
| Backend | Laravel | Web application, business logic, authentication, authorization, queue và integration |
| Database | MySQL | Database duy nhất cho dữ liệu quan hệ, trạng thái nghiệp vụ và queue trong MVP |
| Frontend build | Vite | Build CSS và JavaScript thành production assets |
| UI/CSS | Tailwind CSS | Xây giao diện và design system ở mức utility |
| Frontend interaction | Alpine.js | Tương tác nhỏ phía trình duyệt như modal, dropdown, tab và trạng thái form |

Ứng dụng phù hợp với cách tiếp cận server-rendered bằng Laravel Blade kết hợp Alpine.js. Không có yêu cầu xây SPA hoặc chạy một frontend application server riêng.

### Ý nghĩa của yêu cầu build frontend ở local

- Trong lúc phát triển, developer có thể dùng Vite development server.
- Trước khi triển khai, Vite tạo CSS/JavaScript production đã được bundle và version hóa.
- Hosting phục vụ các static assets đã build; không chạy Vite development server.
- Nhóm cần hỏi mentor CI có được phép thực hiện production build hay artifact bắt buộc phải build trên máy local.

### Quyết định phiên bản

Phiên bản Laravel, PHP, Node.js, Tailwind, Alpine và database chưa được chốt. Nhóm chỉ nên pin phiên bản sau khi kiểm tra môi trường hosting và thời gian hỗ trợ, tránh chọn phiên bản tùy ý trên từng máy.

## 12. Kiến trúc cấp cao được đề xuất

### Phong cách kiến trúc

Sử dụng **modular monolith**: một ứng dụng Laravel duy nhất nhưng chia domain rõ ràng. Đây là lựa chọn phù hợp hơn microservices đối với team ba người vì giảm chi phí triển khai, giao tiếp giữa service và debugging, trong khi vẫn cho phép tách trách nhiệm trong codebase.

Các domain dự kiến:

- Identity
- Communities & Membership
- Community Content
- Learning
- Events
- Billing & Entitlements
- Gamification
- Notifications

### Sơ đồ thành phần

```text
Browser
   |
   v
Web server / Reverse proxy
   |
   v
Laravel modular monolith ---------------------> External services
   |                 |                          - SePay Sandbox
   |                 |                          - Email provider
   |                 |                          - Meeting links
   |                 v
   |              Queue worker
   |
   +-------------> MySQL
   |
   +-------------> Redis (deferred; chỉ thêm sau MVP khi có nhu cầu được đo)
   |
   +-------------> File/Object storage
```

### Nguyên tắc dữ liệu

- Community là ranh giới tenant ở mức nghiệp vụ.
- Các bảng phụ thuộc community cần khóa ngoại và phạm vi truy vấn rõ ràng.
- Authorization được kiểm tra phía server; không dựa vào việc ẩn nút trên giao diện.
- SePay IPN cần authentication/validation và idempotency.
- File private không được lộ chỉ vì người dùng biết URL.
- Migration phải có khả năng chạy trong quy trình deploy.

Database schema chi tiết chỉ được thiết kế sau khi actors, golden flow và MVP scope được duyệt.

## 13. DevOps trong dự án

DevOps không phải phần trang trí hoặc danh sách công cụ. Mục tiêu là chứng minh team có thể đưa một thay đổi đã kiểm tra từ repository lên môi trường chạy được và có thể phát hiện, phục hồi khi xảy ra lỗi.

### Năng lực vận hành nên hướng tới

- Môi trường local nhất quán cho ba thành viên.
- Pull request có automated quality gates.
- Production frontend assets được build có thể tái lập.
- Application artifact hoặc container image được version hóa.
- Có môi trường staging để kiểm tra golden flow.
- Deploy có migration, health check và rollback procedure.
- Secret không nằm trong source code hoặc Git history.
- Có application logs và theo dõi uptime.
- Có backup database và ít nhất một lần kiểm chứng restore.

### Pipeline tham chiếu

```text
Commit / Pull Request
        |
        v
Lint + Static analysis + Tests + Frontend build
        |
        v
Build versioned artifact/image
        |
        v
Deploy staging -> Migrate -> Health check -> Smoke test
        |
        +---- lỗi ----> Dừng hoặc rollback
        |
        +---- đạt ----> Release evidence
```

### Những công nghệ chưa nên mặc định đưa vào

- Kubernetes.
- Microservices.
- Service mesh.
- Multi-region deployment.
- Hệ thống streaming video tự quản lý.
- Nhiều công cụ IaC/configuration management cùng lúc chỉ để tăng số lượng công nghệ.

Những công nghệ trên chỉ có ý nghĩa khi một yêu cầu cụ thể buộc phải dùng chúng.

## 14. Tổ chức nhóm hiện tại

Team có ba người:

| Thành viên | Thông tin hiện có | Nội dung còn phải tự đánh giá |
|---|---|---|
| Hoàng | Chốt quyết định product/architecture, điều phối và theo dõi các module trọng yếu; muốn phát triển năng lực DevOps để phục vụ xin việc | Laravel, frontend, database, thời gian cam kết và mức kinh nghiệm DevOps hiện tại |
| Tiến | Chuyên fullstack | Công nghệ đã dùng, feature từng làm, khả năng test/review và thời gian tham gia |
| Khoa | Chuyên cyber, vẫn làm fullstack được; theo đánh giá của lead có tư duy nhạy hơn Tiến | Bằng chứng năng lực, mảng cyber mạnh nhất, kinh nghiệm application security và thời gian tham gia |

Phân công chi tiết nằm trong `03_DELIVERY_PLAN.md`. Hoàng giữ quyền quyết định cuối về scope, architecture và ưu tiên; việc phân công vẫn dựa trên timebox hai tuần, mục tiêu học, bằng chứng năng lực và bus factor, không chỉ dựa trên nhận xét ai mạnh hơn.

## 15. Phạm vi định hướng

### Candidate MVP

Danh sách dưới đây là đầu vào để nhóm lựa chọn, chưa phải scope đã được duyệt:

- Account và authentication.
- Tạo và cấu hình community.
- Membership và role cơ bản.
- Feed, post và comment.
- Course, module/section, lesson và progress cơ bản.
- Event với link họp bên ngoài.
- Private invitation flow cho free membership.
- SePay Sandbox flow cho paid invited membership.
- Moderation và platform administration tối thiểu.
- Authorization và tenant-isolation tests.
- Staging deployment có health check.

### Candidate later

- Reaction, point, level và leaderboard.
- Email notification và reminder.
- Drip content hoặc level-based unlock.
- Dashboard analytics.
- Nhiều pricing tier.

### Candidate out of scope

- Native video transcoding/streaming.
- Native video conference.
- Chat realtime hoàn chỉnh.
- Mobile app.
- Affiliate payout.
- KYC, thuế và merchant-of-record thực.
- Full LMS exam/certificate/SCORM.
- AI recommendation.
- Kubernetes và microservices.

Scope chính thức được thiết lập trong `01_PRODUCT_SCOPE.md`; kế hoạch thực thi dùng timebox hai tuần và acceptance của buổi demo.

## 16. Rủi ro chính

### Scope quá rộng

Community, LMS, event, payment và DevOps đều có thể trở thành dự án riêng. Nếu không đóng băng MVP, team dễ có nhiều màn hình nhưng không có flow nào hoàn chỉnh.

### Rò rỉ dữ liệu giữa community

Multi-community làm tăng rủi ro authorization. Mọi endpoint quan trọng cần có test user/community không hợp lệ.

### Payment bị đánh giá thấp

Một checkout thành công trên giao diện chưa chứng minh membership đúng. Webhook, idempotency, failure state và access control mới là phần quan trọng.

### Media làm tăng chi phí và độ phức tạp

Upload video có thể kéo theo storage, processing, bandwidth và access control. MVP nên ưu tiên embed hoặc giới hạn loại file.

### DevOps chiếm mất thời gian sản phẩm

Nếu chạy theo nhiều tool, nhóm có thể không hoàn thành golden flow. DevOps nên phát triển theo nhu cầu thực của application qua từng giai đoạn.

### Một người giữ kiến thức trọng yếu

Payment, security và deployment cần reviewer/backup. Dự án không nên phụ thuộc hoàn toàn vào một thành viên.

## 17. Tiêu chí thành công cấp dự án

Các tiêu chí dưới đây là định hướng, cần được đo trong timebox hai tuần:

- Một creator hoàn thành được golden flow trên staging.
- Một member nhận đúng quyền truy cập và không truy cập được dữ liệu community khác.
- Team có test cho các authorization boundary quan trọng.
- Pipeline kiểm tra code, test và production frontend build.
- Có quy trình deploy, health check và rollback được ghi lại.
- Có backup/restore evidence nếu dự án vận hành dữ liệu lâu dài.
- Mỗi thành viên giải thích được domain mình phụ trách và hiểu luồng end-to-end.
- Bản demo phân biệt rõ tính năng đã hoạt động, tính năng mô phỏng và tính năng ngoài phạm vi.

## 18. Những quyết định còn mở

1. Ngày bắt đầu, ngày demo và acceptance chính thức?
2. CI có được build frontend artifact theo cách hiểu chính thức của mentor không?
3. Hosting hoặc môi trường triển khai nào sẽ được xem xét sau khi local readiness đạt?
4. Production/demo tương lai có cần public Internet hay chỉ staging nội bộ?

Các câu trả lời và quyết định phải được ghi trong tài liệu sống tương ứng: Product Scope, Domain & Architecture, Delivery Plan hoặc DevOps & Operations.

## 19. Trạng thái hiện tại

- Repository chưa được khởi tạo trong thư mục dự án.
- Chưa có application code.
- Chưa pin phiên bản Laravel, PHP, Node.js và MySQL.
- Domain model và quy tắc tenant đã có; physical schema/migrations chưa được triển khai.
- Danh sách Must scope, actors, golden/failure flows và readiness gate đã được chốt trong bốn tài liệu sống.
- Đã chốt multi-community private/invite-only, MySQL duy nhất, Docker Compose local, database queue, Redis sau MVP, SePay Sandbox và local-first.
- Timebox implementation là hai tuần; Hoàng là người chốt quyết định và theo dõi các module trọng yếu.
- Đã rút gọn bộ tài liệu thành bốn tài liệu sống: Product Scope, Domain & Architecture, Delivery Plan và DevOps & Operations.
- Bước tiếp theo là review tài liệu với team, chốt ngày bắt đầu/capacity, khởi tạo repository/backlog và triển khai vertical slice đầu tiên.

## 20. Trình tự làm việc tiếp theo

1. Cả nhóm review bốn tài liệu sống và phản biện Must scope, state machine, tenant boundary.
2. Hoàng xác nhận ngày bắt đầu, ngày demo và acceptance với mentor.
3. Tiến và Khoa cam kết số giờ thực tế trong 10 ngày làm việc.
4. Pin phiên bản công nghệ, khởi tạo repository, Docker Compose và CI baseline.
5. Tạo Linear project/backlog nếu team chốt đề xuất trong Delivery Plan.
6. Triển khai VS-01; kiểm chứng fresh setup trên ít nhất hai máy trước khi mở rộng dependency.
7. Theo dõi checkpoint D1/D3/D5/D8/D10; mọi scope change phải do Hoàng quyết định và ghi lại.

## 21. Tài liệu tham chiếu

- Skool Pricing: <https://www.skool.com/pricing>
- Skool pricing models: <https://help.skool.com/article/215-how-to-setup-pricing-for-the-group>
- Skool member roles: <https://help.skool.com/article/74-member-roles>
- Skool Classroom: <https://help.skool.com/article/166-what-is-classroom>
- Skool video support: <https://help.skool.com/article/58-video>
- Skool points and levels: <https://help.skool.com/article/31-how-do-points-and-levels-work>
- Skool payments FAQ: <https://help.skool.com/article/86-subscriptions-faq>
- Laravel Vite documentation: <https://laravel.com/framework/docs/vite>
- Laravel deployment documentation: <https://laravel.com/framework/docs/deployment>
- SePay Sandbox: <https://developer.sepay.vn/vi/cong-thanh-toan/sandbox>
- SePay IPN: <https://developer.sepay.vn/vi/cong-thanh-toan/IPN>
- Linear concepts: <https://linear.app/docs/conceptual-model>
- Linear keyboard shortcuts: <https://linear.app/docs/creating-issues>

Các nguồn tham chiếu mô tả sản phẩm/công nghệ bên ngoài tại thời điểm nghiên cứu. Chúng không tự động trở thành yêu cầu của S-cool.
