@php
    $guideJson = json_decode($node->content_json ?? '{}', true) ?: [];
    $uploadedVideoUrl = $guideJson['uploaded_video_url'] ?? '';
    $embedVideoUrl = $guideJson['embed_video_url'] ?? ($guideJson['video_url'] ?? '');
    $videoSource = $guideJson['video_source'] ?? (!empty($uploadedVideoUrl) ? 'file' : (!empty($embedVideoUrl) ? 'url' : 'none'));
    $pdfUrl = ($videoSource === 'file') ? $uploadedVideoUrl : $embedVideoUrl;

    // Fallback if no PDF url is uploaded
    if (empty($pdfUrl) || str_contains($pdfUrl, 'fliphtml5.com')) {
        // Fallback to the latest PDF in storage if available, otherwise use a placeholder
        $pdfUrl = asset('storage/user-guides/ZiYV4XKRUaWlL62rjXNQr0owHE2r0HAvFM1wYkiU.pdf');
    }
@endphp

<div class="space-y-4" x-data="flipbookPlayer('{{ $pdfUrl }}')">
    <!-- Flipbook Player Container -->
    <div class="relative w-full h-[600px] md:h-[850px] lg:h-[700px] xl:h-[800px] bg-gradient-to-br from-stone-900 via-stone-950 to-neutral-900 rounded-xl overflow-hidden shadow-2xl border border-neutral-800 flex flex-col select-none"
         id="flipbookPlayerContainer"
         :class="{ 'fixed inset-0 z-[99999] h-screen rounded-none': isFullscreen }"
         @keydown.left.window="prevPage"
         @keydown.right.window="nextPage"
         @keydown.escape.window="if(isFullscreen) isFullscreen = false">

        <!-- Loading overlay -->
        <div x-show="loading" class="absolute inset-0 z-50 bg-black/75 backdrop-blur-sm flex flex-col items-center justify-center text-white gap-3">
            <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs font-bold uppercase tracking-wider text-neutral-400">Đang tải và chuẩn bị trang sách...</p>
            <div class="w-48 h-1 bg-neutral-800 rounded-full overflow-hidden mt-1">
                <div class="h-full bg-primary transition-all duration-300" :style="`width: ${loadingProgress}%`"></div>
            </div>
        </div>

        <!-- Canvas Book Display -->
        <div class="flex-1 w-full relative overflow-hidden p-4 flex items-center justify-center" id="bookContainer">
            <div class="relative flex items-center justify-center transition-transform duration-300 origin-center"
                 :style="`transform: scale(${zoom});`"
                 id="bookViewport">
                
                <!-- PageFlip element -->
                <div id="book" x-ignore class="opacity-0 transition-opacity duration-500 shadow-2xl" style="display: none;">
                    <!-- Pages will be dynamically injected here -->
                </div>

            </div>
        </div>

        <!-- Bottom Controls Bar (Glassmorphic) -->
        <div class="bg-black/90 border-t border-neutral-800 p-3 flex flex-col md:flex-row items-center justify-between gap-3 text-neutral-300 z-40">
            <!-- Left Side controls: Zoom & Sound -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1 bg-neutral-900/80 rounded-md p-1 border border-neutral-800">
                    <button @click="zoomOut" class="p-1.5 hover:bg-neutral-800 rounded transition-colors text-neutral-400 hover:text-white" title="Thu nhỏ">
                        <i data-lucide="zoom-out" class="w-4 h-4"></i>
                    </button>
                    <span class="text-xs font-mono font-bold w-12 text-center" x-text="`${Math.round(zoom * 100)}%`"></span>
                    <button @click="zoomIn" class="p-1.5 hover:bg-neutral-800 rounded transition-colors text-neutral-400 hover:text-white" title="Phóng to">
                        <i data-lucide="zoom-in" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Autoplay -->
                <button @click="toggleAutoplay" 
                        class="p-1.5 rounded-md border transition-colors flex items-center gap-1.5 text-xs font-semibold"
                        :class="autoplay ? 'bg-primary/20 border-primary text-primary hover:bg-primary/30' : 'bg-neutral-900 border-neutral-800 text-neutral-400 hover:text-white'">
                    <i data-lucide="play" class="w-3.5 h-3.5" x-show="!autoplay"></i>
                    <i data-lucide="pause" class="w-3.5 h-3.5" x-show="autoplay"></i>
                    <span>Tự chạy</span>
                </button>

                <!-- Sound FX Toggle -->
                <button @click="soundEnabled = !soundEnabled" 
                        class="p-1.5 rounded-md bg-neutral-900 border border-neutral-800 text-neutral-400 hover:text-white transition-colors"
                        :title="soundEnabled ? 'Tắt âm thanh' : 'Bật âm thanh'">
                    <i data-lucide="volume-2" class="w-4 h-4" x-show="soundEnabled"></i>
                    <i data-lucide="volume-x" class="w-4 h-4" x-show="!soundEnabled"></i>
                </button>
            </div>

            <!-- Mid controls: Navigation -->
            <div class="flex items-center gap-2">
                <button @click="firstPage" :disabled="currentPage === 1" class="p-1.5 bg-neutral-900 hover:bg-neutral-800 disabled:opacity-40 disabled:hover:bg-neutral-900 rounded-md border border-neutral-800 transition-colors" title="Trang đầu">
                    <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                </button>
                <button @click="prevPage" :disabled="currentPage === 1" class="p-1.5 bg-neutral-900 hover:bg-neutral-800 disabled:opacity-40 disabled:hover:bg-neutral-900 rounded-md border border-neutral-800 transition-colors" title="Trang trước">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>

                <div class="flex items-center gap-1 bg-neutral-900/80 rounded-md px-2.5 py-1 border border-neutral-800 text-xs">
                    <span class="font-semibold">Trang</span>
                    <input type="number" 
                           x-model.number.lazy="pageInput" 
                           @change="goToPage(pageInput)"
                           class="w-10 bg-neutral-950 border border-neutral-800 text-center rounded text-white py-0.5 focus:outline-none focus:border-primary text-xs font-bold" 
                           min="1" :max="numPages">
                    <span class="text-neutral-500">/</span>
                    <span class="font-bold text-neutral-400" x-text="numPages"></span>
                </div>

                <button @click="nextPage" :disabled="currentPage >= numPages" class="p-1.5 bg-neutral-900 hover:bg-neutral-800 disabled:opacity-40 disabled:hover:bg-neutral-900 rounded-md border border-neutral-800 transition-colors" title="Trang sau">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
                <button @click="lastPage" :disabled="currentPage >= numPages" class="p-1.5 bg-neutral-900 hover:bg-neutral-800 disabled:opacity-40 disabled:hover:bg-neutral-900 rounded-md border border-neutral-800 transition-colors" title="Trang cuối">
                    <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Right controls: Page Mode & Fullscreen -->
            <div class="flex items-center gap-3">
                <button @click="toggleFullscreen" class="p-1.5 bg-neutral-900 hover:bg-neutral-800 rounded-md border border-neutral-800 text-neutral-400 hover:text-white transition-colors" title="Toàn màn hình">
                    <i data-lucide="maximize" class="w-4 h-4" x-show="!isFullscreen"></i>
                    <i data-lucide="minimize" class="w-4 h-4" x-show="isFullscreen"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>
