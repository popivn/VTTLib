<div class="not-prose w-full space-y-4">
    <!-- Main Article Card -->
    <article class="bg-card text-card-foreground border border-border rounded overflow-hidden shadow-xs">
        <!-- Featured Image -->
        @if($news->featured_image)
            <div class="aspect-video w-full overflow-hidden bg-muted">
                <img src="{{ $news->featured_image }}" alt="{{ $news->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-3 space-y-3">
            <!-- Meta information -->
            <div class="flex flex-wrap items-center gap-3">
                @if($news->category)
                    <span class="px-2 py-0.5 rounded-sm bg-rose-50 dark:bg-rose-950/20 text-vttu-red text-[10px] font-black uppercase tracking-wider border border-rose-100 dark:border-rose-900/10">
                        {{ $news->category->name }}
                    </span>
                @endif
                <div class="flex items-center text-muted-foreground text-xs font-bold">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 mr-1.5 flex-shrink-0"></i>
                    <span>{{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center text-muted-foreground text-xs font-bold">
                    <i data-lucide="eye" class="w-3.5 h-3.5 mr-1.5 flex-shrink-0"></i>
                    <span>{{ number_format($news->view_count) }} {{ __('lượt xem') }}</span>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-sm md:text-base font-black text-foreground leading-snug">
                {{ $news->title }}
            </h1>

            <!-- Summary -->
            @if($news->summary)
                <div class="p-3 bg-muted/50 rounded border-l-4 border-vttu-red">
                    <p class="text-xs text-muted-foreground leading-relaxed italic font-medium">
                        {{ $news->summary }}
                    </p>
                </div>
            @endif

            <!-- Content HTML -->
            <div class="text-xs md:text-sm text-foreground leading-relaxed font-medium space-y-3 pt-2">
                {!! nl2br($news->content) !!}
            </div>

            <!-- Tags -->
            @if($news->tags->count() > 0)
                <div class="pt-3 border-t border-border flex flex-wrap gap-1.5">
                    @foreach($news->tags as $tag)
                        <a href="{{ route('news.tag', $tag->slug) }}" class="px-2 py-0.5 bg-muted hover:bg-vttu-red/10 text-muted-foreground hover:text-vttu-red rounded-sm text-[10px] font-bold border border-border transition-colors">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </article>

    <!-- Navigation (Previous / Next) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @if(isset($previousNews) && $previousNews)
            <a href="{{ $previousNews->url }}" class="p-3 bg-card border border-border rounded shadow-xs hover:bg-muted/30 transition-all group flex flex-col justify-center">
                <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest block mb-1">{{ __('Bài trước') }}</span>
                <h4 class="text-xs font-bold text-foreground group-hover:text-vttu-red transition-colors line-clamp-1">{{ $previousNews->title }}</h4>
            </a>
        @endif
        @if(isset($nextNews) && $nextNews)
            <a href="{{ $nextNews->url }}" class="p-3 bg-card border border-border rounded shadow-xs hover:bg-muted/30 transition-all group flex flex-col justify-center sm:text-right">
                <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest block mb-1">{{ __('Bài tiếp theo') }}</span>
                <h4 class="text-xs font-bold text-foreground group-hover:text-vttu-red transition-colors line-clamp-1">{{ $nextNews->title }}</h4>
            </a>
        @endif
    </div>

    <!-- Related News -->
    @if(isset($relatedNews) && $relatedNews->count() > 0)
        <div class="bg-card border border-border rounded p-3 shadow-xs space-y-3">
            <h3 class="text-[10px] font-black text-foreground uppercase tracking-wider flex items-center">
                <span class="w-1 h-3.5 bg-vttu-red rounded-full mr-2"></span>
                {{ __('TIN LIÊN QUAN') }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($relatedNews as $item)
                    <a href="{{ $item->url }}" class="flex gap-2.5 group">
                        <div class="w-12 h-12 bg-muted rounded overflow-hidden flex-shrink-0">
                            <img src="{{ $item->featured_image ?? 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=120&q=80' }}" 
                                 alt="{{ $item->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-foreground group-hover:text-vttu-red transition-colors line-clamp-2 leading-snug">{{ $item->title }}</h4>
                            <span class="text-[9px] text-muted-foreground font-bold mt-1 block uppercase tracking-wider">
                                {{ $item->published_at ? $item->published_at->format('d/m/Y') : $item->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
