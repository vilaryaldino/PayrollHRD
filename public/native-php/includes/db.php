<?php
// config/database.php - Koneksi MySQL PDO untuk native-php & modul payrollhrd
$host = '127.0.0.1';
$port = '3306';
$db   = 'payrollhrd';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    $pdo = null;
    $db_error = $e->getMessage();
    die("<div style='margin: 30px auto; max-width: 650px; padding: 20px; background: #fee2e2; border: 1px solid #ef4444; border-radius: 8px; color: #991b1b; font-family: system-ui, sans-serif;'>
        <h3 style='margin-top:0;'>⚠️ Gagal Terhubung ke Database MySQL</h3>
        <p><strong>Pesan Error:</strong> " . htmlspecialchars($db_error) . "</p>
        <hr style='border: 0; border-top: 1px solid #f87171; margin: 15px 0;'>
        <p style='margin-bottom: 5px;'><strong>Solusi:</strong></p>
        <ul style='margin-top: 5px;'>
            <li>Pastikan service <strong>MySQL</strong> di panel XAMPP berstatus <strong>Running</strong> (hijau).</li>
            <li>Pastikan database <code>" . htmlspecialchars($db) . "</code> ada di phpMyAdmin / MySQL.</li>
        </ul>
    </div>");
}

