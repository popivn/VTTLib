<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

try {
    echo "Connecting to SQL Server database...\n";
    $oldConn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $oldConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Clearing oer_resources table in MySQL...\n";
    DB::table('oer_resources')->truncate();

    // Directory for OER covers
    $coverDir = public_path('storage/oer/covers');
    if (!File::exists($coverDir)) {
        File::makeDirectory($coverDir, 0755, true);
    }

    $nodes = [
        '240227044344' => [
            'name' => 'Y khoa - Dược học',
            'subjects' => ['Y khoa', 'Dược học']
        ],
        '240228103808' => [
            'name' => 'Kinh tế',
            'subjects' => ['Kinh tế']
        ],
        '240228103925' => [
            'name' => 'Luật',
            'subjects' => ['Luật']
        ],
        '240228103407' => [
            'name' => 'Thể loại khác',
            'subjects' => ['Khác']
        ]
    ];

    // Helper to extract fields using boundary labels
    function oerExtractField($text, $startLabel, $endLabels = []) {
        $pos = mb_strpos($text, $startLabel);
        if ($pos === false) return '';
        
        $start = $pos + mb_strlen($startLabel);
        $bestEnd = mb_strlen($text);
        
        foreach ($endLabels as $endLabel) {
            $endPos = mb_strpos($text, $endLabel, $start);
            if ($endPos !== false && $endPos < $bestEnd) {
                $bestEnd = $endPos;
            }
        }
        
        $val = mb_substr($text, $start, $bestEnd - $start);
        $val = ltrim(trim($val), ':');
        return trim($val);
    }

    $totalImported = 0;

    foreach ($nodes as $nodeId => $nodeInfo) {
        $pageName = $nodeInfo['name'];
        $subjects = $nodeInfo['subjects'];

        echo "=== Syncing OER Category: $pageName (ID: $nodeId) ===\n";

        $stmt = $oldConn->prepare("SELECT CONTENT FROM PORTAL_SITE WHERE ID = :id");
        $stmt->execute([':id' => $nodeId]);
        $html = $stmt->fetchColumn();

        if (empty($html)) {
            echo "  [WARNING] No HTML content found for category: $pageName\n\n";
            continue;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $tables = $xpath->query("//table");

        if ($tables->length <= 1) {
            echo "  [WARNING] No OER data table found in category: $pageName\n\n";
            continue;
        }

        // The actual data table is the second table (index 1)
        $table = $tables->item(1);
        $trQuery = $xpath->query(".//tr", $table);
        echo "  Found " . $trQuery->length . " rows of resources.\n";

        $categoryCount = 0;

        foreach ($trQuery as $index => $tr) {
            $tds = $xpath->query(".//td", $tr);
            if ($tds->length === 0) continue;

            $tdDetails = $tds->item(0);
            $tdCover = $tds->length > 1 ? $tds->item(1) : null;

            // 1. Extract Title and Link
            $title = '';
            $externalLink = '';
            
            // Try to find the title link
            $aTitleQuery = $xpath->query(".//h1//a | .//p//a | .//a", $tdDetails);
            if ($aTitleQuery->length > 0) {
                // The first link is typically the title link
                $aTitle = $aTitleQuery->item(0);
                $title = trim($aTitle->textContent);
                $externalLink = trim($aTitle->getAttribute('href'));
            } else {
                // Fallback to text content of the first h1 or paragraph
                $h1Query = $xpath->query(".//h1 | .//p", $tdDetails);
                if ($h1Query->length > 0) {
                    $title = trim($h1Query->item(0)->textContent);
                }
            }

            if (empty($title)) {
                // If title is still empty, skip this row
                continue;
            }

            // 2. Extract detailed info fields from td text
            $text = $tdDetails->textContent;

            // Remove extra whitespace and line endings to make extraction reliable
            $cleanText = preg_replace('/\s+/', ' ', $text);

            $authorsStr = oerExtractField($cleanText, 'Tác giả', ['Thông tin xuất bản', 'Nguồn đóng góp', 'Giấy phép', 'Ngày đăng']);
            $publisher = oerExtractField($cleanText, 'Thông tin xuất bản', ['Nguồn đóng góp', 'Giấy phép', 'Ngày đăng']);
            $source = oerExtractField($cleanText, 'Nguồn đóng góp', ['Giấy phép', 'Ngày đăng', 'Tài liệu được phân phối']);
            
            $license = oerExtractField($cleanText, 'Giấy phép', ['Ngày đăng']);
            if (empty($license)) {
                $license = oerExtractField($cleanText, 'Tài liệu được phân phối tuân theo Giấy phép', ['Ngày đăng']);
            }

            $publishDateStr = oerExtractField($cleanText, 'Ngày đăng', []);

            // 3. Process authors as array
            $authorsArray = [];
            if (!empty($authorsStr)) {
                $authorsArray = array_map('trim', explode(',', $authorsStr));
                $authorsArray = array_filter($authorsArray);
            }

            // 4. Parse publish year
            $publishYear = null;
            if (!empty($publishDateStr)) {
                if (preg_match('/\b(19\d\d|20\d\d)\b/', $publishDateStr, $matches)) {
                    $publishYear = intval($matches[1]);
                }
            }

            // 5. Detect language
            // Simple check: if source contains open.umn or open.bc or authors have Western letters, default to 'en', else 'vi'
            $lang = 'vi';
            if (str_contains(strtolower($source), 'open.umn.edu') || 
                str_contains(strtolower($source), 'open.bccampus.ca') ||
                preg_match('/[a-zA-Z]/', $authorsStr) && !preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/i', $authorsStr)) {
                $lang = 'en';
            }

            // 6. Handle Cover Image
            $coverPath = null;
            if ($tdCover) {
                $imgQuery = $xpath->query(".//img", $tdCover);
                if ($imgQuery->length > 0) {
                    $imgSrc = $imgQuery->item(0)->getAttribute('src');
                    if (preg_match('/imageId=(\d+)/', $imgSrc, $imgMatches)) {
                        $mediaId = $imgMatches[1];
                        
                        // Query binary from SQL Server
                        try {
                            $mediaStmt = $oldConn->prepare("SELECT MEDIAEX, MEDIACONTENT FROM PORTAL_NEWS_MEDIA WHERE MEDIAID = :id");
                            $mediaStmt->execute([':id' => $mediaId]);
                            $mediaRow = $mediaStmt->fetch(PDO::FETCH_ASSOC);

                            if ($mediaRow) {
                                $mediaEx = $mediaRow['MEDIAEX'] ?: 'png';
                                $mediaBinary = $mediaRow['MEDIACONTENT'];
                                if (is_resource($mediaBinary)) {
                                    $mediaBinary = stream_get_contents($mediaBinary);
                                }

                                if (!empty($mediaBinary)) {
                                    $coverFilename = "cover_{$mediaId}.{$mediaEx}";
                                    File::put($coverDir . '/' . $coverFilename, $mediaBinary);
                                    $coverPath = "oer/covers/" . $coverFilename;
                                }
                            }
                        } catch (Exception $mediaEx) {
                            // Fail silently
                        }
                    }
                }
            }

            // 7. Insert resource into MySQL
            DB::table('oer_resources')->insert([
                'title' => $title,
                'resource_type' => 'book',
                'file_path' => '',
                'language' => $lang,
                'authors' => json_encode($authorsArray, JSON_UNESCAPED_UNICODE),
                'subjects' => json_encode($subjects, JSON_UNESCAPED_UNICODE),
                'educational_levels' => json_encode([], JSON_UNESCAPED_UNICODE),
                'license' => $license ?: 'N/A',
                'license_url' => '',
                'description' => '',
                'publisher' => $publisher ?: 'N/A',
                'publish_year' => $publishYear,
                'format' => str_ends_with(strtolower($externalLink), '.pdf') ? 'pdf' : 'url',
                'identifier' => '',
                'source' => $source ?: 'N/A',
                'external_link' => $externalLink,
                'keywords' => '',
                'file_name' => '',
                'file_size' => 0,
                'cover_path' => $coverPath ?: '',
                'status' => 'published',
                'view_count' => 0,
                'download_count' => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $categoryCount++;
            $totalImported++;
        }

        echo "  [SUCCESS] Synced $categoryCount resources for $pageName.\n\n";
    }

    echo "=== SYNC COMPLETED SUCCESSFULLY ===\n";
    echo "Total $totalImported OER resources synced from old system.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
