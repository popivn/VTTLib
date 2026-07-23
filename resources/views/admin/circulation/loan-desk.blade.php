@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    @if(session('success'))
        <div class="bg-emerald-500/15 border border-emerald-500/30 text-emerald-500 dark:text-emerald-400 p-3 text-xs rounded-sm font-medium">
            [OK] {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-destructive/15 border border-destructive/30 text-destructive p-3 text-xs rounded-sm font-medium">
            [ERROR] {{ session('error') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-amber-500/15 border border-amber-500/30 text-amber-600 dark:text-amber-400 p-3 text-xs rounded-sm font-medium">
            [WARNING] {{ session('warning') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-foreground">{{ __('Bàn mượn trả sách (Loan Desk)') }}</h1>
            <p class="text-xs text-muted-foreground">{{ __('Quản lý Lưu thông - Mượn, Trả, Đọc tại chỗ, Giữ lại sách') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.circulation.reports.index') }}" class="btn-compact-secondary">
                <i data-lucide="bar-chart-3" class="w-4 h-4 mr-1"></i><span>{{ __('Báo cáo') }}</span>
            </a>
            <a href="{{ route('admin.circulation.tools') }}" class="btn-compact-secondary">
                <i data-lucide="wrench" class="w-4 h-4 mr-1"></i><span>{{ __('Công cụ') }}</span>
            </a>
            <a href="{{ route('admin.circulation.policies.index') }}" class="btn-compact-secondary">
                <i data-lucide="settings" class="w-4 h-4 mr-1"></i><span>{{ __('Quy định') }}</span>
            </a>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
        <div class="flex border-b border-border overflow-x-auto">
            <button type="button" onclick="switchTab('checkout')" id="checkoutTab" 
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-emerald-500 text-emerald-600 bg-emerald-500/5 dark:text-emerald-400 border-r border-border shrink-0 flex items-center gap-1.5">
                <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                <span>{{ __('Mượn & Trả') }}</span>
            </button>
            <button type="button" onclick="switchTab('reading-room')" id="readingRoomTab"
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground border-r border-border shrink-0 flex items-center gap-1.5">
                <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                <span>{{ __('Mượn đọc') }}</span>
            </button>
            <button type="button" onclick="switchTab('hold')" id="holdTab"
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground shrink-0 flex items-center gap-1.5">
                <i data-lucide="bookmark" class="w-3.5 h-3.5"></i>
                <span>{{ __('Giữ lại') }}</span>
            </button>
        </div>

        <!-- AJAX Tab Content Container -->
        <div class="p-3">
            <div id="tabContent" class="space-y-3">
                {{-- Initial content loaded by JS --}}
                <div class="flex flex-col items-center justify-center py-12 text-muted-foreground">
                    <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-primary mb-2"></i>
                    <p class="text-xs">{{ __("Đang tải...") }}</p>
                </div>
            </div>
        </div>






        </div>
    </div>



</div>

<style>
    .input-field {
        width: 100%;
        height: 2.25rem; /* h-9 */
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        background-color: hsl(var(--background));
        color: hsl(var(--foreground));
        border: 1px solid hsl(var(--input));
        border-radius: var(--radius-sm, 0.125rem);
        transition: all 0.2s;
    }
    .input-field:focus {
        outline: none;
        border-color: hsl(var(--primary));
        box-shadow: 0 0 0 1px hsl(var(--primary));
    }
    .btn-compact-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 2.25rem;
        padding: 0 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        background-color: hsl(var(--primary));
        color: hsl(var(--primary-foreground));
        border-radius: var(--radius-sm, 0.125rem);
        transition: all 0.2s;
    }
    .btn-compact-primary:hover {
        background-color: hsl(var(--primary) / 0.9);
    }
    .btn-compact-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 2.25rem;
        padding: 0 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        background-color: hsl(var(--secondary));
        color: hsl(var(--secondary-foreground));
        border: 1px solid hsl(var(--border));
        border-radius: var(--radius-sm, 0.125rem);
        transition: all 0.2s;
    }
    .btn-compact-secondary:hover {
        background-color: hsl(var(--secondary) / 0.8);
    }
    .patron-info-scroll { max-height: 600px; overflow-y: auto; }
</style>

<!-- Recall Modal -->
<div id="recallModal" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center">
    <div class="bg-card text-foreground border border-border rounded-md shadow-lg p-5 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-border">
            <h3 class="text-sm font-bold flex items-center gap-1.5 text-foreground">
                <i data-lucide="rotate-cw" class="w-4 h-4 text-amber-500"></i>
                <span>{{ __("Triệu hồi tài liệu") }}</span>
            </h3>
            <button onclick="closeRecallModal()" class="text-muted-foreground hover:text-foreground">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-foreground mb-1">{{ __("Mã vạch tài liệu") }} *</label>
                <input type="text" id="recallBookBarcode" class="input-field" placeholder="{{ __("Nhập mã vạch tài liệu cần triệu hồi") }}">
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-foreground mb-1">{{ __("Lý do triệu hồi") }}</label>
                <textarea id="recallReason" class="input-field !h-auto py-1.5" rows="2" placeholder="{{ __("Nhập lý do triệu hồi (không bắt buộc)") }}"></textarea>
            </div>
            
            <div class="flex justify-end gap-2 pt-2">
                <button onclick="closeRecallModal()" class="px-3 py-1.5 text-xs bg-secondary text-secondary-foreground border border-border font-semibold rounded-sm hover:opacity-90 transition">
                    {{ __("Hủy") }}
                </button>
                <button onclick="processRecall()" class="px-3 py-1.5 text-xs bg-amber-500 text-white font-semibold rounded-sm hover:bg-amber-600 transition">
                    {{ __("Triệu hồi") }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Declare Lost Modal -->
<div id="declareLostModal" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center">
    <div class="bg-card text-foreground border border-border rounded-md shadow-lg p-5 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-border">
            <h3 class="text-sm font-bold flex items-center gap-1.5 text-foreground">
                <i data-lucide="alert-circle" class="w-4 h-4 text-destructive"></i>
                <span>{{ __("Khai báo mất tài liệu") }}</span>
            </h3>
            <button onclick="closeDeclareLostModal()" class="text-muted-foreground hover:text-foreground">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-foreground mb-1">{{ __("Chọn tài liệu") }} *</label>
                <div id="declareLostBooksList" class="max-h-40 overflow-y-auto space-y-1.5 p-2 bg-muted/20 border border-border rounded-sm">
                    <!-- Books will be populated here -->
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-foreground mb-1">{{ __("Ghi chú") }}</label>
                <textarea id="declareLostNotes" class="input-field !h-auto py-1.5" rows="2" placeholder="{{ __("Nhập ghi chú (không bắt buộc)") }}"></textarea>
            </div>
            
            <div class="flex justify-end gap-2 pt-2">
                <button onclick="closeDeclareLostModal()" class="px-3 py-1.5 text-xs bg-secondary text-secondary-foreground border border-border font-semibold rounded-sm hover:opacity-90 transition">
                    {{ __("Hủy") }}
                </button>
                <button onclick="processDeclareLost()" class="px-3 py-1.5 text-xs bg-destructive text-destructive-foreground font-semibold rounded-sm hover:opacity-90 transition">
                    {{ __("Khai báo mất") }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Lock history and loan data from controller
const lockHistoryData = @json($allLockHistory ?? []);
const loanTransactionsData = @json($allLoanTransactions ?? []);

// Dynamic Lucide icon refresher helper
function updateHTMLAndRefreshIcons(element, htmlContent) {
    if (typeof element === 'string') {
        element = document.getElementById(element);
    }
    if (element) {
        element.innerHTML = htmlContent;
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }
}

// SweetAlert2 styled custom helper for dark/light HSL compatibility
function getSwalConfig(title, icon = 'info', extra = {}) {
    return {
        title: title,
        icon: icon,
        background: 'hsl(var(--card))',
        color: 'hsl(var(--foreground))',
        customClass: {
            popup: 'border border-border rounded-md shadow-lg',
            title: 'text-sm font-bold text-foreground',
            htmlContainer: 'text-xs text-muted-foreground',
            confirmButton: 'px-3 py-1.5 bg-primary text-primary-foreground text-xs font-semibold rounded-sm mx-1 hover:opacity-90 transition-all',
            cancelButton: 'px-3 py-1.5 bg-secondary text-secondary-foreground text-xs font-semibold rounded-sm mx-1 hover:opacity-90 border border-border transition-all',
            input: 'input-field !mx-0'
        },
        buttonsStyling: false,
        ...extra
    };
}

// Tab switching function using AJAX
async function switchTab(tabName) {
    // Update URL query parameter without reloading page
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);

    // Remove active styles from all tabs
    const allTabs = ['checkout', 'readingRoom', 'hold'];
    allTabs.forEach(tab => {
        const el = document.getElementById(tab + 'Tab');
        if (el) {
            el.classList.remove(
                'border-emerald-500', 'text-emerald-600', 'bg-emerald-500/5', 'dark:text-emerald-400',
                'border-purple-500', 'text-purple-600', 'bg-purple-500/5', 'dark:text-purple-400',
                'border-orange-500', 'text-orange-600', 'bg-orange-500/5', 'dark:text-orange-400',
                'border-transparent', 'text-muted-foreground'
            );
            el.classList.add('border-transparent', 'text-muted-foreground');
        }
    });
    
    // Set active tab styles
    const tabKey = tabName === 'reading-room' ? 'readingRoom' : tabName;
    const activeEl = document.getElementById(tabKey + 'Tab');
    if (activeEl) {
        activeEl.classList.remove('border-transparent', 'text-muted-foreground');
        if (tabName === 'checkout') {
            activeEl.classList.add('border-emerald-500', 'text-emerald-600', 'bg-emerald-500/5', 'dark:text-emerald-400');
        } else if (tabName === 'reading-room') {
            activeEl.classList.add('border-purple-500', 'text-purple-600', 'bg-purple-500/5', 'dark:text-purple-400');
        } else if (tabName === 'hold') {
            activeEl.classList.add('border-orange-500', 'text-orange-600', 'bg-orange-500/5', 'dark:text-orange-400');
        }
    }
    
    // Show loading spinner
    const container = document.getElementById('tabContent');
    if (container) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 text-muted-foreground">
                <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-primary mb-2"></i>
                <p class="text-xs">{{ __("Đang tải...") }}</p>
            </div>
        `;
        if (window.lucide) window.lucide.createIcons();
    }

    try {
        const response = await fetch(`{{ route('admin.circulation.tab-content') }}?tab=${tabName}`);
        const data = await response.json();
        if (data.html && container) {
            updateHTMLAndRefreshIcons(container, data.html);
            // Re-bind all input listeners for the new tab
            bindTabEvents(tabName);
        }
    } catch (e) {
        console.error(e);
        if (container) {
            container.innerHTML = `<div class="text-center py-6 text-destructive text-xs">{{ __("Lỗi tải nội dung tab.") }}</div>`;
        }
    }
}

// Auto-switch tab on page load based on query param
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    let tab = urlParams.get('tab');
    if (!tab || !['checkout', 'reading-room', 'hold'].includes(tab)) {
        tab = 'checkout';
    }
    switchTab(tab);
});

// Return single loan inline
function returnSingleBook(loanId, barcode, title) {
    Swal.fire(getSwalConfig('{{ __("Xác nhận trả sách") }}', 'question', {
        html: `Trả tài liệu <strong>${title}</strong>?<br><small class="font-mono">${barcode}</small>`,
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        confirmButtonText: '{{ __("Trả sách") }}',
        cancelButtonText: '{{ __("Hủy") }}'
    })).then((result) => {
        if (!result.isConfirmed) return;

        Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
            text: '{{ __("Đang trả sách...") }}',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        }));

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.circulation.checkin") }}';
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrf) {
            const t = document.createElement('input'); t.type = 'hidden'; t.name = '_token'; t.value = csrf;
            form.appendChild(t);
        }
        const bInput = document.createElement('input'); bInput.type = 'hidden'; bInput.name = 'barcode'; bInput.value = barcode;
        form.appendChild(bInput);
        document.body.appendChild(form);
        form.submit();
    });
}

// Logic for loading active loans into merged checkout tab
async function loadPatronActiveLoans() {
    const patronCode = document.getElementById('patron_code').value.trim();
    if (!patronCode) return;

    const listDiv = document.getElementById('patronActiveLoans');
    updateHTMLAndRefreshIcons(listDiv, '<div class="text-center py-6"><i data-lucide="loader-2" class="w-6 h-6 mx-auto animate-spin text-primary mb-1"></i><p class="text-xs">Đang tải danh sách...</p></div>');

    try {
        const response = await fetch(`{{ route('admin.circulation.search-patron') }}?code=${patronCode}`);
        const data = await response.json();

        if (data.success && data.data) {
            const patronData = data.data;
            
            // Display patron info
            displayPatronResult(data);
            
            let loansHtml = '<div class="space-y-2">';
            if (patronData.active_loans && patronData.active_loans.length > 0) {
                patronData.active_loans.forEach(loan => {
                    const dueDate = new Date(loan.due_date);
                    const isOverdue = dueDate < new Date();
                    const statusColor = isOverdue ? 'text-destructive font-semibold' : 'text-emerald-500 font-semibold';
                    const title = loan.book_item?.bibliographic_record?.title || 'N/A';
                    const barcode = loan.book_item?.barcode || '';
                    const renewalCount = loan.renewal_count || 0;
                    const maxRenewals = loan.max_renewals !== undefined ? loan.max_renewals : (loan.policy?.max_renewals ?? 2);
                    const canRenew = (!loan.status || loan.status === 'borrowed') && renewalCount < maxRenewals;
                    
                    loansHtml += `
                        <div class="p-3 bg-muted/20 border border-border rounded-md flex justify-between items-center hover:bg-muted/40 transition-all">
                            <div class="flex-1 min-w-0 pr-3">
                                <p class="text-xs font-bold text-foreground truncate">${title}</p>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span class="text-[9px] font-mono text-muted-foreground">${barcode}</span>
                                    <span class="text-[9px] ${statusColor}">Hạn trả: ${dueDate.toLocaleDateString('vi-VN')}</span>
                                    ${isOverdue ? '<span class="text-[8px] bg-destructive/10 text-destructive px-1.5 py-0.5 rounded-sm font-bold uppercase">Quá hạn</span>' : ''}
                                    <span class="text-[9px] text-muted-foreground">Gia hạn: ${renewalCount}/${maxRenewals}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                ${canRenew ? `
                                    <button onclick="renewSpecificBook(${loan.id}, '${barcode}', '${addslashes(title)}')" 
                                            class="w-7 h-7 flex items-center justify-center rounded-sm bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500 hover:text-white border border-emerald-500/20 transition-all"
                                            title="Gia hạn">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    </button>
                                ` : `
                                    <button disabled title="Đã hết lượt gia hạn (${renewalCount}/${maxRenewals})"
                                            class="w-7 h-7 flex items-center justify-center rounded-sm bg-muted text-muted-foreground/40 border border-border cursor-not-allowed">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    </button>
                                `}
                                <button onclick="returnSingleBook(${loan.id}, '${barcode}', '${addslashes(title)}')" 
                                        class="flex items-center gap-1 px-2.5 py-1 bg-blue-500/10 text-blue-600 hover:bg-blue-500 hover:text-white border border-blue-500/20 rounded-sm transition-all text-[10px] font-bold">
                                    <i data-lucide="undo-2" class="w-3 h-3"></i>
                                    Trả sách
                                </button>
                            </div>
                        </div>
                    `;
                });
            } else {
                loansHtml += `
                    <div class="text-center py-6 text-muted-foreground italic">
                        <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                        <p class="text-xs">Độc giả này hiện không có sách nào đang mượn.</p>
                    </div>`;
            }
            loansHtml += '</div>';
            updateHTMLAndRefreshIcons(listDiv, loansHtml);
        } else {
            updateHTMLAndRefreshIcons(listDiv, `<div class="text-center py-6 text-destructive font-semibold text-xs"><p>${data.message || 'Không tìm thấy độc giả.'}</p></div>`);
        }
    } catch (error) {
        console.error(error);
        updateHTMLAndRefreshIcons(listDiv, '<div class="text-center py-6 text-destructive text-xs"><p>Lỗi khi kết nối đến máy chủ.</p></div>');
    }
}

async function processReturn(loanId, isOverdue) {
    if (isOverdue) {
        const result = await Swal.fire(getSwalConfig('Sách đã quá hạn!', 'warning', {
            text: 'Bạn có muốn THA THỨ cho lần mượn quá hạn này không? Hệ thống sẽ lưu lại lịch sử tha thứ.',
            showCancelButton: true,
            confirmButtonText: 'Có, tha thứ',
            cancelButtonText: 'Không, tính phạt',
        }));

        if (result.isConfirmed) {
            submitReturn(loanId, true);
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            submitReturn(loanId, false);
        }
    } else {
        submitReturn(loanId, false);
    }
}

function submitReturn(loanId, forgive) {
    Swal.fire(getSwalConfig('Đang xử lý...', 'info', {
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    }));

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('loan_id', loanId);
    formData.append('forgive', forgive ? 1 : 0);

    fetch(`{{ route('admin.circulation.checkin') }}`, {
        method: 'POST',
        body: formData,
        headers: { 
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire(getSwalConfig('Thành công!', 'success', { text: data.message }));
            loadPatronActiveLoans();
        } else {
            Swal.fire(getSwalConfig('Lỗi!', 'error', { text: data.message }));
        }
    })
    .catch(error => {
        Swal.fire(getSwalConfig('Lỗi hệ thống!', 'error', { text: 'Không thể thực hiện trả sách.' }));
    });
}

// Search timeout variables
let patronSearchTimeout;
let bookSearchTimeout;


// Re-bind input event listeners when dynamic HTML changes
function bindTabEvents(tabName) {
    if (tabName === 'checkout') {
        const patronCodeInput = document.getElementById('patron_code');
        if (patronCodeInput) {
            patronCodeInput.addEventListener('input', function() {
                clearTimeout(patronSearchTimeout);
                const value = this.value.trim();
                const mirror = document.getElementById('checkout_patron_code_mirror');
                if (mirror) mirror.value = value;
                
                if (value.length >= 2) {
                    patronSearchTimeout = setTimeout(() => {
                        searchPatronByCode(value);
                        loadPatronActiveLoans();
                    }, 500);
                } else {
                    const infoDiv = document.getElementById('patronInfo');
                    if (infoDiv) {
                        updateHTMLAndRefreshIcons(infoDiv, `
                            <div class="text-center text-muted-foreground text-xs py-6">
                                <i data-lucide="user" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                                <p>{{ __('Nhập mã bạn đọc để hiển thị thông tin') }}</p>
                            </div>
                        `);
                    }
                    const loansDiv = document.getElementById('patronActiveLoans');
                    if (loansDiv) {
                        updateHTMLAndRefreshIcons(loansDiv, `
                            <div class="text-center text-muted-foreground py-5">
                                <i data-lucide="book" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                                <p class="text-xs">{{ __('Nhập mã bạn đọc để hiển thị danh sách sách đang mượn') }}</p>
                            </div>
                        `);
                    }
                }
            });
            patronCodeInput.addEventListener('change', function() {
                const mirror = document.getElementById('checkout_patron_code_mirror');
                if (mirror) mirror.value = this.value.trim();
            });
        }

        const bookBarcodeInput = document.getElementById('book_barcode');
        if (bookBarcodeInput) {
            bookBarcodeInput.addEventListener('input', function() {
                clearTimeout(bookSearchTimeout);
                const value = this.value.trim();
                if (value.length >= 2) {
                    bookSearchTimeout = setTimeout(() => {
                        searchBookByBarcode(value);
                    }, 500);
                } else {
                    const resultDiv = document.getElementById('bookSearchResult');
                    if (resultDiv) {
                        updateHTMLAndRefreshIcons(resultDiv, `
                            <div class="text-center text-muted-foreground text-xs py-6">
                                <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                                <p>{{ __('Nhập mã vạch sách để hiển thị thông tin') }}</p>
                            </div>
                        `);
                    }
                }
            });
        }
    } else if (tabName === 'reading-room') {
        const readingPatronCodeInput = document.getElementById('reading_patron_code');
        if (readingPatronCodeInput) {
            readingPatronCodeInput.addEventListener('input', function() {
                clearTimeout(patronSearchTimeout);
                const value = this.value.trim();
                if (value.length >= 2) {
                    patronSearchTimeout = setTimeout(() => {
                        searchPatronByCode(value);
                    }, 500);
                } else {
                    const infoDiv = document.getElementById('readingPatronInfo');
                    if (infoDiv) {
                        updateHTMLAndRefreshIcons(infoDiv, `
                            <div class="text-center text-muted-foreground text-xs py-6">
                                <i data-lucide="user" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                                <p>{{ __('Nhập mã bạn đọc để hiển thị thông tin') }}</p>
                            </div>
                        `);
                    }
                }
            });
        }

        const readingBookBarcodeInput = document.getElementById('reading_book_barcode');
        if (readingBookBarcodeInput) {
            readingBookBarcodeInput.addEventListener('input', function() {
                clearTimeout(bookSearchTimeout);
                const value = this.value.trim();
                if (value.length >= 2) {
                    bookSearchTimeout = setTimeout(() => { searchBookByBarcode(value); }, 500);
                }
            });
        }
    } else if (tabName === 'hold') {
        const holdPatronCodeInput = document.getElementById('hold_patron_code');
        if (holdPatronCodeInput) {
            holdPatronCodeInput.addEventListener('input', function() {
                clearTimeout(patronSearchTimeout);
                const value = this.value.trim();
                if (value.length >= 2) {
                    patronSearchTimeout = setTimeout(() => {
                        searchPatronByCode(value);
                    }, 500);
                } else {
                    const infoDiv = document.getElementById('holdPatronInfo');
                    if (infoDiv) {
                        updateHTMLAndRefreshIcons(infoDiv, `
                            <div class="text-center text-muted-foreground text-xs py-6">
                                <i data-lucide="user" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                                <p>{{ __('Nhập mã bạn đọc để hiển thị thông tin') }}</p>
                            </div>
                        `);
                    }
                }
            });
        }

        const holdBookBarcodeInput = document.getElementById('hold_book_barcode');
        if (holdBookBarcodeInput) {
            holdBookBarcodeInput.addEventListener('input', function() {
                clearTimeout(bookSearchTimeout);
                const value = this.value.trim();
                if (value.length >= 2) {
                    bookSearchTimeout = setTimeout(() => { searchBookByBarcode(value); }, 500);
                }
            });
        }
    }
}



function searchPatronByCode(code) {
    const url = `{{ route('admin.circulation.search-patron') }}?code=${encodeURIComponent(code)}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            displayPatronResult(data);
        })
        .catch(error => {
            displayPatronError();
        });
}

function searchBookByBarcode(barcode) {
    const url = `{{ route('admin.circulation.search-book') }}?barcode=${encodeURIComponent(barcode)}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            displayBookResult(data);
        })
        .catch(error => {
            displayBookError();
        });
}

function displayPatronResult(patron) {
    // Determine which patron info div to update based on DOM presence
    let infoDiv = document.getElementById('readingPatronInfo')
        || document.getElementById('holdPatronInfo')
        || document.getElementById('patronInfo');

    
    if (!infoDiv) return;
    
    if (patron.success) {
        const canBorrow = patron.data.can_borrow;
        const outstandingFine = patron.data.outstanding_fine || 0;
        const loans = patron.data.current_loans || 0;
        
        let borrowingStatus, statusColor, statusIcon;
        if (!canBorrow) {
            if (loans >= patron.data.max_loans) {
                borrowingStatus = 'Đã đạt giới hạn mượn sách';
                statusColor = 'text-destructive bg-destructive/10 border-destructive/20';
                statusIcon = 'alert-triangle';
            } else if (outstandingFine > 0) {
                borrowingStatus = `Còn nợ phí: ${outstandingFine.toLocaleString('vi-VN')}đ`;
                statusColor = 'text-destructive bg-destructive/10 border-destructive/20';
                statusIcon = 'alert-circle';
            } else {
                borrowingStatus = 'Không thể mượn';
                statusColor = 'text-destructive bg-destructive/10 border-destructive/20';
                statusIcon = 'x';
            }
        } else {
            borrowingStatus = 'Có thể mượn sách';
            statusColor = 'text-emerald-500 bg-emerald-500/10 border-emerald-500/20';
            statusIcon = 'check-circle';
        }
        
        let html = `
            <div class="space-y-3">
                <div class="flex items-start space-x-3 pb-3 border-b border-border">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-20 rounded-md overflow-hidden bg-muted flex items-center justify-center border border-border">
                            ${patron.data.profile_image ? 
                                `<img src="${patron.data.profile_image}" alt="${patron.data.display_name || 'Patron'}" class="w-full h-full object-cover">` :
                                `<i data-lucide="user" class="w-6 h-6 text-muted-foreground"></i>`
                            }
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-foreground truncate">
                            ${patron.data.display_name || patron.data.user?.name || 'N/A'}
                        </h4>
                        <p class="text-xs text-muted-foreground font-mono leading-none mt-0.5">${patron.data.patron_code}</p>
                        <div class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-semibold mt-2 border ${statusColor} gap-1">
                            <i data-lucide="${statusIcon}" class="w-3 h-3"></i>
                            <span>${borrowingStatus}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                            <div>
                                <span class="text-muted-foreground">{{ __("Số sách đang mượn") }}:</span>
                                <span class="text-foreground font-semibold ml-0.5">${loans}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">{{ __("Phí chưa trả") }}:</span>
                                <span class="text-foreground font-semibold ml-0.5">${outstandingFine.toLocaleString('vi-VN')}đ</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Transaction Statistics -->
                <div class="bg-muted/20 border border-border rounded-sm p-2.5 text-xs">
                    <h5 class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5">{{ __("Lịch sử giao dịch") }}</h5>
                    <div class="grid grid-cols-3 gap-1 text-center">
                        <div>
                            <div class="text-primary font-bold text-base leading-none">${patron.data.transaction_stats?.total_checkouts || 0}</div>
                            <div class="text-muted-foreground text-[9px] mt-0.5 uppercase tracking-wide">{{ __("Đã mượn") }}</div>
                        </div>
                        <div>
                            <div class="text-emerald-500 font-bold text-base leading-none">${patron.data.transaction_stats?.total_checkins || 0}</div>
                            <div class="text-muted-foreground text-[9px] mt-0.5 uppercase tracking-wide">{{ __("Đã trả") }}</div>
                        </div>
                        <div>
                            <div class="text-amber-500 font-bold text-base leading-none">${patron.data.transaction_stats?.total_renewals || 0}</div>
                            <div class="text-muted-foreground text-[9px] mt-0.5 uppercase tracking-wide">{{ __("Gia hạn") }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Current Loans Table -->
                ${loans > 0 ? `
                    <div class="bg-card border border-border rounded-md p-3 shadow-sm space-y-2">
                        <h5 class="text-xs font-bold uppercase tracking-wider text-foreground mb-2 flex items-center justify-between">
                            <span>{{ __("Tài liệu đang mượn") }} (${loans})</span>
                        </h5>
                        <div class="overflow-x-auto">
                            <table class="current-loans-table w-full text-xs border border-border rounded-sm">
                                <thead class="bg-muted/60 border-b border-border text-muted-foreground uppercase text-[10px] font-bold">
                                    <tr>
                                        <th class="p-2 text-center w-8"><input type="checkbox" checked disabled class="rounded"></th>
                                        <th class="p-2 text-left w-28">{{ __("Mã tài liệu") }}</th>
                                        <th class="p-2 text-left">{{ __("Mô tả") }}</th>
                                        <th class="p-2 text-right w-24">{{ __("Giá tiền") }}</th>
                                        <th class="p-2 text-center w-24">{{ __("Ngày mượn") }}</th>
                                        <th class="p-2 text-center w-24">{{ __("Hạn trả") }}</th>
                                        <th class="p-2 text-left w-28">{{ __("Người thực hiện") }}</th>
                                        <th class="p-2 text-left w-28">{{ __("Ghi chú") }}</th>
                                        <th class="p-2 text-center w-20">{{ __("Thao tác") }}</th>
                                    </tr>
                                </thead>
                                <tbody id="currentLoansTableBody" class="divide-y divide-border">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted-foreground py-4 text-xs">
                                            <i data-lucide="loader-2" class="w-4 h-4 mx-auto animate-spin text-primary inline mr-1"></i>
                                            {{ __("Đang tải...") }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
        updateHTMLAndRefreshIcons(infoDiv, html);
        
        // After render: load tab-specific data inline inside patron card
        const isReadingRoomTabActive = !!document.getElementById('readingPatronInfo');
        const isHoldTabActive = !!document.getElementById('holdPatronInfo');
        if (isReadingRoomTabActive) {
            setTimeout(() => { loadReadingRoomTransactions(); }, 100);
        } else if (isHoldTabActive) {
            setTimeout(() => { loadPatronReservations(); }, 100);
        } else if (loans > 0 && document.getElementById('currentLoansTableBody')) {
            setTimeout(() => {
                loadCurrentLoans(patron.data.id, patron.data.active_loans);
            }, 100);
        }
        
    } else {
        updateHTMLAndRefreshIcons(infoDiv, `
            <div class="p-3 rounded-sm border bg-destructive/15 border-destructive/30">
                <div class="flex items-center gap-1.5 text-destructive">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span class="text-xs font-semibold">${patron.message || '{{ __("Không tìm thấy bạn đọc") }}'}</span>
                </div>
            </div>
        `);
    }
}

function displayBookResult(book) {
    // Determine which book info div to update based on DOM presence
    let resultDiv = document.getElementById('readingBookInfo')
        || document.getElementById('holdBookInfo')
        || document.getElementById('bookSearchResult');

    
    if (!resultDiv) return;
    
    if (book.success) {
        const isAvailable = book.data.status === 'available';
        
        let html = `
            <div class="p-3 rounded-md border ${isAvailable ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-destructive/15 border-destructive/20'}">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        ${book.data.cover_image ? 
                            `<img src="${book.data.cover_image}" alt="${book.data.title || 'Book cover'}" class="w-16 h-20 object-cover rounded-md shadow-sm border border-border">` :
                            `<div class="w-16 h-20 bg-muted rounded-md flex items-center justify-center border border-border">
                                <i data-lucide="book-open" class="w-6 h-6 text-muted-foreground"></i>
                            </div>`
                        }
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-xs ${isAvailable ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive'} truncate">
                            ${book.data.title || 'N/A'}
                        </h4>
                        <p class="text-[11px] text-muted-foreground mt-1">
                            {{ __("Barcode") }}: <span class="font-mono text-foreground font-semibold">${book.data.barcode}</span>
                        </p>
                        <div class="bg-card/50 rounded-sm p-1.5 border border-border/50 space-y-1 mt-2 text-[11px]">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">{{ __("Author") }}:</span>
                                <span class="text-foreground font-medium truncate ml-1">${book.data.author || '{{ __("N/A") }}'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">{{ __("Call Number") }}:</span>
                                <span class="text-foreground font-mono font-medium truncate ml-1">${book.data.call_number || '{{ __("N/A") }}'}</span>
                            </div>
                        </div>
                        ${book.data.current_loan ? `<p class="text-[10px] text-amber-500 font-semibold mt-2 flex items-center gap-0.5"><i data-lucide="user" class="w-3 h-3"></i>{{ __("On_loan_to") }}: ${book.data.current_loan.patron_name}</p>` : ''}
                    </div>
                    <div class="ml-2 flex-shrink-0">
                        ${isAvailable ? 
                            `<span class="inline-flex items-center px-2 py-0.5 rounded-sm text-[9px] font-bold uppercase bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400">{{ __("Available") }}</span>` :
                            `<span class="inline-flex items-center px-2 py-0.5 rounded-sm text-[9px] font-bold uppercase bg-destructive/15 border border-destructive/20 text-destructive">{{ __("Not_Available") }}</span>`
                        }
                    </div>
                </div>
            </div>
        `;
        updateHTMLAndRefreshIcons(resultDiv, html);
    } else {
        updateHTMLAndRefreshIcons(resultDiv, `
            <div class="p-3 rounded-md border bg-destructive/15 border-destructive/20">
                <p class="text-xs text-destructive font-semibold">${book.message}</p>
            </div>
        `);
    }
}

function displayPatronError() {
    let resultDiv = document.getElementById('readingPatronInfo')
        || document.getElementById('holdPatronInfo')
        || document.getElementById('patronInfo');
    if (resultDiv) {
        updateHTMLAndRefreshIcons(resultDiv, `
            <div class="p-3 rounded-md border bg-destructive/15 border-destructive/20">
                <p class="text-xs text-destructive font-semibold">{{ __("Search_Error") }}</p>
            </div>
        `);
    }
}

function displayBookError() {
    let resultDiv = document.getElementById('readingBookInfo')
        || document.getElementById('holdBookInfo')
        || document.getElementById('bookSearchResult');
    if (resultDiv) {
        updateHTMLAndRefreshIcons(resultDiv, `
            <div class="p-3 rounded-md border bg-destructive/15 border-destructive/20">
                <p class="text-xs text-destructive font-semibold">{{ __("Search_Error") }}</p>
            </div>
        `);
    }
}

// Load current loans for patron
function loadCurrentLoans(patronId, activeLoans = null) {
    const tbody = document.getElementById('currentLoansTableBody');
    if (!tbody) return;
    
    const patronLoans = activeLoans ? activeLoans : (loanTransactionsData ? loanTransactionsData.filter(loan => 
        loan.patron_detail_id === patronId && loan.status === 'borrowed'
    ) : []);
    
    if (patronLoans.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted-foreground py-4 text-xs italic">
                    {{ __("Không có tài liệu nào đang mượn") }}
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = patronLoans.map(loan => {
        const loanDate = loan.loan_date ? new Date(loan.loan_date).toLocaleDateString('vi-VN') : 'N/A';
        const dueDateObj = loan.due_date ? new Date(loan.due_date) : null;
        const dueDateStr = dueDateObj ? dueDateObj.toLocaleDateString('vi-VN') : 'N/A';
        const now = new Date();
        const isOverdue = dueDateObj && dueDateObj < now;
        
        // Remaining days calculation
        const diffTime = dueDateObj ? (dueDateObj - now) : 0;
        const remainingDays = dueDateObj ? Math.ceil(diffTime / (1000 * 60 * 60 * 24)) : 0;
        
        const title = loan.book_item?.bibliographic_record?.title || 'N/A';
        const author = loan.book_item?.bibliographic_record?.author || '';
        const publisher = loan.book_item?.bibliographic_record?.publisher || '';
        const year = loan.book_item?.bibliographic_record?.publish_year || '';
        const price = loan.book_item?.price ? (typeof loan.book_item.price === 'number' ? loan.book_item.price.toLocaleString('vi-VN') + 'đ' : loan.book_item.price) : '0đ';
        const location = loan.book_item?.storage_location?.name || loan.book_item?.location || 'Kho';
        const materialType = loan.book_item?.storage_type || 'Giáo trình';
        const renewalCount = loan.renewal_count || 0;
        const maxRenewals = loan.max_renewals !== undefined ? loan.max_renewals : (loan.policy?.max_renewals ?? 2);
        const canRenew = (!loan.status || loan.status === 'borrowed') && renewalCount < maxRenewals;
        const loanedBy = loan.loaned_by_user?.name || loan.loaned_by_user?.username || 'staff';
        const notes = loan.notes || 'Sách đang mượn';

        let descParts = [title];
        if (author) descParts.push(author);
        if (publisher) descParts.push(publisher);
        if (year) descParts.push(year);
        const fullDesc = descParts.join(' / ');

        let renewBtn = '';
        if (canRenew) {
            renewBtn = `
                <button onclick="renewSpecificBook(${loan.id}, '${loan.book_item?.barcode || ''}', '${addslashes(title)}')" 
                        class="p-1 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-500/10 rounded transition-colors"
                        title="{{ __("Gia hạn") }}">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                </button>
            `;
        } else {
            renewBtn = `
                <button disabled 
                        class="p-1 text-muted-foreground/40 cursor-not-allowed rounded"
                        title="{{ __("Đã hết lượt gia hạn") }} (${renewalCount}/${maxRenewals})">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                </button>
            `;
        }

        return `
            <tr class="border-b border-border hover:bg-muted/20">
                <td class="p-2 text-center"><input type="checkbox" checked disabled class="rounded text-primary"></td>
                <td class="p-2 font-mono font-bold text-foreground text-xs">${loan.book_item?.barcode || 'N/A'}</td>
                <td class="p-2 text-xs">
                    <div class="font-medium text-foreground italic line-clamp-2" title="${fullDesc}">${fullDesc}</div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1 text-[10px]">
                        <div>Vị trí: <span class="text-blue-600 dark:text-blue-400 font-semibold">${location}</span></div>
                        <div>Loại: <span class="text-blue-600 dark:text-blue-400 font-semibold">${materialType}</span></div>
                        <div>Gia hạn: <span class="text-destructive font-bold">${renewalCount} (Lần)</span></div>
                        <div>Còn hạn: <span class="${remainingDays < 0 ? 'text-destructive font-bold' : 'text-emerald-600 font-bold'}">${remainingDays} (Ngày)</span></div>
                    </div>
                </td>
                <td class="p-2 text-right font-medium text-foreground">${price}</td>
                <td class="p-2 text-center text-emerald-600 dark:text-emerald-400 font-semibold">${loanDate}</td>
                <td class="p-2 text-center ${isOverdue ? 'text-destructive font-bold' : 'text-emerald-600 dark:text-emerald-400 font-semibold'}">${dueDateStr}</td>
                <td class="p-2 text-muted-foreground text-xs">${loanedBy}</td>
                <td class="p-2 text-muted-foreground text-xs">${notes}</td>
                <td class="p-2 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="returnSingleBook(${loan.id}, '${loan.book_item?.barcode || ''}', '${addslashes(title)}')" 
                                class="p-1 text-blue-500 hover:text-blue-600 hover:bg-blue-500/10 rounded transition-colors"
                                title="{{ __("Trả sách") }}">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5"></i>
                        </button>
                        ${renewBtn}
                        <button onclick="recallSpecificBook('${loan.book_item?.barcode || ''}', '${addslashes(title)}')" 
                                class="p-1 text-amber-500 hover:text-amber-600 hover:bg-amber-500/10 rounded transition-colors"
                                title="{{ __("Triệu hồi") }}">
                            <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                        </button>
                        <button onclick="declareLostSpecificBook('${loan.book_item?.barcode || ''}', '${addslashes(title)}')" 
                                class="p-1 text-destructive hover:text-destructive-foreground hover:bg-destructive/10 rounded transition-colors"
                                title="{{ __("Khai báo mất") }}">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    if (window.lucide) window.lucide.createIcons();
}

// Helper to escape single quotes in JS strings from Blade template
function addslashes(str) {
    return str.replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

// Recall specific book
function recallSpecificBook(barcode, title) {
    if (!barcode) {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Không thể triệu hồi tài liệu này") }}' }));
        return;
    }
    document.getElementById('recallBookBarcode').value = barcode;
    document.getElementById('recallReason').value = `Triệu hồi tài liệu: ${title}`;
    showRecallModal();
}

// Declare specific book lost
function declareLostSpecificBook(barcode, title) {
    if (!barcode) {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Không thể khai báo mất tài liệu này") }}' }));
        return;
    }
    
    Swal.fire(getSwalConfig('{{ __("Xác nhận khai báo mất tài liệu") }}', 'warning', {
        html: `<strong>${title}</strong><br><small>${barcode}</small>`,
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: '{{ __("Khai báo mất") }}',
        cancelButtonText: '{{ __("Hủy") }}'
    })).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
                text: '{{ __("Đang khai báo mất tài liệu...") }}',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            }));
            
            fetch('{{ route("admin.circulation.declare-lost") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    barcode: barcode,
                    notes: `Khai báo mất: ${title}`
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', { text: `{{ __("Khai báo mất tài liệu thành công") }}: ${title}` }));
                    const patronCode = document.getElementById('patron_code').value.trim();
                    if (patronCode) searchPatronByCode(patronCode);
                } else {
                    Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
                }
            })
            .catch(error => {
                Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra") }}' }));
            });
        }
    });
}

// Renew specific book
function renewSpecificBook(loanId, barcode, title) {
    if (!loanId) {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Không thể gia hạn tài liệu này") }}' }));
        return;
    }
    
    Swal.fire(getSwalConfig('{{ __("Xác nhận gia hạn tài liệu") }}', 'warning', {
        html: `Bạn có chắc chắn muốn gia hạn tài liệu <strong>${title}</strong>?<br><small>${barcode}</small>`,
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: '{{ __("Gia hạn") }}',
        cancelButtonText: '{{ __("Hủy") }}'
    })).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
                text: '{{ __("Đang gia hạn tài liệu...") }}',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            }));
            
            const renewUrl = '{{ route("admin.circulation.renew", ":id") }}'.replace(':id', loanId);
            
            fetch(renewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', { text: data.message || '{{ __("Gia hạn tài liệu thành công") }}' }));
                    const patronCode = document.getElementById('patron_code').value.trim();
                    if (patronCode) {
                        searchPatronByCode(patronCode);
                    } else {
                        setTimeout(() => { window.location.reload(); }, 1500);
                    }
                } else {
                    Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra khi gia hạn tài liệu") }}' }));
            });
        }
    });
}

