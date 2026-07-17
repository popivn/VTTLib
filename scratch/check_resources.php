<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== RESOURCES TABLE ===\n";
    $stmt = $conn->query("SELECT TOP 50 * FROM RESOURCES");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
