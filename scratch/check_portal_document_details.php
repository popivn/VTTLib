<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== COLUMNS IN PORTAL_DOCUMENT ===\n";
    $stmt = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'PORTAL_DOCUMENT'");
    while ($col = $stmt->fetchColumn()) {
        echo "  - $col\n";
    }

    echo "\n=== PORTAL_DOCUMENT_CATEGORY ===\n";
    $stmtCat = $conn->query("SELECT * FROM PORTAL_DOCUMENT_CATEGORY");
    print_r($stmtCat->fetchAll(PDO::FETCH_ASSOC));

    echo "\n=== ROWS IN PORTAL_DOCUMENT WITH CATEGORYID = 233 ===\n";
    // Let's select all fields
    $stmtDoc = $conn->query("SELECT * FROM PORTAL_DOCUMENT WHERE CATEGORYID = 233");
    $rows = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "--------------------------\n";
        foreach ($row as $k => $v) {
            if ($k === 'IMAGE' && $v !== null) {
                echo "  $k: [binary data of size " . strlen($v) . "]\n";
            } else {
                echo "  $k: $v\n";
            }
        }
    }

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
