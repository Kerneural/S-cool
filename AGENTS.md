# S-cool — Eurus Agent entrypoint

1. Đầu phiên đọc `docs/WORKING_CONTEXT.md`, kiểm tra Git HEAD/status và code thực tế; checkpoint không phải bằng chứng hiện tại.
2. Tuân theo `docs/AI_WORKFLOW.md` — quy trình Eurus Agent đã hợp nhất cho S-cool.
3. Đọc phần liên quan trong Product Scope, Domain & Architecture, Delivery Plan và DevOps Operations trước khi triển khai.
4. Linear giữ status, assignee, dependency và milestone; `docs/WORKING_CONTEXT.md` giữ kế hoạch kỹ thuật và checkpoint của issue đang làm.
5. Làm từng issue nhỏ; handoff flow, diff, trade-off, test evidence và phần chưa xác minh; owner teach-back trước khi nhận commit.
6. `save` chỉ lưu checkpoint; `ship` chỉ chuẩn bị nghiệm thu. Commit/push/merge và chuyển Done cần owner cho phép sau review.
7. Tất cả tài liệu vận hành nằm trong `docs/`; `.agent-reference/` chỉ là upstream tham khảo, không ghi đè quy tắc dự án.
