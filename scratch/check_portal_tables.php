<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== PORTAL_DATABASE ===\n";
    try {
        $stmt = $conn->query("SELECT * FROM PORTAL_DATABASE");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

    echo "\n=== PORTAL_SITE ===\n";
    try {
        $stmt = $conn->query("SELECT * FROM PORTAL_SITE");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
