@extends('layouts.admin')

@section('title', 'Chi tiết Video')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.videos.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-muted hover:bg-muted/80 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-foreground">{{ __('Chi tiết Video') }}</h1>
                <p class="text-sm text-muted-foreground">{{ $video->title }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Video Player -->
            <div class="lg:col-span-2">
                <div class="bg-card rounded-lg border shadow-sm overflow-hidden">
                    <div class="aspect-video bg-black relative">
                        <video controls class="w-full h-full" preload="metadata">
                            <source src="{{ $video->video_url }}" type="{{ $video->mime_type }}">
                            Trình duyệt của bạn không hỗ trợ video HTML5.
                        </video>
                    </div>
                    
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-foreground mb-2">{{ $video->title }}</h2>
                        @if($video->description)
                            <p class="text-sm text-muted-foreground leading-relaxed">{{ $video->description }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Video Information -->
            <div class="space-y-6">
                <!-- Basic Info -->
                <div class="bg-card rounded-lg border shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-foreground mb-4">{{ __('Thông tin video') }}</h3>
                    
                    <div class="space-y-3">
                        <!-- Status -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Trạng thái:</span>
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                         {{ $video->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas {{ $video->is_active ? 'fa-eye' : 'fa-eye-slash' }} mr-1"></i>
                                {{ $video->is_active ? 'Hiển thị' : 'Đã ẩn' }}
                            </span>
                        </div>

                        <!-- File Size -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Kích thước:</span>
                            <span class="text-sm font-medium text-foreground">{{ $video->formatted_size }}</span>
                        </div>

                        <!-- Duration -->
                        @if($video->duration)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Thời lượng:</span>
                                <span class="text-sm font-medium text-foreground">{{ $video->duration }}</span>
                            </div>
                        @endif

                        <!-- Format -->
                        @if($video->mime_type)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Định dạng:</span>
                                <span class="text-sm font-medium text-foreground">{{ strtoupper(pathinfo($video->path, PATHINFO_EXTENSION)) }}</span>
                            </div>
                        @endif

                        <!-- Sort Order -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Thứ tự:</span>
                            <span class="text-sm font-medium text-foreground">{{ $video->sort_order }}</span>
                        </div>

                        <!-- Creator -->
                        @if($video->creator)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Người tạo:</span>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-foreground">{{ $video->creator->name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $video->creator->username }}</div>
                                </div>
                            </div>
                        @endif

                        <!-- Created Date -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Ngày tạo:</span>
                            <span class="text-sm font-medium text-foreground">{{ $video->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <!-- Updated Date -->
                        @if($video->updated_at != $video->created_at)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Cập nhật:</span>
                                <span class="text-sm font-medium text-foreground">{{ $video->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Thumbnail -->
                @if($video->thumbnail_url)
                    <div class="bg-card rounded-lg border shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-foreground mb-4">{{ __('Ảnh thumbnail') }}</h3>
                        <img src="{{ $video->thumbnail_url }}" alt="Thumbnail" class="w-full rounded border">
                    </div>
                @endif

                <!-- Actions -->
                <div class="bg-card rounded-lg border shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-foreground mb-4">{{ __('Thao tác') }}</h3>
                    
                    <div class="space-y-3">
                        <!-- Edit -->
                        <a href="{{ route('admin.videos.edit', $video) }}"
                           class="flex items-center gap-3 p-3 rounded bg-orange-50 text-orange-700 hover:bg-orange-100 transition-colors">
                            <i class="fas fa-edit w-4 h-4"></i>
                            <span class="text-sm font-medium">Chỉnh sửa video</span>
                        </a>

                        <!-- Toggle Status -->
                        <button onclick="toggleStatus({{ $video->id }}, {{ $video->is_active ? 'false' : 'true' }})"
                                class="flex items-center gap-3 p-3 rounded w-full text-left transition-colors
                                       {{ $video->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                            <i class="fas {{ $video->is_active ? 'fa-eye-slash' : 'fa-eye' }} w-4 h-4"></i>
                            <span class="text-sm font-medium">{{ $video->is_active ? 'Ẩn video' : 'Hiển thị video' }}</span>
                        </button>

                        <!-- Download -->
                        <a href="{{ $video->video_url }}" download="{{ $video->title }}"
                           class="flex items-center gap-3 p-3 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                            <i class="fas fa-download w-4 h-4"></i>
                            <span class="text-sm font-medium">Tải xuống</span>
                        </a>

                        <!-- Delete -->
                        <button onclick="deleteVideo({{ $video->id }})"
                                class="flex items-center gap-3 p-3 rounded bg-red-50 text-red-700 hover:bg-red-100 transition-colors w-full text-left">
                            <i class="fas fa-trash w-4 h-4"></i>
                            <span class="text-sm font-medium">Xóa video</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function toggleStatus(videoId, newStatus) {
    try {
        const response = await fetch(`/topsecret/videos/${videoId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        });

        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    } catch (error) {
        alert('Có lỗi xảy ra: ' + error.message);
    }
}

async function deleteVideo(videoId) {
    if (!confirm('Bạn có chắc chắn muốn xóa video này? Hành động này không thể hoàn tác.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        const response = await fetch(`/topsecret/videos/${videoId}`, {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            window.location.href = '{{ route("admin.videos.index") }}';
        } else {
            alert('Có lỗi xảy ra khi xóa video');
        }
    } catch (error) {
        alert('Có lỗi xảy ra: ' + error.message);
    }
}
</script>
@endsection