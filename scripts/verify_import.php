<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== VERIFYING IMPORTED DATA IN MYSQL ===\n\n";

    // 1. Total counts
    $topicsCount = DB::table('portal_topics')->count();
    $articlesCount = DB::table('portal_articles')->count();
    
    echo "Total topics: $topicsCount\n";
    echo "Total articles: $articlesCount\n\n";

    // 2. Count per topic
    echo "=== ARTICLES BY TOPIC ===\n";
    $topics = DB::table('portal_topics')->get();
    foreach ($topics as $t) {
        $count = DB::table('portal_articles')->where('topic_id', $t->id)->count();
        echo "- ID {$t->id} ({$t->description}): $count articles\n";
    }
    echo "\n";

    // 3. Sample articles with bib details
    echo "=== SAMPLE ARTICLES (TOP 5) ===\n";
    $samples = DB::table('portal_articles')
        ->join('portal_topics', 'portal_articles.topic_id', '=', 'portal_topics.id')
        ->select('portal_articles.*', 'portal_topics.description as topic_name')
        ->limit(5)
        ->get();

    foreach ($samples as $index => $art) {
        $title = 'N/A';
        if ($art->bib_id) {
            // Find in BIBSEARCH if table exists or just check bib_id
            try {
                $title = DB::table('bibsearch')->where('bibid', $art->bib_id)->value('author_main') ?: 'N/A';
            } catch (Exception $e) {
                // If table doesn't exist, we can fallback
            }
        }
        
        $imgSize = $art->book_image ? strlen($art->book_image) : 0;
        
        echo "Row $index:\n";
        echo "  - Article ID: {$art->id}\n";
        echo "  - Topic: {$art->topic_name} (ID: {$art->topic_id})\n";
        echo "  - Bib ID: " . ($art->bib_id ?: 'NULL') . "\n";
        echo "  - Title: " . trim($title) . "\n";
        echo "  - Abstract: " . mb_substr(trim(strip_tags($art->abstract)), 0, 80) . "...\n";
        echo "  - Image size in DB: " . number_format($imgSize) . " bytes\n";
        echo "\n";
    }

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
