<?php
$pdo = new PDO('mysql:host=localhost;dbname=payrollhrd', 'root', '');
$stmt = $pdo->query("SELECT * FROM t_register_lembur WHERE nama_pegawai='JUPE'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
