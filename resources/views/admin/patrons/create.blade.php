@extends('layouts.admin')

@section('content')
<div class="space-y-4 pb-8 px-4 sm:px-6 lg:px-8 bg-background">
    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-border">
        <div>
            <h1 class="text-xl font-bold text-foreground tracking-tight">{{ __('Register New Patron') }}</h1>
            <p class="text-muted-foreground text-xs font-medium mt-0.5">{{ __('Tạo mới thông tin tài khoản độc giả trong hệ thống') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" onclick="autoFillRandom()" class="bg-primary text-primary-foreground hover:bg-primary/90 px-3 py-1.5 rounded text-xs font-bold transition-all flex items-center space-x-1.5 active:scale-[0.98]">
                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                <span>{{ __('Auto Fill') }}</span>
            </button>
            <span class="px-2.5 py-1 text-xs font-bold rounded bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                {{ __('Tình trạng thẻ') }}: {{ __('Bình thường') }}
            </span>
            <a href="{{ route('admin.patrons.index') }}" class="bg-secondary text-secondary-foreground hover:bg-secondary/80 px-2.5 py-1 rounded text-xs font-bold transition-all flex items-center space-x-1 active:scale-[0.98]">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>{{ __('Back') }}</span>
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-destructive/10 border border-destructive/20 rounded p-3 flex items-start space-x-2 shadow-sm text-destructive">
            <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
            <div class="text-xs font-medium">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error) 
                        <li>{{ $error }}</li> 
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="patronCreateForm" action="{{ route('admin.patrons.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-4 gap-3">
        @csrf
        
        <!-- Sidebar: Image & Status Toggles -->
        <div class="lg:col-span-1 space-y-3">
            <div class="bg-card rounded border border-border p-4 flex flex-col items-center" x-data="{ isCircle: false }">
                <div id="avatar-container" 
                     class="bg-muted border border-border border-dashed flex items-center justify-center overflow-hidden relative group cursor-pointer mb-3 transition-all duration-300"
                     :class="isCircle ? 'w-32 h-32 rounded-full' : 'w-32 h-40 rounded-md'"
                     onclick="document.getElementById('avatar-input').click()">
                    <img id="avatar-preview" src="#" class="hidden w-full h-full object-cover">
                    <div id="avatar-placeholder" class="text-muted-foreground flex flex-col items-center p-2 text-center">
                        <i data-lucide="image" class="w-8 h-8 mb-1"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">{{ __('Ảnh đại diện') }}</span>
                    </div>
                </div>
                <input type="file" name="profile_image" id="avatar-input" class="hidden" accept="image/*" onchange="previewAvatar(this)">
                
                <div class="w-full space-y-2">
                    <button type="button" @click="isCircle = !isCircle" class="w-full bg-secondary text-secondary-foreground hover:bg-secondary/80 py-1.5 rounded text-xs font-bold transition-colors flex items-center justify-center space-x-1.5 active:scale-[0.98]">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span x-text="isCircle ? '{{ __('Dạng chữ nhật') }}' : '{{ __('Dạng tròn') }}'"></span>
                    </button>
                    <div class="flex space-x-2 w-full">
                        <button type="button" onclick="document.getElementById('avatar-input').click()" class="flex-1 bg-secondary text-secondary-foreground hover:bg-secondary/80 py-2 rounded text-xs font-bold transition-colors flex items-center justify-center">
                            <i data-lucide="upload" class="w-4 h-4 mr-1"></i>
                            {{ __('Chọn ảnh') }}
                        </button>
                        <button type="button" onclick="removeAvatar()" class="flex-1 bg-destructive/10 text-destructive hover:bg-destructive/20 py-2 rounded text-xs font-bold transition-colors flex items-center justify-center">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>
                            {{ __('Xoá ảnh') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-card rounded border border-border p-4 space-y-3">
                <label class="flex items-center justify-between group cursor-pointer">
                    <span class="text-xs text-foreground">{{ __('Chỉ đăng ký đọc') }}</span>
                    <div class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_read_only" value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                    </div>
                </label>
                <label class="flex items-center justify-between group cursor-pointer">
                    <span class="text-xs text-foreground">{{ __('Thẻ chờ in') }}</span>
                    <div class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_waiting_for_print" value="1" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
                    </div>
                </label>
                
                <label class="flex items-center justify-between group cursor-pointer">
                    <span class="text-xs text-foreground">{{ __('Đọc tại chỗ') }}</span>
                    <div class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_reading_room_only" value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                    </div>
                </label>
                
                <label class="flex items-center justify-between group cursor-pointer">
                    <span class="text-xs text-foreground">{{ __('Thêm vào danh sách chờ in') }}</span>
                    <div class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="add_to_print_queue" value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                    </div>
                </label>
            </div>

            <div class="bg-card rounded border border-border p-4 space-y-3" x-data="userSearch()" x-init="$watch('status', () => $nextTick(() => { if (window.lucide) window.lucide.createIcons(); }))">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-foreground">{{ __('Liên kết tài khoản') }}</label>
                        <button type="button" @click="removeLink" class="text-xs font-bold text-destructive hover:underline">
                            {{ __('Xoá liên kết') }}
                        </button>
                    </div>
                    
                    <div class="relative">
                        <input type="text" 
                            x-model="query" 
                            @input.debounce.500ms="search"
                            @focus="if(searchResults.length > 0) showDropdown = true"
                            placeholder="{{ __('Nhập tên, email hoặc username...') }}"
                            class="w-full pl-3 pr-8 py-1.5 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all"
                            :class="status === 'found' && !selectedUser?.is_linked_to_other ? 'border-emerald-500/30' : (status === 'found' && selectedUser?.is_linked_to_other ? 'border-destructive/30' : (status === 'not_found' ? 'border-destructive/30' : ''))">
                        
                        <button type="button" @click="search" class="absolute right-2.5 top-2 text-muted-foreground hover:text-foreground transition-colors">
                            <svg x-show="loading" class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.062 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>

                        <div x-show="showDropdown && searchResults.length > 0" @click.away="showDropdown = false" x-cloak class="absolute z-50 w-full mt-1 bg-card border border-border rounded shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="user in searchResults" :key="user.id">
                                <div @click="selectUser(user)" class="p-2 border-b border-border/50 hover:bg-muted/60 cursor-pointer transition-colors flex items-center justify-between text-xs">
                                    <div class="min-w-0 flex-1 pr-2">
                                        <div class="font-bold text-foreground truncate">
                                            <span x-text="user.name"></span>
                                            (<span x-text="user.username" class="text-primary"></span>)
                                        </div>
                                        <div class="text-[11px] text-muted-foreground truncate" x-text="user.email"></div>
                                    </div>
                                    <div class="shrink-0">
                                        <span x-show="user.is_linked_to_other" class="px-1.5 py-0.5 text-[9px] font-bold bg-destructive/10 text-destructive border border-destructive/20 rounded">
                                            {{ __('Đã liên kết') }}
                                        </span>
                                        <span x-show="!user.is_linked_to_other" class="px-1.5 py-0.5 text-[9px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 rounded">
                                            {{ __('Hợp lệ') }}
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <input type="hidden" name="user_id" :value="selectedUser && !selectedUser.is_linked_to_other ? selectedUser.id : ''">

                    <div x-show="status === 'found' && selectedUser && !selectedUser.is_linked_to_other" x-cloak class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded border animate-in fade-in duration-300">
                        <div class="flex items-center space-x-2">
                            <div class="p-1 bg-emerald-500 rounded text-white flex-shrink-0">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider leading-none mb-0.5">
                                    {{ __('Tài khoản hợp lệ') }}
                                </p>
                                <p class="text-xs font-bold text-foreground truncate">
                                    <span x-text="selectedUser?.name"></span>
                                    (<span x-text="selectedUser?.username" class="text-primary"></span>)
                                </p>
                                <p class="text-[11px] text-muted-foreground truncate" x-text="selectedUser?.email"></p>
                            </div>
                        </div>
                    </div>

                    <div x-show="status === 'found' && selectedUser && selectedUser.is_linked_to_other" x-cloak class="p-2.5 bg-destructive/10 rounded border border-destructive/20 animate-in fade-in duration-300">
                        <div class="flex items-center space-x-2">
                            <div class="p-1 bg-destructive rounded text-white flex-shrink-0">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold text-destructive uppercase tracking-wider leading-none mb-0.5">{{ __('Đã liên kết với độc giả khác') }}</p>
                                <p class="text-xs font-bold text-foreground truncate">
                                    <span x-text="selectedUser?.name"></span>
                                    (<span x-text="selectedUser?.username" class="text-primary"></span>)
                                </p>
                                <p class="text-[11px] text-muted-foreground truncate mt-0.5">
                                    {{ __('Tài khoản này đã được liên kết với độc giả khác:') }}
                                    <strong x-text="selectedUser?.linked_patron_name"></strong>
                                    (<span x-text="selectedUser?.linked_patron_code" class="font-mono"></span>)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div x-show="status === 'not_found'" x-cloak class="p-2.5 bg-destructive/10 rounded border border-destructive/20 animate-in fade-in duration-300">
                        <div class="flex items-center space-x-2">
                            <div class="p-1 bg-destructive rounded text-white flex-shrink-0">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-destructive uppercase tracking-wider leading-none mb-0.5">{{ __('Không tìm thấy') }}</p>
                                <p class="text-[11px] text-muted-foreground">{{ __('Vui lòng kiểm tra lại tên hoặc email.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="status === 'idle' && !selectedUser" class="p-2.5 bg-amber-500/10 rounded border border-amber-500/20 animate-in fade-in duration-300">
                        <div class="flex items-center space-x-2">
                            <div class="p-1 bg-amber-500 rounded text-white flex-shrink-0">
                                <i data-lucide="link-2-off" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider leading-none mb-0.5">{{ __('Chưa có tài khoản liên kết') }}</p>
                                <p class="text-[11px] text-muted-foreground">{{ __('Độc giả này chưa được gán tài khoản đăng nhập.') }}</p>
                            </div>
                        </div>
                    </div>

                    <p class="text-[10px] text-muted-foreground italic px-0.5" x-show="status === 'idle'">{{ __('Hệ thống tự động tìm sau 0.5s ngưng nhập hoặc nhấn icon tìm kiếm') }}</p>
                </div>
            </div>
        </div>

        <!-- Main Form Content -->
        <div class="lg:col-span-3 space-y-3">
            <!-- Part 1: Identity -->
            <div class="bg-card rounded border border-border overflow-hidden">
                <div class="px-4 py-2 border-b border-border bg-muted/30">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('1. Thông tin định danh') }}</h2>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-foreground block">{{ __('Mã độc giả') }} <span class="text-destructive">*</span></label>
                        <div class="relative">
                            <input type="text" name="patron_code" required value="{{ old('patron_code', $nextCode ?? date('ymdHis')) }}"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            @if(isset($nextCode))
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 px-1.5 py-0.5 bg-primary/10 text-[9px] font-bold text-primary rounded">{{ __('Quy tắc hệ thống') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-foreground block">{{ __('Số CMND/CCCD') }}</label>
                        <input type="text" name="id_card" value="{{ old('id_card') }}" placeholder="Ví dụ: 0123456789"
                            class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-foreground block">{{ __('MSSV') }}</label>
                        <input type="text" name="mssv" value="{{ old('mssv') }}" placeholder="Ví dụ: 20210001"
                            class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-foreground block">{{ __('Số danh bạ') }}</label>
                        <input type="text" name="phone_contact" value="{{ old('phone_contact') }}" placeholder="5339"
                            class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-foreground block">{{ __('Loại độc giả') }}</label>
                        <select name="patron_group_id" class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            @foreach($patronGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-foreground block">{{ __('Họ và tên độc giả') }} <span class="text-destructive">*</span></label>
                        <input type="text" name="display_name" required value="{{ old('display_name') }}" placeholder="NGUYEN VAN A"
                            class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                    </div>
                </div>
            </div>

            <!-- Part 2: Personal & Organization -->
            <div class="bg-card rounded border border-border overflow-hidden">
                <div class="px-4 py-2 border-b border-border bg-muted/30">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('2. Cá nhân & Đơn vị') }}</h2>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-3">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Ngày sinh') }}</label>
                            <input type="date" name="dob" value="{{ old('dob') }}"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Giới tính') }}</label>
                            <div class="flex items-center space-x-4 h-9">
                                <label class="flex items-center space-x-1.5 cursor-pointer group">
                                    <input type="radio" name="gender" value="male" class="w-3.5 h-3.5 text-primary border-border bg-background focus:ring-primary" checked>
                                    <span class="text-xs text-foreground group-hover:text-foreground/80 transition-colors">{{ __('Nam') }}</span>
                                </label>
                                <label class="flex items-center space-x-1.5 cursor-pointer group">
                                    <input type="radio" name="gender" value="female" class="w-3.5 h-3.5 text-primary border-border bg-background focus:ring-primary">
                                    <span class="text-xs text-foreground group-hover:text-foreground/80 transition-colors">{{ __('Nữ') }}</span>
                                </label>
                                <label class="flex items-center space-x-1.5 cursor-pointer group">
                                    <input type="radio" name="gender" value="other" class="w-3.5 h-3.5 text-primary border-border bg-background focus:ring-primary">
                                    <span class="text-xs text-foreground group-hover:text-foreground/80 transition-colors">{{ __('Khác') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-foreground block">{{ __('Tên trường') }}</label>
                                <input type="text" name="school_name" value="{{ old('school_name') }}"
                                    class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-foreground block">{{ __('Khóa') }}</label>
                                <input type="text" name="batch" value="{{ old('batch') }}"
                                    class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-foreground block">{{ __('Bộ phận') }}</label>
                                <input type="text" name="department" value="{{ old('department') }}"
                                    class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-foreground block">{{ __('Chức vụ/Lớp') }}</label>
                                <input type="text" name="position_class" value="{{ old('position_class') }}"
                                    class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Part 3: Contact & Auth -->
            <div class="bg-card rounded border border-border overflow-hidden">
                <div class="px-4 py-2 border-b border-border bg-muted/30">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('3. Liên lạc') }}</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Số điện thoại') }}</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Fax') }}</label>
                            <input type="text" name="fax" value="{{ old('fax') }}"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Thư điện tử (Email)') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="example@domain.com"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-3 border-t border-border">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Chi nhánh') }}</label>
                            <select name="branch" class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                <option value="all">{{ __('Tất cả chi nhánh') }}</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Phân loại') }}</label>
                            <select name="classification_type" class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                <option value="individual">{{ __('Cá nhân') }}</option>
                                <option value="group">{{ __('Tổ chức') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-border">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-foreground">{{ __('Danh sách địa chỉ') }}</label>
                            <button type="button" onclick="addAddressField()" class="text-xs font-bold text-primary hover:text-primary/80 flex items-center space-x-1">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>{{ __('Thêm địa chỉ') }}</span>
                            </button>
                        </div>
                        <div id="address-list" class="space-y-2">
                            <div class="relative flex items-center">
                                <input type="text" name="addresses[]" placeholder="{{ __('Địa chỉ chính...') }}"
                                    class="w-full pr-16 h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                <span class="absolute right-3 text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-1.5 py-0.5 rounded">{{ __('Mặc định') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Part 4: Financial & System Dates -->
            <div class="bg-card rounded border border-border overflow-hidden">
                <div class="px-4 py-2 border-b border-border bg-muted/30">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('4. Tài chính & Hệ thống') }}</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Lệ phí làm thẻ') }}</label>
                            <input type="number" name="card_fee" value="0"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Tiền thế chân') }}</label>
                            <input type="number" name="deposit" value="0"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Số dư tài khoản') }}</label>
                            <input type="number" name="balance" value="0"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-3 border-t border-border">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Ngày cập nhật') }}</label>
                            <input type="date" value="{{ date('Y-m-d') }}" disabled
                                class="w-full h-9 px-3 border border-border bg-muted text-muted-foreground rounded text-xs cursor-not-allowed">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Ngày đăng ký') }} <span class="text-destructive">*</span></label>
                            <input type="date" name="registration_date" required value="{{ date('Y-m-d') }}"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-foreground block">{{ __('Ngày hết hạn') }} <span class="text-destructive">*</span></label>
                            <input type="date" name="expiry_date" required value="{{ date('Y-m-d', strtotime('+1 year')) }}"
                                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="space-y-1 pt-3 border-t border-border">
                        <label class="text-xs font-medium text-foreground block">{{ __('Ghi chú') }}</label>
                        <textarea name="notes" rows="2" placeholder="{{ __('Nhập ghi chú thêm về độc giả...') }}"
                            class="w-full p-2 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all"></textarea>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-foreground block">{{ __('Tập tin đính kèm') }}</label>
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-24 border border-dashed border-border rounded cursor-pointer bg-muted/30 hover:bg-muted transition-all">
                                <div class="flex flex-col items-center justify-center py-4">
                                    <i data-lucide="paperclip" class="w-6 h-6 mb-1 text-muted-foreground"></i>
                                    <p class="text-xs text-muted-foreground font-bold">{{ __('Chọn file đính kèm') }}</p>
                                </div>
                                <input name="attachments" type="file" class="hidden" />
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="card_status" value="normal">

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-primary-foreground py-2.5 rounded text-xs font-bold transition-all flex items-center justify-center space-x-1.5 active:scale-[0.98] shadow-sm">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>{{ __('Initialize Identity') }}</span>
            </button>
        </div>
    </form>
