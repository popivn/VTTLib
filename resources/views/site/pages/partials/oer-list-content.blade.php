@php
    $oerResources = $oerResources ?? collect();
    $totalOerCount = $totalOerCount ?? 0;
    $oerSubjects = $oerSubjects ?? collect();
    $currentSubjectId = $currentSubjectId ?? request()->query('subject_id');
@endphp

<div class="space-y-6 animate-fade-in">
    @include('site.pages.partials.oer-header')

    <!-- Subject Filter Cards / Tabs with FontAwesome Icons -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <!-- All Subjects Option -->
        <a href="{{ request()->fullUrlWithQuery(['subject_id' => null]) }}" 
           class="flex flex-col items-center group">
            <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center mb-2 group-hover:shadow-md transition-all">
                <i class="fas fa-layer-group text-2xl {{ !$currentSubjectId ? 'text-[#8b0000]' : 'text-slate-400 group-hover:text-[#8b0000]' }}"></i>
            </div>
            <div class="w-full py-2.5 px-2 rounded-lg text-center font-black uppercase tracking-wider text-[10px] md:text-xs transition-colors shadow-sm {{ !$currentSubjectId ? 'bg-[#8b0000] text-white' : 'bg-slate-800 text-white hover:bg-[#8b0000]' }}">
                {{ __('Tất cả chủ đề') }}
            </div>
        </a>

        @foreach($oerSubjects as $sub)
            @php
                $isSelected = (string)$currentSubjectId === (string)$sub->id;
                $iconClass = 'fas fa-book-bookmark';
                if (str_contains(mb_strtolower($sub->name), 'y học')) $iconClass = 'fas fa-heart-pulse';
                elseif (str_contains(mb_strtolower($sub->name), 'kinh tế')) $iconClass = 'fas fa-chart-line';
                elseif (str_contains(mb_strtolower($sub->name), 'luật')) $iconClass = 'fas fa-scale-balanced';
                elseif (str_contains(mb_strtolower($sub->name), 'khác')) $iconClass = 'fas fa-microscope';
            @endphp
            <a href="{{ request()->fullUrlWithQuery(['subject_id' => $sub->id]) }}" 
               class="flex flex-col items-center group">
                <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center mb-2 group-hover:shadow-md transition-all">
                    <i class="{{ $iconClass }} text-2xl {{ $isSelected ? 'text-[#8b0000]' : 'text-slate-400 group-hover:text-[#8b0000]' }}"></i>
                </div>
                <div class="w-full py-2.5 px-2 rounded-lg text-center font-black uppercase tracking-wider text-[10px] md:text-xs transition-colors shadow-sm truncate {{ $isSelected ? 'bg-[#8b0000] text-white' : 'bg-slate-800 text-white hover:bg-[#8b0000]' }}" title="{{ $sub->name }}">
                    {{ $sub->name }}
                    <span class="text-[9px] font-semibold opacity-80">({{ $sub->resources_count ?? 0 }})</span>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Search Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
        <form id="oer-search-form" action="{{ request()->url() }}" method="GET" class="flex flex-col sm:flex-row gap-2">
            @if($currentSubjectId)
                <input type="hidden" name="subject_id" value="{{ $currentSubjectId }}">
            @endif
            @if(request()->has('sort'))
                <input type="hidden" name="sort" value="{{ request()->query('sort') }}">
            @endif

            <div class="flex-1 relative">
                <input type="text" 
                       name="q" 
                       id="oer-search-input" 
                       value="{{ $keyword ?? '' }}"
                       placeholder="{{ __('Nhập từ khóa tên sách, giáo trình, tác giả để tìm trong OER...') }}" 
                       class="w-full h-10 pl-10 pr-4 text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-[#8b0000] transition-all">
                <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
            </div>

            <button type="submit" class="px-6 h-10 bg-[#8b0000] text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#680102] transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-search"></i> {{ __('Tìm kiếm') }}
            </button>

            @if($keyword || $currentSubjectId)
                <a href="{{ request()->url() }}" class="px-4 h-10 bg-slate-200 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-300 transition-colors flex items-center justify-center gap-1.5" title="Bỏ lọc">
                    <i class="fas fa-times"></i> {{ __('Hủy lọc') }}
                </a>
            @endif
        </form>
    </div>

    <!-- Results Header -->
    <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-2 text-[#8b0000]">
            <i class="fas fa-book-open text-base"></i>
            <span class="text-xs font-black uppercase tracking-wider">
                {{ __('Tổng số tài nguyên OER') }}: <span class="text-sm ml-0.5 text-slate-900">{{ number_format($totalOerCount) }}</span> {{ __('sách/giáo trình') }}
            </span>
        </div>
    </div>

    <!-- OER Book Cards Grid / List -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse($oerResources as $item)
        <div class="p-5 flex flex-col sm:flex-row gap-5 group hover:bg-slate-50/60 transition-colors">
            <!-- Book Cover Thumbnail -->
            <div class="w-24 h-32 md:w-28 md:h-36 bg-slate-100 rounded-lg border border-slate-200 overflow-hidden flex-shrink-0 shadow-sm relative group-hover:shadow-md transition-all">
                <img src="{{ asset($item->thumbnail_url) }}" 
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                     alt="{{ $item->title }}">
            </div>

            <!-- Book Metadata -->
            <div class="flex-1 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="text-sm md:text-base font-black text-slate-900 group-hover:text-[#8b0000] transition-colors leading-snug line-clamp-2">
                        {{ $item->title }}
                    </h3>
                    @if($item->subject)
                        <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold rounded-full flex-shrink-0">
                            {{ $item->subject->name }}
                        </span>
                    @endif
                </div>

                <div class="space-y-1 text-xs text-slate-600">
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-user-pen text-[11px] text-slate-400 w-4"></i>
                        <span class="font-bold text-slate-700">{{ __('Tác giả') }}:</span>
                        <span>{{ $item->author ?: __('Chưa rõ') }}</span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-building-columns text-[11px] text-slate-400 w-4"></i>
                        <span class="font-bold text-slate-700">{{ __('Nguồn / NXB') }}:</span>
                        <span>{{ $item->publisher ?: __('Cổng thông tin Thư viện VTTU') }}</span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-scale-balanced text-[11px] text-slate-400 w-4"></i>
                        <span class="font-bold text-slate-700">{{ __('Giấy phép') }}:</span>
                        <span class="font-bold text-blue-600">{{ $item->license ?: 'CC BY-NC' }}</span>
                    </div>
                </div>

                @if($item->description)
                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed pt-1">
                        {{ $item->description }}
                    </p>
                @endif

                <!-- Read / Access Button -->
                <div class="pt-2">
                    <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-[#8b0000] hover:bg-[#680102] text-white text-xs font-bold rounded-lg shadow-sm transition-all active:scale-95">
                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                        <span>{{ __('Truy cập đọc sách mở') }}</span>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="py-16 text-center text-slate-400">
            <i class="fas fa-book-open text-4xl mb-3"></i>
            <p class="text-xs font-bold uppercase tracking-wider">{{ __('Không tìm thấy tài nguyên giáo dục mở nào') }}</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($oerResources instanceof \Illuminate\Pagination\LengthAwarePaginator && $oerResources->hasPages())
        <div class="pt-4">
            {{ $oerResources->links('site.partials.pagination-compact') }}
        </div>
    @endif
</div>