function showRecallModal() {
    document.getElementById('recallModal').classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}

function closeRecallModal() {
    document.getElementById('recallModal').classList.add('hidden');
    document.getElementById('recallBookBarcode').value = '';
    document.getElementById('recallReason').value = '';
}

function showDeclareLostModal() {
    document.getElementById('declareLostModal').classList.remove('hidden');
    loadPatronBooksForDeclareLost();
    if (window.lucide) window.lucide.createIcons();
}

function closeDeclareLostModal() {
    document.getElementById('declareLostModal').classList.add('hidden');
    document.getElementById('declareLostNotes').value = '';
}

// Load patron books for declare lost
function loadPatronBooksForDeclareLost() {
    const patronCode = document.getElementById('patron_code').value.trim();
    if (!patronCode) return;
    
    fetch(`{{ route('admin.circulation.search-patron') }}?patron_code=${encodeURIComponent(patronCode)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.current_loans > 0) {
                loadCurrentLoansForDeclareLost(data.data.id, data.data.active_loans);
            } else {
                updateHTMLAndRefreshIcons('declareLostBooksList', `<div class="text-muted-foreground text-xs text-center py-4">{{ __("Bạn đọc không có tài liệu đang mượn") }}</div>`);
            }
        })
        .catch(error => {
            console.error('Error loading patron books:', error);
        });
}

function loadCurrentLoansForDeclareLost(patronId, activeLoans = null) {
    const listDiv = document.getElementById('declareLostBooksList');
    const patronLoans = activeLoans ? activeLoans : (loanTransactionsData ? loanTransactionsData.filter(loan => 
        loan.patron_detail_id === patronId && loan.status === 'borrowed'
    ) : []);
    
    if (patronLoans.length === 0) {
        updateHTMLAndRefreshIcons(listDiv, `<div class="text-muted-foreground text-xs text-center py-4">{{ __("Bạn đọc không có tài liệu đang mượn") }}</div>`);
        return;
    }
    
    let html = '<div class="space-y-1.5">';
    patronLoans.forEach(loan => {
        html += `
            <div class="flex items-center p-2 bg-muted/40 rounded-sm">
                <input type="checkbox" id="declare_lost_book_${loan.book_item.id}" value="${loan.book_item.barcode}" class="mr-2 rounded-sm border-border text-primary focus:ring-primary focus:ring-offset-0">
                <label for="declare_lost_book_${loan.book_item.id}" class="text-xs text-foreground cursor-pointer flex-1 truncate">
                    ${loan.book_item.bibliographic_record.title} - <span class="font-mono text-[10px] text-muted-foreground">${loan.book_item.barcode}</span>
                </label>
            </div>
        `;
    });
    html += '</div>';
    updateHTMLAndRefreshIcons(listDiv, html);
}

// Process recall
function processRecall() {
    const barcode = document.getElementById('recallBookBarcode').value.trim();
    const reason = document.getElementById('recallReason').value.trim();
    
    if (!barcode) {
        Swal.fire(getSwalConfig('{{ __("Thông báo") }}', 'warning', { text: '{{ __("Vui lòng nhập mã vạch tài liệu") }}' }));
        return;
    }
    
    Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
        text: '{{ __("Đang triệu hồi tài liệu...") }}',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    }));
    
    fetch('{{ route("admin.circulation.recall") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ barcode: barcode, reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = data.message;
            if (data.data.is_overdue) {
                message += `\n\nTài liệu đã quá hạn. Hạn trả không thay đổi: ${data.data.new_due_date}`;
            } else {
                message += `\n\nHạn trả đã cập nhật thành ngày triệu hồi: ${data.data.new_due_date}`;
            }
            Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', { text: message }));
            closeRecallModal();
            setTimeout(() => { window.location.reload(); }, 1500);
        } else {
            Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
        }
    })
    .catch(error => {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra khi triệu hồi tài liệu") }}' }));
    });
}

// Process declare lost
function processDeclareLost() {
    const checkboxes = document.querySelectorAll('#declareLostBooksList input[type="checkbox"]:checked');
    const notes = document.getElementById('declareLostNotes').value.trim();
    
    if (checkboxes.length === 0) {
        Swal.fire(getSwalConfig('{{ __("Thông báo") }}', 'warning', { text: '{{ __("Vui lòng chọn ít nhất một tài liệu") }}' }));
        return;
    }
    
    Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
        text: '{{ __("Đang khai báo mất tài liệu...") }}',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    }));
    
    const promises = Array.from(checkboxes).map(cb => {
        return fetch('{{ route("admin.circulation.declare-lost") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ barcode: cb.value, notes: notes || 'Khai báo mất từ thủ thư' })
        }).then(r => r.json());
    });
    
    Promise.all(promises)
        .then(results => {
            const failed = results.filter(r => !r.success);
            if (failed.length > 0) {
                Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: failed[0].message || 'Có lỗi xảy ra khi khai báo mất một số tài liệu' }));
            } else {
                Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', { text: '{{ __("Khai báo mất tài liệu thành công") }}' }));
                closeDeclareLostModal();
                setTimeout(() => { window.location.reload(); }, 1500);
            }
        })
        .catch(err => {
            Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra") }}' }));
        });
}

// Loan Transaction Actions
function recallLoanTransaction(barcode, title) {
    if (!barcode) {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Không thể triệu hồi tài liệu này") }}' }));
        return;
    }
    document.getElementById('recallBookBarcode').value = barcode;
    document.getElementById('recallReason').value = `Triệu hồi tài liệu: ${title}`;
    showRecallModal();
}

function declareLostLoanTransaction(barcode, title) {
    declareLostSpecificBook(barcode, title);
}

// ==================== READING ROOM FUNCTIONS ====================

// Process reading room checkout
function processReadingRoomCheckout() {
    const patronCode = document.getElementById('reading_patron_code').value.trim();
    const bookBarcode = document.getElementById('reading_book_barcode').value.trim();
    const notes = document.getElementById('reading_notes').value.trim();
    
    if (!patronCode || !bookBarcode) {
        Swal.fire(getSwalConfig('{{ __("Thông báo") }}', 'warning', { text: '{{ __("Vui lòng nhập mã bạn đọc và mã tài liệu") }}' }));
        return;
    }
    
    Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
        text: '{{ __("Đang xử lý mượn đọc...") }}',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    }));
    
    fetch('{{ route("admin.circulation.reading-room.checkout") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ patron_code: patronCode, barcode: bookBarcode, notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', {
                html: `
                    ${data.message}<br><br>
                    <small style="text-align:left; display:block;">
                        <strong>{{ __("Bạn đọc") }}:</strong> ${data.data.patron_name}<br>
                        <strong>{{ __("Tài liệu") }}:</strong> ${data.data.book_title}<br>
                        <strong>{{ __("Hạn trả") }}:</strong> ${data.data.due_time}
                    </small>
                `
            }));
            document.getElementById('reading_book_barcode').value = '';
            loadReadingRoomTransactions();
        } else {
            Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
        }
    })
    .catch(error => {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra. Vui lòng thử lại.") }}' }));
    });
}

// Load reading room transactions for patron - inject into patron info card
function loadReadingRoomTransactions() {
    const patronCode = document.getElementById('reading_patron_code').value.trim();
    if (!patronCode) return;

    // Find or create the inline list container inside the patron info card
    let inlineList = document.getElementById('readingRoomInlineList');
    if (!inlineList) {
        const patronInfoDiv = document.getElementById('readingPatronInfo');
        if (patronInfoDiv && patronInfoDiv.querySelector('.space-y-3, div')) {
            const wrapper = document.createElement('div');
            wrapper.id = 'readingRoomInlineList';
            wrapper.className = 'mt-3 pt-3 border-t border-border';
            wrapper.innerHTML = `<div class="text-center py-3"><i data-lucide="loader-2" class="w-4 h-4 mx-auto animate-spin text-primary"></i></div>`;
            patronInfoDiv.appendChild(wrapper);
        }
        inlineList = document.getElementById('readingRoomInlineList');
    } else {
        inlineList.innerHTML = `<div class="text-center py-3"><i data-lucide="loader-2" class="w-4 h-4 mx-auto animate-spin text-primary"></i></div>`;
    }
    if (!inlineList) return;

    fetch(`{{ route("admin.circulation.reading-room.transactions") }}?patron_code=${patronCode}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayReadingRoomTransactions(data, inlineList);
            } else {
                updateHTMLAndRefreshIcons(inlineList, `<div class="text-center text-destructive py-3 text-xs font-semibold">${data.message}</div>`);
            }
        })
        .catch(() => {
            updateHTMLAndRefreshIcons(inlineList, `<div class="text-center text-destructive py-3 text-xs">{{ __("Có lỗi xảy ra khi tải danh sách") }}</div>`);
        });
}

