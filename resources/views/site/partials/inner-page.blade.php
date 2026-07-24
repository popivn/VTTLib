{{--
    Shared inner page layout - Dùng chung cho các trang con mục Giới thiệu

    Params:
    - $node         (required) SiteNode hiện tại
    - $accent       (optional) Màu chủ đạo: blue, indigo, amber, emerald, violet
    - $badgeText    (optional) Text badge trên header
    - $badgeIcon    (optional) Icon class cho badge
    - $sectionLabel (optional) Tiêu đề sidebar
--}}

@php
    $accent       = $accent ?? 'primary';
    $badgeText    = $badgeText ?? $node->display_name;
    $badgeIcon    = $badgeIcon ?? ($node->icon ?? 'circle-info');

    // Check if current node is a guide under co-so-du-lieu
    $isGuide = str_starts_with($node->node_code, 'guide-') || ($node->parent && $node->parent->node_code === 'co-so-du-lieu');

    if ($isGuide) {
        $sectionLabel = ($node->parent && $node->parent->parent) ? $node->parent->parent->display_name : ($node->parent ? $node->parent->display_name : $node->display_name);
    } else {
        $sectionLabel = ($node->parent) ? $node->parent->display_name : $node->display_name;
    }

    // Sidebar: lấy các trang anh em
    $sidebarItems = collect();
    if ($isGuide) {
        $refNode = $node->parent;
        if ($refNode && $refNode->parent) {
            $sidebarItems = $refNode->parent->activeChildren()->orderBy('sort_order')->get();
        } else if ($refNode) {
            $sidebarItems = $refNode->activeChildren()->orderBy('sort_order')->get();
        }
    } else {
        if ($node->parent) {
            $sidebarItems = $node->parent->activeChildren()->orderBy('sort_order')->get();
        } else {
            $sidebarItems = $node->activeChildren()->orderBy('sort_order')->get();
        }
    }
    if ($sidebarItems->count() === 0) {
        $sidebarItems = collect([$node]);
    }

    // Lucide icon mapping from FA if possible, or fallback to database icon
    function getLucideIcon($faIcon) {
        $map = [
            'fas fa-circle-info'    => 'info',
            'fas fa-info-circle'    => 'info',
            'fas fa-bullseye'       => 'target',
            'fas fa-scale-balanced' => 'scale',
            'fas fa-clock'          => 'clock',
            'fas fa-sitemap'        => 'network',
            'fas fa-compass'        => 'compass',
            'fas fa-home'           => 'home',
            'fas fa-search'         => 'search',
            'fas fa-phone'          => 'phone',
            'fas fa-university'     => 'landmark',
            'fas fa-concierge-bell' => 'bell',
            'fas fa-headset'        => 'headset',
            'fas fa-cloud'          => 'cloud',
            'fas fa-book-open-reader' => 'book-open',
            'fas fa-mobile-screen'  => 'smartphone',
            'fas fa-sign-in-alt'    => 'log-in',
            'fas fa-key'            => 'key',
            'fas fa-book-journal-whills' => 'book-open',
            'fas fa-file-pdf'       => 'file-text',
            'fas fa-calendar-check' => 'calendar-check',
            'fas fa-plus-circle'    => 'plus-circle',
            'fas fa-layer-group'    => 'layers',
            'fas fa-book'           => 'book',
            'fas fa-database'       => 'database',
            'fas fa-globe'          => 'globe',
            'fas fa-newspaper'      => 'newspaper',
            'fas fa-tablet-alt'     => 'tablet',
            'fas fa-graduation-cap' => 'graduation-cap',
            'fas fa-video'          => 'video',
            'fas fa-poll'           => 'bar-chart-3',
            'fas fa-map-marker-alt' => 'map-pin',
            'fas fa-arrow-left'     => 'arrow-left',
            'fas fa-arrow-right'    => 'arrow-right',
        ];
        return $map[$faIcon] ?? 'file-text';
    }
@endphp

