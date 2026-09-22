<?php
// pages/shift.php - Master Data Shift Kerja langsung tersambung ke tabel M_SHIFT di MySQL
require_once __DIR__ . '/../includes/db.php';

// CRUD Shift ke MySQL
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $code      = strtoupper(trim($_POST['shift_code']));
        $nama      = trim($_POST['nama_shift']);
        $mulai     = $_POST['jam_mulai'];
        $selesai   = $_POST['jam_selesai'];
        $awal      = $_POST['jam_awal'];
        $akhir     = $_POST['jam_akhir'];
        $overnight = isset($_POST['is_overnight']) ? 1 : 0;
        $warna     = $_POST['warna'] ?? '#3B82F6';

        $stmt = $pdo->prepare("INSERT INTO M_SHIFT (SHIFT_CODE, NAMA_SHIFT, JAM_MULAI, JAM_SELESAI, JAM_AWAL, JAM_AKHIR, IS_OVERNIGHT, WARNA_LABEL)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $nama, $mulai, $selesai, $awal, $akhir, $overnight, $warna]);

        header("Location: ?page=shift&msg=added");
        exit;

    } elseif ($_POST['action'] === 'edit') {
        $idJadwal  = (int)$_POST['id'];
        $code      = strtoupper(trim($_POST['shift_code']));
        $nama      = trim($_POST['nama_shift']);
        $mulai     = $_POST['jam_mulai'];
        $selesai   = $_POST['jam_selesai'];
        $awal      = $_POST['jam_awal'];
        $akhir     = $_POST['jam_akhir'];
        $overnight = isset($_POST['is_overnight']) ? 1 : 0;
        $warna     = $_POST['warna'] ?? '#3B82F6';

        $stmt = $pdo->prepare("UPDATE M_SHIFT SET 
                               SHIFT_CODE = ?, NAMA_SHIFT = ?, JAM_MULAI = ?, JAM_SELESAI = ?, 
                               JAM_AWAL = ?, JAM_AKHIR = ?, IS_OVERNIGHT = ?, WARNA_LABEL = ?
                               WHERE ID_JADWAL = ?");
        $stmt->execute([$code, $nama, $mulai, $selesai, $awal, $akhir, $overnight, $warna, $idJadwal]);

        header("Location: ?page=shift&msg=edited");
        exit;

    } elseif ($_POST['action'] === 'delete') {
        $idJadwal = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM M_SHIFT WHERE ID_JADWAL = ?");
        $stmt->execute([$idJadwal]);

        header("Location: ?page=shift&msg=deleted");
        exit;
    }
}

// Ambil daftar shift dari MySQL
$stmt = $pdo->query("SELECT * FROM M_SHIFT ORDER BY JAM_MULAI ASC");
$shifts = $stmt->fetchAll();

// Hitung durasi kerja
function hitungDurasiShift($mulai, $selesai) {
    $m = strtotime($mulai);
    $s = strtotime($selesai);
    if ($s <= $m) {
        $s += 86400; // Lintas hari
    }
    $diff = ($s - $m) / 3600;
    return number_format($diff, 1) . ' jam';
}
?>

