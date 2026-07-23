@extends('layouts.admin')

@section('content')
<div class="space-y-3 animate-fade-in">
    <!-- Header -->
    <div class="bg-card p-4 border border-border rounded">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="sitemap" class="w-4 h-4 text-muted-foreground"></i>
                <h1 class="text-sm font-bold text-foreground uppercase tracking-wide">{{ __('Quản lý Website') }}</h1>
            </div>
            <a href="{{ route('admin.site-nodes.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>{{ __('Thêm') }}
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'layout', zoomImageUrl: null }" class="bg-card border border-border rounded overflow-hidden">
        <!-- Tab Navigation -->
        <div class="flex border-b border-border bg-muted/30">
            <button @click="activeTab = 'layout'; window.history.replaceState({}, '', '?tab=layout')" 
                    class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold uppercase text-muted-foreground transition-all"
                    :class="activeTab === 'layout' ? 'bg-card text-foreground border-b-2 border-primary' : 'hover:bg-muted'">
                <i data-lucide="palette" class="w-4 h-4"></i>
                <span class="hidden sm:inline">{{ __('Cấu hình giao diện') }}</span>
            </button>
            <button @click="activeTab = 'structure'; window.history.replaceState({}, '', '?tab=structure')" 
                    class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold uppercase text-muted-foreground transition-all"
                    :class="activeTab === 'structure' ? 'bg-card text-foreground border-b-2 border-primary' : 'hover:bg-muted'">
                <i data-lucide="sitemap" class="w-4 h-4"></i>
                <span class="hidden sm:inline">{{ __('Cấu trúc') }}</span>
            </button>
        </div>

        <!-- Tab Content -->
        <div class="p-4">
            <!-- Layout Tab -->
            <div x-show="activeTab === 'layout'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">
                <!-- Logo & Name Section -->
                <div class="bg-slate-50/70 dark:bg-slate-900/10 border border-slate-300 dark:border-slate-700 rounded-lg p-5 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide pb-2.5 border-b border-slate-300 dark:border-slate-700 flex items-center gap-2">
                        <i data-lucide="settings" class="w-4 h-4 text-muted-foreground"></i>
                        {{ __('Logo & Tên') }}
                    </h3>
                    
                    <form action="{{ route('admin.site-nodes.layout-settings') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        
                        <!-- Preview -->
                        <div class="p-2.5 bg-muted/20 border border-border rounded">
                            <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-wide mb-2">Xem trước</p>
                            <div class="flex items-center gap-2 p-2.5 bg-card border border-border rounded">
                                @php $currentLogo = \App\Models\SystemSetting::get('site_logo'); @endphp
                                @if($currentLogo)
                                    <img src="{{ asset('storage/' . $currentLogo) }}" alt="Logo" class="h-10 w-10 object-contain rounded border border-border bg-background">
                                @else
                                    <div class="h-10 w-10 rounded bg-muted flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="library" class="w-5 h-5 text-muted-foreground"></i>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-xs font-bold text-foreground truncate">{{ \App\Models\SystemSetting::get('site_name', 'Thư viện số') }}</h5>
                                    <p class="text-[9px] text-muted-foreground">Header</p>
                                </div>
                            </div>
                        </div>

                        <!-- Inputs -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tên') }}</label>
                                <input type="text" name="site_name"
                                       value="{{ \App\Models\SystemSetting::get('site_name', 'Thư viện số') }}"
                                       class="w-full h-9 bg-background border border-border rounded-sm px-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>

                            <div class="space-y-1.5" x-data="{ fileName: '', previewUrl: '{{ $currentLogo ? asset('storage/' . $currentLogo) : '' }}', removeLogo: false }">
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Logo') }}</label>
                                <div class="flex items-center gap-1.5">
                                    <label class="flex-1 flex items-center gap-1.5 px-2.5 h-9 bg-background border border-border border-dashed rounded-sm cursor-pointer hover:bg-muted/50 transition-all group">
                                        <i data-lucide="cloud-upload" class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors flex-shrink-0"></i>
                                        <span class="text-xs text-muted-foreground group-hover:text-foreground truncate" x-text="fileName || 'Chọn...'"></span>
                                        <input type="file" name="site_logo" accept="image/*" class="hidden"
                                               @change="fileName = $event.target.files[0]?.name; previewUrl = URL.createObjectURL($event.target.files[0]); removeLogo = false">
                                    </label>
                                    @if($currentLogo)
                                        <button type="button" @click="removeLogo = !removeLogo; previewUrl = removeLogo ? '' : '{{ asset('storage/' . $currentLogo) }}'"
                                                class="w-9 h-9 flex items-center justify-center rounded-sm transition-all border border-border"
                                                :class="removeLogo ? 'bg-red-500/10 text-red-600' : 'bg-background hover:bg-muted'">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                                        </button>
                                        <input type="hidden" name="remove_logo" :value="removeLogo ? 1 : 0">
                                    @endif
                                </div>
                                <template x-if="previewUrl">
                                    <div class="flex items-center gap-1.5 p-1.5 bg-muted/30 rounded border border-border text-[9px] text-muted-foreground">
                                        <img :src="previewUrl" class="h-6 w-6 object-contain rounded border border-border bg-background">
                                        <span>Xem trước</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Book Intro Image Section -->
                        @php $currentBookIntroImg = \App\Models\SystemSetting::get('book_intro_image'); @endphp
                        <div class="border-t border-border pt-4">
                            <h4 class="text-[10px] font-bold text-foreground uppercase tracking-wide mb-3 flex items-center gap-1.5">
                                <i data-lucide="book-open" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                {{ __('Ảnh phần Giới thiệu sách hàng tháng') }}
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div class="p-2.5 bg-muted/20 border border-border rounded flex items-center gap-2.5">
                                    @if($currentBookIntroImg)
                                        <img src="{{ asset('storage/' . $currentBookIntroImg) }}" alt="Book Intro" class="h-14 w-10 object-contain rounded border border-border bg-background shadow-sm">
                                    @else
                                        <div class="h-14 w-10 rounded bg-muted flex items-center justify-center flex-shrink-0 border border-border border-dashed">
                                            <i data-lucide="book" class="w-4 h-4 text-muted-foreground"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-xs font-bold text-foreground truncate">{{ __('Ảnh giới thiệu sách') }}</h5>
                                        <p class="text-[9px] text-muted-foreground">{{ __('Tỷ lệ 3:4 khuyên dùng') }}</p>
                                    </div>
                                </div>

                                <div class="space-y-1.5" x-data="{ fileName: '', previewUrl: '{{ $currentBookIntroImg ? asset('storage/' . $currentBookIntroImg) : '' }}', removeBookIntro: false }">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Chọn ảnh mới') }}</label>
                                    <div class="flex items-center gap-1.5">
                                        <label class="flex-1 flex items-center gap-1.5 px-2.5 h-9 bg-background border border-border border-dashed rounded-sm cursor-pointer hover:bg-muted/50 transition-all group">
                                            <i data-lucide="cloud-upload" class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors flex-shrink-0"></i>
                                            <span class="text-xs text-muted-foreground group-hover:text-foreground truncate" x-text="fileName || 'Chọn...'"></span>
                                            <input type="file" name="book_intro_image" accept="image/*" class="hidden"
                                                   @change="fileName = $event.target.files[0]?.name; previewUrl = URL.createObjectURL($event.target.files[0]); removeBookIntro = false">
                                        </label>
                                        @if($currentBookIntroImg)
                                            <button type="button" @click="removeBookIntro = !removeBookIntro; previewUrl = removeBookIntro ? '' : '{{ asset('storage/' . $currentBookIntroImg) }}'"
                                                    class="w-9 h-9 flex items-center justify-center rounded-sm transition-all border border-border"
                                                    :class="removeBookIntro ? 'bg-red-500/10 text-red-600' : 'bg-background hover:bg-muted'">
                                                <i data-lucide="trash-2" class="w-3 h-3"></i>
                                            </button>
                                            <input type="hidden" name="remove_book_intro" :value="removeBookIntro ? 1 : 0">
                                        @endif
                                    </div>
                                    <template x-if="previewUrl">
                                        <div class="flex items-center gap-1.5 p-1.5 bg-muted/30 rounded border border-border text-[9px] text-muted-foreground">
                                            <img :src="previewUrl" class="h-8 w-6 object-contain rounded border border-border bg-background">
                                            <span>Xem trước</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end pt-2 border-t border-border">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded-sm transition-all active:scale-95">
                                <i data-lucide="save" class="w-4 h-4"></i>{{ __('Lưu') }}
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Banners Section -->
                <div class="bg-white dark:bg-slate-950/20 border border-slate-300 dark:border-slate-700 rounded-lg p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-300 dark:border-slate-700">
                        <h3 class="text-xs font-bold text-foreground uppercase tracking-wide flex items-center gap-2">
                            <i data-lucide="image" class="w-4 h-4 text-muted-foreground"></i>
                            {{ __('Quản lý Banners') }}
                        </h3>
                        <button type="button" 
                                @click="document.getElementById('bannerForm').style.display = document.getElementById('bannerForm').style.display === 'none' ? 'block' : 'none'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded-sm transition-all">
                            <i data-lucide="plus" class="w-3 h-3"></i>{{ __('Thêm Banner') }}
                        </button>
                    </div>

                    <!-- Add Form -->
                    <div id="bannerForm" style="display: none;" class="mb-2.5 p-2.5 bg-muted/20 border border-border border-dashed rounded space-y-2.5" x-data="{ fileName: '', previewUrl: '' }">
                        <form action="{{ route('admin.site-nodes.add-banner') }}" method="POST" enctype="multipart/form-data" class="space-y-2.5">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tên Banner') }} *</label>
                                    <input type="text" name="title" required placeholder="Tên Banner" class="w-full h-9 bg-background border border-border rounded-sm px-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:ring-1 focus:ring-primary outline-none transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Link URL Redirect') }} *</label>
                                    <input type="url" name="link_url" required placeholder="https://..." class="w-full h-9 bg-background border border-border rounded-sm px-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:ring-1 focus:ring-primary outline-none transition-all">
                                </div>
                                <div class="space-y-1.5 sm:col-span-2">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Ảnh Banner') }} *</label>
                                    <label class="flex items-center gap-1.5 px-2.5 h-9 bg-background border border-border border-dashed rounded-sm cursor-pointer hover:bg-muted/50 transition-all group">
                                        <i data-lucide="cloud-upload" class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors flex-shrink-0"></i>
                                        <span class="text-xs text-muted-foreground group-hover:text-foreground truncate" x-text="fileName || 'Chọn ảnh...'"></span>
                                        <input type="file" name="image_path" accept="image/*" required class="hidden" @change="fileName = $event.target.files[0]?.name; previewUrl = URL.createObjectURL($event.target.files[0])">
                                    </label>
                                    <template x-if="previewUrl">
                                        <div class="flex items-center gap-1.5 p-1.5 bg-muted/30 rounded border border-border text-[9px] text-muted-foreground">
                                            <img :src="previewUrl" @click="zoomImageUrl = previewUrl" class="h-10 w-20 object-contain rounded border border-border bg-background cursor-zoom-in hover:opacity-95 transition-all" title="Bấm để phóng to">
                                            <span class="cursor-pointer font-bold" @click="zoomImageUrl = previewUrl">Xem trước (Click để phóng to)</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="flex justify-end gap-1.5 pt-2 border-t border-border">
                                <button type="button" @click="document.getElementById('bannerForm').style.display = 'none'" class="px-3 py-1.5 bg-muted hover:bg-muted/80 text-muted-foreground text-xs font-bold rounded-sm transition-all">{{ __('Hủy') }}</button>
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded-sm transition-all active:scale-95">
                                    <i data-lucide="plus" class="w-3 h-3"></i>{{ __('Thêm') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- List Banners -->
                    @php $banners = \App\Models\Banner::orderBy('sort_order')->orderBy('created_at', 'desc')->get(); @endphp
                    @if($banners->count() > 0)
                        <div class="space-y-0 border border-border rounded overflow-hidden">
                            @foreach($banners as $banner)
                                <div class="flex items-center gap-2 p-2.5 bg-card border-b border-border last:border-b-0 hover:bg-muted/50 transition-all group">
                                    @if($banner->image_url && file_exists(storage_path('app/public/' . $banner->image_url)))
                                        <img src="{{ asset('storage/' . $banner->image_url) }}" 
                                             @click="zoomImageUrl = '{{ asset('storage/' . $banner->image_url) }}'"
                                             alt="{{ $banner->title }}" 
                                             class="h-12 w-24 object-contain rounded-sm border border-border bg-background flex-shrink-0 cursor-zoom-in hover:opacity-95 transition-all" 
                                             title="Bấm để phóng to">
                                    @else
                                        <div class="h-12 w-24 rounded-sm bg-muted flex items-center justify-center flex-shrink-0"><i data-lucide="image" class="w-4 h-4 text-muted-foreground"></i></div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <h5 class="text-xs font-bold text-foreground truncate">{{ $banner->title }}</h5>
                                            <span id="banner-badge-{{ $banner->id }}" class="inline-flex items-center px-1.5 py-0.5 rounded-sm text-[8px] font-bold uppercase tracking-wider {{ $banner->status === 'active' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-destructive/10 text-destructive border-destructive/20' }} border">
                                                {{ $banner->status === 'active' ? 'Hoạt động' : 'Ẩn' }}
                                            </span>
                                        </div>
                                        <a href="{{ $banner->link_url }}" target="_blank" class="text-[9px] text-blue-600 hover:underline truncate block">{{ $banner->link_url }}</a>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" 
                                                id="banner-btn-{{ $banner->id }}"
                                                onclick="toggleBannerStatus({{ $banner->id }})"
                                                class="p-1.5 rounded-sm {{ $banner->status === 'active' ? 'bg-amber-500/10 hover:bg-amber-500 text-amber-600 hover:text-white border-amber-500/20' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-600 hover:text-white border-emerald-500/20' }} transition-all border" 
                                                title="{{ $banner->status === 'active' ? __('Ẩn banner') : __('Hiện banner') }}">
                                            <i data-lucide="{{ $banner->status === 'active' ? 'eye-off' : 'eye' }}" class="w-3 h-3"></i>
                                        </button>
                                        <form action="{{ route('admin.site-nodes.delete-banner', $banner->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Xác nhận xóa banner này?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-sm bg-red-500/10 hover:bg-red-500 text-red-600 hover:text-white transition-all border border-red-500/20" title="{{ __('Xóa banner') }}"><i data-lucide="trash-2" class="w-3 h-3"></i></button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center p-4 bg-muted/20 border border-border border-dashed rounded text-center">
                            <i data-lucide="image" class="w-5 h-5 text-muted-foreground mb-1 opacity-50"></i>
                            <p class="text-xs text-muted-foreground">{{ __('Chưa có banner nào') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Network Logos Section -->
                <div class="bg-slate-50/70 dark:bg-slate-900/10 border border-slate-300 dark:border-slate-700 rounded-lg p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-300 dark:border-slate-700">
                        <h3 class="text-xs font-bold text-foreground uppercase tracking-wide flex items-center gap-2">
                            <i data-lucide="network" class="w-4 h-4 text-muted-foreground"></i>
                            {{ __('Nhãn hiệu liên kết') }}
                        </h3>
                        <button type="button" 
                                @click="document.getElementById('networkLogoForm').style.display = document.getElementById('networkLogoForm').style.display === 'none' ? 'block' : 'none'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded-sm transition-all">
                            <i data-lucide="plus" class="w-3 h-3"></i>{{ __('Thêm') }}
                        </button>
                    </div>

                    <!-- Add Form -->
                    <div id="networkLogoForm" style="display: none;" class="mb-2.5 p-2.5 bg-muted/20 border border-border border-dashed rounded space-y-2.5" x-data="{ fileName: '', previewUrl: '' }">
                        <form action="{{ route('admin.site-nodes.add-network-logo') }}" method="POST" enctype="multipart/form-data" class="space-y-2.5">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tên') }} *</label>
                                    <input type="text" name="name" required placeholder="Tên thư viện" class="w-full h-9 bg-background border border-border rounded-sm px-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:ring-1 focus:ring-primary outline-none transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('URL') }} *</label>
                                    <input type="url" name="url" required placeholder="https://..." class="w-full h-9 bg-background border border-border rounded-sm px-2.5 text-xs text-foreground placeholder:text-muted-foreground focus:ring-1 focus:ring-primary outline-none transition-all">
                                </div>
                                <div class="space-y-1.5 sm:col-span-2">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Logo') }} *</label>
                                    <label class="flex items-center gap-1.5 px-2.5 h-9 bg-background border border-border border-dashed rounded-sm cursor-pointer hover:bg-muted/50 transition-all group">
                                        <i data-lucide="cloud-upload" class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors flex-shrink-0"></i>
                                        <span class="text-xs text-muted-foreground group-hover:text-foreground truncate" x-text="fileName || 'Chọn...'"></span>
                                        <input type="file" name="logo_path" accept="image/*" required class="hidden" @change="fileName = $event.target.files[0]?.name; previewUrl = URL.createObjectURL($event.target.files[0])">
                                    </label>
                                    <template x-if="previewUrl">
                                        <div class="flex items-center gap-1.5 p-1.5 bg-muted/30 rounded border border-border text-[9px] text-muted-foreground">
                                            <img :src="previewUrl" class="h-6 w-6 object-contain rounded border border-border bg-background">
                                            <span>Xem trước</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="flex justify-end gap-1.5 pt-2 border-t border-border">
                                <button type="button" @click="document.getElementById('networkLogoForm').style.display = 'none'" class="px-3 py-1.5 bg-muted hover:bg-muted/80 text-muted-foreground text-xs font-bold rounded-sm transition-all">{{ __('Hủy') }}</button>
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded-sm transition-all active:scale-95">
                                    <i data-lucide="plus" class="w-3 h-3"></i>{{ __('Thêm') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- List -->
                    @php $networkLogos = \App\Models\LibraryNetworkLogo::orderBy('sort_order')->get(); @endphp
                    @if($networkLogos->count() > 0)
                        <div class="space-y-0 border border-border rounded overflow-hidden">
                            @foreach($networkLogos as $logo)
                                <div class="flex items-center gap-2 p-2.5 bg-card border-b border-border last:border-b-0 hover:bg-muted/50 transition-all group">
                                    @if($logo->logo_path && file_exists(storage_path('app/public/' . $logo->logo_path)))
                                        <img src="{{ asset('storage/' . $logo->logo_path) }}" alt="{{ $logo->name }}" class="h-8 w-8 object-contain rounded-sm border border-border bg-background flex-shrink-0">
                                    @else
                                        <div class="h-8 w-8 rounded-sm bg-muted flex items-center justify-center flex-shrink-0"><i data-lucide="image" class="w-3 h-3 text-muted-foreground"></i></div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-xs font-bold text-foreground truncate">{{ $logo->name }}</h5>
                                        <a href="{{ $logo->url }}" target="_blank" class="text-[9px] text-blue-600 hover:underline truncate block">{{ $logo->url }}</a>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" @click="editLogo({{ $logo->id }}, '{{ $logo->name }}', '{{ $logo->url }}', '{{ asset('storage/' . $logo->logo_path) }}')" class="p-1.5 rounded-sm bg-blue-500/10 hover:bg-blue-500 text-blue-600 hover:text-white transition-all border border-blue-500/20"><i data-lucide="edit-2" class="w-3 h-3"></i></button>
                                        <form action="{{ route('admin.site-nodes.delete-network-logo', $logo->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Xác nhận?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-sm bg-red-500/10 hover:bg-red-500 text-red-600 hover:text-white transition-all border border-red-500/20"><i data-lucide="trash-2" class="w-3 h-3"></i></button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center p-4 bg-muted/20 border border-border border-dashed rounded text-center">
                            <i data-lucide="image" class="w-5 h-5 text-muted-foreground mb-1 opacity-50"></i>
                            <p class="text-xs text-muted-foreground">{{ __('Chưa có') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Structure Tab -->
            <div x-show="activeTab === 'structure'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-2 mb-3">
                    <div class="bg-muted/50 p-3 border border-border rounded">
                        <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-wide mb-0.5">Tổng số</p>
                        <p class="text-lg font-black text-foreground">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-muted/50 p-3 border border-border rounded">
                        <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-wide mb-0.5">Hoạt động</p>
                        <p class="text-lg font-black text-foreground">{{ $stats['published'] ?? 0 }}</p>
                    </div>
                    <div class="bg-muted/50 p-3 border border-border rounded">
                        <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-wide mb-0.5">Bản nháp</p>
                        <p class="text-lg font-black text-foreground">{{ $stats['draft'] ?? 0 }}</p>
                    </div>
                </div>

                <!-- Tree Section Header -->
                <div class="flex items-center justify-between mb-2.5 pb-2.5 border-b border-border">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide flex items-center gap-2">
                        <i data-lucide="sitemap" class="w-4 h-4 text-muted-foreground"></i>
                        {{ __('Cấu trúc Website') }}
                    </h3>
                    <div class="flex items-center gap-1.5">
                        <button onclick="expandAll()" class="p-1.5 hover:bg-muted rounded-sm text-muted-foreground hover:text-foreground transition-all border border-border" title="Mở hết">
                            <i data-lucide="expand" class="w-3 h-3"></i>
                        </button>
                        <button onclick="collapseAll()" class="p-1.5 hover:bg-muted rounded-sm text-muted-foreground hover:text-foreground transition-all border border-border" title="Thu hết">
                            <i data-lucide="shrink" class="w-3 h-3"></i>
                        </button>
                        <a href="{{ route('admin.site-nodes.create') }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded-sm transition-all">
                            <i data-lucide="plus" class="w-3 h-3"></i>{{ __('Thêm') }}
                        </a>
                    </div>
                </div>

                @if(count($tree) > 0)
                    <div id="site-tree" class="space-y-0.5">
                        @include('admin.site-nodes.tree', ['nodes' => $tree, 'level' => 0])
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <i data-lucide="sitemap" class="w-6 h-6 text-muted-foreground mb-2 opacity-50"></i>
                        <p class="text-xs text-muted-foreground mb-2">{{ __('Chưa có') }}</p>
                        <a href="{{ route('admin.site-nodes.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded-sm">
                            <i data-lucide="plus" class="w-4 h-4"></i>{{ __('Tạo') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Zoom Image Modal -->
        <div x-show="zoomImageUrl" 
             class="fixed inset-0 bg-black/80 z-[100] flex items-center justify-center p-4 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="zoomImageUrl = null"
             style="display: none;">
            <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center justify-center" @click.stop>
                <button type="button" @click="zoomImageUrl = null" class="absolute -top-10 right-0 p-2 text-white/70 hover:text-white transition-all">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
                <img :src="zoomImageUrl" class="max-w-full max-h-[85vh] object-contain rounded border border-border bg-card shadow-2xl">
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editLogoModal" style="display: none;" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm" @click.self="document.getElementById('editLogoModal').style.display = 'none'">
    <div class="bg-card rounded border border-border shadow-2xl max-w-sm w-full" x-data="{ editFileName: '', editPreviewUrl: '' }">
        <div class="flex items-center justify-between p-3 border-b border-border bg-muted/30">
            <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">{{ __('Chỉnh sửa') }}</h3>
            <button type="button" @click="document.getElementById('editLogoModal').style.display = 'none'" class="p-1 hover:bg-muted rounded transition-all">
                <i data-lucide="x" class="w-4 h-4 text-muted-foreground"></i>
            </button>
        </div>
        <form id="editLogoForm" method="POST" enctype="multipart/form-data" class="p-3 space-y-2.5">
            @csrf
            @method('POST')
            <input type="hidden" id="logoId" name="logo_id">
            <div class="space-y-1.5">
                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tên') }} *</label>
                <input type="text" id="editName" name="name" required class="w-full h-9 bg-background border border-border rounded-sm px-2.5 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('URL') }} *</label>
                <input type="url" id="editUrl" name="url" required class="w-full h-9 bg-background border border-border rounded-sm px-2.5 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div class="space-y-1.5">
                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Logo') }}</label>
                <label class="flex items-center gap-1.5 px-2.5 h-9 bg-background border border-border border-dashed rounded-sm cursor-pointer hover:bg-muted/50 transition-all group">
                    <i data-lucide="cloud-upload" class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors flex-shrink-0"></i>
                    <span class="text-xs text-muted-foreground group-hover:text-foreground truncate" x-text="editFileName || 'Chọn...'"></span>
                    <input type="file" name="logo_path" accept="image/*" class="hidden" @change="editFileName = $event.target.files[0]?.name; editPreviewUrl = URL.createObjectURL($event.target.files[0])">
                </label>
            </div>
            <div id="currentLogoPreview" class="flex items-center gap-1.5 p-1.5 bg-muted/30 rounded border border-border text-[9px] text-muted-foreground">
                <img id="currentLogoImg" src="" alt="Logo" class="h-6 w-6 object-contain rounded border border-border bg-background">
                <span>Hiện tại</span>
            </div>
            <div id="newLogoPreview" style="display: none;" class="flex items-center gap-1.5 p-1.5 bg-muted/30 rounded border border-border text-[9px] text-muted-foreground" x-data>
                <img :src="editPreviewUrl" alt="New" class="h-6 w-6 object-contain rounded border border-border bg-background">
                <span>Mới</span>
            </div>
            <div class="flex justify-end gap-1.5 pt-2 border-t border-border">
                <button type="button" @click="document.getElementById('editLogoModal').style.display = 'none'" class="px-3 py-1.5 bg-muted hover:bg-muted/80 text-muted-foreground text-xs font-bold rounded-sm transition-all">{{ __('Hủy') }}</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded-sm transition-all active:scale-95">
                    <i data-lucide="save" class="w-3 h-3"></i>{{ __('Lưu') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .tree-children { display: block; }
    .hidden { display: none; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>
function toggleNode(nodeId) {
    const children = document.getElementById(`children-${nodeId}`);
    const toggle = document.getElementById(`toggle-${nodeId}`);
    if (children) {
        children.classList.toggle('hidden');
        const icon = toggle.querySelector('i');
        icon.style.transform = children.classList.contains('hidden') ? 'rotate(-90deg)' : 'rotate(0deg)';
    }
}
function expandAll() {
    document.querySelectorAll('.tree-children').forEach(c => c.classList.remove('hidden'));
    document.querySelectorAll('[id^="toggle-"] i').forEach(i => i.style.transform = 'rotate(0deg)');
}
function collapseAll() {
    document.querySelectorAll('.tree-children').forEach(c => c.classList.add('hidden'));
    document.querySelectorAll('[id^="toggle-"] i').forEach(i => i.style.transform = 'rotate(-90deg)');
}
function editLogo(logoId, logoName, logoUrl, logoPath) {
    document.getElementById('logoId').value = logoId;
    document.getElementById('editName').value = logoName;
    document.getElementById('editUrl').value = logoUrl;
    document.getElementById('currentLogoImg').src = logoPath;
    document.getElementById('editLogoForm').action = `/topsecret/site-nodes/network-logo/${logoId}`;
    document.querySelector('#editLogoForm input[name="logo_path"]').value = '';
    document.getElementById('newLogoPreview').style.display = 'none';
    document.getElementById('editLogoModal').style.display = 'flex';
}
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    const fileInput = document.querySelector('#editLogoForm input[name="logo_path"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const preview = document.getElementById('newLogoPreview');
            const img = preview.querySelector('img');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { img.src = e.target.result; preview.style.display = 'flex'; };
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.style.display = 'none';
            }
        });
    }
});
function showToast(message, type = 'success') {
    // Dispatch Alpine toast event configured in admin layout
    window.dispatchEvent(new CustomEvent('toast', { 
        detail: { message: message, type: type } 
    }));

    // Fallback to SweetAlert2 Toast if present
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
        Toast.fire({
            icon: type === 'danger' || type === 'error' ? 'error' : 'success',
            title: message
        });
    }
}

function toggleStatus(nodeId) {
    fetch(`/topsecret/site-nodes/${nodeId}/toggle-status`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')}
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const badge = document.getElementById(`status-${nodeId}`);
            if (badge) {
                badge.className = `inline-flex items-center px-2 py-1 rounded-sm text-[9px] font-bold text-white ${d.is_active ? 'bg-green-600' : 'bg-slate-400'}`;
                badge.textContent = d.is_active ? 'Hoạt động' : 'Ẩn';
            }
            showToast(d.message || 'Cập nhật trạng thái node thành công!', 'success');
        } else {
            showToast(d.message || 'Lỗi khi cập nhật trạng thái', 'error');
        }
    })
    .catch(err => {
        showToast('Lỗi kết nối khi cập nhật trạng thái', 'error');
    });
}

function toggleBannerStatus(bannerId) {
    const btn = document.getElementById(`banner-btn-${bannerId}`);
    if (btn) btn.disabled = true;

    fetch(`/topsecret/site-nodes/banner/${bannerId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const badge = document.getElementById(`banner-badge-${bannerId}`);
            const isActive = d.is_active;

            if (badge) {
                badge.className = `inline-flex items-center px-1.5 py-0.5 rounded-sm text-[8px] font-bold uppercase tracking-wider ${isActive ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-destructive/10 text-destructive border-destructive/20'} border`;
                badge.textContent = isActive ? 'Hoạt động' : 'Ẩn';
            }

            if (btn) {
                btn.className = `p-1.5 rounded-sm ${isActive ? 'bg-amber-500/10 hover:bg-amber-500 text-amber-600 hover:text-white border-amber-500/20' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-600 hover:text-white border-emerald-500/20'} transition-all border`;
                btn.title = isActive ? 'Ẩn banner' : 'Hiện banner';
                btn.innerHTML = `<i data-lucide="${isActive ? 'eye-off' : 'eye'}" class="w-3 h-3"></i>`;
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            }

            showToast(d.message || 'Cập nhật trạng thái banner thành công!', 'success');
        } else {
            showToast(d.message || 'Lỗi khi cập nhật trạng thái banner', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Lỗi kết nối khi cập nhật trạng thái banner', 'error');
    })
    .finally(() => {
        if (btn) btn.disabled = false;
    });
}
</script>
@endpush
@endsection
