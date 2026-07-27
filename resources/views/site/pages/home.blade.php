@extends('layouts.site')

@section('title', 'VTTLib - Thư viện số hiện đại')

@section('content')
<!-- 
DEBUG NGÔN NGỮ:
Locale hiện tại: {{ app()->getLocale() }}
Kiểm tra dịch 'Khai phá': {{ __('Khai phá') }}
-->
@include('site.partials.book-loader')
<div class="bg-slate-50 min-h-screen">
    <!-- 1. Hero Slider Section -->
    <section class="relative bg-white overflow-hidden w-full pt-[68px]" data-aos="fade">
        <div class="swiper heroSwiper w-full h-auto">
            <div class="swiper-wrapper">
                @if(isset($banners) && $banners->count() > 0)
                    @foreach($banners as $banner)
                        <div class="swiper-slide w-full relative group overflow-hidden" style="aspect-ratio: 1792/592;">
                            @if($banner->link_url)
                                <a href="{{ $banner->link_url }}" class="block w-full h-full">
                            @endif
                                <img src="{{ asset('storage/' . $banner->image_url) }}" 
                                     alt="{{ $banner->title }}" 
                                     class="w-full h-full object-cover block group-hover:scale-105 transition-transform duration-700 ease-out">

                                <!-- Hover Overlay & Description (Centered) -->
                                <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center text-center p-6 md:p-12 pointer-events-none">
                                    <h3 class="text-white font-black text-base md:text-2xl lg:text-3xl drop-shadow-lg tracking-wide transform translate-y-3 group-hover:translate-y-0 transition-transform duration-300 ease-out">
                                        {{ $banner->title }}
                                    </h3>
                                    @if($banner->description)
                                        <div class="w-12 h-0.5 bg-vttu-yellow my-2 md:my-3 rounded-full transform scale-0 group-hover:scale-100 transition-transform duration-300 delay-75"></div>
                                        <p class="text-white/95 text-xs md:text-base font-medium max-w-2xl line-clamp-3 drop-shadow leading-relaxed transform translate-y-3 group-hover:translate-y-0 transition-transform duration-300 delay-100 ease-out">
                                            {{ $banner->description }}
                                        </p>
                                    @endif
                                </div>
                            @if($banner->link_url)
                                </a>
                            @endif
                        </div>
                    @endforeach
                @else
                    <!-- Fallback if no banners are added -->
                    <div class="swiper-slide w-full flex items-center justify-center bg-slate-100" style="aspect-ratio: 1792/592;">
                        <div class="text-center p-8">
                            <i class="fas fa-image text-slate-300 text-5xl mb-3"></i>
                            <p class="text-slate-400 font-medium">Vui lòng thêm banner trong trang quản trị.</p>
                        </div>
                    </div>
                @endif
            </div>
            <!-- Slider Controls -->
            <div class="swiper-pagination !bottom-4"></div>
            <div class="swiper-button-next !right-8 !text-vttu-red/30 hover:!text-vttu-red transition-colors after:!text-2xl hidden md:flex"></div>
            <div class="swiper-button-prev !left-8 !text-vttu-red/30 hover:!text-vttu-red transition-colors after:!text-2xl hidden md:flex"></div>
        </div>

        <!-- Floating Sparkling Books Overlay -->
        <div class="absolute inset-0 pointer-events-none z-10 overflow-hidden" id="heroFloatingBooks">
            <!-- Book 1 -->
            <div class="floating-book" style="left:6%;top:18%;animation-delay:0s;animation-duration:6s;">
                <i class="fas fa-book text-white/60 text-lg drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 2 -->
            <div class="floating-book" style="left:14%;top:60%;animation-delay:1.2s;animation-duration:7.5s;">
                <i class="fas fa-book-open text-yellow-300/70 text-sm drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 3 -->
            <div class="floating-book" style="left:82%;top:15%;animation-delay:0.5s;animation-duration:8s;">
                <i class="fas fa-book text-white/50 text-base drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 4 -->
            <div class="floating-book" style="left:90%;top:65%;animation-delay:2s;animation-duration:6.5s;">
                <i class="fas fa-book-open text-yellow-200/60 text-xs drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 5 -->
            <div class="floating-book" style="left:48%;top:8%;animation-delay:0.8s;animation-duration:9s;">
                <i class="fas fa-book text-white/40 text-sm drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 6 -->
            <div class="floating-book" style="left:72%;top:75%;animation-delay:3s;animation-duration:7s;">
                <i class="fas fa-bookmark text-yellow-300/60 text-base drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 7 -->
            <div class="floating-book" style="left:28%;top:78%;animation-delay:1.8s;animation-duration:8.5s;">
                <i class="fas fa-book text-white/50 text-xs drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 8 -->
            <div class="floating-book" style="left:35%;top:40%;animation-delay:2.4s;animation-duration:7.2s;">
                <i class="fas fa-book-open text-white/45 text-base drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 9 -->
            <div class="floating-book" style="left:58%;top:55%;animation-delay:0.4s;animation-duration:6.8s;">
                <i class="fas fa-book text-yellow-100/50 text-sm drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 10 -->
            <div class="floating-book" style="left:3%;top:72%;animation-delay:3.5s;animation-duration:9.5s;">
                <i class="fas fa-bookmark text-white/55 text-xs drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 11 -->
            <div class="floating-book" style="left:95%;top:35%;animation-delay:1.6s;animation-duration:7.8s;">
                <i class="fas fa-book text-yellow-200/55 text-base drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 12 -->
            <div class="floating-book" style="left:42%;top:85%;animation-delay:2.8s;animation-duration:8.2s;">
                <i class="fas fa-book-open text-white/40 text-sm drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 13 -->
            <div class="floating-book" style="left:67%;top:5%;animation-delay:1.4s;animation-duration:6.2s;">
                <i class="fas fa-book text-white/55 text-lg drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 14 -->
            <div class="floating-book" style="left:20%;top:22%;animation-delay:4s;animation-duration:8.8s;">
                <i class="fas fa-bookmark text-yellow-300/50 text-sm drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 15 -->
            <div class="floating-book" style="left:52%;top:30%;animation-delay:3.2s;animation-duration:7.4s;">
                <i class="fas fa-book-open text-white/35 text-xs drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 16 -->
            <div class="floating-book" style="left:78%;top:90%;animation-delay:0.9s;animation-duration:10s;">
                <i class="fas fa-book text-yellow-100/60 text-base drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 17 -->
            <div class="floating-book" style="left:8%;top:88%;animation-delay:2.6s;animation-duration:6.3s;">
                <i class="fas fa-book text-white/45 text-xs drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>
            <!-- Book 18 -->
            <div class="floating-book" style="left:62%;top:48%;animation-delay:1.1s;animation-duration:9.2s;">
                <i class="fas fa-bookmark text-yellow-200/65 text-sm drop-shadow-lg"></i>
                <span class="sparkle-ring"></span>
            </div>

            <!-- Sparkle dots -->
            <div class="sparkle-dot" style="left:22%;top:30%;animation-delay:0.3s;"></div>
            <div class="sparkle-dot" style="left:65%;top:20%;animation-delay:1s;"></div>
            <div class="sparkle-dot" style="left:55%;top:70%;animation-delay:2.2s;"></div>
            <div class="sparkle-dot" style="left:38%;top:15%;animation-delay:0.7s;"></div>
            <div class="sparkle-dot" style="left:78%;top:45%;animation-delay:1.5s;"></div>
            <div class="sparkle-dot" style="left:10%;top:42%;animation-delay:3.1s;"></div>
            <div class="sparkle-dot" style="left:45%;top:58%;animation-delay:0.5s;"></div>
            <div class="sparkle-dot" style="left:30%;top:90%;animation-delay:2.7s;"></div>
            <div class="sparkle-dot" style="left:86%;top:28%;animation-delay:1.9s;"></div>
            <div class="sparkle-dot" style="left:18%;top:50%;animation-delay:3.6s;"></div>
            <div class="sparkle-dot" style="left:70%;top:88%;animation-delay:0.9s;"></div>
            <div class="sparkle-dot" style="left:50%;top:5%;animation-delay:2.4s;"></div>
            <div class="sparkle-dot" style="left:92%;top:55%;animation-delay:1.2s;"></div>
            <div class="sparkle-dot" style="left:5%;top:10%;animation-delay:4.1s;"></div>
        </div>

        <style>
            .floating-book {
                position: absolute;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: floatBook var(--dur, 7s) ease-in-out infinite;
            }
            @keyframes floatBook {
                0%   { transform: translateY(0px) rotate(-5deg) scale(1);   opacity: 0.5; }
                25%  { transform: translateY(-14px) rotate(4deg) scale(1.1); opacity: 1;   }
                50%  { transform: translateY(-6px) rotate(-3deg) scale(0.95); opacity: 0.7; }
                75%  { transform: translateY(-18px) rotate(6deg) scale(1.05); opacity: 1;  }
                100% { transform: translateY(0px) rotate(-5deg) scale(1);   opacity: 0.5; }
            }
            .sparkle-ring {
                position: absolute;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                border: 1.5px solid rgba(255,255,255,0.5);
                animation: sparkleRing 2.5s ease-out infinite;
                pointer-events: none;
            }
            @keyframes sparkleRing {
                0%   { transform: scale(0.6); opacity: 0.9; }
                60%  { transform: scale(1.6); opacity: 0.3; }
                100% { transform: scale(2.2); opacity: 0; }
            }
            .sparkle-dot {
                position: absolute;
                width: 4px;
                height: 4px;
                border-radius: 50%;
                background: rgba(255,220,80,0.85);
                animation: sparkleDot 3s ease-in-out infinite;
                box-shadow: 0 0 5px 2px rgba(255,220,80,0.4);
            }
            @keyframes sparkleDot {
                0%,100% { transform: scale(0.5); opacity: 0.2; }
                50%      { transform: scale(1.8); opacity: 1;   }
            }
            .news-swiper-container .swiper-slide,
            .books-swiper-container .swiper-slide,
            .medical-swiper-container .swiper-slide,
            .book-intro-swiper-container .swiper-slide {
                height: auto !important;
            }
            .news-swiper-container .swiper-wrapper,
            .medical-swiper-container .swiper-wrapper {
                align-items: flex-start !important;
            }
        </style>
    </section>

    <!-- Main Content Grid -->
    <section class="py-8 relative overflow-hidden bg-slate-50 w-full" x-data="{ ...catalogWizard(), sidebarOpen: true }">
        <!-- Content overlay for readability -->
        <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse at center top, rgba(255,255,255,0.15) 0%, transparent 60%);"></div>

        <div class="container-fluid px-4 md:px-6 lg:px-8 relative z-10 w-full">
            <div class="flex flex-col lg:flex-row gap-8 transition-all duration-500 ease-in-out w-full">
                
                <!-- LEFT COLUMN -->
                <div class="transition-all duration-500 ease-in-out flex-1 w-full" 
                     style="min-width: 0;"
                     :class="sidebarOpen ? 'lg:w-[72%]' : 'lg:w-full'">
                    <div class="space-y-6 w-full">
                        <!-- Section 1: 3 Tabs (Sách Mới | Tạp Chí Online | Thư mục) -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-md p-4 shadow-sm border border-slate-100" data-aos="fade-up">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                <div class="flex items-center gap-3 overflow-x-auto" id="book-tabs">
                                    <button @click="loadTab('book', 'book-tabs', 'books-content')"
                                        class="tab-btn text-sm font-bold px-4 py-1.5 rounded-sm whitespace-nowrap transition-all {{ ($activeType ?? 'book') === 'book' ? 'text-white bg-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}">
                                        {{ __('SÁCH MỚI') }}
                                    </button>
                                    <button @click="loadTab('journal', 'book-tabs', 'books-content')"
                                        class="tab-btn text-sm font-bold px-4 py-1.5 rounded-sm whitespace-nowrap transition-all {{ ($activeType ?? '') === 'journal' ? 'text-white bg-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}">
                                        {{ __('TẠP CHÍ ONLINE') }}
                                    </button>
                                    <button @click="loadTab('folder', 'book-tabs', 'books-content')"
                                        class="tab-btn text-sm font-bold px-4 py-1.5 rounded-sm whitespace-nowrap transition-all {{ ($activeType ?? '') === 'folder' ? 'text-white bg-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}">
                                        {{ __('THƯ MỤC') }}
                                    </button>
                                </div>
                                
                                {{-- Sidebar Toggle Button (Only visible on LG) --}}
                                <button @click="sidebarOpen = !sidebarOpen" 
                                        class="hidden lg:flex items-center justify-center w-8 h-8 bg-slate-50 hover:bg-vttu-red hover:text-white text-slate-400 rounded-sm transition-all shadow-sm border border-slate-100 group">
                                    <i class="fas" :class="sidebarOpen ? 'fa-indent' : 'fa-outdent'"></i>
                                </button>
                            </div>
                            <div id="books-content" class="min-h-[300px] relative flex items-start justify-start pt-4">
                                <div class="flex items-center justify-center w-full py-20">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-vttu-red"></div>
                                </div>
                            </div>
                        </div>

                        <div id="info-grid" class="grid grid-cols-1 gap-6 transition-all duration-500" :class="sidebarOpen ? 'md:grid-cols-2' : 'md:grid-cols-2'">
                            <div class="bg-white rounded-md shadow-sm border border-slate-100 overflow-hidden" data-aos="fade-up">
                                    <div class="bg-vttu-red px-4 py-3 flex items-center justify-between">
                                        <h3 class="text-sm font-bold text-white uppercase tracking-tight">{{ __('THÔNG BÁO') }}</h3>
                                        <a href="#" class="text-vttu-yellow text-[10px] font-bold uppercase tracking-widest hover:underline">{{ __('Xem tất cả') }}</a>
                                    </div>
                                    <div class="p-3">
                                        @if(isset($homeAnnouncements) && $homeAnnouncements->count() > 0)
                                            @php $firstAnn = $homeAnnouncements->first(); @endphp
                                            <div class="mb-3 aspect-video rounded-md overflow-hidden bg-slate-100 relative group">
                                                <a href="{{ $firstAnn->url }}" class="w-full h-full block">
                                                    <img src="{{ $firstAnn->featured_image ?? 'https://img.freepik.com/free-vector/breaking-news-concept_23-2148514216.jpg' }}" 
                                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                                         onerror="this.onerror=null;this.src='https://img.freepik.com/free-vector/breaking-news-concept_23-2148514216.jpg'">
                                                </a>
                                            </div>
                                            <div class="space-y-2">
                                                @foreach($homeAnnouncements as $item)
                                                    <a href="{{ $item->url }}" class="flex items-start gap-2 text-vttu-dark hover:text-vttu-red transition-colors group">
                                                        <span class="text-vttu-red mt-1">•</span>
                                                        <div class="flex-1">
                                                            <p class="text-xs font-medium line-clamp-2 leading-snug group-hover:underline">{{ $item->title }}</p>
                                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $item->created_at->format('H:i:s') }}</p>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="py-6 text-center">
                                                <p class="text-xs text-slate-400 italic">Chưa có thông báo mới cập nhật</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            <!-- Tin tức sự kiện -->
                            <div class="bg-white rounded-md shadow-sm border border-slate-100 overflow-hidden" data-aos="fade-up">
                                <div class="bg-vttu-red px-4 py-3 flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-white uppercase tracking-tight">{{ __('TIN TỨC SỰ KIỆN') }}</h3>
                                    <a href="{{ route('news.index') }}" class="text-vttu-yellow text-[10px] font-bold uppercase tracking-widest hover:underline">{{ __('Xem tất cả') }}</a>
                                </div>
                                <div class="p-3">
                                    @if(isset($homeNews) && $homeNews->count() > 0)
                                        @php 
                                            $newsOrdered = $homeNews->sortBy('sort_order');
                                            $firstNews = $newsOrdered->first(); 
                                        @endphp
                                        <div class="mb-3 aspect-video rounded-md overflow-hidden bg-slate-100 relative group">
                                            <a href="{{ $firstNews->url }}" class="w-full h-full block">
                                                <img src="{{ $firstNews->featured_image ?? 'https://img.freepik.com/free-vector/breaking-news-concept_23-2148514216.jpg' }}" 
                                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                                     onerror="this.onerror=null;this.src='https://img.freepik.com/free-vector/breaking-news-concept_23-2148514216.jpg'">
                                            </a>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($homeNews->sortBy('sort_order')->take(5) as $item)
                                                <a href="{{ $item->url }}" class="flex items-start gap-2 text-vttu-dark hover:text-vttu-red transition-colors group">
                                                    <span class="text-vttu-red mt-1">•</span>
                                                    <div class="flex-1">
                                                        <p class="text-xs font-medium line-clamp-2 leading-snug group-hover:underline">{{ $item->title }}</p>
                                                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $item->created_at->format('H:i:s') }}</p>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="py-6 text-center">
                                            <p class="text-xs text-slate-400 italic">Chưa có tin tức mới cập nhật</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: 2 Tabs (Tin mới | Video) -->
                        <div class="bg-white rounded-md shadow-sm border border-slate-100 p-4" data-aos="fade-up">
                            <div class="flex items-center gap-4 border-b border-slate-100 pb-3 mb-4" id="news-tabs">
                                <button @click="loadNewsTab('news', 'news-tabs', 'news-content')"
                                    class="tab-btn text-sm font-bold px-4 py-1.5 rounded-sm transition-all {{ ($activeNewsType ?? 'news') === 'news' ? 'text-white bg-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}">
                                    {{ __('TIN MỚI') }}
                                </button>
                                <button @click="loadNewsTab('video', 'news-tabs', 'news-content')"
                                    class="tab-btn text-sm font-bold px-4 py-1.5 rounded-sm transition-all {{ ($activeNewsType ?? '') === 'video' ? 'text-white bg-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}">
                                    {{ __('VIDEO') }}
                                </button>
                            </div>
                            <div id="news-content" class="min-h-[200px] relative">
                                <div class="flex items-center justify-center w-full py-20">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-vttu-red"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Giới thiệu sách -->
                        <div class="bg-white rounded-md p-4 text-vttu-dark border border-slate-100 shadow-sm relative overflow-hidden" data-aos="fade-up">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-vttu-red/5 blur-3xl rounded-full"></div>
                            <!-- Carousel bên dưới (Giới thiệu sách hàng tháng) -->
                            <div class="relative group/book-intro-swiper overflow-hidden w-full">
                                <div class="flex items-center border-b border-slate-100 pb-3 mb-4">
                                    <span class="text-xs font-bold text-white bg-vttu-red px-4 py-1.5 rounded-sm shadow-sm whitespace-nowrap uppercase">
                                        {{ __('Giới thiệu sách hằng tháng') }}
                                    </span>
                                </div>
                                <div class="swiper book-intro-swiper-container !pb-1">
                                    <div class="swiper-wrapper flex flex-nowrap">
                                        @if(isset($bookIntroductionNews) && count($bookIntroductionNews) > 0)
                                            @foreach($bookIntroductionNews as $item)
                                            <div class="swiper-slide h-auto shrink-0">
                                                <div class="bg-white p-2.5 rounded-md border border-slate-100 hover:border-vttu-red/20 transition-all group flex flex-col shadow-sm">
                                                    <!-- Book Cover -->
                                                    <div class="aspect-[3/4] bg-slate-900 rounded-sm mb-2 border border-slate-50 overflow-hidden relative group/img">
                                                        @if($item->featured_image)
                                                            <!-- Blurred Backdrop Image Fill -->
                                                            <img src="{{ $item->featured_image }}" class="absolute inset-0 w-full h-full object-cover blur-lg scale-125 opacity-50 pointer-events-none">
                                                            <div class="absolute inset-0 bg-black/10"></div>
                                                            <!-- Main Foreground Image -->
                                                            <img src="{{ $item->featured_image }}" class="relative z-10 w-full h-full object-contain group-hover/img:scale-105 transition-transform duration-500">
                                                        @else
                                                            <div class="w-full h-full bg-vttu-red flex items-center justify-center text-white font-bold text-center p-2 text-[10px]">VTTU Library</div>
                                                        @endif
                                                        <div class="absolute top-1.5 right-1.5 z-10">
                                                            <span class="px-1.5 py-0.5 bg-white/90 backdrop-blur text-vttu-red rounded-sm text-[7px] font-bold uppercase tracking-widest shadow-sm">SÁCH</span>
                                                        </div>
                                                    </div>

                                                    <!-- Book Info -->
                                                    <div class="flex-grow flex flex-col gap-1.5">
                                                        <a href="{{ $item->url }}" class="text-[10px] font-bold text-vttu-dark group-hover:text-vttu-red transition-colors leading-tight line-clamp-2 min-h-[1.5rem]">
                                                            {{ $item->title }}
                                                        </a>

                                                        <p class="text-[9px] font-medium text-slate-500 flex items-center gap-1 truncate">
                                                            <i class="fas fa-user-edit text-[7px] text-vttu-red"></i>
                                                            {{ $item->author->name ?? 'VTTU' }}
                                                        </p>

                                                        <div class="mt-auto pt-1.5 flex items-center justify-between border-t border-slate-50">
                                                            <span class="text-[7px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-sm uppercase tracking-tighter border border-emerald-100">SẴN SÀNG</span>
                                                            <a href="{{ $item->url }}" class="w-5 h-5 rounded-sm bg-slate-50 flex items-center justify-center text-vttu-red hover:bg-vttu-red hover:text-white transition-all shadow-sm">
                                                                <i class="fas fa-arrow-right text-[7px]"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        @else
                                            @for($i=1; $i<=2; $i++)
                                            <div class="swiper-slide h-auto shrink-0">
                                                <div class="bg-white p-2.5 rounded-md border border-slate-100 hover:border-vttu-red/20 transition-all group flex flex-col shadow-sm">
                                                    <!-- Book Cover -->
                                                    <div class="aspect-[3/4] bg-slate-50 rounded-sm mb-2 border border-slate-50 flex items-center justify-center overflow-hidden relative">
                                                        @if($i == 1)
                                                            <div class="w-full h-full bg-vttu-red flex items-center justify-center text-white font-bold text-center p-2 text-[10px]">VTTU Library</div>
                                                        @else
                                                            <div class="w-full h-full flex items-center justify-center bg-slate-50">
                                                                <i class="fas fa-book-open text-slate-200 text-2xl"></i>
                                                            </div>
                                                        @endif
                                                        <div class="absolute top-1.5 right-1.5">
                                                            <span class="px-1.5 py-0.5 bg-white/90 backdrop-blur text-vttu-red rounded-sm text-[7px] font-bold uppercase tracking-widest shadow-sm">SÁCH</span>
                                                        </div>
                                                    </div>

                                                    <!-- Book Info -->
                                                    <div class="flex-grow flex flex-col gap-1.5">
                                                        <h3 class="text-[10px] font-bold text-vttu-dark group-hover:text-vttu-red transition-colors leading-tight line-clamp-2 min-h-[1.5rem]">
                                                            {{ $i == 1 ? 'Giáo trình Kinh tế phát triển' : 'Giáo trình Lý thuyết giải phẫu chức năng hệ vận động' }}
                                                        </h3>

                                                        <p class="text-[9px] font-medium text-slate-500 flex items-center gap-1 truncate">
                                                            <i class="fas fa-user-edit text-[7px] text-vttu-red"></i>
                                                            {{ $i == 1 ? 'Phí Thị Hằng' : 'Hoàng Anh Lân' }}
                                                        </p>

                                                        <div class="mt-auto pt-1.5 flex items-center justify-between border-t border-slate-50">
                                                            <span class="text-[7px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-sm uppercase tracking-tighter border border-emerald-100">SẴN SÀNG</span>
                                                            <button class="w-5 h-5 rounded-sm bg-slate-50 flex items-center justify-center text-vttu-red hover:bg-vttu-red hover:text-white transition-all shadow-sm">
                                                                <i class="fas fa-arrow-right text-[7px]"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endfor
                                        @endif
                                    </div>
                                    
                                    <!-- Navigation Buttons -->
                                    <div class="swiper-button-next book-intro-next !w-8 !h-8 !bg-white !rounded-full !shadow-lg !border !border-slate-100 !text-vttu-red after:!text-[10px] !right-2 opacity-0 group-hover/book-intro-swiper:opacity-100 transition-opacity z-30"></div>
                                    <div class="swiper-button-prev book-intro-prev !w-8 !h-8 !bg-white !rounded-full !shadow-lg !border !border-slate-100 !text-vttu-red after:!text-[10px] !left-2 opacity-0 group-hover/book-intro-swiper:opacity-100 transition-opacity z-30"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: 3 Tabs (Sản khoa | Nhi Khoa | Nội Khoa) -->
                        <div class="bg-white rounded-md p-4 shadow-sm border border-slate-100" data-aos="fade-up">
                            <div class="flex items-center gap-3 border-b border-slate-100 pb-3 mb-4 overflow-x-auto" id="medical-tabs">
                                <button @click="loadMedicalTab('Sản khoa', 'medical-tabs', 'medical-content')"
                                    class="tab-btn text-xs font-bold text-white bg-vttu-red px-4 py-1.5 rounded-sm shadow-sm whitespace-nowrap transition-all uppercase">{{ __('SẢN KHOA') }}</button>
                                <button @click="loadMedicalTab('Nhi khoa', 'medical-tabs', 'medical-content')"
                                    class="tab-btn text-xs font-bold text-slate-400 hover:text-vttu-red px-4 py-1.5 rounded-sm whitespace-nowrap transition-all uppercase">{{ __('NHI KHOA') }}</button>
                                <button @click="loadMedicalTab('Nội khoa', 'medical-tabs', 'medical-content')"
                                    class="tab-btn text-xs font-bold text-slate-400 hover:text-vttu-red px-4 py-1.5 rounded-sm whitespace-nowrap transition-all uppercase">{{ __('NỘI KHOA') }}</button>
                            </div>
                            <div id="medical-content" class="min-h-[200px] relative">
                                <div class="flex items-center justify-center w-full py-20">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-vttu-red"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN (Sidebar) -->
                <div class="transition-all duration-500 ease-in-out lg:sticky lg:top-24 h-fit" 
                     :class="sidebarOpen ? 'lg:w-[25%] opacity-100 visible translate-x-0' : 'lg:w-0 lg:ml-[-1rem] opacity-0 invisible translate-x-full'">
                    <div class="space-y-4 min-w-[280px]">
                        
                        <!-- Thời gian phục vụ -->
                        <div class="bg-vttu-red p-4 rounded-md relative overflow-hidden group shadow-sm" data-aos="fade-left">
                            <div class="flex items-center gap-3 mb-4 relative z-10">
                                <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-sm flex items-center justify-center text-vttu-yellow border border-white/20">
                                    <i class="fas fa-clock text-lg"></i>
                                </div>
                                <h3 class="text-lg font-bold text-white tracking-tight leading-tight uppercase">{{ __('THỜI GIAN') }}<br>{{ __('PHỤC VỤ') }}</h3>
                            </div>
                            <div class="space-y-2 relative z-10">
                                <div class="flex justify-between items-center p-3 bg-white/10 backdrop-blur-sm rounded-sm border border-white/10 hover:bg-white/20 transition-all">
                                    <span class="font-medium text-white/90 text-[11px] uppercase tracking-widest">{{ __('Thứ 2 - Thứ 7') }}</span>
                                    <span class="font-bold text-vttu-yellow text-sm">
                                        {{ date('H:i', strtotime(\App\Models\SystemSetting::get('opening_time_weekday', '07:30'))) }} - {{ date('H:i', strtotime(\App\Models\SystemSetting::get('closing_time_weekday', '20:00'))) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-white/10 backdrop-blur-sm rounded-sm border border-white/10 hover:bg-white/20 transition-all">
                                    <span class="font-medium text-white/90 text-[11px] uppercase tracking-widest">{{ __('CN & Ngày lễ') }}</span>
                                    <span class="font-bold text-vttu-yellow text-sm">
                                        {{ \App\Models\SystemSetting::get('sunday_holiday_hours', 'Nghỉ') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Khám phá VTTU / Liên kết nhanh -->
                        <div class="bg-white p-4 rounded-md shadow-sm border border-slate-100" data-aos="fade-left">
                            <div class="flex items-center gap-3 border-b border-slate-100 pb-3 mb-4">
                                <div class="w-8 h-8 bg-vttu-red/5 rounded-sm flex items-center justify-center text-vttu-red animate-pulse">
                                    <i class="fas fa-compass text-sm"></i>
                                </div>
                                <h3 class="text-xs font-black text-vttu-dark uppercase tracking-wider">{{ __('KHÁM PHÁ VTTU 360') }}</h3>
                            </div>
                            
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('site.page', 'tai-lieu-so') }}" class="group flex items-center gap-3 p-2.5 rounded bg-gradient-to-r from-red-800 to-red-700 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                    <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-file-invoice text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ __('Tài liệu số') }}</span>
                                </a>

                                <a href="{{ route('site.oer.landing') }}" class="group flex items-center gap-3 p-2.5 rounded bg-gradient-to-r from-rose-800 to-rose-700 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                    <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-book-reader text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ __('Tài nguyên giáo dục mở') }}</span>
                                </a>

                                <a href="{{ url('opac') }}" class="group flex items-center gap-3 p-2.5 rounded bg-gradient-to-r from-red-700 to-red-600 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                    <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-file-pdf text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ __('Tài liệu điện tử') }}</span>
                                </a>

                                <a href="{{ route('site.page', 'co-so-du-lieu') }}" class="group flex items-center gap-3 p-2.5 rounded bg-gradient-to-r from-amber-800 to-amber-700 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                    <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-database text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ __('Cơ sở dữ liệu') }}</span>
                                </a>

                                <a href="https://study.vttu.edu.vn/" target="_blank" class="group flex items-center gap-3 p-2.5 rounded bg-gradient-to-r from-amber-700 to-amber-600 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                    <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-graduation-cap text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ __('Học liệu VTTU') }}</span>
                                </a>

                                <a href="{{ route('site.page', 'bgtt-vttu') }}" class="group flex items-center gap-3 p-2.5 rounded bg-gradient-to-r from-lime-800 to-lime-700 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                    <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-video text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ __('Video bài giảng') }}</span>
                                </a>

                                <a href="https://docs.google.com/forms/d/1BrAixTzBMMxlQ-fxHQ2_jFJiD3gQ5CT0aa-AC5miEZg/edit" target="_blank" class="group flex items-center gap-3 p-2.5 rounded bg-gradient-to-r from-emerald-700 to-emerald-600 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                    <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-plus-circle text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ __('Đề nghị bổ sung tài liệu') }}</span>
                                </a>

                                <a href="https://docs.google.com/forms/d/e/1FAIpQLSfA1HzeY02CeQbYO_Gr4oVT91lNBtiu6XnYwDHdFo98B4QO2w/viewform?fbzx=-4713703804503636107&pli=1" target="_blank" class="group flex items-center gap-3 p-2.5 rounded bg-gradient-to-r from-emerald-800 to-emerald-700 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                    <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-poll text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ __('Khảo sát ý kiến bạn đọc') }}</span>
                                </a>

                                <a href="{{ route('site.page', 'chuong-trinh-dao-tao-vttu') }}" class="group flex items-center gap-3 p-2.5 rounded bg-gradient-to-r from-teal-900 to-teal-800 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                    <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-university text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider">{{ __('Chương trình đào tạo VTTU') }}</span>
                                </a>
                            </div>
                        </div>

                        <!-- 2 Tabs: Sách Mới | Nổi bật -->
                        <div class="bg-white p-4 rounded-md shadow-sm border border-slate-100" data-aos="fade-left">
                            <div class="flex bg-slate-50 p-1 rounded-sm mb-4" id="resource-tabs">
                                <button @click="loadResourceTab($event, 'new', 'resource-tabs', 'resources-content')"
                                    class="tab-btn flex-1 py-2 text-[10px] font-bold text-center rounded-sm transition-all {{ ($activeResourceType ?? 'new') === 'new' ? 'text-white bg-vttu-red shadow-sm uppercase tracking-widest' : 'text-slate-400 hover:text-vttu-red uppercase tracking-widest' }}">
                                    {{ __('Mới') }}
                                </button>
                                <button @click="loadResourceTab($event, 'featured', 'resource-tabs', 'resources-content')"
                                    class="tab-btn flex-1 py-2 text-[10px] font-bold text-center rounded-sm transition-all {{ ($activeResourceType ?? '') === 'featured' ? 'text-white bg-vttu-red shadow-sm uppercase tracking-widest' : 'text-slate-400 hover:text-vttu-red uppercase tracking-widest' }}">
                                    {{ __('Nổi bật') }}
                                </button>
                            </div>
                            <div id="resources-content" class="min-h-[200px]">
                                @include('site.pages.partials.sidebar-books', ['sidebarBooks' => $sidebarBooks])
                            </div>
                        </div>

                        <!-- Video Section -->
                        <div class="bg-white rounded-md shadow-sm border border-slate-100 p-4" data-aos="fade-left">
                            <h3 class="text-sm font-bold text-vttu-dark mb-3 uppercase tracking-tight">{{ __('VIDEO') }}</h3>
                            
                            <!-- Main Video Player -->
                            @if(isset($sidebarVideos) && $sidebarVideos->count() > 0)
                                @php $firstVideo = $sidebarVideos->first(); @endphp
                                <div class="aspect-video bg-slate-900 rounded-sm relative overflow-hidden group shadow-sm" id="main-video-container">
                                    @if($firstVideo->video_url)
                                        <iframe src="{{ $firstVideo->video_url }}" 
                                                class="w-full h-full" 
                                                frameborder="0" 
                                                allowfullscreen 
                                                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                        </iframe>
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center z-10">
                                            <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white group-hover:scale-110 transition-transform border border-white/30">
                                                <i class="fas fa-play text-[10px]"></i>
                                            </div>
                                        </div>
                                        <img src="{{ $firstVideo->featured_image ?? 'https://img.freepik.com/free-vector/video-streaming-concept-illustration_114360-10731.jpg' }}" 
                                             class="w-full h-full object-cover opacity-60">
                                    @endif
                                </div>
                                <p class="text-[11px] font-medium text-vttu-dark mt-3 leading-snug line-clamp-2">{{ $firstVideo->title }}</p>
                                
                                <!-- Video List (5 items) -->
                                <div class="space-y-2 mt-4 border-t border-slate-100 pt-3">
                                    @foreach($sidebarVideos->take(5) as $video)
                                        <div class="flex gap-2 items-center group cursor-pointer p-2 rounded-sm hover:bg-slate-50 transition-colors video-item" 
                                             data-video-url="{{ $video->video_url }}" 
                                             data-video-title="{{ $video->title }}"
                                             data-video-image="{{ $video->featured_image ?? 'https://img.freepik.com/free-vector/video-streaming-concept-illustration_114360-10731.jpg' }}">
                                            <div class="w-16 h-10 flex-shrink-0 bg-slate-900 rounded-sm relative overflow-hidden shadow-sm">
                                                <img src="{{ $video->featured_image ?? 'https://img.freepik.com/free-vector/video-streaming-concept-illustration_114360-10731.jpg' }}" 
                                                     class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                                                <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/10 transition-colors z-10">
                                                    <div class="w-5 h-5 bg-white/90 backdrop-blur rounded-full flex items-center justify-center text-vttu-red shadow-sm">
                                                        <i class="fas fa-play text-[7px] ml-0.5"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <a href="{{ $video->url }}" class="text-[10px] font-bold text-vttu-dark leading-snug line-clamp-2 group-hover:text-vttu-red transition-colors">
                                                    {{ $video->title }}
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="aspect-video bg-slate-900 rounded-sm relative overflow-hidden group cursor-pointer shadow-sm">
                                    <div class="absolute inset-0 flex items-center justify-center z-10">
                                        <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white group-hover:scale-110 transition-transform border border-white/30">
                                            <i class="fas fa-play text-[10px]"></i>
                                        </div>
                                    </div>
                                    <img src="https://img.freepik.com/free-vector/video-streaming-concept-illustration_114360-10731.jpg" class="w-full h-full object-cover opacity-60">
                                </div>
                                <p class="text-[11px] font-medium text-vttu-dark mt-3 leading-snug">{{ __('Chưa có video nào') }}</p>
                            @endif
                        </div>


                    </div>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom Slider (Network Logos) -->
    <section class="py-8 bg-white border-t border-slate-100">
        <div class="px-4 md:px-12 lg:px-24">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-6">
                <span class="text-xs font-bold text-white bg-vttu-red px-4 py-1.5 rounded-sm shadow-sm whitespace-nowrap uppercase">
                    {{ __('VTTU LIB NETWORK') }}
                </span>
                <div class="flex gap-2">
                    <button onclick="prevSlide()" class="w-8 h-8 rounded-sm border border-slate-200 flex items-center justify-center hover:bg-vttu-red hover:text-white transition-all shadow-sm"><i class="fas fa-chevron-left text-[10px]"></i></button>
                    <button onclick="nextSlide()" class="w-8 h-8 rounded-sm border border-slate-200 flex items-center justify-center hover:bg-vttu-red hover:text-white transition-all shadow-sm"><i class="fas fa-chevron-right text-[10px]"></i></button>
                </div>
            </div>

            @if($networkLogos->count() > 0)
                <div class="relative overflow-hidden">
                    <div id="networkSlider" class="flex gap-4 transition-transform duration-500 ease-in-out" style="transform: translateX(0)">
                        @foreach($networkLogos as $logo)
                            <a href="{{ $logo->url }}" target="_blank" class="flex-shrink-0 w-1/2 md:w-1/4 min-h-[120px] p-6 bg-slate-50 rounded-md border border-slate-100 flex flex-col items-center justify-center text-center group hover:bg-white hover:shadow-md transition-all duration-300">
                                @if($logo->logo_path && file_exists(storage_path('app/public/' . $logo->logo_path)))
                                    <img src="{{ asset('storage/' . $logo->logo_path) }}" alt="{{ $logo->name }}" class="h-12 object-contain mb-4 group-hover:scale-110 transition-transform">
                                @else
                                    <div class="w-12 h-12 bg-white rounded-sm shadow-sm flex items-center justify-center mb-4 group-hover:bg-vttu-red group-hover:text-white transition-all">
                                        <i class="fas fa-university text-xl"></i>
                                    </div>
                                @endif
                                <span class="font-bold text-vttu-dark text-sm uppercase tracking-wider">{{ $logo->name }}</span>
                                <p class="text-[9px] font-medium text-slate-400 mt-1 uppercase tracking-widest">{{ __('Library Information System') }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @for($i=1; $i<=4; $i++)
                    <div class="p-6 bg-slate-50 rounded-md border border-slate-100 flex flex-col items-center justify-center text-center group hover:bg-white hover:shadow-md transition-all duration-300">
                        <div class="w-12 h-12 bg-white rounded-sm shadow-sm flex items-center justify-center mb-4 group-hover:bg-vttu-red group-hover:text-white transition-all">
                            <i class="fas fa-university text-xl"></i>
                        </div>
                        <span class="font-bold text-vttu-dark text-sm uppercase tracking-wider">VTTU LIB {{ $i }}</span>
                        <p class="text-[9px] font-medium text-slate-400 mt-1 uppercase tracking-widest">{{ __('Library Information System') }}</p>
                    </div>
                    @endfor
                </div>
            @endif
        </div>
    </section>
</div>

<style>
    .animate-float { animation: float 6s ease-in-out infinite; }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    
    /* Loading animation for AJAX */
    .tab-loading { opacity: 0.5; pointer-events: none; }
    
    /* Force auto height and prevent stretching in Book Introduction Swiper */
    .book-intro-swiper-container {
        height: auto !important;
    }
    .book-intro-swiper-container .swiper-wrapper {
        height: auto !important;
        align-items: flex-start !important;
    }
</style>
@section('scripts')
<script>
    function catalogWizard() {
        if (window.homeWizard) return window.homeWizard;

        window.homeWizard = {
            currentTab: 'book',
            sidebarOpen: true,
            pages: {
                book: 1,
                journal: 1,
                folder: 1
            },
            hasMore: {
                book: true,
                journal: true,
                folder: true
            },
            loadingMore: false,
            loadTab(type, tabsId, contentId, eventOverride = null) {
                this.currentTab = type;
                this.pages[type] = 1;
                this.hasMore[type] = true;
                this.loadingMore = false;
                
                const target = eventOverride ? eventOverride.currentTarget : event.currentTarget;
                const contentDiv = document.getElementById(contentId);
                const tabsDiv = document.getElementById(tabsId);
                
                if (!contentDiv || !tabsDiv) return;

                contentDiv.classList.add('tab-loading');
                
                fetch(`{{ route('home') }}?type=${type}&page=1`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    contentDiv.innerHTML = html;
                    contentDiv.classList.remove('tab-loading');
                    
                    tabsDiv.querySelectorAll('.tab-btn').forEach(btn => {
                        btn.classList.remove('text-white', 'bg-vttu-red', 'shadow-sm');
                        btn.classList.add('text-slate-400', 'hover:text-vttu-red');
                    });
                    target.classList.remove('text-slate-400', 'hover:text-vttu-red');
                    target.classList.add('text-white', 'bg-vttu-red', 'shadow-sm');

                    // Re-init Swiper for new content
                    this.initBooksSwiper();

                    // Console log dữ liệu vừa get được
                    const books = contentDiv.querySelectorAll('.swiper-slide:not(.py-12)'); // py-12 là cho slide trống (empty)
                    console.log(`[AJAX] Loaded tab: ${type}. Total books found: ${books.length}`);
                })
                .catch(error => {
                    console.error('Error loading tab:', error);
                    contentDiv.classList.remove('tab-loading');
                });
            },
            initBooksSwiper() {
                // Đảm bảo Swiper cũ được destroy nếu cần hoặc chỉ khởi tạo nếu có container
                const container = document.querySelector('.books-swiper-container');
                if (container) {
                    if (container.swiper) container.swiper.destroy();
                    const self = window.homeWizard;
                    new Swiper('.books-swiper-container', {
                        autoHeight: true,
                        slidesPerView: 1,
                        slidesPerGroup: 1,
                        spaceBetween: 12,
                        centeredSlides: false,
                        observer: true,
                        observeParents: true,
                        navigation: {
                            nextEl: '.books-next',
                            prevEl: '.books-prev',
                        },
                        breakpoints: {
                            520: {
                                slidesPerView: 2,
                                slidesPerGroup: 2,
                                spaceBetween: 12
                            },
                            768: {
                                slidesPerView: 3,
                                slidesPerGroup: 3,
                                spaceBetween: 16
                            },
                            1024: {
                                slidesPerView: 4,
                                slidesPerGroup: 4,
                                spaceBetween: 16
                            }
                        },
                        on: {
                            slideChange: function() {
                                const activeIndex = this.activeIndex;
                                if (this.lastActiveIndex === undefined) {
                                    this.lastActiveIndex = 0;
                                }
                                console.log(`[Swiper] Slide changed. Active: ${activeIndex}, Last: ${this.lastActiveIndex}`);
                                if (activeIndex > this.lastActiveIndex) {
                                    self.loadNextPage();
                                }
                                this.lastActiveIndex = activeIndex;
                            }
                        }
                    });
                }
            },
            loadNextPage() {
                const type = this.currentTab;
                if (!this.hasMore[type] || this.loadingMore) return;

                const container = document.querySelector('.books-swiper-container');
                if (!container || !container.swiper) return;
                const wrapper = container.querySelector('.swiper-wrapper');
                if (!wrapper) return;

                const currentSlides = wrapper.querySelectorAll('.swiper-slide:not(.py-12)');
                const offset = currentSlides.length;

                this.loadingMore = true;
                console.log(`[LazyLoad] Requesting book at offset ${offset} for tab ${type}...`);
                
                fetch(`{{ route('home') }}?type=${type}&offset=${offset}&limit=4&only_slides=1`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    const trimmedHtml = html.trim();
                    if (trimmedHtml === '' || trimmedHtml.includes('swiper-slide w-full py-12 text-center')) {
                        console.log(`[LazyLoad] No more items found for tab ${type}.`);
                        this.hasMore[type] = false;
                        this.loadingMore = false;
                        return;
                    }
                    
                    const temp = document.createElement('div');
                    temp.innerHTML = trimmedHtml;
                    
                    let count = 0;
                    while (temp.firstChild) {
                        if (temp.firstChild.nodeType === 1) { // Element node
                            wrapper.appendChild(temp.firstChild);
                            count++;
                        } else {
                            temp.removeChild(temp.firstChild);
                        }
                    }
                    
                    if (count > 0) {
                        console.log(`[LazyLoad] Appended ${count} new slide(s). Updating Swiper...`);
                        container.swiper.update();
                    } else {
                        this.hasMore[type] = false;
                    }
                    this.loadingMore = false;
                })
                .catch(error => {
                    console.error('Error loading next page:', error);
                    this.loadingMore = false;
                });
            },
            loadResourceTab(event, type, tabsId, contentId) {
                const target = event.currentTarget;
                const contentDiv = document.getElementById(contentId);
                const tabsDiv = document.getElementById(tabsId);
                
                if (!contentDiv || !tabsDiv) return;

                console.log('--- SIDEBAR TAB LOAD ---');
                console.log('Type requested:', type);
                console.log('Target URL:', `{{ route('home') }}?resource_type=${type}`);

                contentDiv.classList.add('opacity-50', 'pointer-events-none', 'transition-opacity');
                
                fetch(`{{ route('home') }}?resource_type=${type}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text();
                })
                .then(html => {
                    console.log('--- AJAX RESPONSE DEBUG ---');
                    console.log('HTML Length:', html.length);
                    
                    // Tạo một temp element để parse HTML và đếm số lượng bản ghi
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    // Mỗi cuốn sách trong sidebar-books.blade.php nằm trong một thẻ <a>
                    const items = doc.querySelectorAll('a[href*="/opac/book/"]');
                    console.log('Found book items in response:', items.length);
                    
                    if (items.length === 0) {
                        console.warn('No items found in the HTML response. Possible causes: empty query result, wrong template being rendered, or status filtering.');
                    } else {
                        console.log('Sample book title:', items[0].querySelector('h4')?.textContent?.trim());
                    }

                    contentDiv.innerHTML = html;
                    contentDiv.classList.remove('opacity-50', 'pointer-events-none');
                    
                    tabsDiv.querySelectorAll('.tab-btn').forEach(btn => {
                        btn.classList.remove('text-white', 'bg-vttu-red', 'shadow-lg', 'shadow-vttu-red/20');
                        btn.classList.add('text-slate-400', 'hover:text-vttu-red');
                    });
                    target.classList.remove('text-slate-400', 'hover:text-vttu-red');
                    target.classList.add('text-white', 'bg-vttu-red', 'shadow-lg', 'shadow-vttu-red/20');
                })
                .catch(err => console.error('Tab Load Error:', err));
            },
            loadMedicalTab(type, tabsId, contentId, eventOverride = null) {
                const target = eventOverride ? eventOverride.currentTarget : event.currentTarget;
                const contentDiv = document.getElementById(contentId);
                const tabsDiv = document.getElementById(tabsId);
                
                if (!contentDiv || !tabsDiv) return;

                contentDiv.classList.add('tab-loading');
                
                fetch(`{{ route('home') }}?medical_type=${type}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    contentDiv.innerHTML = html;
                    contentDiv.classList.remove('tab-loading');
                    
                    tabsDiv.querySelectorAll('.tab-btn').forEach(btn => {
                        btn.classList.remove('text-white', 'bg-vttu-red', 'shadow-lg', 'shadow-vttu-red/20');
                        btn.classList.add('text-slate-400', 'hover:text-vttu-red');
                    });
                    target.classList.remove('text-slate-400', 'hover:text-vttu-red');
                    target.classList.add('text-white', 'bg-vttu-red', 'shadow-lg', 'shadow-vttu-red/20');

                    // Khởi tạo Swiper 4-item cho tab chuyên đề Y khoa
                    new Swiper('#medical-content .medical-swiper-container', {
                        autoHeight: true,
                        slidesPerView: 1,
                        slidesPerGroup: 1,
                        spaceBetween: 12,
                        centeredSlides: false,
                        observer: true,
                        observeParents: true,
                        navigation: {
                            nextEl: '#medical-content .medical-next',
                            prevEl: '#medical-content .medical-prev',
                        },
                        breakpoints: {
                            520: {
                                slidesPerView: 2,
                                slidesPerGroup: 2,
                                spaceBetween: 12
                            },
                            768: {
                                slidesPerView: 3,
                                slidesPerGroup: 3,
                                spaceBetween: 16
                            },
                            1024: {
                                slidesPerView: 4,
                                slidesPerGroup: 4,
                                spaceBetween: 16
                            }
                        }
                    });
                });
            },
            loadNewsTab(type, tabsId, contentId, eventOverride = null) {
                const target = eventOverride ? eventOverride.currentTarget : event.currentTarget;
                const contentDiv = document.getElementById(contentId);
                const tabsDiv = document.getElementById(tabsId);
                
                if (!contentDiv || !tabsDiv) return;

                contentDiv.classList.add('tab-loading');
                
                fetch(`{{ route('home') }}?news_type=${type}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    contentDiv.innerHTML = html;
                    contentDiv.classList.remove('tab-loading');
                    
                    tabsDiv.querySelectorAll('.tab-btn').forEach(btn => {
                        btn.classList.remove('text-white', 'bg-vttu-red', 'shadow-sm');
                        btn.classList.add('text-slate-400', 'hover:text-vttu-red');
                    });
                    target.classList.remove('text-slate-400', 'hover:text-vttu-red');
                    target.classList.add('text-white', 'bg-vttu-red', 'shadow-sm');

                    this.initNewsSwiper();
                });
            },
            initNewsSwiper() {
                const container = document.querySelector('.news-swiper-container');
                if (container) {
                    if (container.swiper) container.swiper.destroy();
                    new Swiper('.news-swiper-container', {
                        autoHeight: true,
                        slidesPerView: 1,
                        slidesPerGroup: 1,
                        spaceBetween: 12,
                        observer: true,
                        observeParents: true,
                        navigation: {
                            nextEl: '.news-next',
                            prevEl: '.news-prev',
                        },
                        breakpoints: {
                            520: {
                                slidesPerView: 2,
                                slidesPerGroup: 2,
                                spaceBetween: 12
                            },
                            768: {
                                slidesPerView: 3,
                                slidesPerGroup: 3,
                                spaceBetween: 16
                            },
                            1024: {
                                slidesPerView: 4,
                                slidesPerGroup: 4,
                                spaceBetween: 16
                            }
                        }
                    });
                }
            }
        }
        return window.homeWizard;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const heroSwiper = new Swiper('.heroSwiper', {
            loop: true,
            autoHeight: true,
            autoplay: {
                delay: 10000,
                disableOnInteraction: false,
            },
            on: {
                init: function() {
                    setTimeout(() => {
                        AOS.refresh();
                    }, 300);
                },
                imagesReady: function() {
                    AOS.refresh();
                },
                slideChange: function() {
                    setTimeout(() => {
                        AOS.refresh();
                    }, 300);
                }
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + ' !bg-white !w-3 !h-3"></span>';
                },
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            speed: 1000,
        });

        // Đảm bảo AOS được tính toán lại sau khi toàn bộ ảnh trang web tải xong
        window.addEventListener('load', function() {
            setTimeout(() => {
                AOS.refresh();
            }, 200);
        });
        const wizard = catalogWizard();

        // Force load tab sách đầu tiên bằng AJAX khi vào trang
        const firstTab = document.querySelector('#book-tabs .tab-btn');
        if (firstTab) {
            const mockEvent = { currentTarget: firstTab };
            wizard.loadTab('book', 'book-tabs', 'books-content', mockEvent);
        }

        // Force load tab tin tức đầu tiên bằng AJAX khi vào trang
        const firstNewsTab = document.querySelector('#news-tabs .tab-btn');
        if (firstNewsTab) {
            const mockNewsEvent = { currentTarget: firstNewsTab };
            wizard.loadNewsTab('news', 'news-tabs', 'news-content', mockNewsEvent);
        }

        // Force load tab y khoa đầu tiên bằng AJAX khi vào trang
        const firstMedicalTab = document.querySelector('#medical-tabs .tab-btn');
        if (firstMedicalTab) {
            const mockMedicalEvent = { currentTarget: firstMedicalTab };
            wizard.loadMedicalTab('Sản khoa', 'medical-tabs', 'medical-content', mockMedicalEvent);
        }

        // Initialize Book Introduction Swiper
        new Swiper('.book-intro-swiper-container', {
            slidesPerView: 1,
            slidesPerGroup: 1,
            spaceBetween: 12,
            centeredSlides: false,
            observer: true,
            observeParents: true,
            navigation: {
                nextEl: '.book-intro-next',
                prevEl: '.book-intro-prev',
            },
            breakpoints: {
                520: {
                    slidesPerView: 2,
                    slidesPerGroup: 2,
                    spaceBetween: 12
                },
                768: {
                    slidesPerView: 3,
                    slidesPerGroup: 3,
                    spaceBetween: 16
                },
                1024: {
                    slidesPerView: 4,
                    slidesPerGroup: 4,
                    spaceBetween: 16
                }
            }
        });
    });

    // Network Logos Slider - Auto-slide liên tục từ trái sang phải
    let sliderPosition = 0;
    let autoSlideInterval;
    let itemsPerPage = 1;
    let totalItems = 0;
    let originalItemCount = 0;

    function duplicateItems() {
        const slider = document.getElementById('networkSlider');
        if (!slider) return;

        const sliderChildren = slider.querySelectorAll('a[href]');
        // Chỉ lấy số lượng items gốc ban đầu để nhân bản, tránh nhân bản đã nhân bản
        const itemsLength = sliderChildren.length;
        const itemsToDuplicate = Array.from(sliderChildren).slice(0, originalItemCount);
        
        // Nhân bản 3 lần
        itemsToDuplicate.forEach(item => {
            slider.appendChild(item.cloneNode(true));
        });
        itemsToDuplicate.forEach(item => {
            slider.appendChild(item.cloneNode(true));
        });
        itemsToDuplicate.forEach(item => {
            slider.appendChild(item.cloneNode(true));
        });
        
        totalItems = slider.querySelectorAll('a[href]').length;
        console.log(`[Slider] Items duplicated. Original: ${originalItemCount}, Current total: ${totalItems}`);
    }

    function initNetworkSlider() {
        const slider = document.getElementById('networkSlider');
        if (!slider) return;

        // Reset position và clear interval
        sliderPosition = 0;
        if (autoSlideInterval) clearInterval(autoSlideInterval);

        // Tính toán số item trên màn hình
        const container = slider.parentElement;
        const containerWidth = container.offsetWidth;
        const sliderChildren = slider.querySelectorAll('a[href]');
        
        // Lần đầu, lưu lại originalItemCount
        if (originalItemCount === 0) {
            originalItemCount = sliderChildren.length;
        }
        
        totalItems = sliderChildren.length;
        
        if (totalItems === 0) return;

        // Lấy width của một item
        const firstItem = sliderChildren[0];
        const itemWidth = firstItem.offsetWidth;
        const gap = 16; // gap-4 = 16px
        
        // Tính items per page dựa trên responsive
        const windowWidth = window.innerWidth;
        if (windowWidth < 768) {
            itemsPerPage = 2;
        } else {
            itemsPerPage = 4;
        }

        // Nhân bản items ban đầu lên 3 lần (chỉ lần đầu)
        if (sliderChildren.length === originalItemCount) {
            duplicateItems();
        }
        
        startAutoSlide();
    }

    function startAutoSlide() {
        if (autoSlideInterval) clearInterval(autoSlideInterval);
        
        autoSlideInterval = setInterval(() => {
            const slider = document.getElementById('networkSlider');
            if (!slider) return;

            const sliderChildren = slider.querySelectorAll('a[href]');
            if (sliderChildren.length === 0) return;

            // Lấy width của một item + gap
            const firstItem = sliderChildren[0];
            const itemWidth = firstItem.offsetWidth;
            const gap = 16; // gap-4
            const shift = itemWidth + gap;

            // Tăng vị trí
            sliderPosition += shift;

            // Khi trượt đến index = totalItems - 4, nhân bản lại
            const currentIndex = Math.floor(sliderPosition / shift);
            if (currentIndex >= totalItems - 4) {
                console.log(`[Slider] Reached index ${currentIndex} (threshold: ${totalItems - 4}), duplicating items again...`);
                duplicateItems();
            }

            // Áp dụng transform
            slider.style.transform = `translateX(-${sliderPosition}px)`;
        }, 4000); // Slide mỗi 4 giây
    }

    function nextSlide() {
        const slider = document.getElementById('networkSlider');
        if (!slider) return;

        const sliderChildren = slider.querySelectorAll('a[href]');
        if (sliderChildren.length === 0) return;

        const firstItem = sliderChildren[0];
        const itemWidth = firstItem.offsetWidth;
        const gap = 16;
        const shift = itemWidth + gap;

        sliderPosition += shift;
        
        // Khi trượt đến index = totalItems - 4, nhân bản lại
        const currentIndex = Math.floor(sliderPosition / shift);
        if (currentIndex >= totalItems - 4) {
            console.log(`[Slider] nextSlide: Reached index ${currentIndex}, duplicating items...`);
            duplicateItems();
        }

        slider.style.transform = `translateX(-${sliderPosition}px)`;
        
        // Reset auto-slide timer
        if (autoSlideInterval) clearInterval(autoSlideInterval);
        startAutoSlide();
    }

    function prevSlide() {
        const slider = document.getElementById('networkSlider');
        if (!slider) return;

        const sliderChildren = slider.querySelectorAll('a[href]');
        if (sliderChildren.length === 0) return;

        const firstItem = sliderChildren[0];
        const itemWidth = firstItem.offsetWidth;
        const gap = 16;
        const shift = itemWidth + gap;

        sliderPosition -= shift;
        if (sliderPosition < 0) {
            sliderPosition = 0;
        }

        slider.style.transform = `translateX(-${sliderPosition}px)`;
        
        // Reset auto-slide timer
        if (autoSlideInterval) clearInterval(autoSlideInterval);
        startAutoSlide();
    }

    // Initialize slider khi page load
    document.addEventListener('DOMContentLoaded', function() {
        initNetworkSlider();
        
        // Tự động kích hoạt tab y khoa đầu tiên (Sản khoa)
        const firstMedicalTab = document.querySelector('#medical-tabs .tab-btn');
        if (firstMedicalTab) {
            firstMedicalTab.click();
        }
        
        // Re-init khi resize window
        window.addEventListener('resize', function() {
            sliderPosition = 0;
            initNetworkSlider();
        });

        // Video List Click Handler
        const videoItems = document.querySelectorAll('.video-item');
        videoItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                const videoUrl = this.dataset.videoUrl;
                const videoTitle = this.dataset.videoTitle;
                const videoImage = this.dataset.videoImage;
                const mainContainer = document.getElementById('main-video-container');
                
                if (mainContainer && videoUrl) {
                    // Không thêm autoplay - user bấm play manually
                    mainContainer.innerHTML = `
                        <iframe src="${videoUrl}" 
                                class="w-full h-full" 
                                frameborder="0" 
                                allowfullscreen 
                                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                        </iframe>
                    `;
                    
                    // Update title
                    const titleElement = mainContainer.nextElementSibling;
                    if (titleElement) {
                        titleElement.textContent = videoTitle;
                    }
                    
                    // Highlight selected video item
                    videoItems.forEach(v => v.classList.remove('bg-slate-100'));
                    this.classList.add('bg-slate-100');
                }
            });
        });
    });
</script>
@endsection