<style>
.sh-header h1 { font-size: 1.45rem; font-weight: 700; color: #1e293b; margin: 0; }
.sh-header p  { font-size: .84rem; color: #64748b; margin: 3px 0 0; }

.sh-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.2rem;
    margin-bottom: 1.5rem;
}
.sh-shift-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e8edf2;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    padding: 1.3rem;
    display: flex;
    flex-direction: column;
    gap: .7rem;
    position: relative;
    overflow: hidden;
}
.sh-card-bar {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
}
.sh-shift-name { font-weight: 700; font-size: 1rem; color: #1e293b; }
.sh-time-box {
    background: #f8fafc;
    border-radius: 8px;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    font-size: .83rem;
}
.sh-btn-action {
    border: none; background: transparent;
    width: 32px; height: 32px; border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .9rem; cursor: pointer; transition: background .15s;
}
.sh-btn-edit { color: #2563eb; } .sh-btn-edit:hover { background: #dbeafe; }
.sh-btn-del  { color: #dc2626; } .sh-btn-del:hover  { background: #fee2e2; }
</style>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3 sh-header flex-wrap gap-2">
    <div>
        <h1><i class="bi bi-clock-history me-2 text-primary"></i>Master Shift Kerja (M_SHIFT)</h1>
        <p>Tersambung langsung dengan Database MySQL: <code>payrollhrd.M_SHIFT</code></p>
    </div>
    <div class="d-flex gap-2">
        <a href="?page=jadwal" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-calendar-check me-1"></i> Jadwal Kerja Roster
        </a>
        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addShiftModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Shift Baru
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): 
    $m = ['added' => 'ditambahkan ke MySQL', 'edited' => 'diperbarui di MySQL', 'deleted' => 'dihapus dari MySQL'];
?>
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 mb-3">
    <i class="bi bi-check-circle-fill me-2"></i> Konfigurasi shift berhasil <strong><?= $m[$_GET['msg']] ?? 'disimpan' ?></strong>.
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Grid Kartu Shift -->
<div class="sh-card-grid">
    <?php foreach ($shifts as $s): 
        $color = $s['WARNA_LABEL'] ?? '#3B82F6';
    ?>
    <div class="sh-shift-card">
        <div class="sh-card-bar" style="background-color: <?= $color ?>;"></div>
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span class="badge" style="background-color: <?= $color ?>20; color: <?= $color ?>; border: 1px solid <?= $color ?>40;">
                    <?= htmlspecialchars($s['SHIFT_CODE']) ?>
                </span>
                <div class="sh-shift-name mt-1"><?= htmlspecialchars($s['NAMA_SHIFT']) ?></div>
            </div>
            <div class="d-flex gap-1">
                <button class="sh-btn-action sh-btn-edit" data-bs-toggle="modal" data-bs-target="#editShift<?= $s['ID_JADWAL'] ?>">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <form method="POST" class="d-inline" onsubmit="return confirm('Hapus shift <?= htmlspecialchars(addslashes($s['NAMA_SHIFT'])) ?> dari database?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $s['ID_JADWAL'] ?>">
                    <button type="submit" class="sh-btn-action sh-btn-del"><i class="bi bi-trash3"></i></button>
                </form>
            </div>
        </div>

        <div class="sh-time-box">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted"><i class="bi bi-play-circle text-success me-1"></i>Jam Kerja:</span>
                <strong class="text-dark"><?= substr($s['JAM_MULAI'], 0, 5) ?> - <?= substr($s['JAM_SELESAI'], 0, 5) ?></strong>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size: 0.76rem;">
                <span><i class="bi bi-shield-check text-primary me-1"></i>Rentang Scan Absen:</span>
                <span><?= substr($s['JAM_AWAL'], 0, 5) ?> - <?= substr($s['JAM_AKHIR'], 0, 5) ?></span>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center small text-muted pt-1">
            <span><i class="bi bi-hourglass-split me-1"></i>Durasi: <strong><?= hitungDurasiShift($s['JAM_MULAI'], $s['JAM_SELESAI']) ?></strong></span>
            <?php if (!empty($s['IS_OVERNIGHT'])): ?>
                <span class="badge" style="background:#ede9fe; color:#6b21a8;"><i class="bi bi-moon-stars me-1"></i>Lintas Hari</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Edit Shift -->
    <div class="modal fade" id="editShift<?= $s['ID_JADWAL'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $s['ID_JADWAL'] ?>">

                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header bg-dark text-white rounded-top-4">
                        <h5 class="modal-title fs-6 fw-semibold"><i class="bi bi-pencil-square me-2"></i>Edit Shift Kerja</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Kode Shift (SHIFT_CODE)</label>
                                <input type="text" class="form-control" name="shift_code" value="<?= htmlspecialchars($s['SHIFT_CODE']) ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Warna Label Kalender</label>
                                <input type="color" class="form-control form-control-color w-100" name="warna" value="<?= $s['WARNA_LABEL'] ?? '#3b82f6' ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Nama Shift (NAMA_SHIFT)</label>
                                <input type="text" class="form-control" name="nama_shift" value="<?= htmlspecialchars($s['NAMA_SHIFT']) ?>" required>
                            </div>

                            <div class="col-6">
                                <label class="form-label small fw-bold text-success">Jam Mulai (JAM_MULAI)</label>
                                <input type="time" class="form-control" name="jam_mulai" value="<?= substr($s['JAM_MULAI'], 0, 5) ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-danger">Jam Selesai (JAM_SELESAI)</label>
                                <input type="time" class="form-control" name="jam_selesai" value="<?= substr($s['JAM_SELESAI'], 0, 5) ?>" required>
                            </div>

                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Batas Scan Awal (JAM_AWAL)</label>
                                <input type="time" class="form-control" name="jam_awal" value="<?= substr($s['JAM_AWAL'], 0, 5) ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Batas Scan Akhir (JAM_AKHIR)</label>
                                <input type="time" class="form-control" name="jam_akhir" value="<?= substr($s['JAM_AKHIR'], 0, 5) ?>" required>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_overnight" id="chkOvernight<?= $s['ID_JADWAL'] ?>" <?= !empty($s['IS_OVERNIGHT']) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="chkOvernight<?= $s['ID_JADWAL'] ?>">
                                        Shift Lintas Hari (Melewati jam 00:00 tengah malam)
                                    </label>
                                </div>
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
</div>

<!-- Modal Tambah Shift Baru -->
<div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fs-6 fw-semibold"><i class="bi bi-plus-circle me-2"></i>Tambah Shift Kerja (M_SHIFT)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Kode Shift (SHIFT_CODE)</label>
                            <input type="text" class="form-control" name="shift_code" placeholder="contoh: SH-SORE2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Warna Label</label>
                            <input type="color" class="form-control form-control-color w-100" name="warna" value="#3B82F6">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Nama Shift (NAMA_SHIFT)</label>
                            <input type="text" class="form-control" name="nama_shift" placeholder="contoh: Shift Operasional 2" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-bold text-success">Jam Mulai (JAM_MULAI)</label>
                            <input type="time" class="form-control" name="jam_mulai" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-danger">Jam Selesai (JAM_SELESAI)</label>
                            <input type="time" class="form-control" name="jam_selesai" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Batas Scan Awal (JAM_AWAL)</label>
                            <input type="time" class="form-control" name="jam_awal" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Batas Scan Akhir (JAM_AKHIR)</label>
                            <input type="time" class="form-control" name="jam_akhir" required>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_overnight" id="chkAddOvernight">
                                <label class="form-check-label small" for="chkAddOvernight">
                                    Shift Lintas Hari (Melewati tengah malam)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">Simpan Shift ke Database</button>
                </div>
            </div>
        </form>
    </div>
</div>
