@if(empty($records) || count($records) === 0)
<div class="flex flex-col items-center justify-center py-16 text-muted-foreground bg-card border border-border rounded-md">
    <i data-lucide="inbox" class="w-10 h-10 mb-2 stroke-[1.5] text-muted-foreground/50"></i>
    <p class="text-xs font-semibold">{{ __('Không tìm thấy dữ liệu phù hợp với bộ lọc.') }}</p>
</div>
@else
@php
    $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
@endphp
<div class="p-6 bg-card text-foreground space-y-6">
    <div class="text-center space-y-1 mb-6">
        <h1 class="text-lg font-bold tracking-tight uppercase text-foreground">
            {{ __('XEM TRƯỚC IN MÃ VẠCH') }}
        </h1>
        <p class="text-xs text-muted-foreground">
            {{ __('Tổng số bản ghi thỏa điều kiện: ') }} <span class="font-bold text-foreground">{{ number_format($totalCount) }}</span>
        </p>
    </div>

    <!-- Grid of barcodes (4 labels per row) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-w-4xl mx-auto p-4 bg-muted/10 rounded-md border border-border">
        @foreach($records as $item)
            @php
                $barcodeVal = $item->barcode;
                $svg = '';
                try {
                    $svg = $generator->getBarcode($barcodeVal, $generator::TYPE_CODE_93, 1.2, 38);
                } catch (\Exception $e) {
                    try {
                        $svg = $generator->getBarcode($barcodeVal, $generator::TYPE_CODE_128, 1.2, 38);
                    } catch (\Exception $ex) {
                        $svg = '<div class="text-[10px] text-red-500 font-bold">' . __('Lỗi tạo mã vạch') . '</div>';
                    }
                }
            @endphp
            
            <!-- Barcode Sticker Box -->
            <div class="flex flex-col border border-black dark:border-slate-700 bg-white dark:bg-slate-900 text-black dark:text-slate-100 text-center font-sans text-xs shadow-sm rounded-sm overflow-hidden select-none hover:shadow transition-shadow">
                <!-- Row 1: School Title -->
                <div class="border-b border-dashed border-black dark:border-slate-700 py-1.5 px-2 bg-slate-50 dark:bg-slate-950/40 text-[10px] uppercase font-bold tracking-wider text-muted-foreground">
                    {{ __('Đại học Võ Trường Toản') }}
                </div>
                <!-- Row 2: Subtitle -->
                <div class="border-b border-dashed border-black dark:border-slate-700 py-1 px-2 text-[10px] font-bold text-foreground">
                    {{ __('THƯ VIỆN') }}
                </div>
                <!-- Row 3: Barcode SVG Image -->
                <div class="border-b border-dashed border-black dark:border-slate-700 py-2.5 px-2 flex items-center justify-center bg-white min-h-[48px]">
                    <div class="barcode-svg-wrapper scale-90 sm:scale-100 origin-center">
                        {!! $svg !!}
                    </div>
                </div>
                <!-- Row 4: Raw Barcode Text -->
                <div class="py-1 px-2 text-[11px] font-mono tracking-widest font-bold text-foreground select-text">
                    {{ $barcodeVal }}
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
