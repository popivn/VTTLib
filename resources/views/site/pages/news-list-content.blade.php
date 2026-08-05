<div class="not-prose w-full" x-data="{ loading: false }">
    <!-- Loading Overlay -->
    <div x-show="loading"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/80 backdrop-blur-sm">
        <div class="flex flex-col items-center gap-5">
            <div class="news-loader-logo">
                <img src="{{ asset('assets/imgs/logo-vttu.png') }}" alt="VTTU" class="w-16 h-16 object-contain">
            </div>
            <div class="text-[11px] font-bold text-vttu-dark tracking-[0.1em] uppercase">Đang tải...</div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
        <!-- Left: News List & Filters -->
        <div class="lg:col-span-9 space-y-3">
            <!-- Filter bar -->
            <div class="bg-card border border-border rounded-none p-3 shadow-xs">
                <form action="{{ route('news.search') }}" method="GET" class="flex flex-col sm:flex-row gap-2" @submit="loading = true">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="{{ __('Tìm kiếm tin tức...') }}" class="w-full pl-8 pr-3 py-1.5 bg-background border border-border rounded-none text-xs font-medium text-foreground placeholder:text-muted-foreground focus:ring-1 focus:ring-vttu-red focus:border-vttu-red outline-none h-9">
                        <i data-lucide="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                    </div>
                    
                    <!-- Category Dropdown Filter -->
                    <div class="w-full sm:w-48">
                        <select onchange="window.location.href=this.value" @change="loading = true" class="w-full px-3 py-1.5 bg-background border border-border rounded-none text-xs font-medium text-foreground focus:ring-1 focus:ring-vttu-red focus:border-vttu-red outline-none h-9">
                            <option value="{{ route('news.index') }}">{{ __('Tất cả chuyên mục') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ route('news.category', $cat->slug) }}" {{ isset($category) && $category->id === $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} ({{ $cat->published_news_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <!-- News Cards Grid -->
            @if(isset($news) && $news->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($news as $item)
                        <article class="bg-card text-card-foreground border border-border rounded-none overflow-hidden group hover:bg-muted/30 transition-all duration-300 flex flex-col justify-between h-full">
                            <a href="{{ $item->url }}" @click="loading = true" class="block flex-1 flex flex-col">
                                <!-- Image -->
                                <div class="aspect-video w-full overflow-hidden relative bg-muted">
                                    <img src="{{ $item->featured_image ? (str_starts_with($item->featured_image, 'http') ? $item->featured_image : asset($item->featured_image)) : 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=400&q=80' }}" 
                                         alt="{{ $item->title }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @if($item->category)
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-none bg-background/90 text-vttu-red text-[10px] font-black uppercase tracking-wider border border-border shadow-sm">
                                            {{ $item->category->name }}
                                        </span>
                                    @endif
                                </div>
                                <!-- Body -->
                                <div class="p-3 flex-1 flex flex-col justify-between space-y-2">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center text-muted-foreground text-[10px] font-bold uppercase tracking-wider">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 mr-1.5 flex-shrink-0"></i>
                                            <span>{{ $item->published_at ? $item->published_at->format('d/m/Y') : $item->created_at->format('d/m/Y') }}</span>
                                        </div>
                                        <h3 class="text-sm font-bold text-foreground group-hover:text-vttu-red transition-colors line-clamp-2 !leading-snug">
                                            {{ $item->title }}
                                        </h3>
                                        <p class="text-muted-foreground text-[11px] line-clamp-3 leading-relaxed">
                                            {{ $item->summary }}
                                        </p>
                                    </div>
                                    <div class="pt-2">
                                        <span class="inline-flex items-center gap-1 text-vttu-red text-[10px] font-black uppercase tracking-wider group-hover:translate-x-1 transition-transform">
                                            <span>{{ __('Đọc tiếp') }}</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($news->hasPages())
                    <div class="mt-4 pt-4 border-t border-border flex justify-center" id="news-pagination">
                        {{ $news->links() }}
                    </div>
                @endif
            @else
                <div class="bg-card border border-border rounded-none p-6 text-center shadow-xs">
                    <div class="w-12 h-12 bg-muted rounded-none flex items-center justify-center mx-auto mb-4 text-muted-foreground">
                        <i data-lucide="newspaper" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-black text-foreground mb-1">{{ __('Không tìm thấy bài viết nào') }}</h3>
                    <p class="text-xs text-muted-foreground font-medium mb-4">{{ __('Vui lòng quay lại sau hoặc thử từ khóa tìm kiếm khác.') }}</p>
                    <a href="{{ route('news.index') }}" @click="loading = true" class="inline-flex px-4 py-2 bg-[#A80D0D] hover:bg-[#8f0b0b] !text-white text-xs font-black rounded-none uppercase tracking-wider transition-colors shadow-sm">
                        {{ __('Xem tất cả tin tức') }}
                    </a>
                </div>
            @endif
        </div>

        <!-- Right Column -->
        <aside class="lg:col-span-3 space-y-3 sticky top-20 self-start">
            @php
                $sidebarQuery = \App\Models\News::where('status', 'published')
                    ->orderBy('published_at', 'desc');

                if (isset($category) && $category) {
                    $sidebarQuery->where('category_id', $category->id);
                    $sidebarHeader = str_contains(strtolower($category->name), 'video') ? 'VIDEO MỚI NHẤT' : 'TIN MỚI NHẤT';
                } else {
                    $sidebarHeader = 'TIN MỚI NHẤT';
                }

                $sidebarNews = $sidebarQuery->limit(10)->get();
            @endphp

            @if($sidebarNews->count() > 0)
                <div class="bg-card border border-border rounded-none p-3 shadow-xs">
                    <h3 class="text-xs font-black text-foreground uppercase tracking-normal flex items-center mb-3 pb-2 border-b border-border/60 !mt-0 !mb-3 !text-xs !leading-none" style="margin-top: 0 !important; margin-bottom: 0.75rem !important; font-size: 0.75rem !important; line-height: 1 !important;">
                        <span class="w-1 h-3.5 bg-vttu-red rounded-none mr-2 flex-shrink-0"></span>
                        <span class="truncate">{{ $sidebarHeader }}</span>
                    </h3>
                    <div class="space-y-3">
                        @foreach($sidebarNews as $item)
                            <a href="{{ $item->url }}" @click="loading = true" class="flex gap-2.5 group text-foreground hover:text-vttu-red transition-colors items-start">
                                <div class="w-16 h-12 rounded-sm overflow-hidden flex-shrink-0">
                                    <img src="{{ $item->featured_image ? (str_starts_with($item->featured_image, 'http') ? $item->featured_image : asset($item->featured_image)) : 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=160&q=80' }}" 
                                         alt="{{ $item->title }}"
                                         class="w-full h-full object-cover rounded-sm group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-[#A80D0D] hover:text-[#8f0b0b] transition-colors line-clamp-2 !leading-snug break-words">{{ $item->title }}</h4>
                                    <span class="text-[9px] text-muted-foreground font-bold mt-0.5 block uppercase tracking-wider">
                                        {{ $item->published_at ? $item->published_at->format('d/m/Y') : $item->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Contact Box Card -->
            <div class="bg-card border border-border rounded-none p-3 shadow-xs space-y-3 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-16 h-16 bg-vttu-red/5 blur-xl rounded-full -mr-8 -mt-8"></div>
                <h3 class="text-xs font-black text-foreground uppercase tracking-wider relative z-10 flex items-center">
                    <span class="w-1 h-3.5 bg-vttu-red rounded-none mr-2"></span>
                    {{ __('HỖ TRỢ') }}
                </h3>
                <p class="text-xs text-muted-foreground leading-relaxed">
                    {{ __('Liên hệ với chúng tôi để được giải đáp mọi thắc mắc về tài liệu và dịch vụ thư viện.') }}
                </p>
                <div class="pt-1">
                    <a href="/noi-quy-thu-vien" @click="loading = true" class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 bg-[#A80D0D] hover:bg-[#8f0b0b] !text-white text-xs font-black rounded-none uppercase tracking-wider transition-colors shadow-sm">
                        <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                        <span>{{ __('Liên hệ ngay') }}</span>
                    </a>
                </div>
            </div>

            <!-- Popular Tags Sidebar Box -->
            @if(isset($popularTags) && $popularTags->count() > 0)
                <div class="bg-card border border-border rounded-none p-3 shadow-xs space-y-3">
                    <h3 class="text-xs font-black text-foreground uppercase tracking-wider flex items-center">
                        <span class="w-1 h-3.5 bg-vttu-red rounded-none mr-2"></span>
                        {{ __('THẺ PHỔ BIẾN') }}
                    </h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($popularTags as $t)
                            <a href="{{ isset($category) ? route('news.category.tag', ['category_slug' => $category->slug, 'tag_slug' => $t->slug]) : route('news.tag', $t->slug) }}" 
                               @click="loading = true"
                               class="px-2 py-1 bg-muted hover:bg-vttu-red/10 text-muted-foreground hover:text-vttu-red rounded-none text-[10px] font-bold border border-border transition-colors">
                                #{{ $t->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</div>

<style>
    .news-loader-logo { perspective: 200px; }
    .news-loader-logo img {
        animation: news-logo-3d 2s ease-in-out infinite;
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.15));
    }
    @keyframes news-logo-3d {
        0% { transform: rotateY(0deg); }
        50% { transform: rotateY(180deg); }
        100% { transform: rotateY(360deg); }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var pag = document.getElementById('news-pagination');
        if (pag) {
            pag.addEventListener('click', function(e) {
                if (e.target.closest('a')) {
                    var container = e.target.closest('[x-data]');
                    if (container && container.__x) {
                        container.__x.$data.loading = true;
                    }
                }
            });
        }
    });
</script>
