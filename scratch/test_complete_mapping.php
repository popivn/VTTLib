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
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all unique non-null BIBID from TBARTICLE
    $stmt = $conn->query("SELECT DISTINCT BIBID FROM TBARTICLE WHERE BIBID IS NOT NULL AND BIBID <> 0");
    $bibIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Total unique BIBIDs in TBARTICLE: " . count($bibIds) . "\n\n";

    $mappedCount = 0;
    $unmappedCount = 0;
    $mapping = [];

    foreach ($bibIds as $oldId) {
        // 1. Get MARC21 from SQL Server
        $stmtBib = $conn->prepare("SELECT TOP 1 MARC21 FROM BIBLIOGRAPHIC WHERE BIBID = :bibid");
        $stmtBib->execute(['bibid' => $oldId]);
        $marc = $stmtBib->fetchColumn();
        
        if (!$marc) {
            echo "Old BIBID $oldId -> Not found in SQL Server BIBLIOGRAPHIC!\n";
            $unmappedCount++;
            continue;
        }
        
        // 2. Extract Tag 001
        $m001 = getMarcTag001($marc);
        if (!$m001) {
            echo "Old BIBID $oldId -> No Tag 001 found in MARC21!\n";
            $unmappedCount++;
            continue;
        }
        
        // 3. Search in MySQL
        // Note: multiple records can have the same tag 001 value due to import duplicates.
        // We will select the one that has a title similar or just the first matching record
        $newIds = DB::table('marc_fields')
            ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
            ->where('marc_fields.tag', '001')
            ->where('marc_subfields.value', $m001)
            ->pluck('marc_fields.record_id');
            
        if ($newIds->isEmpty()) {
            echo "Old BIBID $oldId (Tag 001: '$m001') -> NOT FOUND in MySQL marc_subfields!\n";
            $unmappedCount++;
            continue;
        }
        
        // Find best match if there are multiple matches
        $bestId = $newIds[0];
        if ($newIds->count() > 1) {
            // Get SQL Server title
            $stmtTitle = $conn->prepare("SELECT TOP 1 TITLE FROM BIBSEARCH WHERE BIBID = :bibid");
            $stmtTitle->execute(['bibid' => $oldId]);
            $oldTitle = $stmtTitle->fetchColumn();
            
            if ($oldTitle) {
                // Check titles in MySQL
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
        $mappedCount++;
    }

    echo "\n=== COMPLETE MAPPING RESULTS ===\n";
    echo "Mapped successfully: $mappedCount / " . count($bibIds) . "\n";
    echo "Unmapped: $unmappedCount\n";
    
    // Save to temp json
    file_put_contents('scratch/exact_bib_mapping.json', json_encode($mapping, JSON_PRETTY_PRINT));
    echo "Saved exact mapping to scratch/exact_bib_mapping.json\n";

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
