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
                <span>{{ __('Mượn sách') }}</span>
            </button>
            <button type="button" onclick="switchTab('checkin')" id="checkinTab"
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground border-r border-border shrink-0 flex items-center gap-1.5">
                <i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
                <span>{{ __('Trả sách') }}</span>
            </button>
            <button type="button" onclick="switchTab('reading-room')" id="readingRoomTab"
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground border-r border-border shrink-0 flex items-center gap-1.5">
                <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                <span>{{ __('Mượn đọc') }}</span>
            </button>
            <button type="button" onclick="switchTab('hold')" id="holdTab"
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground border-r border-border shrink-0 flex items-center gap-1.5">
                <i data-lucide="bookmark" class="w-3.5 h-3.5"></i>
                <span>{{ __('Giữ lại') }}</span>
            </button>
            <button type="button" onclick="switchTab('borrowed')" id="borrowedTab"
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground border-r border-border shrink-0 flex items-center gap-1.5">
                <i data-lucide="book" class="w-3.5 h-3.5"></i>
                <span>{{ __('Sách đang mượn') }}</span>
            </button>
            <button type="button" onclick="switchTab('requests')" id="requestsTab"
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground border-r border-border shrink-0 flex items-center gap-1.5">
                <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                <span>{{ __('Yêu cầu mượn') }}</span>
                @php
                    $pendingCount = $loanRequests->where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[9px] font-bold bg-destructive text-destructive-foreground shadow-sm">
                        {{ $pendingCount }}
                    </span>
                @endif
            </button>
            <button type="button" onclick="switchTab('renew-overdue')" id="renewOverdueTab"
                    class="px-4 py-2.5 text-xs font-semibold transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground shrink-0 flex items-center gap-1.5">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i>
                <span>{{ __('Gia Hạn Sách Quá Hạn') }}</span>
                @if($overdueLoans->count() > 0)
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[9px] font-bold bg-amber-500 text-white shadow-sm">
                        {{ $overdueLoans->count() }}
                    </span>
                @endif
            </button>
        </div>

        <!-- Tab Content -->
        <div class="p-3">
            <!-- Checkout Tab -->
            <div id="checkoutContent" class="space-y-3">
                <div class="flex justify-end">
                    <button type="button" onclick="window.location.reload()" class="text-[10px] text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i> {{ __('Tải lại trang') }}
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-bold mb-3 text-emerald-500 flex items-center gap-1">
                            <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                            <span>{{ __('Cho mượn sách (Checkout)') }}</span>
                        </h3>
                        <form action="{{ route('admin.circulation.checkout') }}" method="POST" class="space-y-3 bg-card border border-border p-3 rounded-md shadow-sm">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Mã bạn đọc') }} *</label>
                                <div class="relative">
                                    <input type="text" id="patron_code" name="patron_code" required class="input-field pr-9" 
                                        placeholder="{{ __('Quét hoặc nhập mã bạn đọc...') }}" autofocus>
                                    <button type="button" onclick="searchPatronByCode(document.getElementById('patron_code').value)" 
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Mã vạch tài liệu') }} *</label>
                                <div class="relative">
                                    <input type="text" id="book_barcode" name="barcode" required class="input-field pr-9" 
                                        placeholder="{{ __('Quét hoặc nhập mã vạch tài liệu...') }}">
                                    <button type="button" onclick="searchBookByBarcode(document.getElementById('book_barcode').value)" 
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="w-full btn-compact-primary h-10 text-sm">
                                {{ __('Cho mượn sách') }}
                            </button>
                        </form>
                    </div>

                    <div class="space-y-3">
                        @include('admin.circulation.components.patron-info', ['id' => 'patronInfo'])
                        @include('admin.circulation.components.book-info', ['id' => 'bookSearchResult'])
                    </div>
                </div>
            </div>

            <!-- Checkin Tab -->
            <div id="checkinContent" class="space-y-3 hidden">
                <div class="flex justify-end">
                    <button type="button" onclick="loadPatronActiveLoans()" class="text-[10px] text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i> {{ __('Tải lại danh sách') }}
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-bold mb-3 text-blue-500 flex items-center gap-1">
                            <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                            <span>{{ __('Trả sách (Checkin)') }}</span>
                        </h3>
                        <div class="bg-card border border-border p-3 rounded-md shadow-sm space-y-3">
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Mã bạn đọc') }} *</label>
                                <div class="relative">
                                    <input type="text" id="checkin_patron_code" name="patron_code" required class="input-field pr-9" 
                                        placeholder="{{ __('Quét hoặc nhập mã bạn đọc...') }}" onchange="loadPatronActiveLoans()">
                                    <button type="button" onclick="searchPatronByCode(document.getElementById('checkin_patron_code').value)" 
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        @include('admin.circulation.components.patron-info', ['id' => 'checkinPatronInfo'])
                        
                        <div class="bg-card border border-border p-3 rounded-md shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-wider mb-2 text-foreground">{{ __('Sách đang mượn') }}</h3>
                            <div id="patronActiveLoans" class="min-h-[150px] flex flex-col justify-center">
                                <div class="text-center text-muted-foreground py-6">
                                    <i data-lucide="book" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                                    <p class="text-xs">{{ __('Nhập mã bạn đọc để hiển thị danh sách sách đang mượn') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reading Room Tab -->
            <div id="readingRoomContent" class="space-y-3 hidden">
                <div class="flex justify-end">
                    <button type="button" onclick="loadAllReadingRoomTransactions(); loadReadingRoomTransactions();" class="text-[10px] text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i> {{ __('Tải lại danh sách') }}
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-bold mb-3 text-purple-500 flex items-center gap-1">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                            <span>{{ __('Mượn đọc tại chỗ') }}</span>
                        </h3>
                        <form id="readingRoomForm" class="space-y-3 bg-card border border-border p-3 rounded-md shadow-sm">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Patron_Code') }} *</label>
                                <div class="relative">
                                    <input type="text" id="reading_patron_code" name="patron_code" required class="input-field pr-9" 
                                        placeholder="{{ __('Scan_or_enter_patron_code') }}" onchange="loadReadingRoomTransactions()">
                                    <button type="button" onclick="searchPatronByCode(document.getElementById('reading_patron_code').value)" 
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Book_Barcode') }} *</label>
                                <div class="relative">
                                    <input type="text" id="reading_book_barcode" name="barcode" required class="input-field pr-9" 
                                        placeholder="{{ __('Scan_or_enter_barcode') }}">
                                    <button type="button" onclick="searchBookByBarcode(document.getElementById('reading_book_barcode').value)" 
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Ghi chú') }}</label>
                                <textarea id="reading_notes" name="notes" rows="2" class="input-field !h-auto py-1.5" 
                                    placeholder="{{ __('Nhập ghi chú (không bắt buộc)') }}"></textarea>
                            </div>
                            <button type="button" onclick="processReadingRoomCheckout()" 
                                    class="w-full btn-compact-primary h-10 text-sm">
                                <i data-lucide="book-open" class="w-4 h-4 mr-1"></i>{{ __('Mượn đọc tại chỗ') }}
                            </button>
                        </form>
                    </div>

                    <div class="space-y-3">
                        @include('admin.circulation.components.patron-info', ['id' => 'readingPatronInfo'])
                        @include('admin.circulation.components.book-info', ['id' => 'readingBookInfo'])
                        
                        <div class="bg-card border border-border p-3 rounded-md shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-wider mb-2 text-foreground">{{ __('Tài liệu đang mượn đọc') }}</h3>
                            <div id="readingRoomTransactions" class="min-h-[150px] flex flex-col justify-center">
                                <div class="text-center text-muted-foreground py-6">
                                    <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                                    <p class="text-xs">{{ __('Nhập mã bạn đọc để xem tài liệu đang mượn đọc') }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" onclick="processReadingRoomCheckin()" 
                                        class="w-full px-3 py-1.5 bg-destructive hover:bg-destructive/90 text-destructive-foreground text-xs font-bold uppercase rounded-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1" 
                                        id="returnReadingRoomBtn" disabled>
                                    <i data-lucide="undo-2" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('Trả các tài liệu đã chọn') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Active Reading Room Transactions (Staff View) -->
                <div class="bg-card border border-border p-3 rounded-md shadow-sm">
                    <h3 class="text-sm font-bold mb-3 text-purple-500 flex items-center gap-1">
                        <i data-lucide="list" class="w-4 h-4"></i>
                        <span>{{ __('Tất cả tài liệu đang mượn đọc') }}</span>
                    </h3>
                    <div id="allReadingRoomTransactions" class="overflow-x-auto">
                        <div class="text-center text-muted-foreground py-4">
                            <p class="text-xs">{{ __('Đang tải danh sách...') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hold/Reserve Tab -->
            <div id="holdContent" class="space-y-3 hidden">
                <div class="flex justify-end">
                    <button type="button" onclick="loadAllReservations(); loadPatronReservations();" class="text-[10px] text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i> {{ __('Tải lại danh sách') }}
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-bold mb-3 text-orange-500 flex items-center gap-1">
                            <i data-lucide="bookmark" class="w-4 h-4"></i>
                            <span>{{ __('Giữ lại sách (Hold/Reserve)') }}</span>
                        </h3>
                        <form id="holdForm" class="space-y-3 bg-card border border-border p-3 rounded-md shadow-sm">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Patron_Code') }} *</label>
                                <div class="relative">
                                    <input type="text" id="hold_patron_code" name="patron_code" required class="input-field pr-9" 
                                        placeholder="{{ __('Scan_or_enter_patron_code') }}" onchange="loadPatronReservations()">
                                    <button type="button" onclick="searchPatronByCode(document.getElementById('hold_patron_code').value)" 
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Book_Barcode') }} *</label>
                                <div class="relative">
                                    <input type="text" id="hold_book_barcode" name="barcode" required class="input-field pr-9" 
                                        placeholder="{{ __('Scan_or_enter_barcode') }}">
                                    <button type="button" onclick="searchBookByBarcode(document.getElementById('hold_book_barcode').value)" 
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Ghi chú') }}</label>
                                <textarea id="hold_notes" name="notes" rows="2" class="input-field !h-auto py-1.5" 
                                    placeholder="{{ __('Nhập ghi chú (không bắt buộc)') }}"></textarea>
                            </div>
                            <button type="button" onclick="processPlaceHold()" 
                                    class="w-full btn-compact-primary h-10 text-sm">
                                <i data-lucide="bookmark" class="w-4 h-4 mr-1"></i>{{ __('Giữ lại sách') }}
                            </button>
                        </form>
                    </div>

                    <div class="space-y-3">
                        @include('admin.circulation.components.patron-info', ['id' => 'holdPatronInfo'])
                        
                        <div class="bg-card border border-border p-3 rounded-md shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-wider mb-2 text-foreground">{{ __('Reservations đang hoạt động') }}</h3>
                            <div id="patronReservations" class="min-h-[150px] flex flex-col justify-center">
                                <div class="text-center text-muted-foreground py-6">
                                    <i data-lucide="bookmark" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                                    <p class="text-xs">{{ __("Nhập mã bạn đọc để xem reservations") }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Active Reservations (Staff View) -->
                <div class="bg-card border border-border p-3 rounded-md shadow-sm">
                    <h3 class="text-sm font-bold mb-3 text-orange-500 flex items-center gap-1">
                        <i data-lucide="list" class="w-4 h-4"></i>
                        <span>{{ __('Tất cả Reservations đang hoạt động') }}</span>
                    </h3>
                    <div id="allReservations" class="overflow-x-auto">
                        <div class="text-center text-muted-foreground py-4">
                            <p class="text-xs">{{ __("Đang tải danh sách...") }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Currently Borrowed Tab -->
            <div id="borrowedContent" class="space-y-3 hidden">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-bold text-indigo-500 flex items-center gap-1">
                        <i data-lucide="book" class="w-4 h-4"></i>
                        <span>{{ __('Danh sách sách đang được mượn') }}</span>
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.location.href='{{ route('admin.circulation.loan-desk') }}?tab=borrowed'" class="text-[10px] text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
                            <i data-lucide="rotate-cw" class="w-3 h-3"></i> {{ __('Tải lại') }}
                        </button>
                        <span class="bg-indigo-500/10 text-indigo-500 dark:text-indigo-400 px-2 py-0.5 rounded-full text-[10px] font-bold">
                            {{ $activeLoans->count() }} {{ __('cuốn sách') }}
                        </span>
                    </div>
                </div>
                <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] font-semibold">
                                <tr>
                                    <th class="p-2.5 text-center w-8"><input type="checkbox" checked disabled class="rounded"></th>
                                    <th class="p-2.5 text-left w-28">{{ __('Mã tài liệu') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Mô tả') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Người mượn') }}</th>
                                    <th class="p-2.5 text-right w-24">{{ __('Giá tiền') }}</th>
                                    <th class="p-2.5 text-center w-24">{{ __('Ngày mượn') }}</th>
                                    <th class="p-2.5 text-center w-24">{{ __('Hạn trả') }}</th>
                                    <th class="p-2.5 text-left w-28">{{ __('Người thực hiện') }}</th>
                                    <th class="p-2.5 text-left w-28">{{ __('Ghi chú') }}</th>
                                    <th class="p-2.5 text-center w-20">{{ __('Thao tác') }}</th>
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
                                    <td class="p-2.5 text-center"><input type="checkbox" checked disabled class="rounded text-primary"></td>
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
                                            <span class="ml-1 text-[8px] bg-destructive/15 text-destructive px-1 py-0.5 rounded-sm uppercase tracking-wide">Quá hạn</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-muted-foreground text-xs">{{ $loan->loanedByUser->name ?? $loan->loanedByUser->username ?? 'staff' }}</td>
                                    <td class="p-2.5 text-muted-foreground text-xs">{{ $loan->notes ?? 'Sách đang mượn' }}</td>
                                    <td class="p-2.5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button onclick="recallLoanTransaction('{{ $loan->bookItem->barcode }}', '{{ addslashes($title) }}')" 
                                                    class="p-1 text-amber-500 hover:text-amber-600 hover:bg-amber-500/10 rounded transition-colors" 
                                                    title="{{ __("Triệu hồi") }}">
                                                <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <button onclick="declareLostLoanTransaction('{{ $loan->bookItem->barcode }}', '{{ addslashes($title) }}')" 
                                                    class="p-1 text-destructive hover:text-destructive/90 hover:bg-destructive/10 rounded transition-colors" 
                                                    title="{{ __("Khai báo mất") }}">
                                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-muted-foreground italic">
                                        {{ __('Không có sách nào đang được mượn.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Loan Requests Tab -->
            <div id="requestsContent" class="space-y-3 hidden">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-bold text-amber-500 flex items-center gap-1">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                        <span>{{ __('Yêu cầu mượn sách đang chờ') }}</span>
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.location.href='{{ route('admin.circulation.loan-desk') }}?tab=requests'" class="text-[10px] text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
                            <i data-lucide="rotate-cw" class="w-3 h-3"></i> {{ __('Tải lại') }}
                        </button>
                        <div class="flex gap-1.5">
                            <span class="bg-amber-500/15 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded-full text-[10px] font-bold">
                                {{ $loanRequests->where('status', 'pending')->count() }} {{ __('đang chờ') }}
                            </span>
                            <span class="bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded-full text-[10px] font-bold">
                                {{ $loanRequests->where('status', 'ready')->count() }} {{ __('sẵn sàng') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] font-semibold">
                                <tr>
                                    <th class="p-2 text-left">{{ __('Độc giả') }}</th>
                                    <th class="p-2 text-left">{{ __('Tài liệu yêu cầu') }}</th>
                                    <th class="p-2 text-left">{{ __('Ngày đăng ký') }}</th>
                                    <th class="p-2 text-center">{{ __('Trạng thái') }}</th>
                                    <th class="p-2 text-right">{{ __('Thao tác') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($loanRequests as $req)
                                @php
                                    $reqTitle = $req->bibliographicRecord->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
                                @endphp
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="p-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-[10px] border border-primary/20">
                                                {{ substr($req->patron->display_name ?? $req->patron->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-foreground">{{ $req->patron->display_name ?? $req->patron->user->name }}</p>
                                                <p class="text-[10px] text-muted-foreground font-mono leading-none">{{ $req->patron->patron_code }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2">
                                        <div class="max-w-xs">
                                            <p class="text-xs font-semibold text-foreground truncate" title="{{ $reqTitle }}">{{ $reqTitle }}</p>
                                            <p class="text-[9px] text-muted-foreground leading-none mt-0.5">Record ID: #{{ $req->bibliographic_record_id }}</p>
                                        </div>
                                    </td>
                                    <td class="p-2 text-[11px] text-muted-foreground">
                                        {{ $req->reservation_date->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="p-2 text-center">
                                        @if($req->status == 'pending')
                                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 text-[9px] font-bold uppercase rounded-sm border border-amber-500/20">Chờ duyệt</span>
                                        @elseif($req->status == 'ready')
                                            <div class="flex flex-col items-center">
                                                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 text-[9px] font-bold uppercase rounded-sm border border-emerald-500/20">Chờ lấy</span>
                                                @if($req->expiry_date)
                                                    @php
                                                        $daysLeft = now()->diffInDays($req->expiry_date, false);
                                                        $isExpiringSoon = $daysLeft <= 1;
                                                    @endphp
                                                    <span class="text-[8px] mt-0.5 {{ $isExpiringSoon ? 'text-destructive font-bold' : 'text-muted-foreground' }} flex items-center gap-0.5">
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
                                    <td class="p-2 text-right">
                                        <div class="flex justify-end gap-1">
                                            @if($req->status == 'pending')
                                                <button onclick="approveLoanRequest({{ $req->id }})" class="w-7 h-7 flex items-center justify-center bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500 hover:text-emerald-foreground rounded-sm transition-all border border-emerald-500/20" title="{{ __('Phê duyệt') }}">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <button onclick="openRejectLoanModal({{ $req->id }})" class="w-7 h-7 flex items-center justify-center bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground rounded-sm transition-all border border-destructive/20" title="{{ __('Từ chối') }}">
                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @elseif($req->status == 'ready')
                                                <button onclick="fulfillReservation({{ $req->id }})" class="w-7 h-7 flex items-center justify-center bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground rounded-sm transition-all border border-primary/20" title="{{ __('Cho mượn') }}">
                                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <button onclick="openRejectLoanModal({{ $req->id }})" class="w-7 h-7 flex items-center justify-center bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground rounded-sm transition-all border border-destructive/20" title="{{ __('Hủy yêu cầu') }}">
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

            <!-- Renew Overdue Tab Content -->
            <div id="renewOverdueContent" class="space-y-3 hidden">
                <div class="flex justify-between items-center bg-amber-500/10 border border-amber-500/20 p-3 rounded-md">
                    <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider">{{ __('Gia Hạn Sách Quá Hạn') }}</h3>
                            <p class="text-[11px] text-muted-foreground">{{ __('Danh sách các tài liệu mượn đã quá hạn cần được xử lý gia hạn hoặc thu phạt.') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.location.reload()" class="btn-compact-secondary text-xs">
                        <i data-lucide="rotate-cw" class="w-3.5 h-3.5 mr-1"></i>{{ __('Tải lại trang') }}
                    </button>
                </div>

                @if($overdueLoans->count() > 0)
                <div class="bg-card rounded-md border border-border overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] font-semibold">
                                <tr>
                                    <th class="p-2.5 text-left">{{ __('Bạn đọc') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Tài liệu') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Ngày mượn') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Hạn trả') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Số ngày quá hạn') }}</th>
                                    <th class="p-2.5 text-left">{{ __('Lượt gia hạn') }}</th>
                                    <th class="p-2.5 text-center">{{ __('Thao tác gia hạn') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($overdueLoans as $loan)
                                <tr class="hover:bg-muted/30">
                                    <td class="p-2.5 font-medium">
                                        <div class="font-bold text-foreground">{{ $loan->patron->display_name ?? $loan->patron->user->name ?? 'N/A' }}</div>
                                        <div class="text-[10px] text-muted-foreground font-mono">{{ $loan->patron->patron_code }}</div>
                                    </td>
                                    <td class="p-2.5">
                                        <div class="font-semibold text-foreground line-clamp-1">{{ $loan->bookItem->bibliographicRecord->title ?? 'N/A' }}</div>
                                        <div class="text-[10px] text-muted-foreground font-mono">{{ $loan->bookItem->barcode }}</div>
                                    </td>
                                    <td class="p-2.5 text-muted-foreground">
                                        {{ $loan->loan_date ? $loan->loan_date->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="p-2.5 text-destructive font-semibold">
                                        {{ $loan->due_date ? $loan->due_date->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="p-2.5">
                                        <span class="bg-destructive/10 text-destructive px-2 py-0.5 rounded-sm text-[10px] font-bold">
                                            Quá {{ $loan->getOverdueDays() }} ngày
                                        </span>
                                    </td>
                                    <td class="p-2.5 text-muted-foreground">
                                        <span class="font-mono text-xs">{{ $loan->renewal_count }}/{{ $loan->policy->max_renewals ?? '?' }}</span>
                                    </td>
                                    <td class="p-2.5 text-center">
                                        @if($loan->canRenew())
                                        <form action="{{ route('admin.circulation.renew', $loan) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn-compact-primary py-1 px-3 text-xs bg-amber-500 hover:bg-amber-600 border-amber-500 text-white">
                                                <i data-lucide="rotate-cw" class="w-3.5 h-3.5 mr-1 inline"></i>{{ __('Gia hạn ngay') }}
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-[10px] text-muted-foreground bg-muted px-2 py-1 rounded">
                                            {{ __('Hết lượt gia hạn') }}
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="text-center text-muted-foreground py-10 bg-card border border-border rounded-md">
                    <i data-lucide="check-circle-2" class="w-10 h-10 mx-auto mb-2 text-emerald-500/50"></i>
                    <p class="text-xs font-semibold">{{ __('Không có tài liệu nào bị quá hạn hiện tại.') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Overdue Loans Alert -->
    @if($overdueLoans->count() > 0)
    <div class="bg-card rounded-md border border-destructive shadow-sm overflow-hidden border-l-4">
        <div class="p-3 bg-destructive/10 border-b border-destructive/20">
            <h3 class="text-sm font-bold text-destructive flex items-center gap-1.5">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>{{ __('Sách quá hạn') }} ({{ $overdueLoans->count() }})</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] font-semibold">
                    <tr>
                        <th class="p-2 text-left">{{ __('Bạn đọc') }}</th>
                        <th class="p-2 text-left">{{ __('Tài liệu') }}</th>
                        <th class="p-2 text-left">{{ __('Hạn trả') }}</th>
                        <th class="p-2 text-left">{{ __('Số ngày quá hạn') }}</th>
                        <th class="p-2 text-left">{{ __('Thao tác') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($overdueLoans as $loan)
                    <tr class="hover:bg-muted/30">
                        <td class="p-2">
                            <div class="font-medium text-foreground">{{ $loan->patron->display_name ?? $loan->patron->user->name }}</div>
                            <div class="text-[10px] text-muted-foreground font-mono leading-none mt-0.5">{{ $loan->patron->patron_code }}</div>
                        </td>
                        <td class="p-2">
                            <div class="font-medium text-foreground max-w-xs truncate" title="{{ $loan->bookItem->bibliographicRecord->title ?? 'N/A' }}">{{ $loan->bookItem->bibliographicRecord->title ?? 'N/A' }}</div>
                            <div class="text-[10px] text-muted-foreground font-mono leading-none mt-0.5">{{ $loan->bookItem->barcode }}</div>
                        </td>
                        <td class="p-2 text-destructive font-medium">{{ $loan->due_date->format('d/m/Y') }}</td>
                        <td class="p-2">
                            <span class="bg-destructive/10 text-destructive px-2 py-0.5 rounded-sm text-[10px] font-bold">
                                {{ $loan->getOverdueDays() }} {{ __('days') }}
                            </span>
                        </td>
                        <td class="p-2">
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.circulation.renew', $loan) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-primary hover:underline" 
                                        {{ !$loan->canRenew() ? 'disabled' : '' }}>
                                        {{ __('Renew') }}
                                    </button>
                                </form>
                                <button onclick="recallLoanTransaction('{{ $loan->bookItem->barcode }}', '{{ addslashes($loan->bookItem->bibliographicRecord->title ?? '') }}')" 
                                        class="text-amber-500 hover:text-amber-600" 
                                        title="{{ __("Triệu hồi") }}">
                                    <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                                </button>
                                <button onclick="declareLostLoanTransaction('{{ $loan->bookItem->barcode }}', '{{ addslashes($loan->bookItem->bibliographicRecord->title ?? '') }}')" 
                                        class="text-destructive hover:text-destructive/90" 
                                        title="{{ __("Khai báo mất") }}">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Transactions -->
    <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
        <div class="p-3 border-b border-border bg-muted/20">
            <h3 class="text-sm font-bold text-foreground">{{ __("Hành động gần đây") }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] font-semibold">
                    <tr>
                        <th class="p-2 text-left">{{ __('Date') }}</th>
                        <th class="p-2 text-left">{{ __('Patron') }}</th>
                        <th class="p-2 text-left">{{ __('Book') }}</th>
                        <th class="p-2 text-left">{{ __('Due_Date') }}</th>
                        <th class="p-2 text-left">{{ __('Status') }}</th>
                        <th class="p-2 text-left">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($recentLoans as $loan)
                    <tr class="hover:bg-muted/30">
                        <td class="p-2 text-[11px] text-muted-foreground">{{ $loan->loan_date->format('d/m/Y H:i') }}</td>
                        <td class="p-2">
                            <div class="font-medium text-foreground">{{ $loan->patron->display_name ?? $loan->patron->user->name }}</div>
                            <div class="text-[10px] text-muted-foreground font-mono leading-none mt-0.5">{{ $loan->patron->patron_code }}</div>
                        </td>
                        <td class="p-2">
                            <div class="font-medium text-foreground max-w-xs truncate" title="{{ $loan->bookItem->bibliographicRecord->title ?? 'N/A' }}">{{ $loan->bookItem->bibliographicRecord->title ?? 'N/A' }}</div>
                            <div class="text-[10px] text-muted-foreground font-mono leading-none mt-0.5">{{ $loan->bookItem->barcode }}</div>
                        </td>
                        <td class="p-2 text-[11px] text-muted-foreground {{ $loan->isOverdue() ? 'text-destructive font-bold' : '' }}">
                            {{ $loan->due_date->format('d/m/Y') }}
                        </td>
                        <td class="p-2">
                            @php
                                $statusClass = match($loan->status) {
                                    'borrowed' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
                                    'returned' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
                                    'overdue' => 'bg-destructive/10 text-destructive border border-destructive/20',
                                    'lost' => 'bg-muted text-muted-foreground border border-border',
                                    default => 'bg-muted text-muted-foreground border border-border'
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-sm text-[9px] font-bold uppercase {{ $statusClass }}">
                                {{ __(ucfirst($loan->status)) }}
                            </span>
                        </td>
                        <td class="p-2">
                            @if($loan->status === 'borrowed')
                                <div class="flex items-center gap-2">
                                    @if($loan->canRenew())
                                    <form action="{{ route('admin.circulation.renew', $loan) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-primary hover:underline">
                                            {{ __('Renew') }}
                                        </button>
                                    </form>
                                    @endif
                                    <span class="text-[10px] text-muted-foreground">({{ $loan->renewal_count }}/{{ $loan->policy->max_renewals ?? '?' }})</span>
                                    <button onclick="recallLoanTransaction('{{ $loan->bookItem->barcode }}', '{{ addslashes($loan->bookItem->bibliographicRecord->title ?? '') }}')" 
                                            class="text-amber-500 hover:text-amber-600" 
                                            title="{{ __("Triệu hồi") }}">
                                        <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button onclick="declareLostLoanTransaction('{{ $loan->bookItem->barcode }}', '{{ addslashes($loan->bookItem->bibliographicRecord->title ?? '') }}')" 
                                            class="text-destructive hover:text-destructive/90" 
                                            title="{{ __("Khai báo mất") }}">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-muted-foreground">{{ __('No_recent_transactions') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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

// Tab switching function
function switchTab(tabName) {
    // Update URL query parameter without reloading page
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);

    // Hide all tab contents
    const contents = ['checkoutContent', 'checkinContent', 'readingRoomContent', 'holdContent', 'borrowedContent', 'requestsContent', 'renewOverdueContent'];
    contents.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    });
    
    // Remove active/inactive classes from all tabs
    const allTabs = ['checkout', 'checkin', 'readingRoom', 'hold', 'borrowed', 'requests', 'renewOverdue'];
    allTabs.forEach(tab => {
        const el = document.getElementById(tab + 'Tab');
        if (el) {
            el.classList.remove(
                'border-emerald-500', 'text-emerald-600', 'bg-emerald-500/5', 'dark:text-emerald-400',
                'border-blue-500', 'text-blue-600', 'bg-blue-500/5', 'dark:text-blue-400',
                'border-purple-500', 'text-purple-600', 'bg-purple-500/5', 'dark:text-purple-400',
                'border-orange-500', 'text-orange-600', 'bg-orange-500/5', 'dark:text-orange-400',
                'border-indigo-500', 'text-indigo-600', 'bg-indigo-500/5', 'dark:text-indigo-400',
                'border-amber-500', 'text-amber-600', 'bg-amber-500/5', 'dark:text-amber-400',
                'border-transparent', 'text-muted-foreground'
            );
            el.classList.add('border-transparent', 'text-muted-foreground');
        }
    });
    
    // Set active tab styles
    const tabKey = tabName === 'reading-room' ? 'readingRoom' : (tabName === 'renew-overdue' ? 'renewOverdue' : tabName);
    const activeEl = document.getElementById(tabKey + 'Tab');
    if (activeEl) {
        activeEl.classList.remove('border-transparent', 'text-muted-foreground');
        if (tabName === 'checkout') {
            activeEl.classList.add('border-emerald-500', 'text-emerald-600', 'bg-emerald-500/5', 'dark:text-emerald-400');
        } else if (tabName === 'checkin') {
            activeEl.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-500/5', 'dark:text-blue-400');
        } else if (tabName === 'reading-room') {
            activeEl.classList.add('border-purple-500', 'text-purple-600', 'bg-purple-500/5', 'dark:text-purple-400');
            loadAllReadingRoomTransactions();
        } else if (tabName === 'hold') {
            activeEl.classList.add('border-orange-500', 'text-orange-600', 'bg-orange-500/5', 'dark:text-orange-400');
            loadAllReservations();
        } else if (tabName === 'borrowed' || tabName === 'requests') {
            activeEl.classList.add('border-indigo-500', 'text-indigo-600', 'bg-indigo-500/5', 'dark:text-indigo-400');
        } else if (tabName === 'renew-overdue') {
            activeEl.classList.add('border-amber-500', 'text-amber-600', 'bg-amber-500/5', 'dark:text-amber-400');
        }
    }
    
    // Show selected content
    const contentKey = tabName === 'reading-room' ? 'readingRoomContent' : (tabName === 'hold' ? 'holdContent' : (tabName === 'renew-overdue' ? 'renewOverdueContent' : tabName + 'Content'));
    const contentEl = document.getElementById(contentKey);
    if (contentEl) {
        contentEl.classList.remove('hidden');
    }
}

// Auto-switch tab on page load based on query param
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab && ['checkout', 'checkin', 'reading-room', 'hold', 'borrowed', 'requests', 'renew-overdue'].includes(tab)) {
        switchTab(tab);
    }
    if (window.lucide) window.lucide.createIcons();
});

// Logic for Checkin (Return) by Patron Code
async function loadPatronActiveLoans() {
    const patronCode = document.getElementById('checkin_patron_code').value.trim();
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
                    
                    loansHtml += `
                        <div class="p-3 bg-muted/20 border border-border rounded-md flex justify-between items-center group hover:border-primary transition-all">
                            <div class="flex-1 min-w-0 pr-2">
                                <p class="text-xs font-bold text-foreground truncate">${loan.book_item.bibliographic_record.title}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[9px] font-mono text-muted-foreground uppercase">${loan.book_item.barcode}</span>
                                    <span class="text-[9px] ${statusColor} uppercase">Hạn trả: ${dueDate.toLocaleDateString('vi-VN')}</span>
                                    ${isOverdue ? '<span class="text-[8px] bg-destructive/10 text-destructive px-1.5 py-0.5 rounded-sm font-bold uppercase">Quá hạn</span>' : ''}
                                </div>
                            </div>
                            <button onclick="processReturn(${loan.id}, ${isOverdue})" class="btn-compact-primary py-1 px-3 text-[10px]">
                                Trả sách
                            </button>
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

// Patron search with 0.5s delay
const patronCodeInput = document.getElementById('patron_code');
if (patronCodeInput) {
    patronCodeInput.addEventListener('input', function() {
        clearTimeout(patronSearchTimeout);
        const value = this.value.trim();
        
        if (value.length >= 2) {
            patronSearchTimeout = setTimeout(() => {
                searchPatronByCode(value);
            }, 500);
        } else {
            // Clear patron info when input is empty
            const infoDiv = document.getElementById('patronInfo');
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

// Book search with 0.5s delay
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
            // Clear book info when input is empty
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

// AJAX search functions
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
    // Check which tab is active to determine which info div to update
    let infoDiv;
    if (!document.getElementById('checkinContent').classList.contains('hidden')) {
        infoDiv = document.getElementById('checkinPatronInfo');
    } else if (!document.getElementById('readingRoomContent').classList.contains('hidden')) {
        infoDiv = document.getElementById('readingPatronInfo');
    } else if (!document.getElementById('holdContent').classList.contains('hidden')) {
        infoDiv = document.getElementById('holdPatronInfo');
    } else {
        infoDiv = document.getElementById('patronInfo');
    }
    
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
        
        // Load current loans in dynamic table
        const isLoanTabActive = document.getElementById('readingRoomContent').classList.contains('hidden');
        if (loans > 0 && isLoanTabActive && document.getElementById('currentLoansTableBody')) {
            setTimeout(() => {
                loadCurrentLoans(patron.data.id);
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
    let resultDiv;
    if (!document.getElementById('readingRoomContent').classList.contains('hidden')) {
        resultDiv = document.getElementById('readingBookInfo');
    } else if (!document.getElementById('holdContent').classList.contains('hidden')) {
        resultDiv = document.getElementById('holdBookInfo');
    } else {
        resultDiv = document.getElementById('bookSearchResult');
    }
    
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
    let resultDiv;
    if (!document.getElementById('checkinContent').classList.contains('hidden')) {
        resultDiv = document.getElementById('checkinPatronInfo');
    } else if (!document.getElementById('readingRoomContent').classList.contains('hidden')) {
        resultDiv = document.getElementById('readingPatronInfo');
    } else if (!document.getElementById('holdContent').classList.contains('hidden')) {
        resultDiv = document.getElementById('holdPatronInfo');
    } else {
        resultDiv = document.getElementById('patronInfo');
    }
    if (resultDiv) {
        updateHTMLAndRefreshIcons(resultDiv, `
            <div class="p-3 rounded-md border bg-destructive/15 border-destructive/20">
                <p class="text-xs text-destructive font-semibold">{{ __("Search_Error") }}</p>
            </div>
        `);
    }
}

function displayBookError() {
    let resultDiv;
    if (!document.getElementById('readingRoomContent').classList.contains('hidden')) {
        resultDiv = document.getElementById('readingBookInfo');
    } else if (!document.getElementById('holdContent').classList.contains('hidden')) {
        resultDiv = document.getElementById('holdBookInfo');
    } else {
        resultDiv = document.getElementById('bookSearchResult');
    }
    if (resultDiv) {
        updateHTMLAndRefreshIcons(resultDiv, `
            <div class="p-3 rounded-md border bg-destructive/15 border-destructive/20">
                <p class="text-xs text-destructive font-semibold">{{ __("Search_Error") }}</p>
            </div>
        `);
    }
}

// Load current loans for patron
function loadCurrentLoans(patronId) {
    const tbody = document.getElementById('currentLoansTableBody');
    if (!tbody) return;
    
    const patronLoans = loanTransactionsData ? loanTransactionsData.filter(loan => 
        loan.patron_detail_id === patronId && loan.status === 'borrowed'
    ) : [];
    
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
        const loanedBy = loan.loaned_by_user?.name || loan.loaned_by_user?.username || 'staff';
        const notes = loan.notes || 'Sách đang mượn';

        let descParts = [title];
        if (author) descParts.push(author);
        if (publisher) descParts.push(publisher);
        if (year) descParts.push(year);
        const fullDesc = descParts.join(' / ');

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
                loadCurrentLoansForDeclareLost(data.data.id);
            } else {
                updateHTMLAndRefreshIcons('declareLostBooksList', `<div class="text-muted-foreground text-xs text-center py-4">{{ __("Bạn đọc không có tài liệu đang mượn") }}</div>`);
            }
        })
        .catch(error => {
            console.error('Error loading patron books:', error);
        });
}

function loadCurrentLoansForDeclareLost(patronId) {
    const listDiv = document.getElementById('declareLostBooksList');
    const patronLoans = loanTransactionsData ? loanTransactionsData.filter(loan => 
        loan.patron_detail_id === patronId && loan.status === 'borrowed'
    ) : [];
    
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
            document.getElementById('reading_notes').value = '';
            loadReadingRoomTransactions();
            loadAllReadingRoomTransactions();
        } else {
            Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
        }
    })
    .catch(error => {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra. Vui lòng thử lại.") }}' }));
    });
}

// Load reading room transactions for patron
function loadReadingRoomTransactions() {
    const patronCode = document.getElementById('reading_patron_code').value.trim();
    const container = document.getElementById('readingRoomTransactions');
    
    if (!patronCode) {
        updateHTMLAndRefreshIcons(container, `
            <div class="text-center text-muted-foreground py-6">
                <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                <p class="text-xs">{{ __("Nhập mã bạn đọc để xem tài liệu đang mượn đọc") }}</p>
            </div>
        `);
        document.getElementById('returnReadingRoomBtn').disabled = true;
        const readingPatronInfo = document.getElementById('readingPatronInfo');
        if (readingPatronInfo) {
            updateHTMLAndRefreshIcons(readingPatronInfo, `
                <div class="text-center text-muted-foreground text-xs py-6">
                    <i data-lucide="user" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                    <p>{{ __('Nhập mã bạn đọc để hiển thị thông tin') }}</p>
                </div>
            `);
        }
        return;
    }
    
    searchPatronByCode(patronCode);
    
    fetch(`{{ route("admin.circulation.reading-room.transactions") }}?patron_code=${patronCode}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayReadingRoomTransactions(data);
            } else {
                updateHTMLAndRefreshIcons(container, `<div class="text-center text-destructive py-4 text-xs font-semibold"><p>${data.message}</p></div>`);
                document.getElementById('returnReadingRoomBtn').disabled = true;
            }
        })
        .catch(error => {
            updateHTMLAndRefreshIcons(container, `<div class="text-center text-destructive py-4 text-xs font-semibold"><p>{{ __("Có lỗi xảy ra khi tải danh sách") }}</p></div>`);
            document.getElementById('returnReadingRoomBtn').disabled = true;
        });
}

// Display reading room transactions
function displayReadingRoomTransactions(data) {
    const container = document.getElementById('readingRoomTransactions');
    const returnBtn = document.getElementById('returnReadingRoomBtn');
    
    if (data.transactions.length === 0) {
        updateHTMLAndRefreshIcons(container, `
            <div class="text-center text-muted-foreground py-6">
                <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                <p class="text-xs">{{ __("Bạn đọc không có tài liệu nào đang mượn đọc") }}</p>
            </div>
        `);
        returnBtn.disabled = true;
        return;
    }
    
    let html = `
        <div class="space-y-2">
            <div class="text-[10px] text-muted-foreground mb-1 font-semibold uppercase">
                {{ __("Tổng số") }}: ${data.total_count} {{ __("tài liệu") }}
            </div>
    `;
    
    data.transactions.forEach(transaction => {
        const statusClass = transaction.is_overdue ? 'text-destructive font-bold' : 'text-emerald-500 font-semibold';
        const statusText = transaction.is_overdue ? '{{ __("Quá hạn") }}' : '{{ __("Đang mượn") }}';
        
        html += `
            <div class="flex items-center space-x-2.5 p-2 bg-muted/20 border border-border rounded-sm hover:bg-muted/40 transition-colors">
                <input type="checkbox" class="reading-room-checkbox rounded-sm border-border text-primary focus:ring-primary focus:ring-offset-0" value="${transaction.id}">
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-xs text-foreground truncate">${transaction.book_title}</div>
                    <div class="text-[10px] text-muted-foreground mt-0.5 truncate">
                        ${transaction.author} • <span class="font-mono">${transaction.barcode}</span>
                    </div>
                    <div class="text-[9px] mt-1 text-muted-foreground">
                        <span class="${statusClass}">${statusText}</span> • 
                        {{ __("Mượn") }}: ${transaction.checkout_time} • 
                        {{ __("Hạn") }}: ${transaction.due_time}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `</div>`;
    updateHTMLAndRefreshIcons(container, html);
    returnBtn.disabled = false;
    
    const checkboxes = document.querySelectorAll('.reading-room-checkbox');
    if (checkboxes.length > 0) {
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateReturnButton);
        });
    }
}

function updateReturnButton() {
    const checkboxes = document.querySelectorAll('.reading-room-checkbox:checked');
    const returnBtn = document.getElementById('returnReadingRoomBtn');
    returnBtn.disabled = checkboxes.length === 0;
}

// Process reading room checkin
function processReadingRoomCheckin() {
    const checkboxes = document.querySelectorAll('.reading-room-checkbox:checked');
    
    if (checkboxes.length === 0) {
        Swal.fire(getSwalConfig('{{ __("Thông báo") }}', 'warning', { text: '{{ __("Vui lòng chọn ít nhất một tài liệu để trả") }}' }));
        return;
    }
    
    const transactionIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    Swal.fire(getSwalConfig('{{ __("Đang xử lý") }}', 'info', {
        text: '{{ __("Đang trả tài liệu mượn đọc...") }}',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    }));
    
    fetch('{{ route("admin.circulation.reading-room.checkin") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ transaction_ids: transactionIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let itemsHtml = '';
            data.data.checked_in_items.forEach(item => {
                itemsHtml += `<li>${item.book_title} (${item.barcode}) - {{ __("Thời gian") }}: ${item.duration}</li>`;
            });
            
            Swal.fire(getSwalConfig('{{ __("Thành công") }}', 'success', {
                html: `
                    ${data.message}<br><br>
                    <small style="text-align: left; display: block;">
                        <strong>{{ __("Các tài liệu đã trả") }}:</strong><br>
                        <ul>${itemsHtml}</ul>
                    </small>
                `
            }));
            loadReadingRoomTransactions();
            loadAllReadingRoomTransactions();
        } else {
            Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
        }
    })
    .catch(error => {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra. Vui lòng thử lại.") }}' }));
    });
}

// Load all active reading room transactions
function loadAllReadingRoomTransactions() {
    const container = document.getElementById('allReadingRoomTransactions');
    fetch('{{ route("admin.circulation.reading-room.active") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAllReadingRoomTransactions(data.data);
            } else {
                updateHTMLAndRefreshIcons(container, `<div class="text-center text-destructive py-4 text-xs font-semibold"><p>${data.message}</p></div>`);
            }
        })
        .catch(error => {
            updateHTMLAndRefreshIcons(container, `<div class="text-center text-destructive py-4 text-xs font-semibold"><p>{{ __("Có lỗi xảy ra khi tải danh sách") }}</p></div>`);
        });
}

// Display all reading room transactions
function displayAllReadingRoomTransactions(data) {
    const container = document.getElementById('allReadingRoomTransactions');
    
    if (data.transactions.length === 0) {
        updateHTMLAndRefreshIcons(container, `
            <div class="text-center text-muted-foreground py-6 text-xs">
                <p>{{ __("Không có tài liệu nào đang mượn đọc") }}</p>
            </div>
        `);
        return;
    }
    
    let html = `
        <div class="mb-2 text-xs font-semibold flex items-center gap-2">
            <span class="text-muted-foreground">{{ __("Tổng số") }}: ${data.total_count}</span>
            ${data.overdue_count > 0 ? `<span class="text-destructive">{{ __("Quá hạn") }}: ${data.overdue_count}</span>` : ''}
        </div>
        <div class="overflow-x-auto rounded-sm border border-border">
            <table class="w-full text-xs">
                <thead class="bg-muted/50 border-b border-border text-muted-foreground text-[10px] uppercase font-semibold">
                    <tr>
                        <th class="p-2 text-left">{{ __("Bạn đọc") }}</th>
                        <th class="p-2 text-left">{{ __("Tài liệu") }}</th>
                        <th class="p-2 text-left">{{ __("Mã vạch") }}</th>
                        <th class="p-2 text-left">{{ __("Mượn") }}</th>
                        <th class="p-2 text-left">{{ __("Hạn") }}</th>
                        <th class="p-2 text-left">{{ __("Thời gian") }}</th>
                        <th class="p-2 text-left">{{ __("Trạng thái") }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
    `;
    
    data.transactions.forEach(transaction => {
        const statusClass = transaction.is_overdue ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
        
        html += `
            <tr class="hover:bg-muted/30">
                <td class="p-2">
                    <div class="font-bold text-foreground text-xs leading-none">${transaction.patron_name}</div>
                    <div class="text-[10px] text-muted-foreground font-mono mt-0.5 leading-none">${transaction.patron_code}</div>
                </td>
                <td class="p-2">
                    <div class="text-xs text-foreground font-medium max-w-[150px] truncate" title="${transaction.book_title}">
                        ${transaction.book_title}
                    </div>
                </td>
                <td class="p-2 text-xs font-mono">${transaction.barcode}</td>
                <td class="p-2 text-[11px] text-muted-foreground">${transaction.checkout_time}</td>
                <td class="p-2 text-[11px] ${transaction.is_overdue ? 'text-destructive font-bold' : 'text-muted-foreground'}">${transaction.due_time}</td>
                <td class="p-2 text-[11px] text-muted-foreground">${transaction.duration}</td>
                <td class="p-2">
                    <span class="px-2 py-0.5 rounded-sm text-[9px] font-bold uppercase border ${statusClass}">
                        ${transaction.status_display}
                    </span>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    updateHTMLAndRefreshIcons(container, html);
}

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
            loadAllReservations();
        } else {
            Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: data.message }));
        }
    })
    .catch(error => {
        Swal.fire(getSwalConfig('{{ __("Lỗi") }}', 'error', { text: '{{ __("Có lỗi xảy ra. Vui lòng thử lại.") }}' }));
    });
}

// Load patron reservations
function loadPatronReservations() {
    const patronCode = document.getElementById('hold_patron_code').value.trim();
    const container = document.getElementById('patronReservations');
    
    if (!patronCode) {
        updateHTMLAndRefreshIcons(container, `
            <div class="text-center text-muted-foreground py-6">
                <i data-lucide="bookmark" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                <p class="text-xs">{{ __("Nhập mã bạn đọc để xem reservations") }}</p>
            </div>
        `);
        return;
    }
    
    fetch(`{{ route("admin.circulation.hold.patron") }}?patron_code=${patronCode}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPatronReservations(data.data);
            } else {
                updateHTMLAndRefreshIcons(container, `<div class="text-center text-destructive py-4 text-xs font-semibold"><p>${data.message}</p></div>`);
            }
        })
        .catch(error => {
            updateHTMLAndRefreshIcons(container, `<div class="text-center text-destructive py-4 text-xs font-semibold"><p>{{ __("Có lỗi xảy ra khi tải danh sách") }}</p></div>`);
        });
}

// Display patron reservations
function displayPatronReservations(data) {
    const container = document.getElementById('patronReservations');
    
    if (data.reservations.length === 0) {
        updateHTMLAndRefreshIcons(container, `
            <div class="text-center text-muted-foreground py-6 text-xs">
                <i data-lucide="bookmark" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/30"></i>
                <p>{{ __("Bạn đọc không có reservation nào đang hoạt động") }}</p>
            </div>
        `);
        return;
    }
    
    let html = `
        <div class="space-y-2">
            <div class="text-[10px] text-muted-foreground mb-1 font-semibold uppercase">
                {{ __("Tổng số") }}: ${data.total_count} {{ __("reservation") }}
            </div>
    `;
    
    data.reservations.forEach(reservation => {
        html += `
            <div class="p-2.5 bg-muted/20 border border-border rounded-sm hover:bg-muted/40 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="font-bold text-xs text-foreground truncate">${reservation.book_title}</div>
                        <div class="text-[10px] text-muted-foreground mt-0.5 truncate">
                            ${reservation.author} • <span class="font-mono">${reservation.barcode}</span>
                        </div>
                        <div class="text-[10px] mt-1 space-y-0.5 text-muted-foreground">
                            <div class="${reservation.status_color}">${reservation.status_display}</div>
                            <div>{{ __("Đặt giữ") }}: ${reservation.reservation_date}</div>
                            <div>{{ __("Hết hạn") }}: ${reservation.expiry_date}</div>
                            ${reservation.position ? `<div>{{ __("Vị trí trong hàng chờ") }}: #${reservation.position}</div>` : ''}
                            <div>{{ __("Điểm nhận") }}: ${reservation.pickup_branch}</div>
                        </div>
                    </div>
                    <div class="flex space-x-1.5 ml-2">
                        ${reservation.status === 'ready' ? `
                            <button onclick="fulfillReservation(${reservation.id})" 
                                    class="text-emerald-500 hover:text-emerald-600" 
                                    title="{{ __("Cho mượn") }}">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            </button>
                        ` : ''}
                        <button onclick="cancelReservation(${reservation.id})" 
                                class="text-destructive hover:text-destructive/90" 
                                title="{{ __("Hủy") }}">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                ${reservation.notes ? `<div class="text-[10px] text-muted-foreground mt-1 italic border-t border-border/50 pt-1">${reservation.notes}</div>` : ''}
            </div>
        `;
    });
    
    html += `</div>`;
    updateHTMLAndRefreshIcons(container, html);
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
                    loadAllReservations();
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
                    loadAllReservations();
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

// Load all active reservations
function loadAllReservations() {
    const container = document.getElementById('allReservations');
    fetch('{{ route("admin.circulation.hold.all") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAllReservations(data.data);
            } else {
                updateHTMLAndRefreshIcons(container, `<div class="text-center text-destructive py-4 text-xs font-semibold"><p>${data.message}</p></div>`);
            }
        })
        .catch(error => {
            updateHTMLAndRefreshIcons(container, `<div class="text-center text-destructive py-4 text-xs font-semibold"><p>{{ __("Có lỗi xảy ra khi tải danh sách") }}</p></div>`);
        });
}

// Display all reservations
function displayAllReservations(data) {
    const container = document.getElementById('allReservations');
    
    if (data.reservations.length === 0) {
        updateHTMLAndRefreshIcons(container, `
            <div class="text-center text-muted-foreground py-6 text-xs">
                <p>{{ __("Không có reservation nào đang hoạt động") }}</p>
            </div>
        `);
        return;
    }
    
    let html = `
        <div class="mb-2 text-xs font-semibold flex items-center gap-2">
            <span class="text-muted-foreground">{{ __("Tổng số") }}: ${data.total_count}</span>
            <span class="text-emerald-500">{{ __("Sẵn sàng") }}: ${data.ready_count}</span>
            <span class="text-amber-500">{{ __("Đang chờ") }}: ${data.pending_count}</span>
        </div>
        <div class="overflow-x-auto rounded-sm border border-border">
            <table class="w-full text-xs">
                <thead class="bg-muted/50 border-b border-border text-muted-foreground text-[10px] uppercase font-semibold">
                    <tr>
                        <th class="p-2 text-left">{{ __("Bạn đọc") }}</th>
                        <th class="p-2 text-left">{{ __("Tài liệu") }}</th>
                        <th class="p-2 text-left">{{ __("Mã vạch") }}</th>
                        <th class="p-2 text-left">{{ __("Đặt giữ") }}</th>
                        <th class="p-2 text-left">{{ __("Hết hạn") }}</th>
                        <th class="p-2 text-left">{{ __("Trạng thái") }}</th>
                        <th class="p-2 text-left">{{ __("Vị trí") }}</th>
                        <th class="p-2 text-left">{{ __("Hành động") }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
    `;
    
    data.reservations.forEach(reservation => {
        html += `
            <tr class="hover:bg-muted/30">
                <td class="p-2">
                    <div class="font-bold text-foreground text-xs leading-none">${reservation.patron_name}</div>
                    <div class="text-[10px] text-muted-foreground font-mono mt-0.5 leading-none">${reservation.patron_code}</div>
                </td>
                <td class="p-2">
                    <div class="text-xs text-foreground font-medium max-w-[150px] truncate" title="${reservation.book_title}">
                        ${reservation.book_title}
                    </div>
                </td>
                <td class="p-2 text-xs font-mono">${reservation.barcode}</td>
                <td class="p-2 text-[11px] text-muted-foreground">${reservation.reservation_date}</td>
                <td class="p-2 text-[11px] ${reservation.is_expired ? 'text-destructive font-semibold' : 'text-muted-foreground'}">${reservation.expiry_date}</td>
                <td class="p-2">
                    <span class="px-2 py-0.5 rounded-sm text-[9px] font-bold uppercase border ${reservation.status_color}">
                        ${reservation.status_display}
                    </span>
                </td>
                <td class="p-2 text-xs text-muted-foreground">${reservation.position || '-'}</td>
                <td class="p-2">
                    <div class="flex items-center gap-1.5">
                        ${reservation.status === 'ready' ? `
                            <button onclick="fulfillReservation(${reservation.id})" 
                                    class="text-emerald-500 hover:text-emerald-600" 
                                    title="{{ __("Cho mượn") }}">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            </button>
                        ` : ''}
                        <button onclick="cancelReservation(${reservation.id})" 
                                class="text-destructive hover:text-destructive/90" 
                                title="{{ __("Hủy") }}">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    updateHTMLAndRefreshIcons(container, html);
}

// ==================== LOAN REQUEST FUNCTIONS ====================

async function approveLoanRequest(id) {
    const result = await Swal.fire(getSwalConfig('{{ __("Phê duyệt yêu cầu?") }}', 'question', {
        text: '{{ __("Bạn có chắc chắn muốn phê duyệt yêu cầu mượn này?") }}',
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
</script>
@endsection
