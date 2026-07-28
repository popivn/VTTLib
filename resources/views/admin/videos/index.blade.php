@extends('layouts.admin')

@section('title', 'Quản lý Video')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground tracking-tight">{{ __('Quản lý Video') }}</h1>
            <p class="text-sm text-muted-foreground">{{ __('Quản lý video bài giảng và tài liệu học tập') }}</p>
        </div>
        <a href="{{ route('admin.videos.create') }}" 
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded text-sm font-medium transition-all duration-200 active:scale-95 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm">
            <i class="fas fa-plus w-4 h-4"></i>
            {{ __('Thêm Video') }}
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="bg-card rounded-lg border shadow-sm mb-6">
        <div class="p-3 bg-muted/30 border-b border-border">
            <form method="GET" class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1 sm:max-w-xs">
                    <input type="text" name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Tìm kiếm video..."
                           class="w-full h-9 pl-9 pr-3 text-sm border border-input rounded bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground"></i>
                </div>

                <select name="status" 
                        class="h-9 px-3 text-sm border border-input rounded bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hiển thị</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Đã ẩn</option>
                </select>

                <button type="submit" 
                        class="h-9 px-4 text-sm font-medium rounded transition-all duration-200 bg-primary text-primary-foreground hover:bg-primary/90">
                    <i class="fas fa-search mr-1"></i>
                    Tìm kiếm
                </button>

                @if(request('search') || request('status'))
                    <a href="{{ route('admin.videos.index') }}" 
                       class="h-9 px-4 text-sm font-medium rounded transition-all duration-200 bg-muted text-muted-foreground hover:bg-muted/80 border border-border hover:text-foreground flex items-center">
                        <i class="fas fa-times mr-1"></i>
                        Xóa bộ lọc
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Videos Table -->
    <div class="bg-card rounded-lg border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr class="border-b border-border text-left">
                        <th class="py-3 px-4 font-semibold text-foreground text-sm">Video</th>
                        <th class="py-3 px-4 font-semibold text-foreground text-sm">Thông tin</th>
                        <th class="py-3 px-4 font-semibold text-foreground text-sm">Trạng thái</th>
                        <th class="py-3 px-4 font-semibold text-foreground text-sm">Người tạo</th>
                        <th class="py-3 px-4 font-semibold text-foreground text-sm">Ngày tạo</th>
                        <th class="py-3 px-4 font-semibold text-foreground text-sm text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border text-sm">
                    @forelse($videos as $video)
                        <tr class="hover:bg-muted/30 transition-colors">
                            <!-- Video Info -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-16 h-12 rounded bg-muted flex items-center justify-center overflow-hidden flex-shrink-0">
                                        @if($video->thumbnail_url)
                                            <img src="{{ $video->thumbnail_url }}" alt="Thumbnail" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-video text-muted-foreground"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-foreground truncate">{{ $video->title }}</h3>
                                        @if($video->description)
                                            <p class="text-xs text-muted-foreground line-clamp-2">{{ $video->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- File Info -->
                            <td class="py-3 px-4">
                                <div class="text-xs text-muted-foreground space-y-1">
                                    <div><span class="font-medium">Kích thước:</span> {{ $video->formatted_size }}</div>
                                    @if($video->duration)
                                        <div><span class="font-medium">Thời lượng:</span> {{ $video->duration }}</div>
                                    @endif
                                    @if($video->mime_type)
                                        <div><span class="font-medium">Định dạng:</span> {{ strtoupper(pathinfo($video->path, PATHINFO_EXTENSION)) }}</div>
                                    @endif
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                <button onclick="toggleStatus({{ $video->id }}, {{ $video->is_active ? 'false' : 'true' }})"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full transition-colors
                                               {{ $video->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                    <i class="fas {{ $video->is_active ? 'fa-eye' : 'fa-eye-slash' }} mr-1"></i>
                                    {{ $video->is_active ? 'Hiển thị' : 'Đã ẩn' }}
                                </button>
                            </td>

                            <!-- Creator -->
                            <td class="py-3 px-4">
                                <div class="text-xs">
                                    @if($video->creator)
                                        <div class="font-medium text-foreground">{{ $video->creator->name }}</div>
                                        <div class="text-muted-foreground">{{ $video->creator->username }}</div>
                                    @else
                                        <span class="text-muted-foreground">—</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Created At -->
                            <td class="py-3 px-4">
                                <div class="text-xs text-muted-foreground">
                                    {{ $video->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="copyVideoLink('{{ $video->video_url }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded text-xs bg-purple-100 text-purple-600 hover:bg-purple-200 transition-colors"
                                            title="Copy link video">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    
                                    <a href="{{ $video->video_url }}" target="_blank"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded text-xs bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors"
                                       title="Xem video">
                                        <i class="fas fa-play"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.videos.edit', $video) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded text-xs bg-orange-100 text-orange-600 hover:bg-orange-200 transition-colors"
                                       title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button onclick="deleteVideo({{ $video->id }})"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded text-xs bg-red-100 text-red-600 hover:bg-red-200 transition-colors"
                                            title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fas fa-video text-4xl text-muted-foreground/50"></i>
                                    <p class="text-muted-foreground">Chưa có video nào</p>
                                    <a href="{{ route('admin.videos.create') }}" class="text-primary hover:underline">Thêm video đầu tiên</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($videos->hasPages())
            <div class="px-4 py-3 border-t border-border">
                {{ $videos->links() }}
            </div>
        @endif
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
            location.reload();
        } else {
            alert('Có lỗi xảy ra khi xóa video');
        }
    } catch (error) {
        alert('Có lỗi xảy ra: ' + error.message);
    }
}

function copyVideoLink(url) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            // Show success message
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.remove('bg-purple-100', 'text-purple-600');
            button.classList.add('bg-green-100', 'text-green-600');
            
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.classList.remove('bg-green-100', 'text-green-600');
                button.classList.add('bg-purple-100', 'text-purple-600');
            }, 2000);
        }).catch(() => {
            fallbackCopyTextToClipboard(url);
        });
    } else {
        fallbackCopyTextToClipboard(url);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        alert('Đã copy link video!');
    } catch (err) {
        alert('Không thể copy link. Vui lòng copy thủ công: ' + text);
    }
    
    document.body.removeChild(textArea);
}
</script>
@endsection