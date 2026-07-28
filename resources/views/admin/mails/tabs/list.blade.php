<!-- Mail List Tab -->
<div class="space-y-4">
    <!-- Filter Form -->
    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-100 dark:border-slate-700">
        <form method="GET" action="{{ route('admin.mails.index', ['tab' => 'list']) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Tìm kiếm người nhận</label>
                <input type="email" name="recipient" placeholder="email@example.com" value="{{ request('recipient') }}" 
                    class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Chủ đề</label>
                <input type="text" name="subject" placeholder="Chủ đề email" value="{{ request('subject') }}" 
                    class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Trạng thái</label>
                <select name="status" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    <option value="">-- Tất cả --</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Đã gửi</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Tìm kiếm
                </button>
                <a href="{{ route('admin.mails.index', ['tab' => 'list']) }}" class="px-4 py-2 text-sm font-semibold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                    Xóa
                </a>
            </div>
        </form>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 p-4 rounded-lg text-sm">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 p-4 rounded-lg text-sm">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Email List Table -->
    <div class="overflow-x-auto border border-slate-100 dark:border-slate-800 rounded-lg">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800">
                    <th class="px-6 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Người nhận</th>
                    <th class="px-6 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Chủ đề</th>
                    <th class="px-6 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Trạng thái</th>
                    <th class="px-6 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Gửi lúc</th>
                    <th class="px-6 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($mails ?? [] as $mail)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200">{{ $mail->recipient }}</td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300 max-w-xs truncate">{{ $mail->subject }}</td>
                        <td class="px-6 py-4">
                            @if($mail->status === 'sent')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>
                                    Đã gửi
                                </span>
                            @elseif($mail->status === 'failed')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-950/30 text-red-700 dark:text-red-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                    Thất bại
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                    Đang chờ
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-xs">
                            {{ $mail->sent_at ? \Carbon\Carbon::parse($mail->sent_at)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.mails.show', $mail->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('admin.mails.resend', $mail->id) }}" method="POST" class="inline" onsubmit="return confirm('Gửi lại email này?')">
                                    @csrf
                                    <button type="submit" class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 transition-colors">
                                        <i data-lucide="repeat" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.mails.destroy', $mail->id) }}" method="POST" class="inline" onsubmit="return confirm('Xóa email này khỏi logs?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                            <p>Không có email nào</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($mails ?? false)
        <div class="mt-6">
            {{ $mails->links() }}
        </div>
    @endif
</div>
