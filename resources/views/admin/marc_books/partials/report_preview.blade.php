@if(empty($rows))
<div class="flex flex-col items-center justify-center py-16 text-muted-foreground">
    <i data-lucide="inbox" class="w-10 h-10 mb-2 stroke-[1.5] text-muted-foreground/50"></i>
    <p class="text-xs font-semibold">{{ __('Không tìm thấy dữ liệu phù hợp với bộ lọc.') }}</p>
</div>
@else
<div class="flex flex-col">
    <!-- Table Header Summary -->
    <div class="px-4 py-2 border-b border-border bg-muted/20 flex items-center justify-between text-[11px] font-bold text-muted-foreground uppercase tracking-wider">
        <span>{{ __('Danh sách xem trước') }}</span>
        <span>{{ __('Tìm thấy :count dòng', ['count' => number_format($totalCount)]) }}</span>
    </div>

    <!-- Table content -->
    <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-border bg-muted/40 text-muted-foreground font-semibold">
                    @foreach($headers as $header)
                    <th class="px-4 py-2.5 font-bold uppercase tracking-wider text-[10px]">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-border bg-card">
                @foreach($rows as $row)
                <tr class="hover:bg-muted/30 transition-colors">
                    @foreach($row as $cell)
                    <td class="px-4 py-2 text-foreground font-medium truncate max-w-[300px]" title="{{ $cell }}">{{ $cell }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <div class="preview-pagination p-3 border-t border-border bg-muted/10 flex flex-col sm:flex-row items-center justify-between gap-2">
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