</div>

<script>
    function userSearch() {
        return {
            query: '',
            loading: false,
            status: 'idle',
            selectedUser: null,
            searchResults: [],
            showDropdown: false,

            async search() {
                if (this.query.trim().length < 2) {
                    this.searchResults = [];
                    this.showDropdown = false;
                    this.status = this.selectedUser ? 'found' : 'idle';
                    return;
                }

                this.loading = true;
                try {
                    const response = await fetch(`{{ route('admin.patrons.search-users') }}?q=${encodeURIComponent(this.query)}`);
                    const data = await response.json();

                    this.searchResults = data || [];
                    if (this.searchResults.length > 0) {
                        this.showDropdown = true;
                        this.selectedUser = this.searchResults[0];
                        this.status = 'found';
                    } else {
                        this.selectedUser = null;
                        this.status = 'not_found';
                        this.showDropdown = false;
                    }
                } catch (error) {
                    console.error('Search error:', error);
                    this.status = 'not_found';
                    this.showDropdown = false;
                } finally {
                    this.loading = false;
                }
            },

            selectUser(user) {
                this.selectedUser = user;
                this.status = 'found';
                this.showDropdown = false;
                if (!user.is_linked_to_other) {
                    this.autoFill(user);
                }
            },

            autoFill(user) {
                if (user) {
                    const displayNameFields = document.getElementsByName('display_name');
                    if (displayNameFields.length > 0 && !displayNameFields[0].value.trim()) {
                        displayNameFields[0].value = user.name || '';
                    }
                    const emailFields = document.getElementsByName('email');
                    if (emailFields.length > 0 && !emailFields[0].value.trim()) {
                        emailFields[0].value = user.email || '';
                    }
                }
            },

            removeLink() {
                this.selectedUser = null;
                this.query = '';
                this.searchResults = [];
                this.showDropdown = false;
                this.status = 'idle';
            }
        }
    }

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
                document.getElementById('avatar-preview').classList.remove('hidden');
                document.getElementById('avatar-placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function removeAvatar() {
        document.getElementById('avatar-preview').src = '#';
        document.getElementById('avatar-preview').classList.add('hidden');
        document.getElementById('avatar-placeholder').classList.remove('hidden');
        document.getElementById('avatar-input').value = '';
    }

    function addAddressField() {
        const container = document.getElementById('address-list');
        const div = document.createElement('div');
        div.className = 'relative flex items-center space-x-2 animate-in fade-in duration-300';
        div.innerHTML = `
            <input type="text" name="addresses[]" placeholder="{{ __('Địa chỉ bổ sung...') }}"
                class="w-full h-9 px-3 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
            <button type="button" onclick="this.parentElement.remove()" class="p-1 text-muted-foreground hover:text-destructive transition-colors">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(div);
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({ parent: div });
        }
    }

    function autoFillRandom() {
        const randomStr = Math.random().toString(36).substring(2, 7);
        const lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Đặng', 'Bùi'];
        const middleNames = ['Văn', 'Thị', 'Hữu', 'Đức', 'Thành', 'Minh', 'Quang', 'Ngọc', 'Tuấn'];
        const firstNames = ['An', 'Bình', 'Cường', 'Dũng', 'Hải', 'Hùng', 'Linh', 'Long', 'Nam', 'Phong', 'Quân', 'Sơn', 'Tấn', 'Tú', 'Vinh'];

        const randomLastName = lastNames[Math.floor(Math.random() * lastNames.length)];
        const randomMiddleName = middleNames[Math.floor(Math.random() * middleNames.length)];
        const randomFirstName = firstNames[Math.floor(Math.random() * firstNames.length)];

        document.getElementsByName('display_name')[0].value = `${randomLastName} ${randomMiddleName} ${randomFirstName}`;
        document.getElementsByName('id_card')[0].value = '0' + Math.floor(10000000000 + Math.random() * 90000000000);
        document.getElementsByName('phone')[0].value = '09' + Math.floor(10000000 + Math.random() * 90000000);
        document.getElementsByName('email')[0].value = `user_${randomStr}@vttu.edu.vn`;
        document.getElementsByName('department')[0].value = 'CNTT';
        document.getElementsByName('position_class')[0].value = 'DHCNTT15A';
    }
</script>
@endsection
