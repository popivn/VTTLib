@extends('layouts.admin')

@section('title', __('Quản lý HDSD'))

@section('content')
@php
    $hasDbNodes = isset($subNodes) && $subNodes->count() > 0;
    $initialTab = $hasDbNodes ? $subNodes->first()->node_code : 'tra-cuu-tai-lieu-giay';
    $requestedTab = request('tab', $initialTab);
@endphp

<div class="space-y-6" x-data="{ 
    activeTab: (new URLSearchParams(window.location.search)).get('tab') || '{{ $requestedTab }}',
    switchTab(tabCode) {
        this.activeTab = tabCode;
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabCode);
        window.history.replaceState({}, '', url);
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-card border border-border p-4 rounded-xl shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-lg border border-primary/20">
                <i class="fas fa-book-open-reader"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-foreground uppercase tracking-wide">
                    {{ __('Quản lý Hướng Dẫn Sử Dụng (HDSD)') }}
                </h1>
                <p class="text-xs text-muted-foreground">
                    {{ __('Quản lý danh mục, thông tin hướng dẫn và chọn nguồn Video hiển thị') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Bar -->
    <div class="bg-card border border-border rounded-xl p-3 shadow-xs">
        <div class="flex flex-wrap items-center gap-2">
            @if($hasDbNodes)
                @foreach($subNodes as $node)
                    <button type="button"
                            @click="switchTab('{{ $node->node_code }}')"
                            :class="activeTab === '{{ $node->node_code }}' ? 'bg-primary text-primary-foreground shadow-md ring-2 ring-primary/30 font-bold scale-[1.02]' : 'bg-muted/50 hover:bg-muted text-muted-foreground hover:text-foreground font-medium border border-border/50'"
                            class="px-3.5 py-2 rounded-lg text-xs flex items-center gap-2 transition-all duration-200 cursor-pointer">
                        <i class="{{ $node->icon ?: 'fas fa-file-alt' }} text-xs"></i>
                        <span>{{ $node->display_name ?: $node->node_name }}</span>
                    </button>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Tab Content Panes -->
    @if($hasDbNodes)
        @foreach($subNodes as $node)
            @php
                $json = json_decode($node->content_json ?? '{}', true) ?: [];
                $title = $json['title'] ?? $node->display_name ?? $node->node_name;
                $conditionTitle = $json['condition_title'] ?? 'Điều kiện / Lưu ý:';
                $conditionDesc = $json['condition_desc'] ?? '';
                
                // Parse sections (dynamic array or fallback to legacy steps/section2_steps)
                $sections = $json['sections'] ?? [];
                if (empty($sections)) {
                    $legacySteps = $json['steps'] ?? [];
                    $legacySec2Steps = $json['section2_steps'] ?? [];
                    if (!empty($legacySteps)) {
                        $sections[] = [
                            'title' => $json['steps_title'] ?? 'Các bước thực hiện:',
                            'steps' => $legacySteps
                        ];
                    }
                    if (!empty($legacySec2Steps)) {
                        $sections[] = [
                            'title' => $json['section2_title'] ?? 'Các bước thực hiện Phần 2:',
                            'steps' => $legacySec2Steps
                        ];
                    }
                }
                if (empty($sections)) {
                    $sections[] = [
                        'title' => 'Các bước thực hiện:',
                        'steps' => [['title' => '', 'content' => '']]
                    ];
                }

                $videoTitle = $json['video_title'] ?? 'Bạn đọc vui lòng xem video hướng dẫn dưới đây:';
                $uploadedVideoUrl = $json['uploaded_video_url'] ?? '';
                $embedVideoUrl = $json['embed_video_url'] ?? ($json['video_url'] ?? '');
                $videoSource = $json['video_source'] ?? (!empty($uploadedVideoUrl) ? 'file' : 'url');
            @endphp
            <div x-show="activeTab === '{{ $node->node_code }}'" x-cloak class="space-y-4">
                <form action="{{ route('admin.user-guides.update', ['siteNode' => $node->id, 'tab' => $node->node_code]) }}" method="POST" enctype="multipart/form-data" class="space-y-4" 
                      @submit.prevent="submitForm($event)"
                      x-data="userGuideEditor('{{ $node->node_code }}', '{{ $videoSource }}', '{{ addslashes($uploadedVideoUrl) }}', '{{ addslashes($embedVideoUrl) }}', {{ json_encode($sections) }}, '{{ addslashes($videoTitle) }}')">
                    @csrf
                    <div class="grid grid-cols-1 transition-all duration-300" :class="showPreview ? 'lg:grid-cols-2 gap-6' : 'lg:grid-cols-1 gap-0'">
                        <!-- Form Left Column: Settings -->
                        <div class="bg-card border border-border rounded-xl p-5 shadow-xs space-y-5">
                            <div class="flex items-center justify-between border-b border-border pb-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-2">
                                    <i class="{{ $node->icon ?: 'fas fa-edit' }}"></i>
                                    {{ __('Cấu hình nội dung') }}: {{ $node->node_name }}
                                </h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-muted text-muted-foreground border border-border">
                                        {{ $node->node_code }}
                                    </span>
                                    <button type="button" @click="showPreview = !showPreview" 
                                            class="px-2.5 py-1 rounded-md text-[11px] font-bold border transition-all flex items-center gap-1.5"
                                            :class="showPreview ? 'bg-muted text-muted-foreground border-border hover:bg-muted/80' : 'bg-primary/10 text-primary border-primary/20 hover:bg-primary/20'">
                                        <i class="fas" :class="showPreview ? 'fa-eye-slash' : 'fa-desktop'"></i>
                                        <span x-text="showPreview ? 'Ẩn xem trước' : 'Hiện xem trước'"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- 1. General Header & Notes -->
                                <div class="space-y-3">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tiêu đề bài Hướng dẫn') }} *</label>
                                        <input type="text" name="title" value="{{ $title }}" required class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none font-medium">
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tiêu đề Lưu ý / Điều kiện') }}</label>
                                            <input type="text" name="condition_title" value="{{ $conditionTitle }}" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Nội dung Lưu ý (Mô tả)') }}</label>
                                            <input type="text" name="condition_desc" value="{{ $conditionDesc }}" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none">
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Dynamic Multiple Sections Container -->
                                <div class="space-y-5 pt-3 border-t border-border">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[11px] font-black text-primary uppercase tracking-wider flex items-center gap-2">
                                            <i class="fas fa-layer-group"></i>
                                            {{ __('Danh sách các Khối (Sections) Hướng Dẫn') }}
                                        </label>
                                        <button type="button" @click="addSection()" class="px-3 py-1.5 bg-primary text-primary-foreground text-xs font-bold rounded-lg shadow-sm hover:bg-primary/90 flex items-center gap-1.5 transition-all">
                                            <i class="fas fa-plus text-[10px]"></i>
                                            {{ __('Thêm Khối (Section) mới') }}
                                        </button>
                                    </div>

                                    <div class="space-y-6">
                                        <template x-for="(section, sIndex) in sectionsList" :key="sIndex">
                                            <div class="p-4 bg-muted/30 border border-border rounded-xl space-y-4 relative shadow-xs" x-data="{ isSecCollapsed: false }">
                                                <!-- Section Header -->
                                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-border/80 pb-3">
                                                    <div class="flex items-center gap-2 cursor-pointer select-none" @click="isSecCollapsed = !isSecCollapsed">
                                                        <button type="button" class="w-6 h-6 rounded bg-primary/10 text-primary flex items-center justify-center text-xs transition-transform duration-200" :class="isSecCollapsed ? '-rotate-90' : ''">
                                                            <i class="fas fa-chevron-down"></i>
                                                        </button>
                                                        <span class="px-2.5 py-0.5 rounded text-[11px] font-black bg-primary text-primary-foreground">
                                                            <span>Khối</span>
                                                            <span x-text="sIndex + 1"></span>
                                                        </span>
                                                        <span class="text-xs font-bold text-foreground truncate max-w-[200px] sm:max-w-[300px]" x-text="section.title || ('Khối ' + (sIndex + 1))"></span>
                                                    </div>

                                                    <div class="flex items-center gap-2">
                                                        <button type="button" @click="addStepToSection(sIndex)" class="px-2.5 py-1 bg-primary/10 hover:bg-primary/20 text-primary text-[11px] font-bold rounded-md border border-primary/20 flex items-center gap-1 transition-all">
                                                            <i class="fas fa-plus text-[9px]"></i>
                                                            {{ __('Thêm bước vào khối này') }}
                                                        </button>
                                                        <button type="button" @click="removeSection(sIndex)" x-show="sectionsList.length > 1" class="px-2 py-1 text-red-500 hover:bg-red-500/10 rounded text-xs transition-colors" title="Xóa khối này">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Section Content -->
                                                <div x-show="!isSecCollapsed" x-collapse class="space-y-4">
                                                    <div class="space-y-1.5">
                                                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tiêu đề Khối này') }}</label>
                                                        <input type="text" :name="'sections[' + sIndex + '][title]'" x-model="section.title" placeholder="Ví dụ: 1. Tìm kiếm sách hoặc 2. Trả sách tự động" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground font-bold focus:ring-1 focus:ring-primary outline-none">
                                                    </div>

                                                    <!-- Steps within Section -->
                                                    <div class="space-y-3 pl-2 sm:pl-3 border-l-2 border-primary/20">
                                                        <template x-for="(step, stIndex) in section.steps" :key="stIndex">
                                                            <div class="p-3 bg-background border border-border rounded-lg space-y-3 shadow-2xs" x-data="{ isStepCollapsed: false }">
                                                                <div class="flex items-center justify-between border-b border-border/40 pb-2">
                                                                    <div class="flex items-center gap-2 cursor-pointer select-none" @click="isStepCollapsed = !isStepCollapsed">
                                                                        <button type="button" class="text-muted-foreground text-[10px] transition-transform duration-200" :class="isStepCollapsed ? '-rotate-90' : ''">
                                                                            <i class="fas fa-chevron-down"></i>
                                                                        </button>
                                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-muted text-foreground border border-border">
                                                                            <span>Bước</span>
                                                                            <span x-text="stIndex + 1"></span>
                                                                        </span>
                                                                        <span class="text-xs font-bold text-foreground truncate max-w-[180px] sm:max-w-[280px]" x-text="step.title || ('Bước ' + (stIndex + 1))"></span>
                                                                    </div>

                                                                    <div class="flex items-center gap-2">
                                                                        <button type="button" @click="isStepCollapsed = !isStepCollapsed" class="text-[10px] text-muted-foreground hover:text-foreground font-medium">
                                                                            <span x-text="isStepCollapsed ? 'Mở rộng' : 'Thu gọn'"></span>
                                                                        </button>
                                                                        <button type="button" @click="removeStepFromSection(sIndex, stIndex)" x-show="section.steps.length > 1" class="text-muted-foreground hover:text-red-500 text-xs p-1 transition-colors" title="Xóa bước này">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>

                                                                <div x-show="!isStepCollapsed" class="space-y-3 pt-1">
                                                                    <div class="space-y-1">
                                                                        <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tên bước') }}</label>
                                                                        <input type="text" :name="'sections[' + sIndex + '][steps][' + stIndex + '][title]'" x-model="step.title" placeholder="Ví dụ: Nhập từ khóa tìm kiếm" class="w-full h-8 bg-background border border-border rounded-md px-3 text-xs text-foreground font-bold focus:ring-1 focus:ring-primary outline-none">
                                                                    </div>

                                                                    <div class="space-y-1.5">
                                                                        <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Nội dung bước (TinyMCE)') }}</label>
                                                                        <textarea :id="'editor_' + nodeCode + '_' + sIndex + '_' + stIndex" 
                                                                                  :name="'sections[' + sIndex + '][steps][' + stIndex + '][content]'" 
                                                                                  x-model="step.content" 
                                                                                  class="tinymce-editor w-full min-h-[140px] bg-background border border-border rounded-lg p-3 text-xs text-foreground"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- 3. Video Settings Section -->
                                <div class="space-y-3 pt-3 border-t border-border">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tiêu đề khung Video') }}</label>
                                        <input type="text" name="video_title" x-model="videoTitle" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] font-bold text-primary uppercase tracking-wide flex items-center gap-1.5">
                                            <i class="fas fa-file-video"></i>
                                            {{ __('Tùy chọn Nguồn Video / PDF Trình chiếu sử dụng') }}
                                        </label>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <label class="p-2.5 rounded-lg border transition-all flex items-center gap-2 cursor-pointer" :class="videoSource === 'file' ? 'border-primary bg-primary/5' : 'border-border bg-background'">
                                                <input type="radio" name="video_source" value="file" x-model="videoSource" class="w-3.5 h-3.5 text-primary">
                                                <span class="text-xs font-bold text-foreground">{{ __('File Video / PDF tải lên') }}</span>
                                            </label>
                                            <label class="p-2.5 rounded-lg border transition-all flex items-center gap-2 cursor-pointer" :class="videoSource === 'url' ? 'border-primary bg-primary/5' : 'border-border bg-background'">
                                                <input type="radio" name="video_source" value="url" x-model="videoSource" class="w-3.5 h-3.5 text-primary">
                                                <span class="text-xs font-bold text-foreground">{{ __('Link URL Canva / YT / Flipbook') }}</span>
                                            </label>
                                        </div>

                                        <div x-show="videoSource === 'file'" class="pt-1">
                                            <label class="flex items-center gap-2 px-3 h-9 bg-card border border-border border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-all group">
                                                <i class="fas fa-cloud-upload-alt text-muted-foreground group-hover:text-foreground text-xs"></i>
                                                <span class="text-[11px] text-muted-foreground group-hover:text-foreground truncate" x-text="uploadedFileName || 'Tải lên file Video / PDF (MP4, WEBM, PDF... Max 100MB)...'"></span>
                                                <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime,application/pdf" class="hidden" @change="uploadedFileName = $event.target.files[0]?.name; if($event.target.files[0]) newFilePreviewUrl = URL.createObjectURL($event.target.files[0])">
                                            </label>
                                            <p x-show="uploadedUrl" class="text-[10px] text-muted-foreground mt-1 flex items-center gap-1" x-cloak>
                                                <i class="fas fa-check-circle text-emerald-500"></i>
                                                {{ __('Đã có file tải lên:') }} <a :href="uploadedUrl" target="_blank" class="text-primary underline font-mono truncate max-w-[200px] inline-block align-bottom" x-text="uploadedUrl.split('/').pop()"></a>
                                            </p>
                                        </div>

                                        <div x-show="videoSource === 'url'" class="pt-1 space-y-1">
                                            <input type="url" name="embed_video_url" x-model="embedUrl" placeholder="https://www.youtube.com/embed/... hoặc https://online.fliphtml5.com/... hoặc Canva" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none font-mono">
                                            <p class="text-[10px] text-muted-foreground">
                                                {{ __('Nhập mã nhúng Embed hoặc link Youtube / Canva / Flipbook bài giảng.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-4 border-t border-border flex justify-end">
                                <button type="submit" 
                                        :disabled="isSaving"
                                        class="px-5 py-2 bg-primary text-primary-foreground font-bold text-xs rounded-lg shadow-md hover:bg-primary/90 transition-all flex items-center gap-2 cursor-pointer active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <template x-if="isSaving">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </template>
                                    <template x-if="!isSaving">
                                        <i class="fas fa-save"></i>
                                    </template>
                                    <span x-text="isSaving ? 'Đang lưu...' : '{{ __('Lưu thay đổi bài Hướng dẫn') }}'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Form Right Column: Live Interactive Preview -->
                        <div x-show="showPreview" x-transition class="bg-card border border-border rounded-xl p-5 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-border pb-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                                    <i class="fas fa-desktop text-primary"></i>
                                    {{ __('Xem trước Giao diện ngoài Trang chủ') }}
                                </h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 font-bold border border-emerald-500/20 uppercase tracking-wide">
                                        Live Preview
                                    </span>
                                    <button type="button" @click="showPreview = false" class="text-muted-foreground hover:text-foreground text-xs p-1 transition-colors" title="Đóng cột xem trước">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Render Container -->
                            <div class="border border-border rounded-lg p-4 bg-background space-y-5 shadow-inner">
                                <!-- Dynamic Render Sections in Preview -->
                                <template x-for="(sec, sIdx) in sectionsList" :key="sIdx">
                                    <div class="space-y-3" :class="sIdx > 0 ? 'pt-3 border-t border-border' : ''">
                                        <h4 class="font-bold text-foreground text-xs flex items-center gap-2 border-b border-border pb-2" x-show="sec.title">
                                            <i class="fas fa-info-circle text-vttu-red"></i>
                                            <span x-text="sec.title || ('Khối ' + (sIdx + 1))"></span>
                                        </h4>

                                        <div class="space-y-2">
                                            <template x-for="(st, stIdx) in sec.steps" :key="stIdx">
                                                <div x-show="st.title || st.content" class="flex gap-3 p-3 bg-card border border-border rounded-md shadow-2xs">
                                                    <div class="w-6 h-6 bg-vttu-red/10 text-vttu-red rounded flex items-center justify-center font-bold text-xs flex-shrink-0" x-text="stIdx + 1"></div>
                                                    <div class="space-y-0.5 flex-1 min-w-0">
                                                        <h5 class="font-bold text-foreground text-xs" x-text="st.title || 'Bước ' + (stIdx + 1)"></h5>
                                                        <div class="text-xs text-muted-foreground leading-relaxed break-words prose dark:prose-invert max-w-none" x-html="st.content || ''"></div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <!-- Video / PDF Preview -->
                                <template x-if="activePreviewUrl">
                                    <div class="space-y-2 pt-3 border-t border-border">
                                        <h4 class="font-bold text-foreground text-xs flex items-center gap-1.5" x-text="videoTitle || 'Video / Tài liệu trình chiếu:'"></h4>
                                        <div>
                                            <template x-if="activePreviewUrl.toLowerCase().includes('.pdf') || (uploadedFileName && uploadedFileName.toLowerCase().endsWith('.pdf'))">
                                                <div :id="'pdf-canvas-container-' + nodeCode" class="space-y-3 max-h-[600px] overflow-y-auto p-2 bg-muted/40 border border-border rounded-lg shadow-inner">
                                                    <div class="text-xs text-muted-foreground p-4 text-center">Đang nạp file PDF...</div>
                                                </div>
                                            </template>
                                            <template x-if="!(activePreviewUrl.toLowerCase().includes('.pdf') || (uploadedFileName && uploadedFileName.toLowerCase().endsWith('.pdf')))">
                                                <div class="relative w-full aspect-video rounded-md overflow-hidden border border-border shadow-md bg-black">
                                                    <template x-if="videoSource === 'file' || activePreviewUrl.includes('.mp4') || activePreviewUrl.includes('.webm') || activePreviewUrl.includes('blob:')">
                                                        <video :src="activePreviewUrl" controls class="w-full h-full object-contain"></video>
                                                    </template>
                                                    <template x-if="videoSource === 'url' && !(activePreviewUrl.includes('.mp4') || activePreviewUrl.includes('.webm') || activePreviewUrl.includes('blob:'))">
                                                        <iframe :src="activePreviewUrl" class="absolute inset-0 w-full h-full border-0" allowfullscreen allow="fullscreen"></iframe>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endforeach
    @endif
</div>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/{{ env('TinyEMC', 'no-api-key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    if (window.pdfjsLib) {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    async function renderPdfToCanvasList(url, containerId) {
        const container = document.getElementById(containerId);
        if (!container || !window.pdfjsLib) return;
        
        try {
            const loadingTask = window.pdfjsLib.getDocument({
                url: url,
                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                cMapPacked: true,
            });
            const pdf = await loadingTask.promise;
            container.innerHTML = '';
            
            // Render first 10 pages maximum for ultra fast response
            const pageLimit = Math.min(pdf.numPages, 15);
            for (let i = 1; i <= pageLimit; i++) {
                const page = await pdf.getPage(i);
                const viewport = page.getViewport({ scale: 1.0 });
                
                const wrapper = document.createElement('div');
                wrapper.className = 'bg-card border border-border rounded-lg p-2 shadow-xs mb-3 space-y-1.5';
                
                const title = document.createElement('div');
                title.className = 'text-[10px] font-bold text-muted-foreground uppercase tracking-wider text-center border-b border-border pb-1';
                title.textContent = `Trang ${i} / ${pdf.numPages}`;
                
                const canvas = document.createElement('canvas');
                canvas.className = 'w-full h-auto rounded border border-border bg-white';
                const ctx = canvas.getContext('2d', { alpha: false });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                wrapper.appendChild(title);
                wrapper.appendChild(canvas);
                container.appendChild(wrapper);

                page.render({ canvasContext: ctx, viewport: viewport });
            }
        } catch (err) {
            console.error('PDF.js render error:', err);
            container.innerHTML = `<iframe src="${url}" class="w-full h-[500px] border-0 rounded-lg"></iframe>`;
        }
    }

    function initUserGuideTinyMCE() {
        tinymce.init({
            selector: '.tinymce-editor',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 250,
            branding: false,
            promotion: false,
            language: 'vi',
            images_upload_handler: function (blobInfo, progress) {
                return new Promise((resolve, reject) => {
                    resolve('data:' + blobInfo.blob().type + ';base64,' + blobInfo.base64());
                });
            },
            setup: function (editor) {
                editor.on('change keyup input ExecCommand', function () {
                    editor.save();
                    const el = editor.getElement();
                    if (el) {
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initUserGuideTinyMCE();
    });

    function userGuideEditor(nodeCode, videoSource, uploadedUrl, embedUrl, sections, videoTitle = '') {
        return {
            showPreview: true,
            nodeCode: nodeCode,
            videoSource: videoSource,
            uploadedUrl: uploadedUrl,
            embedUrl: embedUrl,
            videoTitle: videoTitle,
            uploadedFileName: '',
            newFilePreviewUrl: '',
            sectionsList: sections,
            isSaving: false,
            async submitForm(event) {
                if (window.tinymce) {
                    window.tinymce.triggerSave();
                }

                const form = event.target;
                const formData = new FormData(form);
                this.isSaving = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        if (result.uploaded_video_url) {
                            this.uploadedUrl = result.uploaded_video_url;
                            this.uploadedFileName = '';
                            this.newFilePreviewUrl = '';
                        }
                        
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: result.message || 'Cập nhật thành công!', type: 'success' }
                        }));
                    } else {
                        let errorMsg = result.message || 'Có lỗi xảy ra, vui lòng kiểm tra lại.';
                        if (result.errors) {
                            const firstKey = Object.keys(result.errors)[0];
                            if (firstKey && result.errors[firstKey] && result.errors[firstKey].length > 0) {
                                errorMsg = result.errors[firstKey][0];
                            }
                        }
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: errorMsg, type: 'error' }
                        }));
                    }
                } catch (err) {
                    console.error('AJAX Submit Error:', err);
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: 'Lỗi kết nối máy chủ. Vui lòng thử lại.', type: 'error' }
                    }));
                } finally {
                    this.isSaving = false;
                }
            },
            init() {
                this.$watch('activePreviewUrl', (newUrl) => {
                    if (newUrl && (newUrl.toLowerCase().includes('.pdf') || (this.uploadedFileName && this.uploadedFileName.toLowerCase().endsWith('.pdf')))) {
                        this.$nextTick(() => {
                            renderPdfToCanvasList(newUrl, 'pdf-canvas-container-' + this.nodeCode);
                        });
                    }
                });
                if (this.activePreviewUrl && (this.activePreviewUrl.toLowerCase().includes('.pdf') || (this.uploadedFileName && this.uploadedFileName.toLowerCase().endsWith('.pdf')))) {
                    this.$nextTick(() => {
                        renderPdfToCanvasList(this.activePreviewUrl, 'pdf-canvas-container-' + this.nodeCode);
                    });
                }
            },
            addSection() {
                this.sectionsList.push({
                    title: 'Khối ' + (this.sectionsList.length + 1) + ':',
                    steps: [{ title: '', content: '' }]
                });
                this.$nextTick(() => {
                    initUserGuideTinyMCE();
                });
            },
            removeSection(sIndex) {
                if (this.sectionsList.length > 1) {
                    this.sectionsList[sIndex].steps.forEach((step, stIndex) => {
                        let editorId = 'editor_' + this.nodeCode + '_' + sIndex + '_' + stIndex;
                        if (tinymce.get(editorId)) {
                            tinymce.get(editorId).remove();
                        }
                    });
                    this.sectionsList.splice(sIndex, 1);
                }
            },
            addStepToSection(sIndex) {
                this.sectionsList[sIndex].steps.push({ title: '', content: '' });
                this.$nextTick(() => {
                    initUserGuideTinyMCE();
                });
            },
            removeStepFromSection(sIndex, stIndex) {
                if (this.sectionsList[sIndex].steps.length > 1) {
                    let editorId = 'editor_' + this.nodeCode + '_' + sIndex + '_' + stIndex;
                    if (tinymce.get(editorId)) {
                        tinymce.get(editorId).remove();
                    }
                    this.sectionsList[sIndex].steps.splice(stIndex, 1);
                }
            },
            get activePreviewUrl() {
                if (this.videoSource === 'file') {
                    return this.newFilePreviewUrl || this.uploadedUrl;
                }
                return this.embedUrl;
            }
        };
    }
</script>
@endpush
@endsection
