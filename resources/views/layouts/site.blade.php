<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Thư viện số')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/imgs/logo-vttu.png') }}">
    
    <!-- SEO Meta Tags -->
    @if(isset($node))
        @section('meta-description')
            <meta name="description" content="{{ $node->meta_description ?: Str::limit(strip_tags($node->content ?? ''), 160) }}">
        @show
        @section('meta-keywords')
            <meta name="keywords" content="{{ $node->meta_keywords ?: 'thư viện, số, quản lý, sách' }}">
        @show
    @else
        <meta name="description" content="Thư viện số - Nền tảng quản lý thư viện hiện đại">
        <meta name="keywords" content="thư viện, số, quản lý, sách, OPAC">
    @endif
    
    <!-- CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Be Vietnam Pro', 'sans-serif'],
                        montserrat: ['Be Vietnam Pro', 'sans-serif'],
                    },
                    colors: {
                        vttu: {
                            dark: '#680102',
                            red: '#7B0000',
                            yellow: '#FFD700',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @stack('styles')
    <!-- Custom Styles -->
    <style>
        .prose {
            max-width: none;
        }
        
        .container-fluid {
            width: 100%;
            padding-right: 1.5rem;
            padding-left: 1.5rem;
            margin-right: auto;
            margin-left: auto;
        }
        
        /* Floating Animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        
        /* Gradient Text Animation */
        @keyframes gradient-text {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-gradient-text {
            background: linear-gradient(-45deg, #3b82f6, #06b6d4, #8b5cf6, #ec4899);
            background-size: 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient-text 5s ease infinite;
        }

        /* 3D Card Hover */
        .card-3d {
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            perspective: 1000px;
        }
        .card-3d:hover {
            transform: translateY(-10px) rotateX(5deg) rotateY(2deg);
        }

        .prose:not(.not-prose) h1, .prose:not(.not-prose) h2, .prose:not(.not-prose) h3 {
            color: inherit;
            font-weight: bold;
        }
        .prose:not(.not-prose) h1 { font-size: 2.5rem; margin-top: 2rem; margin-bottom: 1rem; }
        .prose:not(.not-prose) h2 { font-size: 2rem; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .prose:not(.not-prose) h3 { font-size: 1.5rem; margin-top: 1.25rem; margin-bottom: 0.5rem; }
        .prose p {
            margin-bottom: 1rem;
            line-height: 1.7;
        }
        .prose img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .prose ul, .prose ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        .prose li {
            margin-bottom: 0.5rem;
        }
        .prose a {
            color: #2563eb;
            text-decoration: none;
        }
        .prose a:hover {
            text-decoration: underline;
        }
        .prose blockquote {
            border-left: 4px solid #e5e7eb;
            padding-left: 1rem;
            margin: 1rem 0;
            font-style: italic;
            color: #6b7280;
        }
        .prose code {
            background-color: #f3f4f6;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .prose pre {
            background-color: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header id="siteHeader" class="fixed top-0 left-0 w-full z-50 transition-all duration-500 bg-vttu-dark shadow-xl">
        <nav class="w-full px-4 md:px-6 lg:px-4 xl:px-8 2xl:px-12 py-3 transition-all duration-500" id="headerNav">
            <div class="w-full flex justify-between items-center transition-all duration-500" id="headerContainer">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="/" class="flex items-center space-x-2">
                        @php 
                            $siteLogo = \App\Models\SystemSetting::get('site_logo'); 
                            $siteName = \App\Models\SystemSetting::get('site_name', '');
                        @endphp
                        @if($siteLogo)
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo" class="h-full w-auto max-h-16 object-contain transition-all duration-500" id="headerLogo">
                        @else
                            <i class="fas fa-book-open text-vttu-yellow text-4xl transition-all duration-500" id="headerLogoIcon"></i>
                        @endif
                        @if($siteName)
                            <span class="font-black text-xl text-white tracking-tighter transition-all duration-500 whitespace-nowrap" id="headerTitle">{{ $siteName }}</span>
                        @endif
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center lg:space-x-2 xl:space-x-4 flex-1 justify-end lg:px-2 xl:px-6">
                    @if(isset($menuItems))
                        @foreach($menuItems as $item)
                            @if(str_contains(strtolower($item->node_code ?? ''), 'khao-sat') || str_contains(mb_strtolower($item->display_name ?? ''), 'khảo sát'))
                                @continue
                            @endif
                            @if($item->activeChildren && $item->activeChildren->count() > 0)
                                <!-- Dropdown Node -->
                                <div class="relative group h-full flex items-center" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                                    <a href="{{ $item->getUrl() }}" class="text-white/80 group-hover:text-white font-bold text-[11px] uppercase tracking-[0.05em] transition-all py-5 flex items-center space-x-1 outline-none whitespace-nowrap">
                                        <span>{{ __($item->display_name) }}</span>
                                        <i class="fas fa-chevron-down text-[8px] opacity-50 transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                                    </a>
                                    <div class="absolute -bottom-1 left-0 w-0 h-0.5 bg-vttu-yellow transition-all group-hover:w-full"></div>
 
                                    <!-- Dropdown Menu -->
                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 translate-y-2"
                                         class="absolute top-full left-1/2 -translate-x-1/2 mt-0 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 py-2 z-[60] overflow-hidden"
                                         style="display: none;">
                                        @foreach($item->activeChildren as $child)
                                            <a href="{{ $child->getUrl() }}" 
                                               class="flex items-center space-x-3 px-4 py-3 text-vttu-dark/80 hover:text-vttu-red hover:bg-vttu-red/5 transition-all group/item">
                                                @if($child->icon)
                                                    <i class="{{ $child->icon }} text-xs opacity-50 group-hover/item:opacity-100 transition-opacity w-4 text-center"></i>
                                                @endif
                                                <span class="text-[10px] font-black uppercase tracking-widest">{{ __($child->display_name) }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="relative group whitespace-nowrap">
                                    <a href="{{ $item->getUrl() }}" 
                                       class="text-white/80 hover:text-white font-bold text-[11px] uppercase tracking-[0.05em] transition-all py-5 block">
                                        {{ __($item->display_name) }}
                                    </a>
                                    <div class="absolute -bottom-1 left-0 w-0 h-0.5 bg-vttu-yellow transition-all group-hover:w-full"></div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                <!-- Language & User Desktop -->
                <div class="hidden lg:flex items-center lg:space-x-2 xl:space-x-4 flex-shrink-0">
                    <!-- Language Switcher Desktop -->
                    <div class="relative group" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" 
                                class="flex items-center space-x-2 px-3 py-2 bg-white/10 hover:bg-white/20 text-white rounded-full border border-white/20 transition-all backdrop-blur-md">
                            <i class="fas fa-globe text-xs"></i>
                            <span class="font-black text-[10px] uppercase tracking-widest">{{ app()->getLocale() == 'vi' ? 'VI' : 'EN' }}</span>
                            <i class="fas fa-chevron-down text-[8px] opacity-50 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        
                        <!-- Dropdown -->
                        <div x-show="open" 
                             style="display: none;"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-40 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-[70]">
                            <a href="{{ route('lang.switch', 'vi') }}" class="flex items-center justify-between px-4 py-2 text-vttu-dark hover:bg-vttu-red/5 transition-all {{ app()->getLocale() == 'vi' ? 'bg-vttu-red/5' : '' }}">
                                <span class="text-[10px] font-black uppercase tracking-widest">Tiếng Việt</span>
                                @if(app()->getLocale() == 'vi') <i class="fas fa-check text-[8px] text-vttu-red"></i> @endif
                            </a>
                            <a href="{{ route('lang.switch', 'en') }}" class="flex items-center justify-between px-4 py-2 text-vttu-dark hover:bg-vttu-red/5 transition-all {{ app()->getLocale() == 'en' ? 'bg-vttu-red/5' : '' }}">
                                <span class="text-[10px] font-black uppercase tracking-widest">English</span>
                                @if(app()->getLocale() == 'en') <i class="fas fa-check text-[8px] text-vttu-red"></i> @endif
                            </a>
                        </div>
                    </div>
                    
                    @auth
                        <div class="relative group">
                            <button class="flex items-center space-x-2 px-4 py-2 bg-white/10 hover:bg-white text-white hover:text-vttu-red rounded-full border border-white/20 transition-all shadow-lg backdrop-blur-md group-hover:shadow-vttu-red/20">
                                <div class="w-6 h-6 bg-vttu-yellow text-vttu-dark rounded-full flex items-center justify-center text-[8px] font-black">
                                    {{ strtoupper(substr(Auth::user()->name ?? Auth::user()->username, 0, 1)) }}
                                </div>
                                <span class="font-black text-[10px] uppercase tracking-widest whitespace-nowrap truncate max-w-[100px]">{{ Auth::user()->name ?? Auth::user()->username }}</span>
                                <i class="fas fa-chevron-down text-[8px] opacity-50 group-hover:rotate-180 transition-transform"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform translate-y-2 group-hover:translate-y-0 z-[60]">
                                <div class="px-4 py-2 border-b border-slate-50 mb-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ __('Tài khoản') }}</p>
                                        @if(Auth::user()->roles->isNotEmpty())
                                            <span class="text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 bg-red-50 text-vttu-red rounded-md border border-vttu-red/10">{{ Auth::user()->roles->pluck('display_name')->implode(', ') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-black text-vttu-dark truncate mt-0.5">{{ Auth::user()->name ?? Auth::user()->username }}</p>
                                </div>
                                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('root') || Auth::user()->getSidebarTabs()->isNotEmpty())
                                    <a href="/topsecret/dashboard" class="flex items-center space-x-2 px-4 py-2 text-vttu-dark/80 hover:text-vttu-red hover:bg-vttu-red/5 transition-all">
                                        <i class="fas fa-tachometer-alt w-4 text-[10px]"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest">{{ __('Quản trị') }}</span>
                                    </a>
                                @endif
                                <a href="{{ route('profile') }}" class="flex items-center space-x-2 px-4 py-2 text-vttu-dark/80 hover:text-vttu-red hover:bg-vttu-red/5 transition-all">
                                    <i class="fas fa-user-circle w-4 text-[10px]"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest">{{ __('Hồ sơ') }}</span>
                                </a>
                                <form action="{{ route('logout') }}" method="POST" class="mt-1 border-t border-slate-50 pt-1">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center space-x-2 px-4 py-2 text-red-500 hover:bg-red-50 transition-all text-left">
                                        <i class="fas fa-sign-out-alt w-4 text-[10px]"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest">{{ __('Đăng xuất') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-2 bg-white/10 hover:bg-white text-white hover:text-vttu-red font-black text-[10px] uppercase tracking-widest rounded-full border border-white/20 transition-all shadow-lg backdrop-blur-md whitespace-nowrap">
                            {{ __('Đăng nhập') }}
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden">
                    <button onclick="toggleMobileMenu()" class="p-2 bg-white/10 rounded-xl text-white">
                        <i class="fas fa-bars-staggered text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden lg:hidden mt-4 bg-slate-900/95 backdrop-blur-2xl rounded-[2rem] border border-white/10 p-6 shadow-2xl">
                <!-- Language Switcher Mobile -->
                <div class="flex items-center justify-center space-x-4 mb-6 pb-6 border-b border-white/5">
                    <a href="{{ route('lang.switch', 'vi') }}" 
                       class="flex-1 text-center py-2 rounded-xl font-black tracking-widest transition-all {{ app()->getLocale() == 'vi' ? 'bg-vttu-yellow text-vttu-dark' : 'bg-white/5 text-white/40' }}">VI</a>
                    <a href="{{ route('lang.switch', 'en') }}" 
                       class="flex-1 text-center py-2 rounded-xl font-black tracking-widest transition-all {{ app()->getLocale() == 'en' ? 'bg-vttu-yellow text-vttu-dark' : 'bg-white/5 text-white/40' }}">EN</a>
                </div>

                @if(isset($menuItems))
                    @foreach($menuItems as $item)
                        @if(str_contains(strtolower($item->node_code ?? ''), 'khao-sat') || str_contains(mb_strtolower($item->display_name ?? ''), 'khảo sát'))
                            @continue
                        @endif
                        @if($item->activeChildren->count() > 0)
                            <div x-data="{ open: false }" class="border-b border-white/5 last:border-0">
                                <button @click="open = !open" 
                                        class="w-full flex items-center justify-between py-4 text-white/80 hover:text-white font-black text-sm uppercase tracking-widest outline-none">
                                    <span>{{ __($item->display_name) }}</span>
                                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="open" 
                                     x-collapse
                                     class="pl-4 pb-2 space-y-2"
                                     style="display: none;">
                                    @if($item->getUrl() !== '#')
                                        <a href="{{ $item->getUrl() }}" 
                                           class="block py-2 text-vttu-yellow hover:text-white text-[11px] font-black uppercase tracking-widest transition-all">
                                            @if($item->icon)
                                                <i class="{{ $item->icon }} mr-2 text-[10px] opacity-50"></i>
                                            @endif
                                            {{ __('TẤT CẢ') }} {{ __($item->display_name) }}
                                        </a>
                                    @endif
                                    @foreach($item->activeChildren as $child)
                                        <a href="{{ $child->getUrl() }}" 
                                           class="block py-2 text-white/50 hover:text-vttu-yellow text-[11px] font-black uppercase tracking-widest transition-all">
                                            @if($child->icon)
                                                <i class="{{ $child->icon }} mr-2 text-[10px] opacity-50"></i>
                                            @endif
                                            {{ __($child->display_name) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $item->getUrl() }}" 
                               class="block py-4 text-white/80 hover:text-white font-black text-sm uppercase tracking-widest border-b border-white/5 last:border-0">
                                {{ __($item->display_name) }}
                            </a>
                        @endif
                    @endforeach
                @endif
                
                @auth
                    <div class="py-4 border-b border-white/5">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">{{ __('Đã đăng nhập') }}</p>
                            @if(Auth::user()->roles->isNotEmpty())
                                <span class="text-[9px] font-black uppercase tracking-widest px-1.5 py-0.5 bg-white/10 text-vttu-yellow rounded-md border border-white/5">{{ Auth::user()->roles->pluck('display_name')->implode(', ') }}</span>
                            @endif
                        </div>
                        <p class="text-white font-black">{{ Auth::user()->name ?? Auth::user()->username }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full mt-6 py-4 bg-vttu-red text-white text-center font-black rounded-2xl uppercase tracking-widest text-xs">
                            {{ __('ĐĂNG XUẤT') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block mt-6 py-4 bg-vttu-red text-white text-center font-black rounded-2xl">
                        {{ __('ĐĂNG NHẬP') }}
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-vttu-dark text-white/90 border-t border-white/5 py-8 md:py-12 mt-8 transition-colors duration-200">
        <div class="container mx-auto px-4 md:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                @php
                    $footerAddress = \App\Models\SystemSetting::get('address');
                    $footerPhone = \App\Models\SystemSetting::get('phone');
                    $footerEmail = \App\Models\SystemSetting::get('email');
                    $footerDesc = \App\Models\SystemSetting::get('footer_description');
                    $fbUrl = \App\Models\SystemSetting::get('facebook_url');
                    $ytUrl = \App\Models\SystemSetting::get('youtube_url');
                    $zaloUrl = \App\Models\SystemSetting::get('zalo_url');
                    $libNameVi = \App\Models\SystemSetting::get('library_name_vi');
                    $footerSiteLogo = \App\Models\SystemSetting::get('site_logo');
                @endphp
                <!-- About & Access Counter -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-2 group cursor-default">
                        @if($footerSiteLogo)
                            <img src="{{ asset('storage/' . $footerSiteLogo) }}" alt="Logo" class="h-10 w-auto max-h-10 object-contain">
                        @else
                            <div class="w-8 h-8 rounded-sm bg-white/10 flex items-center justify-center text-vttu-yellow group-hover:bg-vttu-yellow group-hover:text-vttu-dark transition-all duration-300 shadow-sm border border-white/10">
                                <i class="fas fa-book-open text-xs"></i>
                            </div>
                        @endif
                    </div>
                    @if(!empty($footerDesc))
                        <p class="text-xs leading-relaxed text-white/60 max-w-xs">
                            {{ $footerDesc }}
                        </p>
                    @endif
                    
                    @if(!empty($fbUrl) || !empty($ytUrl) || !empty($zaloUrl))
                        <div class="flex items-center gap-2 pt-1">
                            @if(!empty($fbUrl))
                                <a href="{{ $fbUrl }}" target="_blank" rel="noopener noreferrer" title="Facebook" class="w-8 h-8 rounded-sm bg-white/5 flex items-center justify-center text-white/60 hover:bg-vttu-yellow hover:text-vttu-dark active:scale-90 transition-all border border-white/10">
                                    <i class="fab fa-facebook-f text-xs"></i>
                                </a>
                            @endif
                            @if(!empty($ytUrl))
                                <a href="{{ $ytUrl }}" target="_blank" rel="noopener noreferrer" title="YouTube" class="w-8 h-8 rounded-sm bg-white/5 flex items-center justify-center text-white/60 hover:bg-vttu-yellow hover:text-vttu-dark active:scale-90 transition-all border border-white/10">
                                    <i class="fab fa-youtube text-xs"></i>
                                </a>
                            @endif
                            @if(!empty($zaloUrl))
                                <a href="{{ $zaloUrl }}" target="_blank" rel="noopener noreferrer" title="Zalo / Contact" class="w-8 h-8 rounded-sm bg-white/5 flex items-center justify-center text-white/60 hover:bg-vttu-yellow hover:text-vttu-dark active:scale-90 transition-all border border-white/10">
                                    <i class="fas fa-comments text-xs"></i>
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Visitor Statistics Block (Clean Simple Text & Async AJAX) -->
                    <div class="pt-3 mt-3 border-t border-white/10 text-xs leading-relaxed text-white/80 space-y-1.5 font-sans">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span id="stat-total-visits" class="font-black text-sm text-white">...</span>
                            <span class="text-white/70">{{ __('Tổng lượt truy cập') }}</span>
                            <span id="stat-online-total" class="font-black text-sm text-white ml-2">...</span>
                            <span class="text-white/70">{{ __('Số lượt online') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap pl-2">
                            <span id="stat-online-members" class="font-black text-xs text-white">...</span>
                            <span class="text-white/70">{{ __('Thành viên online') }}</span>
                            <span id="stat-online-guests" class="font-black text-xs text-white ml-2">...</span>
                            <span class="text-white/70">{{ __('Khách online') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span id="stat-today" class="font-black text-xs text-white">...</span>
                            <span class="text-white/70">{{ __('Trong ngày') }}</span>
                            <span id="stat-yesterday" class="font-black text-xs text-white ml-1.5">...</span>
                            <span class="text-white/70">{{ __('Hôm qua') }}</span>
                            <span id="stat-month" class="font-black text-xs text-white ml-1.5">...</span>
                            <span class="text-white/70">{{ __('Trong tháng') }}</span>
                        </div>
                        <div class="pt-1 text-[10px] text-vttu-yellow/90 font-semibold flex items-center gap-1">
                            <i class="fas fa-history text-[9px]"></i>
                            <span>{{ __('Hệ thống đã trải qua') }} <strong id="stat-days-operating" class="text-white font-black">...</strong> {{ __('ngày từ lượt truy cập đầu tiên') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-3 bg-vttu-yellow rounded-full"></div>
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-vttu-yellow/80">{{ __('Liên kết nhanh') }}</h3>
                    </div>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ url('/') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Trang chủ') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('site.page', 'gioi-thieu-chung') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Giới thiệu chung') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('site.page', 'noi-quy-thu-vien') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Nội quy thư viện') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('site.page', 'thoi-gian-phuc-vu') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Thời gian phục vụ') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('news.index') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Tin tức & Thông báo') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('site.sitemap') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Sơ đồ trang (Sitemap)') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Services & Resources -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-3 bg-vttu-yellow rounded-full"></div>
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-vttu-yellow/80">{{ __('Dịch vụ & Tài nguyên') }}</h3>
                    </div>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ url('opac') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Tra cứu OPAC') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('site.page', 'tai-lieu-so') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Tài liệu số hóa') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('site.oer.landing') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Tài nguyên giáo dục mở') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('site.page', 'co-so-du-lieu') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Cơ sở dữ liệu trực tuyến') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('site.page', 'cam-nang-hdsd') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Hướng dẫn sử dụng') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('site.page', 'de-nghi-bo-sung') }}" class="text-xs text-white/50 hover:text-vttu-yellow hover:pl-1 flex items-center group transition-all">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-0 group-hover:opacity-100 transition-all text-vttu-yellow"></i>
                                {{ __('Đề nghị bổ sung tài liệu') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-3 bg-vttu-yellow rounded-full"></div>
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-vttu-yellow/80">{{ __('Liên hệ') }}</h3>
                    </div>
                    <div class="space-y-3">
                        @if(!empty($footerAddress))
                            <div class="flex items-start gap-3 group">
                                <div class="w-8 h-8 rounded-sm bg-white/5 flex items-center justify-center text-vttu-yellow flex-shrink-0 group-hover:bg-vttu-yellow group-hover:text-vttu-dark transition-colors border border-white/10 shadow-sm">
                                    <i class="fas fa-map-marker-alt text-xs"></i>
                                </div>
                                <span class="text-xs leading-relaxed text-white/60 group-hover:text-white transition-colors font-medium">
                                    {{ $footerAddress }}
                                </span>
                            </div>
                        @endif
                        @if(!empty($footerPhone))
                            <div class="flex items-center gap-3 group">
                                <div class="w-8 h-8 rounded-sm bg-white/5 flex items-center justify-center text-vttu-yellow flex-shrink-0 group-hover:bg-vttu-yellow group-hover:text-vttu-dark transition-colors border border-white/10 shadow-sm">
                                    <i class="fas fa-phone-alt text-xs"></i>
                                </div>
                                <span class="text-xs text-white/60 group-hover:text-white transition-colors font-medium">{{ $footerPhone }}</span>
                            </div>
                        @endif
                        @if(!empty($footerEmail))
                            <div class="flex items-center gap-3 group">
                                <div class="w-8 h-8 rounded-sm bg-white/5 flex items-center justify-center text-vttu-yellow flex-shrink-0 group-hover:bg-vttu-yellow group-hover:text-vttu-dark transition-colors border border-white/10 shadow-sm">
                                    <i class="fas fa-envelope text-xs"></i>
                                </div>
                                <span class="text-xs text-white/60 group-hover:text-white transition-colors font-medium truncate">{{ __('Mail') }}: {{ $footerEmail }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bottom Footer -->
            <div class="border-t border-white/10 mt-10 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-[10px] font-medium text-white/40 text-center md:text-left">
                    {{ __('© 2026 VTTU Library. Tất cả quyền được bảo lưu.') }}
                </p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('site.page', 'noi-quy-thu-vien') }}" class="text-[10px] font-bold text-white/40 hover:text-vttu-yellow transition-colors">{{ __('Nội quy & Điều khoản') }}</a>
                    <a href="{{ route('site.page', 'ban-do-website-thu-vien') }}" class="text-[10px] font-bold text-white/40 hover:text-vttu-yellow transition-colors">{{ __('Sơ đồ trang') }}</a>
                    <div class="w-1.5 h-1.5 rounded-full bg-vttu-yellow animate-pulse"></div>
                    <span class="text-[10px] font-black text-vttu-yellow/70 tracking-widest uppercase">{{ __('System Online') }}</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Async AJAX loading for Visitor Statistics (No impact on page load speed)
            fetch('/footer-stats', {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    var elTotal = document.getElementById('stat-total-visits');
                    var elOnlineTotal = document.getElementById('stat-online-total');
                    var elOnlineMembers = document.getElementById('stat-online-members');
                    var elOnlineGuests = document.getElementById('stat-online-guests');
                    var elToday = document.getElementById('stat-today');
                    var elYesterday = document.getElementById('stat-yesterday');
                    var elMonth = document.getElementById('stat-month');
                    var elDaysOperating = document.getElementById('stat-days-operating');

                    if (elTotal) elTotal.textContent = data.total_visits;
                    if (elOnlineTotal) elOnlineTotal.textContent = data.online_total;
                    if (elOnlineMembers) elOnlineMembers.textContent = data.online_members;
                    if (elOnlineGuests) elOnlineGuests.textContent = data.online_guests;
                    if (elToday) elToday.textContent = data.today;
                    if (elYesterday) elYesterday.textContent = data.yesterday;
                    if (elMonth) elMonth.textContent = data.month;
                    if (elDaysOperating) elDaysOperating.textContent = data.days_operating;
                })
                .catch(function(err) {
                    console.error('Error fetching footer stats:', err);
                });
        });
    </script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Global Image Fallback Handler
        document.addEventListener('error', function (e) {
            if (e.target.tagName.toLowerCase() === 'img') {
                const fallbackUrl = "{{ asset('assets/imgs/books/noimage.png') }}";
                
                // Tránh lặp vô tận nếu chính ảnh fallback cũng lỗi
                if (e.target.src !== fallbackUrl) {
                    e.target.src = fallbackUrl;
                }
            }
        }, true);

        // Header transparency handling (Disabled as per user request for solid bg)
        window.addEventListener('scroll', function() {
            const header = document.getElementById('siteHeader');
            const navLinks = document.querySelectorAll('#siteHeader a:not(.bg-white):not(.rounded-full), #siteHeader button:not(.lg\\:hidden)');
            
            if (window.scrollY > 50) {
                header.classList.add('shadow-2xl');
            } else {
                header.classList.remove('shadow-2xl');
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            window.dispatchEvent(new Event('scroll'));
        });

        AOS.init({
            duration: 500,
            once: true,
            easing: 'ease-out',
        });
    </script>
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileMenu');
            const button = event.target.closest('button');
            
            if (!menu.contains(event.target) && !button) {
                menu.classList.add('hidden');
            }
        });
    </script>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
