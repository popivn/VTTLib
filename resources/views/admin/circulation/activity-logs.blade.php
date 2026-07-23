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

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-border">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-foreground">{{ __('Nhật ký lưu thông') }}</h1>
            <p class="text-xs text-muted-foreground">{{ __('Xem và quản lý tất cả các hoạt động mượn, trả, gia hạn, báo mất tài liệu') }}</p>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.circulation.loan-desk') }}" class="btn-compact-secondary w-full sm:w-auto flex justify-center items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i><span>{{ __('Bàn mượn trả') }}</span>
            </a>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-card rounded-md border border-border p-3 shadow-sm">
        <form action="{{ route('admin.circulation.logs') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">{{ __('Tìm kiếm') }}</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="{{ __('Tìm mã bạn đọc, tên, mã vạch sách, tên sách...') }}"
                           class="input-field pl-8 !h-9">
                    <div class="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-muted-foreground">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">{{ __('Trạng thái') }}</label>
                <select name="status" class="input-field !h-9" onchange="this.form.submit()">
                    <option value="">{{ __('Tất cả trạng thái') }}</option>
                    <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>{{ __('Đang mượn (Borrowed)') }}</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>{{ __('Đã trả (Returned)') }}</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>{{ __('Quá hạn (Overdue)') }}</option>
                    <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>{{ __('Báo mất (Lost)') }}</option>
                    <option value="recalled" {{ request('status') === 'recalled' ? 'selected' : '' }}>{{ __('Triệu hồi (Recalled)') }}</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground font-semibold rounded-sm text-xs hover:opacity-90 transition !h-9 flex items-center gap-1">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>{{ __('Lọc') }}</span>
                </button>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('admin.circulation.logs') }}" class="px-4 py-2 bg-secondary text-secondary-foreground border border-border font-semibold rounded-sm text-xs hover:opacity-90 transition !h-9 flex items-center justify-center">
                        {{ __('Xóa lọc') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] font-semibold">
                    <tr>
                        <th class="p-2.5 text-left w-36">{{ __('Thời gian thực hiện') }}</th>
                        <th class="p-2.5 text-left w-52">{{ __('Độc giả') }}</th>
                        <th class="p-2.5 text-left">{{ __('Tài liệu') }}</th>
                        <th class="p-2.5 text-center w-28">{{ __('Ngày mượn') }}</th>
                        <th class="p-2.5 text-center w-28">{{ __('Hạn trả') }}</th>
                        <th class="p-2.5 text-center w-24">{{ __('Trạng thái') }}</th>
                        <th class="p-2.5 text-center w-24">{{ __('Thao tác') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($logs as $loan)
                    @php
                        $dueDate = $loan->due_date;
                        $title = $loan->bookItem->bibliographicRecord->title ?? 'N/A';
                        $author = $loan->bookItem->bibliographicRecord->author ?? '';
                        $publisher = $loan->bookItem->bibliographicRecord->publisher ?? '';
                        $year = $loan->bookItem->bibliographicRecord->publish_year ?? '';
                        $fullDesc = implode(' / ', array_filter([$title, $author, $publisher, $year]));
                    @endphp
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="p-2.5 text-muted-foreground font-mono">
                            {{ $loan->created_at ? $loan->created_at->format('d/m/Y H:i') : 'N/A' }}
                        </td>
                        <td class="p-2.5">
                            <div class="font-semibold text-foreground text-xs">{{ $loan->patron->display_name ?? $loan->patron->user->name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-muted-foreground font-mono leading-none mt-0.5">{{ $loan->patron->patron_code ?? 'N/A' }}</div>
                        </td>
                        <td class="p-2.5">
                            <div class="font-medium text-foreground italic line-clamp-1" title="{{ $fullDesc }}">{{ $fullDesc }}</div>
                            <div class="flex items-center gap-2 mt-1 text-[10px]">
                                <span class="font-mono text-muted-foreground">Barcode: <strong class="text-foreground font-semibold">{{ $loan->bookItem->barcode ?? 'N/A' }}</strong></span>
                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                <span class="text-muted-foreground">Vị trí: <strong class="text-foreground font-semibold">{{ $loan->bookItem->storageLocation->name ?? $loan->bookItem->location ?? 'Kho' }}</strong></span>
                            </div>
                        </td>
                        <td class="p-2.5 text-center text-muted-foreground">
                            {{ $loan->loan_date ? $loan->loan_date->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td class="p-2.5 text-center font-medium {{ $loan->isOverdue() ? 'text-destructive font-bold' : 'text-muted-foreground' }}">
                            {{ $loan->due_date ? $loan->due_date->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td class="p-2.5 text-center">
                            @php
                                $statusClass = match($loan->status) {
                                    'borrowed' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
                                    'returned' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
                                    'overdue' => 'bg-destructive/10 text-destructive border border-destructive/20',
                                    'lost' => 'bg-muted text-muted-foreground border border-border',
                                    'recalled' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20',
                                    default => 'bg-muted text-muted-foreground border border-border'
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-sm text-[9px] font-bold uppercase {{ $statusClass }}">
                                {{ __(ucfirst($loan->status)) }}
                            </span>
                        </td>
                        <td class="p-2.5 text-center">
                            @if($loan->status === 'borrowed' || $loan->status === 'recalled')
                            <div class="flex items-center justify-center gap-1.5">
                                @if($loan->canRenew())
                                <button onclick="renewSpecificBook({{ $loan->id }}, '{{ $loan->bookItem->barcode }}', '{{ addslashes($title) }}')" 
                                        class="p-1 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-500/10 rounded transition-colors" 
                                        title="{{ __("Gia hạn") }}">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                </button>
                                @else
                                <button disabled 
                                        class="p-1 text-muted-foreground/40 cursor-not-allowed rounded" 
                                        title="{{ __("Đã hết lượt gia hạn") }} ({{ $loan->renewal_count }}/{{ $loan->policy->max_renewals ?? '?' }})">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                </button>
                                @endif

                                <button onclick="recallSpecificBook('{{ $loan->bookItem->barcode }}', '{{ addslashes($title) }}')" 
                                        class="p-1 text-amber-500 hover:text-amber-600 hover:bg-amber-500/10 rounded transition-colors" 
                                        title="{{ __("Triệu hồi") }}">
                                    <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                                </button>

                                <button onclick="declareLostSpecificBook('{{ $loan->bookItem->barcode }}', '{{ addslashes($title) }}')" 
                                        class="p-1 text-destructive hover:text-destructive/90 hover:bg-destructive/10 rounded transition-colors" 
                                        title="{{ __("Khai báo mất") }}">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                            @else
                            <span class="text-[10px] text-muted-foreground">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-muted-foreground italic">
                            {{ __('Không có lịch sử lưu thông nào phù hợp với bộ lọc.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="p-3 border-t border-border bg-muted/10 flex justify-end">
            {{ $logs->appends(request()->query())->links() }}
        </div>
        @endif
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
        box-shadow: 0 0 0 2px hsl(var(--primary) / 0.1);
    }
    .btn-compact-secondary {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.125rem;
        border: 1px solid hsl(var(--border));
        background-color: hsl(var(--secondary));
        color: hsl(var(--secondary-foreground));
        transition: all 0.2s;
    }
    .btn-compact-secondary:hover {
        background-color: hsl(var(--accent));
        color: hsl(var(--accent-foreground));
    }
</style>

@push('scripts')
<script>
// Escape single quotes in JS strings
function addslashes(str) {
    return str.replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

// SweetAlert2 HSL theme config
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

// Renew action
function renewSpecificBook(loanId, barcode, title) {
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
                    setTimeout(() => { window.location.reload(); }, 1200);
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

// Recall action
function recallSpecificBook(barcode, title) {
    Swal.fire(getSwalConfig('{{ __("Xác nhận triệu hồi tài liệu") }}', 'warning', {
        html: `Nhập lý do triệu hồi tài liệu: <strong>${title}</strong>`,
        input: 'text',
        inputValue: `Triệu hồi tài liệu: ${title}`,
        showCancelButton: true,
        confirmButtonText: '{{ __("Triệu hồi") }}',
        cancelButtonText: '{{ __("Hủy") }}',
        inputValidator: (value) => {
            if (!value) {
                return '{{ __("Vui lòng nhập lý do triệu hồi") }}';
            }
        }
    })).then((result) => {
        if (result.isConfirmed) {
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
                body: JSON.stringify({ barcode: barcode, reason: result.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', { text: '{{ __("Gửi yêu cầu triệu hồi tài liệu thành công") }}' }));
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra") }}' }));
            });
        }
    });
}

// Declare lost action
function declareLostSpecificBook(barcode, title) {
    Swal.fire(getSwalConfig('{{ __("Xác nhận báo mất tài liệu") }}', 'warning', {
        html: `Bạn có chắc muốn báo mất tài liệu <strong>${title}</strong>?<br><small>${barcode}</small>`,
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
                    notes: `Khai báo báo mất: ${title}`
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', { text: `{{ __("Khai báo mất tài liệu thành công") }}` }));
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra") }}' }));
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }
});
</script>
@endpush
@endsection
