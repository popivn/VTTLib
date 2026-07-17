<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== PORTAL_DOCUMENT_CATEGORY ===\n";
    $stmt = $conn->query("SELECT * FROM PORTAL_DOCUMENT_CATEGORY");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "ID: {$row['ID']} | Parent: {$row['PARENTID']} | Name: {$row['NAME']}\n";
    }

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
