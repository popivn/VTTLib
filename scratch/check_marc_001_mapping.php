<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$bibIdsToCheck = [9570, 9473, 9472, 9396, 9259, 9179, 9148, 7877, 5292, 5287, 5283, 5107, 4928, 3341, 3345, 9649, 9706];

echo "=== MAPPING BIBID TO NEW ID VIA MARC 001 ===\n";
foreach ($bibIdsToCheck as $oldId) {
    // Find record where tag = '001' and value = $oldId
    $newId = DB::table('marc_fields')
        ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
        ->where('marc_fields.tag', '001')
        ->where('marc_subfields.value', (string)$oldId)
        ->value('marc_fields.record_id');
        
    if ($newId) {
        // Get title of the new record to make sure it matches
        $title = DB::table('marc_fields')
            ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
            ->where('marc_fields.record_id', $newId)
            ->where('marc_fields.tag', '245')
            ->where('marc_subfields.code', 'a')
            ->value('marc_subfields.value');
            
        echo "Old BIBID $oldId -> New Record ID: $newId | Title: " . trim($title) . "\n";
    } else {
        echo "Old BIBID $oldId -> NOT FOUND via MARC 001\n";
    }
}
