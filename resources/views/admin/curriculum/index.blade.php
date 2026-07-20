@extends('layouts.admin')

@section('content')
<div class="space-y-4 animate-in fade-in duration-500" x-data="{ showAddModal: false, editModalData: null }">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-primary flex items-center justify-center text-primary-foreground shadow-sm">
                <i class="fas fa-graduation-cap text-base"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-foreground tracking-tight">{{ __('Quản lý Chương trình Đào tạo') }}</h1>
                <p class="text-sm text-muted-foreground">{{ __('Quản lý danh sách các ngành học, hình ảnh, mô tả và liên kết chi tiết.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('/chuong-trinh-dao-tao-vttu') }}" target="_blank" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-bold bg-muted hover:bg-muted/80 text-foreground border border-border transition-all shadow-sm">
                <i class="fas fa-external-link-alt text-xs"></i>
                <span>Xem trang thực tế</span>
            </a>
            <button @click="showAddModal = true" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded text-xs font-bold bg-primary hover:bg-primary/90 text-primary-foreground transition-all shadow-sm">
                <i class="fas fa-plus text-xs"></i>
                <span>Thêm Ngành Mới</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 text-sm rounded-md flex items-center gap-2">
            <i class="fas fa-check-circle text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Data Table Card -->
    <div class="bg-card rounded-md border border-border shadow-sm overflow-hidden">
        <div class="p-3 bg-muted/50 border-b border-border flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                <i class="fas fa-list text-primary"></i>
                <span>Danh sách Ngành Đào Tạo ({{ $majors->count() }})</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-muted/30 border-b border-border text-muted-foreground uppercase tracking-widest text-[10px] font-bold">
                    <tr>
                        <th class="p-3 text-center w-12">#</th>
                        <th class="p-3 w-24">Hình ảnh</th>
                        <th class="p-3 w-48">Tên ngành</th>
                        <th class="p-3">Mô tả chi tiết</th>
                        <th class="p-3 w-48">Liên kết ("Xem thêm")</th>
                        <th class="p-3 text-center w-20">Trạng thái</th>
                        <th class="p-3 text-center w-28">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($majors as $index => $item)
                        <tr class="hover:bg-muted/20 transition-colors">
                            <td class="p-3 text-center font-bold text-muted-foreground">{{ $item->sort_order ?: ($index + 1) }}</td>
                            <td class="p-3">
                                @if($item->image)
                                    <img src="{{ $item->image }}" alt="{{ $item->title }}" class="w-16 h-12 object-cover rounded border border-border">
                                @else
                                    <div class="w-16 h-12 bg-muted rounded border border-border flex items-center justify-center text-muted-foreground text-[10px]">No image</div>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="font-bold text-foreground block text-sm">{{ $item->title }}</span>
                            </td>
                            <td class="p-3 text-muted-foreground leading-relaxed">
                                <p class="line-clamp-2">{{ $item->description }}</p>
                            </td>
                            <td class="p-3">
                                @if($item->link_url)
                                    <a href="{{ $item->link_url }}" target="_blank" class="text-primary hover:underline font-mono text-[11px] truncate max-w-[180px] block" title="{{ $item->link_url }}">
                                        {{ $item->link_url }}
                                    </a>
                                @else
                                    <span class="text-muted-foreground italic text-[11px]">Chưa có link</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                @if($item->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Hiển thị</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">Ẩn</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button @click="editModalData = {{ json_encode($item) }}" 
                                            class="p-1.5 rounded hover:bg-muted text-muted-foreground hover:text-foreground transition-all" title="Chỉnh sửa">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    
                                    <form action="{{ route('admin.curriculum.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa ngành này?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded hover:bg-rose-500/10 text-muted-foreground hover:text-rose-500 transition-all" title="Xóa">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-muted-foreground">
                                Chưa có ngành đào tạo nào. Bấm nút "Thêm Ngành Mới" để tạo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Thêm Ngành Mới -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.away="showAddModal = false" class="bg-card rounded-lg border border-border shadow-xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-200">
            <div class="p-4 bg-muted/40 border-b border-border flex items-center justify-between">
                <h3 class="font-bold text-sm text-foreground flex items-center gap-2">
                    <i class="fas fa-plus text-primary"></i>
                    <span>Thêm Ngành Đào Tạo Mới</span>
                </h3>
                <button @click="showAddModal = false" class="text-muted-foreground hover:text-foreground"><i class="fas fa-times"></i></button>
            </div>

            <form action="{{ route('admin.curriculum.store') }}" method="POST" enctype="multipart/form-data" class="p-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Tên ngành <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required class="w-full px-3 py-2 text-xs border border-input rounded bg-background text-foreground" placeholder="Ví dụ: RĂNG - HÀM - MẶT">
                </div>

                <div>
                    <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Hình ảnh (Upload file hoặc nhập URL)</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-muted-foreground border border-input rounded p-1 mb-1">
                    <input type="text" name="image_url" class="w-full px-3 py-1.5 text-xs border border-input rounded bg-background text-foreground" placeholder="Hoặc dán URL ảnh tại đây: /storage/pages/...">
                </div>

                <div>
                    <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Mô tả ngành học</label>
                    <textarea name="description" rows="4" class="w-full p-2.5 text-xs border border-input rounded bg-background text-foreground" placeholder="Nhập tóm tắt mô tả về ngành đào tạo..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Đường dẫn liên kết (Link nút "Xem thêm")</label>
                    <input type="url" name="link_url" class="w-full px-3 py-2 text-xs border border-input rounded bg-background text-foreground" placeholder="https://www.canva.com/design/...">
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-border">
                    <div>
                        <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" value="1" class="w-full px-3 py-1.5 text-xs border border-input rounded bg-background text-foreground">
                    </div>
                    <div class="flex items-center pt-4">
                        <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-input text-primary">
                            <span>Hiển thị trên website</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="showAddModal = false" class="px-3 py-2 text-xs font-bold rounded bg-muted hover:bg-muted/80 text-foreground">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold rounded bg-primary hover:bg-primary/90 text-primary-foreground">Tạo mới</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Chỉnh Sửa Ngành -->
    <div x-show="editModalData !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.away="editModalData = null" class="bg-card rounded-lg border border-border shadow-xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-200">
            <div class="p-4 bg-muted/40 border-b border-border flex items-center justify-between">
                <h3 class="font-bold text-sm text-foreground flex items-center gap-2">
                    <i class="fas fa-edit text-primary"></i>
                    <span>Chỉnh Sửa Ngành Đào Tạo</span>
                </h3>
                <button @click="editModalData = null" class="text-muted-foreground hover:text-foreground"><i class="fas fa-times"></i></button>
            </div>

            <template x-if="editModalData">
                <form :action="'{{ url('/topsecret/curriculum') }}/' + editModalData.id" method="POST" enctype="multipart/form-data" class="p-4 space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Tên ngành <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" :value="editModalData.title" required class="w-full px-3 py-2 text-xs border border-input rounded bg-background text-foreground">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Hình ảnh</label>
                        <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-muted-foreground border border-input rounded p-1 mb-1">
                        <input type="text" name="image_url" :value="editModalData.image" class="w-full px-3 py-1.5 text-xs border border-input rounded bg-background text-foreground">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Mô tả ngành học</label>
                        <textarea name="description" rows="4" x-text="editModalData.description" class="w-full p-2.5 text-xs border border-input rounded bg-background text-foreground"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Đường dẫn liên kết (Link nút "Xem thêm")</label>
                        <input type="url" name="link_url" :value="editModalData.link_url" class="w-full px-3 py-2 text-xs border border-input rounded bg-background text-foreground">
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-border">
                        <div>
                            <label class="block text-xs font-bold text-muted-foreground uppercase mb-1">Thứ tự hiển thị</label>
                            <input type="number" name="sort_order" :value="editModalData.sort_order" class="w-full px-3 py-1.5 text-xs border border-input rounded bg-background text-foreground">
                        </div>
                        <div class="flex items-center pt-4">
                            <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" :checked="editModalData.is_active" class="rounded border-input text-primary">
                                <span>Hiển thị trên website</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="editModalData = null" class="px-3 py-2 text-xs font-bold rounded bg-muted hover:bg-muted/80 text-foreground">Hủy</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded bg-primary hover:bg-primary/90 text-primary-foreground">Cập nhật</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection
