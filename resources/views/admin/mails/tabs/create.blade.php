<!-- View/Preview Overdue Book Email Tab -->
<div class="space-y-6" x-data="{ previewExpanded: true }">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Interactive Template Selector & Configurator -->
        <div :class="previewExpanded ? 'lg:col-span-5' : 'lg:col-span-12'" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-800 rounded-xl p-5 shadow-sm flex flex-col transition-all duration-300">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2 mb-1">
                        <i data-lucide="layout-template" class="w-4 h-4 text-[#680102]"></i>
                        Cấu Hình Chi Tiết Thư
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Chỉnh sửa tất cả thông tin, lời nhắc dưới đây.</p>
                </div>

                <!-- Toggle Preview Button -->
                <button @click="previewExpanded = !previewExpanded" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#680102] dark:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-xs">
                    <span x-text="previewExpanded ? 'Mở rộng vùng soạn' : 'Hiện xem trước'"></span>
                    <!-- Inline SVGs to avoid Lucide initialization lag -->
                    <svg x-show="previewExpanded" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4M4 20l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path></svg>
                    <svg x-show="!previewExpanded" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </button>
            </div>

            <!-- Select Template Dropdown -->
            <div class="mb-4 bg-white dark:bg-slate-850 p-3 rounded-lg border border-slate-200/50 dark:border-slate-700/50">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Mẫu email đang hiển thị</label>
                <select id="templateSelect" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-medium">
                    <option value="overdue" selected>Sinh Viên Quá Hạn Mượn Sách</option>
                </select>
            </div>

            <!-- Scrollable Controls Container -->
            <div :class="previewExpanded ? 'max-h-[500px]' : 'max-h-[720px]'" class="space-y-5 flex-1 overflow-y-auto pr-2 custom-scrollbar transition-all duration-300">
                
                <!-- Section: Student details -->
                <div class="space-y-3 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200/50 dark:border-slate-700/50">
                    <span class="text-[11px] font-bold text-[#680102] dark:text-red-400 uppercase tracking-wider">1. Thông tin sinh viên</span>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên sinh viên</label>
                            <input type="text" id="paramStudentName" value="Nguyễn Văn A" oninput="updateTemplatePreview()"
                                class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102]">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">MSSV / Mã thẻ</label>
                            <input type="text" id="paramStudentId" value="SV20261001" oninput="updateTemplatePreview()"
                                class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Email sinh viên</label>
                        <input type="email" id="paramStudentEmail" value="sv20261001@vttu.edu.vn" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-mono">
                    </div>
                </div>

                <!-- Section: Overdue days & Books -->
                <div class="space-y-3 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200/50 dark:border-slate-700/50">
                    <span class="text-[11px] font-bold text-[#680102] dark:text-red-400 uppercase tracking-wider">2. Thông tin quá hạn (Bảng động)</span>
                    
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">Số ngày quá hạn</label>
                            <span id="overdueDaysVal" class="text-[10px] font-bold text-red-500 bg-red-500/10 px-2 py-0.5 rounded">5 ngày</span>
                        </div>
                        <input type="range" id="paramOverdueDays" min="1" max="30" value="5" oninput="onOverdueSliderInput(this.value)"
                            class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-[#680102]">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Sách quá hạn (mỗi dòng 1 cuốn)</label>
                        <textarea id="paramBooksList" rows="3" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-sans resize-none">Giáo trình Lập trình Web PHP (Vòng 1)
Giáo trình Cấu trúc dữ liệu và giải thuật</textarea>
                    </div>
                </div>

                <!-- Section: Editable Text Blocks -->
                <div class="space-y-4 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200/50 dark:border-slate-700/50">
                    <span class="text-[11px] font-bold text-[#680102] dark:text-red-400 uppercase tracking-wider">3. Nội dung văn bản tùy biến</span>

                    <!-- Library Name & Subtitle -->
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên thư viện (Tiêu đề chính)</label>
                        <input type="text" id="paramLibraryName" value="{{ $templateData['library_name'] ?? 'Thư viện Đại học Võ Trường Toản' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102]">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tiêu đề phụ email</label>
                        <input type="text" id="paramSubtitle" value="{{ $templateData['subtitle'] ?? 'Thông báo hoàn trả tài liệu quá hạn' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102]">
                    </div>

                    <!-- Greeting Template -->
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1 flex justify-between">
                            <span>Lời chào mở đầu</span>
                            <span class="text-[9px] text-slate-400 font-mono font-normal">Hỗ trợ {name}, {id}</span>
                        </label>
                        <input type="text" id="paramGreeting" value="{{ $templateData['greeting'] ?? 'Thân gửi sinh viên {name} (MSSV: {id}),' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102]">
                    </div>

                    <!-- Intro Paragraph Template -->
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1 flex justify-between">
                            <span>Đoạn văn giới thiệu</span>
                            <span class="text-[9px] text-slate-400 font-mono font-normal">Hỗ trợ {days}</span>
                        </label>
                        <textarea id="paramIntro" rows="4" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-sans resize-none leading-relaxed">{{ $templateData['intro'] ?? 'Hệ thống ghi nhận bạn đang mượn tài liệu tại Thư viện đã quá thời hạn trả quy định là {days} ngày. Để bảo đảm quyền lợi của bản thân cũng như phục vụ tài liệu cho các bạn khác, đề nghị bạn khẩn trương đến thư viện để thực hiện thủ tục trả sách và hoàn phí quá hạn (nếu có).' }}</textarea>
                    </div>

                    <!-- Notice Title & Warnings -->
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tiêu đề Lưu ý</label>
                        <input type="text" id="paramNoticeTitle" value="{{ $templateData['notice_title'] ?? '⚠️ LƯU Ý QUAN TRỌNG:' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-bold text-red-600 dark:text-red-400">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Nội dung Lưu ý (Từng dòng tương ứng với 1 ý)</label>
                        <textarea id="paramNoticeContent" rows="5" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-sans resize-none leading-relaxed">{{ $templateData['notice_content'] ?? "Mức phạt quá hạn áp dụng theo quy định hiện hành: 5.000 đ/ngày/cuốn sách.\nTrường hợp không hoàn trả hoặc để quá hạn kéo dài, tài khoản thư viện của bạn sẽ bị tạm khóa và hệ thống có thể tạm đình chỉ quyền mượn sách.\nNếu bạn đã trả sách hoặc gia hạn tài liệu thành công trước thời gian nhận được email này, vui lòng bỏ qua thư thông báo này hoặc liên hệ phản hồi trực tiếp qua mail." }}</textarea>
                    </div>

                    <!-- Signature, Address & Footer -->
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Chữ ký cuối thư</label>
                        <input type="text" id="paramSignature" value="{{ $templateData['signature'] ?? 'Ban Quản lý Thư viện Đại học Võ Trường Toản' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-medium">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Địa chỉ liên hệ</label>
                        <input type="text" id="paramAddress" value="{{ $templateData['address'] ?? 'Khu đô thị ĐH Võ Trường Toản, QL 1A, Châu Thành A, Hậu Giang' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102]">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Chú thích chân trang (Footer Note)</label>
                        <input type="text" id="paramFooterNote" value="{{ $templateData['footer_note'] ?? 'Email tự động, vui lòng không phản hồi thư này.' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102]">
                    </div>
                </div>
            </div>

            <!-- Save Template Config Area -->
            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800 flex justify-end">
                <button onclick="saveMailTemplate()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold bg-[#680102] hover:bg-[#500102] text-white transition-all shadow-sm active:scale-95">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    Lưu cấu hình Template
                </button>
            </div>
        </div>

        <!-- Real-time HTML Preview & Quick Actions -->
        <div x-show="previewExpanded" class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm flex flex-col transition-all duration-300">
            <!-- Preview Header with Actions -->
            <div class="bg-slate-50 dark:bg-slate-850 px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200 ml-2">Xem Trước Thư Gửi Đi (Visual HTML Preview)</span>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button onclick="copyHtmlTemplate()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors shadow-xs">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        Sao chép mã HTML
                    </button>
                    <button onclick="printHtmlTemplate()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 dark:bg-red-950/30 border border-red-100 dark:border-red-900/40 text-[#680102] dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/60 transition-colors">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        In thư nhắc nhở
                    </button>
                </div>
            </div>
            
            <!-- Iframe Container -->
            <div class="p-6 bg-slate-100 dark:bg-slate-950 flex-1 flex justify-center items-center">
                <!-- Iframe to isolate styles and display responsive email layout -->
                <iframe id="htmlPreviewIframe" class="w-full max-w-2xl bg-white rounded-lg shadow-md border border-slate-200/60 dark:border-slate-800 min-h-[580px]"></iframe>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom thin scrollbar for left parameters list */
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
    }
