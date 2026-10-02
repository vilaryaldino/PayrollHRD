<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("
    CREATE TABLE IF NOT EXISTS `t_dinas_luar` (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `id_pegawai` int(10) UNSIGNED NOT NULL,
      `jenis_dinas` enum('LK','LP') NOT NULL COMMENT 'LK = Luar Kota, LP = Luar Pulau',
      `tanggal_mulai` date NOT NULL,
      `tanggal_selesai` date NOT NULL,
      `jumlah_hari` int(11) NOT NULL,
      `tarif_um` decimal(10,2) DEFAULT 0.00 COMMENT 'Nominal Uang Makan per hari',
      `total_uang_makan` decimal(12,2) DEFAULT 0.00,
      `catatan` varchar(255) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table created successfully.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
