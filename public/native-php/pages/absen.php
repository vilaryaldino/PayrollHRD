<?php
// pages/absen.php - Modul Data Absensi (Import Fingerspot & Tambah Manual)
$currentPage = 'absen';
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ============================================================
// BUAT TABEL t_data_absensi JIKA BELUM ADA
// ============================================================
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `t_data_absensi` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `id_mesin` VARCHAR(50) NOT NULL COMMENT 'ID Mesin Absensi dari Fingerspot',
        `id_pegawai` INT UNSIGNED NULL,
        `nama_pegawai` VARCHAR(150) NOT NULL,
        `tanggal` DATE NOT NULL,
        `jam_kehadiran` TIME NULL,
        `jam_kepulangan` TIME NULL,
        `lokasi_absen` VARCHAR(100) NULL DEFAULT 'Kantor Pusat',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY `idx_absen_tanggal` (`tanggal`),
        KEY `idx_absen_mesin` (`id_mesin`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
} catch (PDOException $e) { /* tabel sudah ada */ }

// ============================================================
// FUNGSI BANTU
// ============================================================
function excelDateToPhp($excelDate) {
    if (is_numeric($excelDate)) {
        return date('Y-m-d', ($excelDate - 25569) * 86400);
    }
    $ts = strtotime($excelDate);
    return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
}

