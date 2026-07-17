<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== SEARCHING PORTAL_SITE FOR LINKS OR LIBRARIES ===\n";
    
    // Search in all string columns of PORTAL_SITE
    $keywords = ['cantholib', 'nlv.gov.vn', 'thuvien', 'thu vien', 'library-network', 'http', 'https'];
    
    $stmt = $conn->query("SELECT * FROM PORTAL_SITE");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rows as $row) {
        $matched = false;
        $matchedWord = '';
        foreach ($row as $k => $v) {
            if ($v !== null && is_string($v)) {
                foreach ($keywords as $kw) {
                    if (stripos($v, $kw) !== false) {
                        $matched = true;
                        $matchedWord = $kw;
                        break 2;
                    }
                }
            }
        }
        if ($matched) {
            echo "Matched row ID: {$row['ID']} | Name: {$row['NAME']} | Parent ID: {$row['PARENTID']} | Link: {$row['LINK']} | Match Word: '$matchedWord'\n";
            // Print a snippet of CONTENT if it has link
            if (!empty($row['CONTENT'])) {
                if (preg_match_all('/href="([^"]+)"/i', $row['CONTENT'], $m)) {
                    foreach ($m[1] as $url) {
                        echo "  Found Link: $url\n";
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
