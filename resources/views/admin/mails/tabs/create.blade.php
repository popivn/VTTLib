<!-- View/Preview Overdue Book Email Tab -->
<div class="space-y-6" x-data="{ previewExpanded: true, currentTemplate: 'overdue', settingsOpen: false }" @close-settings.window="settingsOpen = false">
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

                <div class="flex items-center gap-2">
                    <!-- Settings Button -->
                    <button @click="settingsOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 transition-colors shadow-xs">
                        <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                        Cài đặt
                    </button>

                    <!-- Toggle Preview Button -->
                    <button @click="previewExpanded = !previewExpanded" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#680102] dark:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-xs">
                        <span x-text="previewExpanded ? 'Mở rộng vùng soạn' : 'Hiện xem trước'"></span>
                        <svg x-show="previewExpanded" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4M4 20l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path></svg>
                        <svg x-show="!previewExpanded" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Select Template Dropdown -->
            <div class="mb-4 bg-white dark:bg-slate-850 p-3 rounded-lg border border-slate-200/50 dark:border-slate-700/50">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Mẫu email đang hiển thị</label>
                <select id="templateSelect" x-model="currentTemplate" @change="onTemplateSwitch()" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-medium">
                    <option value="overdue" selected>Sinh Viên Quá Hạn Mượn Sách</option>
                    <option value="due_soon">Thông Báo Sắp Đến Hạn Trả Sách</option>
                </select>
            </div>

            <!-- Scrollable Controls Container -->
            <div :class="previewExpanded ? 'max-h-[500px]' : 'max-h-[720px]'" class="space-y-5 flex-1 overflow-y-auto pr-2 custom-scrollbar transition-all duration-300">

                <!-- ==================== OVERDUE TEMPLATE FIELDS ==================== -->
                <div x-show="currentTemplate === 'overdue'">
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
                </div><!-- end overdue template fields -->

                <!-- ==================== DUE SOON TEMPLATE FIELDS ==================== -->
                <div x-show="currentTemplate === 'due_soon'" x-cloak>
                <!-- Section: Student details -->
                <div class="space-y-3 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200/50 dark:border-slate-700/50">
                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">1. Thông tin sinh viên</span>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên sinh viên</label>
                            <input type="text" id="dsParamStudentName" value="Nguyễn Văn A" oninput="updateTemplatePreview()"
                                class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">MSSV / Mã thẻ</label>
                            <input type="text" id="dsParamStudentId" value="SV20261001" oninput="updateTemplatePreview()"
                                class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600 font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Email sinh viên</label>
                        <input type="email" id="dsParamStudentEmail" value="sv20261001@vttu.edu.vn" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600 font-mono">
                    </div>
                </div>

                <!-- Section: Due soon days & Books -->
                <div class="space-y-3 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200/50 dark:border-slate-700/50">
                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">2. Thông tin sắp đến hạn (Bảng động)</span>
                    
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">Số ngày còn lại</label>
                            <span id="dsDaysLeftVal" class="text-[10px] font-bold text-amber-600 bg-amber-100 px-2 py-0.5 rounded">3 ngày</span>
                        </div>
                        <input type="range" id="dsParamDaysLeft" min="1" max="30" value="{{ $dueSoonDays ?? 3 }}" oninput="onDsSliderInput(this.value)"
                            class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-amber-600">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Sách sắp đến hạn (mỗi dòng 1 cuốn, định dạng: Tên sách | Mã sách)</label>
                        <textarea id="dsParamBooksList" rows="3" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600 font-sans resize-none">Giáo trình Lập trình Web PHP | VT001