function excelTimeToPhp($val) {
    if ($val === null || $val === '') return null;
    if (is_numeric($val) && $val < 1) {
        $totalSec = round((float)$val * 86400);
        $h = intdiv($totalSec, 3600);
        $m = intdiv($totalSec % 3600, 60);
        $s = $totalSec % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
    $ts = strtotime($val);
    return $ts ? date('H:i:s', $ts) : null;
}

// ============================================================
// PEMROSESAN POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'manual';

    // HAPUS DATA
    if ($action === 'delete') {
        $idDel = (int)($_POST['id_delete'] ?? 0);
        if ($idDel > 0) {
            try {
                $pdo->prepare("DELETE FROM t_data_absensi WHERE id = ?")->execute([$idDel]);
                $_SESSION['flash_success'] = "Data absensi berhasil dihapus.";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Gagal menghapus: " . $e->getMessage();
            }
        }
        header("Location: ?page=absen"); exit;
    }

    // TAMBAH MANUAL
    if ($action === 'manual') {
        $idMesin  = trim($_POST['id_mesin'] ?? '');
        $tanggal  = trim($_POST['tanggal'] ?? '');
        $jamIn    = trim($_POST['jam_kehadiran'] ?? '');
        $jamOut   = trim($_POST['jam_kepulangan'] ?? '');
        $lokasi   = trim($_POST['lokasi_absen'] ?? 'Kantor Pusat');

        if (empty($idMesin) || empty($tanggal)) {
            $_SESSION['flash_error'] = "ID Mesin dan Tanggal wajib diisi!";
            header("Location: ?page=absen"); exit;
        }

        // Mapping ID Mesin ke data pegawai
        $stmtMap = $pdo->prepare("SELECT ID_PEGAWAI, NM_PEGAWAI FROM m_pegawai WHERE ID_PEGAWAI_MESIN = ? LIMIT 1");
        $stmtMap->execute([$idMesin]);
        $pegawai = $stmtMap->fetch();

        $idPegawai   = $pegawai ? (int)$pegawai['ID_PEGAWAI'] : null;
        $namaPegawai = $pegawai ? $pegawai['NM_PEGAWAI'] : 'Pegawai Tidak Dikenal / Belum Terdaftar';

        try {
            $stmt = $pdo->prepare("
                INSERT INTO t_data_absensi (id_mesin, id_pegawai, nama_pegawai, tanggal, jam_kehadiran, jam_kepulangan, lokasi_absen)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    id_pegawai = VALUES(id_pegawai),
                    nama_pegawai = VALUES(nama_pegawai),
                    jam_kehadiran = VALUES(jam_kehadiran),
                    jam_kepulangan = VALUES(jam_kepulangan),
                    lokasi_absen = VALUES(lokasi_absen)
            ");
            $stmt->execute([$idMesin, $idPegawai, $namaPegawai, $tanggal, $jamIn ?: null, $jamOut ?: null, $lokasi]);
            $_SESSION['flash_success'] = "Data absensi manual untuk <strong>" . htmlspecialchars($namaPegawai) . "</strong> berhasil disimpan.";
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Gagal menyimpan: " . $e->getMessage();
        }
        header("Location: ?page=absen"); exit;
    }

    // IMPORT EXCEL
    if ($action === 'import') {
        $vendorAutoload = __DIR__ . '/../../../../vendor/autoload.php';
        if (!file_exists($vendorAutoload)) {
            $_SESSION['flash_error'] = "Library PhpSpreadsheet tidak ditemukan. Jalankan: <code>composer require phpoffice/phpspreadsheet</code>";
            header("Location: ?page=absen"); exit;
        }
        require_once $vendorAutoload;

        if (empty($_FILES['file_excel']['tmp_name'])) {
            $_SESSION['flash_error'] = "File Excel tidak ditemukan / gagal diupload.";
            header("Location: ?page=absen"); exit;
        }

        $ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xls', 'xlsx'])) {
            $_SESSION['flash_error'] = "Format file tidak valid. Hanya .xls dan .xlsx yang diizinkan.";
            header("Location: ?page=absen"); exit;
        }

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($_FILES['file_excel']['tmp_name']);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($_FILES['file_excel']['tmp_name']);
            $sheet  = $spreadsheet->getActiveSheet();
            $rows   = $sheet->toArray(null, true, true, false);

            // Preload semua data master ke memori (hindari N+1 Query)
            $stmtMaster  = $pdo->query("SELECT ID_PEGAWAI_MESIN, ID_PEGAWAI, NM_PEGAWAI FROM m_pegawai WHERE IS_AKTIF = 1");
            $masterPegawai = [];
            foreach ($stmtMaster->fetchAll() as $m) {
                $masterPegawai[strtoupper(trim($m['ID_PEGAWAI_MESIN']))] = $m;
            }

            $sukses = 0; $gagal = 0; $skip = 0;

            // Baris 0 = header → dilewati
            // Kolom: [0]=ID Mesin, [1]=Tanggal, [2]=Jam Masuk, [3]=Jam Pulang, [4]=Lokasi
            foreach ($rows as $i => $row) {
                if ($i === 0) continue;
                if (empty($row[0])) { $skip++; continue; }

                $idMesin = strtoupper(trim((string)$row[0]));
                $tanggal = excelDateToPhp($row[1] ?? '');
                $jamIn   = excelTimeToPhp($row[2] ?? null);
                $jamOut  = excelTimeToPhp($row[3] ?? null);
                $lokasi  = trim((string)($row[4] ?? 'Kantor Pusat')) ?: 'Kantor Pusat';

                if (empty($idMesin) || empty($tanggal)) { $skip++; continue; }

                $pegawaiData = $masterPegawai[$idMesin] ?? null;
                $idPeg = $pegawaiData ? (int)$pegawaiData['ID_PEGAWAI'] : null;
                $nmPeg = $pegawaiData ? $pegawaiData['NM_PEGAWAI']      : 'Pegawai Tidak Dikenal / Belum Terdaftar';

                try {
                    $stmtIns = $pdo->prepare("
                        INSERT INTO t_data_absensi (id_mesin, id_pegawai, nama_pegawai, tanggal, jam_kehadiran, jam_kepulangan, lokasi_absen)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            id_pegawai     = VALUES(id_pegawai),
                            nama_pegawai   = VALUES(nama_pegawai),
                            jam_kehadiran  = COALESCE(jam_kehadiran, VALUES(jam_kehadiran)),
                            jam_kepulangan = VALUES(jam_kepulangan),
                            lokasi_absen   = VALUES(lokasi_absen)
                    ");
                    $stmtIns->execute([$idMesin, $idPeg, $nmPeg, $tanggal, $jamIn, $jamOut, $lokasi]);
                    $sukses++;
                } catch (PDOException $e) { $gagal++; }
            }

            $_SESSION['flash_success'] = "Import selesai! <strong>{$sukses}</strong> baris berhasil, <strong>{$gagal}</strong> gagal, <strong>{$skip}</strong> dilewati.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Gagal membaca file Excel: " . $e->getMessage();
        }
        header("Location: ?page=absen"); exit;
    }
}

