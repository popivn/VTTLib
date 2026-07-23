<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OpenEducationalResource;
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

echo "=== EXTRACTING IMAGE BLOB FOR MEDIAID 1318 FROM PORTAL_NEWS_MEDIA ===\n";

$row = $oldDb->table('PORTAL_NEWS_MEDIA')->where('MEDIAID', 1318)->first();

if (!$row) {
    echo "Not found in PORTAL_NEWS_MEDIA. Trying PORTAL_SITE_IMAGE...\n";
    $row = $oldDb->table('PORTAL_SITE_IMAGE')->where('IMAGEID', 1318)->first();
    if ($row) {
        $blob = $row->IMAGECONTENT;
        $ext = $row->IMAGEEXT ?? 'jpeg';
    }
} else {
    $blob = $row->MEDIACONTENT;
    $ext = $row->MEDIAEX ?? 'jpeg';
}

if (!empty($blob)) {
    $targetDir = public_path('storage/pages/oer');
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = "oer_338_original_cntt1.{$ext}";
    $filePath = $targetDir . '/' . $fileName;
    $dbPath = 'storage/pages/oer/' . $fileName;

    file_put_contents($filePath, $blob);
    echo "SUCCESSFULLY EXTRACTED ORIGINAL IMAGE FROM OLD DB BLOB!\n";
    echo "File size: " . filesize($filePath) . " bytes\n";
    echo "File path: $filePath\n";

    $book = OpenEducationalResource::find(338);
    if ($book) {
        $book->update(['thumbnail_url' => $dbPath]);
        echo "UPDATED BOOK #338 THUMBNAIL IN MYSQL DB!\n";
    }
} else {
    echo "Blob content is empty for image 1318\n";
}
