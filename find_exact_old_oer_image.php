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

echo "=== SEARCHING OLD DB HTML CONTENT FOR 'Algorithms and Data Structures' ===\n";

$pages = ['240228103407', '240227044344', '240228103808', '240228103925', '240227084255'];

foreach ($pages as $pageId) {
    $content = $oldDb->table('PORTAL_SITE')->where('ID', $pageId)->value('CONTENT');
    if (!$content) continue;

    if (str_contains($content, 'Algorithms and Data Structures')) {
        echo "FOUND TITLE IN PAGE ID: $pageId\n";

        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        $xpath = new \DOMXPath($dom);

        $nodes = $xpath->query('//*[contains(text(), "Algorithms and Data Structures")]');
        foreach ($nodes as $node) {
            // Traverse parent <tr> or container
            $parent = $node->parentNode;
            while ($parent && strtolower($parent->nodeName) !== 'tr' && strtolower($parent->nodeName) !== 'div') {
                $parent = $parent->parentNode;
            }

            if ($parent) {
                echo "CONTAINER HTML:\n" . $dom->saveHTML($parent) . "\n";

                $imgs = $xpath->query('.//img', $parent);
                foreach ($imgs as $img) {
                    echo "  -> EXACT OLD IMG SRC: " . $img->getAttribute('src') . "\n";
                }
            }
        }
    }
}
