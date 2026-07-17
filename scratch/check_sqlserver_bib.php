<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $bibIds = [9570, 7877, 5283, 5107, 4928, 3341];

    foreach ($bibIds as $id) {
        echo "=== SQL SERVER BIBLIOGRAPHIC RECORD: $id ===\n";
        $stmt = $conn->prepare("SELECT TOP 1 MARC21 FROM BIBLIOGRAPHIC WHERE BIBID = :bibid");
        $stmt->execute(['bibid' => $id]);
        $marc = $stmt->fetchColumn();
        
        if ($marc) {
            echo "MARC21: " . substr($marc, 0, 500) . "...\n\n";
        } else {
            echo "Not found in BIBLIOGRAPHIC.\n\n";
        }
    }
} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
