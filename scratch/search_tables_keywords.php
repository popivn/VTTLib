<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $keywords = ['link', 'partner', 'logo', 'network', 'member', 'web', 'site', 'url', 'agency', 'portal'];
    
    $stmt = $conn->query("SELECT name FROM sys.tables ORDER BY name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "=== TABLES MATCHING KEYWORDS IN SQL SERVER ===\n";
    foreach ($tables as $t) {
        foreach ($keywords as $kw) {
            if (stripos($t, $kw) !== false) {
                echo "- $t (matched '$kw')\n";
                break;
            }
        }
    }
} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