// Display reading room transactions inline inside patron info card
function displayReadingRoomTransactions(data, container) {
    if (!container) container = document.getElementById('readingRoomInlineList');
    if (!container) return;

    if (!data.transactions || data.transactions.length === 0) {
        updateHTMLAndRefreshIcons(container, `
            <div class="text-center text-muted-foreground py-4">
                <i data-lucide="check-circle" class="w-6 h-6 mx-auto mb-1 text-muted-foreground/30"></i>
                <p class="text-xs">{{ __("Bạn đọc không có tài liệu nào đang mượn đọc") }}</p>
            </div>
        `);
        return;
    }

    let html = `
        <h5 class="text-[10px] font-bold uppercase tracking-wider text-purple-500 mb-2 flex items-center gap-1">
            <i data-lucide="book-open" class="w-3 h-3"></i>
            {{ __("Tài liệu đang mượn đọc") }} (${data.total_count})
        </h5>
        <div class="space-y-2">
    `;

    data.transactions.forEach(transaction => {
        const statusClass = transaction.is_overdue ? 'text-destructive font-bold' : 'text-emerald-500 font-semibold';
        const statusText = transaction.is_overdue ? '{{ __("Quá hạn") }}' : '{{ __("Đang mượn") }}';

        html += `
            <div class="p-2.5 bg-muted/20 border border-border rounded-md flex items-center justify-between hover:bg-muted/40 transition-all">
                <div class="flex-1 min-w-0 pr-3">
                    <p class="text-xs font-bold text-foreground truncate">${transaction.book_title}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="text-[9px] font-mono text-muted-foreground">${transaction.barcode}</span>
                        <span class="text-[9px] ${statusClass}">${statusText}</span>
                        <span class="text-[9px] text-muted-foreground">Mượn: ${transaction.checkout_time}</span>
                        <span class="text-[9px] text-muted-foreground">Hạn: ${transaction.due_time}</span>
                    </div>
                </div>
                <button onclick="returnReadingRoomSingle(${transaction.id}, '${transaction.barcode}', '${addslashes(transaction.book_title)}')"
                        class="flex items-center gap-1 px-2.5 py-1 bg-purple-500/10 text-purple-600 hover:bg-purple-500 hover:text-white border border-purple-500/20 rounded-sm transition-all text-[10px] font-bold shrink-0">
                    <i data-lucide="undo-2" class="w-3 h-3"></i>
                    Trả
                </button>
            </div>
        `;
    });

    html += `</div>`;
    updateHTMLAndRefreshIcons(container, html);
}

