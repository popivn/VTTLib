<?php
require __DIR__ . '/vendor/autoload.php';

// Initialize VTTLib Laravel App
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SiteNode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "=== EXTRACTING ALL MEDIA IMAGES FROM OLD DB TO PUBLIC/STORAGE/NEWS_MEDIA ===\n";

// Ensure storage directory exists
$targetDir = public_path('storage/news_media');
if (!File::exists($targetDir)) {
    File::makeDirectory($targetDir, 0777, true, true);
}

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

$node = SiteNode::where('node_code', 'khung-chuong-trinh-dao-tao')->first();
if (!$node) {
    echo "ERROR: Node not found!\n";
    exit(1);
}

$content = $node->content;

// Find all imageId patterns in HTML
preg_match_all('/imageId=(\d+)/i', $content, $matches);
$imageIds = array_unique($matches[1] ?? []);

echo "Found " . count($imageIds) . " image IDs in content.\n";

$replacements = [];

foreach ($imageIds as $imgId) {
    try {
        $imgRow = $oldDb->table('DOCFILETHUMB')
            ->where('FILEID', $imgId)
            ->orWhere('THUMBID', $imgId)
            ->first();

        if ($imgRow && !empty($imgRow->THUMBIMAGE)) {
            $filename = "img_{$imgId}.jpg";
            $savePath = $targetDir . '/' . $filename;
            File::put($savePath, $imgRow->THUMBIMAGE);
            echo "Saved image {$imgId} -> {$filename}\n";

            $oldUrlPattern = "/News/ViewImageMedia?imageId={$imgId}";
            $newUrl = asset("storage/news_media/{$filename}");
            $replacements[$oldUrlPattern] = "/storage/news_media/{$filename}";
        }
    } catch (\Throwable $e) {
        echo "Error saving image {$imgId}: " . $e->getMessage() . "\n";
    }
}

// Replace all old image URLs in content with new local file URLs
foreach ($replacements as $oldUrl => $newUrl) {
    $content = str_replace($oldUrl, $newUrl, $content);
}

$node->update([
    'content' => $content,
    'content_html' => $content,
]);

echo "SUCCESS! All images extracted and saved locally into public/storage/news_media/\n";
