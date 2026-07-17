<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== PORTAL_DATABASE ROW COUNT ===\n";
    $count = $conn->query("SELECT COUNT(*) FROM PORTAL_DATABASE")->fetchColumn();
    echo "Total: $count\n";

    if ($count > 0) {
        $stmt = $conn->query("SELECT * FROM PORTAL_DATABASE");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
