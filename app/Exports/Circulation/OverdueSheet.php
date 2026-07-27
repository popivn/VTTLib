<?php

namespace App\Exports\Circulation;


use App\Models\LoanTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class OverdueSheet implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return LoanTransaction::with(['patron.user', 'bookItem.bibliographicRecord', 'policy'])
            ->where('status', 'borrowed')
            ->where('due_date', '<', Carbon::now())
            ->get();
    }

    public function title(): string
    {
        return 'Muon qua han';
    }

    public function headings(): array
    {
        return [
            'Ma vach',
            'Nhan de',
            'Ban doc',
            'Han tra',
            'So ngay tre',
            'Tien phat du kien'
        ];
    }

    public function map($loan): array
    {
        $overdueDays = Carbon::now()->diffInDays(Carbon::parse($loan->due_date), false);
        $overdueDays = (int) ceil(abs($overdueDays));
        $fine = $loan->policy ? $loan->policy->fine_per_day * $overdueDays : 0;

        return [
            $loan->bookItem->barcode,
            $loan->bookItem->bibliographicRecord->title ?? '',
            $loan->patron->user->name ?? '',
            $loan->due_date,
            $overdueDays,
            $fine
        ];
    }
}
