<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== SAMPLE MARC 001 VALUES IN MYSQL ===\n";
    $rows = DB::table('marc_fields')
        ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
        ->where('marc_fields.tag', '001')
        ->select('marc_fields.record_id', 'marc_subfields.value')
        ->limit(100)
        ->get();
        
    $count = 0;
    foreach ($rows as $r) {
        echo "Record ID: {$r->record_id} | Value: {$r->value}\n";
        $count++;
    }
    echo "Printed $count rows.\n";

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
