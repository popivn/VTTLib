@forelse($newBooks as $book)
@php
    $title = $book->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
    $author = $book->fields->where('tag', '100')->first()?->subfields->where('code', 'a')->first()?->value 
            ?? $book->fields->where('tag', '700')->first()?->subfields->where('code', 'a')->first()?->value 
            ?? 'Đang cập nhật tác giả';
@endphp
<div class="swiper-slide h-auto shrink-0">
    <div class="bg-white p-3 rounded-md border border-slate-100 hover:border-vttu-red/20 transition-all group flex flex-col h-full shadow-sm hover:shadow-md">
        <!-- Book Cover -->
        <a href="{{ route('opac.book.show', $book->id) }}" class="block aspect-[3/4] bg-slate-900 rounded-md mb-3 border border-slate-100 overflow-hidden relative group/img">
            @php
                $imgUrl = $book->cover_image ? asset('storage/' . $book->cover_image) : asset('assets/imgs/books/noimage.png');
            @endphp
            <!-- Blurred Backdrop Image Fill -->
            <img src="{{ $imgUrl }}" alt="" class="absolute inset-0 w-full h-full object-cover blur-lg scale-125 opacity-50 pointer-events-none">
            <div class="absolute inset-0 bg-black/10"></div>
            <!-- Main Foreground Image -->
            <img src="{{ $imgUrl }}" alt="{{ $title }}" class="relative z-10 w-full h-full object-contain group-hover/img:scale-105 transition-transform duration-500">
            <div class="absolute top-2 z-10 right-2">
                @php
                    $lang = app()->getLocale();
                    $typeDisplay = ($lang === 'vi') ? ($book->record_type_vi ?? 'Sách') : ($book->record_type_en ?? 'Book');
                @endphp
                <span class="px-2 py-0.5 bg-white/90 backdrop-blur text-vttu-dark rounded-sm text-[8px] font-black uppercase tracking-widest shadow-sm">{{ $typeDisplay }}</span>
            </div>
        </a>

        <!-- Book Info -->
        <div class="flex-grow flex flex-col gap-2">
            <a href="{{ route('opac.book.show', $book->id) }}" class="block">
                <h3 class="text-xs font-bold text-vttu-dark group-hover:text-vttu-red transition-colors leading-tight line-clamp-2 min-h-[2.5rem]">
                    {{ $title }}
                </h3>
            </a>
            <p class="text-[10px] font-medium text-slate-500 flex items-center gap-1.5 truncate min-h-[1.25rem]">
                <i class="fas fa-user-edit text-[8px] text-vttu-red"></i>
                {{ $author }}
            </p>
            
            <div class="mt-auto pt-2 flex items-center justify-between border-t border-slate-50">
                @if($book->items->where('status', 'available')->count() > 0)
                    <span class="text-[8px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-sm uppercase tracking-tighter border border-emerald-100">Sẵn sàng</span>
                @else
                    <span class="text-[8px] font-bold text-rose-500 bg-rose-50 px-2 py-0.5 rounded-sm uppercase tracking-tighter border border-rose-100">Hết sách</span>
                @endif
                <a href="{{ route('opac.book.show', $book->id) }}" class="w-6 h-6 rounded-sm bg-slate-50 flex items-center justify-center text-vttu-red hover:bg-vttu-red hover:text-white transition-all shadow-sm">
                    <i class="fas fa-arrow-right text-[8px]"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@empty
<div class="swiper-slide w-full py-12 text-center shrink-0">
    <p class="text-slate-400 font-bold italic text-sm">Không có tài liệu nào trong chuyên mục này.</p>
</div>
@endforelse