Giáo trình Cấu trúc dữ liệu và giải thuật | VT002</textarea>
                    </div>
                </div>

                <!-- Section: Editable Text Blocks -->
                <div class="space-y-4 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200/50 dark:border-slate-700/50">
                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">3. Nội dung văn bản tùy biến</span>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tên thư viện (Tiêu đề chính)</label>
                        <input type="text" id="dsParamLibraryName" value="{{ $dueSoonTemplateData['library_name'] ?? 'Thư viện Đại học Võ Trường Toản' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tiêu đề phụ email</label>
                        <input type="text" id="dsParamSubtitle" value="{{ $dueSoonTemplateData['subtitle'] ?? 'Thông báo sắp đến hạn trả sách' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1 flex justify-between">
                            <span>Lời chào mở đầu</span>
                            <span class="text-[9px] text-slate-400 font-mono font-normal">Hỗ trợ {name}, {id}</span>
                        </label>
                        <input type="text" id="dsParamGreeting" value="{{ $dueSoonTemplateData['greeting'] ?? 'Thân gửi sinh viên {name} (MSSV: {id}),' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1 flex justify-between">
                            <span>Đoạn văn giới thiệu</span>
                            <span class="text-[9px] text-slate-400 font-mono font-normal">Hỗ trợ {days}</span>
                        </label>
                        <textarea id="dsParamIntro" rows="4" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600 font-sans resize-none leading-relaxed">{{ $dueSoonTemplateData['intro'] ?? 'Hệ thống ghi nhận bạn đang mượn tài liệu tại Thư viện sắp đến thời hạn trả quy định. Còn {days} ngày nữa là đến hạn trả sách. Đề nghị bạn chú ý hoàn trả đúng hạn hoặc gia hạn tài liệu để tránh phí phạt quá hạn.' }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Tiêu đề Lưu ý</label>
                        <input type="text" id="dsParamNoticeTitle" value="{{ $dueSoonTemplateData['notice_title'] ?? 'ℹ️ THÔNG TIN GIA HẠN:' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600 font-bold text-amber-700 dark:text-amber-400">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Nội dung Lưu ý (Từng dòng tương ứng với 1 ý)</label>
                        <textarea id="dsParamNoticeContent" rows="5" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600 font-sans resize-none leading-relaxed">{{ $dueSoonTemplateData['notice_content'] ?? "Bạn có thể gia hạn tài liệu trực tuyến qua hệ thống thư viện hoặc đến trực tiếp thư viện để gia hạn.\nMức phạt quá hạn áp dụng theo quy định hiện hành: 5.000 đ/ngày/cuốn sách.\nNếu bạn đã trả sách hoặc gia hạn tài liệu thành công trước thời gian nhận được email này, vui lòng bỏ qua thư thông báo này." }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Chữ ký cuối thư</label>
                        <input type="text" id="dsParamSignature" value="{{ $dueSoonTemplateData['signature'] ?? 'Ban Quản lý Thư viện Đại học Võ Trường Toản' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600 font-medium">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Địa chỉ liên hệ</label>
                        <input type="text" id="dsParamAddress" value="{{ $dueSoonTemplateData['address'] ?? 'Khu đô thị ĐH Võ Trường Toản, QL 1A, Châu Thành A, Hậu Giang' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600">
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Chú thích chân trang (Footer Note)</label>
                        <input type="text" id="dsParamFooterNote" value="{{ $dueSoonTemplateData['footer_note'] ?? 'Email tự động, vui lòng không phản hồi thư này.' }}" oninput="updateTemplatePreview()"
                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600">
                    </div>
                </div>
                </div><!-- end due_soon template fields -->
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

<!-- Settings Modal -->
<div x-show="settingsOpen" x-cloak @keydown.escape.window="settingsOpen = false" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div x-show="settingsOpen" @click="settingsOpen = false" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div x-show="settingsOpen" x-transition class="relative bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i data-lucide="settings" class="w-5 h-5 text-amber-600"></i>
                Cài đặt thông báo mail
            </h3>
            <button @click="settingsOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Số ngày báo hạn trước khi trả sách</label>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-2">Hệ thống sẽ gửi email thông báo cho bạn đọc khi sách sắp đến hạn trả trong khoảng số ngày này.</p>
                <div class="flex items-center gap-3">
                    <input type="number" id="settingsDueSoonDays" min="1" max="30" value="{{ $dueSoonDays ?? 3 }}"
                        class="w-20 px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-600 text-center font-bold">
                    <span class="text-xs text-slate-500 dark:text-slate-400">ngày</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <button @click="settingsOpen = false" class="px-4 py-2 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                Hủy
            </button>
            <button onclick="saveSettings()" class="px-4 py-2 rounded-lg text-xs font-semibold bg-amber-600 hover:bg-amber-700 text-white transition-colors shadow-sm">
                <i data-lucide="save" class="w-3.5 h-3.5 inline mr-1"></i>
                Lưu cài đặt
            </button>
        </div>
    </div>
