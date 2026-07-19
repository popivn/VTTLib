@extends('layouts.site')

@section('title', $CSDLName . ' - Thư viện')

@section('content')
@php
    $accent       = 'primary';
    
    // Sidebar variables are passed from controller

    // Client view colors based on Rule.txt
    $sidebarIcons = [
        'fas fa-circle-info'    => ['from-vttu-red to-vttu-dark',    'shadow-vttu-red/25'],
        'fas fa-bullseye'       => ['from-vttu-red to-vttu-dark',    'shadow-vttu-red/25'],
        'fas fa-scale-balanced' => ['from-vttu-red to-vttu-dark',    'shadow-vttu-red/25'],
        'fas fa-clock'          => ['from-vttu-red to-vttu-dark',    'shadow-vttu-red/25'],
        'fas fa-sitemap'        => ['from-vttu-red to-vttu-dark',    'shadow-vttu-red/25'],
    ];

    if (!function_exists('getLucideIconDetail')) {
        function getLucideIconDetail($faIcon) {
            $map = [
                'fas fa-circle-info'    => 'info',
                'fas fa-bullseye'       => 'target',
                'fas fa-scale-balanced' => 'scale',
                'fas fa-clock'          => 'clock',
                'fas fa-sitemap'        => 'sitemap',
                'fas fa-home'           => 'home',
                'fas fa-search'         => 'search',
                'fas fa-arrow-left'     => 'arrow-left',
                'fas fa-arrow-right'    => 'arrow-right',
            ];
            return $map[$faIcon] ?? 'file-text';
        }
    }
@endphp

