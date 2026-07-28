@extends('layouts.admin')

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                <i data-lucide="mail" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight">{{ __('Quản Lý Mail') }}</h1>
                <p class="text-xs font-medium text-slate-400 dark:text-slate-500">{{ __('Hệ thống quản lý, theo dõi hàng đợi gửi email tự động.') }}</p>
            </div>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                {{ __('Đang Phát Triển') }}
            </span>
        </div>
    </div>

    <!-- Glassmorphic Banner Info -->
    <div class="relative overflow-hidden rounded-2xl border border-indigo-100/80 dark:border-indigo-900/30 bg-gradient-to-br from-indigo-50/50 via-white to-violet-50/30 dark:from-indigo-950/20 dark:via-slate-900 dark:to-violet-950/10 p-6 md:p-8 shadow-sm">
        <!-- Abstract Background Shapes -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-400/10 dark:bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-violet-400/10 dark:bg-violet-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row items-center gap-6 z-10">
            <!-- Icon/Visual -->
            <div class="relative flex-shrink-0 w-24 h-24 flex items-center justify-center">
                <div class="absolute inset-0 bg-indigo-500/10 dark:bg-indigo-500/20 rounded-full animate-ping opacity-75"></div>
                <div class="relative w-20 h-20 rounded-full bg-gradient-to-tr from-indigo-500 via-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-xl shadow-indigo-500/30">
                    <i data-lucide="cog" class="w-10 h-10 animate-spin" style="animation-duration: 8s;"></i>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 text-center md:text-left space-y-3">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                    {{ __('Tính năng Mail Management đang được hoàn thiện') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-2xl leading-relaxed">
                    {{ __('Chúng tôi đang xây dựng một module quản lý thư điện tử toàn diện, tích hợp trực tiếp dịch vụ gửi mail, cấu hình mẫu email động, và báo cáo thống kê trạng thái gửi thư theo thời gian thực.') }}
                </p>
                
                <!-- Progress -->
                <div class="pt-2 max-w-md">
                    <div class="flex justify-between items-center text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        <span>{{ __('Tiến độ hoàn thành dự kiến') }}</span>
                        <span class="text-indigo-600 dark:text-indigo-400">75%</span>
                    </div>
                    <div class="w-full bg-slate-200/70 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-violet-600 h-full rounded-full transition-all duration-1000" style="width: 75%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Preview of Upcoming Features -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ __('Các chức năng sắp ra mắt') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1 -->
            <div class="group bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-xl p-5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-900/60 transition-all duration-300 transform hover:-translate-y-1">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-1.5">{{ __('Hàng đợi gửi thư') }}</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed">{{ __('Xem danh sách thư đang đợi gửi, thử gửi lại email lỗi, quản lý tốc độ gửi.') }}</p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded">{{ __('Queue Monitor') }}</span>
                    <span class="text-[10px] text-slate-300 dark:text-slate-600 font-medium">Coming Soon</span>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="group bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-xl p-5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-900/60 transition-all duration-300 transform hover:-translate-y-1">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="layout" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-1.5">{{ __('Mẫu thư (Templates)') }}</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed">{{ __('Quản lý danh sách mẫu mail thông báo, khảo sát, tin tức với các biến động linh hoạt.') }}</p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded">{{ __('WYSIWYG Editor') }}</span>
                    <span class="text-[10px] text-slate-300 dark:text-slate-600 font-medium">Coming Soon</span>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="group bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-xl p-5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-900/60 transition-all duration-300 transform hover:-translate-y-1">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="history" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-1.5">{{ __('Lịch sử & Thống kê') }}</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed">{{ __('Theo dõi tỷ lệ gửi thành công, thất bại, và xem lại nhật ký nội dung thư đã gửi.') }}</p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded">{{ __('Analytics') }}</span>
                    <span class="text-[10px] text-slate-300 dark:text-slate-600 font-medium">Coming Soon</span>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="group bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-xl p-5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-900/60 transition-all duration-300 transform hover:-translate-y-1">
                <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-600 dark:bg-purple-950/30 dark:text-purple-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="sliders" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-700 dark:text-slate-200 mb-1.5">{{ __('Cấu hình SMTP') }}</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed">{{ __('Tùy chỉnh thông tin kết nối máy chủ gửi mail của trường hoặc nhà cung cấp ngoài.') }}</p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-purple-600 dark:text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded">{{ __('SMTP Settings') }}</span>
                    <span class="text-[10px] text-slate-300 dark:text-slate-600 font-medium">Coming Soon</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Back to Dashboard CTA -->
    <div class="flex justify-center md:justify-start">
        <a href="{{ url('/topsecret') }}" class="inline-flex items-center gap-2 px-5 py-2.5 h-10 rounded-lg text-xs font-semibold transition-all duration-200 active:scale-95 bg-slate-800 text-white hover:bg-slate-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 shadow-md hover:shadow-lg">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            {{ __('Quay về Trang chủ quản trị') }}
        </a>
    </div>
</div>
@endsection
