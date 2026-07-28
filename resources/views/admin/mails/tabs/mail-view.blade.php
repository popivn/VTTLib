<!-- Mail View Tab - Preview Email Before Sending -->
<div class="space-y-4 max-w-4xl">
    <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-sm text-blue-800 dark:text-blue-300">
        <div class="flex gap-2">
            <i data-lucide="info" class="w-5 h-5 flex-shrink-0"></i>
            <div>
                <p class="font-semibold mb-1">Xem Trước Email</p>
                <p>Tạo một bản xem trước của email trước khi gửi. Chọn mẫu hoặc điền nội dung để xem cách nó sẽ hiển thị khi nhận được.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Input Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg p-4 sticky top-6">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">Thông Tin Email</h3>

                <form id="mailViewForm" class="space-y-4">
                    <!-- Template Selection -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Chọn Mẫu</label>
                        <select id="templateSelect" onchange="loadTemplate()" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                            <option value="">-- Mẫu Trống --</option>
                            <option value="welcome">Chào Mừng Người Dùng</option>
                            <option value="announcement">Thông Báo</option>
                            <option value="reminder">Nhắc Nhở</option>
                            <option value="report">Báo Cáo</option>
                            <option value="survey">Khảo Sát</option>
                        </select>
                    </div>

                    <!-- Recipient -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Người Nhận</label>
                        <input type="email" id="mailViewTo" placeholder="user@example.com" value="demo@example.com"
                            class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500">
                    </div>

                    <!-- Subject -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Chủ Đề</label>
                        <input type="text" id="mailViewSubject" placeholder="Chủ đề email" value="Xin chào"
                            class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500">
                    </div>

                    <!-- From Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Từ</label>
                        <input type="text" id="mailViewFrom" placeholder="admin@library.edu.vn" value="admin@library.edu.vn"
                            class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500">
                    </div>

                    <!-- Language -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Ngôn Ngữ</label>
                        <select id="mailViewLang" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                            <option value="vi">Tiếng Việt</option>
                            <option value="en">English</option>
                        </select>
                    </div>

                    <!-- Body Content -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Nội Dung</label>
                        <textarea id="mailViewBody" placeholder="Nội dung email..." rows="6"
                            class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 resize-none font-mono">Xin chào {{name}},

Đây là một email mẫu từ hệ thống quản lý thư viện.

Cảm ơn đã sử dụng dịch vụ của chúng tôi!</textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button type="button" onclick="updatePreview()" class="flex-1 px-3 py-2 text-xs font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            Cập Nhật
                        </button>
                        <button type="button" onclick="resetMailView()" class="flex-1 px-3 py-2 text-xs font-semibold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                            Xóa
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg overflow-hidden shadow-sm">
                <!-- Email Header -->
                <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 border-b border-slate-100 dark:border-slate-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold">
                                <span id="previewInitial">A</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    <span id="previewFrom">admin@library.edu.vn</span>
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Thư viện Đại học</p>
                            </div>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400" id="previewTime">{{ now()->format('H:i:s') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-2">Tới: <span id="previewRecipient">demo@example.com</span></p>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100" id="previewSubjectDisplay">Xin chào</h2>
                </div>

                <!-- Email Body -->
                <div class="p-6 min-h-96">
                    <div id="previewBodyDisplay" class="prose dark:prose-invert max-w-none text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap font-sans leading-relaxed">
                        Xin chào {{name}},

Đây là một email mẫu từ hệ thống quản lý thư viện.

