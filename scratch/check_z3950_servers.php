<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== z3950_servers TABLE IN MYSQL ===\n";
    $servers = DB::table('z3950_servers')->get();
    print_r($servers);

    echo "\n=== library_network_logos TABLE IN MYSQL ===\n";
    $logos = DB::table('library_network_logos')->get();
    print_r($logos);

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
