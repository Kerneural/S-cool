# Bộ tài liệu S-cool

> Trạng thái: Bốn tài liệu sống để team tự viết, review và cập nhật trong quá trình phát triển.

## Cấu trúc hiện tại

`00_PROJECT_OVERVIEW.md` là tài liệu nền để nắm bức tranh toàn dự án. Nó không phải tài liệu phải cập nhật hằng ngày.

Bốn tài liệu sống là:

1. `01_PRODUCT_SCOPE.md` — vấn đề, actors, golden flow, MVP và out-of-scope.
2. `02_DOMAIN_ARCHITECTURE.md` — domain model, multi-tenancy, authorization, workflows và trade-off kỹ thuật.
3. `03_DELIVERY_PLAN.md` — milestones, vertical slices, ownership, test và Definition of Done.
4. `04_DEVOPS_OPERATIONS.md` — local environment, CI, build, secrets, database, queue và vận hành.

Các outline discovery cũ đã được loại khỏi workspace; sử dụng bốn tài liệu sống ở trên.

Quy tắc team và agent sử dụng AI: [AI_WORKFLOW.md](AI_WORKFLOW.md). Đọc trước khi triển khai hoặc nhận commit.

Quy trình Eurus Agent được hợp nhất trong `AI_WORKFLOW.md`; checkpoint và technical plan cho issue đang làm tại [WORKING_CONTEXT.md](WORKING_CONTEXT.md). Linear vẫn là nguồn theo dõi tiến độ. `AGENTS.md` ở root là entrypoint để agent đọc đúng các tài liệu này.

## Các quyết định hiện tại

- `DECISION`: S-cool là nền tảng multi-community theo yêu cầu mentor.
- `DECISION`: Mọi community MVP là private và invite-only; không public discovery/join.
- `PROPOSED`: Invitation ràng buộc với email cụ thể để bảo đảm đúng nhóm; Hoàng xác nhận trước migration.
- `DECISION`: Payment giai đoạn đầu dùng SePay Sandbox; payment production xem xét sau.
- `DECISION`: UI lấy Community/Classroom/Calendar của Skool làm tham chiếu vừa phải, không sao chép thương hiệu hoặc pixel-perfect.
- `DECISION`: Hoàn thiện và ổn định local trước; hosting được quyết định sau.
- `DECISION`: Database duy nhất của dự án là MySQL; không support song song MariaDB.
- `FACT`: Stack bắt buộc là Laravel, MySQL, Vite, Tailwind CSS và Alpine.js.

## Thứ tự viết

1. Chốt `01_PRODUCT_SCOPE.md` đến mức biết chính xác Must scope và golden flow.
2. Viết phần multi-tenancy, authorization, domain model và state machine trong `02_DOMAIN_ARCHITECTURE.md`.
3. Chia scope thành vertical slices và milestone trong `03_DELIVERY_PLAN.md`.
4. Thiết kế local setup và quality gates trong `04_DEVOPS_OPERATIONS.md`.

Không cần hoàn thiện toàn bộ tài liệu trước khi code. Chỉ cần phần liên quan tới slice sắp làm đủ rõ, sau đó cập nhật tài liệu khi có quyết định mới.

## Quy ước viết

- `FACT`: sự thật đã được kiểm chứng hoặc yêu cầu chính thức từ mentor.
- `ASSUMPTION`: giả định tạm thời, cần kiểm chứng.
- `DECISION`: quyết định đã được nhóm thống nhất.
- `TODO`: nội dung còn thiếu và người chịu trách nhiệm bổ sung.
- `OUT`: chủ động loại khỏi phạm vi.

Quyết định sản phẩm ghi trong Product Scope hoặc Delivery Plan. Quyết định kiến trúc ghi trong Domain & Architecture. Quyết định vận hành ghi trong DevOps & Operations.

## Điều kiện để bắt đầu slice đầu tiên

- [ ] Actors và quyền của slice đã rõ.
- [ ] Acceptance criteria có thể kiểm thử.
- [ ] Tenant boundary và authorization impact đã được xác định.
- [ ] Entity/state liên quan đã đủ rõ để thiết kế migration.
- [ ] Owner và reviewer đã được chỉ định.
- [ ] Local setup hoặc dependency cần thiết đã sẵn sàng.
