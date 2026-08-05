@if(empty($records) || count($records) === 0)
<div class="flex flex-col items-center justify-center py-16 text-muted-foreground bg-card border border-border rounded-md">
    <i data-lucide="inbox" class="w-10 h-10 mb-2 stroke-[1.5] text-muted-foreground/50"></i>
    <p class="text-xs font-semibold">{{ __('Không tìm thấy dữ liệu phù hợp với bộ lọc.') }}</p>
</div>
@else
<div class="p-6 bg-card text-foreground space-y-6">
    <div class="text-center space-y-1 mb-6">
        <h1 class="text-lg font-bold tracking-tight uppercase text-foreground">
            {{ __('XEM TRƯỚC NHÃN GÁY SÁCH') }}
        </h1>
        <p class="text-xs text-muted-foreground">
            {{ __('Tổng số bản ghi thỏa điều kiện: ') }} <span class="font-bold text-foreground">{{ number_format($totalCount) }}</span>
        </p>
    </div>

    <!-- Grid representing the label sticker layout (4 labels per row on desktop) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-w-4xl mx-auto p-4 bg-muted/10 rounded-md border border-border">
        @foreach($records as $item)
            @php
                $record = $item->bibliographicRecord;
                $ddc = $record->getMarcValue('082', 'a') ?: $record->getMarcValue('090', 'a');
                $authorCode = $record->getMarcValue('082', 'b') ?: ($record->getMarcValue('090', 'b') ?: ($record->getMarcValue('100', 'a') ? mb_substr($record->getMarcValue('100', 'a'), 0, 3, 'UTF-8') : ''));
            @endphp
            
            <!-- Sticky Label Box -->
            <div class="flex flex-col border border-black dark:border-slate-700 bg-white dark:bg-slate-900 text-black dark:text-slate-100 text-center font-serif text-sm shadow-sm rounded-sm overflow-hidden select-none hover:shadow transition-shadow">
                <!-- Row 1: Library Prefix -->
                <div class="border-b border-black dark:border-slate-700 py-2 px-2 bg-slate-50 dark:bg-slate-950/40 select-text text-xs text-muted-foreground font-sans uppercase tracking-wider font-semibold">
                    TV ĐH VTT
                </div>
                <!-- Row 2: DDC -->
                <div class="border-b border-dashed border-black dark:border-slate-700 py-2.5 px-2 font-bold tracking-wide select-text min-h-[36px] flex items-center justify-center text-sm">
                    {{ $ddc ?: '--' }}
                </div>
                <!-- Row 3: Cutter/Author Code -->
                <div class="py-2.5 px-2 font-bold tracking-wide select-text min-h-[36px] flex items-center justify-center text-sm">
                    {{ $authorCode ?: '--' }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination Links -->
    <div class="preview-pagination p-3 border-t border-border bg-muted/10 flex flex-col sm:flex-row items-center justify-between gap-2 mt-6">
        <div class="text-[11px] text-muted-foreground text-center sm:text-left">
            {{ __('Hiển thị từ :first đến :last trong tổng số :total bản ghi', [
                'first' => number_format($paginated->firstItem()),
                'last' => number_format($paginated->lastItem()),
                'total' => number_format($paginated->total())
            ]) }}
        </div>
        <div class="flex items-center gap-1 flex-wrap justify-center">
            {{ $paginated->links('admin.marc_books.partials.pagination-compact') }}
        </div>
    </div>
</div>
@endif
