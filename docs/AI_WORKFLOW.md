# Quy tắc làm việc với AI — S-cool

Áp dụng cho Hoàng, Tiến, Khoa và agent. Người nhận commit chịu trách nhiệm về thay đổi, kể cả code do AI viết.

## Một issue → một vòng kiểm soát

1. **Chốt hướng:** rõ outcome, scope, AC, dependency và trade-off; Hoàng chốt thay đổi scope/architecture.
2. **Triển khai:** AI làm từng thay đổi nhỏ trong issue, thêm tests phù hợp và cập nhật tài liệu khi decision thay đổi.
3. **Handoff:** AI giải thích flow, diff, lý do lựa chọn, rủi ro bảo mật, kết quả test và phần chưa xác minh.
4. **Teach-back:** owner đọc diff, chạy thử và giải thích lại bằng lời mình: hành vi, dữ liệu, quyền truy cập, trade-off và một failure case.
5. **Review & commit:** reviewer xác nhận AC và evidence; owner hiểu rồi mới nhận commit. Chưa hiểu hoặc chưa kiểm chứng thì giữ `In Review`.

## Quy tắc chung

- Không nhận code chỉ vì chạy được; không merge thay đổi ngoài scope chưa được chốt.
- Auth, tenant isolation và payment phải kiểm tra cả happy path lẫn request trái quyền; Khoa review boundary bảo mật.
- ADR chỉ dành cho quyết định kiến trúc có trade-off đáng kể; ghi ngắn trong Domain & Architecture.
- Linear giữ trạng thái; PR/tests/demo giữ evidence. `Done` nghĩa là AC đã được kiểm chứng.
- AI không tự commit/push hoặc chuyển `Done` khi chưa được owner cho phép.
- Nếu không đủ thời gian review, thu nhỏ issue hoặc nhờ reviewer hỗ trợ trước khi nhận thay đổi.

## Eurus Agent — quy trình dùng chung

Áp dụng từ [eurus-agent](https://github.com/EurusDevSec/eurus-agent), snapshot `4891844118595d1cc0728621bc7b436ff784d36c` ngày 03/10/2026. Các tên dưới đây là quy ước prompt, không tự đăng ký slash command trong ứng dụng.

| Prompt | Hành động |
|---|---|
| `start` / `/init` / `continue` / `/resume` | Đọc WORKING_CONTEXT, Git status/HEAD, code và issue; xác minh checkpoint trước khi tiếp tục. |
| `/spec` / `/challenge` | Đọc outcome/AC trong Linear; kiểm tra boundaries, validation, failure cases, security và dependency. Ghi decision mở trong WORKING_CONTEXT. |
| `/plan` | Ghi file dự kiến sửa/tạo, trade-off, các bước và cách test cho issue hiện tại. |
| `/build` | Triển khai một bước nhỏ theo plan; cập nhật kế hoạch kỹ thuật nếu thiết kế thay đổi. |
| `/test` / `/review` | Chạy kiểm tra liên quan và review diff/AC/security; ghi command, result và phần chưa xác minh. |
| `/grill-me` | Owner giải thích flow, trade-off và failure case; hỏi một câu mỗi lượt. |
| `/ship` | Chuẩn bị handoff, evidence và commit message; owner nghiệm thu trước commit/Done. |
| `save` / `/save` / `cuối ngày` | Cập nhật WORKING_CONTEXT và next action; không tự commit/push. |

Test nhỏ trong vòng lặp; chạy đủ kiểm tra liên quan trước nghiệm thu, không giới hạn cứng 5 giây. Nếu lỗi lặp lại, đổi giả thuyết và kiểm tra nguyên nhân. Giữ thay đổi có sẵn của người dùng, sửa bằng patch và tránh refactor ngoài scope. Không ghi PASS khi chưa chạy test.

## Một nguồn cho mỗi loại thông tin

- Product: `01_PRODUCT_SCOPE.md`.
- Architecture, security boundaries và ADR: `02_DOMAIN_ARCHITECTURE.md`.
- Lịch milestone, ownership và DoD: `03_DELIVERY_PLAN.md`.
- Local/CI/queue/secrets/operations: `04_DEVOPS_OPERATIONS.md`.
- Status, assignee, AC, dependency, evidence lịch sử: Linear issue/Project Update.
- Issue đang làm, technical plan, test evidence mới nhất và next action: `WORKING_CONTEXT.md`. Thay nội dung active issue khi chuyển việc; evidence hoàn thành chuyển vào Linear/PR.

Không tạo thêm ROADMAP, FEATURES, ARCHITECTURE hoặc spec file sao chép các nguồn trên. Đọc phần liên quan của docs khi làm issue; stack và constraints được giữ ở tài liệu sản phẩm/kiến trúc.
