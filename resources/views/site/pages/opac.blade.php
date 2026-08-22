@extends('layouts.site')

@section('title', 'Tra cứu OPAC | Thư viện Đại học Võ Trường Toản')

@section('meta-description')
    <meta name="description" content="Tra cứu mục lục trực tuyến OPAC Thư viện Võ Trường Toản. Tìm kiếm sách, giáo trình, luận văn, tài liệu chuyên ngành Y Dược nhanh chóng.">
@endsection

@section('content')
<div class="bg-slate-50 min-h-screen pt-24 pb-12" x-data="{ loading: false }" x-init="$watch('loading', v => { if (v) document.body.style.cursor = 'wait' })">
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
            <div class="opac-loader-logo">
                <img src="{{ asset('assets/imgs/logo-vttu.png') }}" alt="VTTU" class="w-16 h-16 object-contain">
            </div>
            <div class="text-[11px] font-bold text-vttu-dark tracking-[0.1em] uppercase">Đang tra cứu...</div>
        </div>
    </div>
    <div class="px-4 md:px-12 lg:px-24">
        
        <!-- Header OPAC -->
        <div class="bg-white rounded-md p-6 shadow-sm border border-slate-100 mb-6" data-aos="fade-down">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-xl font-bold text-vttu-dark tracking-tight uppercase">TRA CỨU OPAC</h1>
                    <p class="text-slate-500 text-xs font-medium mt-1">Hệ thống tra cứu mục lục công cộng trực tuyến</p>
                </div>
                <div class="flex items-center gap-3 bg-emerald-50 px-4 py-2 rounded-sm border border-emerald-100">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <div>
                        <p class="text-[8px] font-bold text-emerald-600 uppercase tracking-widest leading-none">Trạng thái hệ thống</p>
                        <p class="text-xs font-bold text-vttu-dark mt-1">Kết nối thành công ({{ number_format($totalRecords ?? 0) }} biểu ghi)</p>
                    </div>
                </div>
            </div>

            <!-- Search Bar OPAC -->
            <div class="mt-6">
                <form action="{{ route('site.opac') }}" method="GET" class="relative group" @submit="loading = true">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 group-focus-within:text-vttu-red transition-colors text-sm"></i>
                    </div>
                    <input type="text" name="q" value="{{ request('q') }}" 
                        placeholder="Nhập tên sách, tác giả, từ khóa..." 
                        class="w-full bg-slate-50 border border-slate-100 focus:border-vttu-red/20 focus:bg-white pl-12 pr-44 py-3 rounded-sm text-sm font-medium text-vttu-dark transition-all outline-none shadow-sm">
                    <div class="absolute inset-y-1 right-1 flex gap-1.5">
                        <select name="type" class="bg-white border border-slate-200 rounded-sm px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500 outline-none focus:border-vttu-red transition-all">
                            <option value="all">Bất kỳ</option>
                            <option value="title" {{ request('type') == 'title' ? 'selected' : '' }}>Nhan đề</option>
                            <option value="author" {{ request('type') == 'author' ? 'selected' : '' }}>Tác giả</option>
                            <option value="subject" {{ request('type') == 'subject' ? 'selected' : '' }}>Chủ đề</option>
                            <option value="accession" {{ request('type') == 'accession' ? 'selected' : '' }}>Số đăng ký cá biệt</option>
                        </select>
                        <button type="submit" class="bg-vttu-red text-white px-6 rounded-sm font-bold uppercase text-[10px] tracking-widest hover:bg-vttu-dark transition-all shadow-sm">
                            Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8" x-data="{ viewMode: 'grid' }">
            
            <!-- CỘT TRÁI (75%) - Kết quả tra cứu -->
            <div class="lg:col-span-9 space-y-4" data-aos="fade-right">
                {{-- Toolbar OPAC --}}
                <div class="flex items-center justify-between bg-white p-3 rounded-md shadow-sm border border-slate-100">
                    <div class="flex flex-wrap items-center gap-4 md:gap-6 ml-1">
                        <!-- Hiển thị: Grid / List -->
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Hiển thị:</span>
                            <div class="flex bg-slate-50 p-0.5 rounded-sm border border-slate-100">
                                <button @click="viewMode = 'grid'" 
                                        :class="viewMode === 'grid' ? 'bg-white text-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red'"
                                        class="w-8 h-8 rounded-sm flex items-center justify-center transition-all">
                                    <i class="fas fa-th-large text-[10px]"></i>
                                </button>
                                <button @click="viewMode = 'list'" 
                                        :class="viewMode === 'list' ? 'bg-white text-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red'"
                                        class="w-8 h-8 rounded-sm flex items-center justify-center transition-all">
                                    <i class="fas fa-list text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        @php
                            $currentSort = request('sort', 'accession_desc');
                        @endphp

                        <!-- Sắp xếp: Cũ -> Mới (mặc định), Mới -> Cũ -->
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Cập nhật:</span>
                            <div class="flex bg-slate-50 p-0.5 rounded-sm border border-slate-100">
                                <!-- Cũ đến mới (Mặc định) -->
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}" 
                                   @click="loading = true"
                                   class="w-8 h-8 rounded-sm flex items-center justify-center transition-all {{ $currentSort === 'oldest' ? 'bg-white text-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}"
                                   title="Cập nhật: Cũ đến Mới">
                                    <i class="fas fa-sort-amount-up text-[10px]"></i>
                                </a>
                                <!-- Mới đến cũ -->
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" 
                                   @click="loading = true"
                                   class="w-8 h-8 rounded-sm flex items-center justify-center transition-all {{ $currentSort === 'newest' ? 'bg-white text-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}"
                                   title="Cập nhật: Mới đến Cũ">
                                    <i class="fas fa-sort-amount-down text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Số đăng ký: Tăng dần, Giảm dần -->
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Số Đăng Ký:</span>
                            <div class="flex bg-slate-50 p-0.5 rounded-sm border border-slate-100">
                                <!-- Tăng dần -->
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'accession_asc']) }}" 
                                   @click="loading = true"
                                   class="w-8 h-8 rounded-sm flex items-center justify-center transition-all {{ $currentSort === 'accession_asc' ? 'bg-white text-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}"
                                   title="Số đăng ký: Tăng dần">
                                    <i class="fas fa-sort-numeric-down text-[10px]"></i>
                                </a>
                                <!-- Giảm dần -->
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'accession_desc']) }}" 
                                   @click="loading = true"
                                   class="w-8 h-8 rounded-sm flex items-center justify-center transition-all {{ $currentSort === 'accession_desc' ? 'bg-white text-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}"
                                   title="Số đăng ký: Giảm dần">
                                    <i class="fas fa-sort-numeric-up text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Nhan đề: A-Z, Z-A -->
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Tên sách:</span>
                            <div class="flex bg-slate-50 p-0.5 rounded-sm border border-slate-100">
                                <!-- A - Z -->
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'title_az']) }}" 
                                   @click="loading = true"
                                   class="w-8 h-8 rounded-sm flex items-center justify-center transition-all {{ $currentSort === 'title_az' ? 'bg-white text-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}"
                                   title="Tên sách: A đến Z">
                                    <i class="fas fa-sort-alpha-down text-[10px]"></i>
                                </a>
                                <!-- Z - A -->
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'title_za']) }}" 
                                   @click="loading = true"
                                   class="w-8 h-8 rounded-sm flex items-center justify-center transition-all {{ $currentSort === 'title_za' ? 'bg-white text-vttu-red shadow-sm' : 'text-slate-400 hover:text-vttu-red' }}"
                                   title="Tên sách: Z đến A">
                                    <i class="fas fa-sort-alpha-up text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mr-1">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                            Kết quả: <span class="text-vttu-red">{{ number_format($books->total()) }}</span> tài liệu
                        </span>
                    </div>
                </div>

                <div x-show="viewMode === 'grid'" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                    @forelse($books as $book)
                    @php
                        // Helper để lấy nội dung từ MARC fields
                        $getTitle = function($book) {
                            $f245 = $book->fields->where('tag', '245')->first();
                            return $f245 ? $f245->subfields->where('code', 'a')->first()?->value : 'Không có nhan đề';
                        };
                        $getAuthor = function($book) {
                            $f100 = $book->fields->where('tag', '100')->first();
                            if ($f100) return $f100->subfields->where('code', 'a')->first()?->value;
                            $f700 = $book->fields->where('tag', '700')->first();
                            return $f700 ? $f700->subfields->where('code', 'a')->first()?->value : 'Đang cập nhật tác giả';
                        };
                        $title = $getTitle($book);
                    @endphp
                    <div class="bg-white p-3 rounded-md border border-slate-100 hover:border-vttu-red/20 transition-all group flex flex-col shadow-sm hover:shadow-md">
                        <!-- Book Cover -->
                        <a href="{{ route('opac.book.show', $book->id) }}" class="block aspect-[3/4] bg-slate-100 rounded-md mb-3 border border-slate-100 group-hover:bg-vttu-red/5 transition-colors overflow-hidden relative">
                            @if($book->cover_image && \Storage::disk('public')->exists($book->cover_image))
                                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $title }}" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-500">
                            @else
                                <img src="{{ asset('assets/imgs/books/noimage.png') }}" alt="{{ $title }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                            @endif
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-0.5 bg-white/90 backdrop-blur text-vttu-dark rounded-sm text-[8px] font-bold uppercase tracking-widest shadow-sm">{{ $book->record_type ?? 'Sách' }}</span>
                            </div>
                        </a>

                        <!-- Book Info -->
                        <div class="flex-grow flex flex-col gap-1.5">
                            <a href="{{ route('opac.book.show', $book->id) }}">
                                <h3 class="text-xs font-bold text-vttu-dark group-hover:text-vttu-red transition-colors leading-tight line-clamp-2 min-h-[2rem]">
                                    {{ $title }}
                                </h3>
                            </a>
                            <p class="text-[10px] font-medium text-slate-500 flex items-center gap-1 truncate">
                                <i class="fas fa-user-edit text-[8px] text-vttu-red"></i>
                                {{ $getAuthor($book) }}
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
                    @empty
                        <div class="col-span-full bg-white p-12 rounded-md text-center border border-slate-100 shadow-sm">
                            <i class="fas fa-search text-slate-200 text-4xl mb-3"></i>
                            <p class="text-slate-500 font-bold text-sm">Không tìm thấy tài liệu nào trong hệ thống.</p>
                        </div>
                    @endforelse
                </div>

                {{-- List View --}}
                <div x-show="viewMode === 'list'" class="space-y-3">
                    @foreach($books as $book)
                    @php
                        $title = $getTitle($book);
                        $author = $getAuthor($book);
                        $summary = $book->fields->where('tag', '520')->first()?->subfields->where('code', 'a')->first()?->value;
                        $publisher = $book->fields->where('tag', '260')->first()?->subfields->where('code', 'b')->first()?->value;
                        $year = $book->fields->where('tag', '260')->first()?->subfields->where('code', 'c')->first()?->value;
                    @endphp
                    <div class="bg-white p-4 rounded-md border border-slate-100 hover:border-vttu-red/20 transition-all group shadow-sm hover:shadow-md">
                        <div class="flex gap-4">
                            <!-- Cover Small -->
                            <div class="w-20 aspect-[3/4] bg-slate-100 rounded-sm overflow-hidden flex-shrink-0 border border-slate-100">
                                @if($book->cover_image && \Storage::disk('public')->exists($book->cover_image))
                                    <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $title }}" class="w-full h-full object-contain mix-blend-multiply">
                                @else
                                    <img src="{{ asset('assets/imgs/books/noimage.png') }}" alt="{{ $title }}" class="w-full h-full object-contain">
                                @endif
                            </div>
                            <!-- Content -->
                            <div class="flex-1 flex flex-col">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('opac.book.show', $book->id) }}">
                                            <h3 class="text-sm font-bold text-vttu-dark group-hover:text-vttu-red transition-colors leading-tight mb-1.5 truncate">
                                                {{ $title }}
                                            </h3>
                                        </a>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mb-2">
                                            <div class="flex items-center text-[10px] font-bold text-slate-500">
                                                <i class="fas fa-user-edit text-vttu-red mr-1.5 text-[8px]"></i>
                                                {{ $author }}
                                            </div>
                                            @if($publisher)
                                            <div class="flex items-center text-[10px] font-medium text-slate-400">
                                                <i class="fas fa-print mr-1.5 opacity-50 text-[8px]"></i>
                                                {{ $publisher }} {{ $year ? "($year)" : '' }}
                                            </div>
                                            @endif
                                            <div class="flex items-center">
                                                @if($book->items->where('status', 'available')->count() > 0)
                                                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-600 rounded-sm text-[8px] font-bold uppercase tracking-widest border border-emerald-100">Sẵn sàng</span>
                                                @else
                                                    <span class="px-1.5 py-0.5 bg-rose-50 text-rose-500 rounded-sm text-[8px] font-bold uppercase tracking-widest border border-rose-100">Hết sách</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($summary)
                                            <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed mb-0 italic">"{{ Str::limit($summary, 200) }}"</p>
                                        @endif
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('opac.book.show', $book->id) }}" class="inline-flex items-center px-4 py-2 bg-vttu-red hover:bg-vttu-dark text-white text-[9px] font-bold uppercase tracking-widest rounded-sm transition-all shadow-sm">
                                            Chi tiết
                                            <i class="fas fa-arrow-right ml-1.5 text-[8px]"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination OPAC -->
                <div class="flex justify-center pt-8" id="opac-pagination">
                    {{ $books->links() }}
                </div>
            </div>

            <!-- CỘT PHẢI (25%) - Filters & Sidebar -->
            <div class="lg:col-span-3 space-y-4" data-aos="fade-left">
                
                <!-- Sách theo kho -->
                <div class="bg-white rounded-md p-4 shadow-sm border border-slate-100">
                    <h3 class="text-xs font-bold text-vttu-dark uppercase tracking-widest border-b border-slate-50 pb-3 mb-3">SÁCH THEO KHO</h3>
                    <div class="space-y-2">
                        @forelse($sidebar['locations'] as $location)
                        <a href="{{ route('site.opac', ['location' => $location->id]) }}" @click="loading = true" class="flex justify-between items-center group">
                            <span class="text-xs font-medium text-slate-600 group-hover:text-vttu-red transition-colors truncate pr-2">{{ $location->name }}</span>
                            <span class="bg-slate-50 text-slate-400 px-1.5 py-0.5 rounded-sm text-[9px] font-bold group-hover:bg-vttu-red/10 group-hover:text-vttu-red transition-all">{{ $location->book_items_count }}</span>
                        </a>
                        @empty
                        <p class="text-[10px] text-slate-400 italic">Đang cập nhật...</p>
                        @endforelse
                    </div>
                </div>

                <!-- Sách theo phân loại (DDC) -->
                <div class="bg-vttu-dark rounded-md p-4 shadow-sm text-white">
                    <h3 class="text-xs font-bold text-vttu-yellow uppercase tracking-widest border-b border-white/10 pb-3 mb-3">PHÂN LOẠI DDC</h3>
                    <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1.5 custom-scrollbar">
                        @forelse($sidebar['ddc'] as $ddc)
                        <a href="{{ route('site.opac', ['ddc' => $ddc['code']]) }}" @click="loading = true" class="flex justify-between items-start group border-b border-white/5 pb-1.5 last:border-0">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-vttu-yellow tracking-widest">{{ $ddc['code'] }}</span>
                                <span class="text-xs font-medium text-white/70 group-hover:text-white transition-colors leading-tight">{{ $ddc['name'] }}</span>
                            </div>
                            <span class="text-[9px] font-bold bg-white/10 px-1.5 py-0.5 rounded-sm group-hover:bg-vttu-yellow group-hover:text-vttu-dark transition-all">{{ $ddc['count'] }}</span>
                        </a>
                        @empty
                        <p class="text-[10px] text-white/40 italic text-center">Đang cập nhật...</p>
                        @endforelse
                    </div>
                </div>

                <!-- Mượn nhiều -->
                <div class="bg-white rounded-md p-4 shadow-sm border border-slate-100">
                    <h3 class="text-xs font-bold text-vttu-dark uppercase tracking-widest border-b border-slate-50 pb-3 mb-3">SÁCH PHỔ BIẾN</h3>
                    <div class="space-y-4">
                        @forelse($sidebar['mostBorrowed'] as $mb)
                        @php
                            $mbTitle = $mb->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
                            $mbPub = $mb->fields->where('tag', '260')->first()?->subfields->where('code', 'b')->first()?->value ?? 'Đang cập nhật';
                        @endphp
                        <a href="{{ route('opac.book.show', $mb->id) }}" @click="loading = true" class="group block">
                            <h4 class="text-[11px] font-bold text-vttu-dark group-hover:text-vttu-red transition-colors line-clamp-2 leading-tight">{{ $mbTitle }}</h4>
                            <p class="text-[9px] font-medium text-slate-400 mt-1 uppercase tracking-widest">{{ $mbPub }}</p>
                        </a>
                        @empty
                        <p class="text-[10px] text-slate-400 italic">Đang cập nhật...</p>
                        @endforelse
                    </div>
                </div>

                <!-- Từ khóa hot -->
                <div class="bg-white rounded-md p-4 shadow-sm border border-slate-100">
                    <h3 class="text-xs font-bold text-vttu-dark uppercase tracking-widest border-b border-slate-50 pb-3 mb-3">TỪ KHÓA HOT</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($sidebar['hotKeywords'] as $tag)
                        <a href="{{ route('site.opac', ['q' => $tag]) }}" @click="loading = true" class="px-2.5 py-1 bg-slate-50 hover:bg-vttu-red hover:text-white text-slate-500 text-[9px] font-bold uppercase tracking-widest rounded-sm transition-all border border-slate-100">
                            {{ $tag }}
                        </a>
                        @empty
                        <p class="text-[10px] text-slate-400 italic">Đang cập nhật...</p>
                        @endforelse
                    </div>
                </div>

            </div>

            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #FFD700; }

    .opac-loader-logo { perspective: 200px; }
    .opac-loader-logo img {
        animation: opac-logo-3d 2s ease-in-out infinite;
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.15));
    }
    @keyframes opac-logo-3d {
        0% { transform: rotateY(0deg); }
        50% { transform: rotateY(180deg); }
        100% { transform: rotateY(360deg); }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var pag = document.getElementById('opac-pagination');
        if (pag) {
            pag.addEventListener('click', function(e) {
                if (e.target.closest('a')) {
                    var overlay = document.querySelector('[x-data="{ loading: false }"]');
                    if (overlay && overlay.__x) {
                        overlay.__x.$data.loading = true;
                    }
                }
            });
        }
    });
</script>
@endsection
