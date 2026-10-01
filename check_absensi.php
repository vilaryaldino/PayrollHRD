<?php
$pdo = new PDO('mysql:host=localhost;dbname=payrollhrd', 'root', '');
print_r($pdo->query("SELECT id_pegawai, id_pegawai_mesin, nama_pegawai FROM t_data_absensi LIMIT 5")->fetchAll(PDO::FETCH_ASSOC));
