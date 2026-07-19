<div class="space-y-4 animate-fade-in"
     x-data="{ 
         isFullscreen: false,
         toggleFullscreen() {
             let container = document.getElementById('pdf-viewer-container');
             if (!container) return;
             if (!document.fullscreenElement) {
                 container.requestFullscreen().then(() => {
                     this.isFullscreen = true;
                     if(typeof sidebarOpen !== 'undefined') sidebarOpen = false;
                 }).catch(err => {
                     console.warn('Native fullscreen failed, fallback to CSS:', err);
                     this.isFullscreen = true;
                     if(typeof sidebarOpen !== 'undefined') sidebarOpen = false;
                 });
             } else {
                 document.exitFullscreen().then(() => {
                     this.isFullscreen = false;
                     if(typeof sidebarOpen !== 'undefined') sidebarOpen = true;
                 }).catch(err => {
                     console.warn('Exit fullscreen failed, fallback to CSS:', err);
                     this.isFullscreen = false;
                     if(typeof sidebarOpen !== 'undefined') sidebarOpen = true;
                 });
             }
         }
     }"
     @fullscreenchange.window="isFullscreen = !!document.fullscreenElement; if (!isFullscreen && typeof sidebarOpen !== 'undefined') sidebarOpen = true; if (isFullscreen && typeof sidebarOpen !== 'undefined') sidebarOpen = false"
     x-effect="document.body.style.overflow = isFullscreen ? 'hidden' : 'auto'">

    <!-- Header with Back Button -->
    <div class="flex items-center justify-between bg-card p-3 border border-border rounded-sm shadow-sm" x-show="!isFullscreen" x-transition>
        <div class="flex items-center gap-3">
            <a href="{{ route('site.digital-resources.show', $resource->id) }}"
               class="p-2 rounded bg-muted hover:bg-primary/10 hover:text-primary active:scale-95 transition-all border border-border shadow-sm group"
               title="{{ __('Quay lại chi tiết') }}">
                <i data-lucide="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
            </a>
            <div class="w-1 h-4 bg-vttu-red rounded-full"></div>
            <h1 class="text-xs md:text-sm font-black text-vttu-dark uppercase tracking-tight line-clamp-1">
                {{ $resource->title }}
            </h1>
        </div>
        <div class="flex items-center gap-2">
            @auth
                @php
                    $allowedGroups = json_decode(\App\Models\SystemSetting::get('digital_download_allowed_groups', '[]'), true) ?: [];
                    $userGroupId = auth()->user()->patronDetail?->patron_group_id;
                    $canDownload = in_array($userGroupId, $allowedGroups);
                @endphp

                @if($canDownload)
                <a href="{{ route('site.digital-resources.download', $resource->id) }}"
                   class="hidden md:flex items-center px-4 py-2 bg-[#3b82f6] text-white rounded-sm text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 active:scale-95 transition-all shadow-sm">
                    <i data-lucide="download" class="w-3.5 h-3.5 mr-1.5"></i> {{ __('Tải tài liệu') }}
                </a>
                @endif
            @endauth

            <button @click="toggleFullscreen()"
                    class="flex items-center px-4 py-2 bg-vttu-red text-white rounded-sm text-[10px] font-black uppercase tracking-widest hover:bg-vttu-dark active:scale-95 transition-all shadow-sm">
                <i data-lucide="maximize" class="w-3.5 h-3.5 mr-1.5"></i> {{ __('Mở rộng tối đa') }}
            </button>
        </div>
    </div>

    @php
        $viewLimits    = json_decode(\App\Models\SystemSetting::get('digital_view_page_limits', '[]'), true) ?: [];
        $allowedGroups = json_decode(\App\Models\SystemSetting::get('digital_download_allowed_groups', '[]'), true) ?: [];
        $userGroupId   = auth()->user()?->patronDetail?->patron_group_id;

        $canDownload = in_array($userGroupId, $allowedGroups);

        // Chỉ giới hạn trang với KHÁCH (chưa đăng nhập)
        // Sinh viên / user đã đăng nhập → không giới hạn (0 = toàn bộ)
        $pageLimit = auth()->check() ? 0 : ($viewLimits['guest'] ?? 10);

        // Kiem tra phan mo rong file
        $extension = strtolower(pathinfo($resource->file_name, PATHINFO_EXTENSION));
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
    @endphp

    @if($isImage)
        {{-- ===== TÀI LIỆU DẠNG ẢNH: Hiển thị trực tiếp thẻ img ===== --}}
        <div id="pdf-viewer-container"
             class="bg-slate-950 border border-border rounded-sm shadow-lg overflow-hidden flex items-center justify-center p-4"
             :class="isFullscreen ? 'fixed inset-0 z-[9999] m-0 rounded-none w-screen h-screen bg-black' : 'relative h-[750px] md:h-[calc(100vh-180px)]'">
            
            <div class="w-full h-full overflow-auto flex items-center justify-center">
                <img src="{{ route('site.digital-resources.stream', $resource->id) }}" 
                     alt="{{ $resource->title }}"
                     class="max-w-full max-h-full object-contain rounded shadow-md">
            </div>

            <div x-show="isFullscreen" class="absolute bottom-6 right-6 z-[10000]" x-transition>
                <button @click="toggleFullscreen()"
                        class="w-12 h-12 flex items-center justify-center bg-vttu-red text-white rounded-full shadow-2xl hover:bg-vttu-dark active:scale-90 transition-all border-2 border-white/20"
                        title="{{ __('Thu nhỏ') }}">
                    <i data-lucide="minimize" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    @else
        @if($canDownload)
            {{-- ===== GIÁO VIÊN: Iframe stream thẳng file gốc ===== --}}
            <div id="pdf-viewer-container"
                 class="bg-card border border-border rounded-sm shadow-lg overflow-hidden relative"
                 :class="isFullscreen ? 'fixed inset-0 z-[9999] m-0 rounded-none w-screen h-screen' : 'relative h-[750px] md:h-[calc(100vh-180px)]'">

                <iframe src="{{ route('site.digital-resources.stream', $resource->id) }}#toolbar=1&navpanes=1&scrollbar=1"
                        class="w-full h-full border-none"
                        allow="autoplay; fullscreen"
                        allowfullscreen>
                </iframe>

                <div x-show="isFullscreen" class="absolute bottom-6 right-6 z-[10000]" x-transition>
                    <button @click="toggleFullscreen()"
                            class="w-12 h-12 flex items-center justify-center bg-vttu-red text-white rounded-full shadow-2xl hover:bg-vttu-dark active:scale-90 transition-all border-2 border-white/20"
                            title="{{ __('Thu nhỏ') }}">
                        <i data-lucide="minimize" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        @else
            {{-- ===== SINH VIÊN / KHÁCH: PDF.js 3 layers + stream mã hóa ===== --}}
            <div id="pdf-viewer-container"
                 class="bg-slate-950 border border-border rounded-sm shadow-lg overflow-hidden relative"
                 :class="isFullscreen ? 'fixed inset-0 z-[9999] m-0 rounded-none w-screen h-screen bg-black' : 'relative h-[750px] md:h-[calc(100vh-180px)]'">

                <!-- Floating Top Viewer Controls Bar -->
                <div id="pdf-floating-controls" class="absolute top-3 left-1/2 -translate-x-1/2 z-[10000] flex items-center gap-3 bg-slate-900/90 backdrop-blur-md px-4 py-1.5 rounded-full border border-slate-700/80 shadow-2xl text-white transition-all">
                    <!-- Page Indicator & Search / Jump to Page -->
                    <div class="flex items-center gap-2">
                        <i class="fas fa-file-alt text-vttu-yellow text-xs"></i>
                        <span class="text-xs font-bold text-slate-300">Trang</span>
                        <input type="number" 
                               id="pdf-page-jump-input" 
                               min="1" 
                               value="1" 
                               class="w-12 h-6 px-1 text-center bg-slate-800 border border-slate-600 rounded text-xs font-bold text-vttu-yellow outline-none focus:border-vttu-yellow transition-all"
                               title="Nhập số trang để tìm / chuyển nhanh">
                        <span class="text-xs font-bold text-slate-300">/ <span id="pdf-total-pages-display">--</span></span>
                        <button id="pdf-page-jump-btn" type="button" class="px-2.5 py-0.5 bg-vttu-red hover:bg-vttu-dark text-white rounded text-[10px] font-bold uppercase transition-all shadow-sm">
                            Đến
                        </button>
                    </div>

                    <div class="h-4 w-px bg-slate-700"></div>

                    <!-- Zoom Level Display / Buttons -->
                    <div class="flex items-center gap-1.5">
                        <button id="pdf-zoom-out" type="button" class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-xs text-slate-300 hover:text-white transition-all" title="Thu nhỏ">
                            <i class="fas fa-minus text-[9px]"></i>
                        </button>
                        <span id="pdf-zoom-val" class="text-[10px] font-bold text-slate-300 w-10 text-center">100%</span>
                        <button id="pdf-zoom-in" type="button" class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-xs text-slate-300 hover:text-white transition-all" title="Phóng to">
                            <i class="fas fa-plus text-[9px]"></i>
                        </button>
                    </div>

                    <div class="h-4 w-px bg-slate-700"></div>

                    <!-- Fullscreen Button -->
                    <button @click="toggleFullscreen()" type="button" class="p-1 text-slate-300 hover:text-vttu-yellow text-xs transition-colors" title="Toàn màn hình">
                        <i class="fas" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                    </button>
                </div>

                <div id="pdf-render-container"
                     class="w-full h-full overflow-y-auto flex flex-col items-center bg-black/40 pt-16 pb-6 px-4 gap-8">

                    {{-- Loading spinner --}}
                    <div id="pdf-loading" class="flex flex-col items-center justify-center min-h-full">
                        <div class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-[10px] font-bold uppercase tracking-widest mt-4 text-slate-400">{{ __('Đang chuẩn bị tài liệu...') }}</p>
                    </div>
                </div>

                <div x-show="isFullscreen" class="absolute bottom-6 right-6 z-[10000]" x-transition>
                    <button @click="toggleFullscreen()"
                            class="w-12 h-12 flex items-center justify-center bg-vttu-red text-white rounded-full shadow-2xl hover:bg-vttu-dark active:scale-90 transition-all border-2 border-white/20"
                            title="{{ __('Thu nhỏ') }}">
                        <i data-lucide="minimize" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        @endif
    @endif