<div class="min-h-screen bg-background text-foreground animate-fade-in" x-data="{ sidebarOpen: true }">
    <!-- Header / Breadcrumb -->
    <header class="sticky top-0 z-40 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="container flex h-14 items-center px-4 md:px-6">
            <nav class="flex items-center space-x-2 text-sm font-medium">
                <a href="/" class="flex items-center gap-1 text-muted-foreground hover:text-foreground transition-colors">
                    <i data-lucide="home" class="w-4 h-4"></i>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-muted-foreground opacity-50"></i>
                @if($parent)
                    <a href="{{ $parent->getUrl() }}" class="text-muted-foreground hover:text-foreground transition-colors">
                        {{ $parent->display_name }}
                    </a>
                @else
                    <span class="text-muted-foreground">{{ __('Tài nguyên') }}</span>
                @endif
                <i data-lucide="chevron-right" class="w-3 h-3 text-muted-foreground opacity-50"></i>
                <span class="font-bold text-foreground">{{ $CSDLName }}</span>
            </nav>
        </div>
    </header>

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
                    <div class="p-3 bg-vttu-red border-b border-vttu-red/20 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-white/10 blur-xl rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-700"></div>
                        <div class="flex items-center gap-2 relative z-10">
                            <div class="w-1 h-4 bg-vttu-yellow rounded-full"></div>
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-white">{{ $sectionLabel }}</h3>
                        </div>
                    </div>
                    <nav class="p-2 space-y-1">
                        @foreach($sidebarItems as $item)
                            @php 
                                $active = $item->node_code === 'co-so-du-lieu';
                                $colorClasses = $sidebarIcons[$item->icon] ?? null;
                            @endphp
                            <a href="{{ $item->getUrl() }}"
                               class="flex items-center gap-3 px-3 py-2 rounded text-sm transition-all relative group
                                      {{ $active 
                                         ? 'bg-vttu-red text-white font-bold shadow-md shadow-vttu-red/20' 
                                         : 'text-muted-foreground hover:bg-vttu-red/10 hover:text-vttu-red active:bg-vttu-red active:text-white active:scale-[0.98]' }}">
                                
                                @if(!$active && $colorClasses)
                                    <div class="w-8 h-8 rounded-sm bg-gradient-to-br {{ $colorClasses[0] }} {{ $colorClasses[1] }} flex items-center justify-center text-white flex-shrink-0 transition-all group-hover:scale-110 group-active:scale-95 group-active:bg-none group-active:text-vttu-yellow">
                                        <i data-lucide="{{ getLucideIconDetail($item->icon) }}" class="w-4 h-4"></i>
                                    </div>
                                @else
                                    <div class="w-8 h-8 flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-110 group-active:scale-95">
                                        <i data-lucide="{{ getLucideIconDetail($item->icon) }}" class="w-4 h-4 {{ $active ? 'text-vttu-yellow' : 'opacity-70' }}"></i>
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
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="p-1.5 rounded bg-muted hover:bg-primary/10 hover:text-primary active:scale-95 transition-all border border-border shadow-sm group"
                                title="{{ __('Thu gọn/Mở rộng Sidebar') }}">
                            <i data-lucide="panel-left-close" class="w-3.5 h-3.5 transition-transform duration-300" :class="!sidebarOpen && 'rotate-180'"></i>
                        </button>

                        <div class="w-1 h-3.5 bg-vttu-red rounded-full ml-1"></div>
                        <h2 class="text-xs font-black uppercase tracking-widest text-vttu-dark">{{ __('Chi tiết Cơ sở dữ liệu') }}</h2>
                    </div>
                </div>

                <article class="bg-card text-card-foreground border border-border rounded-md shadow-sm overflow-hidden text-gray-500">
                    <div class="p-4 border-b border-border bg-gradient-to-r from-vttu-red to-vttu-dark opacity-90">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded flex items-center justify-center border shadow-sm bg-white/10 text-white border-white/20">
                                <i data-lucide="database" class="w-5 h-5 text-vttu-yellow"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-white/70">Cơ sở dữ liệu</p>
                                <h1 class="text-xl md:text-2xl font-black tracking-tight text-white">{{ $CSDLName }}</h1>
                            </div>
                        </div>
                    </div>

                    <!-- Article Body -->
                    <div class="p-4 md:p-6 space-y-6">
                        @if($database)
                            <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                                <!-- Logo Container -->
                                @if($database->image_url)
                                <div class="w-48 h-28 bg-white border border-slate-200 rounded-md p-4 flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden">
                                    <img src="{{ $database->image_url }}" alt="{{ $database->title }}" class="max-w-full max-h-full object-contain">
                                </div>
                                @endif
                                
                                <!-- Details Container -->
                                <div class="flex-1 space-y-4 text-center md:text-left">
                                    <div class="prose prose-sm md:prose-base dark:prose-invert max-w-none text-slate-600 dark:text-slate-300">
                                        {!! $database->content !!}
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 pt-2">
                                        @if($database->url)
                                            <a href="{{ $database->url }}" target="_blank" rel="noopener noreferrer" 
                                               class="px-5 py-1.5 bg-vttu-red text-white hover:bg-vttu-dark rounded font-bold text-xs shadow-sm transition-all active:scale-95 duration-200 inline-flex items-center gap-1.5">
                                                <i class="fas fa-external-link-alt text-[10px]"></i> {{ __('Truy cập') }}
                                            </a>
                                        @endif
                                        @if($database->hd_url)
                                            <a href="{{ $database->hd_url }}" target="_blank" rel="noopener noreferrer" 
                                               class="px-5 py-1.5 border border-vttu-red hover:border-vttu-red/80 text-vttu-red hover:text-white hover:bg-vttu-red rounded font-bold text-xs shadow-sm transition-all active:scale-95 duration-200 inline-flex items-center gap-1.5">
                                                <i class="fas fa-file-pdf text-[10px]"></i> {{ __('Tài liệu hướng dẫn') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Fallback UI when database does not exist in tables, using compacted variables -->
                            <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-lg p-4 text-amber-800 dark:text-amber-300 text-sm leading-relaxed mb-6">
                                <i class="fas fa-exclamation-triangle mr-2 text-amber-500"></i>
                                <span>{{ __('Thông tin chi tiết cho cơ sở dữ liệu này hiện đang được cập nhật. Dưới đây là thông tin cơ bản được nhận từ hệ thống.') }}</span>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded border border-border">
                                        <span class="text-xs text-muted-foreground uppercase font-bold">{{ __('Mã CSDL') }}</span>
                                        <p class="text-lg font-bold text-foreground mt-1">#{{ $CSDLId }}</p>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded border border-border md:col-span-2">
                                        <span class="text-xs text-muted-foreground uppercase font-bold">{{ __('Tên CSDL') }}</span>
                                        <p class="text-lg font-bold text-vttu-red mt-1">{{ $CSDLName }}</p>
                                    </div>
                                </div>

                                <div class="bg-slate-50 dark:bg-slate-900/50 p-6 rounded border border-border mt-4">
                                    <h3 class="font-bold text-foreground text-sm uppercase mb-3 flex items-center gap-2">
                                        <i class="fas fa-info-circle text-vttu-red"></i> {{ __('Mô tả Cơ sở dữ liệu') }}
                                    </h3>
                                    <p class="text-sm text-muted-foreground leading-relaxed">
                                        {{ __('Hệ thống chưa tìm thấy dữ liệu bổ sung trong cơ sở dữ liệu cho mã số') }} <strong>{{ $CSDLId }}</strong>.
                                        {{ __('Vui lòng kiểm tra lại liên kết hoặc quay lại trang danh sách Cơ sở dữ liệu trực tuyến để chọn tài nguyên phù hợp.') }}
                                    </p>
                                    <div class="mt-6 flex gap-3">
                                        <a href="{{ url('/co-so-du-lieu') }}" class="px-4 py-2 border border-vttu-red text-vttu-red hover:bg-vttu-red hover:text-white rounded text-xs font-bold transition-all">
                                            {{ __('← Quay lại danh sách') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
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
@endsection
