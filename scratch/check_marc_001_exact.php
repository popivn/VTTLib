<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$targets = ['-409', '-1401', '-1049', '-691', '-469'];

echo "=== CHECKING EXACT MARC 001 VALUES IN MYSQL ===\n";
foreach ($targets as $val) {
    $res = DB::table('marc_fields')
        ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
        ->where('marc_fields.tag', '001')
        ->where('marc_subfields.value', $val)
        ->select('marc_fields.record_id', 'marc_subfields.value')
        ->first();
        
    if ($res) {
        // Get Title
        $title = DB::table('marc_fields')
            ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
            ->where('marc_fields.record_id', $res->record_id)
            ->where('marc_fields.tag', '245')
            ->where('marc_subfields.code', 'a')
            ->value('marc_subfields.value');
        echo "Value $val -> Record ID: {$res->record_id} | Title: " . trim($title) . "\n";
    } else {
        echo "Value $val -> NOT FOUND\n";
    }
}
