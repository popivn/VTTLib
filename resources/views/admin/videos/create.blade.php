@extends('layouts.admin')

@section('title', 'Thêm Video Mới')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.videos.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-muted hover:bg-muted/80 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-foreground">{{ __('Thêm Video Mới') }}</h1>
                <p class="text-sm text-muted-foreground">{{ __('Tải lên video bài giảng hoặc tài liệu học tập') }}</p>
            </div>
        </div>

        <div class="bg-card rounded-lg border shadow-sm">
            <form action="{{ route('admin.videos.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Title -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-foreground">{{ __('Tên video') }} <span class="text-destructive">*</span></label>
                    <input type="text" name="title" 
                           value="{{ old('title') }}"
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
                              placeholder="Mô tả ngắn gọn về video...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Video File Upload -->
                <div class="space-y-2" 
                     x-data="videoUploadComponent()">
                    <label class="text-sm font-medium text-foreground">{{ __('File video') }} <span class="text-destructive">*</span></label>
                    <div class="border-2 border-dashed border-border rounded-lg p-6 text-center hover:border-primary/50 transition-colors"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="dragOver = false; handleDrop($event)"
                         :class="{ 'border-primary bg-primary/5': dragOver }">
                        
                        <input type="file" name="video_file" accept="video/*" class="hidden" 
                               @change="handleFileSelect($event)" id="video-upload">
                        
                        <!-- Hidden fields for form submission -->
                        <input type="hidden" name="video_path" x-model="videoPath">
                        <input type="hidden" name="video_size" x-model="videoFileSize">
                        <input type="hidden" name="video_mime_type" x-model="videoMimeType">
                        
                        <div x-show="!fileName && !uploadCompleted">
                            <i class="fas fa-cloud-upload-alt text-3xl text-muted-foreground mb-4"></i>
                            <p class="text-sm text-foreground mb-2">Kéo thả video vào đây hoặc</p>
                            <label for="video-upload" 
                                   class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded text-sm font-medium hover:bg-primary/90 transition-colors cursor-pointer">
                                <i class="fas fa-upload mr-2"></i>
                                Chọn file video
                            </label>
                            <p class="text-xs text-muted-foreground mt-2">
                                Hỗ trợ: MP4, AVI, MOV, WMV, FLV, WebM, MKV (tối đa 500MB)
                            </p>
                        </div>

                        <div x-show="isUploading" class="space-y-3">
                            <i class="fas fa-video text-3xl text-primary mb-2"></i>
                            <p class="text-sm font-medium text-foreground" x-text="fileName"></p>
                            <p class="text-xs text-muted-foreground" x-text="fileSize"></p>
                            
                            <div class="w-full bg-muted rounded-full h-3">
                                <div class="bg-primary h-3 rounded-full transition-all duration-300 flex items-center justify-center" 
                                     :style="`width: ${uploadProgress}%`">
                                    <span class="text-xs text-white font-medium" x-show="uploadProgress > 10" x-text="`${Math.round(uploadProgress)}%`"></span>
                                </div>
                            </div>
                            
                            <p class="text-xs text-muted-foreground">Đang tải video lên server...</p>
                        </div>

                        <div x-show="uploadCompleted" class="space-y-2">
                            <i class="fas fa-check-circle text-3xl text-green-600 mb-2"></i>
                            <p class="text-sm font-medium text-foreground" x-text="fileName"></p>
                            <p class="text-xs text-muted-foreground" x-text="fileSize"></p>
                            <p class="text-xs text-green-600 font-medium">✓ Tải lên thành công</p>
                            
                            <button type="button" @click="clearFile()" 
                                    class="text-xs text-destructive hover:underline">
                                Chọn file khác
                            </button>
                        </div>
                    </div>
                    @error('video_path')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                    @enderror
                    @error('video_file')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Thumbnail Upload -->
                <div class="space-y-2"
                     x-data="thumbnailUploadComponent()">
                    <label class="text-sm font-medium text-foreground">{{ __('Ảnh thumbnail (tùy chọn)') }}</label>
                    <div class="border-2 border-dashed border-border rounded-lg p-4 text-center hover:border-primary/50 transition-colors">
                        
                        <input type="file" name="thumbnail_file" accept="image/*" class="hidden" 
                               @change="handleThumbSelect($event)" id="thumb-upload">
                        
                        <!-- Hidden field for form submission -->
                        <input type="hidden" name="thumbnail_path" x-model="thumbPath">
                        
                        <div x-show="!thumbName && !thumbCompleted">
                            <i class="fas fa-image text-2xl text-muted-foreground mb-2"></i>
                            <label for="thumb-upload" 
                                   class="inline-flex items-center px-3 py-2 bg-muted text-muted-foreground rounded text-xs font-medium hover:bg-muted/80 transition-colors cursor-pointer">
                                <i class="fas fa-upload mr-1"></i>
                                Chọn ảnh thumbnail
                            </label>
                            <p class="text-xs text-muted-foreground mt-1">PNG, JPG, GIF, WebP (tối đa 10MB)</p>
                        </div>

                        <div x-show="thumbUploading" class="space-y-2">
                            <i class="fas fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                            <p class="text-xs text-muted-foreground">Đang tải ảnh...</p>
                        </div>

                        <div x-show="thumbCompleted" class="space-y-2">
                            <img x-show="thumbPreview" :src="thumbPreview" class="w-20 h-16 object-cover rounded mx-auto">
                            <p class="text-xs font-medium text-foreground" x-text="thumbName"></p>
                            <p class="text-xs text-green-600 font-medium">✓ Tải lên thành công</p>
                            <button type="button" @click="clearThumb()" 
                                    class="text-xs text-destructive hover:underline">
                                Chọn ảnh khác
                            </button>
                        </div>
                    </div>
                    @error('thumbnail_path')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                    @enderror
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
                               value="{{ old('sort_order', 0) }}" min="0"
                               class="w-full h-10 px-3 text-sm border border-input rounded bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">{{ __('Trạng thái') }}</label>
                        <div class="flex items-center space-x-3 h-10">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-border rounded focus:ring-primary focus:ring-offset-0">
                                <span class="ml-2 text-sm text-foreground">Hiển thị ngay</span>
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
                        {{ __('Lưu Video') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Alpine.js component for video upload
function videoUploadComponent() {
    return {
        dragOver: false,
        fileName: '',
        fileSize: '',
        uploadProgress: 0,
        isUploading: false,
        uploadCompleted: false,
        videoPath: '',
        videoFileSize: 0,
        videoMimeType: '',
        
        async handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.fileName = file.name;
                this.fileSize = this.formatFileSize(file.size);
                
                // Start upload immediately
                await this.uploadVideo(file);
            }
        },
        
        async handleDrop(event) {
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                this.fileName = file.name;
                this.fileSize = this.formatFileSize(file.size);
                
                // Set the file input
                document.getElementById('video-upload').files = files;
                
                // Start upload immediately
                await this.uploadVideo(file);
            }
        },
        
        async uploadVideo(file) {
            const formData = new FormData();
            formData.append('video_file', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            // Update states
            this.isUploading = true;
            this.uploadProgress = 0;
            this.uploadCompleted = false;
            
            try {
                const xhr = new XMLHttpRequest();
                
                // Track upload progress
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                        this.$nextTick();
                    }
                });
                
                // Handle completion
                xhr.addEventListener('load', () => {
                    this.isUploading = false;
                    
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            this.uploadCompleted = true;
                            this.videoPath = response.path;
                            this.videoFileSize = response.size;
                            this.videoMimeType = response.mime_type;
                            this.fileSize = response.formatted_size;
                            
                            console.log('Upload success:', response);
                        } else {
                            alert('Lỗi: ' + response.message);
                            this.clearFile();
                        }
                    } else {
                        alert('Có lỗi xảy ra khi tải video');
                        this.clearFile();
                    }
                });
                
                // Handle error
                xhr.addEventListener('error', () => {
                    this.isUploading = false;
                    alert('Có lỗi xảy ra khi tải video');
                    this.clearFile();
                });
                
                xhr.open('POST', '{{ route("admin.videos.upload-video") }}');
                xhr.send(formData);
                
            } catch (error) {
                this.isUploading = false;
                alert('Có lỗi xảy ra: ' + error.message);
                this.clearFile();
            }
        },
        
        clearFile() {
            this.fileName = '';
            this.fileSize = '';
            this.uploadProgress = 0;
            this.isUploading = false;
            this.uploadCompleted = false;
            this.videoPath = '';
            this.videoFileSize = 0;
            this.videoMimeType = '';
            document.getElementById('video-upload').value = '';
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    };
}

