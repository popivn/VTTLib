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

    // 1. Fetch main page content
    $sql = "SELECT CONTENT FROM PORTAL_SITE WHERE ID = '230721092935'";
    $stmt = $oldConn->query($sql);
    $mainHtml = $stmt->fetchColumn();

    if (empty($mainHtml)) {
        throw new Exception("Main page content is empty or not found.");
    }

    // 2. Parse main page list items
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $mainHtml);
    libxml_clear_errors();

    $finder = new DomXPath($dom);
    $divs = $finder->query("//div[contains(@class, 'news-items')]");
    
    echo "Found " . $divs->length . " database items on the main page.\n\n";

    // 3. Clear new tables in MySQL
    echo "Clearing online_databases table in MySQL...\n";
    DB::table('online_databases')->truncate();

    // Find parent site node for 'co-so-du-lieu'
    $parentCatalogNode = DB::table('site_nodes')->where('node_code', 'co-so-du-lieu')->first();
    if (!$parentCatalogNode) {
        $parentCatalogNode = DB::table('site_nodes')->where('node_code', 'sb-co-so-du-lieu')->first();
    }
    $parentId = $parentCatalogNode ? $parentCatalogNode->id : null;
    echo "Parent SiteNode ID for guides: " . ($parentId ?: 'None') . "\n";

    // Directory for saving images
    $dbImgDir = storage_path('app/public/online-databases');
    if (!File::exists($dbImgDir)) {
        File::makeDirectory($dbImgDir, 0755, true);
    }

    $importedCount = 0;
    foreach ($divs as $index => $div) {
        // A. Extract Title
        $boldTags = $div->getElementsByTagName('b');
        $title = '';
        if ($boldTags->length > 0) {
            $title = trim($boldTags->item(0)->textContent);
        }
        
        // Clean up title (remove double spaces, extra colons, etc)
        $title = preg_replace('/\s+/', ' ', $title);
        $title = rtrim(trim($title), ':');

        if (empty($title)) {
            // Fallback to first bold tag of span/p
            $strongTags = $div->getElementsByTagName('strong');
            if ($strongTags->length > 0) {
                $title = trim($strongTags->item(0)->textContent);
            }
        }
        
        if (empty($title)) {
            continue; // Skip items without a title
        }

        // B. Extract short content (all text inside the div except link texts)
        // Let's clone the node to clean it up before extracting text
        $divClone = $div->cloneNode(true);
        // Remove link tags from text extraction
        $aTags = $divClone->getElementsByTagName('a');
        while ($aTags->length > 0) {
            $aTags->item(0)->parentNode->removeChild($aTags->item(0));
        }
        // Remove script or style tags if any
        $styleTags = $divClone->getElementsByTagName('style');
        while ($styleTags->length > 0) {
            $styleTags->item(0)->parentNode->removeChild($styleTags->item(0));
        }
        
        $shortDesc = trim($divClone->textContent);
        // Replace multiple whitespace/newlines
        $shortDesc = preg_replace('/\s+/', ' ', $shortDesc);
        // Clean up title from the description text if it starts with it
        if (str_starts_with($shortDesc, $title)) {
            $shortDesc = substr($shortDesc, strlen($title));
        }
        $shortDesc = ltrim(trim($shortDesc), ':-, ');

        // C. Extract Links (Access URL and Guide URL)
        $accessUrl = '';
        $guideUrlCode = '';
        $guideSource = ''; // 'site' or 'news'
        
        $links = $div->getElementsByTagName('a');
        foreach ($links as $link) {
            $linkText = strtolower(trim($link->nodeValue));
            $href = trim($link->getAttribute('href'));
            
            if (strpos($linkText, 'truy cập') !== false || strpos($linkText, 'truy cap') !== false || empty($accessUrl)) {
                if (!empty($href) && $href !== '#' && strpos($linkText, 'hướng dẫn') === false && strpos($linkText, 'huong dan') === false) {
                    $accessUrl = $href;
                }
            }
            
            if (strpos($linkText, 'hướng dẫn') !== false || strpos($linkText, 'huong dan') !== false) {
                if (!empty($href)) {
                    // Extract code or friendly name from URL
                    // Example: http://library.vttu.edu.vn/Page/csdl-springer-link
                    if (preg_match('/\/Page\/([^\/\?#]+)/', $href, $matches)) {
                        $guideUrlCode = $matches[1];
                        $guideSource = 'site';
                    } elseif (preg_match('/\/News\/NewDetail\/([^\/\?#]+)/', $href, $matches)) {
                        $guideUrlCode = $matches[1];
                        $guideSource = 'news';
                    }
                }
            }
        }

        // If no accessUrl found under 'Truy cập' text, fall back to first link that doesn't contain 'Page' or 'News'
        if (empty($accessUrl)) {
            foreach ($links as $link) {
                $href = trim($link->getAttribute('href'));
                if (!empty($href) && strpos($href, '/Page/') === false && strpos($href, '/News/') === false) {
                    $accessUrl = $href;
                    break;
                }
            }
        }

        // D. Extract Image/Logo
        $imageUrl = '';
        $imgTags = $div->getElementsByTagName('img');
        if ($imgTags->length > 0) {
            $src = trim($imgTags->item(0)->getAttribute('src'));
            if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
                $imageUrl = $src;
            } elseif (preg_match('/imageId=(\d+)/', $src, $matches)) {
                $imageId = $matches[1];
                // Query image from SQL Server PORTAL_NEWS_MEDIA
                $mediaStmt = $oldConn->prepare("SELECT MEDIAEX, MEDIACONTENT FROM PORTAL_NEWS_MEDIA WHERE MEDIAID = :mediaId");
                $mediaStmt->execute([':mediaId' => $imageId]);
                $mediaRow = $mediaStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($mediaRow) {
                    $ext = $mediaRow['MEDIAEX'] ?: 'png';
                    $imageBinary = $mediaRow['MEDIACONTENT'];
                    if (is_resource($imageBinary)) {
                        $imageBinary = stream_get_contents($imageBinary);
                    }
                    
                    if (!empty($imageBinary)) {
                        $filename = 'logo_' . $imageId . '.' . $ext;
                        File::put($dbImgDir . '/' . $filename, $imageBinary);
                        $imageUrl = 'online-databases/' . $filename;
                        echo "  Downloaded logo for '$title' (Media ID: $imageId)\n";
                    }
                }
            }
        }

        // E. Fetch Detailed Content for the guide page
        $detailedContent = '';
        if (!empty($guideUrlCode)) {
            if ($guideSource === 'site') {
                $siteStmt = $oldConn->prepare("SELECT CONTENT FROM PORTAL_SITE WHERE FRIENDLYNAME = :friendly");
                $siteStmt->execute([':friendly' => $guideUrlCode]);
                $detailedContent = $siteStmt->fetchColumn();
            } elseif ($guideSource === 'news') {
                $newsStmt = $oldConn->prepare("SELECT CONTENT FROM PORTAL_NEWS_ITEMS WHERE FRIENDLYNAME = :friendly");
                $newsStmt->execute([':friendly' => $guideUrlCode]);
                $detailedContent = $newsStmt->fetchColumn();
            }
        }

        // F. Insert detailed guide as a SiteNode in MySQL if parent is set
        $hdUrl = '';
        if (!empty($detailedContent) && $parentId) {
            $nodeCode = 'guide-' . (!empty($guideUrlCode) ? $guideUrlCode : Str::slug($title));
            $nodeCode = substr($nodeCode, 0, 45);
            
            // Delete existing node if any
            DB::table('site_nodes')->where('node_code', $nodeCode)->delete();
            
            // Insert new site node
            $nodeId = DB::table('site_nodes')->insertGetId([
                'node_code' => $nodeCode,
                'node_name' => $title . ' Guide',
                'display_name' => 'Hướng dẫn ' . $title,
                'parent_id' => $parentId,
                'icon' => 'fas fa-book',
                'masterpage' => '_LayoutPortalTwoCol-Left',
                'is_active' => 1,
                'allow_guest' => 1,
                'content' => $detailedContent,
                'content_html' => $detailedContent,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Set guide url to point to the newly created page
            $hdUrl = "/" . $nodeCode;
            echo "  Created Guide Page for '$title' at code '$nodeCode'\n";
        }

        // If we don't have detailed content, or parent is not set, set hdUrl to the detail page in OnlineDatabase
        // (the detail page displays the short content/description)
        $dbId = $index + 1;
        if (empty($hdUrl)) {
            $hdUrl = "/tai-nguyen/co-so-du-lieu-chi-tiet/{$dbId}";
        }

        // G. Insert into MySQL online_databases table
        DB::table('online_databases')->insert([
            'id' => $dbId,
            'title' => $title,
            'image_url' => $imageUrl,
            'url' => $accessUrl,
            'hd_url' => $hdUrl,
            'content' => $shortDesc,
            'sort_order' => $index,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "  [SUCCESS] Synced Database: $title\n";
        echo "    URL: $accessUrl\n";
        echo "    Guide URL: $hdUrl\n\n";
        $importedCount++;
    }

    echo "Total $importedCount databases fully synchronized successfully.\n";
    echo "=== SYNC COMPLETED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