<div class="min-h-screen bg-background text-foreground animate-fade-in pt-16" x-data="{ sidebarOpen: true }">

    <!-- Floating Expand Button when Sidebar is Collapsed -->
    <div x-show="!sidebarOpen"
         class="fixed left-0 top-1/2 -translate-y-1/2 z-[100]"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         x-cloak>
        <button @click="sidebarOpen = true; if(window.lucide) lucide.createIcons();"
                class="flex items-center justify-center w-10 h-12 bg-vttu-red text-white rounded-r-lg shadow-lg hover:bg-vttu-dark active:scale-95 transition-all border-y border-r border-white/20"
                title="{{ __('Mở rộng Sidebar') }}">
            <i data-lucide="panel-left-open" class="w-5 h-5"></i>
        </button>
    </div>

    <div class="w-full px-4 py-4 mt-[6px] md:px-6 md:py-6">
        <div class="flex flex-col lg:flex-row gap-4">
            
            <!-- Sidebar -->
            <aside class="lg:w-72 space-y-4 order-2 lg:order-1 transition-all duration-300 overflow-hidden lg:sticky lg:top-20 lg:self-start h-fit"
                   x-show="sidebarOpen"
                   x-transition:enter="transition ease-out duration-300"
                   x-transition:enter-start="opacity-0 -translate-x-full"
                   x-transition:enter-end="opacity-100 translate-x-0"
                   x-transition:leave="transition ease-in duration-200"
                   x-transition:leave-start="opacity-100 translate-x-0"
                   x-transition:leave-end="opacity-0 -translate-x-full">
                <!-- Navigation Card -->
                <div class="bg-card text-card-foreground border border-border rounded-md shadow-sm overflow-hidden">
                    <div class="p-3 bg-vttu-red border-b border-vttu-red/20 shadow-sm relative overflow-hidden group flex items-center justify-between">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-white/10 blur-xl rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-700"></div>
                        <div class="flex items-center gap-2 relative z-10">
                            <div class="w-1 h-4 bg-vttu-yellow rounded-full"></div>
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-white">{{ $sectionLabel }}</h3>
                        </div>
                        <button @click="sidebarOpen = false; if(window.lucide) lucide.createIcons();"
                                class="relative z-10 p-1.5 text-white/80 hover:text-white rounded hover:bg-white/10 active:scale-95 transition-all flex items-center justify-center"
                                title="{{ __('Thu gọn Sidebar') }}">
                            <i data-lucide="panel-left-close" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <nav class="p-2 space-y-1">
                        @foreach($sidebarItems as $item)
                            @php 
                                $active = ($item->id === $node->id) 
                                    || ($isGuide && $item->node_code === 'co-so-du-lieu')
                                    || (isset($category) && $item->node_code === $category->slug)
                                    || (isset($news) && $news instanceof \App\Models\News && $news->category && $item->node_code === $news->category->slug);
                            @endphp
                            <a href="{{ $item->getUrl() }}"
                               class="flex items-center gap-3 px-3 py-2 rounded text-sm transition-all relative group
                                      {{ $active 
                                         ? 'bg-vttu-red text-white font-bold shadow-md shadow-vttu-red/20' 
                                         : 'text-muted-foreground hover:bg-vttu-red/10 hover:text-vttu-red active:bg-vttu-red active:text-white active:scale-[0.98]' }}">
                                
                                @if(str_starts_with($item->icon ?? '', 'fa') || str_contains($item->icon ?? '', 'fa-'))
                                    <div class="w-8 h-8 rounded-sm {{ !$active ? 'bg-gradient-to-br from-vttu-red to-vttu-dark shadow-vttu-red/25' : '' }} flex items-center justify-center flex-shrink-0 transition-all group-hover:scale-110 group-active:scale-95">
                                        <i class="{{ $item->icon }} text-xs {{ $active ? 'text-vttu-yellow' : 'text-white' }}"></i>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-sm {{ !$active ? 'bg-gradient-to-br from-vttu-red to-vttu-dark shadow-vttu-red/25' : '' }} flex items-center justify-center flex-shrink-0 transition-all group-hover:scale-110 group-active:scale-95">
                                        <i data-lucide="{{ getLucideIcon($item->icon) }}" class="w-4 h-4 {{ $active ? 'text-vttu-yellow' : 'text-white' }}"></i>
                                    </div>
                                @endif
                                
                                <span class="truncate transition-colors">{{ $item->display_name }}</span>
                                @if($active)
                                    <i data-lucide="chevron-right" class="w-3 h-3 ml-auto text-vttu-yellow/70 group-hover:translate-x-0.5 transition-transform"></i>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>

                <!-- CTA Card -->
                <div class="bg-gradient-to-br from-vttu-red/10 to-vttu-dark/10 border border-vttu-red/20 rounded-md p-4 text-center space-y-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-vttu-red to-vttu-dark rounded-full flex items-center justify-center mx-auto text-vttu-yellow shadow-lg shadow-vttu-red/20">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-foreground italic">{{ __('Tra cứu OPAC') }}</h4>
                        <p class="text-xs text-muted-foreground leading-relaxed mt-1">{{ __('Tìm kiếm tài liệu trực tuyến') }}</p>
                    </div>
                    <a href="{{ route('site.opac') }}"
                       class="inline-flex items-center justify-center w-full px-4 py-2 bg-vttu-yellow text-vttu-dark text-xs font-black rounded shadow-sm hover:bg-yellow-400 active:scale-[0.98] transition-all">
                        {{ __('Tra cứu ngay') }} <i data-lucide="arrow-right" class="w-3 h-3 ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 space-y-4 order-1 lg:order-2 transition-all duration-300">
                @if($node->node_code === 'tai-lieu-so' || $node->masterpage === 'digital-resources')
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    class="p-1.5 rounded bg-muted hover:bg-primary/10 hover:text-primary active:scale-95 transition-all border border-border shadow-sm group"
                                    title="{{ __('Thu gọn/Mở rộng Sidebar') }}">
                                <i data-lucide="panel-left-close" class="w-3.5 h-3.5 transition-transform duration-300" :class="!sidebarOpen && 'rotate-180'"></i>
                            </button>

                            <div class="w-1 h-3.5 bg-vttu-red rounded-full ml-1"></div>
                            <h2 class="text-xs font-black uppercase tracking-widest text-vttu-dark">{{ __('Tài nguyên') }}</h2>
                        </div>
                    </div>
                    
                    @if(request()->routeIs('site.digital-resources.view'))
                        @include('site.pages.partials.digital-resource-view-content')
                    @elseif(isset($resource))
                        @include('site.pages.partials.digital-resource-detail-content')
                    @else
                        @include('site.pages.partials.digital-list-content')
                    @endif
                @elseif($node->node_code === 'chuong-trinh-dao-tao-vttu' || $node->node_code === 'khung-chuong-trinh-dao-tao')
                    @include('site.pages.partials.curriculum-content')
                @elseif($node->node_code === 'huong-dan')
                    @include('site.pages.huong-dan-content')
                @elseif($node->node_code === 'tai-nguyen-giao-duc-mo' || $node->masterpage === 'oer')
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    class="p-1.5 rounded bg-muted hover:bg-primary/10 hover:text-primary active:scale-95 transition-all border border-border shadow-sm group"
                                    title="{{ __('Thu gọn/Mở rộng Sidebar') }}">
                                <i data-lucide="panel-left-close" class="w-3.5 h-3.5 transition-transform duration-300" :class="!sidebarOpen && 'rotate-180'"></i>
                            </button>

                            <div class="w-1 h-3.5 bg-vttu-red rounded-full ml-1"></div>
                            <h2 class="text-xs font-black uppercase tracking-widest text-vttu-dark">{{ __('Tài nguyên giáo dục mở') }}</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('site.oer.landing') }}" class="text-xs font-bold text-slate-600 hover:text-vttu-red transition-colors uppercase tracking-wider">{{ __('Trang chủ OER') }}</a>
                        </div>
                    </div>
                    
                    @if(request()->query('view') === 'landing')
                        @include('site.pages.partials.oer-landing-content')
                    @elseif(request()->query('view') === 'intro')
                        @include('site.pages.partials.oer-intro-content')
                    @elseif(request()->query('view') === 'contribute')
                        @include('site.pages.partials.oer-contribute-content')
                    @else
                        @include('site.pages.partials.oer-list-content')
                    @endif
                @elseif(isset($customContent) && $customContent === true)
                    @php
                        $openingTimeWeekday = \App\Models\SystemSetting::get('opening_time_weekday');
                        $closingTimeWeekday = \App\Models\SystemSetting::get('closing_time_weekday');
                        $openingTimeFormatted = $openingTimeWeekday ? date('H:i', strtotime($openingTimeWeekday)) : '';
                        $closingTimeFormatted = $closingTimeWeekday ? date('H:i', strtotime($closingTimeWeekday)) : '';
                        
                        $sundayHoliday = \App\Models\SystemSetting::get('sunday_holiday_hours');
                        $serviceNote = \App\Models\SystemSetting::get('service_hours_note');
                        
                        $phone = \App\Models\SystemSetting::get('phone');
                        $email = \App\Models\SystemSetting::get('email');
                        $address = \App\Models\SystemSetting::get('address');
                        $libraryNameVi = \App\Models\SystemSetting::get('library_name_vi');
                    @endphp
                    <!-- Custom Content từ page -->
                    <!-- Hero Section with Background Image -->
                    <div class="relative rounded-2xl overflow-hidden shadow-xl mb-8 min-h-[480px] flex items-center justify-center p-6 md:p-10"
                         style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.75) 0%, rgba(15, 23, 42, 0.55) 100%), url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=1200&h=600&fit=crop'); background-size: cover; background-position: center;">
                        
                        <!-- Content Box -->
                        <div class="w-full flex flex-col items-center justify-center text-center relative z-10">
                            <!-- Header Text -->
                            <div class="mb-4">
                                <div class="inline-block px-4 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 mb-2">
                                    <h1 class="text-xs md:text-sm font-bold text-vttu-yellow tracking-widest uppercase">
                                        THƯ VIỆN {{ $libraryNameVi }}
                                    </h1>
                                </div>
                            </div>

                            <!-- Main Title -->
                            <div class="mb-6">
                                <h2 class="text-2xl md:text-4xl font-black text-white tracking-widest uppercase drop-shadow-md">
                                    {{ __('THỜI GIAN PHỤC VỤ') }}
                                </h2>
                                <div class="w-16 h-1 bg-vttu-yellow mx-auto mt-2 rounded-full"></div>
                            </div>

                            <!-- Clock Icon Box -->
                            <div class="relative mb-6">
                                <div class="w-20 h-20 bg-gradient-to-br from-vttu-red via-vttu-red to-vttu-dark rounded-full flex items-center justify-center border-4 border-white shadow-2xl ring-4 ring-white/20">
                                    <div class="text-vttu-yellow text-3xl animate-pulse">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- 2 Columns Info Boxes Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full max-w-2xl mx-auto">
                                <!-- Info Box 1: Mon-Sat -->
                                <div class="bg-white/95 backdrop-blur border-2 border-vttu-red rounded-xl p-5 shadow-lg flex flex-col justify-center items-center">
                                    <p class="text-xs font-black uppercase tracking-wider mb-2 text-vttu-dark">
                                        {{ __('Thứ Hai - Thứ Bảy') }}
                                    </p>
                                    <p class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight">
                                        {{ $openingTimeFormatted }} - {{ $closingTimeFormatted }}
                                    </p>
                                </div>

                                <!-- Info Box 2: Sunday & Holidays -->
                                <div class="bg-white/95 backdrop-blur border-2 border-vttu-red rounded-xl p-5 shadow-lg flex flex-col justify-center items-center">
                                    <p class="text-xs font-black uppercase tracking-wider mb-2 text-vttu-dark">
                                        {{ __('Chủ nhật & Ngày Lễ') }}
                                    </p>
                                    <p class="text-lg lg:text-xl font-black text-vttu-red tracking-wide uppercase">
                                        {{ $sundayHoliday }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        <!-- Box 1 -->
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white flex-shrink-0">
                                    <i class="fas fa-calendar-days text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">{{ __('Ngày làm việc') }}</h4>
                                    <p class="text-gray-700 text-sm leading-relaxed">
                                        {{ __('Từ Thứ Hai đến Thứ Bảy, Thư viện mở cửa phục vụ bạn đọc trong suốt thời gian từ') }} <strong>{{ $openingTimeFormatted }}</strong> {{ __('đến') }} <strong>{{ $closingTimeFormatted }}</strong>{{ __('.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Box 2 -->
                        <div class="bg-gradient-to-br from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center text-white flex-shrink-0">
                                    <i class="fas fa-ban text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">{{ __('Ngày nghỉ') }}</h4>
                                    <p class="text-gray-700 text-sm leading-relaxed">
                                        {{ __('Trạng thái:') }} <strong>{{ $sundayHoliday }}</strong>. {{ $serviceNote }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notice Section -->
                    @if(!empty($serviceNote))
                        <div class="bg-vttu-red/10 border border-vttu-red/30 rounded-lg p-6 mb-8">
                            <div class="flex gap-4">
                                <div class="text-vttu-red text-2xl flex-shrink-0">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-2">{{ __('Thông báo quan trọng') }}</h4>
                                    <p class="text-gray-700 text-sm leading-relaxed">
                                        {{ $serviceNote }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Contact Section -->
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 rounded-lg p-6 border border-slate-200">
                        <h4 class="font-bold text-gray-900 mb-4">{{ __('Liên hệ Thư viện') }}</h4>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="flex gap-3">
                                <div class="text-vttu-red text-xl flex-shrink-0">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600 font-bold uppercase">{{ __('Điện thoại') }}</p>
                                    <p class="text-gray-900 font-semibold">{{ $phone }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="text-vttu-red text-xl flex-shrink-0">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600 font-bold uppercase">{{ __('Email') }}</p>
                                    <p class="text-gray-900 font-semibold">{{ $email }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="text-vttu-red text-xl flex-shrink-0">
                                    <i class="fas fa-map-pin"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600 font-bold uppercase">{{ __('Địa chỉ') }}</p>
                                    <p class="text-gray-900 font-semibold">{{ $address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif(isset($customSitemap) && $customSitemap === true)
                    <!-- Dynamic Website Sitemap Diagram -->
                    <div class="w-full space-y-8 pb-12">
                        
                        <!-- Header Banner -->
                        <div class="flex justify-center mt-2">
                            <div class="relative bg-[#A80D0D] text-white rounded-md py-4 px-8 text-center border-2 border-white/20 shadow-md max-w-xl w-full mx-4">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-white opacity-85"></div>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-white opacity-85"></div>
                                <div class="border border-white/30 rounded-full px-6 py-1.5 inline-block">
                                    <h1 class="text-xl md:text-2xl font-black tracking-widest text-white uppercase font-sans">SƠ ĐỒ WEBSITE</h1>
                                </div>
                            </div>
                        </div>

                        <!-- Main Branch Line Connector (Desktop) -->
                        <div class="hidden md:block w-full max-w-5xl mx-auto -mt-4 mb-4 relative">
                            <div class="w-0.5 h-6 bg-[#A80D0D] mx-auto"></div>
                            <div class="absolute left-[7.14%] right-[7.14%] top-6 h-0.5 bg-[#A80D0D]"></div>
                            <div class="flex justify-between px-[7.14%] pt-6">
                                @for($i = 0; $i < 7; $i++)
                                    <div class="w-px h-3 bg-[#A80D0D]"></div>
                                @endfor
                            </div>
                        </div>

                        <!-- 7 Main Columns Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3 max-w-5xl mx-auto px-4">
                            <!-- Column 1: Trang chủ -->
                            <button onclick="scrollToSection('section-home')" class="flex flex-col items-center group focus:outline-none">
                                <div class="w-16 h-16 md:w-20 md:h-20 rounded-xl bg-[#A80D0D] flex items-center justify-center shadow-md border-2 border-white group-hover:scale-105 transition-transform">
                                    <i data-lucide="home" class="w-8 h-8 text-white"></i>
                                </div>
                                <span class="mt-2 px-1.5 py-1 bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] dark:text-rose-300 text-[10px] font-black uppercase tracking-wider rounded text-center w-full max-w-[90px] truncate shadow-sm border border-rose-100/50 dark:border-rose-900/10">TRANG CHỦ</span>
                            </button>
                            
                            <!-- Column 2: Giới thiệu -->
                            <button onclick="scrollToSection('section-gioi-thieu')" class="flex flex-col items-center group focus:outline-none">
                                <div class="w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden shadow-md border-2 border-white group-hover:scale-105 transition-transform bg-slate-100">
                                    <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=120&h=120&fit=crop" class="w-full h-full object-cover">
                                </div>
                                <span class="mt-2 px-1.5 py-1 bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] dark:text-rose-300 text-[10px] font-black uppercase tracking-wider rounded text-center w-full max-w-[90px] truncate shadow-sm border border-rose-100/50 dark:border-rose-900/10">GIỚI THIỆU</span>
                            </button>

                            <!-- Column 3: Hướng dẫn -->
                            <button onclick="scrollToSection('section-huong-dan')" class="flex flex-col items-center group focus:outline-none">
                                <div class="w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden shadow-md border-2 border-white group-hover:scale-105 transition-transform bg-slate-100">
                                    <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=120&h=120&fit=crop" class="w-full h-full object-cover">
                                </div>
                                <span class="mt-2 px-1.5 py-1 bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] dark:text-rose-300 text-[10px] font-black uppercase tracking-wider rounded text-center w-full max-w-[90px] truncate shadow-sm border border-rose-100/50 dark:border-rose-900/10">HƯỚNG DẪN</span>
                            </button>

                            <!-- Column 4: Tài nguyên -->
                            <button onclick="scrollToSection('section-tai-nguyen')" class="flex flex-col items-center group focus:outline-none">
                                <div class="w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden shadow-md border-2 border-white group-hover:scale-105 transition-transform bg-slate-100">
                                    <img src="https://images.unsplash.com/photo-1506880018603-83d5b814b5a6?w=120&h=120&fit=crop" class="w-full h-full object-cover">
                                </div>
                                <span class="mt-2 px-1.5 py-1 bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] dark:text-rose-300 text-[10px] font-black uppercase tracking-wider rounded text-center w-full max-w-[90px] truncate shadow-sm border border-rose-100/50 dark:border-rose-900/10">TÀI NGUYÊN</span>
                            </button>

                            <!-- Column 5: Tin tức -->
                            <button onclick="scrollToSection('section-tin-tuc')" class="flex flex-col items-center group focus:outline-none">
                                <div class="w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden shadow-md border-2 border-white group-hover:scale-105 transition-transform bg-slate-100">
                                    <img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=120&h=120&fit=crop" class="w-full h-full object-cover">
                                </div>
                                <span class="mt-2 px-1.5 py-1 bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] dark:text-rose-300 text-[10px] font-black uppercase tracking-wider rounded text-center w-full max-w-[90px] truncate shadow-sm border border-rose-100/50 dark:border-rose-900/10">TIN TỨC</span>
                            </button>

                            <!-- Column 6: Tra cứu OPAC -->
                            <button onclick="scrollToSection('section-tra-cuu-opac')" class="flex flex-col items-center group focus:outline-none">
                                <div class="w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden shadow-md border-2 border-white group-hover:scale-105 transition-transform bg-slate-100">
                                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=120&h=120&fit=crop" class="w-full h-full object-cover">
                                </div>
                                <span class="mt-2 px-1.5 py-1 bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] dark:text-rose-300 text-[10px] font-black uppercase tracking-wider rounded text-center w-full max-w-[90px] truncate shadow-sm border border-rose-100/50 dark:border-rose-900/10">TRA CỨU OPAC</span>
                            </button>

                            <!-- Column 7: Thông tin độc giả -->
                            <button onclick="scrollToSection('section-thong-tin-doc-gia')" class="flex flex-col items-center group focus:outline-none">
                                <div class="w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden shadow-md border-2 border-white group-hover:scale-105 transition-transform bg-slate-100">
                                    <img src="https://images.unsplash.com/photo-1614064641938-3bbee52942c7?w=120&h=120&fit=crop" class="w-full h-full object-cover">
                                </div>
                                <span class="mt-2 px-1.5 py-1 bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] dark:text-rose-300 text-[10px] font-black uppercase tracking-wider rounded text-center w-full max-w-[90px] truncate shadow-sm border border-rose-100/50 dark:border-rose-900/10">TT ĐỘC GIẢ</span>
                            </button>
                        </div>

                        <!-- ================== DETAILED SECTIONS ================== -->

                        <!-- SECTION 1: TRANG CHỦ -->
                        <div class="space-y-4 pt-4">
                            <div id="section-home" class="bg-[#A80D0D] text-white py-2.5 px-4 text-center font-black text-sm uppercase tracking-widest rounded shadow-sm">
                                TRANG CHỦ
                            </div>
                            <div class="bg-card border border-border rounded p-6 shadow-sm text-center space-y-4">
                                <p class="text-xs md:text-sm text-muted-foreground leading-relaxed max-w-2xl mx-auto">
                                    Chào mừng đến với Cổng thông tin Thư viện số Trường Đại học Võ Trường Toản. Nơi lưu trữ, kết nối và cung cấp nguồn học liệu, tài nguyên nghiên cứu khoa học phục vụ cho toàn thể Cán bộ, Giảng viên và Sinh viên nhà trường.
                                </p>
                                <a href="/" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#A80D0D] hover:bg-[#8d0a0a] text-white text-xs font-black rounded uppercase tracking-wider transition-all duration-300 shadow-md">
                                    <i data-lucide="home" class="w-4 h-4"></i>
                                    <span>Về Trang chủ Thư viện</span>
                                </a>
                            </div>
                        </div>

                        <!-- SECTION 2: GIỚI THIỆU -->
                        @php
                            $gioiThieuNode = isset($menuItems) ? $menuItems->firstWhere('node_code', 'gioi-thieu') : null;
                            $gioiThieuChildren = $gioiThieuNode ? $gioiThieuNode->activeChildren : collect();
                        @endphp
                        @if($gioiThieuChildren->count() > 0)
                            <div class="space-y-4 pt-4">
                                <div id="section-gioi-thieu" class="bg-[#A80D0D] text-white py-2.5 px-4 text-center font-black text-sm uppercase tracking-widest rounded shadow-sm">
                                    GIỚI THIỆU
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                    @foreach($gioiThieuChildren as $child)
                                        <div class="flex flex-col items-center">
                                            <!-- Down pointing double chevron -->
                                            <i data-lucide="chevrons-down" class="w-4.5 h-4.5 text-[#A80D0D] mb-2 animate-bounce" style="animation-duration: 2.5s;"></i>
                                            <a href="{{ $child->getUrl() }}" class="w-full flex-grow flex flex-col items-center justify-center p-4 bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] dark:text-rose-200 border border-rose-100 dark:border-rose-900/30 rounded shadow-sm text-center min-h-[120px] transition-all hover:scale-105 hover:bg-rose-100/30 hover:shadow-md">
                                                <span class="text-[11px] font-bold leading-relaxed">
                                                    {{ $child->description ?: $child->display_name }}
                                                </span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- SECTION 3: HƯỚNG DẪN -->
                        @php
                            $huongDanNode = isset($menuItems) ? $menuItems->firstWhere('node_code', 'huong-dan') : null;
                            $huongDanChildren = $huongDanNode ? $huongDanNode->activeChildren : collect();
                        @endphp
                        <div class="space-y-4 pt-4">
                            <div id="section-huong-dan" class="bg-[#A80D0D] text-white py-2.5 px-4 text-center font-black text-sm uppercase tracking-widest rounded shadow-sm">
                                HƯỚNG DẪN SỬ DỤNG
                            </div>
                            <div class="bg-card border border-border rounded p-5 shadow-sm space-y-4">
                                <p class="text-xs md:text-sm text-muted-foreground leading-relaxed">
                                    Bao gồm các cẩm nang, tài liệu và quy trình hướng dẫn giúp độc giả có thể dễ dàng tiếp cận và khai thác tối đa các tiện ích, dịch vụ của Thư viện điện tử như: Hướng dẫn đăng nhập tài khoản cá nhân, cài đặt ứng dụng mobile, gia hạn sách trực tuyến, hướng dẫn tra cứu tài liệu in, khai thác cơ sở dữ liệu số,...
                                </p>
                                
                                @if($huongDanChildren->count() > 0)
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-3 border-t border-border">
                                        @foreach($huongDanChildren as $child)
                                            <a href="{{ $child->getUrl() }}" class="flex items-center gap-2.5 p-3 rounded bg-rose-50/50 hover:bg-rose-50 dark:bg-rose-950/10 dark:hover:bg-rose-950/20 text-[#A80D0D] dark:text-rose-300 border border-rose-100/50 dark:border-rose-900/10 transition-all hover:-translate-y-0.5 shadow-xs text-center justify-center">
                                                <i data-lucide="{{ getLucideIcon($child->icon) }}" class="w-4 h-4 text-[#A80D0D] dark:text-rose-400 flex-shrink-0"></i>
                                                <span class="text-[10px] font-black uppercase tracking-wider truncate">{{ $child->display_name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- SECTION 4: TÀI NGUYÊN -->
                        @php
                            $taiNguyenNode = isset($menuItems) ? $menuItems->firstWhere('node_code', 'tai-nguyen') : null;
                            $taiNguyenChildren = $taiNguyenNode ? $taiNguyenNode->activeChildren : collect();
                        @endphp
                        @if($taiNguyenChildren->count() > 0)
                            <div class="space-y-4 pt-4">
                                <div id="section-tai-nguyen" class="bg-[#A80D0D] text-white py-2.5 px-4 text-center font-black text-sm uppercase tracking-widest rounded shadow-sm">
                                    TÀI NGUYÊN THƯ VIỆN
                                </div>
                                
                                <!-- Resource Connector Line (Desktop) -->
                                <div class="hidden md:block w-full max-w-3xl mx-auto -mt-2 mb-2 relative">
                                    <div class="w-0.5 h-4 bg-[#A80D0D] mx-auto"></div>
                                    <div class="absolute left-[12.5%] right-[12.5%] top-4 h-0.5 bg-[#A80D0D]"></div>
                                    <div class="flex justify-between px-[12.5%] pt-4">
                                        @for($i = 0; $i < $taiNguyenChildren->count(); $i++)
                                            <div class="w-px h-3 bg-[#A80D0D]"></div>
                                        @endfor
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                                    @foreach($taiNguyenChildren as $child)
                                        <a href="{{ $child->getUrl() }}" class="flex flex-col items-center p-4 bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] dark:text-rose-200 border border-rose-100 dark:border-rose-900/30 rounded shadow-sm hover:scale-105 transition-transform hover:shadow-md hover:bg-rose-100/30 text-center justify-center min-h-[90px] group">
                                            <i data-lucide="{{ getLucideIcon($child->icon) }}" class="w-5 h-5 mb-2 text-[#A80D0D] dark:text-rose-400 group-hover:scale-110 transition-transform"></i>
                                            <span class="text-[11px] font-black uppercase tracking-wider leading-normal">{{ $child->display_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- SECTION 5: TIN TỨC -->
                        <div class="space-y-4 pt-4">
                            <div id="section-tin-tuc" class="bg-[#A80D0D] text-white py-2.5 px-4 text-center font-black text-sm uppercase tracking-widest rounded shadow-sm">
                                TIN TỨC & HOẠT ĐỘNG
                            </div>
                            <div class="bg-card border border-border rounded p-6 shadow-sm text-center space-y-4">
                                <p class="text-xs md:text-sm text-muted-foreground leading-relaxed max-w-2xl mx-auto">
                                    Cập nhật các thông báo học vụ mới nhất, tin tức sự kiện nổi bật, các chuyên mục giới thiệu sách mới hàng tháng và các video hướng dẫn hoạt động thực tế từ thư viện Đại học Võ Trường Toản.
                                </p>
                                <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#A80D0D] hover:bg-[#8d0a0a] text-white text-xs font-black rounded uppercase tracking-wider transition-all duration-300 shadow-md">
                                    <i data-lucide="newspaper" class="w-4 h-4"></i>
                                    <span>Vào mục Tin tức & Sự kiện</span>
                                </a>
                            </div>
                        </div>

                        <!-- SECTION 6: TRA CỨU OPAC -->
                        <div class="space-y-4 pt-4">
                            <div id="section-tra-cuu-opac" class="bg-[#A80D0D] text-white py-2.5 px-4 text-center font-black text-sm uppercase tracking-widest rounded shadow-sm">
                                TRA CỨU OPAC
                            </div>
                            <div class="bg-card border border-border rounded p-6 shadow-sm text-center space-y-4">
                                <p class="text-xs md:text-sm text-muted-foreground leading-relaxed max-w-2xl mx-auto">
                                    Hệ thống Tìm kiếm trực tuyến (OPAC) giúp độc giả tra cứu nhanh danh mục sách in, giáo trình, báo cáo khóa luận, tài liệu tham khảo bản cứng đang được lưu trữ tại Thư viện Trường.
                                </p>
                                <a href="{{ route('site.opac') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#A80D0D] hover:bg-[#8d0a0a] text-white text-xs font-black rounded uppercase tracking-wider transition-all duration-300 shadow-md">
                                    <i data-lucide="search" class="w-4 h-4"></i>
                                    <span>Truy cập hệ thống OPAC</span>
                                </a>
                            </div>
                        </div>

                        <!-- SECTION 7: THÔNG TIN ĐỘC GIẢ -->
                        <div class="space-y-4 pt-4">
                            <div id="section-thong-tin-doc-gia" class="bg-[#A80D0D] text-white py-2.5 px-4 text-center font-black text-sm uppercase tracking-widest rounded shadow-sm">
                                THÔNG TIN ĐỘC GIẢ
                            </div>
                            <div class="bg-card border border-border rounded p-6 shadow-sm text-center space-y-4">
                                <p class="text-xs md:text-sm text-muted-foreground leading-relaxed max-w-2xl mx-auto">
                                    Đăng nhập trang thông tin độc giả cá nhân để tự quản lý thông tin tài khoản, danh sách tài liệu đang mượn, lịch sử mượn trả, yêu cầu mượn trước tài liệu, gia hạn và đề xuất bổ sung sách mới.
                                </p>
                                <a href="{{ route('profile') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#A80D0D] hover:bg-[#8d0a0a] text-white text-xs font-black rounded uppercase tracking-wider transition-all duration-300 shadow-md">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                    <span>Vào trang Cá nhân độc giả</span>
                                </a>
                            </div>
                        </div>

                    </div>

                    <!-- Client side smooth scroll script -->
                    <script>
                        function scrollToSection(id) {
                            const el = document.getElementById(id);
                            if (el) {
                                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                    </script>
                @else
                    <article class="bg-card text-card-foreground border border-border rounded-md shadow-sm text-gray-500">
                        @php 
                        $hasDarkBg = isset($sidebarIcons[$node->icon]);
                        $headerColors = $sidebarIcons[$node->icon] ?? ['from-muted/20 to-muted/10', '']; 
                    @endphp
                    @if($node->node_code !== 'huong-dan' && $node->node_code !== 'gioi-thieu')
                    <div class="p-4 border-b border-border bg-gradient-to-r {{ $headerColors[0] }} opacity-90 rounded-t-md">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded flex items-center justify-center border shadow-sm
                                        {{ $hasDarkBg ? 'bg-white/10 text-white border-white/20' : 'bg-vttu-red/10 text-vttu-red border-vttu-red/20' }}">
                                <i data-lucide="{{ getLucideIcon($node->icon) }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest
                                          {{ $hasDarkBg ? 'text-white/70' : 'text-vttu-red/80' }}">{{ $sectionLabel }}</p>
                                <h1 class="text-xl md:text-2xl font-black tracking-tight
                                           {{ $hasDarkBg ? 'text-white' : 'text-vttu-red' }}">{{ $node->display_name }}</h1>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Article Body -->
                    <div class="p-4 md:p-6">
                        <div class="prose prose-sm md:prose-base dark:prose-invert max-w-none 
                                    prose-headings:text-foreground prose-headings:font-bold
                                    prose-p:text-muted-foreground prose-p:leading-relaxed
                                    prose-strong:text-foreground
                                    prose-a:text-vttu-red prose-a:font-bold prose-a:no-underline hover:prose-a:underline
                                    prose-img:rounded-md prose-img:border prose-img:border-border shadow-vttu-red/5">
                            {{-- Hiển thị header image cho trang nội quy thư viện --}}
                            @if($node->node_code === 'noi-quy-thu-vien')
                                @include('site.pages.noi-quy-thu-vien-header')
                            @endif
                            
                            @if($node->node_code === 'huong-dan')
                                @include('site.pages.huong-dan-content')
                            @elseif($node->node_code === 'tai-nguyen')
                                @include('site.pages.tai-nguyen-content')
                            @elseif($node->node_code === 'tai-lieu-giay')
                                @include('site.pages.tai-lieu-giay-content')
                            @elseif($node->node_code === 'tin-tuc')
                                @if(isset($news) && $news instanceof \App\Models\News)
                                    @include('site.pages.news-show-content')
                                @else
                                    @include('site.pages.news-list-content')
                                @endif
                            @elseif($node->node_code === 'gioi-thieu-chung')
                                @include('site.pages.gioi-thieu-chung-content')
                            @elseif($node->node_code === 'co-so-du-lieu')
                                @include('site.pages.co-so-du-lieu-content')
                            @elseif($node->node_code === 'noi-quy-thu-vien')
                                @include('site.pages.noi-quy-thu-vien-content')
                            @elseif($node->node_code === 'cam-nang-hdsd')
                                @include('site.pages.cam-nang-hdsd-content')
                            @elseif($node->node_code === 'tai-app-mobile')
                                @include('site.pages.tai-app-mobile-content')
                            @elseif($node->node_code === 'dang-nhap-tai-khoan')
                                @include('site.pages.dang-nhap-tai-khoan-content')
                            @elseif($node->node_code === 'doi-mat-khau')
                                @include('site.pages.doi-mat-khau-content')
                            @elseif($node->node_code === 'tra-cuu-tai-lieu-giay')
                                @include('site.pages.tra-cuu-tai-lieu-giay-content')
                            @elseif($node->node_code === 'tra-cuu-tai-lieu-so')
                                @include('site.pages.tra-cuu-tai-lieu-so-content')
                            @elseif($node->node_code === 'muon-truoc-gia-han')
                                @include('site.pages.muon-truoc-gia-han-content')
                            @elseif($node->node_code === 'de-nghi-bo-sung' || $node->node_code === 'sb-de-nghi-bo-sung')
                                @include('site.pages.de-nghi-bo-sung-content')
                            @elseif($node->node_code === 'khao-sat-y-kien' || $node->node_code === 'sb-khao-sat' || $node->node_code === 'khao-sat')
                                @include('site.pages.khao-sat-y-kien-content')
                            @elseif($node->node_code === 'thoi-gian-phuc-vu')
                                @php
                                    $openTime = \App\Models\SystemSetting::get('opening_time_weekday');
                                    $closeTime = \App\Models\SystemSetting::get('closing_time_weekday');
                                    $sundayHoliday = \App\Models\SystemSetting::get('sunday_holiday_hours');
                                    $serviceNote = \App\Models\SystemSetting::get('service_hours_note');
                                    
                                    $phone = \App\Models\SystemSetting::get('phone');
                                    $email = \App\Models\SystemSetting::get('email');
                                    $address = \App\Models\SystemSetting::get('address');
                                @endphp
                                <div class="space-y-6">
                                    <div class="flex flex-col md:flex-row items-center gap-8 bg-slate-50 p-6 md:p-8 rounded-3xl border border-slate-200 shadow-sm">
                                        <div class="flex-shrink-0 w-full md:w-1/2 rounded-2xl overflow-hidden shadow-lg border border-slate-200">
                                            <img src="/assets/images/thoi-gian-phuc-vu.png" onerror="this.src='https://img.freepik.com/free-vector/modern-office-open-hours-sign-concept_23-2148545161.jpg'" alt="Thời gian phục vụ" class="w-full h-auto object-cover">
                                        </div>
                                        <div class="w-full md:w-1/2 space-y-6">
                                            <div>
                                                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">{{ __('GIỜ MỞ CỬA THƯ VIỆN') }}</h3>
                                                <div class="w-16 h-1 bg-vttu-red rounded-full mt-1"></div>
                                            </div>
                                            <div class="space-y-4">
                                                <div class="flex items-center gap-4 p-4 bg-white rounded-xl shadow-sm border border-slate-100">
                                                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl shrink-0">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Thứ Hai - Thứ Bảy') }}</div>
                                                        <div class="text-xl font-black text-slate-800">{{ date('H:i', strtotime($openTime)) }} - {{ date('H:i', strtotime($closeTime)) }}</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-4 p-4 bg-rose-50/50 rounded-xl shadow-sm border border-rose-100">
                                                    <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center text-xl shrink-0">
                                                        <i class="fas fa-calendar-times"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-bold text-rose-400 uppercase tracking-wider">{{ __('Chủ nhật & Ngày Lễ') }}</div>
                                                        <div class="text-xl font-black text-rose-600 uppercase">{{ $sundayHoliday }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if(!empty($serviceNote))
                                                <p class="text-xs text-slate-500 italic font-medium leading-relaxed">* {{ __('Lưu ý') }}: {{ $serviceNote }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Thông tin liên hệ lấy từ Tab General Settings -->
                                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 rounded-2xl p-6 border border-slate-200">
                                        <h4 class="font-bold text-slate-900 mb-4 text-sm uppercase tracking-wider flex items-center gap-2">
                                            <i class="fas fa-headset text-vttu-red"></i> {{ __('Thông tin liên hệ & Hỗ trợ') }}
                                        </h4>
                                        <div class="grid md:grid-cols-3 gap-4">
                                            <div class="flex gap-3 items-center bg-white p-3.5 rounded-xl border border-slate-100 shadow-sm">
                                                <div class="w-10 h-10 bg-vttu-red/10 text-vttu-red rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-phone"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Điện thoại') }}</p>
                                                    <p class="text-slate-800 font-bold text-xs truncate">{{ $phone }}</p>
                                                </div>
                                            </div>
                                            <div class="flex gap-3 items-center bg-white p-3.5 rounded-xl border border-slate-100 shadow-sm">
                                                <div class="w-10 h-10 bg-vttu-red/10 text-vttu-red rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-envelope"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Email') }}</p>
                                                    <p class="text-slate-800 font-bold text-xs truncate">{{ $email }}</p>
                                                </div>
                                            </div>
                                            <div class="flex gap-3 items-center bg-white p-3.5 rounded-xl border border-slate-100 shadow-sm">
                                                <div class="w-10 h-10 bg-vttu-red/10 text-vttu-red rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-map-pin"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Địa chỉ') }}</p>
                                                    <p class="text-slate-800 font-bold text-xs truncate">{{ $address }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {!! $node->content !!}
                            @endif
                        </div>
                        
                        {{-- Hiển thị component tiện ích thư viện cho trang giới thiệu chung --}}
                        @if($node->node_code === 'gioi-thieu-chung')
                            <div class="mt-8 pt-8 border-t border-border">
                                @include('site.partials.library-benefits')
                            </div>
                        @endif
                    </div>

                    <!-- Navigation Footer -->
                        <div class="p-3 border-t border-border bg-muted/20 grid grid-cols-2 gap-3">
                            @php
                                $prev = $sidebarItems->where('sort_order', '<', $node->sort_order)->last();
                                $next = $sidebarItems->where('sort_order', '>', $node->sort_order)->first();
                            @endphp
                            
                            <div>
                                @if($prev)
                                    <a href="{{ $prev->getUrl() }}" class="flex items-center gap-2 p-2 rounded border border-border bg-card hover:bg-muted hover:border-vttu-red/30 active:bg-accent transition-all group">
                                        <div class="w-7 h-7 rounded bg-muted flex items-center justify-center text-muted-foreground group-hover:bg-vttu-red group-hover:text-white group-active:scale-90 transition-all">
                                            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-wider">{{ __('Trước') }}</p>
                                            <p class="text-[11px] font-bold text-foreground truncate group-hover:text-vttu-red transition-colors">{{ $prev->display_name }}</p>
                                        </div>
                                    </a>
                                @endif
                            </div>
                            
                            <div class="text-right">
                                @if($next)
                                    <a href="{{ $next->getUrl() }}" class="flex items-center justify-end gap-2 p-2 rounded border border-border bg-card hover:bg-muted hover:border-vttu-red/30 active:bg-accent transition-all group">
                                        <div class="overflow-hidden text-right">
                                            <p class="text-[8px] font-bold text-muted-foreground uppercase tracking-wider">{{ __('Tiếp') }}</p>
                                            <p class="text-[11px] font-bold text-foreground truncate group-hover:text-vttu-red transition-colors">{{ $next->display_name }}</p>
                                        </div>
                                        <div class="w-7 h-7 rounded bg-muted flex items-center justify-center text-muted-foreground group-hover:bg-vttu-red group-hover:text-white group-active:scale-90 transition-all">
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endif

                <!-- Page Builder Blocks -->
                @if(isset($node) && $node->activeItems->count() > 0)
                    <div class="space-y-3">
                        @foreach($node->activeItems as $item)
                            <div class="bg-card border border-border rounded-md shadow-sm p-3">
                                @php $itemData = is_string($item->item_data) ? json_decode($item->item_data, true) : $item->item_data; @endphp
                                @includeIf('site.items.' . $item->item_type, ['item' => $item, 'data' => $itemData])
                            </div>
                        @endforeach
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
