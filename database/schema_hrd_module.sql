-- ==========================================================
-- SKEMA BASIS DATA MODUL HRD DASAR
-- Data Master Pegawai, Divisi, Shift Kerja, Libur Nasional, & Jadwal Kerja
-- DBMS: MySQL / MariaDB (InnoDB Engine)
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `T_JADWAL_KERJA`;
DROP TABLE IF EXISTS `M_LIBUR_NASIONAL`;
DROP TABLE IF EXISTS `M_PEGAWAI`;
DROP TABLE IF EXISTS `M_SHIFT`;
DROP TABLE IF EXISTS `M_DIVISI`;
DROP TABLE IF EXISTS `M_KELOMPOK`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. TABEL REFERENSI DIVISI
CREATE TABLE `M_DIVISI` (
    `ID_DIVISI` INT UNSIGNED AUTO_INCREMENT,
    `NAMA_DIVISI` VARCHAR(100) NOT NULL,
    `KETERANGAN` VARCHAR(255) NULL,
    `CREATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `UPDATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID_DIVISI`),
    UNIQUE KEY `UQ_DIVISI_NAMA` (`NAMA_DIVISI`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Divisi / Departemen';

-- 2. TABEL REFERENSI KELOMPOK (REGU / GROUP KERJA)
CREATE TABLE `M_KELOMPOK` (
    `ID_KELOMPOK` INT UNSIGNED AUTO_INCREMENT,
    `NAMA_KELOMPOK` VARCHAR(100) NOT NULL,
    `KETERANGAN` VARCHAR(255) NULL,
    `CREATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `UPDATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID_KELOMPOK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Kelompok / Regu Kerja';

-- 3. TABEL REFERENSI MASTER SHIFT
CREATE TABLE `M_SHIFT` (
    `ID_JADWAL` INT UNSIGNED AUTO_INCREMENT,
    `SHIFT_CODE` VARCHAR(20) NOT NULL,
    `NAMA_SHIFT` VARCHAR(50) NOT NULL,
    `JAM_MULAI` TIME NOT NULL COMMENT 'Waktu shift dimulai (contoh: 07:00:00)',
    `JAM_SELESAI` TIME NOT NULL COMMENT 'Waktu shift berakhir (contoh: 15:00:00)',
    `JAM_AWAL` TIME NOT NULL COMMENT 'Batas awal scan fingerprint/absen masuk (contoh: 06:00:00)',
    `JAM_AKHIR` TIME NOT NULL COMMENT 'Batas akhir toleransi scan absen pulang (contoh: 16:30:00)',
    `IS_OVERNIGHT` TINYINT(1) DEFAULT 0 COMMENT '1 jika shift melewati pergantian hari (lintas malam)',
    `WARNA_LABEL` VARCHAR(20) DEFAULT '#3B82F6' COMMENT 'Kode warna hex untuk visualisasi kalender',
    `CREATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `UPDATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID_JADWAL`),
    UNIQUE KEY `UQ_SHIFT_CODE` (`SHIFT_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Konfigurasi Shift Kerja';

-- 4. TABEL MASTER PEGAWAI
CREATE TABLE `M_PEGAWAI` (
    `ID_PEGAWAI` INT UNSIGNED AUTO_INCREMENT,
    `ID_PEGAWAI_MESIN` VARCHAR(50) NULL COMMENT 'ID User pada Mesin Fingerprint / Attendance Machine',
    `NM_PEGAWAI` VARCHAR(150) NOT NULL,
    `ID_DIVISI` INT UNSIGNED NOT NULL,
    `JENIS_PEGAWAI` ENUM('Staff', 'Harian', 'Kontrak', 'Magang') NOT NULL DEFAULT 'Harian',
    `IS_AKTIF` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Aktif, 0 = Non-Aktif / Resign',
    `ALAMAT` TEXT NULL,
    `NO_TELP_HP` VARCHAR(25) NULL,
    `ID_KELOMPOK` INT UNSIGNED NULL,
    `CREATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `UPDATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID_PEGAWAI`),
    KEY `IDX_PEGAWAI_MESIN` (`ID_PEGAWAI_MESIN`),
    KEY `IDX_PEGAWAI_DIVISI` (`ID_DIVISI`),
    KEY `IDX_PEGAWAI_KELOMPOK` (`ID_KELOMPOK`),
    KEY `IDX_PEGAWAI_STATUS` (`IS_AKTIF`),
    CONSTRAINT `FK_PEGAWAI_DIVISI` FOREIGN KEY (`ID_DIVISI`) REFERENCES `M_DIVISI` (`ID_DIVISI`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `FK_PEGAWAI_KELOMPOK` FOREIGN KEY (`ID_KELOMPOK`) REFERENCES `M_KELOMPOK` (`ID_KELOMPOK`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Data Pegawai';

-- 5. TABEL MASTER HARI LIBUR NASIONAL & CUTI BERSAMA
CREATE TABLE `M_LIBUR_NASIONAL` (
    `ID_LIBUR` INT UNSIGNED AUTO_INCREMENT,
    `TANGGAL` DATE NOT NULL,
    `KETERANGAN` VARCHAR(255) NOT NULL COMMENT 'Deskripsi nama hari libur / cuti bersama',
    `JENIS_LIBUR` ENUM('Nasional', 'Cuti Bersama', 'Khusus Perusahaan') NOT NULL DEFAULT 'Nasional',
    `IS_DIBAYAR` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 jika diperhitungkan dalam hari kerja berbayar',
    `CREATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `UPDATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID_LIBUR`),
    UNIQUE KEY `UQ_LIBUR_TANGGAL` (`TANGGAL`),
    KEY `IDX_LIBUR_PERIODE` (`TANGGAL`, `JENIS_LIBUR`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Hari Libur Nasional dan Cuti Bersama';

-- 6. TABEL PENGHUBUNG PENUGASAN SHIFT PEGAWAI (ROSTER / ROSTERING JADWAL KERJA)
CREATE TABLE `T_JADWAL_KERJA` (
    `ID_PENUGASAN` BIGINT UNSIGNED AUTO_INCREMENT,
    `ID_PEGAWAI` INT UNSIGNED NOT NULL,
    `TANGGAL` DATE NOT NULL,
    `ID_JADWAL` INT UNSIGNED NULL COMMENT 'Foreign Key ke M_SHIFT. Null jika status LIBUR / OFF',
    `STATUS_KERJA` ENUM('Kerja', 'Off', 'Cuti', 'Izin', 'Sakit', 'Libur Nasional') NOT NULL DEFAULT 'Kerja',
    `KETERANGAN` VARCHAR(255) NULL COMMENT 'Catatan penugasan (contoh: Tukar shift atau SPL)',
    `IS_OVERWRITE` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 jika merupakan hasil penggantian manual (override)',
    `ASSIGNED_BY` VARCHAR(50) NULL COMMENT 'Username HRD/Supervisor penanggung jawab jadwal',
    `CREATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `UPDATED_AT` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID_PENUGASAN`),
    -- Memastikan 1 pegawai hanya memiliki 1 status jadwal per tanggal kalender:
    UNIQUE KEY `UQ_PEGAWAI_TANGGAL` (`ID_PEGAWAI`, `TANGGAL`),
    KEY `IDX_JADWAL_TANGGAL` (`TANGGAL`),
    KEY `IDX_JADWAL_SHIFT` (`ID_JADWAL`),
    CONSTRAINT `FK_JADWAL_PEGAWAI` FOREIGN KEY (`ID_PEGAWAI`) REFERENCES `M_PEGAWAI` (`ID_PEGAWAI`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `FK_JADWAL_SHIFT` FOREIGN KEY (`ID_JADWAL`) REFERENCES `M_SHIFT` (`ID_JADWAL`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel Transaksi Penugasan Shift & Roster Kerja Harian';

-- ==========================================================
-- SAMPLE SEED DATA
-- ==========================================================

INSERT INTO `M_DIVISI` (`ID_DIVISI`, `NAMA_DIVISI`, `KETERANGAN`) VALUES
(1, 'Teknologi Informasi', 'Divisi IT & Infrastruktur'),
(2, 'Produksi & Operasional', 'Divisi Produksi Pabrik & Gudang'),
(3, 'Keamanan & HSE', 'Security, Satpam & Keselamatan Kerja'),
(4, 'Human Resource & GA', 'SDM, Personalia & Umum'),
(5, 'Keuangan & Akuntansi', 'Finance & Accounting'),
(6, 'Fabrikasi', 'Divisi Fabrikasi'),
(7, 'Galvanish', 'Divisi Galvanish'),
(8, 'Finance', 'Divisi Finance');

INSERT INTO `M_KELOMPOK` (`ID_KELOMPOK`, `NAMA_KELOMPOK`, `KETERANGAN`) VALUES
(1, 'Regu Alfa (Pagi)', 'Regu operasional shift 1'),
(2, 'Regu Beta (Sore)', 'Regu operasional shift 2'),
(3, 'Regu Gamma (Malam)', 'Regu operasional shift 3'),
(4, 'Non-Shift (Kantor)', 'Karyawan general office');

INSERT INTO `M_SHIFT` (`ID_JADWAL`, `SHIFT_CODE`, `NAMA_SHIFT`, `JAM_MULAI`, `JAM_SELESAI`, `JAM_AWAL`, `JAM_AKHIR`, `IS_OVERNIGHT`, `WARNA_LABEL`) VALUES
(1, 'SH-PAGI', 'Shift Pagi Reguler', '07:00:00', '15:00:00', '06:00:00', '16:00:00', 0, '#10B981'),
(2, 'SH-SORE', 'Shift Sore Reguler', '15:00:00', '23:00:00', '14:00:00', '23:59:59', 0, '#F59E0B'),
(3, 'SH-MLM',  'Shift Malam Lintas Hari', '23:00:00', '07:00:00', '22:00:00', '08:00:00', 1, '#8B5CF6'),
(4, 'NON-SH',  'Non Shift (Office Hour)', '08:00:00', '17:00:00', '07:00:00', '18:00:00', 0, '#3B82F6');

INSERT INTO `M_PEGAWAI` (`ID_PEGAWAI`, `ID_PEGAWAI_MESIN`, `NM_PEGAWAI`, `ID_DIVISI`, `JENIS_PEGAWAI`, `IS_AKTIF`, `ALAMAT`, `NO_TELP_HP`, `ID_KELOMPOK`) VALUES
(1, 'FP001', 'Budi Santoso', 1, 'Staff', 1, 'Jl. Melati No. 12, Jakarta', '081234567890', 4),
(2, 'FP002', 'Siti Aminah', 2, 'Harian', 1, 'Jl. Mawar No. 45, Tangerang', '081398765432', 1),
(3, 'FP003', 'Agus Setiawan', 3, 'Harian', 1, 'Jl. Kenanga No. 8, Bekasi', '085712345678', 3),
(4, 'FP004', 'Dewi Rahayu', 4, 'Staff', 1, 'Jl. Cempaka No. 20, Depok', '082133445566', 4),
(5, 'FP005', 'Rendi Pratama', 2, 'Harian', 1, 'Jl. Anggrek No. 15, Bogor', '087811223344', 2),
(6, 'FP006', 'Maya Kurnia', 2, 'Harian', 0, 'Jl. Dahlia No. 3, Jakarta', '081900112233', 1);

INSERT INTO `M_LIBUR_NASIONAL` (`ID_LIBUR`, `TANGGAL`, `KETERANGAN`, `JENIS_LIBUR`, `IS_DIBAYAR`) VALUES
(1, '2026-01-01', 'Tahun Baru 2026 Masehi', 'Nasional', 1),
(2, '2026-02-17', 'Tahun Baru Imlek 2577 Kongzili', 'Nasional', 1),
(3, '2026-03-20', 'Hari Raya Idul Fitri 1447 H (Hari ke-1)', 'Nasional', 1),
(4, '2026-03-21', 'Hari Raya Idul Fitri 1447 H (Hari ke-2)', 'Nasional', 1),
(5, '2026-03-23', 'Cuti Bersama Hari Raya Idul Fitri', 'Cuti Bersama', 1),
(6, '2026-05-01', 'Hari Buruh Internasional', 'Nasional', 1),
(7, '2026-08-17', 'Hari Kemerdekaan RI ke-81', 'Nasional', 1),
(8, '2026-12-25', 'Hari Raya Natal', 'Nasional', 1);

INSERT INTO `T_JADWAL_KERJA` (`ID_PEGAWAI`, `TANGGAL`, `ID_JADWAL`, `STATUS_KERJA`, `KETERANGAN`, `ASSIGNED_BY`) VALUES
(2, CURDATE(), 1, 'Kerja', 'Shift Reguler Pagi', 'admin_hrd'),
(3, CURDATE(), 3, 'Kerja', 'Shift Malam Penjagaan', 'admin_hrd'),
(5, CURDATE(), 2, 'Kerja', 'Shift Reguler Sore', 'admin_hrd'),
(1, CURDATE(), 4, 'Kerja', 'Office Day', 'admin_hrd');
