<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Let's query AGENCIES
    echo "=== AGENCIES TABLE ===\n";
    try {
        $stmt = $conn->query("SELECT * FROM AGENCIES");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

    // Let's query LIBRARYINF
    echo "\n=== LIBRARYINF TABLE ===\n";
    try {
        $stmt = $conn->query("SELECT * FROM LIBRARYINF");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
