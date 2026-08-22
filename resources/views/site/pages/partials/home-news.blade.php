@php $newsList = $tabNews ?? $homeNews ?? collect(); @endphp
<div id="news-container" class="relative group/news-swiper overflow-hidden w-full animate-in fade-in duration-500">
    <div class="swiper news-swiper-container !pb-1">
        <div class="swiper-wrapper flex flex-nowrap !items-start">
            @if(($newsType ?? 'news') === 'video')
                @if(isset($tabNews) && count($tabNews) > 0)
                    @foreach($tabNews as $item)
                        <div class="swiper-slide !h-auto shrink-0">
                            <div class="bg-white p-3 rounded-md border border-slate-100 hover:border-vttu-red/20 transition-all group flex flex-col h-auto shadow-sm hover:shadow-md">
                                <a href="{{ $item->url }}" class="block aspect-[16/10] bg-slate-900 rounded-md mb-2.5 overflow-hidden relative border border-slate-100 group/img">
                                    @php
                                        $vImg = $item->featured_image ?? 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=400&q=80';
                                    @endphp
                                    <!-- Blurred Backdrop Image Fill -->
                                    <img src="{{ $vImg }}" alt="" class="absolute inset-0 w-full h-full object-cover blur-lg scale-125 opacity-50 pointer-events-none">
                                    <div class="absolute inset-0 bg-black/20"></div>
                                    <!-- Main Foreground Image -->
                                    <img src="{{ $vImg }}" alt="{{ $item->title }}" class="relative z-10 w-full h-full object-contain group-hover/img:scale-105 transition-transform duration-500">
                                    <!-- Play Icon Overlay -->
                                    <div class="absolute inset-0 z-20 flex items-center justify-center bg-black/20 group-hover/img:bg-black/10 transition-colors">
                                        <div class="w-9 h-9 bg-white/90 backdrop-blur rounded-full flex items-center justify-center text-vttu-red shadow-md group-hover/img:scale-110 transition-transform">
                                            <i class="fas fa-play text-xs ml-0.5"></i>
                                        </div>
                                    </div>
                                </a>
                                <div class="flex-grow flex flex-col justify-between gap-2">
                                    <a href="{{ $item->url }}" class="block">
                                        <h3 class="text-xs font-bold text-vttu-dark group-hover:text-vttu-red transition-colors line-clamp-2 leading-snug min-h-[2.25rem]">
                                            {{ $item->title }}
                                        </h3>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="swiper-slide w-full py-8 text-center shrink-0">
                        <p class="text-slate-400 font-bold italic text-sm">Chưa có video nào</p>
                    </div>
                @endif
            @else
                @if($newsList->count() > 0)
                    @php $newsOrdered = $newsList->sortBy('sort_order'); @endphp
                    @foreach($newsOrdered as $item)
                        <div class="swiper-slide !h-auto shrink-0">
                            <div class="bg-white p-3 rounded-md border border-slate-100 hover:border-vttu-red/20 transition-all group flex flex-col h-auto shadow-sm hover:shadow-md">
                                <a href="{{ $item->url }}" class="block aspect-[16/10] bg-slate-900 rounded-md mb-2.5 overflow-hidden relative border border-slate-100 group/img">
                                    @php
                                        $newsImg = $item->featured_image ?? 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=400&q=80';
                                    @endphp
                                    <!-- Blurred Backdrop Image Fill -->
                                    <img src="{{ $newsImg }}" alt="" class="absolute inset-0 w-full h-full object-cover blur-lg scale-125 opacity-50 pointer-events-none">
                                    <div class="absolute inset-0 bg-black/10"></div>
                                    <!-- Main Foreground Image -->
                                    <img src="{{ $newsImg }}" alt="{{ $item->title }}" class="relative z-10 w-full h-full object-contain group-hover/img:scale-105 transition-transform duration-500">
                                </a>
                                <div class="flex-grow flex flex-col justify-between gap-2">
                                    <a href="{{ $item->url }}" class="block">
                                        <h3 class="text-xs font-bold text-vttu-dark group-hover:text-vttu-red transition-colors line-clamp-2 leading-snug min-h-[2.25rem]">
                                            {{ $item->title }}
                                        </h3>
                                    </a>
                                    <p class="text-[10px] font-medium text-slate-400 flex items-center gap-1.5 pt-1.5 border-t border-slate-50">
                                        <i class="far fa-calendar-alt text-[9px] text-vttu-red"></i>
                                        {{ $item->published_at ? $item->published_at->format('d/m/Y') : $item->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="swiper-slide w-full py-8 text-center shrink-0">
                        <p class="text-slate-400 font-bold italic text-sm">Chưa có tin tức mới cập nhật</p>
                    </div>
                @endif
            @endif
        </div>

        <!-- Navigation Buttons -->
        <div class="swiper-button-next news-next !w-8 !h-8 !bg-white !rounded-full !shadow-lg !border !border-slate-100 !text-vttu-red after:!text-[10px] !right-2 opacity-0 group-hover/news-swiper:opacity-100 transition-opacity z-30"></div>
        <div class="swiper-button-prev news-prev !w-8 !h-8 !bg-white !rounded-full !shadow-lg !border !border-slate-100 !text-vttu-red after:!text-[10px] !left-2 opacity-0 group-hover/news-swiper:opacity-100 transition-opacity z-30"></div>
    </div>
</div>
