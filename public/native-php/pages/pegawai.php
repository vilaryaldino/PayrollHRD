<?php
// pages/pegawai.php - Master Data Pegawai terhubung langsung dengan tabel M_PEGAWAI di MySQL
require_once __DIR__ . '/../includes/db.php';

// Ambil Referensi Divisi dari MySQL (M_DIVISI)
$stmtDiv = $pdo->query("SELECT ID_DIVISI, NAMA_DIVISI FROM M_DIVISI ORDER BY NAMA_DIVISI ASC");
$masterDivisi = $stmtDiv->fetchAll();

// Ambil Referensi Kelompok dari MySQL (M_KELOMPOK)
$stmtKel = $pdo->query("SELECT ID_KELOMPOK, NAMA_KELOMPOK FROM M_KELOMPOK ORDER BY NAMA_KELOMPOK ASC");
$masterKelompok = $stmtKel->fetchAll();

// ===== PROSES CRUD (CREATE, UPDATE, DELETE) KE MYSQL =====
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $idMesin   = !empty($_POST['id_pegawai_mesin']) ? trim($_POST['id_pegawai_mesin']) : null;
        $nama      = trim($_POST['nama']);
        $idDivisi  = (int)$_POST['id_divisi'];
        $idKel     = !empty($_POST['id_kelompok']) ? (int)$_POST['id_kelompok'] : null;
        $jenis     = trim($_POST['jenis']);
        $isAktif   = isset($_POST['is_aktif']) ? (int)$_POST['is_aktif'] : 1;
        $noTelp    = !empty($_POST['no_telp']) ? trim($_POST['no_telp']) : null;
        $alamat    = !empty($_POST['alamat']) ? trim($_POST['alamat']) : null;

        // Validasi: Jika memasukkan ID Mesin, cek apakah sudah digunakan oleh pegawai lain yang MASIH AKTIF
        if (!empty($idMesin)) {
            $stmtCek = $pdo->prepare("SELECT ID_PEGAWAI, NM_PEGAWAI FROM M_PEGAWAI WHERE ID_PEGAWAI_MESIN = ? AND IS_AKTIF = 1 LIMIT 1");
            $stmtCek->execute([$idMesin]);
            $pegAktif = $stmtCek->fetch();

            if ($pegAktif) {
                // ID mesin sedang dipakai pegawai aktif
                header("Location: ?page=pegawai&err=" . urlencode("ID Mesin Absen '$idMesin' sedang aktif digunakan oleh pegawai: " . $pegAktif['NM_PEGAWAI'] . ". Hanya ID Mesin dari pegawai yang sudah Resign/Non-Aktif yang dapat dialihkan!"));
                exit;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO M_PEGAWAI (ID_PEGAWAI_MESIN, NM_PEGAWAI, ID_DIVISI, ID_KELOMPOK, JENIS_PEGAWAI, IS_AKTIF, NO_TELP_HP, ALAMAT) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$idMesin, $nama, $idDivisi, $idKel, $jenis, $isAktif, $noTelp, $alamat]);

        header("Location: ?page=pegawai&msg=added");
        exit;

    } elseif ($_POST['action'] === 'edit') {
        $idPegawai = (int)$_POST['id'];
        $idMesin   = !empty($_POST['id_pegawai_mesin']) ? trim($_POST['id_pegawai_mesin']) : null;
        $nama      = trim($_POST['nama']);
        $idDivisi  = (int)$_POST['id_divisi'];
        $idKel     = !empty($_POST['id_kelompok']) ? (int)$_POST['id_kelompok'] : null;
        $jenis     = trim($_POST['jenis']);
        $isAktif   = isset($_POST['is_aktif']) ? (int)$_POST['is_aktif'] : 0;
        $noTelp    = !empty($_POST['no_telp']) ? trim($_POST['no_telp']) : null;
        $alamat    = !empty($_POST['alamat']) ? trim($_POST['alamat']) : null;

        // Validasi: Jika status pegawai ini diset Aktif (1) dan memasukkan ID Mesin, cek apakah sudah dipakai pegawai aktif LAIN
        if (!empty($idMesin) && $isAktif === 1) {
            $stmtCek = $pdo->prepare("SELECT ID_PEGAWAI, NM_PEGAWAI FROM M_PEGAWAI WHERE ID_PEGAWAI_MESIN = ? AND IS_AKTIF = 1 AND ID_PEGAWAI != ? LIMIT 1");
            $stmtCek->execute([$idMesin, $idPegawai]);
            $pegAktif = $stmtCek->fetch();

            if ($pegAktif) {
                header("Location: ?page=pegawai&err=" . urlencode("ID Mesin Absen '$idMesin' sedang aktif digunakan oleh pegawai: " . $pegAktif['NM_PEGAWAI'] . ". Tidak dapat menetapkan ID Mesin yang masih aktif ke pegawai lain!"));
                exit;
            }
        }

        $stmt = $pdo->prepare("UPDATE M_PEGAWAI SET 
                               ID_PEGAWAI_MESIN = ?, NM_PEGAWAI = ?, ID_DIVISI = ?, ID_KELOMPOK = ?, 
                               JENIS_PEGAWAI = ?, IS_AKTIF = ?, NO_TELP_HP = ?, ALAMAT = ? 
                               WHERE ID_PEGAWAI = ?");
        $stmt->execute([$idMesin, $nama, $idDivisi, $idKel, $jenis, $isAktif, $noTelp, $alamat, $idPegawai]);

        header("Location: ?page=pegawai&msg=edited");
        exit;

    } elseif ($_POST['action'] === 'delete') {
        $idPegawai = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM M_PEGAWAI WHERE ID_PEGAWAI = ?");
        $stmt->execute([$idPegawai]);

        header("Location: ?page=pegawai&msg=deleted");
        exit;
    }
}

