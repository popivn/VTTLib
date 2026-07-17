<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== ZDBRESOURCES TABLE ===\n";
    $stmt = $conn->query("SELECT * FROM ZDBRESOURCES");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
