<div class="space-y-3" data-tab="reading-room">
    <div class="flex justify-end">
        <button type="button" onclick="refreshReadingRoom()" class="text-[10px] text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
            <i data-lucide="rotate-cw" class="w-3 h-3"></i> {{ __('Tải lại') }}
        </button>
    </div>

    <!-- 2-column layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <!-- Left: Patron Lookup -->
        <div class="bg-card border border-border p-3 rounded-md shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-purple-500 flex items-center gap-1">
                <i data-lucide="user-search" class="w-4 h-4"></i>
                <span>{{ __('Tra cứu bạn đọc') }}</span>
            </h3>
            <div>
                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Mã bạn đọc') }} *</label>
                <div class="relative">
                    <input type="text" id="reading_patron_code" name="patron_code" class="input-field pr-9"
                        placeholder="{{ __('Quét hoặc nhập mã bạn đọc...') }}" autofocus autocomplete="off">
                    <button type="button" onclick="searchPatronByCode(document.getElementById('reading_patron_code').value)"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-[10px] text-muted-foreground mt-1">{{ __('Quét thẻ để xem thông tin và tài liệu đang mượn đọc') }}</p>
            </div>
        </div>

        <!-- Right: New Reading Room Checkout -->
        <div class="bg-card border border-border p-3 rounded-md shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-purple-500 flex items-center gap-1">
                <i data-lucide="scan-barcode" class="w-4 h-4"></i>
                <span>{{ __('Mượn đọc tại chỗ') }}</span>
            </h3>
            <div>
                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Mã vạch tài liệu') }} *</label>
                <div class="relative">
                    <input type="text" id="reading_book_barcode" name="barcode" class="input-field pr-9"
                        placeholder="{{ __('Quét hoặc nhập mã vạch tài liệu...') }}">
                    <button type="button" onclick="searchBookByBarcode(document.getElementById('reading_book_barcode').value)"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <button type="button" onclick="processReadingRoomCheckout()"
                    class="w-full btn-compact-primary h-10 text-sm flex items-center justify-center gap-1.5">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                {{ __('Xác nhận mượn đọc') }}
            </button>
        </div>
    </div>

    <!-- Patron Info + Book Info (transactions shown inside patron card) -->
    <div class="space-y-3">
        @include('admin.circulation.components.patron-info', ['id' => 'readingPatronInfo'])
        @include('admin.circulation.components.book-info', ['id' => 'readingBookInfo'])
    </div>
</div>