Cảm ơn đã sử dụng dịch vụ của chúng tôi!
                    </div>
                </div>

                <!-- Email Footer -->
                <div class="bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 p-4 text-center text-xs text-slate-500 dark:text-slate-400">
                    <p>© 2026 Thư viện Đại học. Tất cả quyền được bảo lưu.</p>
                    <p class="mt-1">Đây là email tự động, vui lòng không reply.</p>
                </div>
            </div>

            <!-- Additional Features -->
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg p-4">
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Biến Có Sẵn</p>
                    <div class="space-y-1 text-xs font-mono text-slate-600 dark:text-slate-400">
                        <code class="bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded block">{{name}} - Tên người nhận</code>
                        <code class="bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded block">{{email}} - Email người nhận</code>
                        <code class="bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded block">{{date}} - Ngày hôm nay</code>
                        <code class="bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded block">{{library}} - Tên thư viện</code>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg p-4">
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Hành Động</p>
                    <div class="flex flex-col gap-2">
                        <button onclick="printPreview()" class="px-3 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                            <i data-lucide="printer" class="w-3 h-3 inline mr-1"></i> In
                        </button>
                        <button onclick="copyEmailContent()" class="px-3 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                            <i data-lucide="copy" class="w-3 h-3 inline mr-1"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updatePreview() {
        const from = document.getElementById('mailViewFrom').value;
        const to = document.getElementById('mailViewTo').value;
        const subject = document.getElementById('mailViewSubject').value;
        const body = document.getElementById('mailViewBody').value;

        document.getElementById('previewFrom').textContent = from;
        document.getElementById('previewRecipient').textContent = to;
        document.getElementById('previewSubjectDisplay').textContent = subject;
        document.getElementById('previewBodyDisplay').textContent = body;
        
        // Update initial
        const firstChar = from.charAt(0).toUpperCase();
        document.getElementById('previewInitial').textContent = firstChar;
        
        // Update time
        const now = new Date();
        document.getElementById('previewTime').textContent = now.toLocaleTimeString('vi-VN');
    }

    function resetMailView() {
        document.getElementById('mailViewTo').value = 'demo@example.com';
        document.getElementById('mailViewSubject').value = 'Xin chào';
        document.getElementById('mailViewBody').value = 'Xin chào {{name}},\n\nĐây là một email mẫu từ hệ thống quản lý thư viện.\n\nCảm ơn đã sử dụng dịch vụ của chúng tôi!';
        document.getElementById('templateSelect').value = '';
        updatePreview();
    }

    function loadTemplate() {
        const template = document.getElementById('templateSelect').value;
        const templates = {
            welcome: {
                subject: 'Chào mừng bạn đến với Thư viện',
                body: 'Xin chào {{name}},\n\nChào mừng bạn đã tham gia hệ thống thư viện của chúng tôi. Bạn có thể bắt đầu khám phá tài liệu ngay hôm nay.\n\nNếu có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.\n\nTrân trọng,\nThư viện'
            },
            announcement: {
                subject: 'Thông báo quan trọng từ Thư viện',
                body: 'Kính gửi {{name}},\n\nChúng tôi xin thông báo một số thay đổi quan trọng trong hệ thống thư viện.\n\nVui lòng xem thêm tại: {{library}}\n\nCảm ơn.'
            },
            reminder: {
                subject: 'Nhắc nhở: Sách sắp hết hạn mượn',
                body: 'Xin chào {{name}},\n\nNhắc nhở rằng bạn đang mượn {{count}} cuốn sách sắp hết hạn.\n\nNgày trả: {{date}}\n\nVui lòng trả hoặc gia hạn trước ngày hết hạn.\n\nTrân trọng,\nThư viện'
            },
            report: {
                subject: 'Báo cáo hàng tháng',
                body: 'Xin chào {{name}},\n\nDây là báo cáo hoạt động hàng tháng của thư viện.\n\nCác chỉ số chi tiết được gửi kèm theo email này.\n\nCảm ơn.'
            },
            survey: {
                subject: 'Khảo sát mức độ hài lòng',
                body: 'Xin chào {{name}},\n\nChúng tôi rất muốn biết ý kiến của bạn về dịch vụ thư viện.\n\nVui lòng bấm vào liên kết dưới đây để tham gia khảo sát:\n{{survey_link}}\n\nCảm ơn bạn!'
            }
        };

        if (template && templates[template]) {
            document.getElementById('mailViewSubject').value = templates[template].subject;
            document.getElementById('mailViewBody').value = templates[template].body;
            updatePreview();
        }
    }

    function printPreview() {
        const from = document.getElementById('previewFrom').textContent;
        const to = document.getElementById('previewRecipient').textContent;
        const subject = document.getElementById('previewSubjectDisplay').textContent;
        const body = document.getElementById('previewBodyDisplay').textContent;

        const printContent = `
            <div style="font-family: Arial, sans-serif; padding: 20px;">
                <p><strong>Từ:</strong> ${from}</p>
                <p><strong>Tới:</strong> ${to}</p>
                <p><strong>Chủ đề:</strong> ${subject}</p>
                <hr>
                <div style="white-space: pre-wrap; color: #333;">
                    ${body}
                </div>
            </div>
        `;

        const printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }

    function copyEmailContent() {
        const content = document.getElementById('previewBodyDisplay').textContent;
        navigator.clipboard.writeText(content).then(() => {
            alert('Đã sao chép nội dung email!');
        });
    }

    // Auto-update on input change
    document.getElementById('mailViewFrom').addEventListener('change', updatePreview);
    document.getElementById('mailViewTo').addEventListener('change', updatePreview);
    document.getElementById('mailViewSubject').addEventListener('change', updatePreview);
    document.getElementById('mailViewBody').addEventListener('change', updatePreview);

    // Initial preview
    updatePreview();
</script>