<script>
    if (window.pdfjsLib) {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    function initFlipbookPlayer() {
        if (!window.Alpine) return;
        
        // Prevent duplicate registration
        if (window.Alpine.components && window.Alpine.components['flipbookPlayer']) return;

        window.Alpine.data('flipbookPlayer', (pdfUrl) => {
            let pdfDoc = null;
            return {
                pdfUrl: pdfUrl,
                numPages: 0,
                currentPage: 1,
                pageInput: 1,
                zoom: 1.5,
                isFullscreen: false,
                loading: true,
                loadingProgress: 0,
                autoplay: false,
                autoplayTimer: null,
                soundEnabled: true,
                pageFlip: null,
                
                // Layout dimensions
                pageWidth: 400,
                pageHeight: 560,
                bookHeight: 560,
                aspectRatio: 0.71, // width / height standard
                
                renderedPages: {}, // track page numbers that are rendered to avoid duplicates

                init() {
                    window.addEventListener('resize', () => {
                        this.resizeBook();
                    });
                    
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                        setTimeout(() => this.resizeBook(), 100);
                    });
                    
                    this.loadPdf();
                },

                async loadPdf() {
                    this.loading = true;
                    this.loadingProgress = 20;
                    try {
                        const loadingTask = pdfjsLib.getDocument({
                            url: this.pdfUrl,
                            cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                            cMapPacked: true,
                        });
                        
                        loadingTask.onProgress = (progress) => {
                            if (progress.total > 0) {
                                this.loadingProgress = Math.min(80, Math.round((progress.loaded / progress.total) * 100));
                            }
                        };

                        pdfDoc = await loadingTask.promise;
                        this.numPages = pdfDoc.numPages;
                        this.loadingProgress = 90;
                        
                        // Render initial layout dimensions from first page
                        const page = await pdfDoc.getPage(1);
                        const viewport = page.getViewport({ scale: 1.0 });
                        this.aspectRatio = viewport.width / viewport.height;
                        
                        this.resizeBook();
                        this.createPagesDOM();
                        
                        // Wait a tiny bit for elements to exist in DOM
                        setTimeout(() => {
                            this.initPageFlip();
                        }, 100);
                        
                    } catch (error) {
                        console.error('Error loading PDF flipbook:', error);
                        this.loading = false;
                    }
                },

                resizeBook() {
                    const container = document.getElementById('bookContainer');
                    if (!container) return;
                    
                    const containerWidth = container.clientWidth - 32;
                    const containerHeight = container.clientHeight - 32;
                    
                    // Detect screen aspect ratio
                    const screenAspectRatio = containerWidth / containerHeight;
                    const isSquareScreen = screenAspectRatio >= 0.8 && screenAspectRatio <= 1.2;
                    
                    let targetHeight, targetWidth;
                    
                    if (this.isFullscreen) {
                        targetHeight = window.innerHeight - 120;
                    } else {
                        targetHeight = Math.min(containerHeight, 680);
                    }
                    
                    // For square screens, prioritize width to maximize space utilization
                    if (isSquareScreen) {
                        // Calculate based on available width first
                        targetWidth = containerWidth;
                        targetHeight = targetWidth / (this.aspectRatio * 2);
                        
                        // If height overflows, scale down
                        if (targetHeight > (this.isFullscreen ? window.innerHeight - 120 : Math.min(containerHeight, 680))) {
                            const maxAllowedHeight = this.isFullscreen ? window.innerHeight - 120 : Math.min(containerHeight, 680);
                            const scaleFactor = maxAllowedHeight / targetHeight;
                            targetHeight = maxAllowedHeight;
                            targetWidth = targetWidth * scaleFactor;
                        }
                    } else {
                        // Standard logic for non-square screens
                        targetWidth = targetHeight * this.aspectRatio * 2;
                        
                        // If it overflows the width, scale down
                        if (targetWidth > containerWidth) {
                            const scaleFactor = containerWidth / targetWidth;
                            targetWidth = containerWidth;
                            targetHeight = targetHeight * scaleFactor;
                        }
                    }
                    
                    // Add small buffer to prevent overflow during page flip animations
                    const buffer = 10;
                    this.pageHeight = Math.round(targetHeight - buffer);
                    this.pageWidth = Math.round((targetWidth - buffer) / 2);
                    this.bookHeight = this.pageHeight;
                    
                    if (this.pageFlip) {
                        this.pageFlip.update();
                    }
                },

                createPagesDOM() {
                    const bookEl = document.getElementById('book');
                    if (!bookEl) return;
                    
                    bookEl.innerHTML = '';
                    bookEl.style.display = 'block';
                    
                    for (let i = 1; i <= this.numPages; i++) {
                        const pageDiv = document.createElement('div');
                        pageDiv.className = 'page bg-white relative overflow-hidden shadow-inner';
                        // Hard covers for first and last page
                        pageDiv.setAttribute('data-density', i === 1 || i === this.numPages ? 'hard' : 'soft');
                        pageDiv.innerHTML = `<div id="page-container-${i}" class="w-full h-full relative"></div>`;
                        bookEl.appendChild(pageDiv);
                    }
                },

                initPageFlip() {
                    const bookEl = document.getElementById('book');
                    if (!bookEl || typeof St === 'undefined') return;

                    const isMobile = window.innerWidth < 768;
                    this.pageFlip = new St.PageFlip(bookEl, {
                        width: this.pageWidth,
                        height: this.pageHeight,
                        size: "stretch",
                        minWidth: 280,
                        maxWidth: 1000,
                        minHeight: 380,
                        maxHeight: 1350,
                        drawShadow: true,
                        showCover: !isMobile, // Only show cover alone on desktop, on mobile cover is just first page of sequence
                        usePortrait: isMobile, // If true, switches to single-page on narrow screen. If false, stays in double-page mode!
                        flippingTime: 800
                    });

                    this.pageFlip.loadFromHTML(bookEl.querySelectorAll('.page'));
                    bookEl.classList.remove('opacity-0');
                    this.loading = false;

                    // Bind page flip event
                    this.pageFlip.on('flip', (e) => {
                        this.currentPage = e.data + 1;
                        this.pageInput = this.currentPage;
                        this.playPaperFlipSound();
                        this.renderPagesAround();
                    });

                    // Trigger initial render
                    this.renderPagesAround();
                },

                async renderPagesAround() {
                    const p = this.currentPage;
                    const pagesToRender = new Set();
                    
                    // Current and immediate next spread
                    pagesToRender.add(p);
                    if (p > 1) pagesToRender.add(p - 1);
                    if (p < this.numPages) pagesToRender.add(p + 1);
                    if (p + 2 <= this.numPages) pagesToRender.add(p + 2);
                    
                    // Buffer: 6 pages forward
                    for (let i = 1; i <= 6; i++) {
                        if (p + i <= this.numPages) pagesToRender.add(p + i);
                    }
                    
                    // Buffer: 3 pages backward
                    for (let i = 1; i <= 3; i++) {
                        if (p - i >= 1) pagesToRender.add(p - i);
                    }
                    
                    // Render all in parallel
                    await Promise.all(Array.from(pagesToRender).map(pageNum => this.renderPageCanvas(pageNum)));
                },

                async renderPageCanvas(pageNum) {
                    const containerId = `page-container-${pageNum}`;
                    let container = document.getElementById(containerId);
                    if (!container) return;

                    // Check if already rendered
                    if (container.getAttribute('data-rendered') === 'true') {
                        const existingCanvas = container.querySelector('canvas');
                        if (existingCanvas && Math.abs(existingCanvas.width - this.pageWidth * 1.5) <= 20) {
                            return;
                        }
                    }

                    container.setAttribute('data-rendered', 'true');
                    container.innerHTML = '<div class="absolute inset-0 flex items-center justify-center bg-white"><div class="w-6 h-6 border-2 border-neutral-300 border-t-primary rounded-full animate-spin"></div></div>';

                    try {
                        const page = await pdfDoc.getPage(pageNum);
                        const canvas = document.createElement('canvas');
                        canvas.className = 'w-full h-full object-contain';
                        
                        // Render at high resolution (1.5x scale) for crispy sharp text
                        const viewport = page.getViewport({ scale: (this.pageWidth / page.getViewport({scale:1.0}).width) * 1.5 });
                        
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        
                        const ctx = canvas.getContext('2d', { alpha: false });
                        container.innerHTML = '';
                        container.appendChild(canvas);
                        
                        await page.render({ canvasContext: ctx, viewport: viewport }).promise;
                    } catch (err) {
                        console.error('Error rendering page:', pageNum, err);
                        container.innerHTML = '<div class="absolute inset-0 flex items-center justify-center text-red-500 text-xs">Lỗi tải trang</div>';
                    }
                },

                nextPage() {
                    if (this.pageFlip) {
                        this.pageFlip.flipNext();
                    }
                },

                prevPage() {
                    if (this.pageFlip) {
                        this.pageFlip.flipPrev();
                    }
                },

                firstPage() {
                    if (this.pageFlip) {
                        this.pageFlip.flip(0);
                    }
                },

                lastPage() {
                    if (this.pageFlip) {
                        this.pageFlip.flip(this.numPages - 1);
                    }
                },

                goToPage(p) {
                    if (this.pageFlip && p >= 1 && p <= this.numPages) {
                        this.pageFlip.flip(p - 1);
                    }
                },

                zoomIn() {
                    this.zoom = Math.min(3.0, this.zoom + 0.25);
                },

                zoomOut() {
                    this.zoom = Math.max(1.0, this.zoom - 0.25);
                },

                toggleFullscreen() {
                    const container = document.getElementById('flipbookPlayerContainer');
                    if (!container) return;

                    if (!document.fullscreenElement) {
                        container.requestFullscreen().then(() => {
                            this.isFullscreen = true;
                            setTimeout(() => this.resizeBook(), 150);
                        }).catch(err => {
                            console.error('Error enabling fullscreen:', err);
                            this.isFullscreen = !this.isFullscreen;
                            setTimeout(() => this.resizeBook(), 150);
                        });
                    } else {
                        document.exitFullscreen().then(() => {
                            this.isFullscreen = false;
                            setTimeout(() => this.resizeBook(), 150);
                        });
                    }
                },

                toggleAutoplay() {
                    this.autoplay = !this.autoplay;
                    if (this.autoplay) {
                        this.startAutoplay();
                    } else {
                        this.stopAutoplay();
                    }
                },

                startAutoplay() {
                    this.autoplayTimer = setInterval(() => {
                        this.nextPage();
                    }, 4000);
                },

                stopAutoplay() {
                    if (this.autoplayTimer) {
                        clearInterval(this.autoplayTimer);
                        this.autoplayTimer = null;
                    }
                    this.autoplay = false;
                },

                playPaperFlipSound() {
                    if (!this.soundEnabled) return;
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        if (!AudioContext) return;
                        const ctx = new AudioContext();
                        
                        const bufferSize = ctx.sampleRate * 0.35;
                        const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
                        const data = buffer.getChannelData(0);
                        
                        for (let i = 0; i < bufferSize; i++) {
                            data[i] = (Math.random() * 2 - 1) * Math.pow((bufferSize - i) / bufferSize, 2);
                        }
                        
                        const noise = ctx.createBufferSource();
                        noise.buffer = buffer;
                        
                        const filter = ctx.createBiquadFilter();
                        filter.type = 'bandpass';
                        filter.Q.value = 2.5;
                        filter.frequency.setValueAtTime(300, ctx.currentTime);
                        filter.frequency.exponentialRampToValueAtTime(1400, ctx.currentTime + 0.12);
                        filter.frequency.exponentialRampToValueAtTime(250, ctx.currentTime + 0.35);
                        
                        const gain = ctx.createGain();
                        gain.gain.setValueAtTime(0.06, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                        
                        noise.connect(filter);
                        filter.connect(gain);
                        gain.connect(ctx.destination);
                        
                        noise.start();
                    } catch (e) {
                        // blocked by browser autoplay policy
                    }
                }
            };
        });
    }

    if (window.Alpine) {
        initFlipbookPlayer();
    } else {
        document.addEventListener('alpine:init', initFlipbookPlayer);
    }
</script>
<style>
    /* Styling for the custom slider */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* PageFlip specific styling to prevent rendering glitches */
    #book {
        transform-origin: center center;
    }

    .page {
        background-color: white;
        box-shadow: inset 0 0 20px rgba(0,0,0,0.1);
    }

    /* Prevent scrollbars during page flip animations */
    #bookContainer {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
    }

    #bookContainer::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }

    /* Ensure book viewport doesn't overflow */
    #bookViewport {
        max-width: 100%;
        max-height: 100%;
    }
</style>
