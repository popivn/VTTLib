@extends('layouts.site')

@section('title', 'Hồ sơ cá nhân - VTTLib')

@section('content')
<div class="bg-background min-h-screen pt-24 pb-12" x-data="{ activeTab: '{{ request()->query('tab', $errors->has('current_password') || $errors->has('new_password') ? 'password' : (session('success') ? 'password' : 'info')) }}' }" x-init="if(window.lucide) lucide.createIcons(); $watch('activeTab', () => $nextTick(() => { if(window.lucide) lucide.createIcons() }))">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Sidebar: User Info -->
            <div class="lg:col-span-4">
                <div class="bg-card rounded-md p-4 shadow-sm border border-border sticky top-28">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-24 h-24 rounded-full bg-primary/5 flex items-center justify-center border-4 border-card shadow-md mb-4 overflow-hidden">
                            @if($patron && $patron->profile_image)
                                <img src="{{ asset('storage/' . $patron->profile_image) }}" class="w-full h-full object-cover">
                            @elseif($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-vttu-red to-vttu-dark flex items-center justify-center">
                                    <span class="text-2xl font-black text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <h2 class="text-base font-bold text-foreground uppercase tracking-tight">{{ $user->name }}</h2>
                        <p class="text-muted-foreground font-bold text-[10px] mt-1 uppercase tracking-widest">{{ $patron?->patronGroup?->name ?? ($user->roles->pluck('display_name')->implode(', ') ?: 'Độc giả') }}</p>
                        
                        <div class="w-full grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-border">
                            <div class="p-3 bg-muted rounded">
                                <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Mã độc giả</p>
                                <p class="text-xs font-bold text-foreground">{{ $patron?->patron_code ?? 'N/A' }}</p>
                            </div>
                            <div class="p-3 bg-muted rounded">
                                <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Trạng thái thẻ</p>
                                <p class="text-xs font-bold text-foreground">
                                    @if($patron)
                                        @if($patron->card_status == 'normal')
                                            <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 text-[9px] font-bold uppercase rounded">Hoạt động</span>
                                        @elseif($patron->card_status == 'locked')
                                            <span class="px-2 py-0.5 bg-rose-500/10 text-rose-500 text-[9px] font-bold uppercase rounded">Khóa</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 text-[9px] font-bold uppercase rounded">{{ $patron->card_status }}</span>
                                        @endif
                                    @else
                                        <span class="px-2 py-0.5 bg-muted text-muted-foreground text-[9px] font-bold uppercase rounded">N/A</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Menu Tabs -->
                        <div class="w-full mt-4 space-y-2">
                            <button @click="activeTab = 'info'; window.history.replaceState(null, '', '?tab=info')" 
                                    :class="activeTab === 'info' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-foreground hover:bg-muted'" 
                                    class="w-full flex items-center space-x-3 px-4 py-2.5 rounded transition-all text-xs font-bold uppercase tracking-widest">
                                <i data-lucide="user-circle" class="w-4 h-4"></i>
                                <span>Thông tin cá nhân</span>
                            </button>
                            <button @click="activeTab = 'history'; window.history.replaceState(null, '', '?tab=history')" 
                                    :class="activeTab === 'history' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-foreground hover:bg-muted'" 
                                    class="w-full flex items-center space-x-3 px-4 py-2.5 rounded transition-all text-xs font-bold uppercase tracking-widest">
                                <i data-lucide="history" class="w-4 h-4"></i>
                                <span>Lịch sử mượn sách</span>
                            </button>
                            <button @click="activeTab = 'surveys'; window.history.replaceState(null, '', '?tab=surveys')" 
                                    :class="activeTab === 'surveys' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-foreground hover:bg-muted'" 
                                    class="w-full flex items-center space-x-3 px-4 py-2.5 rounded transition-all text-xs font-bold uppercase tracking-widest">
                                <i data-lucide="star" class="w-4 h-4"></i>
                                <span>Lịch sử đánh giá ({{ $mySurveys->count() }})</span>
                            </button>
                            <button @click="activeTab = 'password'; window.history.replaceState(null, '', '?tab=password')" 
                                    :class="activeTab === 'password' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-foreground hover:bg-muted'" 
                                    class="w-full flex items-center space-x-3 px-4 py-2.5 rounded transition-all text-xs font-bold uppercase tracking-widest">
                                <i data-lucide="key-round" class="w-4 h-4"></i>
                                <span>Đổi mật khẩu</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-8">
                @if (session('success'))
                    <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 rounded p-3 text-xs font-bold flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded p-3 text-xs font-bold space-y-1">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center gap-2">
                                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                <!-- Tab: Information -->
                <div x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                    <!-- Stats Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="bg-card p-4 rounded-md shadow-sm border border-border">
                            <div class="w-8 h-8 bg-blue-500/10 text-blue-500 rounded flex items-center justify-center mb-3">
                                <i data-lucide="book" class="w-4 h-4"></i>
                            </div>
                            <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">Đã mượn</p>
                            <h4 class="text-xl font-bold text-foreground">{{ $stats['total_borrowed'] }}</h4>
                        </div>
                        <div class="bg-card p-4 rounded-md shadow-sm border border-border">
                            <div class="w-8 h-8 bg-emerald-500/10 text-emerald-500 rounded flex items-center justify-center mb-3">
                                <i data-lucide="book-open" class="w-4 h-4"></i>
                            </div>
                            <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">Đang mượn</p>
                            <h4 class="text-xl font-bold text-foreground">{{ $stats['active_loans'] }}</h4>
                        </div>
                        <div class="bg-card p-4 rounded-md shadow-sm border border-border">
                            <div class="w-8 h-8 bg-amber-500/10 text-amber-500 rounded flex items-center justify-center mb-3">
                                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            </div>
                            <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">Quá hạn</p>
                            <h4 class="text-xl font-bold text-foreground">{{ $stats['overdue_loans'] }}</h4>
                        </div>
                        <div class="bg-card p-4 rounded-md shadow-sm border border-border">
                            <div class="w-8 h-8 bg-rose-500/10 text-rose-500 rounded flex items-center justify-center mb-3">
                                <i data-lucide="wallet" class="w-4 h-4"></i>
                            </div>
                            <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">Tiền nợ</p>
                            <h4 class="text-xl font-bold text-foreground">{{ number_format($stats['total_fines']) }}đ</h4>
                        </div>
                    </div>

                    <!-- BLOCK 1: Thông tin bạn đọc (patron_details) -->
                    <div class="bg-card rounded-md shadow-sm border border-border overflow-hidden">
                        <div class="flex items-center gap-2 px-4 py-3 bg-primary/5 border-b border-border">
                            <i data-lucide="id-card" class="w-4 h-4 text-primary"></i>
                            <h3 class="text-xs font-bold text-foreground uppercase tracking-[0.2em]">Thông tin bạn đọc</h3>
                            <span class="ml-auto text-[9px] font-bold text-muted-foreground px-2 py-0.5 bg-muted rounded border border-border">patron_details</span>
                        </div>
                        @if($patron)
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Họ và tên bạn đọc</label>
                                <p class="text-xs font-bold text-foreground bg-muted px-3 py-2 rounded border border-border">{{ $patron->attributes['display_name'] ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Mã bạn đọc</label>
                                <p class="text-xs font-bold text-foreground bg-muted px-3 py-2 rounded border border-border font-mono">{{ $patron->patron_code }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Nhóm độc giả</label>
                                <p class="text-xs font-bold text-foreground bg-muted px-3 py-2 rounded border border-border">{{ $patron->patronGroup?->name ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Trạng thái thẻ</label>
                                <p class="bg-muted px-3 py-2 rounded border border-border">
                                    @if($patron->card_status == 'normal')
                                        <span class="text-[9px] font-bold text-emerald-500 uppercase">Hoạt động bình thường</span>
                                    @elseif($patron->card_status == 'locked')
                                        <span class="text-[9px] font-bold text-rose-500 uppercase">Đã khóa</span>
                                    @else
                                        <span class="text-[9px] font-bold text-amber-500 uppercase">{{ $patron->card_status }}</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Số điện thoại</label>
                                <p class="text-xs font-bold bg-muted px-3 py-2 rounded border border-border {{ $patron->phone_contact ? 'text-foreground' : 'text-muted-foreground' }}">{{ $patron->phone_contact ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Ngày sinh</label>
                                <p class="text-xs font-bold bg-muted px-3 py-2 rounded border border-border {{ $patron->dob ? 'text-foreground' : 'text-muted-foreground' }}">{{ $patron->dob ? \Carbon\Carbon::parse($patron->dob)->format('d/m/Y') : '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Đơn vị / Lớp</label>
                                <p class="text-xs font-bold bg-muted px-3 py-2 rounded border border-border {{ $patron->department ? 'text-foreground' : 'text-muted-foreground' }}">{{ $patron->department ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">CCCD / MSSV</label>
                                <p class="text-xs font-bold bg-muted px-3 py-2 rounded border border-border {{ $patron->id_card || $patron->mssv ? 'text-foreground' : 'text-muted-foreground' }}">{{ $patron->id_card ?? $patron->mssv ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Ngày đăng ký</label>
                                <p class="text-xs font-bold text-foreground bg-muted px-3 py-2 rounded border border-border">{{ $patron->registration_date ? \Carbon\Carbon::parse($patron->registration_date)->format('d/m/Y') : '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Ngày hết hạn</label>
                                <p class="text-xs font-bold bg-muted px-3 py-2 rounded border border-border {{ $patron->expiry_date && \Carbon\Carbon::parse($patron->expiry_date)->isPast() ? 'text-rose-500' : 'text-foreground' }}">
                                    {{ $patron->expiry_date ? \Carbon\Carbon::parse($patron->expiry_date)->format('d/m/Y') : '—' }}
                                    @if($patron->expiry_date && \Carbon\Carbon::parse($patron->expiry_date)->isPast())
                                        <span class="text-[8px] text-rose-500 ml-1">(Đã hết hạn)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @else
                        <div class="p-6 text-center text-xs text-muted-foreground font-bold">
                            <i data-lucide="user-x" class="w-8 h-8 mx-auto mb-2 text-muted-foreground/50"></i>
                            Tài khoản này chưa được liên kết với hồ sơ bạn đọc nào.
                        </div>
                        @endif
                    </div>

                    <!-- BLOCK 2: Thông tin tài khoản (users) -->
                    <div class="bg-card rounded-md shadow-sm border border-border overflow-hidden">
                        <div class="flex items-center gap-2 px-4 py-3 bg-muted/50 border-b border-border">
                            <i data-lucide="user-circle" class="w-4 h-4 text-muted-foreground"></i>
                            <h3 class="text-xs font-bold text-foreground uppercase tracking-[0.2em]">Thông tin tài khoản</h3>
                            <span class="ml-auto text-[9px] font-bold text-muted-foreground px-2 py-0.5 bg-muted rounded border border-border">users</span>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Tên hiển thị (name)</label>
                                <p class="text-xs font-bold text-foreground bg-muted px-3 py-2 rounded border border-border">{{ $user->name }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Tên đăng nhập (username)</label>
                                <p class="text-xs font-bold bg-muted px-3 py-2 rounded border border-border {{ $user->username ? 'text-foreground font-mono' : 'text-muted-foreground' }}">{{ $user->username ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Email</label>
                                <p class="text-xs font-bold text-foreground bg-muted px-3 py-2 rounded border border-border">{{ $user->email }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Vai trò</label>
                                <p class="text-xs font-bold text-foreground bg-muted px-3 py-2 rounded border border-border">{{ $user->roles->pluck('display_name')->implode(', ') ?: 'Bạn đọc' }}</p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Trạng thái tài khoản</label>
                                <p class="bg-muted px-3 py-2 rounded border border-border">
                                    @if($user->status == 'active')
                                        <span class="text-[9px] font-bold text-emerald-500 uppercase">Đang hoạt động</span>
                                    @else
                                        <span class="text-[9px] font-bold text-rose-500 uppercase">{{ $user->status ?? 'N/A' }}</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Ngày tạo tài khoản</label>
                                <p class="text-xs font-bold text-foreground bg-muted px-3 py-2 rounded border border-border">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Tab: Loan History -->
                <div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                    <!-- Section: Reservations -->
                    <div class="bg-card rounded-md p-4 shadow-sm border border-border">
                        <h3 class="flex items-center gap-2 text-xs font-bold text-foreground uppercase tracking-[0.2em] mb-4">
                            <span class="w-4 h-1 bg-amber-400 rounded-full"></span>
                            Yêu cầu mượn & Đăng ký
                        </h3>
                        
                        <div class="space-y-2">
                            @forelse($reservations as $res)
                            @php
                                $resTitle = $res->bibliographicRecord->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
                            @endphp
                            <div class="p-3 bg-muted/50 hover:bg-muted/80 rounded border border-border transition-colors">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-14 bg-background rounded flex items-center justify-center text-muted-foreground border border-border shadow-sm overflow-hidden flex-shrink-0">
                                            @if($res->bibliographicRecord->cover_image)
                                                <img src="{{ asset('storage/' . $res->bibliographicRecord->cover_image) }}" class="w-full h-full object-contain dark:brightness-90">
                                            @else
                                                <i data-lucide="book" class="w-5 h-5 text-muted-foreground"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-foreground line-clamp-1">{{ $resTitle }}</h4>
                                            <p class="text-[9px] text-muted-foreground font-bold mt-1 uppercase tracking-widest">Đăng ký: {{ $res->reservation_date->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        @if($res->status == 'pending')
                                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 text-[9px] font-bold uppercase rounded border border-amber-500/20">Đang chờ duyệt</span>
                                        @elseif($res->status == 'ready')
                                            <div class="text-right">
                                                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 text-[9px] font-bold uppercase rounded border border-emerald-500/20">Sẵn sàng nhận sách</span>
                                                @if($res->expiry_date)
                                                <p class="text-[8px] text-emerald-500 font-bold mt-0.5 uppercase tracking-widest">
                                                    Còn {{ now()->diffInDays($res->expiry_date, false) }} ngày
                                                </p>
                                                @endif
                                            </div>
                                        @elseif($res->status == 'cancelled' || $res->status == 'rejected')
                                            <span class="px-2 py-0.5 bg-rose-500/10 text-rose-500 text-[9px] font-bold uppercase rounded border border-rose-500/20">Đã hủy / Từ chối</span>
                                        @elseif($res->status == 'completed')
                                            <span class="px-2 py-0.5 bg-blue-500/10 text-blue-500 text-[9px] font-bold uppercase rounded border border-blue-500/20">Đã hoàn tất</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-6">
                                <p class="text-muted-foreground font-bold text-xs">Không có yêu cầu nào.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Section: Current Loans -->
                    <div class="bg-card rounded-md p-4 shadow-sm border border-border">
                        <h3 class="flex items-center gap-2 text-xs font-bold text-foreground uppercase tracking-[0.2em] mb-4">
                            <span class="w-4 h-1 bg-emerald-500 rounded-full"></span>
                            Sách đang mượn
                        </h3>
                        
                        <div class="space-y-2">
                            @forelse($activeLoans as $loan)
                            @php
                                $loanTitle = $loan->bookItem->bibliographicRecord->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
                                $isOverdue = $loan->isOverdue();
                            @endphp
                            <div class="p-3 {{ $isOverdue ? 'bg-rose-500/5 border-rose-500/20 hover:bg-rose-500/10' : 'bg-muted/50 border-border hover:bg-muted/80' }} rounded border transition-colors">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-14 bg-background rounded flex items-center justify-center text-muted-foreground border border-border shadow-sm overflow-hidden flex-shrink-0">
                                            @if($loan->bookItem->bibliographicRecord->cover_image)
                                                <img src="{{ asset('storage/' . $loan->bookItem->bibliographicRecord->cover_image) }}" class="w-full h-full object-contain dark:brightness-90">
                                            @else
                                                <i data-lucide="book" class="w-5 h-5 text-muted-foreground"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-foreground line-clamp-1">{{ $loanTitle }}</h4>
                                            <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-1">
                                                <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">Ngày mượn: {{ $loan->loan_date->format('d/m/Y') }}</p>
                                                <p class="text-[9px] font-bold uppercase tracking-widest {{ $isOverdue ? 'text-rose-500' : 'text-muted-foreground' }}">
                                                    Hạn trả: {{ $loan->due_date->format('d/m/Y') }} 
                                                    @if($isOverdue) ({{ $loan->getOverdueDays() }} ngày) @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="px-2 py-0.5 {{ $isOverdue ? 'bg-rose-500/10 text-rose-500 border-rose-500/20' : 'bg-blue-500/10 text-blue-500 border-blue-500/20' }} text-[9px] font-bold uppercase rounded border">
                                            {{ $isOverdue ? 'Quá hạn' : 'Đang mượn' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-6">
                                <p class="text-muted-foreground font-bold text-xs">Hiện không mượn cuốn sách nào.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Section: Returned Loans -->
                    <div class="bg-card rounded-md p-4 shadow-sm border border-border">
                        <h3 class="flex items-center gap-2 text-xs font-bold text-foreground uppercase tracking-[0.2em] mb-4">
                            <span class="w-4 h-1 bg-slate-300 rounded-full"></span>
                            Lịch sử đã trả
                        </h3>
                        
                        <div class="space-y-2">
                            @forelse($returnedLoans as $loan)
                            @php
                                $loanTitle = $loan->bookItem->bibliographicRecord->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
                            @endphp
                            <div class="p-3 bg-muted/30 hover:bg-muted/50 rounded border border-border transition-all">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-14 bg-background rounded flex items-center justify-center text-muted-foreground border border-border shadow-sm overflow-hidden flex-shrink-0">
                                            @if($loan->bookItem->bibliographicRecord->cover_image)
                                                <img src="{{ asset('storage/' . $loan->bookItem->bibliographicRecord->cover_image) }}" class="w-full h-full object-contain dark:brightness-90">
                                            @else
                                                <i data-lucide="book" class="w-5 h-5 text-muted-foreground"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-foreground line-clamp-1">{{ $loanTitle }}</h4>
                                            <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-1">
                                                <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">Đã trả: {{ $loan->return_date ? $loan->return_date->format('d/m/Y') : 'N/A' }}</p>
                                                <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">Mã vạch: {{ $loan->bookItem->barcode }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="px-2 py-0.5 bg-muted text-muted-foreground text-[9px] font-bold uppercase rounded border border-border">Đã trả</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-6">
                                <p class="text-muted-foreground font-bold text-xs">Chưa có lịch sử trả sách.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Tab: Change Password -->
                <div x-show="activeTab === 'password'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="bg-card rounded-md p-4 shadow-sm border border-border">
                        <h3 class="flex items-center gap-2 text-xs font-bold text-foreground uppercase tracking-[0.2em] mb-4">
                            <span class="w-4 h-1 bg-primary rounded-full"></span>
                            Đổi mật khẩu tài khoản
                        </h3>
                        
                        <form action="{{ route('profile.change-password') }}" method="POST" class="space-y-3 max-w-md">
                            @csrf
                            <div>
                                <label for="current_password" class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Mật khẩu hiện tại</label>
                                <input type="password" name="current_password" id="current_password" required
                                       class="w-full text-xs font-bold text-foreground bg-muted px-3 py-2.5 rounded border border-border focus:outline-none focus:border-primary transition-colors">
                            </div>
                            
                            <div>
                                <label for="new_password" class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Mật khẩu mới</label>
                                <input type="password" name="new_password" id="new_password" required
                                       class="w-full text-xs font-bold text-foreground bg-muted px-3 py-2.5 rounded border border-border focus:outline-none focus:border-primary transition-colors">
                            </div>
                            
                            <div>
                                <label for="new_password_confirmation" class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">Xác nhận mật khẩu mới</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                                       class="w-full text-xs font-bold text-foreground bg-muted px-3 py-2.5 rounded border border-border focus:outline-none focus:border-primary transition-colors">
                            </div>
                            
                            <div class="pt-2">
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 text-xs font-bold rounded shadow-sm transition-all gap-1.5 active:scale-[0.98]">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    <span>Cập nhật mật khẩu</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tab: Survey History -->
                <div x-show="activeTab === 'surveys'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="bg-card rounded-md p-4 shadow-sm border border-border">
                        <div class="flex items-center justify-between mb-4 border-b border-border pb-3">
                            <h3 class="flex items-center gap-2 text-xs font-bold text-foreground uppercase tracking-[0.2em]">
                                <span class="w-4 h-1 bg-primary rounded-full"></span>
                                Lịch sử khảo sát & Ý kiến đóng góp ({{ $mySurveys->count() }})
                            </h3>
                            <a href="/khao-sat-y-kien" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>Gửi khảo sát mới</span>
                            </a>
                        </div>

                        <div class="space-y-4">
                            @forelse($mySurveys as $survey)
                                <div class="p-4 rounded-md border border-border bg-muted/20 hover:border-primary/30 transition-all space-y-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-border pb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                                {{ $survey->rating_overall }} / 5 ⭐
                                            </span>
                                            <span class="text-xs font-bold text-foreground">{{ $survey->survey_category }}</span>
                                        </div>
                                        <span class="text-[10px] font-medium text-muted-foreground">
                                            <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>
                                            {{ $survey->created_at ? $survey->created_at->format('H:i d/m/Y') : '' }}
                                        </span>
                                    </div>

                                    @if($survey->ratings && $survey->ratings->count() > 0)
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs bg-card p-3 rounded border border-border">
                                            @foreach($survey->ratings as $item)
                                                <div class="flex justify-between items-center text-muted-foreground">
                                                    <span class="font-medium text-foreground/80">{{ $item->criterion?->name }}:</span>
                                                    <span class="font-bold text-amber-500 flex items-center gap-1">
                                                        {{ $item->rating }}/5 ⭐
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($survey->content)
                                        <div class="p-3 bg-muted/40 rounded border border-border/60 text-xs text-foreground italic leading-relaxed whitespace-pre-line">
                                            "{{ $survey->content }}"
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-10 space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto text-muted-foreground">
                                        <i data-lucide="file-text" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-xs font-bold text-muted-foreground">Bạn chưa gửi khảo sát hay đóng góp ý kiến nào.</p>
                                    <a href="/khao-sat-y-kien" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-xs font-bold rounded shadow-sm hover:bg-primary/90 transition-all">
                                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                        <span>Gửi ý kiến khảo sát ngay</span>
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
