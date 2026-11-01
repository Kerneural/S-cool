<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1" aria-label="Breadcrumb">
                    <a href="{{ url('/') }}" class="hover:text-teal-600 transition">Trang chủ</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-800">Trợ giúp & Hướng dẫn tác vụ</span>
                </nav>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                    {{ __('Trung tâm Hướng dẫn & Tài liệu S-cool') }}
                </h2>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold bg-amber-500/10 text-amber-800 border border-amber-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Môi trường Thử nghiệm (Test Mode)
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{ activeTab: 'member' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Hero Banner -->
            <div class="bg-gradient-to-br from-slate-900 via-teal-950 to-slate-900 rounded-3xl p-8 sm:p-10 text-white shadow-lg relative overflow-hidden border border-teal-900/40">
                <div class="relative z-10 max-w-3xl space-y-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        Hướng dẫn tác vụ trực quan
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight leading-tight">
                        Chỉ dẫn chi tiết từng bước cho mọi vai trò trên S-cool
                    </h1>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        Tìm hiểu cách tham gia cộng đồng, học tập trong Classroom, nộp bài thực hành Proof of Work, quản lý xuất bản khóa học và xử lý sự cố trong môi trường an toàn.
                    </p>

                    <div class="pt-2 flex flex-wrap gap-3 text-xs sm:text-sm">
                        <a href="{{ route('communities.index') }}" class="px-5 py-2.5 bg-teal-500 hover:bg-teal-400 text-slate-950 rounded-xl font-bold shadow-md shadow-teal-500/20 transition duration-150">
                            Khám phá Cộng đồng
                        </a>
                        <a href="#test-mode-notice" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold transition border border-white/15 backdrop-blur-xs">
                            Cảnh báo Thanh toán Thử nghiệm
                        </a>
                    </div>
                </div>
            </div>

            <!-- Critical Safety Notice: SePay Test Mode Notice (AC-02, AC-04) -->
            <div id="test-mode-notice" class="bg-amber-50/90 border border-amber-300/80 rounded-2xl p-6 sm:p-7 shadow-xs">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-amber-100 text-amber-800 rounded-xl shrink-0 mt-0.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-sm font-bold text-amber-950 uppercase tracking-wider">
                            Ranh giới Bảo mật & Thử nghiệm: Chế độ SePay Test Mode
                        </h3>
                        <p class="text-sm text-amber-900/90 leading-relaxed font-medium">
                            Gia nhập cộng đồng trả phí, mua khóa học lẻ và gia hạn thành viên trên nền tảng này hoạt động hoàn toàn ở chế độ <strong>SePay Test Mode</strong>.
                            Các khóa học đi kèm yêu cầu quyền thành viên đang hiệu lực; các khóa học độc lập cần có quyền riêng biệt tương ứng. Quyền truy cập có thời hạn sẽ tự hết hạn sau 30 ngày (theo giờ UTC) mà không tự động trừ tiền.
                        </p>
                        <div class="mt-2 text-sm text-rose-800 font-bold bg-amber-100/90 p-3.5 rounded-xl border border-amber-300/80">
                            ⚠️ TUYỆT ĐỐI KHÔNG chuyển tiền thật từ ứng dụng ngân hàng của bạn. Mọi mã QR, số tài khoản và thông tin webhook trên hệ thống đều là dữ liệu giả lập phục vụ kiểm thử.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role-Based Task Navigation Tabs (AC-01, AC-05) -->
            <div class="border-b border-slate-200">
                <nav class="flex space-x-6" aria-label="Danh mục hướng dẫn">
                    <button type="button"
                            @click="activeTab = 'member'"
                            :class="activeTab === 'member' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                            class="py-3 px-1 border-b-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500/20 rounded-t">
                        📘 Hướng dẫn Học viên & Thành viên
                    </button>
                    <button type="button"
                            @click="activeTab = 'creator'"
                            :class="activeTab === 'creator' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                            class="py-3 px-1 border-b-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500/20 rounded-t">
                        🛠️ Creator Studio & Vận hành Khóa học
                    </button>
                    <button type="button"
                            @click="activeTab = 'troubleshooting'"
                            :class="activeTab === 'troubleshooting' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                            class="py-3 px-1 border-b-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500/20 rounded-t">
                        🔧 Xử lý Sự cố & Khôi phục
                    </button>
                </nav>
            </div>

            <!-- Tab 1: Learner & Member Guides -->
            <div x-show="activeTab === 'member'" class="space-y-8" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Task M1: Onboarding & Account Verification -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">M1</span>
                            <h3 class="text-base font-bold text-slate-900">Gia nhập Cộng đồng & Xác thực Tài khoản</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Cách đăng ký tài khoản, chấp nhận mã lời mời và duy trì phiên đăng nhập cookie ổn định.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Các bước thực hiện:</div>
                            <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
                                <li>Mở đường link lời mời nhận được qua email (hoặc trong Mailpit ở môi trường local).</li>
                                <li>Đăng nhập hoặc đăng ký tài khoản tại <code class="bg-white px-1 py-0.5 rounded border border-slate-200">127.0.0.1:8080/login</code>.</li>
                                <li>Xác thực email qua đường link có chữ ký số (signed URL) gửi về hộp thư.</li>
                                <li>Bấm <strong>Chấp nhận lời mời</strong> (Accept Invitation) tại trang đích của cộng đồng.</li>
                            </ol>
                        </div>
                        <div class="text-xs text-teal-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kết quả: Trạng thái thành viên chuyển thành ACTIVE; mở khóa quyền truy cập Classroom và Feed thảo luận.
                        </div>
                    </div>

                    <!-- Task M2: Searching Content Across 3 Surfaces -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">M2</span>
                            <h3 class="text-base font-bold text-slate-900">Tìm kiếm Nội dung Được cấp quyền (3 Bề mặt)</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            S-cool cung cấp công cụ tìm kiếm trực tiếp, phản hồi nhanh và chịu lỗi gõ phím trên 3 điểm truy cập.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Các vị trí tìm kiếm & tính năng:</div>
                            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                                <li><strong>Bề mặt 1 (Dashboard)</strong>: Tìm kiếm trực tiếp danh sách cộng đồng có chống rung (debounce); nhận diện lỗi gõ phím như <code class="bg-white px-1 py-0.5 rounded border border-slate-200">dokcer</code>.</li>
                                <li><strong>Bề mặt 2 (Classroom)</strong>: Lọc nhanh các khóa học và chương mục bên trong cộng đồng hiện tại mà không tải lại trang.</li>
                                <li><strong>Bề mặt 3 (Typeahead)</strong>: Ô tìm kiếm toàn cầu trên thanh điều hướng với gợi ý phân loại và ưu tiên kết quả khớp chính xác.</li>
                            </ul>
                        </div>
                        <div class="text-xs text-teal-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kết quả: Truy vấn SQL giới hạn chỉ quét dữ liệu được cấp quyền, bảo vệ metadata của các khóa học riêng tư.
                        </div>
                    </div>

                    <!-- Task M3: Course Learning & Submitting Proof of Work -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">M3</span>
                            <h3 class="text-base font-bold text-slate-900">Học tập trong Classroom & Nộp bài Proof of Work</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Theo dõi bài giảng theo lộ trình, xem video hướng dẫn và nộp minh chứng kết quả thực hành.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Quy trình học và nộp bài:</div>
                            <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
                                <li>Mở khóa học bạn có quyền truy cập từ thẻ <strong>Classroom</strong> của cộng đồng.</li>
                                <li>Đọc nội dung bài học định dạng Markdown, làm theo các bước thực hành và chạy thử mã nguồn.</li>
                                <li>Với các bài học thực hành, tìm khối <strong>Nộp bài tập Proof of Work</strong> ở cuối bài.</li>
                                <li>Dán link kho mã nguồn GitHub, đường dẫn demo hoặc log kết quả từ terminal rồi bấm nộp.</li>
                            </ol>
                        </div>
                        <div class="text-xs text-teal-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kết quả: Tiến độ bài học được ghi nhận; bài nộp chuyển vào hàng chờ chấm duyệt của Creator.
                        </div>
                    </div>

                    <!-- Task M4: Paid Access Simulation via SePay Test Mode -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">M4</span>
                            <h3 class="text-base font-bold text-slate-900">Thanh toán Mẫu & Trải nghiệm Phí Gia nhập</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Cách thử nghiệm quy trình thanh toán vào cộng đồng tính phí và mua khóa học độc lập.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Mô phỏng thanh toán:</div>
                            <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
                                <li>Tại thẻ cộng đồng có phí hoặc trang Giới thiệu, bấm <strong>Tiếp tục thanh toán</strong> (Continue to checkout).</li>
                                <li>Kiểm tra mã tham chiếu giao dịch và số tiền chuyển khoản bằng Việt Nam Đồng (VND).</li>
                                <li>Ở môi trường kiểm thử, bộ giả lập webhook IPN hoặc test runner sẽ gửi tín hiệu xác nhận hợp lệ.</li>
                                <li>Khi hệ thống nhận webhook hợp lệ, đơn hàng lập tức chuyển từ chờ xử lý sang thành công.</li>
                            </ol>
                        </div>
                        <div class="text-xs text-teal-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kết quả: Giao dịch được lưu với trạng thái succeeded; cấp quyền truy cập trọn đời hoặc có thời hạn ngay lập tức.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Creator Studio & Operations -->
            <div x-show="activeTab === 'creator'" class="space-y-8" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Task C1: Creating a Community & Setting Rules -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">C1</span>
                            <h3 class="text-base font-bold text-slate-900">Thiết lập Cộng đồng & Nội quy Hoạt động</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Khởi tạo không gian làm việc riêng, thiết lập quy chuẩn ứng xử và hình ảnh đại diện.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Checklist thiết lập:</div>
                            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                                <li>Vào <strong>Dashboard &gt; Cộng đồng sở hữu &gt; Tạo cộng đồng</strong>.</li>
                                <li>Điền tên duy nhất, đường dẫn slug, mô tả và đặt giá theo VNĐ (hoặc chọn Miễn phí).</li>
                                <li>Tải ảnh bìa lên (định dạng JPEG/PNG/WebP, dung lượng tối đa 2MB, tự động co giãn).</li>
                                <li>Soạn thảo các quy định onboarding để xây dựng văn hóa giao tiếp văn minh.</li>
                            </ul>
                        </div>
                        <div class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kết quả: Cộng đồng được tạo cùng các danh mục bài viết mặc định (Thông báo, Hỏi đáp, Thành tựu).
                        </div>
                    </div>

                    <!-- Task C2: Building Curriculum in Classroom -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">C2</span>
                            <h3 class="text-base font-bold text-slate-900">Xây dựng Khung Khóa học & Quy trình Xuất bản</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Sắp xếp giáo trình theo các chương học logic và bài học tích hợp video.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Các bước biên soạn:</div>
                            <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
                                <li>Trong tab Classroom của cộng đồng bạn làm chủ, bấm <strong>Tạo khóa học</strong> (Create Course).</li>
                                <li>Thêm các Chương học (Course Sections) để phân bổ lộ trình hợp lý.</li>
                                <li>Thêm bài học kèm đường link nhúng video (Loom, YouTube, hoặc video MP4 máy chủ).</li>
                                <li>Soạn thảo bài học bằng Markdown chuẩn; đánh dấu <em>Bài học thực hành</em> nếu cần học viên nộp bài.</li>
                                <li>Chuyển trạng thái khóa học từ <strong>DRAFT</strong> (Bản nháp) sang <strong>PUBLISHED</strong> (Đã xuất bản) khi hoàn thiện.</li>
                            </ol>
                        </div>
                        <div class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kết quả: Bài học ở trạng thái nháp sẽ được ẩn hoàn toàn đối với học viên cho đến khi xuất bản chính thức.
                        </div>
                    </div>

                    <!-- Task C3: Reviewing Proof of Work Submissions -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">C3</span>
                            <h3 class="text-base font-bold text-slate-900">Chế độ Creator Review & Phản hồi Bài thực hành</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Đánh giá bài tập của học viên, gửi nhận xét và công nhận hoàn thành bài thực hành.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Quy trình duyệt bài:</div>
                            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                                <li>Mở bài học ở chế độ <strong>Creator Review Mode</strong>.</li>
                                <li>Kiểm tra đường link bài tập của học viên hoặc xem ảnh/log kết quả đính kèm.</li>
                                <li>Chọn <strong>Phê duyệt</strong> (Approve) để trao chứng nhận hoặc <strong>Yêu cầu chỉnh sửa</strong> kèm lời nhắn.</li>
                                <li>Khi bài nộp được duyệt, điểm đóng góp của học viên trên bảng xếp hạng sẽ được cập nhật tự động.</li>
                            </ul>
                        </div>
                        <div class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kết quả: Toàn bộ lịch sử duyệt bài được lưu vết minh bạch; học viên nhận thông báo kết quả tức thì.
                        </div>
                    </div>

                    <!-- Task C4: Managing Members & Invitations -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">C4</span>
                            <h3 class="text-base font-bold text-slate-900">Quản lý Thành viên, Đình chỉ & Kích hoạt lại</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Cấp mã lời mời có chữ ký số và duy trì kỷ luật cộng đồng thông qua bảng điều khiển thành viên.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Các thao tác quản lý:</div>
                            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                                <li>Gửi mã mời dùng một lần qua email với thời hạn hiệu lực 7 ngày.</li>
                                <li>Theo dõi danh sách thành viên đang hoạt động, ngày tham gia và điểm tích lũy trong tab <strong>Thành viên</strong>.</li>
                                <li>Tạm thời đình chỉ thành viên vi phạm quy tắc kèm theo lý do bắt buộc để phục vụ kiểm tra nhật ký.</li>
                                <li>Khôi phục quyền cho thành viên khi thích hợp; tiến độ học và điểm cũ sẽ được phục hồi nguyên vẹn.</li>
                            </ul>
                        </div>
                        <div class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kết quả: Tài khoản bị đình chỉ sẽ lập tức mất quyền truy cập vào bảng tin, lớp học và lịch sự kiện của nhóm.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Troubleshooting & Recovery -->
            <div x-show="activeTab === 'troubleshooting'" class="space-y-8" x-cloak>
                <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Các Sự cố Thường gặp & Cách Xử lý</h3>
                        <p class="text-sm text-slate-600 mt-1">Giải pháp nhanh cho các vấn đề liên quan đến quyền truy cập, thanh toán và hiển thị.</p>
                    </div>

                    <div class="space-y-4 text-xs sm:text-sm">
                        <!-- Issue 1 -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-2">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <span class="text-rose-600">●</span>
                                "Đường link lời mời báo lỗi đã hết hạn hoặc không hợp lệ"
                            </div>
                            <p class="text-slate-600 text-xs leading-relaxed">
                                Mã lời mời có thời hạn 7 ngày hoặc sẽ mất hiệu lực sau khi đã được chấp nhận một lần. Nếu bạn gặp lỗi này, hãy liên hệ Creator của cộng đồng để xin cấp link mời mới. Đồng thời hãy đảm bảo bạn đang đăng nhập đúng địa chỉ email đã nhận lời mời.
                            </p>
                        </div>

                        <!-- Issue 2 -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-2">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <span class="text-rose-600">●</span>
                                "Bị văng đăng nhập đột ngột hoặc phiên không đồng bộ khi chuyển trang"
                            </div>
                            <p class="text-slate-600 text-xs leading-relaxed">
                                Luôn truy cập bằng tên miền chuẩn <code class="bg-white px-1 py-0.5 rounded border border-slate-200 font-mono">http://127.0.0.1:8080</code>. Tuyệt đối không dùng lẫn lộn giữa <code class="bg-white px-1 py-0.5 rounded border border-slate-200 font-mono">localhost</code> và <code class="bg-white px-1 py-0.5 rounded border border-slate-200 font-mono">127.0.0.1</code> trên thanh địa chỉ, vì cookie phiên làm việc được gắn cố định theo từng host cụ thể.
                            </p>
                        </div>

                        <!-- Issue 3 -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-2">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <span class="text-rose-600">●</span>
                                "Trình phát video bài học bị màn hình đen hoặc báo lỗi không tìm thấy luồng"
                            </div>
                            <p class="text-slate-600 text-xs leading-relaxed">
                                Đảm bảo link video từ Loom hoặc YouTube sử dụng định dạng URL nhúng chuẩn. Ở môi trường phát triển cục bộ, file video lưu trên ổ cứng cần nằm trong thư mục được cấp quyền với định dạng MIME hợp lệ. Các lượt truy cập trực tiếp bằng file URL mà không qua giao diện sẽ bị hệ thống bảo mật ngăn chặn.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Quick Links -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div>
                    Nền tảng Học tập & Cộng đồng S-cool &bull; Tài liệu Hướng dẫn Công khai
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="hover:text-teal-600 font-bold transition">Trang chủ</a>
                    <a href="{{ route('communities.index') }}" class="hover:text-teal-600 font-bold transition">Cộng đồng</a>
                    <a href="{{ route('login') }}" class="hover:text-teal-600 font-bold transition">Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
