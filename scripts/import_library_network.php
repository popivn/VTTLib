<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

function cleanUrl($url) {
    $url = trim($url);
    $url = rtrim($url, '/');
    $url = preg_replace('/^https?:\/\/(www\.)?/', '', $url);
    return strtolower($url);
}

function getExtensionFromBinary($data) {
    if (strpos($data, "\x89PNG\r\n\x1a\n") === 0) {
        return 'png';
    }
    if (strpos($data, "\xff\xd8\xff") === 0) {
        return 'jpg';
    }
    if (strpos($data, "GIF87a") === 0 || strpos($data, "GIF89a") === 0) {
        return 'gif';
    }
    return 'png'; // default fallback
}

$cleanMapping = [
    'nlv.gov.vn' => 'Thư viện Quốc gia Việt Nam',
    'vttuhospital.com' => 'Bệnh viện ĐH Võ Trường Toản',
    'cesti.idm.oclc.org/login?url=https://link.springer.com' => 'Cơ sở dữ liệu SpringerLink',
    'vttu.edu.vn' => 'Trường Đại học Võ Trường Toản',
    'lrc.ctu.edu.vn' => 'Trung tâm Học liệu ĐH Cần Thơ',
    'scholar.dlu.edu.vn/thuvienso' => 'Thư viện số ĐH Đà Lạt',
    'cantholib.org.vn' => 'Thư viện Thành phố Cần Thơ',
];

try {
    echo "Connecting to SQL Server database...\n";
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query Slider/Banner documents that have non-empty LinkUrl
    $stmt = $conn->query("
        SELECT * FROM PORTAL_DOCUMENT 
        WHERE CATEGORYID = 233 
          AND LinkUrl IS NOT NULL 
          AND LinkUrl <> ''
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($rows) . " network library links in PORTAL_DOCUMENT.\n\n";

    // Truncate existing library_network_logos in MySQL
    echo "Clearing library_network_logos table in MySQL...\n";
    DB::table('library_network_logos')->truncate();

    // Ensure output directories exist
    $logoDir = storage_path('app/public/network-logos');
    if (!File::exists($logoDir)) {
        File::makeDirectory($logoDir, 0755, true);
    }

    $importCount = 0;
    foreach ($rows as $index => $row) {
        $linkUrl = trim($row['LinkUrl']);
        $cleanedUrl = cleanUrl($linkUrl);

        // Get friendly name
        $name = isset($cleanMapping[$cleanedUrl]) ? $cleanMapping[$cleanedUrl] : null;
        if (!$name) {
            // Fallback: use domain name
            $parsed = parse_url($linkUrl);
            $name = isset($parsed['host']) ? str_replace('www.', '', $parsed['host']) : 'Thư viện liên kết';
        }

        // Handle binary image
        $imageBinary = $row['IMAGE'];
        if (is_resource($imageBinary)) {
            $imageBinary = stream_get_contents($imageBinary);
        }

        $logoPath = '';
        if (!empty($imageBinary)) {
            $ext = getExtensionFromBinary($imageBinary);
            $filename = 'logo_' . $row['ID'] . '.' . $ext;
            $fullPath = $logoDir . '/' . $filename;
            
            // Save file
            File::put($fullPath, $imageBinary);
            $logoPath = 'network-logos/' . $filename;
            echo "  Saved image for ID {$row['ID']} to public storage.\n";
        }

        // Insert into library_network_logos
        DB::table('library_network_logos')->insert([
            'id' => $row['ID'],
            'name' => $name,
            'logo_path' => $logoPath,
            'url' => $linkUrl,
            'sort_order' => $row['STT'] ?? $index,
            'is_active' => $row['APPROVED'] ?? 1,
            'created_at' => $row['CREATED'] ?: now(),
            'updated_at' => $row['MODIFIED'] ?: now(),
        ]);

        echo "  [SUCCESS] Imported: $name -> $linkUrl\n";
        $importCount++;
    }

    echo "\nTotal $importCount library network logo records imported successfully.\n";
    echo "=== IMPORT COMPLETED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