{{-- Hidden config cho JS --}}
<input type="hidden" id="pdf-data"
       data-stream-url="{{ route('site.digital-resources.stream', $resource->id) }}"
       data-key-url="{{ route('site.digital-resources.stream-key', $resource->id) }}"
       data-limit="{{ $pageLimit }}"
       data-mode="{{ $canDownload ? 'iframe' : 'secure' }}"
       data-authenticated="{{ auth()->check() ? '1' : '0' }}">

@if(!$resource->file_url)
    <div class="w-full p-8 flex flex-col items-center justify-center bg-muted/20 rounded-sm border border-border">
        <i data-lucide="file-warning" class="w-10 h-10 text-vttu-red mb-3"></i>
        <h3 class="text-sm font-bold text-foreground uppercase tracking-widest">{{ __('Không thể tải file PDF') }}</h3>
        <p class="text-xs text-muted-foreground mt-2">{{ __('Vui lòng kiểm tra lại đường dẫn tệp tin hoặc liên hệ quản trị viên.') }}</p>
    </div>
@endif
</div>

{{-- ===================================================
     CSS: 3 Layers stacked chính xác trên nhau
     Thứ tự từ dưới lên: Canvas → Text → Annotation
     =================================================== --}}
<style>
    /* Wrapper mỗi trang: relative container để các layer xếp chồng */
    .pdf-page-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 2.5rem;
        box-shadow: 0 20px 60px -10px rgba(0,0,0,0.7);
        background: #fff;
        line-height: 1;
    }

    /* Placeholder chờ render — cùng kích thước trang để giữ chỗ */
    .pdf-page-placeholder {
        background: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        margin-bottom: 2.5rem;
        box-shadow: 0 20px 60px -10px rgba(0,0,0,0.7);
    }

    /* ─── Progress bar ─── */
    #pdf-progress-bar {
        transition: width 0.3s ease;
    }

    /* ─── LAYER 1: CANVAS ─── */
    .pdf-page-wrapper canvas {
        display: block;
    }

    /* ─── LAYER 2: TEXT LAYER ─── */
    .pdf-page-wrapper .textLayer {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: auto;
        cursor: text;
    }

    .pdf-page-wrapper .textLayer span,
    .pdf-page-wrapper .textLayer br {
        color: transparent;
        position: absolute;
        white-space: pre;
        cursor: text;
        transform-origin: 0% 0%;
    }

    .pdf-page-wrapper .textLayer span::selection {
        background: rgba(180, 20, 20, 0.25);
        color: transparent;
    }

    /* ─── LAYER 3: ANNOTATION LAYER ─── */
    .pdf-page-wrapper .annotationLayer {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: auto;
    }

    .pdf-page-wrapper .annotationLayer .linkAnnotation > a {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        border: 2px solid transparent;
        border-radius: 2px;
        transition: border-color 0.15s;
    }

    .pdf-page-wrapper .annotationLayer .linkAnnotation > a:hover {
        border-color: rgba(59, 130, 246, 0.5);
        background: rgba(59, 130, 246, 0.08);
    }
