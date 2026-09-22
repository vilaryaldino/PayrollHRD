<?php
// Migrator script untuk meng-eksekusi skema database ke database payrollhrd MySQL
require_once __DIR__ . '/../public/native-php/includes/db.php';

if (!$pdo) {
    die("Koneksi MySQL gagal: " . ($db_error ?? 'Unknown error') . "\n");
}

$sqlFile = __DIR__ . '/schema_hrd_module.sql';
if (!file_exists($sqlFile)) {
    die("File SQL $sqlFile tidak ditemukan!\n");
}

$sql = file_get_contents($sqlFile);

try {
    // Eksekusi skema SQL
    $pdo->exec($sql);
    echo "BERHASIL: Semua tabel dan data awal di database 'payrollhrd' telah berhasil dibuat!\n";
    
    // Tampilkan daftar tabel yang baru dibuat
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabel saat ini di database payrollhrd (" . count($tables) . "):\n";
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "  - $t ($count baris data)\n";
    }
} catch (PDOException $e) {
    echo "GAGAL: " . $e->getMessage() . "\n";
}
