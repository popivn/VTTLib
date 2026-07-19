<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Copying content_html to content in site_nodes ===\n";
    $nodes = DB::table('site_nodes')->where('node_code', 'like', '%guide-%')->get();
    foreach ($nodes as $node) {
        if (!empty($node->content_html) && empty($node->content)) {
            DB::table('site_nodes')->where('id', $node->id)->update([
                'content' => $node->content_html
            ]);
            echo "  Updated Node ID: {$node->id} | Code: {$node->node_code} | Title: {$node->display_name}\n";
        }
    }
    echo "Done updating site_nodes.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
