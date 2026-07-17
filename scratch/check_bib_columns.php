<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    $cols = Schema::getColumnListing('bibliographic_records');
    echo "=== COLUMNS IN bibliographic_records ===\n";
    print_r($cols);

    // Let's check tag 001 for a record, or look at how bibid is mapped.
    // Let's find a record by looking up its MARC 001 value.
    $records = DB::table('bibliographic_records')->limit(10)->get();
    echo "\n=== SAMPLE RECORDS ===\n";
    foreach ($records as $r) {
        // Find MARC 001 tag value
        $m001 = DB::table('marc_fields')
            ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
            ->where('marc_fields.record_id', $r->id)
            ->where('marc_fields.tag', '001')
            ->value('marc_subfields.value');
            
        echo "ID: {$r->id} | MARC 001: " . ($m001 ?: 'N/A') . "\n";
    }

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
