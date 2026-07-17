<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$recordIds = [7448, 805];

foreach ($recordIds as $id) {
    echo "=== Record ID: $id ===\n";
    $fields = DB::table('marc_fields')
        ->where('record_id', $id)
        ->get();
        
    foreach ($fields as $f) {
        $subfields = DB::table('marc_subfields')
            ->where('marc_field_id', $f->id)
            ->get();
            
        foreach ($subfields as $sf) {
            echo "Tag: {$f->tag} | Code: {$sf->code} | Value: {$sf->value}\n";
        }
    }
    echo "\n";
}
