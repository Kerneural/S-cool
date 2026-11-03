# Bàn giao S-cool — triển khai cho nhiều chuyên gia

Mỗi chuyên gia sở hữu một website riêng và quản lý nhiều cộng đồng trong website đó. Dùng chung mã nguồn; tách dữ liệu, cấu hình và quyền quản trị giữa các khách hàng.

## 1. Tạo riêng cho mỗi website

- Tên miền, CloudPanel Site và Site User.
- Database, tài khoản DB, thư mục uploads và file `.env`.
- `APP_KEY`: tạo mới khi cài website mới; giữ nguyên khi cập nhật website đang dùng.
- Tài khoản Owner đã xác minh, SMTP và cron xử lý hàng đợi đúng thư mục website.
- Cấu hình tên website, URL và tiếng Việt mặc định:

```dotenv
APP_NAME="Tên website của chuyên gia"
APP_URL=https://ten-mien-chuyen-gia
APP_LOCALE=vi
APP_FALLBACK_LOCALE=en
SCOOL_DEPLOYMENT_MODE=expert
SCOOL_OWNER_EMAIL=email-owner-da-xac-minh
```

Có thể dùng chung máy chủ CloudPanel; CPU, RAM và sự cố máy chủ vẫn ảnh hưởng nhiều website. Không dùng chung database, uploads hoặc thông tin bí mật giữa khách hàng.

## 2. Bàn giao bản cài sạch

- Không nhân bản nguyên database hiện tại: có tài khoản demo, nội dung tham khảo và lịch sử nghiệm thu.
- Website mới chỉ có Owner của khách hàng, cấu hình cần thiết và nội dung mẫu được chọn rõ ràng.
- Không mang theo thành viên, mật khẩu demo, lời mời, đơn hàng hoặc dữ liệu khách hàng khác.
- Bàn giao quyền quản lý tên miền, CloudPanel và email gửi thư qua kênh riêng; không ghi mật khẩu vào tài liệu/Git.

## 3. Nghiệm thu trước khi mở sử dụng

- Owner tạo cộng đồng, khóa học, bài học và xuất bản được.
- Đăng ký mới, xác minh email, quên mật khẩu và thư mời hoạt động; kiểm tra cả Spam.
- Member nhận lời mời, học, lưu tiến độ sau tải lại, đăng bài/bình luận và nhận điểm đúng quy tắc.
- Member không truy cập được màn hình quản trị; website khác không thấy dữ liệu của nhau.
- Ảnh và dữ liệu còn nguyên sau cập nhật; cron xử lý hàng đợi hoạt động.

**Phạm vi hiện tại:** đã nghiệm thu luồng mời Owner–Member trên host. Luồng đăng ký mới/quên mật khẩu cần kiểm tra trước khi mở rộng sử dụng. Nếu cần khách tự khám phá và tham gia, phải triển khai/nghiệm thu luồng công khai riêng; bản hiện tại không phải chợ cộng đồng như Skool.

**Thanh toán:** mới nghiệm thu SePay Test Mode. Phải cấu hình tài khoản thanh toán của khách hàng và nghiệm thu giao dịch/cấp quyền trước khi thu tiền thật. DRM chưa được triển khai.

## 4. Cập nhật và vận hành

- Dùng cùng nguồn mã và phiên bản phát hành; ưu tiên tùy chỉnh bằng cấu hình để tránh nhiều bản code lệch nhau.
- Mỗi lần cập nhật: backup DB/uploads → lấy bản đã merge → cài dependencies → build CSS/JS → migrate → cập nhật cache/Livewire → kiểm tra cron và luồng Owner–Member.
- Không chạy lại seed demo hoặc nhập database local để cập nhật website đang có người dùng.
- Giữ nguyên `.env`, `APP_KEY`, dữ liệu và uploads khi cập nhật.
- Chốt người chịu trách nhiệm cập nhật, theo dõi lỗi, backup và khôi phục; kiểm tra khôi phục trước khi cam kết vận hành chính thức.

Đây là checklist bàn giao; các lệnh triển khai/cập nhật thực hiện theo runbook CloudPanel đi kèm bản bàn giao.
