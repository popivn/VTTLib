<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DigitalQtyStatsExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $foldersData;
    protected $title;

    public function __construct(array $foldersData, string $title = 'Thống kê số lượng tài liệu số')
    {
        $this->foldersData = $foldersData;
        $this->title = $title;
    }

    public function view(): View
    {
        return view('admin.digital-resources.reports.excel_qty_stats', [
            'foldersData' => $this->foldersData
        ]);
    }

    public function title(): string
    {
        return 'THỐNG KÊ SỐ LƯỢNG TÀI LIỆU SỐ';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['name' => 'Times New Roman']],
        ];
    }
}
