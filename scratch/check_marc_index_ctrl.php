<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== COLUMNS IN MARC21INDEXCTRL ===\n";
    $stmt = $conn->query("
        SELECT COLUMN_NAME, DATA_TYPE 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'MARC21INDEXCTRL'
    ");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "  - {$col['COLUMN_NAME']} ({$col['DATA_TYPE']})\n";
    }

    echo "\n=== SAMPLE FROM MARC21INDEXCTRL ===\n";
    $stmtData = $conn->query("SELECT TOP 10 * FROM MARC21INDEXCTRL");
    $rows = $stmtData->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
