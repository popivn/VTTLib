<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function getMarcTag001($marc) {
    if (strlen($marc) < 24) return null;
    $baseAddress = intval(substr($marc, 12, 5));
    
    $dirEnd = strpos($marc, "\x1e");
    if ($dirEnd === false) {
        $dirEnd = $baseAddress - 1;
    }
    $directory = substr($marc, 24, $dirEnd - 24);
    
    $len = strlen($directory);
    for ($i = 0; $i < $len; $i += 12) {
        if ($i + 12 > $len) break;
        $tag = substr($directory, $i, 3);
        $fieldLength = intval(substr($directory, $i + 3, 4));
        $fieldOffset = intval(substr($directory, $i + 7, 5));
        
        if ($tag === '001') {
            $val = substr($marc, $baseAddress + $fieldOffset, $fieldLength);
            return trim(preg_replace('/[^0-9\-]/', '', $val));
        }
    }
    return null;
}

try {
    echo "Connecting to SQL Server database...\n";
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Building BIBID to MySQL Record ID mapping via MARC 001...\n";
    // 1. Get all unique BIBIDs from TBARTICLE
    $stmtBibIds = $conn->query("SELECT DISTINCT BIBID FROM TBARTICLE WHERE BIBID IS NOT NULL AND BIBID <> 0");
    $oldBibIds = $stmtBibIds->fetchAll(PDO::FETCH_COLUMN);

    $mapping = [];
    foreach ($oldBibIds as $oldId) {
        // Get MARC21 string
        $stmtMarc = $conn->prepare("SELECT TOP 1 MARC21 FROM BIBLIOGRAPHIC WHERE BIBID = :bibid");
        $stmtMarc->execute(['bibid' => $oldId]);
        $marc = $stmtMarc->fetchColumn();
        
        if (!$marc) {
            echo "  [WARNING] Old BIBID $oldId not found in SQL Server BIBLIOGRAPHIC!\n";
            continue;
        }
        
        $m001 = getMarcTag001($marc);
        if (!$m001) {
            echo "  [WARNING] Old BIBID $oldId does not have a Tag 001 value!\n";
            continue;
        }
        
        // Find in MySQL
        $newIds = DB::table('marc_fields')
            ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
            ->where('marc_fields.tag', '001')
            ->where('marc_subfields.value', $m001)
            ->pluck('marc_fields.record_id');
            
        if ($newIds->isEmpty()) {
            echo "  [WARNING] Old BIBID $oldId (Tag 001: '$m001') not found in MySQL!\n";
            continue;
        }
        
        // Match first or best
        $bestId = $newIds[0];
        if ($newIds->count() > 1) {
            $stmtTitle = $conn->prepare("SELECT TOP 1 TITLE FROM BIBSEARCH WHERE BIBID = :bibid");
            $stmtTitle->execute(['bibid' => $oldId]);
            $oldTitle = $stmtTitle->fetchColumn();
            
            if ($oldTitle) {
                $bestScore = -1;
                foreach ($newIds as $nid) {
                    $mTitle = DB::table('marc_fields')
                        ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
                        ->where('marc_fields.record_id', $nid)
                        ->where('marc_fields.tag', '245')
                        ->where('marc_subfields.code', 'a')
                        ->value('marc_subfields.value');
                        
                    similar_text(mb_strtolower($oldTitle), mb_strtolower($mTitle), $score);
                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestId = $nid;
                    }
                }
            }
        }
        
        $mapping[$oldId] = $bestId;
    }
    
    echo "Successfully mapped " . count($mapping) . " / " . count($oldBibIds) . " BIBIDs.\n\n";

    echo "Clearing existing portal_topics and portal_articles in MySQL...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('portal_articles')->truncate();
    DB::table('portal_topics')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    // 2. Import Topics
    echo "Importing TBTOPIC...\n";
    $stmt = $conn->query("SELECT * FROM TBTOPIC");
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $topicCount = 0;
    foreach ($topics as $t) {
        DB::table('portal_topics')->insert([
            'id' => $t['TOPICID'],
            'parent_id' => $t['PARENT'] == 0 ? null : $t['PARENT'],
            'description' => $t['DESCRIPTION'],
            'abstract' => $t['ABSTRACT'],
            'is_active' => $t['ACTIVE'] == 1,
            'display_index' => $t['DISPLAYINDEX'] ?? 0,
            'customer_id' => $t['CUSTOMERID'] ?? 'DEFAULT',
            'created_at' => $t['CREATEDATE'] ?: now(),
            'updated_at' => now(),
        ]);
        echo "  -> Imported Topic: ID {$t['TOPICID']} - {$t['DESCRIPTION']}\n";
        $topicCount++;
    }
    echo "Total $topicCount topics imported.\n\n";

    // 3. Import Articles
    echo "Importing TBARTICLE...\n";
    $stmtArt = $conn->query("SELECT * FROM TBARTICLE");
    
    $articleCount = 0;
    while ($art = $stmtArt->fetch(PDO::FETCH_ASSOC)) {
        $bibId = $art['BIBID'];
        $mappedBibId = isset($mapping[$bibId]) ? $mapping[$bibId] : null;

        if (!empty($bibId) && empty($mappedBibId)) {
            echo "  [WARNING] BIBID $bibId for ARTICLEID {$art['ARTICLEID']} could not be mapped to any record in MySQL. Setting bib_id to NULL.\n";
        }

        // Handle book_image binary data
        $bookImage = $art['BOOKIMAGE'];
        if (is_resource($bookImage)) {
            $binaryData = stream_get_contents($bookImage);
        } else {
            $binaryData = $bookImage;
        }

        DB::table('portal_articles')->insert([
            'id' => $art['ARTICLEID'],
            'topic_id' => $art['TOPICID'],
            'bib_id' => $mappedBibId,
            'abstract' => $art['ABSTRACT'],
            'contents' => $art['CONTENTS'],
            'book_image' => $binaryData,
            'access_counter' => $art['ACCESSCOUNTER'] ?? 0,
            'sort_order' => $art['SORTORDER'] ?? 0,
            'user_id' => $art['USERID'],
            'user_id_last' => $art['USERIDLAST'],
            'customer_id' => $art['CUSTOMERID'] ?? 'DEFAULT',
            'created_at' => $art['CREATEDATE'] ?: now(),
            'updated_at' => $art['MODIFYDATE'] ?: now(),
        ]);
        
        $msg = "  -> Imported Article: ID {$art['ARTICLEID']} (Old BIBID: " . ($bibId ?: 'NULL') . " -> New ID: " . ($mappedBibId ?: 'NULL') . ")\n";
        echo $msg;
        $articleCount++;
    }
    
    echo "\nTotal $articleCount articles imported successfully.\n";
    echo "=== IMPORT COMPLETED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
    exit(1);
}
