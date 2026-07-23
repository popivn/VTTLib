<div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm text-slate-800 space-y-6 overflow-x-auto" style="font-family: 'Times New Roman', Times, serif;">
    <!-- Website Link Top Left -->
    <div class="text-xs text-slate-700">
        Website: <a href="http://library.vttu.edu.vn/" target="_blank" class="text-blue-700 underline">http://library.vttu.edu.vn/</a>
    </div>

    <!-- Main Title Centered -->
    <div class="text-center py-2">
        <h2 class="text-lg md:text-xl font-bold tracking-wide text-slate-900 uppercase">
            THỐNG KÊ SỐ LƯỢNG TÀI LIỆU SỐ TRONG THƯ VIỆN
        </h2>
    </div>

    <!-- Grouped Folders -->
    <div class="space-y-6">
        @foreach($foldersData as $folderData)
        <div class="space-y-2">
            <!-- Folder Title Row (e.g. 788   Bài giảng VTTU) -->
            <div class="flex items-center gap-4 text-sm font-bold text-slate-900 pl-8 pt-2">
                <span>{{ $folderData['folder_id'] }}</span>
                <span>{{ $folderData['folder_name'] }}</span>
            </div>

            <!-- Table Block -->
            <div class="overflow-x-auto border border-slate-300">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-[#d9e2f3] text-slate-900 font-bold border-b border-slate-300 text-center">
                            <th class="py-2 px-4 border-r border-slate-300 w-16 uppercase">STT</th>
                            <th class="py-2 px-4 border-r border-slate-300 uppercase">THỂ LOẠI TÀI LIỆU</th>
                            <th class="py-2 px-4 w-32 uppercase text-right">SỐ LƯỢNG</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($folderData['types'] as $idx => $typeRow)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-1.5 px-4 text-center border-r border-slate-300 font-medium">{{ $idx + 1 }}</td>
                            <td class="py-1.5 px-4 border-r border-slate-300 font-medium">{{ $typeRow['name'] }}</td>
                            <td class="py-1.5 px-4 text-right font-medium">{{ number_format($typeRow['count']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-3 px-4 text-center text-slate-400 italic">
                                {{ __('Chưa có tài liệu số nào trong thư mục này') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Subtotal Row -->
            <div class="flex justify-end items-center gap-4 text-xs font-bold text-slate-900 pr-4 pt-1">
                <span class="underline">Tổng số:</span>
                <span class="text-sm min-w-[50px] text-right">{{ number_format($folderData['total']) }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