// ===== FILTER DATA & QUERY KE MYSQL =====
$filterDivisi = isset($_GET['filter_divisi']) && $_GET['filter_divisi'] !== '' ? (int)$_GET['filter_divisi'] : '';
$filterStatus = isset($_GET['filter_status']) && $_GET['filter_status'] !== '' ? (int)$_GET['filter_status'] : '';
$searchQuery  = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT p.*, d.NAMA_DIVISI, k.NAMA_KELOMPOK 
        FROM M_PEGAWAI p 
        LEFT JOIN M_DIVISI d ON p.ID_DIVISI = d.ID_DIVISI 
        LEFT JOIN M_KELOMPOK k ON p.ID_KELOMPOK = k.ID_KELOMPOK 
        WHERE 1=1";
$params = [];

if ($filterDivisi !== '') {
    $sql .= " AND p.ID_DIVISI = ?";
    $params[] = $filterDivisi;
}
if ($filterStatus !== '') {
    $sql .= " AND p.IS_AKTIF = ?";
    $params[] = $filterStatus;
}
if ($searchQuery !== '') {
    $sql .= " AND (p.NM_PEGAWAI LIKE ? OR p.ID_PEGAWAI_MESIN LIKE ? OR p.ALAMAT LIKE ?)";
    $like = "%$searchQuery%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY p.ID_PEGAWAI ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pegawaiList = $stmt->fetchAll();

// Counter Statistik dari Database
$totalPegawai  = $pdo->query("SELECT COUNT(*) FROM M_PEGAWAI")->fetchColumn();
$totalAktif    = $pdo->query("SELECT COUNT(*) FROM M_PEGAWAI WHERE IS_AKTIF = 1")->fetchColumn();
$totalNonAktif = $pdo->query("SELECT COUNT(*) FROM M_PEGAWAI WHERE IS_AKTIF = 0")->fetchColumn();
$totalStaff    = $pdo->query("SELECT COUNT(*) FROM M_PEGAWAI WHERE JENIS_PEGAWAI = 'Staff'")->fetchColumn();
$totalHarian   = $pdo->query("SELECT COUNT(*) FROM M_PEGAWAI WHERE JENIS_PEGAWAI = 'Harian'")->fetchColumn();

$avatarColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6'];
?>

<style>
.pg-header h1 { font-size: 1.45rem; font-weight: 700; color: #1e293b; margin: 0; }
.pg-header p  { font-size: .84rem; color: #64748b; margin: 3px 0 0; }

.pg-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    overflow: hidden; border: 1px solid #e8edf2;
}
.pg-stats {
    display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center;
    padding: .85rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e8edf2;
}
.pg-stat-item { font-size: .82rem; color: #64748b; display: flex; align-items: center; gap: 6px; }
.pg-stat-item strong { color: #1e293b; font-size: .92rem; }

.pg-card .table { margin: 0; }
.pg-card .table thead th {
    background: #f1f5f9; color: #475569;
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    padding: .85rem 1rem; border: none;
    border-bottom: 2px solid #e2e8f0;
}
.pg-card .table tbody td {
    padding: .85rem 1rem; border-color: #f1f5f9;
    vertical-align: middle; font-size: .86rem; color: #334155;
}
.pg-card .table tbody tr:hover { background: #f8fafc; }

.pg-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.pg-name   { font-weight: 600; color: #1e293b; font-size: .88rem; }
.pg-meta   { font-size: .74rem; color: #64748b; display: flex; gap: 6px; align-items: center; }

.pg-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .72rem; font-weight: 600;
    padding: .25rem .6rem; border-radius: 20px;
}
.badge-staff    { background: #dbeafe; color: #1d4ed8; }
.badge-harian   { background: #e0e7ff; color: #4338ca; }
.badge-aktif    { background: #d1fae5; color: #065f46; }
.badge-nonaktif { background: #fee2e2; color: #b91c1c; }

.pg-btn-action {
    border: none; background: transparent;
    width: 32px; height: 32px; border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .88rem; cursor: pointer; transition: background .15s;
}
.pg-btn-edit { color: #2563eb; } .pg-btn-edit:hover { background: #dbeafe; }
.pg-btn-del  { color: #dc2626; } .pg-btn-del:hover  { background: #fee2e2; }
</style>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3 pg-header flex-wrap gap-2">
    <div>
        <h1><i class="bi bi-people-fill me-2 text-primary"></i>Master Data Pegawai (M_PEGAWAI)</h1>
        <p>Tersambung langsung dengan Database MySQL: <code>payrollhrd.M_PEGAWAI</code></p>
    </div>
    <div class="d-flex gap-2">
        <a href="?page=jadwal" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-calendar-check me-1"></i> Jadwal Roster Shift
        </a>
        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addPegawaiModal">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Pegawai
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): 
    $msgMap = ['added' => 'ditambahkan ke MySQL', 'edited' => 'diperbarui di MySQL', 'deleted' => 'dihapus dari MySQL'];
?>
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 mb-3">
    <i class="bi bi-check-circle-fill me-2"></i> Data pegawai berhasil <strong><?= $msgMap[$_GET['msg']] ?? 'diproses' ?></strong>.
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['err'])): ?>
<div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Validasi Gagal:</strong> <?= htmlspecialchars($_GET['err']) ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filter & Toolbar Panel -->
<div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white">
    <form method="GET" class="row g-2 align-items-center">
        <input type="hidden" name="page" value="pegawai">

        <div class="col-md-4 col-sm-12">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0" name="search"
                       value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cari nama, ID mesin, alamat...">
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <select name="filter_divisi" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Semua Divisi --</option>
                <?php foreach ($masterDivisi as $div): ?>
                    <option value="<?= $div['ID_DIVISI'] ?>" <?= $filterDivisi === (int)$div['ID_DIVISI'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($div['NAMA_DIVISI']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3 col-sm-6">
            <select name="filter_status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Semua Status Pegawai --</option>
                <option value="1" <?= $filterStatus === 1 ? 'selected' : '' ?>>Hanya Pegawai Aktif</option>
                <option value="0" <?= $filterStatus === 0 ? 'selected' : '' ?>>Hanya Non-Aktif / Resign</option>
            </select>
        </div>

        <div class="col-md-2 col-sm-12 d-flex gap-1 justify-content-end">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
            <?php if ($filterDivisi !== '' || $filterStatus !== '' || $searchQuery !== ''): ?>
                <a href="?page=pegawai" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Container Tabel Pegawai -->
<div class="pg-card">
    <div class="pg-stats">
        <div class="pg-stat-item"><i class="bi bi-people text-primary"></i> Total: <strong><?= $totalPegawai ?></strong></div>
        <div class="pg-stat-item"><i class="bi bi-check-circle text-success"></i> Aktif: <strong><?= $totalAktif ?></strong></div>
        <div class="pg-stat-item"><i class="bi bi-x-circle text-danger"></i> Resign: <strong><?= $totalNonAktif ?></strong></div>
        <div class="pg-stat-item"><i class="bi bi-person-badge text-info"></i> Staff: <strong><?= $totalStaff ?></strong></div>
        <div class="pg-stat-item"><i class="bi bi-person-lines-fill text-warning"></i> Harian: <strong><?= $totalHarian ?></strong></div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Informasi Pegawai</th>
                    <th>ID Mesin Absen</th>
                    <th>Divisi & Kelompok</th>
                    <th>Kategori</th>
                    <th>Kontak & Alamat</th>
                    <th>Status</th>
                    <th style="width: 80px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($pegawaiList) > 0): ?>
                <?php $no = 1; foreach ($pegawaiList as $p): 
                    $color = $avatarColors[($p['ID_PEGAWAI'] - 1) % count($avatarColors)];
                    $isAktif = (int)$p['IS_AKTIF'];
                ?>
                <tr class="<?= !$isAktif ? 'opacity-75 bg-light' : '' ?>">
                    <td class="text-muted small"><?= $no++ ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="pg-avatar" style="background: <?= $color ?>;">
                                <?= strtoupper(substr($p['NM_PEGAWAI'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="pg-name"><?= htmlspecialchars($p['NM_PEGAWAI']) ?></div>
                                <div class="pg-meta">
                                    <span>ID: EMP-<?= str_pad($p['ID_PEGAWAI'], 4, '0', STR_PAD_LEFT) ?></span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                            <i class="bi bi-fingerprint text-primary me-1"></i><?= htmlspecialchars($p['ID_PEGAWAI_MESIN'] ?: '-') ?>
                        </span>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark small"><i class="bi bi-building me-1 text-muted"></i><?= htmlspecialchars($p['NAMA_DIVISI'] ?? 'Tanpa Divisi') ?></div>
                        <div class="text-muted small"><i class="bi bi-people me-1"></i><?= htmlspecialchars($p['NAMA_KELOMPOK'] ?? 'Non-Kelompok') ?></div>
                    </td>
                    <td>
                        <?php if ($p['JENIS_PEGAWAI'] === 'Staff'): ?>
                            <span class="pg-badge badge-staff"><i class="bi bi-person-badge"></i> Staff</span>
                        <?php else: ?>
                            <span class="pg-badge badge-harian"><i class="bi bi-person-lines-fill"></i> Harian</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="small text-dark"><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($p['NO_TELP_HP'] ?: '-') ?></div>
                        <div class="text-muted small text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($p['ALAMAT'] ?: '-') ?>">
                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['ALAMAT'] ?: '-') ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($isAktif === 1): ?>
                            <span class="pg-badge badge-aktif"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                        <?php else: ?>
                            <span class="pg-badge badge-nonaktif"><i class="bi bi-dash-circle-fill"></i> Resign</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <button class="pg-btn-action pg-btn-edit" title="Edit Pegawai"
                                data-bs-toggle="modal" data-bs-target="#editModal<?= $p['ID_PEGAWAI'] ?>">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus data pegawai <?= htmlspecialchars(addslashes($p['NM_PEGAWAI'])) ?> dari database?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $p['ID_PEGAWAI'] ?>">
                            <button type="submit" class="pg-btn-action pg-btn-del" title="Hapus">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- Modal Edit Pegawai -->
                <div class="modal fade" id="editModal<?= $p['ID_PEGAWAI'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <form method="POST">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $p['ID_PEGAWAI'] ?>">

                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-header bg-dark text-white rounded-top-4">
                                    <h5 class="modal-title fs-6 fw-semibold">
                                        <i class="bi bi-pencil-square me-2"></i>Edit Pegawai: <?= htmlspecialchars($p['NM_PEGAWAI']) ?>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Nama Lengkap (NM_PEGAWAI)</label>
                                            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($p['NM_PEGAWAI']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">ID Mesin Fingerprint (ID_PEGAWAI_MESIN)</label>
                                            <input type="text" class="form-control" name="id_pegawai_mesin" value="<?= htmlspecialchars($p['ID_PEGAWAI_MESIN'] ?? '') ?>" placeholder="contoh: FP001">
                                            <div class="form-text" style="font-size: 0.72rem; color: #64748b;">
                                                <i class="bi bi-info-circle me-1"></i>Boleh menggunakan ID Mesin dari pegawai yang berstatus <strong>Resign</strong>.
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Divisi (M_DIVISI)</label>
                                            <select class="form-select" name="id_divisi" required>
                                                <?php foreach ($masterDivisi as $div): ?>
                                                    <option value="<?= $div['ID_DIVISI'] ?>" <?= (int)$p['ID_DIVISI'] === (int)$div['ID_DIVISI'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($div['NAMA_DIVISI']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Kelompok / Regu Kerja (M_KELOMPOK)</label>
                                            <select class="form-select" name="id_kelompok">
                                                <option value="">-- Tanpa Kelompok --</option>
                                                <?php foreach ($masterKelompok as $kel): ?>
                                                    <option value="<?= $kel['ID_KELOMPOK'] ?>" <?= (int)$p['ID_KELOMPOK'] === (int)$kel['ID_KELOMPOK'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($kel['NAMA_KELOMPOK']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Jenis Pegawai (JENIS_PEGAWAI)</label>
                                            <select class="form-select" name="jenis" required>
                                                <option value="Staff" <?= $p['JENIS_PEGAWAI'] === 'Staff' ? 'selected' : '' ?>>Staff (Bulanan)</option>
                                                <option value="Harian" <?= $p['JENIS_PEGAWAI'] === 'Harian' ? 'selected' : '' ?>>Harian (Shift Reguler)</option>
                                                <option value="Kontrak" <?= $p['JENIS_PEGAWAI'] === 'Kontrak' ? 'selected' : '' ?>>Kontrak</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Status Kepegawaian (IS_AKTIF)</label>
                                            <select class="form-select" name="is_aktif" required>
                                                <option value="1" <?= $isAktif === 1 ? 'selected' : '' ?>>Aktif Bekerja</option>
                                                <option value="0" <?= $isAktif === 0 ? 'selected' : '' ?>>Non-Aktif / Resign</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Nomor HP / WhatsApp (NO_TELP_HP)</label>
                                            <input type="text" class="form-control" name="no_telp" value="<?= htmlspecialchars($p['NO_TELP_HP'] ?? '') ?>" placeholder="08xxxxxxxxx">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Alamat Tinggal (ALAMAT)</label>
                                            <input type="text" class="form-control" name="alamat" value="<?= htmlspecialchars($p['ALAMAT'] ?? '') ?>" placeholder="Alamat domisili">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-primary px-4">Simpan Perubahan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        Tidak ada data pegawai yang sesuai kriteria pencarian / filter di database.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Pegawai -->
<div class="modal fade" id="addPegawaiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fs-6 fw-semibold">
                        <i class="bi bi-person-plus-fill me-2"></i>Tambah Pegawai Baru (Database MySQL)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nama Lengkap (NM_PEGAWAI)</label>
                            <input type="text" class="form-control" name="nama" required placeholder="contoh: Andi Wijaya">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ID Mesin Fingerprint (ID_PEGAWAI_MESIN)</label>
                            <input type="text" class="form-control" name="id_pegawai_mesin" placeholder="contoh: FP007">
                            <div class="form-text" style="font-size: 0.72rem; color: #64748b;">
                                <i class="bi bi-info-circle me-1"></i>Boleh menggunakan ID Mesin dari pegawai yang sudah <strong>Resign</strong>. Tidak boleh sama dengan pegawai yang masih <strong>Aktif</strong>.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Divisi (M_DIVISI)</label>
                            <select class="form-select" name="id_divisi" required>
                                <option value="">-- Pilih Divisi --</option>
                                <?php foreach ($masterDivisi as $div): ?>
                                    <option value="<?= $div['ID_DIVISI'] ?>"><?= htmlspecialchars($div['NAMA_DIVISI']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kelompok / Regu Kerja (M_KELOMPOK)</label>
                            <select class="form-select" name="id_kelompok">
                                <option value="">-- Tanpa Kelompok --</option>
                                <?php foreach ($masterKelompok as $kel): ?>
                                    <option value="<?= $kel['ID_KELOMPOK'] ?>"><?= htmlspecialchars($kel['NAMA_KELOMPOK']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Jenis Pegawai (JENIS_PEGAWAI)</label>
                            <select class="form-select" name="jenis" required>
                                <option value="Harian" selected>Harian (Shift Reguler)</option>
                                <option value="Staff">Staff (Bulanan)</option>
                                <option value="Kontrak">Kontrak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Status Kepegawaian (IS_AKTIF)</label>
                            <select class="form-select" name="is_aktif" required>
                                <option value="1" selected>Aktif Bekerja</option>
                                <option value="0">Non-Aktif / Resign</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nomor HP / WhatsApp (NO_TELP_HP)</label>
                            <input type="text" class="form-control" name="no_telp" placeholder="contoh: 081234567890">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Alamat Tinggal (ALAMAT)</label>
                            <input type="text" class="form-control" name="alamat" placeholder="Jl. Raya No...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">Simpan Pegawai ke Database</button>
                </div>
            </div>
        </form>
    </div>
</div>
