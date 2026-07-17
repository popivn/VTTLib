<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$bibIdsToCheck = [9570, 9473, 9472, 9396, 9259, 9179, 9148, 7877, 5292, 5287, 5283, 5107, 4928, 3341, 3345, 9649, 9706];

echo "=== CHECKING BIBLIOGRAPHIC RECORDS EXISTENCE ===\n";
foreach ($bibIdsToCheck as $id) {
    $exists = DB::table('bibliographic_records')->where('id', $id)->exists();
    echo "BIBID $id: " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
}
