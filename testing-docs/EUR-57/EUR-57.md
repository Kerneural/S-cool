> Historical screenshot gallery on runtime 6179e3e389ddc8041568bd56a114d96fa044d401. See [corrected interpretation and current invitation retest](EUR-57-summary.md). Screenshots alone do not establish browser version, elapsed time or a current-candidate PASS.

# Luồng Guest &  Xác thực

## LUỒNG ĐĂNG KÍ, ĐĂNG NHẬP, ĐĂNG XUẤT

### Màn hình đăng kí
![alt text](image.png)
### Màn hình mailpit gửi email xác nhận
![alt text](image-1.png)
### Màn hình đăng nhập sau khi đăng kí
![alt text](image-2.png)
### Sau khi đăng xuất back lại page trước về lại homepage
![alt text](image-3.png)



## lUỒNG GIA LỜI MỜI GIA NHẬP COMMUNITY

### Creator gửi email mời Member1
![alt text](image-4.png)
### Giao diện mời thành công
![alt text](image-5.png)
### Mailpit nhận được email lời mời
![alt text](image-6.png)
### Historical invitation landing without a usable token
The screenshot shows a missing/malformed-token denial. It does not independently reproduce failure of an original valid email link. A separate valid-token runtime defect was subsequently reproduced and fixed locally; see the summary.
![alt text](image-7.png)

# Luồng member

## Tính năng bài feed - bài viết - bình luận

### Giao diện đăng bài viết
![alt text](image-8.png)
### Giao diện bình luận & thả react
![alt text](image-9.png)
### Nhấn F5 bài viết và bình luận vẫn còn nguyên vẹn
![alt text](image-10.png)

## Tính năng live search

### Gỡ từ khóa tìm kiếm khóa học
![alt text](image-11.png)

## Tính năng Classroom & tiến độ học tập

### Vào khóa học được xuất bản
![alt text](image-12.png)
### Giao diện nhấn hoàn thành bài học
![alt text](image-13.png)
- [x] Tiến độ vẫn được giữ nguyên (historical submitted observation)
- [x] Đăng nhập lại vẫn giữ nguyên tiến độ (historical submitted observation)
![alt text](image-14.png)

## Tính năng Events
### Giao diện Event
![alt text](image-15.png)
### Xem chi tiết lịch sự kiện
![alt text](image-16.png)

# Luồng creator

## Quản lý cộng động và cài đặt
### Thay đổi trạng thái Community và nhấn F5
Đổi tên Community từ AI Newbie sang AI Beginner
![alt text](image-17.png)
![alt text](image-18.png)

## Quản lý thành viên và lời mời
### Xem danh sách thành viên Community
![alt text](image-19.png)
### Thu hồi một lời mời chưa kích hoạt
Chuyển trạng thái lời mời từ `PENDING` sang `REVOKED`
![alt text](image-20.png)
![alt text](image-21.png)

## Tính năng SePay Test mode
### BLOCKED: payment execution was not performed in the submitted run
No confirmed payment or Mailpit defect follows from the invitation screenshot. Provider-backed acceptance remains separate.

# Luồng admin

## Quản lý tài khoản toàn sàn
### Xem danh sách Community
![alt text](image-22.png)
### Tạm khóa Community
![alt text](image-24.png)
### Không truy cập Commnunity tạm khóa
![alt text](image-23.png)
### Kích hoạt lại Community
![alt text](image-25.png)
### Từ chối member truy cập trang admin
![alt text](image-26.png)
