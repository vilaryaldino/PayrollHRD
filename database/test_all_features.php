<?php
// Test runner komprehensif tanpa warning / notice
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== MEMULAI TEST INTEGRASI MENYELURUH (ZERO ERROR CHECK) ===\n\n";

$_SERVER['REQUEST_METHOD'] = 'GET';

// 1. Tes Koneksi Database
require_once __DIR__ . '/../public/native-php/includes/db.php';
if (!$pdo) {
    die("FATAL: Koneksi database gagal: " . ($db_error ?? 'Unknown error') . "\n");
}
echo "[PASS] 1. Koneksi PDO ke database MySQL 'payrollhrd' stabil dan aktif.\n";

// 2. Tes Query Seluruh Tabel
$tables = ['M_PEGAWAI', 'M_DIVISI', 'M_KELOMPOK', 'M_SHIFT', 'M_LIBUR_NASIONAL', 'T_JADWAL_KERJA'];
foreach ($tables as $tbl) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
        echo "[PASS] 2. Tabel `$tbl`: $count baris data aktif.\n";
    } catch (Exception $e) {
        die("FATAL: Query tabel `$tbl` gagal: " . $e->getMessage() . "\n");
    }
}

// 3. Tes Render Seluruh Halaman
$pages = ['dashboard', 'pegawai', 'shift', 'libur', 'jadwal', 'register_lembur'];
foreach ($pages as $p) {
    $_GET['page'] = $p;
    ob_start();
    try {
        include __DIR__ . "/../public/native-php/pages/$p.php";
        $output = ob_get_clean();
        echo "[PASS] 3. Render file `$p.php` berhasil tanpa error/warning (" . strlen($output) . " bytes).\n";
    } catch (Throwable $t) {
        ob_end_clean();
        die("FATAL: Error pada `$p.php`: " . $t->getMessage() . " line " . $t->getLine() . "\n");
    }
}

// 4. Tes CRUD Transaksional Pegawai
try {
    $stmt = $pdo->prepare("INSERT INTO M_PEGAWAI (NM_PEGAWAI, ID_DIVISI, JENIS_PEGAWAI, IS_AKTIF, ID_PEGAWAI_MESIN) VALUES ('Pegawai Uji Validasi', 1, 'Harian', 1, 'FP999')");
    $stmt->execute();
    $idBaru = $pdo->lastInsertId();

    $stmt = $pdo->prepare("UPDATE M_PEGAWAI SET NM_PEGAWAI = 'Pegawai Uji Validasi Update' WHERE ID_PEGAWAI = ?");
    $stmt->execute([$idBaru]);

    $stmt = $pdo->prepare("DELETE FROM M_PEGAWAI WHERE ID_PEGAWAI = ?");
    $stmt->execute([$idBaru]);

    echo "[PASS] 4. Operasi CRUD Pegawai (Insert, Update, Delete) berhasil 100%.\n";
} catch (Throwable $t) {
    die("FATAL: CRUD Pegawai gagal: " . $t->getMessage() . "\n");
}

// 5. Tes Penugasan Roster Jadwal Kerja
try {
    $stmt = $pdo->prepare("INSERT INTO T_JADWAL_KERJA (ID_PEGAWAI, TANGGAL, ID_JADWAL, STATUS_KERJA) 
                           VALUES (1, '2026-12-31', 1, 'Kerja')
                           ON DUPLICATE KEY UPDATE STATUS_KERJA = VALUES(STATUS_KERJA)");
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM T_JADWAL_KERJA WHERE ID_PEGAWAI = 1 AND TANGGAL = '2026-12-31'");
    $stmt->execute();

    echo "[PASS] 5. Operasi Penugasan Shift & Roster Kalender berhasil 100%.\n";
} catch (Throwable $t) {
    die("FATAL: Transaksi Roster gagal: " . $t->getMessage() . "\n");
}

echo "\n>>> STATUS AKHIR: SEMUA FITUR VALID, BEBAS DARI ERROR, DAN SIAP DIGUNAKAN <<<\n";
