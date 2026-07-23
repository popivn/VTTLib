@php
    $resources = $resources ?? collect();
    $totalCount = $totalCount ?? 0;
    $folders = $folders ?? collect();
    $currentFolderId = $currentFolderId ?? request()->query('folder_id');
@endphp

<div class="space-y-4 animate-fade-in" x-data="{ treeOpen: true }">
    
    <!-- Top Action Bar with Folder Tree Toggle & Sort Tabs -->
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border bg-muted/20 p-2 rounded-t-md">
        
        <!-- Toggle Folder Tree Button & Active Folder Indicator -->
        <div class="flex items-center gap-2">
            <button @click="treeOpen = !treeOpen" 
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold bg-vttu-red text-white hover:bg-vttu-dark rounded-md shadow-sm transition-all active:scale-95"
                    title="{{ __('Thu gọn / Mở rộng Cây Thư Mục') }}">
                <i class="fa-solid fa-bars text-xs"></i>
                <span x-text="treeOpen ? 'Thu gọn Cây Thư Mục' : 'Mở Cây Thư Mục'"></span>
                <i class="fa-solid fa-angle-left text-xs transition-transform duration-300" :class="!treeOpen && 'rotate-180'"></i>
            </button>

            @if($currentFolderId)
                @php
                    $allFlatFolders = \App\Models\DigitalFolder::where('is_active', true)->get();
                    $activeFolder = $allFlatFolders->firstWhere('id', $currentFolderId);
                @endphp
                @if($activeFolder)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-500/10 text-amber-600 border border-amber-500/30 text-xs font-bold rounded-md">
                        <i class="fa-solid fa-folder-open text-xs"></i>
                        <span>{{ $activeFolder->folder_name }}</span>
                        <a href="{{ request()->fullUrlWithQuery(['folder_id' => null]) }}" class="hover:text-rose-600 ml-1" title="Bỏ lọc thư mục">
                            <i class="fa-solid fa-circle-xmark text-xs"></i>
                        </a>
                    </span>
                @endif
            @endif
        </div>

        <!-- Sort Tabs Navigation -->
        <div class="flex items-center gap-1 overflow-x-auto">
            <button onclick="changeSort('oldest_updated')" class="px-3 py-1.5 text-[11px] uppercase tracking-wider font-bold rounded-md transition-all {{ $currentSort === 'oldest_updated' ? 'bg-vttu-red text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/80' }}">
                {{ __('Cũ đến mới') }}
            </button>
            <button onclick="changeSort('latest')" class="px-3 py-1.5 text-[11px] uppercase tracking-wider font-bold rounded-md transition-all {{ $currentSort === 'latest' ? 'bg-vttu-red text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/80' }}">
                {{ __('Mới nhất') }}
            </button>
            <button onclick="changeSort('most_viewed')" class="px-3 py-1.5 text-[11px] uppercase tracking-wider font-bold rounded-md transition-all {{ $currentSort === 'most_viewed' ? 'bg-vttu-red text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/80' }}">
                {{ __('Xem nhiều') }}
            </button>
            <button onclick="changeSort('most_downloaded')" class="px-3 py-1.5 text-[11px] uppercase tracking-wider font-bold rounded-md transition-all {{ $currentSort === 'most_downloaded' ? 'bg-vttu-red text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/80' }}">
                {{ __('Tải nhiều') }}
            </button>
        </div>
    </div>

    <!-- Main Container: Collapsible Folder Tree + Resource Table -->
    <div class="flex flex-col lg:flex-row gap-4 items-start">
        
        <!-- Left Column: Cây Thư Mục Tài Liệu Số (Collapsible Hierarchical Sidebar) -->
        <div x-show="treeOpen" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="-translate-x-full opacity-0"
             class="w-full lg:w-72 bg-card border border-border rounded-lg shadow-sm overflow-hidden flex-shrink-0 lg:sticky lg:top-20">
            
            <!-- Folder Tree Header -->
            <div class="p-3 bg-muted/40 border-b border-border flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-wider text-vttu-dark flex items-center gap-2">
                    <i class="fa-solid fa-sitemap text-vttu-red"></i>
                    <span>Cây Thư Mục Tài Liệu</span>
                </h3>
                <span class="px-2 py-0.5 bg-vttu-red/10 text-vttu-red text-[10px] font-black rounded-full">
                    {{ $folders->count() }} gốc
                </span>
            </div>

            <!-- Hierarchical Folder Tree List -->
            <div class="p-2 space-y-1 max-h-[600px] overflow-y-auto custom-scrollbar">
                <!-- Option: All Folders -->
                <a href="{{ request()->fullUrlWithQuery(['folder_id' => null]) }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-md text-xs transition-all {{ !$currentFolderId ? 'bg-vttu-red text-white font-bold shadow-sm' : 'text-foreground hover:bg-muted/80' }}">
                    <div class="flex items-center gap-2 truncate">
                        <i class="fa-solid fa-layer-group text-xs {{ !$currentFolderId ? 'text-vttu-yellow' : 'text-vttu-red' }}"></i>
                        <span class="truncate">Tất cả tài liệu số</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ !$currentFolderId ? 'bg-white/20 text-white' : 'bg-muted text-muted-foreground' }}">
                        {{ number_format(\App\Models\DigitalResource::where('status', 'published')->count()) }}
                    </span>
                </a>

                <div class="my-1 border-t border-border/60"></div>

                <!-- List of Parent & Child Folders -->
                @foreach($folders as $folder)
                    @php
                        $hasChildren = $folder->children && $folder->children->count() > 0;
                        $isParentSelected = (string)$currentFolderId === (string)$folder->id;
                        $hasChildSelected = $hasChildren && $folder->children->pluck('id')->map(fn($id) => (string)$id)->contains((string)$currentFolderId);
                        $totalSubResources = $folder->resources_count + ($hasChildren ? $folder->children->sum('resources_count') : 0);
                    @endphp

                    <div x-data="{ open: {{ ($isParentSelected || $hasChildSelected) ? 'true' : 'true' }} }" class="space-y-1">
                        <!-- Parent Folder Row -->
                        @if($hasChildren)
                            <div @click="open = !open" 
                                 class="flex items-center justify-between px-3 py-1.5 rounded-md text-xs transition-all cursor-pointer text-foreground hover:bg-vttu-red/5 hover:text-vttu-red select-none">
                                <div class="flex items-center gap-2 truncate flex-1 min-w-0">
                                    <button type="button" class="p-0.5 text-muted-foreground hover:text-foreground focus:outline-none transition-transform" title="Thu gọn / Mở rộng">
                                        <i class="fa-solid fa-angle-right text-[11px] transition-transform duration-200" :class="open && 'rotate-90'"></i>
                                    </button>

                                    <div class="flex items-center gap-2 truncate flex-1">
                                        <i class="fa-solid fa-folder text-amber-500 text-xs" :class="open ? 'fa-folder-open' : 'fa-folder'"></i>
                                        <span class="truncate font-semibold" title="{{ $folder->folder_name }}">{{ $folder->folder_name }}</span>
                                    </div>
                                </div>

                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-muted text-muted-foreground">
                                    {{ $totalSubResources }}
                                </span>
                            </div>
                        @else
                            <div class="flex items-center justify-between px-3 py-1.5 rounded-md text-xs transition-all {{ $isParentSelected ? 'bg-vttu-red text-white font-bold shadow-sm' : 'text-foreground hover:bg-vttu-red/5 hover:text-vttu-red' }}">
                                <div class="flex items-center gap-2 truncate flex-1 min-w-0">
                                    <span class="w-3"></span>
                                    <a href="{{ request()->fullUrlWithQuery(['folder_id' => $folder->id]) }}" class="flex items-center gap-2 truncate flex-1">
                                        <i class="{{ $isParentSelected ? 'fa-solid fa-folder-open text-vttu-yellow' : 'fa-solid fa-folder text-amber-500' }} text-xs"></i>
                                        <span class="truncate font-semibold" title="{{ $folder->folder_name }}">{{ $folder->folder_name }}</span>
                                    </a>
                                </div>

                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isParentSelected ? 'bg-white/20 text-white' : 'bg-muted text-muted-foreground' }}">
                                    {{ $totalSubResources }}
                                </span>
                            </div>
                        @endif

                        <!-- Nested Child Folders (If Any) -->
                        @if($hasChildren)
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="pl-5 ml-3 border-l-2 border-vttu-red/20 space-y-1">
                                @foreach($folder->children as $child)
                                    @php
                                        $isChildSelected = (string)$currentFolderId === (string)$child->id;
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['folder_id' => $child->id]) }}" 
                                       class="flex items-center justify-between px-2.5 py-1 rounded-md text-xs transition-all {{ $isChildSelected ? 'bg-vttu-red text-white font-bold shadow-sm' : 'text-muted-foreground hover:text-vttu-red hover:bg-vttu-red/5' }}">
                                        <div class="flex items-center gap-2 truncate">
                                            <i class="{{ $isChildSelected ? 'fa-solid fa-folder-open text-vttu-yellow' : 'fa-solid fa-folder text-amber-400' }} text-[11px]"></i>
                                            <span class="truncate" title="{{ $child->folder_name }}">{{ $child->folder_name }}</span>
                                        </div>
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold {{ $isChildSelected ? 'bg-white/20 text-white' : 'bg-muted text-muted-foreground' }}">
                                            {{ $child->resources_count ?? 0 }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right Column: Main Search Form & Data Table -->
        <div class="flex-1 w-full space-y-3">
            
            <!-- Search & Filter Form -->
            <form method="GET" action="" class="bg-card border border-border shadow-sm p-3 rounded-lg flex flex-col md:flex-row gap-2 transition-colors duration-200">
                @if(request()->has('sort'))
                    <input type="hidden" name="sort" value="{{ request()->query('sort') }}">
                @endif
                @if($currentFolderId)
                    <input type="hidden" name="folder_id" value="{{ $currentFolderId }}">
                @endif

                <div class="flex flex-col sm:flex-row gap-2 flex-1 w-full">
                    <!-- Search Field Selection -->
                    <div class="relative min-w-[130px] sm:w-[150px]">
                        <select name="field" class="w-full h-9 pl-3 pr-8 text-xs font-bold bg-muted/30 border border-border rounded-md appearance-none outline-none focus:ring-1 focus:ring-vttu-red/30 transition-all cursor-pointer">
                            <option value="title" {{ ($currentField ?? 'title') === 'title' ? 'selected' : '' }}>{{ __('Tiêu đề') }}</option>
                            <option value="author" {{ ($currentField ?? 'title') === 'author' ? 'selected' : '' }}>{{ __('Tác giả') }}</option>
                            <option value="subject" {{ ($currentField ?? 'title') === 'subject' ? 'selected' : '' }}>{{ __('Chủ đề') }}</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-muted-foreground pointer-events-none"></i>
                    </div>
                    
                    <!-- Keyword Search Input -->
                    <div class="flex-1 relative group">
                        <input type="text" 
                               name="q"
                               value="{{ $keyword ?? '' }}"
                               placeholder="{{ __('Nhập từ khóa tìm kiếm tài liệu số...') }}"
                               class="w-full h-9 pl-3 pr-10 text-xs bg-background border border-border rounded-md outline-none focus:ring-1 focus:ring-vttu-red/30 transition-all placeholder:text-muted-foreground/60">
                        <div class="absolute right-0 top-0 h-9 px-3 flex items-center justify-center text-muted-foreground opacity-40">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Submit & Reset Buttons -->
                <div class="flex gap-1.5 h-9 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none px-5 bg-vttu-red text-white rounded-md hover:bg-vttu-dark active:scale-[0.97] transition-all shadow-sm flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        <span class="text-xs font-black uppercase tracking-wider">{{ __('Tìm') }}</span>
                    </button>
                    @if($keyword || $currentFolderId)
                        <a href="{{ request()->url() }}{{ request()->has('sort') ? '?sort='.request()->query('sort') : '' }}" 
                           class="px-3 bg-muted text-foreground border border-border rounded-md hover:bg-muted/80 transition-all flex items-center justify-center"
                           title="{{ __('Xóa bộ lọc') }}">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Results Count Info Bar -->
            <div class="flex items-center justify-between text-vttu-red px-1">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded bg-vttu-red/10 flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-database text-xs"></i>
                    </div>
                    <span class="text-xs font-black uppercase tracking-wider">
                        {{ __('Tổng số kết quả') }}: <span class="text-sm ml-0.5 text-vttu-dark">{{ number_format($totalCount) }}</span> file
                    </span>
                </div>
            </div>

            <!-- Resource Data Table -->
            <div class="bg-card border border-border rounded-lg shadow-sm overflow-hidden w-full">
                <table class="w-full text-left border-collapse table-fixed">
                    <thead class="bg-muted/50 text-[10px] font-black uppercase tracking-widest text-muted-foreground border-b border-border">
                        <tr>
                            <th class="py-2.5 px-4 w-12 text-center border-r border-border/50">#</th>
                            <th class="py-2.5 px-4">{{ __('Tiêu đề tài liệu') }}</th>
                            <th class="py-2.5 px-4 w-48 text-center hidden md:table-cell border-l border-border/50">{{ __('Tác giả') }}</th>
                            <th class="py-2.5 px-4 w-32 text-center hidden md:table-cell border-l border-border/50">{{ __('Ngày cập nhật') }}</th>
                            <th class="py-2.5 px-4 w-32 text-right hidden sm:table-cell border-l border-border/50">{{ __('Loại tài liệu') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/40 text-xs">
                        @forelse($resources as $index => $item)
                        <tr class="group hover:bg-muted/30 active:bg-muted/50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('site.digital-resources.show', $item->id) }}'">
                            <td class="py-4 px-4 text-center font-bold text-muted-foreground group-hover:text-vttu-red transition-colors border-r border-border/30 w-12">
                                {{ ($resources instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $resources->firstItem() + $index : ($index + 1) }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-20 bg-muted rounded-md flex-shrink-0 overflow-hidden border border-border group-hover:border-vttu-red/30 transition-colors shadow-sm relative">
                                        <img src="{{ $item->thumbnail_url ?? 'https://placehold.co/100x140/7B0000/FFFFFF?text=DOC' }}" 
                                             class="w-full h-full object-cover transition-transform group-hover:scale-110 duration-500"
                                             alt="{{ $item->title }}">
                                    </div>
                                    <div class="space-y-1.5 overflow-hidden flex-1">
                                        <h4 class="font-black text-vttu-dark group-hover:text-vttu-red transition-colors line-clamp-2 text-xs md:text-sm tracking-tight leading-snug uppercase">
                                            {{ $item->title }}
                                        </h4>
                                        <div class="flex flex-wrap gap-2 pt-1 md:hidden">
                                            <span class="text-[10px] text-vttu-red/70 font-bold italic"><i class="fa-solid fa-user mr-0.5"></i> {{ $item->author ?: __('Đang cập nhật') }}</span>
                                            <span class="text-[10px] text-slate-500 font-bold italic"><i class="fa-regular fa-calendar mr-0.5"></i> {{ $item->updated_at ? $item->updated_at->format('d-m-Y') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 w-48 text-center hidden md:table-cell border-l border-border/30">
                                <span class="font-bold text-vttu-dark/80 group-hover:text-vttu-dark transition-colors uppercase tracking-tight text-[11px] line-clamp-2">
                                    {{ $item->author ?: __('Đang cập nhật') }}
                                </span>
                            </td>
                            <td class="py-4 px-4 w-32 text-center hidden md:table-cell border-l border-border/30">
                                <span class="text-muted-foreground group-hover:text-foreground transition-colors font-bold text-[11px]">
                                    {{ $item->updated_at ? $item->updated_at->format('d-m-Y') : 'N/A' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 w-32 text-right hidden sm:table-cell border-l border-border/30">
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-muted text-muted-foreground text-[10px] font-black uppercase border border-border group-hover:bg-vttu-red/10 group-hover:text-vttu-red transition-all">
                                    {{ __('Tài liệu số') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-muted-foreground opacity-40">
                                    <i class="fa-solid fa-folder-open text-4xl mb-3"></i>
                                    <p class="text-xs font-bold uppercase tracking-widest">{{ __('Không tìm thấy tài liệu số nào trong thư mục này') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($resources instanceof \Illuminate\Pagination\LengthAwarePaginator && $resources->hasPages())
                <div class="pt-2">
                    {{ $resources->links('site.partials.pagination-compact') }}
                </div>
            @endif
        </div>

    </div>
</div>

<script>
function changeSort(sortType) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortType);
    window.location.href = url.toString();
}
</script>
