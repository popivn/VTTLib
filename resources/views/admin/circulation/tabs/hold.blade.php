<div class="space-y-3" data-tab="hold">
    <div class="flex justify-end">
        <button type="button" onclick="refreshHoldTab()" class="text-[10px] text-muted-foreground hover:text-foreground transition-colors flex items-center gap-1">
            <i data-lucide="rotate-cw" class="w-3 h-3"></i> {{ __('Tải lại') }}
        </button>
    </div>

    <!-- 2-column layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <!-- Left: Patron Lookup -->
        <div class="bg-card border border-border p-3 rounded-md shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-orange-500 flex items-center gap-1">
                <i data-lucide="user-search" class="w-4 h-4"></i>
                <span>{{ __('Tra cứu bạn đọc') }}</span>
            </h3>
            <div>
                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Mã bạn đọc') }} *</label>
                <div class="relative">
                    <input type="text" id="hold_patron_code" name="patron_code" class="input-field pr-9"
                        placeholder="{{ __('Quét hoặc nhập mã bạn đọc...') }}" autofocus autocomplete="off">
                    <button type="button" onclick="searchPatronByCode(document.getElementById('hold_patron_code').value)"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-[10px] text-muted-foreground mt-1">{{ __('Quét thẻ để xem thông tin và danh sách đặt giữ') }}</p>
            </div>
        </div>

        <!-- Right: Place Hold Form -->
        <div class="bg-card border border-border p-3 rounded-md shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-orange-500 flex items-center gap-1">
                <i data-lucide="bookmark-plus" class="w-4 h-4"></i>
                <span>{{ __('Đặt giữ sách') }}</span>
            </h3>
            <div>
                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Mã vạch tài liệu') }} *</label>
                <div class="relative">
                    <input type="text" id="hold_book_barcode" name="barcode" class="input-field pr-9"
                        placeholder="{{ __('Quét hoặc nhập mã vạch tài liệu...') }}">
                    <button type="button" onclick="searchBookByBarcode(document.getElementById('hold_book_barcode').value)"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-foreground">{{ __('Ghi chú') }}</label>
                <textarea id="hold_notes" name="notes" rows="2" class="input-field !h-auto py-1.5"
                    placeholder="{{ __('Nhập ghi chú (không bắt buộc)') }}"></textarea>
            </div>
            <button type="button" onclick="processPlaceHold()"
                    class="w-full btn-compact-primary h-10 text-sm flex items-center justify-center gap-1.5">
                <i data-lucide="bookmark" class="w-4 h-4"></i>
                {{ __('Xác nhận đặt giữ') }}
            </button>
        </div>
    </div>

    <!-- Patron Info + Book Info (reservations shown inside patron card) -->
    <div class="space-y-3">
        @include('admin.circulation.components.patron-info', ['id' => 'holdPatronInfo'])
        @include('admin.circulation.components.book-info', ['id' => 'holdBookInfo'])
    </div>
</div>
