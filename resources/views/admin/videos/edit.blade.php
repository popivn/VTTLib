@extends('layouts.admin')

@section('title', 'Chỉnh sửa Video')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.videos.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-muted hover:bg-muted/80 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-foreground">{{ __('Chỉnh sửa Video') }}</h1>
                <p class="text-sm text-muted-foreground">{{ $video->title }}</p>
            </div>
        </div>

        <div class="bg-card rounded-lg border shadow-sm">
            <form action="{{ route('admin.videos.update', $video) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Current Video Preview -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-foreground">{{ __('Video hiện tại') }}</label>
                    <div class="bg-muted/50 rounded-lg p-4 border">
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-16 rounded bg-muted flex items-center justify-center overflow-hidden flex-shrink-0">
                                @if($video->thumbnail_url)
                                    <img src="{{ $video->thumbnail_url }}" alt="Thumbnail" class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-video text-muted-foreground"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-foreground">{{ $video->title }}</p>
                                <p class="text-xs text-muted-foreground">{{ $video->formatted_size }}</p>
                                <a href="{{ $video->video_url }}" target="_blank" class="text-xs text-primary hover:underline">Xem video</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Title -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-foreground">{{ __('Tên video') }} <span class="text-destructive">*</span></label>
                    <input type="text" name="title" 
                           value="{{ old('title', $video->title) }}"
                           class="w-full h-10 px-3 text-sm border border-input rounded bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all @error('title') border-destructive @enderror"
                           placeholder="Nhập tên video..."
                           required>
                    @error('title')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-foreground">{{ __('Mô tả') }}</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2 text-sm border border-input rounded bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all @error('description') border-destructive @enderror"
                              placeholder="Mô tả ngắn gọn về video...">{{ old('description', $video->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Replace Video File -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-foreground">{{ __('Thay thế file video (tùy chọn)') }}</label>
                    <div class="border-2 border-dashed border-border rounded-lg p-6 text-center hover:border-primary/50 transition-colors"
                         x-data="{ dragOver: false, fileName: '', fileSize: '' }"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="dragOver = false; handleDrop($event)"
                         :class="{ 'border-primary bg-primary/5': dragOver }">
                        
                        <input type="file" name="video_file" accept="video/*" class="hidden" 
                               @change="handleFileSelect($event)" id="video-upload">
                        
                        <div x-show="!fileName">
                            <i class="fas fa-cloud-upload-alt text-2xl text-muted-foreground mb-2"></i>
                            <p class="text-sm text-foreground mb-2">Chọn file video mới để thay thế</p>
                            <label for="video-upload" 
                                   class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded text-sm font-medium hover:bg-primary/90 transition-colors cursor-pointer">
                                <i class="fas fa-upload mr-2"></i>
                                Chọn file video
                            </label>
                            <p class="text-xs text-muted-foreground mt-2">
                                Để trống nếu không muốn thay đổi file video
                            </p>
                        </div>

                        <div x-show="fileName" class="space-y-2">
                            <i class="fas fa-video text-2xl text-primary mb-2"></i>
                            <p class="text-sm font-medium text-foreground" x-text="fileName"></p>
                            <p class="text-xs text-muted-foreground" x-text="fileSize"></p>
                            <button type="button" @click="clearFile()" 
                                    class="text-xs text-destructive hover:underline">
                                Hủy chọn
                            </button>
                        </div>
                    </div>
                    @error('video_file')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Replace Thumbnail -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-foreground">{{ __('Thay thế ảnh thumbnail (tùy chọn)') }}</label>
                    <div class="border-2 border-dashed border-border rounded-lg p-4 text-center hover:border-primary/50 transition-colors"
                         x-data="{ thumbName: '', thumbPreview: '' }">
                        
                        <input type="file" name="thumbnail" accept="image/*" class="hidden" 
                               @change="handleThumbSelect($event)" id="thumb-upload">
                        
                        <div x-show="!thumbName">
                            @if($video->thumbnail_url)
                                <div class="mb-3">
                                    <p class="text-xs text-muted-foreground mb-2">Thumbnail hiện tại:</p>
                                    <img src="{{ $video->thumbnail_url }}" class="w-20 h-16 object-cover rounded mx-auto">
                                </div>
                            @endif
                            <i class="fas fa-image text-xl text-muted-foreground mb-2"></i>
                            <label for="thumb-upload" 
                                   class="inline-flex items-center px-3 py-2 bg-muted text-muted-foreground rounded text-xs font-medium hover:bg-muted/80 transition-colors cursor-pointer">
                                <i class="fas fa-upload mr-1"></i>
                                {{ $video->thumbnail ? 'Thay đổi thumbnail' : 'Thêm thumbnail' }}
                            </label>
                            <p class="text-xs text-muted-foreground mt-1">PNG, JPG, GIF, WebP (tối đa 10MB)</p>
                        </div>

                        <div x-show="thumbName" class="space-y-2">
                            <img x-show="thumbPreview" :src="thumbPreview" class="w-20 h-16 object-cover rounded mx-auto">
                            <p class="text-xs font-medium text-foreground" x-text="thumbName"></p>
                            <button type="button" @click="clearThumb()" 
                                    class="text-xs text-destructive hover:underline">
                                Hủy chọn
                            </button>
                        </div>
                    </div>
                    @error('thumbnail')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Settings -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Sort Order -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">{{ __('Thứ tự') }}</label>
                        <input type="number" name="sort_order" 
                               value="{{ old('sort_order', $video->sort_order) }}" min="0"
                               class="w-full h-10 px-3 text-sm border border-input rounded bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">{{ __('Trạng thái') }}</label>
                        <div class="flex items-center space-x-3 h-10">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" 
                                       {{ old('is_active', $video->is_active) ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-border rounded focus:ring-primary focus:ring-offset-0">
                                <span class="ml-2 text-sm text-foreground">Hiển thị</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-border">
                    <a href="{{ route('admin.videos.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ __('Hủy') }}
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-primary text-primary-foreground rounded text-sm font-medium hover:bg-primary/90 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        {{ __('Cập nhật Video') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        this.fileName = file.name;
        this.fileSize = formatFileSize(file.size);
    }
}

function handleDrop(event) {
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        document.getElementById('video-upload').files = files;
        this.fileName = file.name;
        this.fileSize = formatFileSize(file.size);
    }
}

function handleThumbSelect(event) {
    const file = event.target.files[0];
    if (file) {
        this.thumbName = file.name;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            this.thumbPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function clearFile() {
    this.fileName = '';
    this.fileSize = '';
    document.getElementById('video-upload').value = '';
}

function clearThumb() {
    this.thumbName = '';
    this.thumbPreview = '';
    document.getElementById('thumb-upload').value = '';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endsection