// Alpine.js component for thumbnail upload
function thumbnailUploadComponent() {
    return {
        thumbName: '',
        thumbPreview: '',
        thumbUploading: false,
        thumbPath: '',
        thumbCompleted: false,
        
        async handleThumbSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.thumbName = file.name;
                
                // Start upload immediately
                await this.uploadThumbnail(file);
            }
        },
        
        async uploadThumbnail(file) {
            const formData = new FormData();
            formData.append('thumbnail', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            this.thumbUploading = true;
            this.thumbCompleted = false;
            
            try {
                const response = await fetch('{{ route("admin.videos.upload-thumbnail") }}', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.thumbPath = data.path;
                    this.thumbPreview = data.url;
                    this.thumbCompleted = true;
                    
                    console.log('Thumbnail upload success:', data);
                } else {
                    alert('Lỗi: ' + data.message);
                    this.clearThumb();
                }
            } catch (error) {
                alert('Có lỗi xảy ra: ' + error.message);
                this.clearThumb();
            } finally {
                this.thumbUploading = false;
            }
        },
        
        clearThumb() {
            this.thumbName = '';
            this.thumbPreview = '';
            this.thumbPath = '';
            this.thumbCompleted = false;
            this.thumbUploading = false;
            document.getElementById('thumb-upload').value = '';
        }
    };
}

// Validation trước khi submit
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').addEventListener('submit', function(e) {
        const videoPath = document.querySelector('input[name="video_path"]').value;
        const videoSize = document.querySelector('input[name="video_size"]').value;
        const videoMimeType = document.querySelector('input[name="video_mime_type"]').value;
        
        console.log('Form validation:', { videoPath, videoSize, videoMimeType });
        
        if (!videoPath || !videoSize || !videoMimeType) {
            e.preventDefault();
            alert('Vui lòng chọn và tải lên video trước khi lưu!');
            return false;
        }
        
        // Show loading state
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu...';
        }
    });
});
</script>
@endsection