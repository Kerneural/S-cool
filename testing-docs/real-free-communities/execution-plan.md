# Kế hoạch Thực thi: Tích hợp Dữ liệu Cộng đồng Miễn phí từ Skool (Zero Mock Data)

## 1. Mục tiêu & Nguyên tắc
- **Nhánh**: `real-free-communities`
- **Nguyên tắc**: 100% Zero Mock Data — Tất cả thông tin (Tên, mô tả, số lượng thành viên, video giới thiệu, khóa học, modules, bài học và video bài giảng) được trích xuất trực tiếp từ các cộng đồng Miễn phí, Công khai, Top thành viên trên `skool.com`.
- **Bảo mật**: Triển khai giải pháp bảo mật video 0 đồng (chống download IDM, che giấu đường dẫn gốc qua Blob/HLS, kiểm tra header, chặn context menu).

## 2. Các Bước Thực Hiện Tuần Tự
1. **Giai đoạn 1**: Phân tích yêu cầu & kịch bản xử lý rủi ro (Hoàn tất).
2. **Giai đoạn 2**: Sử dụng Playwright / Chrome DevTools truy cập `https://www.skool.com/discovery?ty=public&lang=english&pr=free&srt=top-v2`, xác minh bộ lọc, cuộn trang lấy danh sách top cộng đồng, bóc tách About metadata, Intro video và toàn bộ giáo trình Classroom.
3. **Giai đoạn 3**: Đối soát dữ liệu (Reconciliation) đảm bảo 1:1 parity với Skool, không thiếu sót module hay bài học nào.
4. **Giai đoạn 4**: Thay thế dữ liệu mock cũ bằng seeder mới, lưu trữ media cục bộ, đồng bộ schema.
5. **Giai đoạn 5**: Thiết lập hệ thống bảo mật video client & backend.
6. **Giai đoạn 6**: Chụp ảnh nghiệm thu tỷ lệ gốc 1:1 và chạy toàn bộ CI test suite.
