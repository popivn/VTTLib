<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.old_sqlsrv' => [
    'driver' => 'sqlsrv',
    'host' => '192.168.1.33',
    'port' => '1433',
    'database' => 'TDHVTTOAN',
    'username' => 'sa',
    'password' => '@123456',
    'charset' => 'utf8',
    'prefix' => '',
    'trust_server_certificate' => true,
]]);

$oldDb = DB::connection('old_sqlsrv');

echo "=== INSPECTING PORTAL_NEWS_MEDIA ===\n";
try {
    $cols = array_keys((array)$oldDb->table('PORTAL_NEWS_MEDIA')->first());
    echo "Columns: " . implode(', ', $cols) . "\n";

    $row = $oldDb->table('PORTAL_NEWS_MEDIA')
        ->where('ID', 1318)
        ->orWhere('OBJECTID', '240228103407')
        ->first();

    if ($row) {
        print_r((array)$row);
    } else {
        echo "No record for 1318 / 240228103407 in PORTAL_NEWS_MEDIA. Sample row:\n";
        print_r((array)$oldDb->table('PORTAL_NEWS_MEDIA')->first());
    }
} catch (\Throwable $e) {
    echo "Error checking PORTAL_NEWS_MEDIA: " . $e->getMessage() . "\n";
}

echo "\n=== INSPECTING PORTAL_SITE_IMAGE ===\n";
try {
    $cols = array_keys((array)$oldDb->table('PORTAL_SITE_IMAGE')->first());
    echo "Columns: " . implode(', ', $cols) . "\n";

    $row = $oldDb->table('PORTAL_SITE_IMAGE')
        ->where('ID', 1318)
        ->orWhere('OBJECTID', '240228103407')
        ->first();

    if ($row) {
        print_r((array)$row);
    } else {
        echo "No record for 1318 / 240228103407 in PORTAL_SITE_IMAGE. Sample row:\n";
        print_r((array)$oldDb->table('PORTAL_SITE_IMAGE')->first());
    }
} catch (\Throwable $e) {
    echo "Error checking PORTAL_SITE_IMAGE: " . $e->getMessage() . "\n";
}
