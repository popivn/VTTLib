<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmtDoc = $conn->query("SELECT * FROM PORTAL_DOCUMENT WHERE CATEGORYID = 233");
    $rows = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rows as $row) {
        echo "=== Doc ID: {$row['ID']} ===\n";
        foreach ($row as $k => $v) {
            if ($k === 'IMAGE') {
                echo "  $k: [binary data of size " . ($v ? strlen($v) : 0) . "]\n";
            } else {
                echo "  $k: " . var_export($v, true) . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