// ============================================================
// AMBIL DATA UNTUK TAMPILAN
// ============================================================
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$search    = trim($_GET['search'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate   = trim($_GET['end_date'] ?? '');
$perPage   = 15;
$halaman   = max(1, (int)($_GET['p'] ?? 1));
$offset    = ($halaman - 1) * $perPage;

$where  = "WHERE 1=1";
$params = [];
if ($search) {
    $where   .= " AND (nama_pegawai LIKE ? OR id_mesin LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($startDate && $endDate) {
    $where   .= " AND tanggal BETWEEN ? AND ?";
    $params[] = $startDate; $params[] = $endDate;
} elseif ($startDate) {
    $where .= " AND tanggal >= ?"; $params[] = $startDate;
} elseif ($endDate) {
    $where .= " AND tanggal <= ?"; $params[] = $endDate;
}

try {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM t_data_absensi $where");
    $stmtCount->execute($params);
    $totalData = (int)$stmtCount->fetchColumn();
    $totalPage = max(1, ceil($totalData / $perPage));

    $stmtData  = $pdo->prepare("SELECT * FROM t_data_absensi $where ORDER BY tanggal DESC, jam_kehadiran ASC LIMIT $perPage OFFSET $offset");
    $stmtData->execute($params);
    $dataAbsen = $stmtData->fetchAll();
} catch (PDOException $e) {
    $dataAbsen = []; $totalData = 0; $totalPage = 1;
}

try {
    $pegawaiList = $pdo->query("SELECT ID_PEGAWAI_MESIN, NM_PEGAWAI FROM m_pegawai WHERE IS_AKTIF = 1 ORDER BY NM_PEGAWAI ASC")->fetchAll();
} catch (Exception $e) { $pegawaiList = []; }

$hariIndo = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
?>

<!-- ======================================================
     PAGE HEADER
     ====================================================== -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold text-primary"><i class="bi bi-person-badge-fill me-2"></i>Data Absensi Pegawai</h4>
        <small class="text-muted">Manajemen data kehadiran pegawai dari mesin Fingerspot</small>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalManual">
            <i class="bi bi-plus-circle me-1"></i> Tambah Manual
        </button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
            <i class="bi bi-file-earmark-excel me-1"></i> Import Fingerspot
        </button>
    </div>
</div>

<!-- FLASH MESSAGE -->
<?php if ($flashSuccess): ?>
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i><?= $flashSuccess ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flashError): ?>
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $flashError ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- FILTER -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="absen">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-calendar3 me-1"></i>Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-calendar3-range me-1"></i>Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-search me-1"></i>Cari Nama / ID Mesin</label>
                <input type="text" name="search" class="form-control" placeholder="Nama pegawai atau ID mesin absensi..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Cari</button>
                <a href="?page=absen" class="btn btn-light border" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- TABEL DATA -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
        <span class="text-muted small">
            Menampilkan <strong class="text-dark"><?= number_format(count($dataAbsen)) ?></strong>
            dari total <strong class="text-dark"><?= number_format($totalData) ?></strong> record
        </span>
        <span class="badge bg-primary rounded-pill px-3 py-2"><?= number_format($totalData) ?> Total Data</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:45px">No</th>
                        <th>Tanggal</th>
                        <th>ID Mesin</th>
                        <th>Nama Pegawai</th>
                        <th class="text-center">Jam Masuk</th>
                        <th class="text-center">Jam Pulang</th>
                        <th>Lokasi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dataAbsen)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <strong>Belum ada data absensi.</strong>
                            <div class="small mt-1">Import dari mesin Fingerspot atau tambahkan data secara manual.</div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = $offset + 1; ?>
                    <?php foreach ($dataAbsen as $row): ?>
                    <?php
                        $isUnknown = str_contains($row['nama_pegawai'], 'Tidak Dikenal');
                        $ts   = strtotime($row['tanggal']);
                        $hari = $hariIndo[date('w', $ts)];
                        $ada_in  = !empty($row['jam_kehadiran']);
                        $ada_out = !empty($row['jam_kepulangan']);
                    ?>
                    <tr class="<?= $isUnknown ? 'table-warning' : '' ?>">
                        <td class="ps-4 text-muted small"><?= $no++ ?></td>
                        <td>
                            <div class="fw-semibold"><?= date('d/m/Y', $ts) ?></div>
                            <small class="text-muted"><?= $hari ?></small>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2 py-1">
                                <?= htmlspecialchars($row['id_mesin']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($isUnknown): ?>
                                <span class="text-danger fw-semibold">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($row['nama_pegawai']) ?>
                                </span>
                            <?php else: ?>
                                <span class="fw-semibold"><?= htmlspecialchars($row['nama_pegawai']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($ada_in): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-2">
                                    <i class="bi bi-box-arrow-in-right me-1"></i><?= substr($row['jam_kehadiran'], 0, 5) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($ada_out): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-2">
                                    <i class="bi bi-box-arrow-right me-1"></i><?= substr($row['jam_kepulangan'], 0, 5) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($row['lokasi_absen'] ?? '-') ?></td>
                        <td class="text-center">
                            <?php if ($ada_in && $ada_out): ?>
                                <span class="badge bg-success rounded-pill">Lengkap</span>
                            <?php elseif ($ada_in): ?>
                                <span class="badge bg-warning text-dark rounded-pill">Belum Pulang</span>
                            <?php else: ?>
                                <span class="badge bg-danger rounded-pill">Tidak Hadir</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-4">
                            <form method="POST" action="?page=absen" onsubmit="return confirm('Hapus data absensi ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_delete" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash3"></i>
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

    <!-- PAGINASI -->
    <?php if ($totalPage > 1): ?>
    <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center border-top">
        <small class="text-muted">Halaman <strong><?= $halaman ?></strong> dari <strong><?= $totalPage ?></strong></small>
        <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                <?php if ($halaman > 1): ?>
                <li class="page-item">
                    <a class="page-link rounded" href="?page=absen&p=<?= $halaman-1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                <?php for ($p = max(1, $halaman-2); $p <= min($totalPage, $halaman+2); $p++): ?>
                <li class="page-item <?= $p == $halaman ? 'active' : '' ?>">
                    <a class="page-link rounded" href="?page=absen&p=<?= $p ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($halaman < $totalPage): ?>
                <li class="page-item">
                    <a class="page-link rounded" href="?page=absen&p=<?= $halaman+1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>


<!-- ======================================================
     MODAL: IMPORT EXCEL FINGERSPOT
     ====================================================== -->
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="?page=absen" enctype="multipart/form-data">
            <input type="hidden" name="action" value="import">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white border-0 rounded-top" style="background: linear-gradient(135deg,#198754,#20c997);">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-file-earmark-excel-fill me-2"></i>Import Data Mesin Fingerspot
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 bg-info-subtle rounded-3 mb-3">
                        <div class="fw-semibold mb-2"><i class="bi bi-info-circle-fill me-2"></i>Panduan Format File Excel</div>
                        <p class="small mb-2">Pastikan file Excel memiliki <strong>5 kolom header</strong> di baris pertama:</p>
                        <table class="table table-sm table-bordered small mb-0 bg-white rounded">
                            <thead class="table-light"><tr>
                                <th>Kolom A</th><th>Kolom B</th><th>Kolom C</th><th>Kolom D</th><th>Kolom E</th>
                            </tr></thead>
                            <tbody><tr>
                                <td><code>ID Mesin</code></td>
                                <td><code>Tanggal</code></td>
                                <td><code>Jam Masuk</code></td>
                                <td><code>Jam Pulang</code></td>
                                <td><code>Lokasi</code></td>
                            </tr></tbody>
                        </table>
                        <small class="d-block mt-2 text-muted"><i class="bi bi-lightbulb me-1"></i>Baris pertama (header) akan dilewati secara otomatis oleh sistem.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Pilih File Excel <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" name="file_excel" required accept=".xls,.xlsx">
                        <div class="form-text">Maksimal 10MB. Format yang diterima: .xls dan .xlsx</div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-upload me-1"></i> Proses Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================
     MODAL: TAMBAH DATA MANUAL
     ====================================================== -->
<div class="modal fade" id="modalManual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="?page=absen">
            <input type="hidden" name="action" value="manual">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white border-0 rounded-top" style="background: linear-gradient(135deg,#0d6efd,#6610f2);">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Tambah Absensi Manual
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Pegawai (berdasarkan ID Mesin) <span class="text-danger">*</span></label>
                        <select name="id_mesin" class="form-select" required>
                            <option value="">— Pilih Pegawai —</option>
                            <?php foreach ($pegawaiList as $peg): ?>
                            <option value="<?= htmlspecialchars($peg['ID_PEGAWAI_MESIN']) ?>">
                                [<?= htmlspecialchars($peg['ID_PEGAWAI_MESIN']) ?>] &nbsp;<?= htmlspecialchars($peg['NM_PEGAWAI']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">ID Mesin diambil dari tabel master pegawai yang terdaftar aktif.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-box-arrow-in-right text-success me-1"></i>Jam Masuk
                            </label>
                            <input type="time" name="jam_kehadiran" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-box-arrow-right text-primary me-1"></i>Jam Pulang
                            </label>
                            <input type="time" name="jam_kepulangan" class="form-control">
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Lokasi / Keterangan</label>
                        <input type="text" name="lokasi_absen" class="form-control" placeholder="Contoh: WFH / Dinas Luar / Kantor Pusat" value="Kantor Pusat">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