// Return a single reading room transaction
function returnReadingRoomSingle(transactionId, barcode, title) {
    Swal.fire(getSwalConfig('{{ __("Xác nhận trả tài liệu đọc") }}', 'question', {
        html: `Trả tài liệu <strong>${title}</strong>?<br><small class="font-mono">${barcode}</small>`,
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        confirmButtonText: '{{ __("Trả sách") }}',
        cancelButtonText: '{{ __("Hủy") }}'
    })).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
            text: '{{ __("Đang trả tài liệu...") }}',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        }));
        fetch('{{ route("admin.circulation.reading-room.checkin") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ transaction_ids: [transactionId] })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', { text: data.message }));
                loadReadingRoomTransactions();
            } else {
                Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
            }
        })
        .catch(() => Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra") }}' })));
    });
}

// Refresh reading room tab
function refreshReadingRoom() {
    const patronCode = document.getElementById('reading_patron_code').value.trim();
    if (patronCode) {
        searchPatronByCode(patronCode);
    }
}




// processReadingRoomCheckin kept as no-op (checkboxes removed; use returnReadingRoomSingle instead)
function processReadingRoomCheckin() {}

// loadAllReadingRoomTransactions — section removed, kept as no-op for safety
function loadAllReadingRoomTransactions() {}


// ==================== HOLD/RESERVE FUNCTIONS ====================

