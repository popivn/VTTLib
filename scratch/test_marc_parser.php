<?php
function getMarcTag001($marc) {
    if (strlen($marc) < 24) return null;
    $baseAddress = intval(substr($marc, 12, 5));
    $directory = substr($marc, 24, $baseAddress - 24 - 1); // sometimes there is a field terminator, but directory ends before baseAddress
    
    // Better way: find directory until field terminator (character 0x1e)
    $dirEnd = strpos($marc, "\x1e");
    if ($dirEnd === false) {
        $dirEnd = $baseAddress - 1;
    }
    $directory = substr($marc, 24, $dirEnd - 24);
    
    $len = strlen($directory);
    for ($i = 0; $i < $len; $i += 12) {
        if ($i + 12 > $len) break;
        $tag = substr($directory, $i, 3);
        $fieldLength = intval(substr($directory, $i + 3, 4));
        $fieldOffset = intval(substr($directory, $i + 7, 5));
        
        if ($tag === '001') {
            $val = substr($marc, $baseAddress + $fieldOffset, $fieldLength);
            return trim(preg_replace('/[^0-9\-]/', '', $val));
        }
    }
    return null;
}

try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $bibIds = [9570, 7877, 5283, 5107, 4928, 3341];

    foreach ($bibIds as $id) {
        $stmt = $conn->prepare("SELECT TOP 1 MARC21 FROM BIBLIOGRAPHIC WHERE BIBID = :bibid");
        $stmt->execute(['bibid' => $id]);
        $marc = $stmt->fetchColumn();
        
        $m001 = getMarcTag001($marc);
        echo "BIBID $id -> MARC 001 extracted: '$m001'\n";
    }
} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
