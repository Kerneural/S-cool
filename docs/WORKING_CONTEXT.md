# Working context — S-cool

> Checkpoint tiếp tục công việc, không thay thế tiến độ trên Linear. Cập nhật khi save/handoff/chuyển issue; xác minh lại Git và code khi resume.

## Issue hiện tại

- Updated: 03/10/2026.
- Milestone: M1 — Foundation ready, target 03/10/2026.
- Parent: EUR-5 — Identity & Local Bootstrap.
- Issue: [EUR-16 — Bootstrap Laravel and authentication](https://linear.app/eurusdevsec/issue/EUR-16/bootstrap-laravel-and-authentication).
- Owner: Hoàng; reviewer: Tiến.
- Checkpoint thực tế: bootstrap chưa bắt đầu; không có Laravel app/Composer manifest khi kiểm tra.
- Git HEAD khi onboarding: `05a6f71a2e1cbce43ad1315474f1dee764426904`; verify on resume.
- Tests/runtime: NOT RUN; chưa có app runner.
- Working tree có thay đổi tài liệu và agent setup chưa commit; preserve khi resume.

## Outcome và boundaries

Laravel khởi động với MySQL; đăng ký/đăng nhập/đăng xuất/reset password có server-side validation. Sai credentials bị từ chối; auth feature tests đạt; reset email kiểm tra qua Mailpit khi local mail infrastructure sẵn sàng. AC chính thức nằm trong Linear issue.

- Không thêm social login/MFA.
- Không đổi database khỏi MySQL.
- Không triển khai Community/Payment trong issue này.

## Technical plan / decision còn mở

1. Kiểm tra PHP/Composer/Node/Docker và chọn official Laravel auth scaffolding tương thích Blade/Alpine; ghi trade-off.
2. Xác định application root và file dự kiến tạo trước bootstrap; giữ docs và AGENTS hiện tại.
3. Bootstrap, cấu hình auth/MySQL; viết/chạy feature tests phù hợp.
4. Handoff request flow, session lifecycle, password hashing và failure case để owner teach-back.

File topology và test commands: bổ sung sau khi chọn scaffolding; không đoán trước runtime.

## Evidence và next action

- App tests/build: NOT RUN.
- PR/implementation commit: chưa có.
- Owner teach-back: chưa thực hiện.
- Next action: kiểm tra runtime và chốt scaffolding cho EUR-16.

Đọc tiếp: AI_WORKFLOW, phần auth/actors trong Product Scope, identity/stack trong Domain & Architecture, local setup trong DevOps Operations.
