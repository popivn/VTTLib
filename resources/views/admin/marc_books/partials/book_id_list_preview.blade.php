@if(empty($rows))
<div class="flex flex-col items-center justify-center py-16 text-muted-foreground bg-card border border-border rounded-md">
    <i data-lucide="inbox" class="w-10 h-10 mb-2 stroke-[1.5] text-muted-foreground/50"></i>
    <p class="text-xs font-semibold">{{ __('Không tìm thấy dữ liệu phù hợp với bộ lọc.') }}</p>
</div>
@else
<div class="flex flex-col">
    <!-- School Header Info -->
    <div class="text-[11px] text-muted-foreground space-y-0.5 border-b border-border pb-3 mb-5 px-4 pt-4">
        <div class="font-bold text-foreground uppercase tracking-wide">{{ __('THƯ VIỆN - TRƯỜNG ĐẠI HỌC VÕ TRƯỜNG TOẢN') }}</div>
        <div>{{ __('Địa chỉ: Quốc lộ 1A, xã Thạnh Xuân, Thành phố Cần Thơ') }}</div>
        <div>{{ __('Website: http://library.vttu.edu.vn/') }}</div>
    </div>
    
    <!-- Report Title -->
    <div class="text-center mb-6 px-4">
        <h2 class="text-xs sm:text-sm font-bold text-foreground uppercase tracking-widest">{{ __('DANH SÁCH TÀI LIỆU TRONG THƯ VIỆN') }}</h2>
        <p class="text-[10px] text-muted-foreground mt-1">{{ __('Tổng số bản ghi: :count', ['count' => number_format($totalCount)]) }}</p>
    </div>

    <!-- Table content -->
    <div class="overflow-x-auto w-full border-t border-b border-border">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-border bg-muted/40 text-muted-foreground font-semibold">
                    @foreach($headers as $header)
                        <th class="px-3 py-2 font-bold uppercase tracking-wider text-[10px] text-center border-r border-border last:border-r-0">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-border bg-card">
                @foreach($rows as $row)
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-3 py-2 text-center border-r border-border text-muted-foreground w-12">{{ $row[0] }}</td>
                        <td class="px-3 py-2 font-mono text-center border-r border-border font-bold text-primary w-28">{{ $row[1] }}</td>
                        <td class="px-3 py-2 font-medium border-r border-border max-w-[280px] truncate" title="{{ $row[2] }}">{{ $row[2] }}</td>
                        <td class="px-3 py-2 border-r border-border max-w-[150px] truncate text-muted-foreground" title="{{ $row[3] }}">{{ $row[3] }}</td>
                        <td class="px-3 py-2 text-center border-r border-border font-mono w-24">{{ $row[4] }}</td>
                        <td class="px-3 py-2 text-center border-r border-border font-semibold w-24">{{ $row[5] }}</td>
                        <td class="px-3 py-2 text-center border-r border-border w-14"></td>
                        <td class="px-3 py-2 text-center border-r border-border w-14"></td>
                        <td class="px-3 py-2 border-r border-border max-w-[120px] truncate text-muted-foreground" title="{{ $row[8] }}">{{ $row[8] }}</td>
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
