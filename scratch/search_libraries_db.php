<?php
try {
    $conn = new PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all tables and columns
    $stmt = $conn->query("
        SELECT 
            t.name AS table_name,
            c.name AS column_name
        FROM sys.tables t
        JOIN sys.columns c ON t.object_id = c.object_id
        JOIN sys.types ty ON c.user_type_id = ty.user_type_id
        WHERE ty.name IN ('varchar', 'nvarchar', 'text', 'ntext', 'char', 'nchar')
        ORDER BY t.name, c.column_id
    ");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnsByTable = [];
    foreach ($cols as $col) {
        $columnsByTable[$col['table_name']][] = $col['column_name'];
    }

    $keywords = ['cantholib', 'nlv.gov.vn', 'Quốc gia Việt Nam', 'Quốc hội Mỹ', 'Thư viện Lạc Việt', 'Thư viện Thành phố'];

    echo "=== SEARCHING ALL TABLES FOR NETWORK LIBRARIES ===\n";
    foreach ($columnsByTable as $table => $columns) {
        $where = [];
        foreach ($columns as $col) {
            $escaped = "[$col]";
            foreach ($keywords as $kw) {
                $where[] = "$escaped LIKE N'%$kw%'";
            }
        }
        if (empty($where)) continue;
        
        $whereStr = implode(' OR ', $where);
        try {
            $count = $conn->query("SELECT COUNT(*) FROM [$table] WHERE $whereStr")->fetchColumn();
            if ($count > 0) {
                echo "Table: $table (Found $count matches)\n";
                $data = $conn->query("SELECT TOP 5 * FROM [$table] WHERE $whereStr")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($data as $rowIndex => $row) {
                    echo "  Row $rowIndex:\n";
                    foreach ($row as $k => $v) {
                        if ($v !== null && is_string($v)) {
                            $matched = false;
                            foreach ($keywords as $kw) {
                                if (stripos($v, $kw) !== false) {
                                    $matched = true;
                                    break;
                                }
                            }
                            if ($matched) {
                                echo "    - $k: $v\n";
                            }
                        }
                    }
                }
                echo "\n";
            }
        } catch (Exception $e) {
            // ignore
        }
    }

} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
