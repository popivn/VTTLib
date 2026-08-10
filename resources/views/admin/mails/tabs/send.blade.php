<!-- Gửi Mail Tab Content -->
<div class="space-y-6" x-data="{ mailType: 'overdue' }">
    <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-800 rounded-xl p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i data-lucide="mail-search" class="w-4 h-4 text-[#680102]"></i>
                    <span x-text="mailType === 'overdue' ? 'Danh Sách Sinh Viên Quá Hạn Mượn Sách' : 'Danh Sách Sinh Viên Sắp Đến Hạn Trả Sách'"></span>
                </h3>
                <p class="text-xs text-slate-400 dark:text-slate-500" x-text="mailType === 'overdue' ? 'Hệ thống tự động phát hiện các độc giả có giao dịch mượn tài liệu quá hạn chưa trả.' : 'Hệ thống tự động phát hiện các độc giả có tài liệu sắp đến hạn trả trong ' + {{ $dueSoonDays ?? 3 }} + ' ngày tới.'"></p>
            </div>

            <!-- Mail Type Selector & Channel Selector -->
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <div class="w-full sm:w-56">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Loại email nhắc nhở</label>
                    <select id="sendMailTypeSelect" x-model="mailType" @change="switchMailType()" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#680102] font-semibold">
                        <option value="overdue" selected>Sinh Viên Quá Hạn Mượn Sách</option>
                        <option value="due_soon">Sắp Đến Hạn Trả Sách</option>
                    </select>
                </div>
                <div class="w-full sm:w-60">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Kênh gửi email</label>
                    <div class="flex bg-slate-200 dark:bg-slate-800 p-0.5 rounded gap-0.5 border border-slate-300 dark:border-slate-700">
                        <button type="button" id="btnChannelSystem" onclick="setSenderChannel('system')" 
                            class="flex-1 py-1 rounded text-[11px] font-bold text-center transition-all bg-[#680102] text-white shadow-sm">
                            Mail Hệ Thống
                        </button>
                        <button type="button" id="btnChannelLibrary" onclick="setSenderChannel('library')" 
                            class="flex-1 py-1 rounded text-[11px] font-bold text-center transition-all text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                            Mail Thư Viện
                        </button>
                    </div>
                    <input type="hidden" id="senderChannelInput" value="system">
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== OVERDUE LIST ==================== -->
    <div x-show="mailType === 'overdue'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs">
        <div class="px-5 py-4 bg-slate-50 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Tác vụ hàng loạt:</span>
                <button onclick="sendSelectedMails()" id="btnBulkSend" disabled
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[#680102] hover:bg-[#500102] text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    Gửi loạt thư nhắc nhở đã chọn
                </button>
            </div>
            
            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">
                Tìm thấy <span class="text-red-500 font-extrabold">{{ $overdueStudents->count() }}</span> sinh viên quá hạn
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-850/50 border-b border-slate-150 dark:border-slate-800 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                        <th width="4%" class="p-4 text-center">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllPatrons(this)" class="rounded text-[#680102] focus:ring-[#680102] w-3.5 h-3.5">
                        </th>
                        <th class="p-4">Họ và tên sinh viên</th>
                        <th class="p-4">Mã số sinh viên (MSSV)</th>
                        <th class="p-4">Hòm thư (Email)</th>
                        <th class="p-4 text-center">Số cuốn quá hạn</th>
                        <th class="p-4">Ngày hết hạn xa nhất</th>
                        <th width="12%" class="p-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($overdueStudents as $student)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 text-xs text-slate-700 dark:text-slate-300">
                            <td class="p-4 text-center">
                                <input type="checkbox" name="selected_patrons" value="{{ $student->patron_detail_id }}" onchange="updateBulkButtonState()" class="patron-checkbox rounded text-[#680102] focus:ring-[#680102] w-3.5 h-3.5">
                            </td>
                            <td class="p-4 font-bold text-slate-900 dark:text-slate-100">
                                {{ $student->student_name }}
                            </td>
                            <td class="p-4 font-mono font-semibold">{{ $student->student_mssv }}</td>
                            <td class="p-4 font-mono text-slate-500 dark:text-slate-400">{{ $student->student_email }}</td>
                            <td class="p-4 text-center font-bold">
                                <span class="bg-red-500/10 text-red-500 px-2 py-0.5 rounded-full text-[10px]">
                                    {{ $student->overdue_books_count }} cuốn
                                </span>
                            </td>
                            <td class="p-4 text-slate-500 dark:text-slate-400">
                                {{ \Carbon\Carbon::parse($student->oldest_due_date)->format('d/m/Y') }}
                                <span class="text-[10px] text-red-500 font-bold ml-1">
                                    ({{ max(1, (int) now()->diffInDays(\Carbon\Carbon::parse($student->oldest_due_date))) }} ngày)
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <button onclick="sendSingleMail({{ $student->patron_detail_id }})" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded border border-red-200 dark:border-red-900/50 text-[#680102] dark:text-red-400 hover:bg-[#680102]/5 transition-colors">
                                    <i data-lucide="mail" class="w-3 h-3"></i>
                                    Gửi ngay
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 dark:text-slate-500 font-medium">
                                <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto mb-2 text-emerald-500"></i>
                                Hiện tại không có độc giả nào bị quá hạn mượn sách.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== DUE SOON LIST ==================== -->
    <div x-show="mailType === 'due_soon'" x-cloak class="bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-900/40 rounded-xl overflow-hidden shadow-xs">
        <div class="px-5 py-4 bg-amber-50 dark:bg-amber-950/20 border-b border-amber-100 dark:border-amber-900/30 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Tác vụ hàng loạt:</span>
                <button onclick="sendSelectedMails()" id="btnBulkSendDueSoon" disabled
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    Gửi loạt thư nhắc nhở đã chọn
                </button>
            </div>
            
            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">
                Tìm thấy <span class="text-amber-600 font-extrabold">{{ $dueSoonStudents->count() }}</span> sinh viên sắp đến hạn
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-850/50 border-b border-slate-150 dark:border-slate-800 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                        <th width="4%" class="p-4 text-center">
                            <input type="checkbox" id="selectAllCheckboxDueSoon" onchange="toggleSelectAllPatronsDueSoon(this)" class="rounded text-amber-600 focus:ring-amber-600 w-3.5 h-3.5">
                        </th>
                        <th class="p-4">Họ và tên sinh viên</th>
                        <th class="p-4">Mã số sinh viên (MSSV)</th>
                        <th class="p-4">Hòm thư (Email)</th>
                        <th class="p-4 text-center">Số cuốn sắp đến hạn</th>
                        <th class="p-4">Ngày đến hạn gần nhất</th>
                        <th width="12%" class="p-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($dueSoonStudents as $student)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 text-xs text-slate-700 dark:text-slate-300">
                            <td class="p-4 text-center">
                                <input type="checkbox" name="selected_patrons_due_soon" value="{{ $student->patron_detail_id }}" onchange="updateBulkButtonStateDueSoon()" class="patron-checkbox-due-soon rounded text-amber-600 focus:ring-amber-600 w-3.5 h-3.5">
                            </td>
                            <td class="p-4 font-bold text-slate-900 dark:text-slate-100">
                                {{ $student->student_name }}
                            </td>
                            <td class="p-4 font-mono font-semibold">{{ $student->student_mssv }}</td>
                            <td class="p-4 font-mono text-slate-500 dark:text-slate-400">{{ $student->student_email }}</td>
                            <td class="p-4 text-center font-bold">
                                <span class="bg-amber-500/10 text-amber-600 px-2 py-0.5 rounded-full text-[10px]">
                                    {{ $student->due_soon_books_count }} cuốn
                                </span>
                            </td>
                            <td class="p-4 text-slate-500 dark:text-slate-400">
                                {{ \Carbon\Carbon::parse($student->nearest_due_date)->format('d/m/Y') }}
                                <span class="text-[10px] text-amber-600 font-bold ml-1">
                                    (còn {{ max(0, (int) now()->diffInDays(\Carbon\Carbon::parse($student->nearest_due_date), false)) }} ngày)
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <button onclick="sendSingleMail({{ $student->patron_detail_id }})" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded border border-amber-200 dark:border-amber-900/50 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 transition-colors">
                                    <i data-lucide="mail" class="w-3 h-3"></i>
                                    Gửi ngay
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 dark:text-slate-500 font-medium">
                                <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto mb-2 text-emerald-500"></i>
                                Hiện tại không có độc giả nào sắp đến hạn trả sách.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Toggle Sender Channel
    function setSenderChannel(channel) {
        document.getElementById('senderChannelInput').value = channel;
        const btnSystem = document.getElementById('btnChannelSystem');
        const btnLibrary = document.getElementById('btnChannelLibrary');

        if (channel === 'system') {
            btnSystem.className = "flex-1 py-1 rounded text-[11px] font-bold text-center transition-all bg-[#680102] text-white shadow-sm";
            btnLibrary.className = "flex-1 py-1 rounded text-[11px] font-bold text-center transition-all text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200";
        } else {
            btnLibrary.className = "flex-1 py-1 rounded text-[11px] font-bold text-center transition-all bg-[#680102] text-white shadow-sm";
            btnSystem.className = "flex-1 py-1 rounded text-[11px] font-bold text-center transition-all text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200";
        }
    }

    // Switch mail type (overdue / due_soon)
    function switchMailType() {
        updateBulkButtonState();
        updateBulkButtonStateDueSoon();
    }

    // Toggle check/uncheck all checkboxes (overdue)
    function toggleSelectAllPatrons(source) {
        const checkboxes = document.querySelectorAll('.patron-checkbox');
        checkboxes.forEach(chk => {
            chk.checked = source.checked;
        });
        updateBulkButtonState();
    }

    // Toggle check/uncheck all checkboxes (due soon)
    function toggleSelectAllPatronsDueSoon(source) {
        const checkboxes = document.querySelectorAll('.patron-checkbox-due-soon');
        checkboxes.forEach(chk => {
            chk.checked = source.checked;
        });
        updateBulkButtonStateDueSoon();
    }

    // Enable/disable batch button (overdue)
    function updateBulkButtonState() {
        const checkedCount = document.querySelectorAll('.patron-checkbox:checked').length;
        const btn = document.getElementById('btnBulkSend');
        if (btn) {
            btn.disabled = checkedCount === 0;
        }
    }

    // Enable/disable batch button (due soon)
    function updateBulkButtonStateDueSoon() {
        const checkedCount = document.querySelectorAll('.patron-checkbox-due-soon:checked').length;
        const btn = document.getElementById('btnBulkSendDueSoon');
        if (btn) {
            btn.disabled = checkedCount === 0;
        }
    }

    // Get current mail type from select
    function getCurrentMailType() {
        return document.getElementById('sendMailTypeSelect').value;
    }

    // Get selected patron IDs based on current mail type
    function getSelectedPatronIds() {
        const mailType = getCurrentMailType();
        const selector = mailType === 'due_soon' ? '.patron-checkbox-due-soon:checked' : '.patron-checkbox:checked';
        const selected = [];
        document.querySelectorAll(selector).forEach(chk => {
            selected.push(parseInt(chk.value));
        });
        return selected;
    }

    // Get bulk button based on current mail type
    function getBulkButton() {
        const mailType = getCurrentMailType();
        return mailType === 'due_soon' ? document.getElementById('btnBulkSendDueSoon') : document.getElementById('btnBulkSend');
    }

    // Send single email via AJAX
    function sendSingleMail(patronId) {
        const channel = document.getElementById('senderChannelInput').value;
        const mailType = getCurrentMailType();
        const channelText = channel === 'library' ? 'Mail Thư Viện (mailthuvien@vttu.edu.vn)' : 'Mail Hệ Thống';
        const typeText = mailType === 'due_soon' ? 'sắp đến hạn trả sách' : 'quá hạn mượn sách';
        if (!confirm(`Bạn chắc chắn muốn gửi email nhắc nhở ${typeText} cho sinh viên này qua [${channelText}]?`)) {
            return;
        }

        executeSendMail([patronId]);
    }

    // Send selected batch emails
    function sendSelectedMails() {
        const selected = getSelectedPatronIds();
        if (selected.length === 0) return;

        const channel = document.getElementById('senderChannelInput').value;
        const mailType = getCurrentMailType();
        const channelText = channel === 'library' ? 'Mail Thư Viện (mailthuvien@vttu.edu.vn)' : 'Mail Hệ Thống';
        const typeText = mailType === 'due_soon' ? 'sắp đến hạn trả sách' : 'quá hạn mượn sách';
        if (!confirm(`Bạn chắc chắn muốn gửi thư nhắc nhở ${typeText} cho ${selected.length} sinh viên đã chọn qua [${channelText}]?`)) {
            return;
        }

        executeSendMail(selected);
    }

    // AJAX Executor calling web.php route
    function executeSendMail(patronIds) {
        const mailType = getCurrentMailType();
        const btnBulk = getBulkButton();
        const originalText = btnBulk ? btnBulk.innerHTML : '';
        const channel = document.getElementById('senderChannelInput').value;
        const route = mailType === 'due_soon' ? '{{ route('admin.mails.send-due-soon') }}' : '{{ route('admin.mails.send-overdue') }}';
        
        if (btnBulk) {
            btnBulk.disabled = true;
            btnBulk.innerHTML = `<span class="animate-spin inline-block w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full mr-1.5"></span> Đang gửi...`;
        }

        fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                patron_ids: patronIds,
                sender_channel: channel
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Thành công!', text: data.message, timer: 3000, timerProgressBar: true, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Lỗi!', text: data.message || 'Có lỗi xảy ra khi gửi email.', confirmButtonColor: '#ef4444' });
            }
            setTimeout(() => window.location.reload(), 3000);
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Lỗi hệ thống!', text: 'Không thể kết nối đến server.', confirmButtonColor: '#ef4444' });
            if (btnBulk) {
                btnBulk.disabled = false;
                btnBulk.innerHTML = originalText;
            }
        });
    }
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
