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
    // Jika koneksi gagal, catat error
    $pdo = null;
    $db_error = $e->getMessage();
}
