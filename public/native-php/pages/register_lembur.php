<?php
// pages/register_lembur.php - Modul Register Lembur, Perhitungan SPL & Uang Makan Terhubung Database MySQL
$currentPage = 'register_lembur';
require_once __DIR__ . '/../includes/db.php';

// Inisialisasi session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan tabel t_register_lembur tersedia
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `t_register_lembur` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `id_pegawai` INT UNSIGNED NULL,
        `nama_pegawai` VARCHAR(150) NOT NULL,
        `tanggal` DATE NOT NULL,
        `hari` VARCHAR(50) NOT NULL,
        `jam_mulai` TIME NOT NULL,
        `jam_selesai` TIME NOT NULL,
        `jenis_spl` VARCHAR(100) NOT NULL,
        `catatan` TEXT NULL,
        `durasi_lembur` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Durasi lembur selewat 17:00 (jam)',
        `uang_makan` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Uang makan jika selesai > 20:00',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY `idx_lembur_pegawai` (`id_pegawai`),
        KEY `idx_lembur_tanggal` (`tanggal`),
        CONSTRAINT `fk_lembur_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `m_pegawai` (`ID_PEGAWAI`) ON UPDATE CASCADE ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel Transaksi Register Lembur';");
} catch (PDOException $e) {
    // Tangani jika tabel sudah ada atau ada isu constraint
}

// ==========================================================
// FUNGSI BANTU FORMAT WAKTU & DURASI
// ==========================================================
function formatJamLembur($value)
{
    if ($value === null || $value === '' || (float)$value <= 0) {
        return '0 jam';
    }

    $totalMenit = (int)round((float)$value * 60);
    $jam = intdiv($totalMenit, 60);
    $menit = $totalMenit % 60;

    if ($jam > 0 && $menit > 0) {
        return "{$jam} jam {$menit} menit";
    } elseif ($jam > 0) {
        return "{$jam} jam";
    } else {
        return "{$menit} menit";
    }
}

// ==========================================================
// 1. PEMROSESAN FORM POST (SIMPAN & HAPUS DATA)
// ==========================================================
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    // Aksi: Hapus Data Lembur
    if ($action === 'delete') {
        $idDelete = (int)($_POST['id_delete'] ?? 0);
        if ($idDelete > 0) {
            try {
                $stmtDel = $pdo->prepare("DELETE FROM t_register_lembur WHERE id = ?");
                $stmtDel->execute([$idDelete]);
                $_SESSION['flash_success'] = "Data register lembur #{$idDelete} berhasil dihapus.";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
            }
        }
        header("Location: ?page=register_lembur");
        exit;
    }

    // Aksi: Simpan Data Lembur Baru
    if ($action === 'save') {
        $idPegawai   = !empty($_POST['id_pegawai']) ? (int)$_POST['id_pegawai'] : null;
        $namaPegawai = trim($_POST['nama_pegawai'] ?? '');
        $tanggal     = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $hari        = trim($_POST['hari'] ?? 'Hari Kerja');
        $jamMulai    = trim($_POST['jam_mulai'] ?? '17:00');
        $jamSelesai  = trim($_POST['jam_selesai'] ?? '20:00');
        $jenisSPL    = trim($_POST['jenis_spl'] ?? 'SPL Jam Lembur');
        $catatan     = trim($_POST['catatan'] ?? '');

        // Jika ID Pegawai dipilih, ambil nama pegawai dari database
        if ($idPegawai) {
            $stmtCekPeg = $pdo->prepare("SELECT NM_PEGAWAI FROM m_pegawai WHERE ID_PEGAWAI = ? LIMIT 1");
            $stmtCekPeg->execute([$idPegawai]);
            $dbNama = $stmtCekPeg->fetchColumn();
            if ($dbNama) {
                $namaPegawai = $dbNama;
            }
        }

        // Simpan input form ke session untuk repopulasi jika terjadi kesalahan validasi
        $_SESSION['old_input'] = [
            'id_pegawai'   => $idPegawai,
            'nama_pegawai' => $namaPegawai,
            'tanggal'      => $tanggal,
            'hari'         => $hari,
            'jam_mulai'    => $jamMulai,
            'jam_selesai'  => $jamSelesai,
            'jenis_spl'    => $jenisSPL,
            'catatan'      => $catatan,
        ];

        // --------------------------------------------------
        // ATURAN & VALIDASI LOGIKA BISNIS:
        // --------------------------------------------------
        // Validasi: Wajib memilih / mengisi nama pegawai
        if (empty($namaPegawai)) {
            $_SESSION['flash_error'] = "Silakan pilih pegawai terlebih dahulu!";
            header("Location: ?page=register_lembur");
            exit;
        }

        // Validasi format waktu
        $mulaiSec   = strtotime($tanggal . ' ' . $jamMulai);
        $selesaiSec = strtotime($tanggal . ' ' . $jamSelesai);

        if (!$mulaiSec || !$selesaiSec) {
            $_SESSION['flash_error'] = "Format jam mulai atau jam selesai tidak valid!";
            header("Location: ?page=register_lembur");
            exit;
        }

        // Aturan 1: Validasi jam_selesai tidak boleh <= jam_mulai
        if ($selesaiSec <= $mulaiSec) {
            $_SESSION['flash_error'] = "Jam selesai (" . htmlspecialchars($jamSelesai) . ") tidak boleh lebih kecil atau sama dengan jam mulai (" . htmlspecialchars($jamMulai) . ")!";
            header("Location: ?page=register_lembur");
            exit;
        }

        // Aturan 2: Jam Kerja Normal berakhir pukul 17.00.
        // Durasi lembur hanya dihitung dari waktu selewat pukul 17.00.
        // Jika jam mulai < 17.00, maka jam mulai lembur dihitung mulai 17.00.
        $jam17Sec = strtotime($tanggal . ' 17:00:00');
        $jam20Sec = strtotime($tanggal . ' 20:00:00');

        $jamMulaiLemburSec = max($mulaiSec, $jam17Sec);

        if ($selesaiSec > $jamMulaiLemburSec) {
            $durasiDetik  = $selesaiSec - $jamMulaiLemburSec;
            $durasiLembur = round($durasiDetik / 3600, 2); // Konversi ke jam desimal
        } else {
            $durasiLembur = 0.00;
        }

        // Aturan 3: Jika jam_selesai melewati pukul 20.00, sistem secara otomatis
        // menetapkan uang_makan sebesar Rp 15.000, selain itu bernilai Rp 0.
        $uangMakan = ($selesaiSec > $jam20Sec) ? 15000.00 : 0.00;

        // --------------------------------------------------
        // SIMPAN KE DATABASE DENGAN PREPARED STATEMENT PDO
        // --------------------------------------------------
        try {
            $stmtInsert = $pdo->prepare("
                INSERT INTO t_register_lembur 
                (id_pegawai, nama_pegawai, tanggal, hari, jam_mulai, jam_selesai, jenis_spl, catatan, durasi_lembur, uang_makan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtInsert->execute([
                $idPegawai,
                $namaPegawai,
                $tanggal,
                $hari,
                $jamMulai,
                $jamSelesai,
                $jenisSPL,
                $catatan,
                $durasiLembur,
                $uangMakan
            ]);

            $insertedId = (int)$pdo->lastInsertId();
            unset($_SESSION['old_input']);

            $_SESSION['flash_success'] = "Data register lembur untuk <strong>" . htmlspecialchars($namaPegawai) . "</strong> berhasil disimpan ke database (Durasi: " . formatJamLembur($durasiLembur) . ", Uang Makan: Rp " . number_format($uangMakan, 0, ',', '.') . ")!";
            header("Location: ?page=register_lembur&last_id=" . $insertedId);
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Terjadi kesalahan saat menyimpan data: " . $e->getMessage();
            header("Location: ?page=register_lembur");
            exit;
        }
    }
}

// ==========================================================
// 2. AMBIL DATA UNTUK TAMPILAN GET
// ==========================================================
// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
$oldInput     = $_SESSION['old_input'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['old_input']);

// Ambil daftar pegawai aktif dari database MySQL (m_pegawai)
try {
    $stmtPeg = $pdo->query("SELECT ID_PEGAWAI, NM_PEGAWAI FROM m_pegawai WHERE IS_AKTIF = 1 ORDER BY NM_PEGAWAI ASC");
    $pegawaiList = $stmtPeg->fetchAll();
} catch (Exception $e) {
    $pegawaiList = [];
}

// Ambil seluruh data register lembur dari database MySQL
try {
    $stmtLembur = $pdo->query("
        SELECT r.*, p.NM_PEGAWAI as pegawai_master 
        FROM t_register_lembur r
        LEFT JOIN m_pegawai p ON r.id_pegawai = p.ID_PEGAWAI
        ORDER BY r.tanggal DESC, r.id DESC
    ");
    $daftarLembur = $stmtLembur->fetchAll();
} catch (Exception $e) {
    $daftarLembur = [];
}

// Ambil record yang akan ditampilkan pada "Ringkasan Perhitungan"
// Prioritas: 
// 1. Berdasarkan parameter ?last_id=...
// 2. Berdasarkan data lembur paling baru di database
// 3. Fallback nilai default jika database masih kosong
$displaySummary = null;
$targetId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : null;

if ($targetId > 0) {
    foreach ($daftarLembur as $item) {
        if ((int)$item['id'] === $targetId) {
            $displaySummary = $item;
            break;
        }
    }
}

if (!$displaySummary && !empty($daftarLembur)) {
    $displaySummary = $daftarLembur[0];
}

$summaryData = [
    'pegawai'       => $displaySummary ? ($displaySummary['nama_pegawai'] ?: ($displaySummary['pegawai_master'] ?? '—')) : '—',
    'tanggal'       => $displaySummary ? $displaySummary['tanggal'] : date('Y-m-d'),
    'jam_mulai'     => $displaySummary ? substr($displaySummary['jam_mulai'], 0, 5) : '—',
    'jam_selesai'   => $displaySummary ? substr($displaySummary['jam_selesai'], 0, 5) : '—',
    'hari'          => $displaySummary ? $displaySummary['hari'] : 'Hari Kerja',
    'jenis_spl'     => $displaySummary ? $displaySummary['jenis_spl'] : 'SPL Jam Lembur',
    'catatan'       => $displaySummary ? ($displaySummary['catatan'] ?? '') : '',
    'durasi_lembur' => $displaySummary ? (float)$displaySummary['durasi_lembur'] : 0.00,
    'uang_makan'    => $displaySummary ? (float)$displaySummary['uang_makan'] : 0.00,
];

// Nilai form default (menggunakan oldInput jika gagal validasi, atau default baru)
$formPegawai    = $oldInput['id_pegawai'] ?? '';
$formTanggal    = $oldInput['tanggal'] ?? date('Y-m-d');
$formHari       = $oldInput['hari'] ?? 'Hari Kerja';
$formJamMulai   = $oldInput['jam_mulai'] ?? '17:00';
$formJamSelesai = $oldInput['jam_selesai'] ?? '20:00';
$formJenisSPL   = $oldInput['jenis_spl'] ?? 'SPL Jam Lembur';
$formCatatan    = $oldInput['catatan'] ?? '';
?>

<style>
.lembur-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    overflow: hidden;
    border: 1px solid #e8edf2;
}
.lembur-card .card-header {
    padding: .9rem 1.5rem !important;
    border-bottom: 1px solid #e8edf2 !important;
}
.lembur-card .card-body { padding: 1.5rem !important; }
.lembur-form .form-label {
    color: #475569;
    font-size: .75rem;
    margin-bottom: .35rem;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.lembur-form .form-control,
.lembur-form .form-select {
    min-height: 40px;
    border-color: #d8e1ec;
    border-radius: 8px;
    color: #1e293b;
    font-size: .85rem;
}
.lembur-form textarea.form-control { min-height: 76px; }
.lembur-form .form-control:focus,
.lembur-form .form-select:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, .15);
}
.lembur-rule {
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: 8px;
    color: #92400e;
    font-size: .72rem;
    line-height: 1.5;
    padding: .65rem .85rem;
}
.lembur-summary .card-body { padding: 1.25rem !important; }
.lembur-summary-list { margin: 0; }
.lembur-summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: .5rem 0;
    border-bottom: 1px solid #eef2f7;
    font-size: .8rem;
}
.lembur-summary-item:last-child { border-bottom: 0; }
.lembur-summary-item dt { color: #64748b; font-weight: 500; }
.lembur-summary-item dd { color: #0f172a; font-weight: 600; margin: 0; text-align: right; }
.lembur-metric { border-radius: 10px; min-height: 58px; }
.lembur-metric .card-body { padding: .75rem .9rem !important; }
.lembur-metric .metric-icon {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin-right: .6rem;
    font-size: 1.1rem;
}
.lembur-metric .metric-label { color: #64748b; font-size: .68rem; font-weight: 500; }
.lembur-metric .metric-value { color: #0f172a; font-size: .9rem; font-weight: 700; }
.lembur-policy { font-size: .75rem; line-height: 1.5; border-radius: 8px; }

.lembur-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: .75rem 1rem;
    border-bottom: 2px solid #e2e8f0;
}
.lembur-table tbody td {
    padding: .75rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: .84rem;
    color: #334155;
}
.lembur-table tbody tr:hover { background: #f8fafc; }

@media (max-width: 767.98px) {
    .lembur-card .card-header { padding: .85rem 1rem !important; }
    .lembur-card .card-body { padding: 1rem !important; }
}
</style>

<!-- Header & Breadcrumbs -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="h3 text-gray-800 m-0 fw-bold" style="font-size:1.35rem;">Register Lembur</h2>
        <small class="text-muted" style="font-size:.78rem;">Sistem validasi jam kerja normal, perhitungan durasi lembur, SPL, dan hak uang makan</small>
    </div>
    <span class="badge bg-white text-secondary border shadow-sm p-2"><i class="bi bi-calendar-event text-warning me-1"></i> <?php echo date('d F Y'); ?></span>
</div>

<!-- Notifikasi Alert -->
<?php if ($flashSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
        <div><?= $flashSuccess ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
        <div><?= $flashError ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-3">
    <!-- KOLOM KIRI: FORM REGISTER LEMBUR -->
    <div class="col-xl-7 col-lg-7">
        <div class="card lembur-card h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Form Register Lembur</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1"><i class="bi bi-shield-check"></i> PDO Prepared</span>
            </div>
            <div class="card-body p-4 lembur-form">
                <form method="POST" action="?page=register_lembur" id="formLembur">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="nama_pegawai" id="inputNamaPegawai" value="">

                    <!-- Pilih Pegawai -->
                    <div class="mb-3">
                        <label for="pegawai" class="form-label fw-semibold">Nama Pegawai <span class="text-danger">*</span></label>
                        <select class="form-select" id="pegawai" name="id_pegawai" required>
                            <option value="">-- Pilih Pegawai Aktif --</option>
                            <?php foreach ($pegawaiList as $peg): ?>
                                <option value="<?php echo $peg['ID_PEGAWAI']; ?>" 
                                        data-nama="<?php echo htmlspecialchars($peg['NM_PEGAWAI']); ?>"
                                        <?php echo ((string)$formPegawai === (string)$peg['ID_PEGAWAI']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($peg['NM_PEGAWAI']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tanggal & Hari -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tanggal" class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($formTanggal); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="hari" class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                            <select class="form-select" id="hari" name="hari" required>
                                <option value="Hari Kerja" <?php echo ($formHari === 'Hari Kerja') ? 'selected' : ''; ?>>Hari Kerja</option>
                                <option value="Sabtu" <?php echo ($formHari === 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                                <option value="Minggu" <?php echo ($formHari === 'Minggu') ? 'selected' : ''; ?>>Minggu</option>
                                <option value="Hari Libur Nasional" <?php echo ($formHari === 'Hari Libur Nasional') ? 'selected' : ''; ?>>Hari Libur Nasional</option>
                            </select>
                        </div>
                    </div>

                    <!-- Jam Mulai & Jam Selesai -->
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="jam_mulai" class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" value="<?php echo htmlspecialchars($formJamMulai); ?>" required>
                            <small class="text-muted" style="font-size:.7rem;">*Jika < 17:00, perhitungan lembur dimulai pukul 17:00</small>
                        </div>
                        <div class="col-md-6">
                            <label for="jam_selesai" class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" value="<?php echo htmlspecialchars($formJamSelesai); ?>" required>
                            <small class="text-muted" style="font-size:.7rem;">*Wajib lebih besar dari jam mulai</small>
                        </div>
                    </div>

                    <!-- Jenis SPL -->
                    <div class="mt-3">
                        <label for="jenis_spl" class="form-label fw-semibold">Jenis SPL <span class="text-danger">*</span></label>
                        <select class="form-select" id="jenis_spl" name="jenis_spl" required>
                            <option value="SPL Jam Lembur" <?php echo ($formJenisSPL === 'SPL Jam Lembur') ? 'selected' : ''; ?>>SPL Jam Lembur</option>
                            <option value="SPL Hari Libur" <?php echo ($formJenisSPL === 'SPL Hari Libur') ? 'selected' : ''; ?>>SPL Hari Libur</option>
                            <option value="SPL Hari Libur + Jam Lembur" <?php echo ($formJenisSPL === 'SPL Hari Libur + Jam Lembur') ? 'selected' : ''; ?>>SPL Hari Libur + Jam Lembur</option>
                        </select>
                    </div>

                    <!-- Catatan -->
                    <div class="mt-3">
                        <label for="catatan" class="form-label fw-semibold">Catatan / Keterangan</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Contoh: Pengecekan genset pabrik, closing rekap bulanan, support maintenance..."><?php echo htmlspecialchars($formCatatan); ?></textarea>
                    </div>

                    <!-- Banner Aturan Bisnis & Tombol Submit -->
                    <div class="d-flex justify-content-between align-items-center mt-4 gap-3 flex-wrap">
                        <div class="lembur-rule flex-grow-1">
                            <i class="bi bi-info-circle-fill me-1 text-warning"></i>
                            <strong>Aturan Bisnis:</strong> Jam normal berakhir <strong>17.00</strong>. Lembur dihitung hanya setelah 17.00. Melewati <strong>20.00</strong> otomatis dapat uang makan <strong>Rp 15.000</strong>.
                        </div>
                        <button type="submit" class="btn btn-warning text-white fw-bold px-4 py-2 shadow-sm">
                            <i class="bi bi-save me-2"></i> Simpan Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: RINGKASAN PERHITUNGAN -->
    <div class="col-xl-5 col-lg-5">
        <div class="card lembur-card lembur-summary mb-3">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-warning"><i class="bi bi-calculator me-2"></i>Ringkasan Perhitungan</h5>
                <span class="badge bg-warning bg-opacity-25 text-dark font-monospace" style="font-size:.72rem;">Live Summary</span>
            </div>
            <div class="card-body p-4">
                <dl class="lembur-summary-list">
                    <div class="lembur-summary-item">
                        <dt><i class="bi bi-person me-1"></i> Pegawai</dt>
                        <dd id="summaryPegawai" class="text-primary"><?php echo htmlspecialchars($summaryData['pegawai']); ?></dd>
                    </div>
                    <div class="lembur-summary-item">
                        <dt><i class="bi bi-calendar2-check me-1"></i> Tanggal</dt>
                        <dd id="summaryTanggal"><?php echo date('d F Y', strtotime($summaryData['tanggal'])); ?></dd>
                    </div>
                    <div class="lembur-summary-item">
                        <dt><i class="bi bi-clock-history me-1"></i> Jam Lembur</dt>
                        <dd id="summaryJam"><?php echo htmlspecialchars($summaryData['jam_mulai']); ?><?php echo $summaryData['jam_selesai'] !== '—' ? ' - ' . htmlspecialchars($summaryData['jam_selesai']) : ''; ?></dd>
                    </div>
                    <div class="lembur-summary-item">
                        <dt><i class="bi bi-file-earmark-ruled me-1"></i> Jenis SPL</dt>
                        <dd id="summarySPL"><?php echo htmlspecialchars($summaryData['jenis_spl']); ?></dd>
                    </div>
                </dl>

                <!-- Metric Cards: Durasi, Hari, Uang Makan -->
                <div class="mt-3 row g-2">
                    <div class="col-4">
                        <div class="card lembur-metric border-0 bg-warning bg-opacity-10 h-100 shadow-none">
                            <div class="card-body d-flex align-items-center">
                                <span class="metric-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-stopwatch"></i></span>
                                <div>
                                    <div class="metric-label">Durasi Lembur</div>
                                    <div class="metric-value text-warning" id="summaryDurasi"><?php echo formatJamLembur($summaryData['durasi_lembur']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card lembur-metric border-0 bg-primary bg-opacity-10 h-100 shadow-none">
                            <div class="card-body d-flex align-items-center">
                                <span class="metric-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-calendar3"></i></span>
                                <div>
                                    <div class="metric-label">Hari</div>
                                    <div class="metric-value text-primary" id="summaryHari"><?php echo htmlspecialchars($summaryData['hari']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card lembur-metric border-0 bg-success bg-opacity-10 h-100 shadow-none">
                            <div class="card-body d-flex align-items-center">
                                <span class="metric-icon bg-success bg-opacity-25 text-success"><i class="bi bi-cash-stack"></i></span>
                                <div>
                                    <div class="metric-label">Uang Makan</div>
                                    <div class="metric-value text-success" id="summaryUangMakan">
                                        <?php echo $summaryData['uang_makan'] > 0 ? 'Rp ' . number_format($summaryData['uang_makan'], 0, ',', '.') : 'Rp 0'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kebijakan Uang Makan Box -->
                <div class="alert alert-info lembur-policy mt-3 mb-0 border-0 d-flex align-items-start" id="policyBox">
                    <i class="bi bi-info-circle-fill me-2 fs-6 text-info mt-1"></i>
                    <div>
                        <strong>Kebijakan Uang Makan Lembur:</strong><br>
                        <span id="policyText">
                            <?php if ($summaryData['uang_makan'] > 0): ?>
                                Jam selesai melewati pukul <strong>20.00</strong>, sehingga pegawai berhak mendapatkan uang makan sebesar <strong>Rp 15.000</strong>.
                            <?php else: ?>
                                Uang makan sebesar <strong>Rp 15.000</strong> diberikan apabila jam selesai dinas lembur melewati pukul <strong>20.00</strong>.
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL DAFTAR REGISTER LEMBUR (REAL-TIME DARI MYSQL) -->
<div class="card lembur-card mt-4">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="m-0 fw-bold text-dark"><i class="bi bi-table me-2 text-primary"></i>Daftar Register Lembur</h5>
            <small class="text-muted">Data transaksi tersimpan pada tabel MySQL <code>t_register_lembur</code></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2"><i class="bi bi-database me-1"></i> Total <?php echo count($daftarLembur); ?> Data</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle lembur-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Pegawai</th>
                        <th>Tanggal & Hari</th>
                        <th>Jam Kerja</th>
                        <th>Durasi Lembur</th>
                        <th>Jenis SPL</th>
                        <th>Uang Makan</th>
                        <th>Catatan</th>
                        <th class="text-center" style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarLembur)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-1 opacity-50"></i>
                                Belum ada data transaksi register lembur. Silakan isi form di atas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarLembur as $idx => $row): ?>
                            <tr class="<?php echo ($targetId === (int)$row['id']) ? 'table-warning' : ''; ?>">
                                <td class="text-muted fw-bold"><?php echo $idx + 1; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['nama_pegawai'] ?: ($row['pegawai_master'] ?? '—')); ?></div>
                                    <small class="text-muted">ID: #<?php echo $row['id']; ?></small>
                                </td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></div>
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['hari']); ?></span>
                                </td>
                                <td>
                                    <span class="font-monospace text-primary fw-semibold">
                                        <?php echo substr($row['jam_mulai'], 0, 5); ?> - <?php echo substr($row['jam_selesai'], 0, 5); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-25 text-dark fw-bold">
                                        <i class="bi bi-clock me-1"></i><?php echo formatJamLembur($row['durasi_lembur']); ?>
                                    </span>
                                    <small class="d-block text-muted" style="font-size: .7rem;"><?php echo number_format($row['durasi_lembur'], 2); ?> jam</small>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                        <?php echo htmlspecialchars($row['jenis_spl']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ((float)$row['uang_makan'] > 0): ?>
                                        <span class="badge bg-success">Rp <?php echo number_format($row['uang_makan'], 0, ',', '.'); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-50">Rp 0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['catatan'] ?: '—'); ?></small>
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="?page=register_lembur" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data lembur ini?');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_delete" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus Data">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================================
     CLIENT-SIDE JAVASCRIPT: REAL-TIME PREVIEW & VALIDATION
========================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pegawaiSelect = document.getElementById('pegawai');
    const inputNamaPegawai = document.getElementById('inputNamaPegawai');
    const tanggalInput = document.getElementById('tanggal');
    const hariSelect = document.getElementById('hari');
    const jamMulaiInput = document.getElementById('jam_mulai');
    const jamSelesaiInput = document.getElementById('jam_selesai');
    const jenisSplSelect = document.getElementById('jenis_spl');
    const catatanInput = document.getElementById('catatan');

    // Element display summary
    const summaryPegawai = document.getElementById('summaryPegawai');
    const summaryTanggal = document.getElementById('summaryTanggal');
    const summaryJam = document.getElementById('summaryJam');
    const summarySPL = document.getElementById('summarySPL');
    const summaryDurasi = document.getElementById('summaryDurasi');
    const summaryHari = document.getElementById('summaryHari');
    const summaryUangMakan = document.getElementById('summaryUangMakan');
    const policyText = document.getElementById('policyText');

    function updatePreview() {
        const selectedOption = pegawaiSelect.options[pegawaiSelect.selectedIndex];
        const nama = selectedOption ? selectedOption.getAttribute('data-nama') || selectedOption.text : '—';
        if (selectedOption && selectedOption.value) {
            inputNamaPegawai.value = nama;
            summaryPegawai.textContent = nama;
        }

        const tglVal = tanggalInput.value;
        if (tglVal) {
            const dateObj = new Date(tglVal + 'T00:00:00');
            if (!isNaN(dateObj)) {
                const options = { day: '2-digit', month: 'long', year: 'numeric' };
                summaryTanggal.textContent = dateObj.toLocaleDateString('id-ID', options);
            }
        }

        const hariVal = hariSelect.value;
        summaryHari.textContent = hariVal;

        const splVal = jenisSplSelect.value;
        summarySPL.textContent = splVal;

        const jamMulai = jamMulaiInput.value || '17:00';
        const jamSelesai = jamSelesaiInput.value || '20:00';
        summaryJam.textContent = jamMulai + ' - ' + jamSelesai;

        // Hitung durasi lembur & uang makan di sisi klien (Live Feedback)
        const parseTime = (str) => {
            const parts = str.split(':');
            return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
        };

        const mulaiMins = parseTime(jamMulai);
        const selesaiMins = parseTime(jamSelesai);
        const batasNormalMins = 17 * 60; // 17:00
        const batasUangMakanMins = 20 * 60; // 20:00

        if (selesaiMins > mulaiMins) {
            const jamMulaiLembur = Math.max(mulaiMins, batasNormalMins);
            let durasiMins = 0;
            if (selesaiMins > jamMulaiLembur) {
                durasiMins = selesaiMins - jamMulaiLembur;
            }

            const jam = Math.floor(durasiMins / 60);
            const menit = durasiMins % 60;
            let formattedDurasi = '0 jam';
            if (jam > 0 && menit > 0) {
                formattedDurasi = jam + ' jam ' + menit + ' menit';
            } else if (jam > 0) {
                formattedDurasi = jam + ' jam';
            } else if (menit > 0) {
                formattedDurasi = menit + ' menit';
            }
            summaryDurasi.textContent = formattedDurasi;

            if (selesaiMins > batasUangMakanMins) {
                summaryUangMakan.textContent = 'Rp 15.000';
                policyText.innerHTML = 'Jam selesai melewati pukul <strong>20.00</strong>, sehingga pegawai berhak mendapatkan uang makan sebesar <strong>Rp 15.000</strong>.';
            } else {
                summaryUangMakan.textContent = 'Rp 0';
                policyText.innerHTML = 'Uang makan sebesar <strong>Rp 15.000</strong> diberikan apabila jam selesai dinas lembur melewati pukul <strong>20.00</strong>.';
            }
        } else {
            summaryDurasi.textContent = '0 jam';
            summaryUangMakan.textContent = 'Rp 0';
        }
    }

    // Auto-update hari berdasarkan tanggal yang dipilih
    tanggalInput.addEventListener('change', function () {
        const val = this.value;
        if (!val) return;
        const dateObj = new Date(val + 'T00:00:00');
        if (isNaN(dateObj)) return;
        const dayOfWeek = dateObj.getDay(); // 0: Minggu, 6: Sabtu
        if (dayOfWeek === 0) {
            hariSelect.value = 'Minggu';
        } else if (dayOfWeek === 6) {
            hariSelect.value = 'Sabtu';
        } else {
            if (hariSelect.value !== 'Hari Libur Nasional') {
                hariSelect.value = 'Hari Kerja';
            }
        }
        updatePreview();
    });

    // Validasi form sebelum submit
    document.getElementById('formLembur').addEventListener('submit', function (e) {
        const mulai = jamMulaiInput.value;
        const selesai = jamSelesaiInput.value;

        if (selesai <= mulai) {
            e.preventDefault();
            alert('Validasi Gagal: Jam selesai (' + selesai + ') tidak boleh lebih awal atau sama dengan jam mulai (' + mulai + ')!');
            jamSelesaiInput.focus();
            return false;
        }

        const selectedOption = pegawaiSelect.options[pegawaiSelect.selectedIndex];
        if (selectedOption) {
            inputNamaPegawai.value = selectedOption.getAttribute('data-nama') || selectedOption.text;
        }
    });

    // Listener interaksi input
    pegawaiSelect.addEventListener('change', updatePreview);
    hariSelect.addEventListener('change', updatePreview);
    jamMulaiInput.addEventListener('input', updatePreview);
    jamSelesaiInput.addEventListener('input', updatePreview);
    jenisSplSelect.addEventListener('change', updatePreview);

    // Initial sync
    if (pegawaiSelect.value) {
        const opt = pegawaiSelect.options[pegawaiSelect.selectedIndex];
        if (opt) inputNamaPegawai.value = opt.getAttribute('data-nama') || opt.text;
    }
});
</script>
