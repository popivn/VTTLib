<div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">{{ $title }}</h3>
        <span class="text-xs font-semibold text-slate-500">Hiển thị {{ count($rows) }} / {{ number_format($totalCount) }} kết quả</span>
    </div>

    <div class="overflow-x-auto border border-slate-200 rounded-lg">
        <table class="w-full text-xs text-left border-collapse">
            <thead>
                <tr class="bg-slate-800 text-white font-bold text-center">
                    @foreach($headers as $h)
                        <th class="py-2.5 px-3 border-r border-slate-700 last:border-r-0 whitespace-nowrap">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $row)
                <tr class="hover:bg-slate-50 transition-colors">
                    @foreach($row as $idx => $cell)
                        <td class="py-2 px-3 border-r border-slate-100 last:border-r-0 {{ $idx === 0 ? 'text-center font-bold' : '' }} whitespace-nowrap">
                            {{ $cell }}
                        </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="py-8 text-center text-slate-400 font-medium">
                        Không có dữ liệu phù hợp
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
