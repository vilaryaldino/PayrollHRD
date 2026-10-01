<?php
$pdo = new PDO('mysql:host=localhost;dbname=payrollhrd', 'root', '');
$stmt = $pdo->query("SELECT id_pegawai, nama_pegawai, tanggal, jam_kehadiran, jam_kepulangan FROM t_data_absensi WHERE id_pegawai='110' OR id_pegawai_mesin='110' OR nama_pegawai='JUPE'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
