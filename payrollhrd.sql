-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Sep 2026 pada 07.58
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payrollhrd`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `m_divisi`
--

CREATE TABLE `m_divisi` (
  `ID_DIVISI` int(10) UNSIGNED NOT NULL,
  `NAMA_DIVISI` varchar(100) NOT NULL,
  `KETERANGAN` varchar(255) DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Divisi / Departemen';

--
-- Dumping data untuk tabel `m_divisi`
--

INSERT INTO `m_divisi` (`ID_DIVISI`, `NAMA_DIVISI`, `KETERANGAN`, `CREATED_AT`, `UPDATED_AT`) VALUES
(1, 'Teknologi Informasi', 'Divisi IT & Infrastruktur', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(2, 'Produksi & Operasional', 'Divisi Produksi Pabrik & Gudang', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(3, 'Keamanan & HSE', 'Security, Satpam & Keselamatan Kerja', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(4, 'Human Resource & GA', 'SDM, Personalia & Umum', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(5, 'Keuangan & Akuntansi', 'Finance & Accounting', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(6, 'Fabrikasi', 'Divisi Fabrikasi', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(7, 'Galvanish', 'Divisi Galvanish', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(8, 'Finance', 'Divisi Finance', '2026-09-22 03:52:42', '2026-09-22 03:52:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `m_kelompok`
--

CREATE TABLE `m_kelompok` (
  `ID_KELOMPOK` int(10) UNSIGNED NOT NULL,
  `NAMA_KELOMPOK` varchar(100) NOT NULL,
  `KETERANGAN` varchar(255) DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Kelompok / Regu Kerja';

--
-- Dumping data untuk tabel `m_kelompok`
--

INSERT INTO `m_kelompok` (`ID_KELOMPOK`, `NAMA_KELOMPOK`, `KETERANGAN`, `CREATED_AT`, `UPDATED_AT`) VALUES
(1, 'Regu Alfa (Pagi)', 'Regu operasional shift 1', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(2, 'Regu Beta (Sore)', 'Regu operasional shift 2', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(3, 'Regu Gamma (Malam)', 'Regu operasional shift 3', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(4, 'Non-Shift (Kantor)', 'Karyawan general office', '2026-09-22 03:52:42', '2026-09-22 03:52:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `m_libur_nasional`
--

CREATE TABLE `m_libur_nasional` (
  `ID_LIBUR` int(10) UNSIGNED NOT NULL,
  `TANGGAL` date NOT NULL,
  `KETERANGAN` varchar(255) NOT NULL COMMENT 'Deskripsi nama hari libur / cuti bersama',
  `JENIS_LIBUR` enum('Nasional','Cuti Bersama','Khusus Perusahaan') NOT NULL DEFAULT 'Nasional',
  `IS_DIBAYAR` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 jika diperhitungkan dalam hari kerja berbayar',
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Hari Libur Nasional dan Cuti Bersama';

--
-- Dumping data untuk tabel `m_libur_nasional`
--

INSERT INTO `m_libur_nasional` (`ID_LIBUR`, `TANGGAL`, `KETERANGAN`, `JENIS_LIBUR`, `IS_DIBAYAR`, `CREATED_AT`, `UPDATED_AT`) VALUES
(1, '2026-01-01', 'Tahun Baru 2026 Masehi', 'Nasional', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(2, '2026-02-17', 'Tahun Baru Imlek 2577 Kongzili', 'Nasional', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(3, '2026-03-20', 'Hari Raya Idul Fitri 1447 H (Hari ke-1)', 'Nasional', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(4, '2026-03-21', 'Hari Raya Idul Fitri 1447 H (Hari ke-2)', 'Nasional', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(5, '2026-03-23', 'Cuti Bersama Hari Raya Idul Fitri', 'Cuti Bersama', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(6, '2026-05-01', 'Hari Buruh Internasional', 'Nasional', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(7, '2026-08-17', 'Hari Kemerdekaan RI ke-81', 'Nasional', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(8, '2026-12-25', 'Hari Raya Natal', 'Nasional', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `m_pegawai`
--

CREATE TABLE `m_pegawai` (
  `ID_PEGAWAI` int(10) UNSIGNED NOT NULL,
  `ID_PEGAWAI_MESIN` varchar(50) DEFAULT NULL COMMENT 'ID User pada Mesin Fingerprint / Attendance Machine',
  `NM_PEGAWAI` varchar(150) NOT NULL,
  `ID_DIVISI` int(10) UNSIGNED NOT NULL,
  `JENIS_PEGAWAI` enum('Staff','Harian','Kontrak','Magang') NOT NULL DEFAULT 'Harian',
  `IS_AKTIF` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Aktif, 0 = Non-Aktif / Resign',
  `ALAMAT` text DEFAULT NULL,
  `NO_TELP_HP` varchar(25) DEFAULT NULL,
  `ID_KELOMPOK` int(10) UNSIGNED DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Data Pegawai';

--
-- Dumping data untuk tabel `m_pegawai`
--

INSERT INTO `m_pegawai` (`ID_PEGAWAI`, `ID_PEGAWAI_MESIN`, `NM_PEGAWAI`, `ID_DIVISI`, `JENIS_PEGAWAI`, `IS_AKTIF`, `ALAMAT`, `NO_TELP_HP`, `ID_KELOMPOK`, `CREATED_AT`, `UPDATED_AT`) VALUES
(1, 'FP001', 'Budi Santoso', 1, 'Staff', 1, 'Jl. Melati No. 12, Jakarta', '081234567890', 4, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(2, 'FP002', 'Siti Aminah', 2, 'Harian', 1, 'Jl. Mawar No. 45, Tangerang', '081398765432', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(3, 'FP003', 'Agus Setiawan', 3, 'Harian', 1, 'Jl. Kenanga No. 8, Bekasi', '085712345678', 3, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(4, 'FP004', 'Dewi Rahayu', 4, 'Staff', 1, 'Jl. Cempaka No. 20, Depok', '082133445566', 4, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(5, 'FP005', 'Rendi Pratama', 2, 'Harian', 1, 'Jl. Anggrek No. 15, Bogor', '087811223344', 2, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(6, 'FP006', 'Maya Kurnia', 2, 'Harian', 0, 'Jl. Dahlia No. 3, Jakarta', '081900112233', 1, '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(7, 'FP006', 'Rohman Djawa', 4, 'Harian', 1, NULL, NULL, 4, '2026-09-22 04:35:11', '2026-09-22 04:54:30'),
(12, 'FP008', 'VILARY ALDINO EGREA', 3, 'Harian', 1, NULL, NULL, 4, '2026-09-22 05:54:12', '2026-09-22 05:54:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `m_shift`
--

CREATE TABLE `m_shift` (
  `ID_JADWAL` int(10) UNSIGNED NOT NULL,
  `SHIFT_CODE` varchar(20) NOT NULL,
  `NAMA_SHIFT` varchar(50) NOT NULL,
  `JAM_MULAI` time NOT NULL COMMENT 'Waktu shift dimulai (contoh: 07:00:00)',
  `JAM_SELESAI` time NOT NULL COMMENT 'Waktu shift berakhir (contoh: 15:00:00)',
  `JAM_AWAL` time NOT NULL COMMENT 'Batas awal scan fingerprint/absen masuk (contoh: 06:00:00)',
  `JAM_AKHIR` time NOT NULL COMMENT 'Batas akhir toleransi scan absen pulang (contoh: 16:30:00)',
  `IS_OVERNIGHT` tinyint(1) DEFAULT 0 COMMENT '1 jika shift melewati pergantian hari (lintas malam)',
  `WARNA_LABEL` varchar(20) DEFAULT '#3B82F6' COMMENT 'Kode warna hex untuk visualisasi kalender',
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master Konfigurasi Shift Kerja';

--
-- Dumping data untuk tabel `m_shift`
--

INSERT INTO `m_shift` (`ID_JADWAL`, `SHIFT_CODE`, `NAMA_SHIFT`, `JAM_MULAI`, `JAM_SELESAI`, `JAM_AWAL`, `JAM_AKHIR`, `IS_OVERNIGHT`, `WARNA_LABEL`, `CREATED_AT`, `UPDATED_AT`) VALUES
(1, 'SH-PAGI', 'Shift Pagi Reguler', '07:00:00', '15:00:00', '06:00:00', '16:00:00', 0, '#10B981', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(2, 'SH-SORE', 'Shift Sore Reguler', '15:00:00', '23:00:00', '14:00:00', '23:59:59', 0, '#F59E0B', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(3, 'SH-MLM', 'Shift Malam Lintas Hari', '23:00:00', '07:00:00', '22:00:00', '08:00:00', 1, '#8B5CF6', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(4, 'NON-SH', 'Non Shift (Office Hour)', '08:00:00', '17:00:00', '07:00:00', '18:00:00', 0, '#3B82F6', '2026-09-22 03:52:42', '2026-09-22 03:52:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_jadwal_kerja`
--

