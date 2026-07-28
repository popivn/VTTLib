@extends('layouts.admin')

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">Chi Tiết Email</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">ID: #{{ $mail->id }}</p>
        </div>
        <a href="{{ route('admin.mails.index', ['tab' => 'list']) }}" class="px-4 py-2 text-sm font-semibold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Quay Lại
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Email Content -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg overflow-hidden">
            <!-- Email Header -->
            <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 border-b border-slate-100 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white text-lg font-bold">
                            V
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">VTTLib</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Thư viện Đại học</p>
                        </div>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($mail->created_at)->format('d/m/Y H:i:s') }}</span>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">
                    <strong>Tới:</strong> {{ $mail->recipient }}
                </p>
                @if($mail->cc)
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">
                        <strong>CC:</strong> {{ $mail->cc }}
                    </p>
                @endif
                @if($mail->bcc)
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">
                        <strong>BCC:</strong> {{ $mail->bcc }}
                    </p>
                @endif
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-3">{{ $mail->subject }}</h2>
            </div>

            <div class="p-6 bg-slate-100 dark:bg-slate-950">
                <div class="w-full bg-white rounded-lg shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden" style="height: 600px;">
                    <iframe class="w-full h-full border-0" srcdoc="{{ $mail->body }}"></iframe>
                </div>
            </div>

            <!-- Email Footer -->
            <div class="bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 p-4 text-center text-xs text-slate-500 dark:text-slate-400">
                <p>© 2026 Thư viện Đại học. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Status Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg p-4">
                <h3 class="font-semibold text-slate-800 dark:text-slate-100 mb-3">Trạng Thái</h3>
                @if($mail->status === 'sent')
                    <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400">
                        <span class="w-2 h-2 rounded-full bg-green-600"></span>
                        Đã Gửi
                    </span>
                @elseif($mail->status === 'failed')
                    <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold bg-red-100 dark:bg-red-950/30 text-red-700 dark:text-red-400">
                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                        Thất Bại
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                        Đang Chờ
                    </span>
                @endif
            </div>

            <!-- Details Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg p-4">
                <h3 class="font-semibold text-slate-800 dark:text-slate-100 mb-3">Thông Tin Chi Tiết</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">ID Email</p>
                        <p class="text-sm font-mono text-slate-800 dark:text-slate-200">{{ $mail->id }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Người Nhận</p>
                        <p class="text-sm text-slate-800 dark:text-slate-200 break-all">{{ $mail->recipient }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Chủ Đề</p>
                        <p class="text-sm text-slate-800 dark:text-slate-200">{{ $mail->subject }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Ngày Tạo</p>
                        <p class="text-sm text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($mail->created_at)->format('d/m/Y H:i:s') }}</p>
                    </div>
                    @if($mail->sent_at)
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Thời Gian Gửi</p>
                            <p class="text-sm text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($mail->sent_at)->format('d/m/Y H:i:s') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Error Message (if failed) -->
            @if($mail->status === 'failed' && $mail->error_message)
                <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <h3 class="font-semibold text-red-700 dark:text-red-400 mb-2">Lỗi</h3>
                    <p class="text-sm text-red-700 dark:text-red-400 font-mono bg-red-100 dark:bg-red-900/50 p-2 rounded">
                        {{ $mail->error_message }}
                    </p>
                </div>
            @endif

            <!-- Actions -->
            <div class="space-y-2">
                @if($mail->status !== 'sent')
                    <form action="{{ route('admin.mails.resend', $mail->id) }}" method="POST" onsubmit="return confirm('Gửi lại email này?')">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-sm font-semibold bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="repeat" class="w-4 h-4"></i>
                            Gửi Lại
                        </button>
                    </form>
                @endif
                
                <button onclick="printMail()" class="w-full px-4 py-2 text-sm font-semibold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    In Email
                </button>

                <form action="{{ route('admin.mails.destroy', $mail->id) }}" method="POST" onsubmit="return confirm('Xóa email này khỏi logs?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 text-sm font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Xóa Email
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function printMail() {
        const subject = '{{ $mail->subject }}';
        const recipient = '{{ $mail->recipient }}';
        const body = `{{ addslashes($mail->body) }}`;
        const created = '{{ \Carbon\Carbon::parse($mail->created_at)->format('d/m/Y H:i:s') }}';

        const printContent = `
            <div style="font-family: Arial, sans-serif; padding: 20px;">
                <p><strong>Từ:</strong> admin@library.edu.vn</p>
                <p><strong>Tới:</strong> ${recipient}</p>
                <p><strong>Chủ đề:</strong> ${subject}</p>
                <p><strong>Gửi:</strong> ${created}</p>
                <hr>
                <div style="white-space: pre-wrap; color: #333;">
                    ${body}
                </div>
            </div>
        `;

        const printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }
</script>

<style>
    [data-lucide] {
        display: inline;
    }
</style>
@endsection