// Process place hold
function processPlaceHold() {
    const patronCode = document.getElementById('hold_patron_code').value.trim();
    const bookBarcode = document.getElementById('hold_book_barcode').value.trim();
    const notes = document.getElementById('hold_notes').value.trim();
    
    if (!patronCode || !bookBarcode) {
        Swal.fire(getSwalConfig('{{ __("Thông báo") }}', 'warning', { text: '{{ __("Vui lòng nhập mã bạn đọc và mã tài liệu") }}' }));
        return;
    }
    
    Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
        text: '{{ __("Đang giữ lại sách...") }}',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    }));
    
    fetch('{{ route("admin.circulation.hold.place") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ patron_code: patronCode, barcode: bookBarcode, notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', {
                html: `
                    ${data.message}<br><br>
                    <small style="text-align: left; display: block;">
                        <strong>{{ __("Bạn đọc") }}:</strong> ${data.data.patron_name}<br>
                        <strong>{{ __("Tài liệu") }}:</strong> ${data.data.book_title}<br>
                        <strong>{{ __("Trạng thái") }}:</strong> ${data.data.status_display}<br>
                        <strong>{{ __("Hết hạn") }}:</strong> ${data.data.expiry_date}
                    </small>
                `
            }));
            document.getElementById('hold_book_barcode').value = '';
            document.getElementById('hold_notes').value = '';
            loadPatronReservations();
        } else {
            Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
        }
    })
    .catch(error => {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra. Vui lòng thử lại.") }}' }));
    });
}