</div>
</div>

<style>
    [x-cloak] { display: none !important; }
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
        const tplKey = document.getElementById('templateSelect').value;
        let config;

        if (tplKey === 'due_soon') {
            config = {
                library_name: document.getElementById('dsParamLibraryName').value,
                subtitle: document.getElementById('dsParamSubtitle').value,
                greeting: document.getElementById('dsParamGreeting').value,
                intro: document.getElementById('dsParamIntro').value,
                notice_title: document.getElementById('dsParamNoticeTitle').value,
                notice_content: document.getElementById('dsParamNoticeContent').value,
                signature: document.getElementById('dsParamSignature').value,
                address: document.getElementById('dsParamAddress').value,
                footer_note: document.getElementById('dsParamFooterNote').value,
            };
        } else {
            config = {
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
        }

        fetch('{{ route('admin.mails.save-template') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                template_key: tplKey,
                config: config
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Thành công!', text: data.message, timer: 2500, timerProgressBar: true, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Lỗi!', text: 'Có lỗi xảy ra khi lưu cấu hình.', confirmButtonColor: '#ef4444' });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Lỗi hệ thống!', text: 'Không thể kết nối đến server.', confirmButtonColor: '#ef4444' });
        });
    }

    // Save mail settings (due soon days) via AJAX
    function saveSettings() {
        const dueSoonDays = document.getElementById('settingsDueSoonDays').value;

        fetch('{{ route('admin.mails.save-settings') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                due_soon_days: parseInt(dueSoonDays)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Thành công!', text: data.message, timer: 2500, timerProgressBar: true, showConfirmButton: false });
                window.dispatchEvent(new CustomEvent('close-settings'));
            } else {
                Swal.fire({ icon: 'error', title: 'Lỗi!', text: 'Có lỗi xảy ra khi lưu cài đặt.', confirmButtonColor: '#ef4444' });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Lỗi hệ thống!', text: 'Không thể kết nối đến server.', confirmButtonColor: '#ef4444' });
        });
    }

    // Due soon days range input handling
    function onDsSliderInput(val) {
        document.getElementById('dsDaysLeftVal').textContent = val + ' ngày';
        updateTemplatePreview();
    }

    // Switch template and update preview
    function onTemplateSwitch() {
        updateTemplatePreview();
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

    // Build the Due Soon HTML template dynamically with editable inputs
    function buildDueSoonTemplateHtml() {
        const studentName = document.getElementById('dsParamStudentName')?.value || 'Sinh viên';
        const studentId = document.getElementById('dsParamStudentId')?.value || 'Chưa rõ';
        const daysLeft = parseInt(document.getElementById('dsParamDaysLeft')?.value) || 3;
        const booksListText = document.getElementById('dsParamBooksList')?.value || '';
        
        const libraryName = document.getElementById('dsParamLibraryName')?.value || 'Thư viện trường';
        const subtitle = document.getElementById('dsParamSubtitle')?.value || 'Thông báo';
        
        let greeting = document.getElementById('dsParamGreeting')?.value || '';
        greeting = greeting.replace('{name}', studentName).replace('{id}', studentId);

        let intro = document.getElementById('dsParamIntro')?.value || '';
        intro = intro.replace('{days}', daysLeft);

        const noticeTitle = document.getElementById('dsParamNoticeTitle')?.value || 'LƯU Ý:';
        const noticeText = document.getElementById('dsParamNoticeContent')?.value || '';
        const signature = document.getElementById('dsParamSignature')?.value || '';
        const address = document.getElementById('dsParamAddress')?.value || '';
        const footerNote = document.getElementById('dsParamFooterNote')?.value || '';

        const books = booksListText.split('\n').map(b => b.trim()).filter(b => b.length > 0);
        let booksRowsHtml = '';
        
        if (books.length > 0) {
            books.forEach((book, index) => {
                const parts = book.split('|').map(s => s.trim());
                const bookTitle = parts[0] || '';
                const bookCode = parts[1] || '--';
                booksRowsHtml += `
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 12px 10px; font-size: 13px; color: #1e293b; font-weight: 500;">${index + 1}</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #1e293b;">${bookTitle}</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #475569; font-weight: 500; text-align: center;">${bookCode}</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #f59e0b; font-weight: 600; text-align: center;">--/--/----</td>
                    </tr>
                `;
            });
        } else {
            booksRowsHtml = `
                <tr>
                    <td colspan="4" style="padding: 20px; text-align: center; color: #64748b; font-style: italic;">Chưa khai báo danh sách sách</td>
                </tr>
            `;
        }

        const noticeItems = noticeText.split('\n').map(item => item.trim()).filter(item => item.length > 0);
        let noticeHtml = '';
        noticeItems.forEach(item => {
            const cleanItem = item.replace(/^[\s\-\*\•\d\.\)]+/, '').trim();
            noticeHtml += `<li style="margin-bottom: 6px;">${cleanItem}</li>`;
        });

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
                    <!-- Amber Header (Due Soon theme) -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #b45309 0%, #92400e 100%); padding: 35px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; text-transform: uppercase;">${libraryName}</h1>
                            <p style="color: #fde68a; margin: 5px 0 0 0; font-size: 13px; font-weight: 500;">${subtitle}</p>
                        </td>
                    </tr>
                    
                    <!-- Content Body -->
                    <tr>
                        <td style="padding: 40px 40px 30px 40px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 24px; font-weight: 500;">${greeting}</p>
                            
                            <p style="margin: 0 0 24px 0; font-size: 14px; color: #475569; line-height: 22px;">${intro}</p>

                            <!-- Due Soon Table -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                <thead>
                                    <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                        <th width="8%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: left;">STT</th>
                                        <th width="45%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: left;">Tên sách</th>
                                        <th width="22%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: center;">Mã sách</th>
                                        <th width="25%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: center;">Ngày trả</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${booksRowsHtml}
                                </tbody>
                            </table>

                            <!-- Info Notice (Amber theme) -->
                            ${noticeHtml ? `
                            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                                <h4 style="margin: 0 0 6px 0; color: #92400e; font-size: 13px; font-weight: 750;">${noticeTitle}</h4>
                                <ul style="margin: 0; padding-left: 18px; color: #92400e; font-size: 12px; line-height: 1.6;">
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
        const tplKey = document.getElementById('templateSelect').value;
        let html;
        if (tplKey === 'due_soon') {
            html = buildDueSoonTemplateHtml();
        } else {
            html = buildOverdueTemplateHtml();
        }
        updateIframePreview(html);
    }

    // Copy generated HTML code to clipboard
    function copyHtmlTemplate() {
        const tplKey = document.getElementById('templateSelect').value;
        const html = tplKey === 'due_soon' ? buildDueSoonTemplateHtml() : buildOverdueTemplateHtml();
        navigator.clipboard.writeText(html).then(() => {
            Swal.fire({ icon: 'success', title: 'Đã sao chép!', text: 'Mã nguồn HTML đã được sao chép vào clipboard.', timer: 2000, timerProgressBar: true, showConfirmButton: false });
        });
    }

    // Print template
    function printHtmlTemplate() {
        const tplKey = document.getElementById('templateSelect').value;
        const html = tplKey === 'due_soon' ? buildDueSoonTemplateHtml() : buildOverdueTemplateHtml();
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
