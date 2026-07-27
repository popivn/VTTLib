<div class="not-prose w-full py-8 text-center space-y-6">
    <div class="max-w-xl mx-auto space-y-4">
        <!-- Floating Animated Icon -->
        <div class="w-16 h-16 rounded-full bg-rose-50 dark:bg-rose-950/20 text-[#A80D0D] flex items-center justify-center mx-auto border border-rose-100 dark:border-rose-900/30 shadow-sm animate-bounce" style="animation-duration: 3s;">
            <i data-lucide="book-open" class="w-8 h-8"></i>
        </div>
        
        <!-- Explanatory Text -->
        <h2 class="text-lg font-black text-foreground uppercase tracking-wider">
            {{ __('Tra cứu tài liệu giấy') }}
        </h2>

        <!-- Redirect Button using Route Name -->
        <div class="pt-4">
            <a href="{{ route('site.opac') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-[#A80D0D] hover:bg-[#8f0b0b] !text-white text-xs font-black rounded uppercase tracking-wider transition-all duration-300 shadow-md transform hover:scale-[1.02] active:scale-[0.98]">
                <i data-lucide="search" class="w-4 h-4"></i>
                <span>{{ __('Chuyển hướng đến hệ thống OPAC') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
</div>
