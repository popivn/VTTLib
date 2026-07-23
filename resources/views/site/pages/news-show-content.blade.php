<div class="not-prose w-full">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
        <!-- Left: Article Content -->
        <div class="lg:col-span-9 space-y-3">
            <!-- Main Article Card -->
            <article class="bg-card text-card-foreground border border-border rounded-none overflow-hidden shadow-xs">
                <!-- Featured Image -->
                @if($news->featured_image)
                    <div class="aspect-video w-full overflow-hidden bg-muted">
                        <img src="{{ str_starts_with($news->featured_image, 'http') ? $news->featured_image : asset($news->featured_image) }}" alt="{{ $news->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="p-3 space-y-3">
                    <!-- Meta information -->
                    <div class="flex flex-wrap items-center gap-3">
                        @if($news->category)
                            <span class="px-2 py-0.5 rounded-none bg-rose-50 dark:bg-rose-950/20 text-vttu-red text-[10px] font-black uppercase tracking-wider border border-rose-100 dark:border-rose-900/10">
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
                    <h1 class="text-lg md:text-xl font-bold text-foreground !leading-normal my-2">
                        {{ $news->title }}
                    </h1>

                    <!-- Summary -->
                    @if($news->summary)
                        <div class="p-3 bg-muted/50 rounded-none border-l-4 border-vttu-red">
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
                                <a href="{{ $news->category ? route('news.category.tag', ['category_slug' => $news->category->slug, 'tag_slug' => $tag->slug]) : route('news.tag', $tag->slug) }}" class="px-2 py-0.5 bg-muted hover:bg-vttu-red/10 text-muted-foreground hover:text-vttu-red rounded-none text-[10px] font-bold border border-border transition-colors">
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
                    <a href="{{ $previousNews->url }}" class="p-3 bg-card border border-border rounded-none shadow-xs hover:bg-muted/30 transition-all group flex flex-col justify-center">
                        <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest block mb-1">{{ __('Bài trước') }}</span>
                        <h4 class="text-xs font-bold text-foreground group-hover:text-vttu-red transition-colors line-clamp-1">{{ $previousNews->title }}</h4>
                    </a>
                @endif
                @if(isset($nextNews) && $nextNews)
                    <a href="{{ $nextNews->url }}" class="p-3 bg-card border border-border rounded-none shadow-xs hover:bg-muted/30 transition-all group flex flex-col justify-center sm:text-right">
                        <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest block mb-1">{{ __('Bài tiếp theo') }}</span>
                        <h4 class="text-xs font-bold text-foreground group-hover:text-vttu-red transition-colors line-clamp-1">{{ $nextNews->title }}</h4>
                    </a>
                @endif
            </div>
        </div>

        <!-- Right Sidebar: Related News & Info -->
        <aside class="lg:col-span-3 space-y-3 sticky top-20 self-start">
            @php
                $sidebarQuery = \App\Models\News::where('status', 'published')
                    ->where('id', '!=', $news->id)
                    ->orderBy('published_at', 'desc');

                if ($news->category) {
                    $sidebarQuery->where('category_id', $news->category->id);
                    $sidebarHeader = str_contains(strtolower($news->category->name), 'video') ? 'VIDEO MỚI NHẤT' : 'TIN LIÊN QUAN';
                } else {
                    $sidebarHeader = 'TIN MỚI NHẤT';
                }

                $sidebarNews = $sidebarQuery->limit(10)->get();
            @endphp

            @if($sidebarNews->count() > 0)
                <div class="bg-card border border-border rounded-none p-3 shadow-xs space-y-3">
                    <h3 class="text-xs font-black text-foreground uppercase tracking-wider flex items-center">
                        <span class="w-1 h-3.5 bg-vttu-red rounded-none mr-2"></span>
                        {{ $sidebarHeader }}
                    </h3>
                    <div class="space-y-3">
                        @foreach($sidebarNews as $item)
                            <a href="{{ $item->url }}" class="flex gap-2.5 group text-foreground hover:text-vttu-red transition-colors items-center">
                                <div class="w-20 h-14 rounded-sm overflow-hidden flex-shrink-0">
                                    <img src="{{ $item->featured_image ? (str_starts_with($item->featured_image, 'http') ? $item->featured_image : asset($item->featured_image)) : 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=160&q=80' }}" 
                                         alt="{{ $item->title }}"
                                         class="w-full h-full object-cover rounded-sm group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-[#A80D0D] hover:text-[#8f0b0b] transition-colors line-clamp-2 leading-snug">{{ $item->title }}</h4>
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
                <div class="absolute top-0 right-0 w-16 h-16 bg-vttu-red/5 blur-xl rounded-none -mr-8 -mt-8"></div>
                <h3 class="text-xs font-black text-foreground uppercase tracking-wider relative z-10 flex items-center">
                    <span class="w-1 h-3.5 bg-vttu-red rounded-none mr-2"></span>
                    {{ __('HỖ TRỢ') }}
                </h3>
                <p class="text-xs text-muted-foreground leading-relaxed">
                    {{ __('Liên hệ với chúng tôi để được giải đáp mọi thắc mắc về tài liệu và dịch vụ thư viện.') }}
                </p>
                <div class="pt-1">
                    <a href="/noi-quy-thu-vien" class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 bg-[#A80D0D] hover:bg-[#8f0b0b] !text-white text-xs font-black rounded-none uppercase tracking-wider transition-colors shadow-sm">
                        <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                        <span>{{ __('Liên hệ ngay') }}</span>
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>