// Load patron reservations - inject into patron info card
function loadPatronReservations() {
    const patronCode = document.getElementById('hold_patron_code').value.trim();
    if (!patronCode) return;

    // Find or create inline list container inside holdPatronInfo
    let inlineList = document.getElementById('holdReservationsInlineList');
    if (!inlineList) {
        const patronInfoDiv = document.getElementById('holdPatronInfo');
        if (patronInfoDiv) {
            const wrapper = document.createElement('div');
            wrapper.id = 'holdReservationsInlineList';
            wrapper.className = 'mt-3 pt-3 border-t border-border';
            wrapper.innerHTML = `<div class="text-center py-3"><i data-lucide="loader-2" class="w-4 h-4 mx-auto animate-spin text-primary"></i></div>`;
            patronInfoDiv.appendChild(wrapper);
        }
        inlineList = document.getElementById('holdReservationsInlineList');
    } else {
        inlineList.innerHTML = `<div class="text-center py-3"><i data-lucide="loader-2" class="w-4 h-4 mx-auto animate-spin text-primary"></i></div>`;
    }
    if (!inlineList) return;

    fetch(`{{ route("admin.circulation.hold.patron") }}?patron_code=${patronCode}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPatronReservations(data.data, inlineList);
            } else {
                updateHTMLAndRefreshIcons(inlineList, `<div class="text-center text-destructive py-3 text-xs font-semibold">${data.message}</div>`);
            }
        })
        .catch(() => {
            updateHTMLAndRefreshIcons(inlineList, `<div class="text-center text-destructive py-3 text-xs">{{ __("Có lỗi xảy ra khi tải danh sách") }}</div>`);
        });
}

// Display patron reservations inline inside patron info card
function displayPatronReservations(data, container) {
    if (!container) container = document.getElementById('holdReservationsInlineList');
    if (!container) return;

    if (!data.reservations || data.reservations.length === 0) {
        updateHTMLAndRefreshIcons(container, `
            <div class="text-center text-muted-foreground py-4">
                <i data-lucide="bookmark" class="w-6 h-6 mx-auto mb-1 text-muted-foreground/30"></i>
                <p class="text-xs">{{ __("Bạn đọc chưa có yêu cầu đặt giữ nào đang hoạt động") }}</p>
            </div>
        `);
        return;
    }

    let html = `
        <h5 class="text-[10px] font-bold uppercase tracking-wider text-orange-500 mb-2 flex items-center gap-1">
            <i data-lucide="bookmark" class="w-3 h-3"></i>
            {{ __("Danh sách đặt giữ") }} (${data.total_count})
        </h5>
        <div class="space-y-2">
    `;

    data.reservations.forEach(reservation => {
        html += `
            <div class="p-2.5 bg-muted/20 border border-border rounded-md hover:bg-muted/40 transition-all">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-foreground truncate">${reservation.book_title}</p>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="text-[9px] font-mono text-muted-foreground">${reservation.barcode}</span>
                            <span class="text-[9px] ${reservation.status_color} font-semibold">${reservation.status_display}</span>
                            <span class="text-[9px] text-muted-foreground">Đặt: ${reservation.reservation_date}</span>
                            <span class="text-[9px] text-muted-foreground">Hết hạn: ${reservation.expiry_date}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        ${reservation.status === 'ready' ? `
                            <button onclick="fulfillReservation(${reservation.id})"
                                    class="flex items-center gap-1 px-2 py-1 bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500 hover:text-white border border-emerald-500/20 rounded-sm transition-all text-[10px] font-bold"
                                    title="{{ __("Cho mượn") }}">
                                <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                Cho mượn
                            </button>
                        ` : ''}
                        <button onclick="cancelReservation(${reservation.id})"
                                class="flex items-center gap-1 px-2 py-1 bg-destructive/10 text-destructive hover:bg-destructive hover:text-white border border-destructive/20 rounded-sm transition-all text-[10px] font-bold"
                                title="{{ __("Hủy") }}">
                            <i data-lucide="x" class="w-3 h-3"></i>
                            Hủy
                        </button>
                    </div>
                </div>
                ${reservation.notes ? `<div class="text-[10px] text-muted-foreground mt-1.5 italic border-t border-border/50 pt-1">${reservation.notes}</div>` : ''}
            </div>
        `;
    });

    html += `</div>`;
    updateHTMLAndRefreshIcons(container, html);
}

// Refresh hold tab
function refreshHoldTab() {
    const patronCode = document.getElementById('hold_patron_code').value.trim();
    if (patronCode) searchPatronByCode(patronCode);
}

// Cancel reservation
function cancelReservation(reservationId) {
    Swal.fire(getSwalConfig('{{ __("Xác nhận hủy") }}', 'warning', {
        text: '{{ __("Bạn có chắc muốn hủy reservation này?") }}',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: '{{ __("Hủy") }}',
        cancelButtonText: '{{ __("Không") }}'
    })).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
                text: '{{ __("Đang hủy reservation...") }}',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            }));
            
            fetch('{{ route("admin.circulation.hold.cancel") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ reservation_id: reservationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', { text: data.message }));
                    loadPatronReservations();
                } else {
                    Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
                }
            })
            .catch(error => {
                Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra. Vui lòng thử lại.") }}' }));
            });
        }
    });
}

// Fulfill reservation (convert to loan)
function fulfillReservation(reservationId) {
    Swal.fire(getSwalConfig('{{ __("Xác nhận cho mượn") }}', 'question', {
        text: '{{ __("Chuyển reservation thành mượn sách?") }}',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: '{{ __("Cho mượn") }}',
        cancelButtonText: '{{ __("Hủy") }}'
    })).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
                text: '{{ __("Đang cho mượn sách...") }}',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            }));
            
            fetch('{{ route("admin.circulation.hold.fulfill") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ reservation_id: reservationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', {
                        html: `
                            ${data.message}<br><br>
                            <small style="text-align: left; display: block;">
                                <strong>{{ __("Hạn trả") }}:</strong> ${data.data.due_date}<br>
                                <strong>{{ __("Ngày mượn") }}:</strong> ${data.data.loan_date}
                            </small>
                        `
                    }));
                    loadPatronReservations();
                } else {
                    Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
                }
            })
            .catch(error => {
                Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra. Vui lòng thử lại.") }}' }));
            });
        }
    });
}




</script>
@endsection
