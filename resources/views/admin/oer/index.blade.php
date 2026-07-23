@extends('layouts.admin')

@section('title', 'Quản lý Tài nguyên giáo dục mở (OER)')

@section('content')
<div class="p-6 space-y-6" x-data="{ addModalOpen: false, editModalOpen: false, activeItem: {} }">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-black text-[#8b0000] flex items-center gap-3">
                <i class="fas fa-book-open text-xl"></i>
                Quản Lý Tài Nguyên Giáo Dục Mở (OER)
            </h1>
            <p class="text-sm text-slate-500 mt-1">Quản lý kho sách, giáo trình và tài nguyên mở cho sinh viên & giảng viên</p>
        </div>
        <button @click="addModalOpen = true" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#8b0000] hover:bg-[#680102] text-white text-xs font-bold rounded-lg shadow-sm transition-all active:scale-95">
            <i class="fas fa-plus"></i>
            Thêm Tài Nguyên Mới
        </button>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-check text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('admin.oer.index') }}" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-1">
            <!-- Subject Filter -->
            <div class="relative w-full sm:w-56">
                <select name="subject_id" onchange="this.form.submit()" class="w-full h-10 pl-3 pr-8 text-xs font-bold bg-slate-50 border border-slate-200 rounded-lg appearance-none outline-none focus:border-[#8b0000]">
                    <option value="">-- Tất cả chủ đề --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>
                            {{ $sub->name }}
                        </option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"></i>
            </div>

            <!-- Keyword Search Input -->
            <div class="relative flex-1">
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}"
                       placeholder="Nhập tên tài nguyên, tác giả, nhà xuất bản..." 
                       class="w-full h-10 pl-10 pr-4 text-xs bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-[#8b0000]">
                <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
            </div>

            <button type="submit" class="px-5 h-10 bg-[#8b0000] text-white text-xs font-bold rounded-lg hover:bg-[#680102] transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-filter"></i> Lọc
            </button>

            @if(request('q') || request('subject_id'))
                <a href="{{ route('admin.oer.index') }}" class="px-4 h-10 bg-slate-200 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-300 transition-colors flex items-center justify-center gap-1.5" title="Xóa bộ lọc">
                    <i class="fas fa-rotate-left"></i> Đặt lại
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-[11px] font-black uppercase text-slate-500 border-b border-slate-200 tracking-wider">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Tên Tài Nguyên</th>
                        <th class="py-3 px-4 w-44">Chủ đề</th>
                        <th class="py-3 px-4 w-44 text-center">Tác giả / NXB</th>
                        <th class="py-3 px-4 w-28 text-center">Giấy phép</th>
                        <th class="py-3 px-4 w-24 text-center">Trạng thái</th>
                        <th class="py-3 px-4 w-28 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    @forelse($resources as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                {{ $resources->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-16 bg-slate-100 rounded border border-slate-200 overflow-hidden flex-shrink-0">
                                        <img src="{{ asset($item->thumbnail_url) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-bold text-slate-800 hover:text-[#8b0000] line-clamp-2 leading-snug">
                                            {{ $item->title }}
                                        </h4>
                                        <a href="{{ $item->url }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-blue-600 hover:underline">
                                            <i class="fas fa-arrow-up-right-from-square text-[9px]"></i> Mở liên kết đọc sách
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 font-bold rounded text-[10px]">
                                    {{ $item->subject ? $item->subject->name : 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center space-y-0.5">
                                <div class="font-bold text-slate-700 line-clamp-1">{{ $item->author ?: 'N/A' }}</div>
                                <div class="text-[10px] text-slate-400 line-clamp-1">{{ $item->publisher ?: 'N/A' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-600 font-bold rounded text-[10px]">
                                    {{ $item->license ?: 'CC BY-NC' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-full text-[10px]">
                                        <i class="fas fa-circle-check text-[9px]"></i> Khả dụng
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-700 font-bold rounded-full text-[10px]">
                                        <i class="fas fa-circle-minus text-[9px]"></i> Ẩn
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Edit Button -->
                                    <button @click="activeItem = {{ json_encode($item) }}; editModalOpen = true" 
                                            class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-colors" title="Sửa">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('admin.oer.destroy', $item->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition-colors" title="Xóa">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fas fa-book-open text-4xl mb-3"></i>
                                <p class="text-xs font-bold uppercase tracking-wider">Không tìm thấy tài nguyên giáo dục mở nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($resources->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $resources->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Add OER Item -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg font-black text-[#8b0000] flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Thêm Tài Nguyên Giáo Dục Mở
                </h3>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.oer.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Tên Tài Nguyên <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Chủ Đề <span class="text-rose-500">*</span></label>
                        <select name="subject_id" required class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Giấy Phép</label>
                        <input type="text" name="license" placeholder="VD: CC BY-NC" class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Đường Dẫn Đọc/Tải Sách (URL) <span class="text-rose-500">*</span></label>
                    <input type="url" name="url" required placeholder="https://..." class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Tác Giả</label>
                        <input type="text" name="author" class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Nhà Xuất Bản</label>
                        <input type="text" name="publisher" class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Ảnh Bìa (Upload File hoặc URL Ảnh)</label>
                    <input type="file" name="thumbnail" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#8b0000] file:text-white hover:file:bg-[#680102]">
                    <input type="text" name="thumbnail_url" placeholder="Hoặc dán URL ảnh bìa tại đây..." class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000] mt-1">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Mô Tả Chú Thích</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked id="add_active" class="rounded border-slate-300 text-[#8b0000] focus:ring-[#8b0000]">
                    <label for="add_active" class="text-xs font-bold text-slate-700">Hiển thị công khai trên website</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-300">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-[#8b0000] text-white text-xs font-bold rounded-lg hover:bg-[#680102]">Lưu Tài Nguyên</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit OER Item -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg font-black text-[#8b0000] flex items-center gap-2">
                    <i class="fas fa-pen-to-square"></i> Cập Nhật Tài Nguyên Giáo Dục Mở
                </h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" :action="'/admin/oer/' + activeItem.id" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Tên Tài Nguyên <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" x-model="activeItem.title" required class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Chủ Đề <span class="text-rose-500">*</span></label>
                        <select name="subject_id" x-model="activeItem.subject_id" required class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Giấy Phép</label>
                        <input type="text" name="license" x-model="activeItem.license" placeholder="VD: CC BY-NC" class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Đường Dẫn Đọc/Tải Sách (URL) <span class="text-rose-500">*</span></label>
                    <input type="url" name="url" x-model="activeItem.url" required placeholder="https://..." class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Tác Giả</label>
                        <input type="text" name="author" x-model="activeItem.author" class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Nhà Xuất Bản</label>
                        <input type="text" name="publisher" x-model="activeItem.publisher" class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Ảnh Bìa (Upload File hoặc URL Ảnh)</label>
                    <input type="file" name="thumbnail" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#8b0000] file:text-white hover:file:bg-[#680102]">
                    <input type="text" name="thumbnail_url" x-model="activeItem.thumbnail_url" placeholder="Hoặc dán URL ảnh bìa tại đây..." class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000] mt-1">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Mô Tả Chú Thích</label>
                    <textarea name="description" x-model="activeItem.description" rows="3" class="w-full px-3 py-2 text-xs border rounded-lg outline-none focus:border-[#8b0000]"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" :checked="activeItem.is_active" id="edit_active" class="rounded border-slate-300 text-[#8b0000] focus:ring-[#8b0000]">
                    <label for="edit_active" class="text-xs font-bold text-slate-700">Hiển thị công khai trên website</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-300">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-[#8b0000] text-white text-xs font-bold rounded-lg hover:bg-[#680102]">Cập Nhật</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
