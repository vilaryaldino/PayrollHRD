<?php
$pdo = new PDO('mysql:host=localhost;dbname=payrollhrd', 'root', '');
$stmt = $pdo->query("DESCRIBE M_PEGAWAI");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query("SELECT ID_PEGAWAI, NM_PEGAWAI, PIN FROM M_PEGAWAI LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
