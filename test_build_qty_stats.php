<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\DigitalReportController;
use Illuminate\Http\Request;

$controller = new DigitalReportController();
$request = Request::create('/admin/digital-reports/preview', 'POST', [
    'report_type' => 'digital_qty_stats'
]);

$data = $controller->buildQtyStatsData($request);

echo "=== BUILT QTY STATS DATA OUTPUT ===\n";
foreach ($data as $item) {
    echo "Folder ID {$item['folder_id']} | Name: {$item['folder_name']} | Total: {$item['total']}\n";
    foreach ($item['types'] as $t) {
        echo "   - {$t['name']}: {$t['count']}\n";
    }
}