CREATE TABLE `t_jadwal_kerja` (
  `ID_PENUGASAN` bigint(20) UNSIGNED NOT NULL,
  `ID_PEGAWAI` int(10) UNSIGNED NOT NULL,
  `TANGGAL` date NOT NULL,
  `ID_JADWAL` int(10) UNSIGNED DEFAULT NULL COMMENT 'Foreign Key ke M_SHIFT. Null jika status LIBUR / OFF',
  `STATUS_KERJA` enum('Kerja','Off','Cuti','Izin','Sakit','Libur Nasional') NOT NULL DEFAULT 'Kerja',
  `KETERANGAN` varchar(255) DEFAULT NULL COMMENT 'Catatan penugasan (contoh: Tukar shift atau SPL)',
  `IS_OVERWRITE` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 jika merupakan hasil penggantian manual (override)',
  `ASSIGNED_BY` varchar(50) DEFAULT NULL COMMENT 'Username HRD/Supervisor penanggung jawab jadwal',
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel Transaksi Penugasan Shift & Roster Kerja Harian';

--
-- Dumping data untuk tabel `t_jadwal_kerja`
--

INSERT INTO `t_jadwal_kerja` (`ID_PENUGASAN`, `ID_PEGAWAI`, `TANGGAL`, `ID_JADWAL`, `STATUS_KERJA`, `KETERANGAN`, `IS_OVERWRITE`, `ASSIGNED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES
(1, 2, '2026-09-22', 1, 'Kerja', 'Shift Reguler Pagi', 0, 'admin_hrd', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(2, 3, '2026-09-22', 3, 'Kerja', 'Shift Malam Penjagaan', 0, 'admin_hrd', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(3, 5, '2026-09-22', 2, 'Kerja', 'Shift Reguler Sore', 0, 'admin_hrd', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(4, 1, '2026-09-22', 4, 'Kerja', 'Office Day', 0, 'admin_hrd', '2026-09-22 03:52:42', '2026-09-22 03:52:42'),
(5, 4, '2026-09-29', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(6, 4, '2026-09-30', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(7, 4, '2026-10-01', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(8, 4, '2026-10-02', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(9, 4, '2026-10-03', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(10, 4, '2026-10-04', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(11, 4, '2026-10-05', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(12, 4, '2026-10-06', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(13, 4, '2026-10-07', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(14, 4, '2026-10-08', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(15, 4, '2026-10-09', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(16, 4, '2026-10-10', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(17, 4, '2026-10-11', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(18, 4, '2026-10-12', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(19, 4, '2026-10-13', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33'),
(20, 4, '2026-10-14', 4, 'Kerja', 'Penugasan Kolektif', 1, 'admin_hrd', '2026-09-22 04:49:33', '2026-09-22 04:49:33');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `m_divisi`
--
ALTER TABLE `m_divisi`
  ADD PRIMARY KEY (`ID_DIVISI`),
  ADD UNIQUE KEY `UQ_DIVISI_NAMA` (`NAMA_DIVISI`);

--
-- Indeks untuk tabel `m_kelompok`
--
ALTER TABLE `m_kelompok`
  ADD PRIMARY KEY (`ID_KELOMPOK`);

--
-- Indeks untuk tabel `m_libur_nasional`
--
ALTER TABLE `m_libur_nasional`
  ADD PRIMARY KEY (`ID_LIBUR`),
  ADD UNIQUE KEY `UQ_LIBUR_TANGGAL` (`TANGGAL`),
  ADD KEY `IDX_LIBUR_PERIODE` (`TANGGAL`,`JENIS_LIBUR`);

--
-- Indeks untuk tabel `m_pegawai`
--
ALTER TABLE `m_pegawai`
  ADD PRIMARY KEY (`ID_PEGAWAI`),
  ADD KEY `IDX_PEGAWAI_MESIN` (`ID_PEGAWAI_MESIN`),
  ADD KEY `IDX_PEGAWAI_DIVISI` (`ID_DIVISI`),
  ADD KEY `IDX_PEGAWAI_KELOMPOK` (`ID_KELOMPOK`),
  ADD KEY `IDX_PEGAWAI_STATUS` (`IS_AKTIF`);

--
-- Indeks untuk tabel `m_shift`
--
ALTER TABLE `m_shift`
  ADD PRIMARY KEY (`ID_JADWAL`),
  ADD UNIQUE KEY `UQ_SHIFT_CODE` (`SHIFT_CODE`);

--
-- Indeks untuk tabel `t_jadwal_kerja`
--
ALTER TABLE `t_jadwal_kerja`
  ADD PRIMARY KEY (`ID_PENUGASAN`),
  ADD UNIQUE KEY `UQ_PEGAWAI_TANGGAL` (`ID_PEGAWAI`,`TANGGAL`),
  ADD KEY `IDX_JADWAL_TANGGAL` (`TANGGAL`),
  ADD KEY `IDX_JADWAL_SHIFT` (`ID_JADWAL`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `m_divisi`
--
ALTER TABLE `m_divisi`
  MODIFY `ID_DIVISI` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `m_kelompok`
--
ALTER TABLE `m_kelompok`
  MODIFY `ID_KELOMPOK` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `m_libur_nasional`
--
ALTER TABLE `m_libur_nasional`
  MODIFY `ID_LIBUR` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `m_pegawai`
--
ALTER TABLE `m_pegawai`
  MODIFY `ID_PEGAWAI` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `m_shift`
--
ALTER TABLE `m_shift`
  MODIFY `ID_JADWAL` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `t_jadwal_kerja`
--
ALTER TABLE `t_jadwal_kerja`
  MODIFY `ID_PENUGASAN` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `m_pegawai`
--
ALTER TABLE `m_pegawai`
  ADD CONSTRAINT `FK_PEGAWAI_DIVISI` FOREIGN KEY (`ID_DIVISI`) REFERENCES `m_divisi` (`ID_DIVISI`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_PEGAWAI_KELOMPOK` FOREIGN KEY (`ID_KELOMPOK`) REFERENCES `m_kelompok` (`ID_KELOMPOK`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `t_jadwal_kerja`
--
ALTER TABLE `t_jadwal_kerja`
  ADD CONSTRAINT `FK_JADWAL_PEGAWAI` FOREIGN KEY (`ID_PEGAWAI`) REFERENCES `m_pegawai` (`ID_PEGAWAI`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_JADWAL_SHIFT` FOREIGN KEY (`ID_JADWAL`) REFERENCES `m_shift` (`ID_JADWAL`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
