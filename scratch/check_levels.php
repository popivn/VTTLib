<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== BIBLIOGRAPHIC LEVELS IN MYSQL ===\n";
    $levels = DB::table('bibliographic_levels')->get();
    foreach ($levels as $lvl) {
        echo "ID: {$lvl->id} | Code: {$lvl->code} | Name VI: {$lvl->name_vi} | Name EN: {$lvl->name_en}\n";
    }

    echo "\n=== RECORD COUNT BY BIBLIOGRAPHIC LEVEL ===\n";
    $counts = DB::table('bibliographic_records')
        ->select('bibliographic_level', DB::raw('count(*) as total'))
        ->groupBy('bibliographic_level')
        ->get();
    foreach ($counts as $c) {
        echo "Level: " . ($c->bibliographic_level ?: 'NULL') . " | Total: {$c->total}\n";
    }

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
