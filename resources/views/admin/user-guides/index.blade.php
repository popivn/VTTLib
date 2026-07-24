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
    <div class="bg-card border border-border rounded-xl p-2 shadow-xs overflow-hidden">
        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none pb-1 sm:pb-0">
            @if($hasDbNodes)
                @foreach($subNodes as $node)
                    <button type="button"
                            @click="switchTab('{{ $node->node_code }}')"
                            :class="activeTab === '{{ $node->node_code }}' ? 'bg-primary text-primary-foreground shadow-sm font-bold' : 'bg-muted/40 hover:bg-muted text-muted-foreground font-medium'"
                            class="px-3.5 py-2 rounded-lg text-xs flex items-center gap-2 whitespace-nowrap transition-all flex-shrink-0">
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
                $stepsTitle = $json['steps_title'] ?? 'Các bước thực hiện:';
                $steps = $json['steps'] ?? [];
                $section2Title = $json['section2_title'] ?? '';
                $section2Steps = $json['section2_steps'] ?? [];
                $videoTitle = $json['video_title'] ?? 'Bạn đọc vui lòng xem video hướng dẫn dưới đây:';
                $uploadedVideoUrl = $json['uploaded_video_url'] ?? '';
                $embedVideoUrl = $json['embed_video_url'] ?? ($json['video_url'] ?? '');
                $videoSource = $json['video_source'] ?? (!empty($uploadedVideoUrl) ? 'file' : 'url');
            @endphp
            <div x-show="activeTab === '{{ $node->node_code }}'" x-cloak class="space-y-4">
                <form action="{{ route('admin.user-guides.update', ['siteNode' => $node->id, 'tab' => $node->node_code]) }}" method="POST" enctype="multipart/form-data" class="space-y-4" 
                      x-data="{ 
                          videoSource: '{{ $videoSource }}', 
                          uploadedUrl: '{{ addslashes($uploadedVideoUrl) }}', 
                          embedUrl: '{{ addslashes($embedVideoUrl) }}',
                          uploadedFileName: '',
                          newFilePreviewUrl: '',
                          stepsList: {{ json_encode(!empty($steps) ? $steps : [['title' => '', 'content' => '', 'link_url' => '']]) }},
                          section2StepsList: {{ json_encode(!empty($section2Steps) ? $section2Steps : []) }},
                          addStep() {
                              this.stepsList.push({ title: '', content: '', link_url: '' });
                          },
                          removeStep(index) {
                              if (this.stepsList.length > 1) {
                                  this.stepsList.splice(index, 1);
                              }
                          },
                          addSection2Step() {
                              this.section2StepsList.push({ title: '', content: '', link_url: '' });
                          },
                          removeSection2Step(index) {
                              this.section2StepsList.splice(index, 1);
                          },
                          formatText(index, command, value = null) {
                              let editor = document.getElementById('visual_editor_' + '{{ $node->node_code }}' + '_' + index);
                              if (!editor) return;
                              editor.focus();
                              if (command === 'createLink') {
                                  let url = prompt('Nhập đường dẫn URL liên kết (Ví dụ: /opac hoặc https://...):', 'https://');
                                  if (url) {
                                      document.execCommand('createLink', false, url);
                                  }
                              } else {
                                  document.execCommand(command, false, value);
                              }
                              this.stepsList[index].content = editor.innerHTML;
                          },
                          formatSection2Text(index, command, value = null) {
                              let editor = document.getElementById('visual_editor_sec2_' + '{{ $node->node_code }}' + '_' + index);
                              if (!editor) return;
                              editor.focus();
                              if (command === 'createLink') {
                                  let url = prompt('Nhập đường dẫn URL liên kết (Ví dụ: /opac hoặc https://...):', 'https://');
                                  if (url) {
                                      document.execCommand('createLink', false, url);
                                  }
                              } else {
                                  document.execCommand(command, false, value);
                              }
                              this.section2StepsList[index].content = editor.innerHTML;
                          },
                          syncContent(index, event) {
                              this.stepsList[index].content = event.target.innerHTML;
                          },
                          syncSection2Content(index, event) {
                              this.section2StepsList[index].content = event.target.innerHTML;
                          },
                          get activePreviewUrl() {
                              if (this.videoSource === 'file') {
                                  return this.newFilePreviewUrl || this.uploadedUrl;
                              }
                              return this.embedUrl;
                          }
                      }">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Form Left Column: Settings -->
                        <div class="bg-card border border-border rounded-xl p-5 shadow-xs space-y-5">
                            <div class="flex items-center justify-between border-b border-border pb-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-2">
                                    <i class="{{ $node->icon ?: 'fas fa-edit' }}"></i>
                                    {{ __('Cấu hình nội dung') }}: {{ $node->node_name }}
                                </h3>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-muted text-muted-foreground border border-border">
                                    {{ $node->node_code }}
                                </span>
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

                                <!-- 2. Steps Management Section -->
                                <div class="space-y-3 pt-3 border-t border-border">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] font-bold text-primary uppercase tracking-wide flex items-center gap-1.5">
                                            <i class="fas fa-list-ol"></i>
                                            {{ __('Các bước thực hiện Hướng Dẫn') }}
                                        </label>
                                        <button type="button" @click="addStep()" class="px-2.5 py-1 bg-primary/10 hover:bg-primary/20 text-primary text-[11px] font-bold rounded-md border border-primary/20 flex items-center gap-1 transition-all">
                                            <i class="fas fa-plus text-[9px]"></i>
                                            {{ __('Thêm bước mới') }}
                                        </button>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tiêu đề khối Các bước') }}</label>
                                        <input type="text" name="steps_title" value="{{ $stepsTitle }}" placeholder="Ví dụ: Các bước tra cứu sách giấy:" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none font-bold">
                                    </div>

                                    <!-- Dynamic Visual Steps Container -->
                                    <div class="space-y-4 pt-1">
                                        <template x-for="(step, index) in stepsList" :key="index">
                                            <div class="p-4 bg-muted/20 border border-border rounded-xl space-y-3 relative group hover:border-primary/40 transition-all shadow-xs">
                                                <div class="flex items-center justify-between">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[10px] font-bold bg-primary text-primary-foreground">
                                                        <span>{{ __('Bước') }}</span>
                                                        <span x-text="index + 1"></span>
                                                    </span>

                                                    <button type="button" @click="removeStep(index)" x-show="stepsList.length > 1" class="text-muted-foreground hover:text-red-500 text-xs p-1 transition-colors" title="Xóa bước này">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>

                                                <!-- Step Title -->
                                                <div class="space-y-1">
                                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tên bước') }}</label>
                                                    <input type="text" :name="'steps[' + index + '][title]'" x-model="step.title" placeholder="Ví dụ: Truy cập cổng tra cứu OPAC" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground font-bold focus:ring-1 focus:ring-primary outline-none">
                                                </div>

                                                <!-- Visual Word-Like Content Editor -->
                                                <div class="space-y-1.5">
                                                    <div class="flex items-center justify-between gap-1">
                                                        <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Nội dung bước (Soạn thảo như Word - Không cần mã Code)') }}</label>
                                                        
                                                        <!-- Word-Like Visual Toolbar -->
                                                        <div class="flex items-center gap-1 bg-background border border-border rounded-md p-1 shadow-2xs">
                                                            <button type="button" @click="formatText(index, 'bold')" class="w-6 h-6 hover:bg-muted text-xs font-black text-foreground rounded flex items-center justify-center transition-colors" title="Bôi đen chữ rồi bấm nút này để Tô đậm (Bold)">
                                                                <b>B</b>
                                                            </button>
                                                            <button type="button" @click="formatText(index, 'italic')" class="w-6 h-6 hover:bg-muted text-xs italic font-bold text-foreground rounded flex items-center justify-center transition-colors" title="Bôi đen chữ rồi bấm nút này để In nghiêng (Italic)">
                                                                <i>I</i>
                                                            </button>
                                                            <div class="w-px h-3 bg-border"></div>
                                                            <button type="button" @click="formatText(index, 'createLink')" class="px-2 h-6 hover:bg-primary/10 text-primary text-[10px] font-bold rounded flex items-center gap-1 transition-colors" title="Bôi đen chữ rồi bấm để Đính kèm đường dẫn Link">
                                                                <i class="fas fa-link text-[9px]"></i>
                                                                <span>{{ __('Chèn Link') }}</span>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Hidden input to store HTML payload -->
                                                    <input type="hidden" :name="'steps[' + index + '][content]'" :value="step.content">

                                                    <!-- Visual Editable Area (Zero HTML Code Visible) -->
                                                    <div :id="'visual_editor_' + '{{ $node->node_code }}' + '_' + index" 
                                                         contenteditable="true" 
                                                         @input="syncContent(index, $event)" 
                                                         x-html="step.content || ''" 
                                                         placeholder="Nhập nội dung bước tại đây..." 
                                                         class="w-full min-h-[70px] bg-background border border-border rounded-lg p-3 text-xs text-foreground leading-relaxed focus:ring-1 focus:ring-primary outline-none font-sans prose dark:prose-invert max-w-none">
                                                    </div>
                                                    <p class="text-[10px] text-muted-foreground flex items-center gap-1">
                                                        <i class="fas fa-magic text-primary"></i>
                                                        <span>{{ __('Mẹo: Bôi đen đoạn chữ rồi bấm nút [B] để in đậm hoặc [Chèn Link] để gắn liên kết.') }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- 2.5 Section 2 Steps Management (e.g. Gia hạn tài liệu) -->
                                <div class="space-y-3 pt-3 border-t border-border">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] font-bold text-primary uppercase tracking-wide flex items-center gap-1.5">
                                            <i class="fas fa-list-ol"></i>
                                            {{ __('Các bước thực hiện Phần 2 (Tùy chọn - Ví dụ: Gia hạn)') }}
                                        </label>
                                        <button type="button" @click="addSection2Step()" class="px-2.5 py-1 bg-primary/10 hover:bg-primary/20 text-primary text-[11px] font-bold rounded-md border border-primary/20 flex items-center gap-1 transition-all">
                                            <i class="fas fa-plus text-[9px]"></i>
                                            {{ __('Thêm bước phần 2') }}
                                        </button>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tiêu đề khối Phần 2') }}</label>
                                        <input type="text" name="section2_title" value="{{ $section2Title }}" placeholder="Ví dụ: 2. Gia hạn tài liệu trực tuyến:" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none font-bold">
                                    </div>

                                    <!-- Dynamic Section 2 Steps Container -->
                                    <div class="space-y-4 pt-1" x-show="section2StepsList.length > 0 || '{{ $node->node_code }}' === 'muon-truoc-gia-han'">
                                        <template x-for="(step, index) in section2StepsList" :key="index">
                                            <div class="p-4 bg-muted/20 border border-border rounded-xl space-y-3 relative group hover:border-primary/40 transition-all shadow-xs">
                                                <div class="flex items-center justify-between">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[10px] font-bold bg-secondary text-secondary-foreground">
                                                        <span>{{ __('Phần 2 - Bước') }}</span>
                                                        <span x-text="index + 1"></span>
                                                    </span>

                                                    <button type="button" @click="removeSection2Step(index)" class="text-muted-foreground hover:text-red-500 text-xs p-1 transition-colors" title="Xóa bước này">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>

                                                <!-- Step Title -->
                                                <div class="space-y-1">
                                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Tên bước') }}</label>
                                                    <input type="text" :name="'section2_steps[' + index + '][title]'" x-model="step.title" placeholder="Ví dụ: Yêu cầu gia hạn" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground font-bold focus:ring-1 focus:ring-primary outline-none">
                                                </div>

                                                <!-- Visual Content Editor -->
                                                <div class="space-y-1.5">
                                                    <div class="flex items-center justify-between gap-1">
                                                        <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-wide">{{ __('Nội dung bước') }}</label>
                                                        
                                                        <div class="flex items-center gap-1 bg-background border border-border rounded-md p-1 shadow-2xs">
                                                            <button type="button" @click="formatSection2Text(index, 'bold')" class="w-6 h-6 hover:bg-muted text-xs font-black text-foreground rounded flex items-center justify-center transition-colors" title="Bôi đen rồi bấm [B] để tô đậm">
                                                                <b>B</b>
                                                            </button>
                                                            <button type="button" @click="formatSection2Text(index, 'italic')" class="w-6 h-6 hover:bg-muted text-xs italic font-bold text-foreground rounded flex items-center justify-center transition-colors" title="Bôi đen rồi bấm [I] để in nghiêng">
                                                                <i>I</i>
                                                            </button>
                                                            <div class="w-px h-3 bg-border"></div>
                                                            <button type="button" @click="formatSection2Text(index, 'createLink')" class="px-2 h-6 hover:bg-primary/10 text-primary text-[10px] font-bold rounded flex items-center gap-1 transition-colors" title="Chèn Link">
                                                                <i class="fas fa-link text-[9px]"></i>
                                                                <span>{{ __('Chèn Link') }}</span>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" :name="'section2_steps[' + index + '][content]'" :value="step.content">

                                                    <div :id="'visual_editor_sec2_' + '{{ $node->node_code }}' + '_' + index" 
                                                         contenteditable="true" 
                                                         @input="syncSection2Content(index, $event)" 
                                                         x-html="step.content || ''" 
                                                         placeholder="Nhập nội dung bước phần 2..." 
                                                         class="w-full min-h-[70px] bg-background border border-border rounded-lg p-3 text-xs text-foreground leading-relaxed focus:ring-1 focus:ring-primary outline-none font-sans prose dark:prose-invert max-w-none">
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
                                        <input type="text" name="video_title" value="{{ $videoTitle }}" class="w-full h-9 bg-background border border-border rounded-lg px-3 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] font-bold text-primary uppercase tracking-wide flex items-center gap-1.5">
                                            <i class="fas fa-video"></i>
                                            {{ __('Tùy chọn Nguồn Video sử dụng') }}
                                        </label>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <label class="p-2.5 rounded-lg border transition-all flex items-center gap-2 cursor-pointer" :class="videoSource === 'file' ? 'border-primary bg-primary/5' : 'border-border bg-background'">
                                                <input type="radio" name="video_source" value="file" x-model="videoSource" class="w-3.5 h-3.5 text-primary">
                                                <span class="text-xs font-bold text-foreground">{{ __('File Video tải lên') }}</span>
                                            </label>
                                            <label class="p-2.5 rounded-lg border transition-all flex items-center gap-2 cursor-pointer" :class="videoSource === 'url' ? 'border-primary bg-primary/5' : 'border-border bg-background'">
                                                <input type="radio" name="video_source" value="url" x-model="videoSource" class="w-3.5 h-3.5 text-primary">
                                                <span class="text-xs font-bold text-foreground">{{ __('Link URL Canva / YT') }}</span>
                                            </label>
                                        </div>

                                        <div x-show="videoSource === 'file'" class="pt-1">
                                            <label class="flex items-center gap-2 px-3 h-9 bg-card border border-border border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-all group">
                                                <i class="fas fa-cloud-upload-alt text-muted-foreground group-hover:text-foreground text-xs"></i>
                                                <span class="text-[11px] text-muted-foreground group-hover:text-foreground truncate" x-text="uploadedFileName || 'Tải lên file video mới (MP4, WEBM... Max 100MB)...'"></span>
                                                <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime" class="hidden" @change="uploadedFileName = $event.target.files[0]?.name; if($event.target.files[0]) newFilePreviewUrl = URL.createObjectURL($event.target.files[0])">
                                            </label>
                                        </div>

                                        <div x-show="videoSource === 'url'" class="pt-1">
                                            <input type="text" name="embed_video_url" x-model="embedUrl" placeholder="Ví dụ: https://www.canva.com/design/.../watch?embed" class="w-full h-9 bg-card border border-border rounded-lg px-3 text-xs text-foreground focus:ring-1 focus:ring-primary outline-none font-mono">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-border flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold rounded-lg transition-all shadow-sm active:scale-95">
                                    <i class="fas fa-save"></i>
                                    {{ __('Lưu thay đổi bài Hướng dẫn') }}
                                </button>
                            </div>
                        </div>

                        <!-- Form Right Column: Live Interactive Preview -->
                        <div class="bg-card border border-border rounded-xl p-5 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-border pb-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                                    <i class="fas fa-desktop text-primary"></i>
                                    {{ __('Xem trước Giao diện ngoài Trang chủ') }}
                                </h3>
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 font-bold border border-emerald-500/20 uppercase tracking-wide">
                                    Live Preview
                                </span>
                            </div>

                            <!-- Live Render Container -->
                            <div class="border border-border rounded-lg p-4 bg-background space-y-4 shadow-inner">
                                <!-- Steps Preview -->
                                <div class="space-y-3">
                                    <h4 class="font-bold text-foreground text-xs flex items-center gap-2 border-b border-border pb-2">
                                        <i class="fas fa-info-circle text-vttu-red"></i>
                                        <span x-text="stepsTitle || 'Các bước thực hiện:'"></span>
                                    </h4>

                                    <div class="space-y-2">
                                        <template x-for="(st, idx) in stepsList" :key="idx">
                                            <div x-show="st.title || st.content" class="flex gap-3 p-3 bg-card border border-border rounded-md shadow-2xs">
                                                <div class="w-6 h-6 bg-vttu-red/10 text-vttu-red rounded flex items-center justify-center font-bold text-xs flex-shrink-0" x-text="idx + 1"></div>
                                                <div class="space-y-0.5 flex-1 min-w-0">
                                                    <h5 class="font-bold text-foreground text-xs" x-text="st.title || 'Bước ' + (idx + 1)"></h5>
                                                    <div class="text-xs text-muted-foreground leading-relaxed break-words prose dark:prose-invert max-w-none" x-html="st.content || ''"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Section 2 Preview -->
                                <div class="space-y-3 pt-3 border-t border-border" x-show="section2StepsList.length > 0">
                                    <h4 class="font-bold text-foreground text-xs flex items-center gap-2 border-b border-border pb-2">
                                        <i class="fas fa-info-circle text-vttu-red"></i>
                                        <span x-text="section2Title || '2. Gia hạn tài liệu trực tuyến:'"></span>
                                    </h4>

                                    <div class="space-y-2">
                                        <template x-for="(st, idx) in section2StepsList" :key="idx">
                                            <div x-show="st.title || st.content" class="flex gap-3 p-3 bg-card border border-border rounded-md shadow-2xs">
                                                <div class="w-6 h-6 bg-vttu-red/10 text-vttu-red rounded flex items-center justify-center font-bold text-xs flex-shrink-0" x-text="idx + 1"></div>
                                                <div class="space-y-0.5 flex-1 min-w-0">
                                                    <h5 class="font-bold text-foreground text-xs" x-text="st.title || 'Bước ' + (idx + 1)"></h5>
                                                    <div class="text-xs text-muted-foreground leading-relaxed break-words prose dark:prose-invert max-w-none" x-html="st.content || ''"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Video Preview -->
                                <template x-if="activePreviewUrl">
                                    <div class="space-y-2 pt-3 border-t border-border">
                                        <h4 class="font-bold text-foreground text-xs flex items-center gap-1.5" x-text="videoTitle || 'Video hướng dẫn:'"></h4>
                                        <div class="relative w-full aspect-video rounded-md overflow-hidden border border-border shadow-md bg-black">
                                            <template x-if="videoSource === 'file' || activePreviewUrl.includes('.mp4') || activePreviewUrl.includes('.webm') || activePreviewUrl.includes('blob:')">
                                                <video :src="activePreviewUrl" controls class="w-full h-full object-contain"></video>
                                            </template>
                                            <template x-if="videoSource === 'url' && !(activePreviewUrl.includes('.mp4') || activePreviewUrl.includes('.webm') || activePreviewUrl.includes('blob:'))">
                                                <iframe :src="activePreviewUrl" class="absolute inset-0 w-full h-full border-0" allowfullscreen allow="fullscreen"></iframe>
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
@endsection
