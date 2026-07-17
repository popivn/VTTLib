<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\BibliographicRecord;

try {
    $bibIds = DB::table('portal_articles')->whereNotNull('bib_id')->pluck('bib_id')->unique();
    echo "Found " . count($bibIds) . " unique bib_ids mapped in portal_articles.\n";

    $records = BibliographicRecord::whereIn('id', $bibIds)->get();
    echo "Found " . $records->count() . " records in bibliographic_records.\n\n";

    $statusCounts = [];
    foreach ($records as $r) {
        $statusCounts[$r->status] = ($statusCounts[$r->status] ?? 0) + 1;
    }

    echo "Status distribution:\n";
    foreach ($statusCounts as $status => $count) {
        echo "  - $status: $count\n";
    }

    echo "\nSample records details:\n";
    foreach ($records->take(5) as $r) {
        echo "ID: {$r->id} | Status: {$r->status} | Title: {$r->title} | Author: {$r->author}\n";
    }

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
