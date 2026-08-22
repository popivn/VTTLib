@extends('layouts.site')

@php
// Trích xuất dữ liệu MARC trực tiếp để đảm bảo không bị lỗi undefined variable
$marcData = [];
foreach ($record->fields as $field) {
$subfields = [];
foreach ($field->subfields as $sub) {
$subfields[$sub->code] = $sub->value;
}
$marcData[$field->tag] = $subfields;
}

// Nhan đề chính ($a $b)
$displayTitle = ($marcData['245']['a'] ?? 'Không có nhan đề') .
(isset($marcData['245']['b']) ? ' ' . $marcData['245']['b'] : '');

// Nhan đề đầy đủ bao gồm thông tin trách nhiệm ($c)
$fullTitle = $displayTitle . (isset($marcData['245']['c']) ? ' / ' . $marcData['245']['c'] : '');

// Tác giả (100$a hoặc 700$a)
$author = $marcData['100']['a'] ?? ($marcData['700']['a'] ?? '');

// Thông tin xuất bản đã được làm sạch
$pubPlace = rtrim(trim($marcData['260']['a'] ?? ($marcData['264']['a'] ?? '')), ' :-/;');
$publisher = rtrim(trim($marcData['260']['b'] ?? ($marcData['264']['b'] ?? '')), ' :-/;');
$pubYear = rtrim(trim($marcData['260']['c'] ?? ($marcData['264']['c'] ?? '')), ' :-/;.[]');

// Mô tả vật lý (300$a $b $c)
$physPages = trim($marcData['300']['a'] ?? '');
$physDetails = trim($marcData['300']['b'] ?? '');
$physSize = trim($marcData['300']['c'] ?? '');
$physDesc = trim(($physPages ? $physPages . ' ' : '') . ($physDetails ? $physDetails . ', ' : '') . ($physSize ? $physSize : ''));

// Phân loại DDC và Cutter
$ddcVal = $marcData['082']['a'] ?? ($marcData['090']['a'] ?? '');
$cutterVal = $marcData['082']['b'] ?? ($marcData['090']['b'] ?? '');

// Thông tin trách nhiệm (245$c)
$responsibility = $marcData['245']['c'] ?? '';

// Trích xuất danh sách chủ đề (đầu thẻ 6xx)
$subjects = [];
foreach ($record->fields as $field) {
if (str_starts_with($field->tag, '6')) {
foreach ($field->subfields as $sub) {
if ($sub->code === 'a') {
$subjects[] = trim($sub->value, ' .,-:');
}
}
}
}
$subjects = array_unique(array_filter($subjects));

// Tóm tắt (520$a)
$summary = $marcData['520']['a'] ?? 'Nội dung đang được cập nhật...';
@endphp

@section('title', Str::limit($displayTitle, 45) . ' | Thư viện VTTU')

@section('meta-description')
    <meta name="description" content="{{ Str::limit(strip_tags('Sách: ' . $displayTitle . ($author ? ' - Tác giả: ' . $author : '') . ($pubYear ? ' (' . $pubYear . ')' : '') . '. ' . ($summary !== 'Nội dung đang được cập nhật...' ? $summary : 'Thông tin tài liệu tại Thư viện Đại học Võ Trường Toản.')), 150) }}">
@endsection

