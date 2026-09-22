<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=payrollhrd;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "STATUS: CONNECTED\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES (" . count($tables) . "): " . implode(', ', $tables) . "\n";
    
    foreach ($tables as $t) {
        echo "\n--- Columns in $t ---\n";
        $cols = $pdo->query("DESCRIBE `$t`")->fetchAll();
        foreach ($cols as $c) {
            echo "  {$c['Field']} ({$c['Type']})\n";
        }
    }
} catch (PDOException $e) {
    echo "STATUS: ERROR - " . $e->getMessage() . "\n";
}
