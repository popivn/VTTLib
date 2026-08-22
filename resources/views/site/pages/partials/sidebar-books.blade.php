<div class="space-y-2">
    @forelse($sidebarBooks ?? [] as $book)
    @php
        $title = $book->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
        $author = $book->fields->where('tag', '100')->first()?->subfields->where('code', 'a')->first()?->value 
                ?? $book->fields->where('tag', '700')->first()?->subfields->where('code', 'a')->first()?->value 
                ?? 'Đang cập nhật tác giả';
    @endphp
    <a href="{{ route('opac.book.show', $book->id) }}" class="flex gap-3 group p-1 rounded-sm hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100">
        <div class="w-12 h-16 bg-slate-100 rounded-sm flex-shrink-0 overflow-hidden border border-slate-100 shadow-sm relative">
            @if($book->cover_image)
                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $title }}" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-500">
            @else
                <img src="{{ asset('assets/imgs/books/noimage.png') }}" alt="{{ $title }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
            @endif
        </div>
        <div class="flex-1 min-w-0 py-0.5">
            <h4 class="text-[11px] font-bold text-vttu-dark group-hover:text-vttu-red transition-colors line-clamp-2 leading-tight mb-1">{{ $title }}</h4>
            <p class="text-[9px] font-medium text-slate-400 uppercase tracking-widest truncate">{{ $author }}</p>
        </div>
    </a>
    @empty
        <p class="text-[10px] text-slate-400 italic text-center py-4">Chưa có sách mới cập nhật</p>
    @endforelse
</div>
