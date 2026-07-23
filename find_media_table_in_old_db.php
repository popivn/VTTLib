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

$tables = $oldDb->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");

echo "=== ALL TABLES IN OLD DB ===\n";
foreach ($tables as $t) {
    $tableName = $t->TABLE_NAME;
    if (str_contains(strtoupper($tableName), 'MEDIA') || str_contains(strtoupper($tableName), 'IMAGE') || str_contains(strtoupper($tableName), 'NEWS') || str_contains(strtoupper($tableName), 'FILE') || str_contains(strtoupper($tableName), 'PORTAL')) {
        echo " - Table: $tableName\n";
    }
}
