<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\BibliographicRecord;

function cleanString($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/[^\p{L}\p{N}\s]/u', '', $str); // keep only letters and numbers
    $str = preg_replace('/\s+/', ' ', $str); // normalize spaces
    return trim($str);
}

try {
    echo "Connecting to SQL Server database...\n";
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all unique BIBID from TBARTICLE
    $stmt = $conn->query("SELECT DISTINCT BIBID FROM TBARTICLE WHERE BIBID IS NOT NULL AND BIBID <> 0");
    $oldBibIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Found " . count($oldBibIds) . " unique BIBIDs in TBARTICLE.\n\n";

    $mapping = [];
    $notFound = [];

    foreach ($oldBibIds as $oldId) {
        // Get details from SQL Server
        // We can get details from BIBSEARCH (easier than parsing MARC21)
        $stmtSearch = $conn->prepare("SELECT TOP 1 TITLE, AUTHOR FROM BIBSEARCH WHERE BIBID = :bibid");
        $stmtSearch->execute(['bibid' => $oldId]);
        $oldSearch = $stmtSearch->fetch(PDO::FETCH_ASSOC);

        $oldTitle = '';
        $oldAuthor = '';
        if ($oldSearch) {
            $oldTitle = $oldSearch['TITLE'];
            $oldAuthor = $oldSearch['AUTHOR'];
        }

        // If not found in BIBSEARCH, try parsing from BIBLIOGRAPHIC
        if (empty($oldTitle)) {
            $stmtBib = $conn->prepare("SELECT TOP 1 MARC21 FROM BIBLIOGRAPHIC WHERE BIBID = :bibid");
            $stmtBib->execute(['bibid' => $oldId]);
            $marc = $stmtBib->fetchColumn();
            if ($marc) {
                // simple regex to find 245$a
                // note: MARC21 field might have some controls, but let's try title search
                if (preg_match('/a([^a-z0-9\$]{10,})/i', $marc, $m)) {
                    $oldTitle = $m[1];
                }
            }
        }

        if (empty($oldTitle)) {
            echo "Old BIBID $oldId -> Could not retrieve title from SQL Server!\n";
            $notFound[] = $oldId;
            continue;
        }

        $cleanedOldTitle = cleanString($oldTitle);
        
        // Find best match in MySQL by matching title and author
        // We will fetch records containing some keywords of the title
        $keywords = array_filter(explode(' ', $cleanedOldTitle), function($w) {
            return mb_strlen($w) > 2; // only words longer than 2 characters
        });
        
        $query = DB::table('marc_fields')
            ->join('marc_subfields', 'marc_fields.id', '=', 'marc_subfields.marc_field_id')
            ->where('marc_fields.tag', '245')
            ->where('marc_subfields.code', 'a');

        // Let's search using the title keywords
        if (!empty($keywords)) {
            $query->where(function($q) use ($keywords) {
                foreach (array_slice($keywords, 0, 5) as $kw) { // use up to first 5 keywords
                    $q->where('marc_subfields.value', 'LIKE', '%' . $kw . '%');
                }
            });
        }
        
        $matches = $query->select('marc_fields.record_id', 'marc_subfields.value as title')
            ->distinct()
            ->get();

        $bestRecordId = null;
        $bestScore = 0;
        $bestTitle = '';

        foreach ($matches as $match) {
            $cleanedMatchTitle = cleanString($match->title);
            // Calculate similarity score
            $sim = 0;
            similar_text($cleanedOldTitle, $cleanedMatchTitle, $sim);
            
            if ($sim > $bestScore) {
                $bestScore = $sim;
                $bestRecordId = $match->record_id;
                $bestTitle = $match->title;
            }
        }

        // We require a minimum similarity score (e.g. 75%)
        if ($bestRecordId && $bestScore >= 70) {
            echo "Old BIBID $oldId -> MySQL Record ID: $bestRecordId | Similarity: " . round($bestScore, 1) . "%\n";
            echo "  - SQL Server: " . trim($oldTitle) . "\n";
            echo "  - MySQL:      " . trim($bestTitle) . "\n";
            $mapping[$oldId] = $bestRecordId;
        } else {
            echo "Old BIBID $oldId -> NO MATCH FOUND (Best score: " . round($bestScore, 1) . "%: " . trim($bestTitle) . ")\n";
            $notFound[] = $oldId;
        }
    }

    echo "\n=== MAPPING SUMMARY ===\n";
    echo "Successfully mapped: " . count($mapping) . " / " . count($oldBibIds) . "\n";
    echo "Failed to map: " . count($notFound) . "\n";
    
    // Save mapping to a temp json file to use in import script
    file_put_contents('scratch/bib_mapping.json', json_encode($mapping, JSON_PRETTY_PRINT));
    echo "Saved mapping to scratch/bib_mapping.json\n";

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