</style>

{{-- ===================================================
     JavaScript: Progressive lazy rendering
     - Hiển thị progress bar khi tải file lớn
     - Render 3 trang đầu ngay lập tức
     - Các trang còn lại: dùng IntersectionObserver
       để render khi người dùng scroll đến (lazy)
     =================================================== --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {

    // ─── Đọc config ───────────────────────────────────────
    const pdfData    = document.getElementById('pdf-data');
    const mode       = pdfData.dataset.mode;
    const streamUrl  = pdfData.dataset.streamUrl;
    const keyUrl     = pdfData.dataset.keyUrl;
    const limit      = parseInt(pdfData.dataset.limit || '0');
    const isAuth     = pdfData.dataset.authenticated === '1';

    // Nếu là ảnh hoặc dùng iframe → dừng tại đây
    const isImage = {{ $isImage ? 'true' : 'false' }};
    if (isImage || mode === 'iframe') {
        if (window.lucide) lucide.createIcons();
        return;
    }

    // ─── Worker ───────────────────────────────────────────
    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const container = document.getElementById('pdf-render-container');
    const loading   = document.getElementById('pdf-loading');

    if (!streamUrl || !container) return;

    // ── Cập nhật loading UI với progress ──────────────────
    function setLoadingText(text, percent = null) {
        const textEl   = loading.querySelector('#pdf-loading-text');
        const barEl    = loading.querySelector('#pdf-progress-bar');
        const barWrap  = loading.querySelector('#pdf-progress-wrap');
        if (textEl) textEl.textContent = text;
        if (barEl && percent !== null) {
            barWrap.style.display = 'block';
            barEl.style.width = Math.min(percent, 100) + '%';
        }
    }

    // Thêm progress bar vào loading UI
    loading.innerHTML = `
        <div class="flex flex-col items-center gap-4 text-center">
            <div class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
            <p id="pdf-loading-text" class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Đang kết nối...</p>
            <div id="pdf-progress-wrap" class="hidden w-48 bg-slate-700 rounded-full h-1.5 overflow-hidden">
                <div id="pdf-progress-bar" class="h-full bg-primary rounded-full" style="width:0%"></div>
            </div>
        </div>
    `;

    try {
        // ══════════════════════════════════════════════════
        // BƯỚC 1: Fetch encryption key (cả guest lẫn user đã login đều cần)
        // ══════════════════════════════════════════════════
        let decryptKey = null;
        if (keyUrl) {
            try {
                const keyRes = await fetch(keyUrl, { credentials: 'include' });
                if (keyRes.ok) {
                    const { key } = await keyRes.json();
                    decryptKey = base64ToBytes(key);
                }
            } catch (e) {
                console.warn('[PDF Viewer] Could not fetch key:', e.message);
            }
        }

        // ══════════════════════════════════════════════════
        // BƯỚC 2: Stream download với progress bar
        //   Dùng ReadableStream để hiển thị % tải về
        // ══════════════════════════════════════════════════
        setLoadingText('Đang tải tài liệu...', 0);

        const streamRes = await fetch(streamUrl, { credentials: 'include' });
        if (!streamRes.ok) throw new Error('Không thể tải tài liệu (HTTP ' + streamRes.status + ')');

        const contentLength = streamRes.headers.get('Content-Length');
        const totalBytes    = contentLength ? parseInt(contentLength) : 0;

        let receivedBytes = 0;
        const chunks = [];

        const reader = streamRes.body.getReader();
        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            chunks.push(value);
            receivedBytes += value.length;
            if (totalBytes > 0) {
                const pct = Math.round((receivedBytes / totalBytes) * 100);
                const mb  = (receivedBytes / 1048576).toFixed(1);
                setLoadingText(`Đang tải... ${mb} MB (${pct}%)`, pct);
            } else {
                const mb = (receivedBytes / 1048576).toFixed(1);
                setLoadingText(`Đang tải... ${mb} MB`, null);
            }
        }

        // Ghép các chunk thành 1 Uint8Array
        setLoadingText('Đang xử lý tài liệu...', 100);
        const rawBuf = new Uint8Array(receivedBytes);
        let offset = 0;
        for (const chunk of chunks) {
            rawBuf.set(chunk, offset);
            offset += chunk.length;
        }

        // ══════════════════════════════════════════════════
        // BƯỚC 3: Giải mã XOR (nếu có key)
        // ══════════════════════════════════════════════════
        const pdfBytes = decryptKey ? xorCipherInPlace(rawBuf, decryptKey) : rawBuf;

        // ══════════════════════════════════════════════════
        // BƯỚC 4: PDF.js load từ Uint8Array
        // ══════════════════════════════════════════════════
        setLoadingText('Đang mở tài liệu...', 100);
        const loadTask = pdfjsLib.getDocument({ data: pdfBytes });
        const pdf      = await loadTask.promise;

        loading.style.display = 'none';

        const totalPages    = pdf.numPages;
        const pagesToRender = limit > 0 ? Math.min(limit, totalPages) : totalPages;

        // ══════════════════════════════════════════════════
        // Cấu hình Floating Toolbar (Trang / Tìm chuyển trang / Zoom)
        // ══════════════════════════════════════════════════
        const totalDisplayEl = document.getElementById('pdf-total-pages-display');
        const jumpInputEl    = document.getElementById('pdf-page-jump-input');
        const jumpBtnEl      = document.getElementById('pdf-page-jump-btn');
        const zoomInBtn      = document.getElementById('pdf-zoom-in');
        const zoomOutBtn     = document.getElementById('pdf-zoom-out');
        const zoomValEl      = document.getElementById('pdf-zoom-val');

        if (totalDisplayEl) totalDisplayEl.textContent = pagesToRender;
        if (jumpInputEl) jumpInputEl.max = pagesToRender;

        function performJump() {
            if (!jumpInputEl) return;
            const targetP = parseInt(jumpInputEl.value);
            if (!targetP || targetP < 1 || targetP > pagesToRender) return;
            const targetEl = container.querySelector(`[data-page="${targetP}"]`);
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        if (jumpBtnEl) jumpBtnEl.onclick = performJump;
        if (jumpInputEl) {
            jumpInputEl.onkeyup = (e) => {
                if (e.key === 'Enter') performJump();
            };
        }

        window.activePageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const pNum = entry.target.getAttribute('data-page');
                    if (pNum && jumpInputEl && document.activeElement !== jumpInputEl) {
                        jumpInputEl.value = pNum;
                    }
                }
            });
        }, { root: container, threshold: 0.5 });

        let zoomScale = 1.0;
        if (zoomInBtn && zoomOutBtn && zoomValEl) {
            zoomInBtn.onclick = () => {
                if (zoomScale >= 2.0) return;
                zoomScale += 0.15;
                zoomValEl.textContent = Math.round(zoomScale * 100) + '%';
                container.querySelectorAll('.pdf-page-wrapper').forEach(w => {
                    w.style.transform = `scale(${zoomScale})`;
                    w.style.transformOrigin = 'top center';
                });
            };
            zoomOutBtn.onclick = () => {
                if (zoomScale <= 0.6) return;
                zoomScale -= 0.15;
                zoomValEl.textContent = Math.round(zoomScale * 100) + '%';
                container.querySelectorAll('.pdf-page-wrapper').forEach(w => {
                    w.style.transform = `scale(${zoomScale})`;
                    w.style.transformOrigin = 'top center';
                });
            };
        }

        // ══════════════════════════════════════════════════
        // BƯỚC 5: Tính kích thước 1 trang để làm placeholder
        // ══════════════════════════════════════════════════
        const firstPage       = await pdf.getPage(1);
        const baseVP          = firstPage.getViewport({ scale: 1 });
        const containerWidth  = container.clientWidth - 32;
        const isMobile        = window.innerWidth < 768;
        const fitRatio        = isMobile ? 0.95 : 0.72;
        const targetWidth     = containerWidth * fitRatio;
        const scale           = targetWidth / baseVP.width;
        const referenceVP     = firstPage.getViewport({ scale });
        const pageW           = Math.round(referenceVP.width);
        const pageH           = Math.round(referenceVP.height);

        // ══════════════════════════════════════════════════
        // BƯỚC 6: Tạo placeholder cho tất cả trang
        //   3 trang đầu: render ngay
        //   Còn lại: render lazy khi scroll đến
        // ══════════════════════════════════════════════════
        const EAGER_PAGES = 3; // số trang render ngay lập tức
        const renderQueue = new Map(); // pageNum -> placeholder element

        for (let pageNum = 1; pageNum <= pagesToRender; pageNum++) {
            if (pageNum <= EAGER_PAGES) {
                // Render ngay
                await renderPage(pdf, pageNum, scale);
            } else {
                // Tạo placeholder giữ chỗ
                const placeholder = document.createElement('div');
                placeholder.className = 'pdf-page-placeholder';
                placeholder.style.width  = pageW + 'px';
                placeholder.style.height = pageH + 'px';
                placeholder.dataset.page = pageNum;
                placeholder.textContent  = 'Trang ' + pageNum;
                container.appendChild(placeholder);
                renderQueue.set(pageNum, placeholder);
            }
        }

        // ══════════════════════════════════════════════════
        // BƯỚC 7: IntersectionObserver — render khi scroll đến
        // ══════════════════════════════════════════════════
        if (renderQueue.size > 0) {
            const observer = new IntersectionObserver(async (entries) => {
                for (const entry of entries) {
                    if (!entry.isIntersecting) continue;
                    const pNum = parseInt(entry.target.dataset.page);
                    if (!renderQueue.has(pNum)) continue;

                    observer.unobserve(entry.target);
                    renderQueue.delete(pNum);

                    const placeholder = entry.target;
                    placeholder.textContent = '';
                    // Spinner nhỏ trong placeholder
                    const spin = document.createElement('div');
                    spin.className = 'w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin';
                    placeholder.appendChild(spin);

                    // Render rồi thay thế placeholder
                    const wrapper = await renderPage(pdf, pNum, scale, true);
                    container.replaceChild(wrapper, placeholder);
                }
            }, {
                root: container,
                rootMargin: '300px', // bắt đầu render trước 300px
                threshold: 0
            });

            renderQueue.forEach((placeholder) => observer.observe(placeholder));
        }

        // ══════════════════════════════════════════════════
        // BƯỚC 8: Card giới hạn trang
        // ══════════════════════════════════════════════════
        if (limit > 0 && totalPages > limit) {
            const card = document.createElement('div');
            card.className = 'w-full max-w-xl bg-slate-900 border-2 border-dashed border-primary/30 rounded-md p-8 text-center my-8 shadow-2xl';
            card.innerHTML = `
                <div class="inline-flex p-3 bg-primary/10 rounded-full text-primary mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="text-sm font-bold uppercase tracking-widest text-slate-100 mb-2">Bạn đã xem hết ${limit} trang cho phép</h3>
                <p class="text-[11px] text-slate-400 mb-6">Tài liệu có tổng cộng <b class="text-white">${totalPages}</b> trang.<br>Vui lòng đăng nhập với tài khoản giáo viên để xem toàn bộ.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="/login" class="px-6 py-2 bg-primary text-white rounded-sm text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-all">Đăng nhập ngay</a>
                    <a href="{{ route('site.digital-resources.show', $resource->id) }}" class="text-[10px] font-bold text-slate-400 hover:text-slate-200 transition-colors">← Quay lại chi tiết</a>
                </div>
            `;
            container.appendChild(card);
        }

    } catch (err) {
        console.error('[PDF Viewer Error]', err);
        if (loading) {
            loading.style.display = 'flex';
            loading.innerHTML = `
                <div class="flex flex-col items-center gap-3 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-vttu-red" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-200">Lỗi tải tài liệu</p>
                    <p class="text-[10px] text-slate-400 max-w-xs">${err.message}</p>
                </div>
            `;
        }
    }

    // ══════════════════════════════════════════════════════
    // renderPage: Tạo 3 layer chồng lên nhau cho 1 trang PDF
    // Trả về wrapper element (để lazy rendering dùng replaceChild)
    // ══════════════════════════════════════════════════════
    async function renderPage(pdf, pageNum, scale, returnOnly = false) {
        const page = await pdf.getPage(pageNum);

        const dpr      = window.devicePixelRatio || 1;
        const viewport = page.getViewport({ scale });
        const renderVP = page.getViewport({ scale: scale * dpr });

        // ── pageWrapper ──────────────────────────────────
        const wrapper = document.createElement('div');
        wrapper.className = 'pdf-page-wrapper';
        wrapper.setAttribute('data-page', pageNum);
        wrapper.style.width  = viewport.width  + 'px';
        wrapper.style.height = viewport.height + 'px';

        // ── LAYER 1: Canvas ──────────────────────────────
        const canvas = document.createElement('canvas');
        const ctx    = canvas.getContext('2d');
        canvas.width         = Math.floor(renderVP.width);
        canvas.height        = Math.floor(renderVP.height);
        canvas.style.width   = viewport.width  + 'px';
        canvas.style.height  = viewport.height + 'px';
        canvas.style.display = 'block';

        await page.render({ canvasContext: ctx, viewport: renderVP }).promise;
        wrapper.appendChild(canvas);

        // ── LAYER 2: Text Layer ──────────────────────────
        const textDiv = document.createElement('div');
        textDiv.className    = 'textLayer';
        textDiv.style.width  = viewport.width  + 'px';
        textDiv.style.height = viewport.height + 'px';
        wrapper.appendChild(textDiv);

        const textContent = await page.getTextContent();
        const renderTask  = pdfjsLib.renderTextLayer({
            textContentSource: textContent,
            container:         textDiv,
            viewport:          viewport,
            textDivs:          [],
        });
        await renderTask.promise;

        // ── LAYER 3: Annotation Layer ────────────────────
        const annotDiv = document.createElement('div');
        annotDiv.className    = 'annotationLayer';
        annotDiv.style.width  = viewport.width  + 'px';
        annotDiv.style.height = viewport.height + 'px';
        wrapper.appendChild(annotDiv);

        const annotations = await page.getAnnotations();
        if (annotations.length > 0) {
            try {
                const linkService = {
                    getDestinationHash: () => '',
                    getAnchorUrl:       () => '',
                    setHash:            () => {},
                    navigateTo:         () => {},
                    scrollPageIntoView: ({ pageNumber }) => {
                        const target = document.querySelector(`.pdf-page-wrapper[data-page="${pageNumber}"]`);
                        if (target) target.scrollIntoView({ behavior: 'smooth' });
                    },
                };

                // PDF.js 3.x: instance-based API
                const annotLayer = new pdfjsLib.AnnotationLayer({
                    div:      annotDiv,
                    page:     page,
                    viewport: viewport.clone({ dontFlip: true }),
                    linkService,
                    downloadManager: null,
                    annotationCanvasMap: null,
                });
                await annotLayer.render({
                    annotations,
                    viewport:         viewport.clone({ dontFlip: true }),
                    linkService,
                    downloadManager:  null,
                    renderForms:      false,
                    imageResourcesPath: '',
                    enableScripting:  false,
                    hasJSActionsPromise: Promise.resolve(false),
                    fieldObjectsPromise: Promise.resolve(null),
                    annotationCanvasMap: null,
                });
            } catch (annotErr) {
                // Annotation layer không critical — bỏ qua nếu lỗi
                console.warn('[PDF Viewer] Annotation layer skipped:', annotErr.message);
            }
        }


        if (window.activePageObserver) window.activePageObserver.observe(wrapper);
        if (!returnOnly) container.appendChild(wrapper);
        return wrapper;
    }

    // ── Helpers ──────────────────────────────────────────
    function xorCipherInPlace(data, key) {
        const dl = data.length;
        const kl = key.length;
        if (kl === 0) return data;
        for (let i = 0; i < dl; i++) {
            data[i] ^= key[i % kl];
        }
        return data;
    }

    function base64ToBytes(b64) {
        const bin = atob(b64);
        const arr = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
        return arr;
    }

    // ── Bảo vệ UI ────────────────────────────────────────
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.addEventListener('keydown', e => {
        if (e.ctrlKey && ['p', 'P', 's', 'S'].includes(e.key)) {
            e.preventDefault();
            if (window.SwalHelper) {
                window.SwalHelper.showWarning('Cảnh báo!', 'Hệ thống đã chặn chức năng tải/in tài liệu để bảo vệ bản quyền.');
            }
        }
    });

    if (window.lucide) lucide.createIcons();

    @auth
        @php $patronGroup = auth()->user()->patronDetail?->patronGroup; @endphp
        console.log('[PDF Viewer] User:', {{ auth()->id() }},
                    '| Group:', '{{ $patronGroup?->name ?? "None" }} ({{ $patronGroup?->code ?? "None" }})',
                    '| Mode:', '{{ $canDownload ? "iframe" : "secure-encrypted" }}',
                    '| Page limit:', {{ $pageLimit }});
    @else
        console.log('[PDF Viewer] Guest | Mode: secure-encrypted | Page limit:', {{ $pageLimit }});
    @endauth
});
</script>
