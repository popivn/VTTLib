@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1 flex-wrap justify-center">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                <span class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-muted-foreground/40 bg-muted/50 border border-border cursor-default rounded-sm">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-foreground bg-card border border-border rounded-sm hover:bg-muted/50 transition-colors" aria-label="{{ __('pagination.previous') }}">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        @endif

        {{-- Compact Pagination Elements: show max 3 pages around current --}}
        @php
            $currentPage = $paginator->currentPage();
            $lastPage = $paginator->lastPage();
            $startPage = max(1, $currentPage - 1);
            $endPage = min($lastPage, $currentPage + 1);
            // Always show first page
            $showFirstDots = $startPage > 2;
            // Always show last page
            $showLastDots = $endPage < $lastPage - 1;
        @endphp

        {{-- First page --}}
        @if ($startPage > 1)
            @if ($currentPage == 1)
                <span aria-current="page">
                    <span class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-black text-primary-foreground bg-primary border border-primary cursor-default rounded-sm">1</span>
                </span>
            @else
                <a href="{{ $paginator->url(1) }}" class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-bold text-foreground bg-card border border-border rounded-sm hover:bg-muted/50 transition-colors">1</a>
            @endif
        @endif

        {{-- Dots before --}}
        @if ($showFirstDots)
            <span class="relative inline-flex items-center px-1.5 py-1.5 text-xs font-medium text-muted-foreground">…</span>
        @endif

        {{-- Pages around current --}}
        @for ($page = max(2, $startPage); $page <= min($lastPage - 1, $endPage); $page++)
            @if ($page == $currentPage)
                <span aria-current="page">
                    <span class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-black text-primary-foreground bg-primary border border-primary cursor-default rounded-sm">{{ $page }}</span>
                </span>
            @else
                <a href="{{ $paginator->url($page) }}" class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-bold text-foreground bg-card border border-border rounded-sm hover:bg-muted/50 transition-colors">{{ $page }}</a>
            @endif
        @endfor

        {{-- Dots after --}}
        @if ($showLastDots)
            <span class="relative inline-flex items-center px-1.5 py-1.5 text-xs font-medium text-muted-foreground">…</span>
        @endif

        {{-- Last page --}}
        @if ($endPage < $lastPage)
            @if ($currentPage == $lastPage)
                <span aria-current="page">
                    <span class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-black text-primary-foreground bg-primary border border-primary cursor-default rounded-sm">{{ $lastPage }}</span>
                </span>
            @else
                <a href="{{ $paginator->url($lastPage) }}" class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-bold text-foreground bg-card border border-border rounded-sm hover:bg-muted/50 transition-colors">{{ $lastPage }}</a>
            @endif
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-foreground bg-card border border-border rounded-sm hover:bg-muted/50 transition-colors" aria-label="{{ __('pagination.next') }}">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        @else
            <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                <span class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-muted-foreground/40 bg-muted/50 border border-border cursor-default rounded-sm">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
            </span>
        @endif
    </nav>
@endif
