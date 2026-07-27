@extends('layouts.admin')

@section('content')
<div class="report-detail-page bg-white dark:bg-slate-900 rounded-lg shadow-xl overflow-hidden">
    <div class="p-6 border-b border-rose-200 dark:border-rose-900/30 flex justify-between items-center bg-rose-50 dark:bg-rose-950/20">
        <div>
            <h1 class="text-2xl font-black text-rose-800 dark:text-rose-400 uppercase tracking-tight flex items-center">
                <i class="fa-solid fa-clock-rotate-left mr-3 opacity-70"></i>
                {{ __('Overdue Borrowed Items') }}
            </h1>
            <p class="text-sm text-rose-600/70 dark:text-rose-400/50 mt-1">
                {{ __('Displaying all items that have exceeded their due date and have not been returned.') }}
            </p>
        </div>
        <a href="{{ route('admin.circulation.reports.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-slate-300 dark:hover:bg-slate-700 transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i>{{ __('Back') }}
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-rose-50/50 dark:bg-slate-800/50 text-rose-600 dark:text-rose-400/70 text-[10px] font-black uppercase tracking-widest border-b border-rose-100 dark:border-slate-700">
                    <th class="px-6 py-4">{{ __('Barcode') }}</th>
                    <th class="px-6 py-4">{{ __('Title') }}</th>
                    <th class="px-6 py-4">{{ __('Patron') }}</th>
                    <th class="px-6 py-4">{{ __('Due Date') }}</th>
                    <th class="px-6 py-4">{{ __('Overdue Days') }}</th>
                    <th class="px-6 py-4">{{ __('Fine (Est.)') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-rose-100 dark:divide-slate-800">
                @forelse($loans as $loan)
                <tr class="hover:bg-rose-50/30 dark:hover:bg-rose-900/5 transition-colors">
                    <td class="px-6 py-4 font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $loan->bookItem->barcode }}
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ $loan->bookItem->bibliographicRecord->title ?? __('No_Title_Defined') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-xs font-bold mr-3 shadow-inner text-rose-600">
                                {{ substr($loan->patron->user->name ?? 'P', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 leading-none">
                                    {{ $loan->patron->user->name ?? 'Unknown' }}
                                </p>
                                <p class="text-[10px] text-slate-500 mt-1 uppercase font-black tracking-widest">
                                    {{ $loan->patron->patron_code }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-xs font-bold text-rose-600 dark:text-rose-500">
                        {{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 text-xs font-black text-rose-600 dark:text-rose-500">
                        @php
                            $overdueDays = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($loan->due_date), false);
                            $overdueDays = (int) ceil(abs($overdueDays));
                        @endphp
                        {{ $overdueDays }} {{ __('days') }}
                    </td>
                    <td class="px-6 py-4 text-sm font-black text-slate-800 dark:text-slate-200">
                        @php
                            $fine = $loan->policy ? $loan->policy->fine_per_day * $overdueDays : 0;
                        @endphp
                        {{ number_format($fine) }}đ
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-rose-400 italic font-medium">
                        {{ __('No_Overdue_Items_Found') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($loans->hasPages())
    <div class="px-6 py-4 bg-rose-50/50 dark:bg-slate-950/50 border-t border-rose-100 dark:border-slate-800">
        {{ $loans->links() }}
    </div>
    @endif
</div>
@endsection
