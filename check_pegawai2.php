<?php
$pdo = new PDO('mysql:host=localhost;dbname=payrollhrd', 'root', '');
$stmt = $pdo->query("SELECT ID_PEGAWAI, ID_PEGAWAI_MESIN, NM_PEGAWAI FROM M_PEGAWAI WHERE NM_PEGAWAI LIKE '%JUPE%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