</style>

<script>
    // Overdue days range input handling
    function onOverdueSliderInput(val) {
        document.getElementById('overdueDaysVal').textContent = val + ' ngày';
        updateTemplatePreview();
    }

    // Save the template configuration parameters via AJAX
    function saveMailTemplate() {
        const config = {
            library_name: document.getElementById('paramLibraryName').value,
            subtitle: document.getElementById('paramSubtitle').value,
            greeting: document.getElementById('paramGreeting').value,
            intro: document.getElementById('paramIntro').value,
            notice_title: document.getElementById('paramNoticeTitle').value,
            notice_content: document.getElementById('paramNoticeContent').value,
            signature: document.getElementById('paramSignature').value,
            address: document.getElementById('paramAddress').value,
            footer_note: document.getElementById('paramFooterNote').value,
        };

        fetch('{{ route('admin.mails.save-template') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                template_key: 'overdue',
                config: config
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert('Có lỗi xảy ra khi lưu cấu hình.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi hệ thống khi kết nối lưu dữ liệu.');
        });
    }

    // Build the gorgeous Overdue HTML template dynamically with editable inputs
    function buildOverdueTemplateHtml() {
        const studentName = document.getElementById('paramStudentName').value || 'Sinh viên';
        const studentId = document.getElementById('paramStudentId').value || 'Chưa rõ';
        const overdueDays = parseInt(document.getElementById('paramOverdueDays').value) || 1;
        const booksListText = document.getElementById('paramBooksList').value || '';
        
        // Editable text blocks
        const libraryName = document.getElementById('paramLibraryName').value || 'Thư viện trường';
        const subtitle = document.getElementById('paramSubtitle').value || 'Thông báo';
        
        // Process greeting placeholder replacement
        let greeting = document.getElementById('paramGreeting').value || '';
        greeting = greeting.replace('{name}', studentName).replace('{id}', studentId);

        // Process intro placeholder replacement
        let intro = document.getElementById('paramIntro').value || '';
        intro = intro.replace('{days}', overdueDays);

        const noticeTitle = document.getElementById('paramNoticeTitle').value || 'LƯU Ý:';
        const noticeText = document.getElementById('paramNoticeContent').value || '';
        const signature = document.getElementById('paramSignature').value || '';
        const address = document.getElementById('paramAddress').value || '';
        const footerNote = document.getElementById('paramFooterNote').value || '';

        // Parse books list
        const books = booksListText.split('\n').map(b => b.trim()).filter(b => b.length > 0);
        let booksRowsHtml = '';
        
        if (books.length > 0) {
            books.forEach((book, index) => {
                const fine = (overdueDays * 5000).toLocaleString('vi-VN') + ' đ';
                booksRowsHtml += `
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 12px 10px; font-size: 13px; color: #1e293b; font-weight: 500;">${index + 1}</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #1e293b;">${book}</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #ef4444; font-weight: 600; text-align: center;">${overdueDays} ngày</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #475569; text-align: right;">5.000 đ/ngày</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #e11d48; font-weight: bold; text-align: right;">${fine}</td>
                    </tr>
                `;
            });
        } else {
            booksRowsHtml = `
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: #64748b; font-style: italic;">Chưa khai báo danh sách sách quá hạn</td>
                </tr>
            `;
        }

        // Process notice points
        const noticeItems = noticeText.split('\n').map(item => item.trim()).filter(item => item.length > 0);
        let noticeHtml = '';
        noticeItems.forEach(item => {
            const cleanItem = item.replace(/^[\s\-\*\•\d\.\)]+/, '').trim();
            noticeHtml += `<li style="margin-bottom: 6px;">${cleanItem}</li>`;
        });

        // Return inline-styled HTML document for VTTU Crimson Red theme rendering
        return `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${subtitle}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #e2e8f0;">
                    <!-- Crimson Red Header (VTTU Red Color theme #680102) -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #680102 0%, #450001 100%); padding: 35px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; text-transform: uppercase;">${libraryName}</h1>
                            <p style="color: #fca5a5; margin: 5px 0 0 0; font-size: 13px; font-weight: 500;">${subtitle}</p>
                        </td>
                    </tr>
                    
                    <!-- Content Body -->
                    <tr>
                        <td style="padding: 40px 40px 30px 40px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 24px; font-weight: 500;">${greeting}</p>
                            
                            <p style="margin: 0 0 24px 0; font-size: 14px; color: #475569; line-height: 22px;">${intro}</p>

                            <!-- Overdue Table -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                <thead>
                                    <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                        <th width="8%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: left;">STT</th>
                                        <th width="42%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: left;">Tên sách</th>
                                        <th width="15%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: center;">Quá hạn</th>
                                        <th width="18%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: right;">Đơn giá</th>
                                        <th width="17%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: right;">Tạm tính</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${booksRowsHtml}
                                </tbody>
                            </table>

                            <!-- Warnings Notice (Crimson border/alert tone #680102) -->
                            ${noticeHtml ? `
                            <div style="background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                                <h4 style="margin: 0 0 6px 0; color: #680102; font-size: 13px; font-weight: 750;">${noticeTitle}</h4>
                                <ul style="margin: 0; padding-left: 18px; color: #680102; font-size: 12px; line-height: 1.6;">
                                    ${noticeHtml}
                                </ul>
                            </div>
                            ` : ''}
                        </td>
                    </tr>
                    
                    <!-- Footer Info -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 25px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 6px 0; font-size: 13px; font-weight: bold; color: #334155;">${signature}</p>
                            ${address ? `<p style="margin: 0 0 6px 0; font-size: 11px; color: #64748b;">Địa chỉ: ${address}</p>` : ''}
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">${footerNote}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>`;
    }

    // Update the live preview inside the iframe
    function updateIframePreview(htmlContent) {
        const iframe = document.getElementById('htmlPreviewIframe');
        if (iframe) {
            iframe.srcdoc = htmlContent;
        }
    }

    // Generate and show HTML template in iframe
    function updateTemplatePreview() {
        const overdueHtml = buildOverdueTemplateHtml();
        updateIframePreview(overdueHtml);
    }

    // Copy generated HTML code to clipboard
    function copyHtmlTemplate() {
        const html = buildOverdueTemplateHtml();
        navigator.clipboard.writeText(html).then(() => {
            alert('Đã sao chép toàn bộ mã nguồn HTML của email!');
        });
    }

    // Print template
    function printHtmlTemplate() {
        const html = buildOverdueTemplateHtml();
        const printWindow = window.open('', '', 'width=850,height=700');
        printWindow.document.write(html);
        printWindow.document.close();
        setTimeout(() => {
            printWindow.print();
        }, 300);
    }

    // Initialize template preview on load
    window.addEventListener('DOMContentLoaded', () => {
        updateTemplatePreview();
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
