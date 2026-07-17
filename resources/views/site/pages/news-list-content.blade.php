<div class="not-prose w-full space-y-4">
    <!-- Filter bar -->
    <div class="bg-card border border-border rounded p-3 shadow-xs space-y-3">
        <form action="{{ route('news.search') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
            <!-- Search Input -->
            <div class="relative flex-1">
                <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="{{ __('Tìm kiếm tin tức...') }}" class="w-full pl-8 pr-3 py-1.5 bg-background border border-border rounded text-xs font-medium text-foreground placeholder:text-muted-foreground focus:ring-1 focus:ring-vttu-red focus:border-vttu-red outline-none h-9">
                <i data-lucide="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
            </div>
            
            <!-- Category Dropdown Filter -->
            <div class="w-full sm:w-48">
                <select onchange="window.location.href=this.value" class="w-full px-3 py-1.5 bg-background border border-border rounded text-xs font-medium text-foreground focus:ring-1 focus:ring-vttu-red focus:border-vttu-red outline-none h-9">
                    <option value="{{ route('news.index') }}">{{ __('Tất cả chuyên mục') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ route('news.category', $cat->slug) }}" {{ isset($category) && $category->id === $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->published_news_count }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Popular Tags List -->
        @if(isset($popularTags) && $popularTags->count() > 0)
            <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-border">
                <span class="text-[10px] font-black uppercase text-muted-foreground mr-1.5">{{ __('Thẻ phổ biến:') }}</span>
                @foreach($popularTags as $t)
                    <a href="{{ route('news.tag', $t->slug) }}" class="px-2 py-0.5 bg-muted hover:bg-vttu-red/10 text-muted-foreground hover:text-vttu-red rounded-sm text-[10px] font-bold border border-border transition-colors">
                        #{{ $t->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <!-- News List -->
    @if(isset($news) && $news->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($news as $item)
                <article class="bg-card text-card-foreground border border-border rounded overflow-hidden group hover:bg-muted/30 transition-all duration-300 flex flex-col justify-between h-full">
                    <a href="{{ $item->url }}" class="block flex-1 flex flex-col">
                        <!-- Image -->
                        <div class="aspect-video w-full overflow-hidden relative bg-muted">
                            <img src="{{ $item->featured_image ? (str_starts_with($item->featured_image, 'http') ? $item->featured_image : asset($item->featured_image)) : 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=400&q=80' }}" 
                                 alt="{{ $item->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @if($item->category)
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-sm bg-background/90 text-vttu-red text-[10px] font-black uppercase tracking-wider border border-border shadow-sm">
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
                                <h3 class="text-xs font-black text-foreground group-hover:text-vttu-red transition-colors line-clamp-2 leading-snug">
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
            <div class="mt-4 pt-4 border-t border-border flex justify-center">
                {{ $news->links() }}
            </div>
        @endif
    @else
        <div class="bg-card border border-border rounded p-6 text-center shadow-xs">
            <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center mx-auto mb-4 text-muted-foreground">
                <i data-lucide="newspaper" class="w-6 h-6"></i>
            </div>
            <h3 class="text-sm font-black text-foreground mb-1">{{ __('Không tìm thấy bài viết nào') }}</h3>
            <p class="text-xs text-muted-foreground font-medium mb-4">{{ __('Vui lòng quay lại sau hoặc thử từ khóa tìm kiếm khác.') }}</p>
            <a href="{{ route('news.index') }}" class="inline-flex px-4 py-2 bg-[#A80D0D] hover:bg-[#8f0b0b] !text-white text-xs font-black rounded uppercase tracking-wider transition-colors shadow-sm">
                {{ __('Xem tất cả tin tức') }}
            </a>
        </div>
    @endif
</div>