@section('content')
<div class="bg-slate-50 min-h-screen pt-16 pb-8">
    <div class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="flex mb-4 text-sm font-medium" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('home') }}" class="text-slate-400 hover:text-vttu-red transition-colors">Trang chủ</a></li>
                <li><i class="fas fa-chevron-right text-[10px] text-slate-300"></i></li>
                <li><a href="{{ route('site.opac') }}" class="text-slate-400 hover:text-vttu-red transition-colors">Tra cứu OPAC</a></li>
                <li><i class="fas fa-chevron-right text-[10px] text-slate-300"></i></li>
                <li class="text-vttu-dark truncate max-w-[200px] md:max-w-md">{{ $displayTitle }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT: Book Cover & Quick Actions -->
            <div class="lg:col-span-4 space-y-5">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 flex flex-col items-center sticky top-24">
                    <div class="w-full aspect-[3/4] bg-slate-50 rounded-xl overflow-hidden shadow-md mb-5 border border-slate-100 relative group">
                        @if($record->cover_image)
                        <img src="{{ asset('storage/' . $record->cover_image) }}" class="w-full h-full object-contain">
                        @else
                        <img src="{{ asset('assets/imgs/books/noimage.png') }}" class="w-full h-full object-contain">
                        @endif
                        <div class="absolute top-4 right-4">
                            <span class="px-4 py-1.5 bg-vttu-red text-white rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg">{{ $record->record_type ?? 'Sách' }}</span>
                        </div>
                    </div>

                    <div class="w-full space-y-2.5">
                        @if($record->items->where('status', 'available')->count() > 0)
                        <button type="button" onclick="confirmReservation({{ $record->id }}, '{{ addslashes($fullTitle) }}')" class="w-full py-2.5 bg-vttu-red text-white rounded-lg font-black uppercase text-xs tracking-wider hover:bg-vttu-dark transition-all shadow-md flex items-center justify-center gap-2">
                            <i class="fas fa-shopping-basket"></i>
                            Đăng ký mượn ngay
                        </button>
                        @else
                        <button disabled class="w-full py-2.5 bg-slate-200 text-slate-400 rounded-lg font-black uppercase text-xs tracking-wider cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="fas fa-clock"></i>
                            Tài liệu tạm hết
                        </button>
                        @endif

                        <button class="w-full py-2.5 bg-white text-vttu-dark border border-slate-200 rounded-lg font-black uppercase text-xs tracking-wider hover:border-vttu-red/20 hover:text-vttu-red transition-all flex items-center justify-center gap-2">
                            <i class="far fa-heart"></i>
                            Thêm vào yêu thích
                        </button>
                    </div>

                    <!-- Additional Metadata -->
                    <div class="w-full mt-5 pt-4 border-t border-slate-100 grid grid-cols-2 gap-4 text-center">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Lượt xem</p>
                            <p class="text-lg font-black text-vttu-dark">{{ number_format($record->view_count ?? 0) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Lượt mượn</p>
                            <p class="text-lg font-black text-vttu-dark">{{ number_format($record->loan_count ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Book Information -->
            <div class="lg:col-span-8 space-y-5">

                <!-- Main Info Section -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
                    <div class="mb-5 pb-4 border-b border-slate-100">
                        <h1 class="text-xl md:text-2xl font-bold text-vttu-dark tracking-tight leading-snug mb-2">
                            {{ $displayTitle }}
                        </h1>
                        @if(isset($marcData['245']['c']))
                        <p class="text-xs text-slate-500 font-medium mb-4 italic flex items-center gap-1.5">
                            <i class="fas fa-info-circle text-[10px] text-vttu-red"></i>
                            {{ $marcData['245']['c'] }}
                        </p>
                        @endif
                        <div class="flex flex-wrap items-center gap-6">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-vttu-red/10 flex items-center justify-center text-vttu-red">
                                    <i class="fas fa-user-edit text-[10px]"></i>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Tác giả</p>
                                    <p class="text-sm font-bold text-vttu-dark leading-none">{{ $author }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                    <i class="fas fa-building text-[10px]"></i>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Nhà xuất bản</p>
                                    <p class="text-sm font-bold text-vttu-dark leading-none">{{ $publisher }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                    <i class="fas fa-calendar-alt text-[10px]"></i>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Năm XB</p>
                                    <p class="text-sm font-bold text-vttu-dark leading-none">{{ $pubYear ?: 'Đang cập nhật' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Information -->
                    <div class="space-y-4">
                        <h3 class="flex items-center gap-2.5 text-xs font-black text-vttu-dark uppercase tracking-wider mb-4">
                            <span class="w-6 h-1 bg-vttu-red rounded-full"></span>
                            Thông tin chi tiết
                        </h3>
                        <div class="space-y-3">
                            <!-- Tác giả -->
                            @if(!empty($author))
                            <div class="flex items-start">
                                <div class="w-48 text-slate-500 font-medium py-1.5 shrink-0 text-sm">Tác giả:</div>
                                <div class="flex-1 flex flex-wrap gap-2">
                                    <span class="px-3.5 py-1 bg-sky-50 text-sky-600 font-semibold rounded-full text-xs">
                                        {{ $author }}
                                    </span>
                                </div>
                            </div>
                            @endif

                            <!-- Xuất bản -->
                            <div class="flex items-start">
                                <div class="w-48 text-slate-500 font-medium py-1.5 shrink-0 text-sm">Xuất bản:</div>
                                <div class="flex-1 flex flex-wrap items-center gap-2 text-sm text-sky-600 font-semibold">
                                    @if($pubPlace)
                                    <span class="text-slate-600 font-medium">{{ $pubPlace }} :</span>
                                    @endif
                                    @if($publisher)
                                    <span class="px-3.5 py-1 bg-sky-50 text-sky-600 font-semibold rounded-full text-xs">{{ $publisher }}</span>
                                    @endif
                                    @if($pubYear)
                                    @if($publisher) <span class="text-slate-600 font-medium">,</span> @endif
                                    <span class="px-3.5 py-1 bg-sky-50 text-sky-600 font-semibold rounded-full text-xs">{{ $pubYear }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Mô tả vật lý -->
                            @if($physDesc)
                            <div class="flex items-start">
                                <div class="w-48 text-slate-500 font-medium py-1.5 shrink-0 text-sm">Mô tả vật lý:</div>
                                <div class="flex-1 text-sky-600 text-sm font-semibold py-1.5 leading-relaxed">
                                    {{ $physDesc }}
                                </div>
                            </div>
                            @endif

                            <!-- Chủ đề -->
                            @if(!empty($subjects))
                            <div class="flex items-start">
                                <div class="w-48 text-slate-500 font-medium py-1.5 shrink-0 text-sm">Chủ đề :</div>
                                <div class="flex-1 flex flex-wrap gap-2">
                                    @foreach($subjects as $subj)
                                    <span class="px-3.5 py-1 bg-sky-50 text-sky-600 font-semibold rounded-full text-xs">
                                        {{ $subj }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Ký hiệu phân loại -->
                            @if($ddcVal || $cutterVal)
                            <div class="flex items-start">
                                <div class="w-48 text-slate-500 font-medium py-1.5 shrink-0 text-sm">Ký hiệu phân loại:</div>
                                <div class="flex-1 flex flex-wrap gap-2">
                                    @if($ddcVal)
                                    <span class="px-3.5 py-1 bg-sky-50 text-sky-600 font-semibold rounded-full text-xs">
                                        {{ $ddcVal }}
                                    </span>
                                    @endif
                                    @if($cutterVal)
                                    <span class="px-3.5 py-1 bg-sky-50 text-sky-600 font-semibold rounded-full text-xs">
                                        {{ $cutterVal }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Thông tin trách nhiệm -->
                            @if($responsibility)
                            <div class="flex items-start">
                                <div class="w-48 text-slate-500 font-medium py-1.5 shrink-0 text-sm">Thông tin trách nhiệm:</div>
                                <div class="flex-1 text-sky-600 text-sm font-semibold py-1.5 leading-relaxed">
                                    {{ $responsibility }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Availability Section -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
                    <h3 class="flex items-center gap-2.5 text-xs font-black text-vttu-dark uppercase tracking-wider mb-4">
                        <span class="w-6 h-1 bg-emerald-500 rounded-full"></span>
                        Trạng thái các ấn phẩm hiện có
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b border-slate-100">
                                    <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Mã vạch</th>
                                    <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kho tài liệu</th>
                                    <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($record->items as $item)
                                <tr>
                                    <td class="py-3 font-mono text-xs font-bold text-vttu-dark">{{ $item->barcode }}</td>
                                    <td class="py-3 text-xs font-bold text-slate-600">{{ $item->storageLocation->name ?? 'N/A' }}</td>
                                    <td class="py-3 text-center">
                                        @if($item->status == 'available')
                                            <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase rounded-md">Có thể mượn</span>
                                        @elseif($item->status == 'reserved')
                                            <span class="px-2.5 py-0.5 bg-amber-50 text-amber-600 text-[10px] font-bold uppercase rounded-md">Đang giữ lại</span>
                                        @elseif($item->status == 'on_loan')
                                            <span class="px-2.5 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold uppercase rounded-md">Đã cho mượn</span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-rose-50 text-rose-500 text-[10px] font-bold uppercase rounded-md">Đang giữ lại</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary Section (Trường 520) -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
                    <h3 class="flex items-center gap-2.5 text-xs font-black text-vttu-dark uppercase tracking-wider mb-4">
                        <span class="w-6 h-1 bg-vttu-red rounded-full"></span>
                        Tóm tắt nội dung
                    </h3>
                    <div class="prose prose-slate max-w-none">
                        <p class="text-slate-600 leading-relaxed text-xs font-medium bg-slate-50 p-4 rounded-xl border border-slate-100 italic">
                            "{{ $summary }}"
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    @keyframes swal-book-float {
        0% {
            transform: translateY(100vh) translateX(0) rotate(0deg);
            opacity: 0;
        }

        20% {
            opacity: 1;
        }

        80% {
            opacity: 1;
        }

        100% {
            transform: translateY(-20vh) translateX(100px) rotate(360deg);
            opacity: 0;
        }
    }

    .swal2-container .floating-book {
        position: fixed;
        color: rgba(255, 255, 255, 0.3);
        font-size: 2rem;
        pointer-events: none;
        z-index: -1;
        animation: swal-book-float linear infinite;
    }
</style>
<script>
    function createFloatingBooks() {
        const container = document.querySelector('.swal2-container');
        const icons = ['fa-book', 'fa-book-open', 'fa-journal-whills', 'fa-book-bookmark', 'fa-library'];
        for (let i = 0; i < 15; i++) {
            const book = document.createElement('i');
            const icon = icons[Math.floor(Math.random() * icons.length)];
            book.className = `fas ${icon} floating-book`;
            book.style.left = `${Math.random() * 100}vw`;
            book.style.animationDuration = `${3 + Math.random() * 4}s`;
            book.style.animationDelay = `${Math.random() * 2}s`;
            book.style.fontSize = `${1 + Math.random() * 2}rem`;
            container.appendChild(book);
        }
    }

    function confirmReservation(id, title) {
        Swal.fire({
            title: 'Xác nhận mượn sách?',
            html: `Bạn muốn đăng ký mượn cuốn:<br><b class="text-vttu-red">${title}</b>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#680102',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Đăng ký ngay',
            cancelButtonText: 'Hủy bỏ',
            borderRadius: '2rem',
            didOpen: () => {
                createFloatingBooks();
            },
            customClass: {
                popup: 'rounded-[2.5rem] border-4 border-vttu-red/10 shadow-2xl',
                confirmButton: 'rounded-2xl px-8 py-4 font-black uppercase text-xs tracking-[0.2em] shadow-lg shadow-vttu-red/20',
                cancelButton: 'rounded-2xl px-8 py-4 font-black uppercase text-xs tracking-[0.2em]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Hiển thị loading
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Gửi AJAX
                fetch(`/opac/book/${id}/reserve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async response => {
                        const isJson = response.headers.get('content-type')?.includes('application/json');
                        const data = isJson ? await response.json() : null;

                        if (!response.ok) {
                            throw new Error(data?.message || 'Có lỗi xảy ra từ máy chủ.');
                        }
                        return data;
                    })
                    .then(data => {
                        if (data && data.success) {
                            Swal.fire({
                                title: 'Thành công!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#680102',
                                borderRadius: '2rem'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data?.message || 'Đăng ký không thành công.');
                        }
                    })
                    .catch(error => {
                        console.error('Reservation error:', error);
                        Swal.fire({
                            title: 'Thất bại!',
                            text: error.message || 'Không thể kết nối tới hệ thống.',
                            icon: 'error',
                            confirmButtonColor: '#680102',
                            borderRadius: '2rem'
                        });
                    });
            }
        });
    }
</script>
@endsection
@endsection