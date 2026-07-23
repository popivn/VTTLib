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
            <h1 class="text-xl font-bold tracking-tight text-foreground">{{ __('Quản Lý Sách Đang Mượn & Yêu Cầu') }}</h1>
            <p class="text-xs text-muted-foreground">{{ __('Theo dõi, phê duyệt yêu cầu mượn, gia hạn, triệu hồi và xử lý sách đang được mượn hoặc đã quá hạn') }}</p>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.circulation.loan-desk') }}" class="btn-compact-secondary w-full sm:w-auto flex justify-center items-center">
                <i data-lucide="arrow-right-left" class="w-4 h-4 mr-1"></i><span>{{ __('Bàn mượn trả') }}</span>
            </a>
            <a href="{{ route('admin.circulation.logs') }}" class="btn-compact-secondary w-full sm:w-auto flex justify-center items-center">
                <i data-lucide="history" class="w-4 h-4 mr-1"></i><span>{{ __('Nhật ký lưu thông') }}</span>
            </a>
        </div>
    </div>

    @php
        $pendingRequestsCount = $loanRequests->where('status', 'pending')->count();
        $readyRequestsCount = $loanRequests->where('status', 'ready')->count();
        $totalRequestsCount = $pendingRequestsCount + $readyRequestsCount;
    @endphp

    <!-- Tabs Navigation -->
    <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
        <div class="flex border-b border-border overflow-x-auto">
            <button type="button" onclick="switchTab('borrowed')" id="borrowedTab" 
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-indigo-500 text-indigo-600 bg-indigo-500/5 dark:text-indigo-400 border-r border-border shrink-0 flex items-center gap-1.5">
                <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                <span>{{ __('Sách đang mượn') }}</span>
                <span class="bg-indigo-500/10 text-indigo-500 dark:text-indigo-400 px-1.5 py-0.5 rounded-full text-[9px] font-bold">
                    {{ $activeLoans->count() }}
                </span>
            </button>
            <button type="button" onclick="switchTab('requests')" id="requestsTab" 
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground border-r border-border shrink-0 flex items-center gap-1.5">
                <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                <span>{{ __('Yêu cầu mượn') }}</span>
                @if($totalRequestsCount > 0)
                    <span class="bg-amber-500/10 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 rounded-full text-[9px] font-bold">
                        {{ $totalRequestsCount }}
                    </span>
                @endif
            </button>
            <button type="button" onclick="switchTab('overdue')" id="overdueTab"
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground shrink-0 flex items-center gap-1.5">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i>
                <span>{{ __('Sách quá hạn') }}</span>
                @if($overdueLoans->count() > 0)
                    <span class="bg-destructive/10 text-destructive px-1.5 py-0.5 rounded-full text-[9px] font-bold">
                        {{ $overdueLoans->count() }}
                    </span>
                @endif
            </button>
        </div>

        <div class="p-3">
            <!-- Currently Borrowed Tab Content -->
            <div id="borrowedContent" class="space-y-3">
                <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] font-semibold">
                                <tr>
                                    <th class="p-2.5 text-left w-28">{{ __('Mã tài liệu') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Mô tả tài liệu') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Người mượn') }}</th>
                                    <th class="p-2.5 text-right w-24">{{ __('Giá tiền') }}</th>
                                    <th class="p-2.5 text-center w-24">{{ __('Ngày mượn') }}</th>
                                    <th class="p-2.5 text-center w-24">{{ __('Hạn trả') }}</th>
                                    <th class="p-2.5 text-left w-28">{{ __('Người thực hiện') }}</th>
                                    <th class="p-2.5 text-left w-28">{{ __('Ghi chú') }}</th>
                                    <th class="p-2.5 text-center w-24">{{ __('Thao tác') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($activeLoans as $loan)
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $dueDate = $loan->due_date;
                                    $remainingDays = $dueDate ? ceil($now->diffInDays($dueDate, false)) : 0;
                                    $title = $loan->bookItem->bibliographicRecord->title ?? 'N/A';
                                    $author = $loan->bookItem->bibliographicRecord->author ?? '';
                                    $publisher = $loan->bookItem->bibliographicRecord->publisher ?? '';
                                    $year = $loan->bookItem->bibliographicRecord->publish_year ?? '';
                                    $fullDesc = implode(' / ', array_filter([$title, $author, $publisher, $year]));
                                @endphp
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="p-2.5 font-mono font-bold text-foreground text-xs">{{ $loan->bookItem->barcode }}</td>
                                    <td class="p-2.5 text-xs">
                                        <div class="font-medium text-foreground italic line-clamp-2" title="{{ $fullDesc }}">{{ $fullDesc }}</div>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1 text-[10px]">
                                            <div>Vị trí: <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ $loan->bookItem->storageLocation->name ?? $loan->bookItem->location ?? 'Kho' }}</span></div>
                                            <div>Loại: <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ $loan->bookItem->storage_type ?? 'Giáo trình' }}</span></div>
                                            <div>Gia hạn: <span class="text-destructive font-bold">{{ $loan->renewal_count ?? 0 }} (Lần)</span></div>
                                            <div>Còn hạn: <span class="{{ $remainingDays < 0 ? 'text-destructive font-bold' : 'text-emerald-600 font-bold' }}">{{ $remainingDays }} (Ngày)</span></div>
                                        </div>
                                    </td>
                                    <td class="p-2.5">
                                        <div class="font-semibold text-foreground text-xs">{{ $loan->patron->display_name ?? $loan->patron->user->name ?? 'N/A' }}</div>
                                        <div class="text-[10px] text-muted-foreground font-mono">{{ $loan->patron->patron_code }}</div>
                                    </td>
                                    <td class="p-2.5 text-right font-medium text-foreground">{{ number_format($loan->bookItem->price ?? 0) }}đ</td>
                                    <td class="p-2.5 text-center text-emerald-600 dark:text-emerald-400 font-semibold">
                                        {{ $loan->loan_date ? $loan->loan_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="p-2.5 text-center {{ $loan->isOverdue() ? 'text-destructive font-bold' : 'text-emerald-600 dark:text-emerald-400 font-semibold' }}">
                                        {{ $loan->due_date ? $loan->due_date->format('d/m/Y') : '-' }}
                                        @if($loan->isOverdue())
                                            <span class="ml-1 text-[8px] bg-destructive/15 text-destructive px-1 py-0.5 rounded-sm uppercase tracking-wide font-bold">Quá hạn</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-muted-foreground text-xs">{{ $loan->loanedByUser->name ?? $loan->loanedByUser->username ?? 'staff' }}</td>
                                    <td class="p-2.5 text-muted-foreground text-xs">{{ $loan->notes ?? 'Sách đang mượn' }}</td>
                                    <td class="p-2.5 text-center">
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
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-muted-foreground italic">
                                        {{ __('Không có sách nào đang được mượn.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Loan Requests Tab Content -->
            <div id="requestsContent" class="space-y-3 hidden">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-bold text-amber-500 flex items-center gap-1">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                        <span>{{ __('Yêu cầu mượn sách đang chờ phê duyệt hoặc sẵn sàng lấy') }}</span>
                    </h3>
                    <div class="flex gap-1.5 text-[10px]">
                        <span class="bg-amber-500/15 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded-full font-bold">
                            {{ $pendingRequestsCount }} {{ __('đang chờ') }}
                        </span>
                        <span class="bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded-full font-bold">
                            {{ $readyRequestsCount }} {{ __('sẵn sàng') }}
                        </span>
                    </div>
                </div>
                <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] font-semibold">
                                <tr>
                                    <th class="p-2.5 text-left">{{ __('Độc giả') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Tài liệu yêu cầu') }}</th>
                                    <th class="p-2.5 text-center w-36">{{ __('Ngày đăng ký') }}</th>
                                    <th class="p-2.5 text-center w-32">{{ __('Trạng thái') }}</th>
                                    <th class="p-2.5 text-center w-28">{{ __('Thao tác') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($loanRequests as $req)
                                @php
                                    $reqTitle = $req->bibliographicRecord->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
                                    $reqAuthor = $req->bibliographicRecord->fields->where('tag', '100')->first()?->subfields->where('code', 'a')->first()?->value ?? '';
                                    $reqFullDesc = $reqTitle . ($reqAuthor ? ' / ' . $reqAuthor : '');
                                @endphp
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="p-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-[10px] border border-primary/20">
                                                {{ substr($req->patron->display_name ?? $req->patron->user->name ?? '?', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-foreground">{{ $req->patron->display_name ?? $req->patron->user->name ?? 'N/A' }}</p>
                                                <p class="text-[10px] text-muted-foreground font-mono leading-none mt-0.5">{{ $req->patron->patron_code ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2.5">
                                        <div class="max-w-md">
                                            <p class="text-xs font-semibold text-foreground line-clamp-1" title="{{ $reqFullDesc }}">{{ $reqFullDesc }}</p>
                                            <p class="text-[9px] text-muted-foreground leading-none mt-0.5">Record ID: #{{ $req->bibliographic_record_id }}</p>
                                        </div>
                                    </td>
                                    <td class="p-2.5 text-center text-muted-foreground font-mono">
                                        {{ $req->reservation_date ? $req->reservation_date->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="p-2.5 text-center">
                                        @if($req->status == 'pending')
                                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[9px] font-bold uppercase rounded-sm border border-amber-500/20">Chờ duyệt</span>
                                        @elseif($req->status == 'ready')
                                            <div class="flex flex-col items-center">
                                                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[9px] font-bold uppercase rounded-sm border border-emerald-500/20 font-semibold">Sẵn sàng lấy</span>
                                                @if($req->expiry_date)
                                                    @php
                                                        $daysLeft = now()->diffInDays($req->expiry_date, false);
                                                        $isExpiringSoon = $daysLeft <= 1;
                                                    @endphp
                                                    <span class="text-[8px] mt-1 {{ $isExpiringSoon ? 'text-destructive font-bold' : 'text-muted-foreground' }} flex items-center gap-0.5">
                                                        <i data-lucide="clock" class="w-2.5 h-2.5"></i>
                                                        @if($daysLeft > 0)
                                                            {{ __('Còn :days ngày', ['days' => ceil($daysLeft)]) }}
                                                        @else
                                                            {{ __('Hết hạn hôm nay') }}
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-center">
                                        <div class="flex justify-center gap-1">
                                            @if($req->status == 'pending')
                                                <button onclick="approveLoanRequest({{ $req->id }}, '{{ addslashes($reqTitle) }}')" 
                                                        class="w-7 h-7 flex items-center justify-center bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500 hover:text-emerald-foreground rounded-sm transition-all border border-emerald-500/20" 
                                                        title="{{ __('Phê duyệt') }}">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <button onclick="openRejectLoanModal({{ $req->id }})" 
                                                        class="w-7 h-7 flex items-center justify-center bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground rounded-sm transition-all border border-destructive/20" 
                                                        title="{{ __('Từ chối') }}">
                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @elseif($req->status == 'ready')
                                                <button onclick="fulfillReservationRequest({{ $req->id }}, '{{ addslashes($reqTitle) }}')" 
                                                        class="w-7 h-7 flex items-center justify-center bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground rounded-sm transition-all border border-primary/20" 
                                                        title="{{ __('Cho mượn') }}">
                                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <button onclick="openRejectLoanModal({{ $req->id }})" 
                                                        class="w-7 h-7 flex items-center justify-center bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground rounded-sm transition-all border border-destructive/20" 
                                                        title="{{ __('Hủy yêu cầu') }}">
                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-muted-foreground italic">
                                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                                        <p>{{ __('Không có yêu cầu mượn nào.') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Overdue Books Tab Content -->
            <div id="overdueContent" class="space-y-3 hidden">
                <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] font-semibold">
                                <tr>
                                    <th class="p-2.5 text-left w-28">{{ __('Mã tài liệu') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Mô tả tài liệu') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Người mượn') }}</th>
                                    <th class="p-2.5 text-center w-24">{{ __('Ngày mượn') }}</th>
                                    <th class="p-2.5 text-center w-24">{{ __('Hạn trả') }}</th>
                                    <th class="p-2.5 text-center w-36">{{ __('Số ngày quá hạn') }}</th>
                                    <th class="p-2.5 text-center w-24">{{ __('Lượt gia hạn') }}</th>
                                    <th class="p-2.5 text-center w-24">{{ __('Thao tác') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($overdueLoans as $loan)
                                @php
                                    $title = $loan->bookItem->bibliographicRecord->title ?? 'N/A';
                                    $author = $loan->bookItem->bibliographicRecord->author ?? '';
                                    $publisher = $loan->bookItem->bibliographicRecord->publisher ?? '';
                                    $year = $loan->bookItem->bibliographicRecord->publish_year ?? '';
                                    $fullDesc = implode(' / ', array_filter([$title, $author, $publisher, $year]));
                                @endphp
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="p-2.5 font-mono font-bold text-foreground text-xs">{{ $loan->bookItem->barcode }}</td>
                                    <td class="p-2.5 text-xs">
                                        <div class="font-medium text-foreground italic line-clamp-1" title="{{ $fullDesc }}">{{ $fullDesc }}</div>
                                        <div class="flex items-center gap-2 mt-1 text-[10px] text-muted-foreground">
                                            <span>Vị trí: <strong class="text-foreground">{{ $loan->bookItem->storageLocation->name ?? $loan->bookItem->location ?? 'Kho' }}</strong></span>
                                        </div>
                                    </td>
                                    <td class="p-2.5">
                                        <div class="font-semibold text-foreground text-xs">{{ $loan->patron->display_name ?? $loan->patron->user->name ?? 'N/A' }}</div>
                                        <div class="text-[10px] text-muted-foreground font-mono">{{ $loan->patron->patron_code }}</div>
                                    </td>
                                    <td class="p-2.5 text-center text-muted-foreground font-mono">
                                        {{ $loan->loan_date ? $loan->loan_date->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="p-2.5 text-center text-destructive font-semibold">
                                        {{ $loan->due_date ? $loan->due_date->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="p-2.5 text-center">
                                        <span class="bg-destructive/10 text-destructive px-2 py-0.5 rounded-sm text-[10px] font-bold">
                                            Quá {{ $loan->getOverdueDays() }} ngày
                                        </span>
                                    </td>
                                    <td class="p-2.5 text-center text-muted-foreground font-mono">
                                        {{ $loan->renewal_count }}/{{ $loan->policy->max_renewals ?? '?' }}
                                    </td>
                                    <td class="p-2.5 text-center">
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
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-muted-foreground italic">
                                        {{ __('Không có sách nào bị quá hạn hiện tại.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

// Tab switcher
function switchTab(tabName) {
    // Update URL query parameter without reloading page
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);

    // Hide all contents
    document.getElementById('borrowedContent').classList.add('hidden');
    document.getElementById('requestsContent').classList.add('hidden');
    document.getElementById('overdueContent').classList.add('hidden');

    // Remove active styles
    const borrowedTab = document.getElementById('borrowedTab');
    const requestsTab = document.getElementById('requestsTab');
    const overdueTab = document.getElementById('overdueTab');
    
    borrowedTab.className = 'px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground border-r border-border shrink-0 flex items-center gap-1.5';
    requestsTab.className = 'px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground border-r border-border shrink-0 flex items-center gap-1.5';
    overdueTab.className = 'px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground shrink-0 flex items-center gap-1.5';

    // Set active styles & show content
    if (tabName === 'borrowed') {
        document.getElementById('borrowedContent').classList.remove('hidden');
        borrowedTab.className = 'px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-indigo-500 text-indigo-600 bg-indigo-500/5 dark:text-indigo-400 border-r border-border shrink-0 flex items-center gap-1.5';
    } else if (tabName === 'requests') {
        document.getElementById('requestsContent').classList.remove('hidden');
        requestsTab.className = 'px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-indigo-500 text-indigo-600 bg-indigo-500/5 dark:text-indigo-400 border-r border-border shrink-0 flex items-center gap-1.5';
    } else if (tabName === 'overdue') {
        document.getElementById('overdueContent').classList.remove('hidden');
        overdueTab.className = 'px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-amber-500 text-amber-600 bg-amber-500/5 dark:text-amber-400 shrink-0 flex items-center gap-1.5';
    }
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

// Reservation Approvals and Rejections
async function approveLoanRequest(id, title) {
    const result = await Swal.fire(getSwalConfig('{{ __("Phê duyệt yêu cầu?") }}', 'question', {
        html: `Bạn có chắc chắn muốn phê duyệt yêu cầu mượn tài liệu <strong>${title}</strong>?`,
        showCancelButton: true,
        confirmButtonText: '{{ __("Phê duyệt") }}',
        cancelButtonText: '{{ __("Hủy") }}'
    }));
    
    if (!result.isConfirmed) return;

    Swal.fire(getSwalConfig('{{ __("Đang xử lý...") }}', 'info', { allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } }));

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/topsecret/circulation/requests/${id}/approve`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    document.body.appendChild(form);
    form.submit();
}

function openRejectLoanModal(id) {
    Swal.fire(getSwalConfig('{{ __("Từ chối yêu cầu") }}', 'warning', {
        input: 'textarea',
        inputLabel: '{{ __("Lý do từ chối") }}',
        inputPlaceholder: '{{ __("Nhập lý do để thông báo cho độc giả...") }}',
        showCancelButton: true,
        confirmButtonText: '{{ __("Xác nhận từ chối") }}',
        cancelButtonText: '{{ __("Hủy bỏ") }}',
        confirmButtonColor: '#ef4444',
        showLoaderOnConfirm: true,
        preConfirm: (reason) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/topsecret/circulation/requests/${id}/reject`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
                
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = reason;
                form.appendChild(reasonInput);
            }

            document.body.appendChild(form);
            form.submit();
        },
        allowOutsideClick: () => !Swal.isLoading()
    }));
}

function fulfillReservationRequest(reservationId, title) {
    Swal.fire(getSwalConfig('{{ __("Xác nhận cho mượn") }}', 'question', {
        html: `Bạn có chắc chắn muốn cho bạn đọc mượn tài liệu <strong>${title}</strong> theo yêu cầu này?`,
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: '{{ __("Cho mượn") }}',
        cancelButtonText: '{{ __("Hủy") }}'
    })).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
                text: '{{ __("Đang thực hiện cho mượn sách...") }}',
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
                    setTimeout(() => { window.location.reload(); }, 1500);
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

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }
    
    // Auto-switch tab on page load based on query param
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab && ['borrowed', 'requests', 'overdue'].includes(tab)) {
        switchTab(tab);
    }
});
</script>
@endpush
@endsection
