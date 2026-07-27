@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="page-header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Quản lý yêu cầu mượn sách</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Phê duyệt hoặc từ chối các yêu cầu đăng ký mượn từ độc giả qua OPAC</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.circulation.requests', ['status' => 'pending']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-bold {{ $status == 'pending' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                    Yêu cầu mượn
                </a>
                <a href="{{ route('admin.circulation.requests', ['status' => 'ready']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-bold {{ $status == 'ready' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                    Cho phép mượn
                </a>
                <a href="{{ route('admin.circulation.requests', ['status' => 'all']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-bold {{ $status == 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                    Tất cả
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Độc giả</th>
                    <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Tài liệu yêu cầu</th>
                    <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Ngày đăng ký</th>
                    <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Trạng thái</th>
                    <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($requests as $req)
                @php
                    $title = $req->bibliographicRecord->fields->where('tag', '245')->first()?->subfields->where('code', 'a')->first()?->value ?? 'Không có nhan đề';
                @endphp
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs border border-indigo-100 shadow-sm">
                                {{ substr($req->patron->display_name ?? $req->patron->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $req->patron->display_name ?? $req->patron->user->name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $req->patron->patron_code }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="max-w-xs">
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200 line-clamp-1" title="{{ $title }}">{{ $title }}</p>
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest">Record ID: #{{ $req->bibliographic_record_id }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ $req->reservation_date->format('d/m/Y H:i') }}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($req->status == 'pending')
                            <span class="px-2 py-1 bg-amber-50 text-amber-600 text-[10px] font-black uppercase rounded-lg border border-amber-100">Yêu cầu mượn</span>
                        @elseif($req->status == 'ready')
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase rounded-lg border border-emerald-100">Cho phép mượn</span>
                        @elseif($req->status == 'cancelled')
                            <span class="px-2 py-1 bg-rose-50 text-rose-500 text-[10px] font-black uppercase rounded-lg border border-rose-100">Đã hủy</span>
                        @elseif($req->status == 'fulfilled')
                            <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase rounded-lg border border-blue-100">Đã nhận sách</span>
                        @else
                            <span class="px-2 py-1 bg-slate-50 text-slate-500 text-[10px] font-black uppercase rounded-lg">{{ $req->status }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($req->status == 'pending')
                        <div class="flex justify-end gap-2">
                            <form action="{{ route('admin.circulation.requests.approve', $req->id) }}" method="POST" onsubmit="return confirm('Phê duyệt yêu cầu này và giữ sách cho độc giả?')">
                                @csrf
                                <button type="submit" class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg transition-all" title="Phê duyệt">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <button onclick="openRejectModal({{ $req->id }})" class="p-2 bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white rounded-lg transition-all" title="Từ chối">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                        <i class="fas fa-inbox text-4xl mb-4 opacity-20"></i>
                        <p>Không có yêu cầu mượn nào được tìm thấy.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $requests->links() }}
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-75" onclick="closeRejectModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 dark:border-slate-800">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="px-8 pt-8 pb-6">
                    <h3 class="text-xl font-black text-vttu-dark dark:text-slate-100 uppercase tracking-tight mb-4">Từ chối yêu cầu</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Lý do từ chối</label>
                            <textarea name="reason" rows="4" class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-none rounded-3xl focus:ring-2 focus:ring-vttu-red/20 text-sm font-medium" placeholder="Nhập lý do để thông báo cho độc giả..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="px-8 pb-8 flex gap-3">
                    <button type="button" onclick="closeRejectModal()" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-slate-200 transition-all">Hủy bỏ</button>
                    <button type="submit" class="flex-1 py-4 bg-rose-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-rose-600 transition-all shadow-lg shadow-rose-200">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRejectModal(id) {
        const form = document.getElementById('rejectForm');
        form.action = `/topsecret/circulation/requests/${id}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endsection
