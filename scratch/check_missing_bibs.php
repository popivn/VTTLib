<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Titles from old DB for the missing ones:
// 7877 -> Isselbacher (Harrison's Principles of Internal Medicine - Nội tiết học và chuyển hóa)
// 5283 -> Nguyễn Khánh Trạch (Điều trị học nội khoa)
// 5107 -> Trần Ngọc Ân (Bài giảng bệnh học nội khoa tập 2)
// 4928 -> Isselbacher (Harrison's Principles of Internal Medicine - Rối loạn hệ tim mạch...)
// 3341 -> Nguyễn Thị Minh An (Bài giảng Bệnh Học Nội Khoa - Tập 1)

$searchTerms = [
    '7877' => 'Isselbacher',
    '5283' => 'Nguyễn Khánh Trạch',
    '5107' => 'Trần Ngọc Ân',
    '4928' => 'Isselbacher',
    '3341' => 'Nguyễn Thị Minh An',
];

echo "=== SEARCHING FOR MISSING BIBS BY TITLE/AUTHOR ===\n";
foreach ($searchTerms as $oldId => $term) {
    // Find record where tag = '100' or '245' matches term
    $matches = DB::table('marc_fields')
        ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
        ->whereIn('marc_fields.tag', ['100', '245', '700'])
        ->where('marc_subfields.value', 'LIKE', '%' . $term . '%')
        ->select('marc_fields.record_id', 'marc_fields.tag', 'marc_subfields.value')
        ->distinct()
        ->limit(3)
        ->get();
        
    echo "Old ID: $oldId ($term):\n";
    foreach ($matches as $m) {
        // Get MARC 001
        $m001 = DB::table('marc_fields')
            ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
            ->where('marc_fields.record_id', $m->record_id)
            ->where('marc_fields.tag', '001')
            ->value('marc_subfields.value');
            
        // Get Title
        $title = DB::table('marc_fields')
            ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
            ->where('marc_fields.record_id', $m->record_id)
            ->where('marc_fields.tag', '245')
            ->where('marc_subfields.code', 'a')
            ->value('marc_subfields.value');
            
        echo "  - Record ID: {$m->record_id} | Title: " . trim($title) . " | MARC 001: $m001 | Match: {$m->tag} = {$m->value}\n";
    }
}
