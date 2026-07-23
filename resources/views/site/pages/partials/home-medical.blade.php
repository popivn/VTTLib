<div class="relative group/medical-swiper overflow-hidden w-full animate-in fade-in duration-500">
    <div class="swiper medical-swiper-container !pb-1">
        <div class="swiper-wrapper flex flex-nowrap !items-start">
            @forelse($newBooks as $book)
                @php
                    $title = $book->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
                    $author = $book->fields->where('tag', '100')->first()?->subfields->where('code', 'a')->first()?->value 
                            ?? $book->fields->where('tag', '700')->first()?->subfields->where('code', 'a')->first()?->value 
                            ?? 'Đang cập nhật tác giả';
                @endphp
                <div class="swiper-slide !h-auto shrink-0">
                    <div class="bg-white p-2.5 rounded-md border border-slate-100 hover:border-vttu-red/20 transition-all group flex flex-col shadow-sm h-auto">
                        <!-- Book Cover -->
                        <a href="{{ route('opac.book.show', $book->id) }}" class="block aspect-[3/4] bg-slate-900 rounded-sm mb-2 border border-slate-50 overflow-hidden relative group/img">
                            @php
                                $medImgUrl = $book->cover_image ? asset('storage/' . $book->cover_image) : asset('assets/imgs/books/noimage.png');
                            @endphp
                            <!-- Blurred Backdrop Image Fill -->
                            <img src="{{ $medImgUrl }}" class="absolute inset-0 w-full h-full object-cover blur-lg scale-125 opacity-50 pointer-events-none">
                            <div class="absolute inset-0 bg-black/10"></div>
                            <!-- Main Foreground Image -->
                            <img src="{{ $medImgUrl }}" class="relative z-10 w-full h-full object-contain group-hover/img:scale-105 transition-transform duration-500">
                            <div class="absolute top-1.5 right-1.5 z-10">
                                @php
                                    $lang = app()->getLocale();
                                    $typeDisplay = ($lang === 'vi') ? ($book->record_type_vi ?? 'Sách') : ($book->record_type_en ?? 'Book');
                                @endphp
                                <span class="px-1.5 py-0.5 bg-white/90 backdrop-blur text-vttu-red rounded-sm text-[7px] font-bold uppercase tracking-widest shadow-sm">{{ $typeDisplay }}</span>
                            </div>
                        </a>

                        <!-- Book Info -->
                        <div class="flex-grow flex flex-col gap-1.5">
                            <a href="{{ route('opac.book.show', $book->id) }}" class="block">
                                <h3 class="text-[10px] font-bold text-vttu-dark group-hover:text-vttu-red transition-colors leading-tight line-clamp-2 min-h-[1.5rem]">
                                    {{ $title }}
                                </h3>
                            </a>
                            <p class="text-[9px] font-medium text-slate-500 flex items-center gap-1 truncate">
                                <i class="fas fa-user-edit text-[7px] text-vttu-red"></i>
                                {{ $author }}
                            </p>
                            
                            <div class="mt-auto pt-1.5 flex items-center justify-between border-t border-slate-50">
                                @if($book->items->where('status', 'available')->count() > 0)
                                    <span class="text-[7px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-sm uppercase tracking-tighter border border-emerald-100">Sẵn sàng</span>
                                @else
                                    <span class="text-[7px] font-bold text-rose-500 bg-rose-50 px-1.5 py-0.5 rounded-sm uppercase tracking-tighter border border-rose-100">Hết sách</span>
                                @endif
                                <a href="{{ route('opac.book.show', $book->id) }}" class="w-5 h-5 rounded-sm bg-slate-50 flex items-center justify-center text-vttu-red hover:bg-vttu-red hover:text-white transition-all shadow-sm">
                                    <i class="fas fa-arrow-right text-[7px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-4 py-12 text-center text-slate-400 font-medium w-full">
                    {{ __('Hiện chưa có sách nào trong chuyên đề này.') }}
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Navigation Buttons -->
    <div class="swiper-button-next medical-next !w-8 !h-8 !bg-white !rounded-full !shadow-lg !border !border-slate-100 !text-vttu-red after:!text-[10px] !right-2 opacity-0 group-hover/medical-swiper:opacity-100 transition-opacity z-30"></div>
    <div class="swiper-button-prev medical-prev !w-8 !h-8 !bg-white !rounded-full !shadow-lg !border !border-slate-100 !text-vttu-red after:!text-[10px] !left-2 opacity-0 group-hover/medical-swiper:opacity-100 transition-opacity z-30"></div>
</div>
