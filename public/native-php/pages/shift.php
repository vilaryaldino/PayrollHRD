<?php
// Dummy data shift kerja
if (!isset($_SESSION['shift'])) {
    $_SESSION['shift'] = [
        ['id' => 1, 'nama_shift' => 'Shift Pagi',  'jam_masuk' => '06:00', 'jam_keluar' => '14:00', 'keterangan' => 'Shift pagi reguler'],
        ['id' => 2, 'nama_shift' => 'Shift Sore',  'jam_masuk' => '14:00', 'jam_keluar' => '22:00', 'keterangan' => 'Shift sore reguler'],
        ['id' => 3, 'nama_shift' => 'Shift Malam', 'jam_masuk' => '22:00', 'jam_keluar' => '06:00', 'keterangan' => 'Shift malam reguler'],
    ];
}

// CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $newId = count($_SESSION['shift']) > 0 ? max(array_column($_SESSION['shift'], 'id')) + 1 : 1;
        $_SESSION['shift'][] = [
            'id'          => $newId,
            'nama_shift'  => trim($_POST['nama_shift']),
            'jam_masuk'   => $_POST['jam_masuk'],
            'jam_keluar'  => $_POST['jam_keluar'],
            'keterangan'  => trim($_POST['keterangan']),
        ];
        header("Location: ?page=shift&msg=added"); exit;
    } elseif ($_POST['action'] == 'edit') {
        foreach ($_SESSION['shift'] as $k => $s) {
            if ($s['id'] == $_POST['id']) {
                $_SESSION['shift'][$k] = [
                    'id'         => $s['id'],
                    'nama_shift' => trim($_POST['nama_shift']),
                    'jam_masuk'  => $_POST['jam_masuk'],
                    'jam_keluar' => $_POST['jam_keluar'],
                    'keterangan' => trim($_POST['keterangan']),
                ];
                break;
            }
        }
        header("Location: ?page=shift&msg=edited"); exit;
    } elseif ($_POST['action'] == 'delete') {
        foreach ($_SESSION['shift'] as $k => $s) {
            if ($s['id'] == $_POST['id']) { unset($_SESSION['shift'][$k]); break; }
        }
        $_SESSION['shift'] = array_values($_SESSION['shift']);
        header("Location: ?page=shift&msg=deleted"); exit;
    }
}

// Hitung durasi shift
function hitungDurasi($masuk, $keluar) {
    $m = strtotime($masuk); $k = strtotime($keluar);
    if ($k <= $m) $k += 86400; // lewat tengah malam
    $diff = ($k - $m) / 3600;
    return number_format($diff, 1) . ' jam';
}

$shiftIcons = ['Shift Pagi'=>'bi-sun','Shift Sore'=>'bi-cloud-sun','Shift Malam'=>'bi-moon-stars'];
$shiftColors = ['Shift Pagi'=>['#d1fae5','#065f46'],'Shift Sore'=>['#fef3c7','#92400e'],'Shift Malam'=>['#ede9fe','#5b21b6']];
?>

<style>
.sh-header h1 { font-size:1.45rem; font-weight:700; color:#1e293b; margin:0; }
.sh-header p  { font-size:.84rem; color:#64748b; margin:3px 0 0; }

.sh-card-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.sh-shift-card {
    background:#fff; border-radius:12px; border:1px solid #e8edf2;
    box-shadow:0 2px 10px rgba(0,0,0,.05); padding:1.4rem;
    display:flex; flex-direction:column; gap:.5rem;
}
.sh-shift-icon { font-size:2rem; margin-bottom:.3rem; }
.sh-shift-name { font-weight:700; font-size:1rem; color:#1e293b; }
.sh-shift-time { font-size:.85rem; color:#475569; display:flex; align-items:center; gap:6px; }
.sh-shift-dur  { font-size:.78rem; font-weight:600; padding:.2rem .6rem; border-radius:12px; display:inline-block; margin-top:.3rem; }
.sh-shift-ket  { font-size:.78rem; color:#94a3b8; margin-top:.2rem; }
.sh-shift-actions { margin-top:.5rem; display:flex; gap:.5rem; }

.sh-table-card { background:#fff; border-radius:12px; border:1px solid #e8edf2; box-shadow:0 2px 10px rgba(0,0,0,.05); overflow:hidden; }
.sh-table-card table { margin:0; }
.sh-table-card thead th { background:#f1f5f9; color:#475569; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; padding:.8rem 1.2rem; border:none; border-bottom:2px solid #e2e8f0; }
.sh-table-card tbody td { padding:.85rem 1.2rem; border-color:#f1f5f9; font-size:.87rem; color:#334155; vertical-align:middle; }
.sh-table-card tbody tr:last-child td { border-bottom:none; }
.sh-table-card tbody tr:hover { background:#f8fafc; }

.sh-btn-edit, .sh-btn-del { border:none; background:transparent; width:30px; height:30px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center; font-size:.9rem; cursor:pointer; transition:background .15s; }
.sh-btn-edit { color:#2563eb; } .sh-btn-edit:hover { background:#dbeafe; }
.sh-btn-del  { color:#dc2626; } .sh-btn-del:hover  { background:#fee2e2; }

.sh-modal .modal-content { border-radius:14px; border:none; box-shadow:0 8px 40px rgba(0,0,0,.15); }
.sh-modal .modal-header  { background:#1e293b; color:#fff; border-radius:14px 14px 0 0; padding:1rem 1.5rem; border:none; }
.sh-modal .modal-header .btn-close { filter:invert(1); opacity:.7; }
.sh-modal .modal-body    { padding:1.4rem 1.5rem; }
.sh-modal .modal-footer  { padding:.85rem 1.5rem; background:#f8fafc; border-radius:0 0 14px 14px; border-top:1px solid #e2e8f0; }
.sh-modal .form-label    { font-size:.8rem; font-weight:600; color:#475569; margin-bottom:.3rem; }
.sh-modal .form-control  { border-radius:8px; border-color:#cbd5e1; font-size:.87rem; }
.sh-modal .form-control:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
</style>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3 sh-header">
    <div>
        <h1><i class="bi bi-clock-history me-2 text-primary"></i>Shift Kerja</h1>
        <p>Kelola jadwal shift kerja untuk pegawai harian</p>
    </div>
    <button class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#addShiftModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Shift
    </button>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php $m=['added'=>'ditambahkan','edited'=>'diperbarui','deleted'=>'dihapus']; ?>
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 mb-3">
        <i class="bi bi-check-circle-fill me-2"></i> Data shift berhasil <strong><?= $m[$_GET['msg']] ?? '' ?></strong>.
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Kartu Shift Visual -->
<div class="sh-card-grid">
<?php foreach ($_SESSION['shift'] as $s):
    $icon = $shiftIcons[$s['nama_shift']] ?? 'bi-clock';
    $col  = $shiftColors[$s['nama_shift']] ?? ['#f1f5f9','#334155'];
    $dur  = hitungDurasi($s['jam_masuk'], $s['jam_keluar']);
?>
<div class="sh-shift-card">
    <div class="sh-shift-icon"><i class="bi <?= $icon ?>" style="color:<?= $col[1] ?>"></i></div>
    <div class="sh-shift-name"><?= htmlspecialchars($s['nama_shift']) ?></div>
    <div class="sh-shift-time">
        <i class="bi bi-box-arrow-in-right text-success"></i> <?= $s['jam_masuk'] ?>
        &nbsp;—&nbsp;
        <i class="bi bi-box-arrow-right text-danger"></i> <?= $s['jam_keluar'] ?>
    </div>
    <span class="sh-shift-dur" style="background:<?= $col[0] ?>;color:<?= $col[1] ?>">
        <i class="bi bi-hourglass-split me-1"></i><?= $dur ?>
    </span>
    <div class="sh-shift-ket"><?= htmlspecialchars($s['keterangan']) ?></div>
    <div class="sh-shift-actions">
        <button class="btn btn-sm btn-light border text-primary px-3" data-bs-toggle="modal" data-bs-target="#editShift<?= $s['id'] ?>">
            <i class="bi bi-pencil-square me-1"></i> Edit
        </button>
        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus shift <?= htmlspecialchars(addslashes($s['nama_shift'])) ?>?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <button class="btn btn-sm btn-light border text-danger px-3"><i class="bi bi-trash3 me-1"></i> Hapus</button>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade sh-modal" id="editShift<?= $s['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id"     value="<?= $s['id'] ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-semibold"><i class="bi bi-pencil-square me-2"></i>Edit Shift Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Shift</label>
                        <input type="text" class="form-control" name="nama_shift" value="<?= htmlspecialchars($s['nama_shift']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Jam Masuk</label>
                            <input type="time" class="form-control" name="jam_masuk" value="<?= $s['jam_masuk'] ?>" required>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Jam Keluar</label>
                            <input type="time" class="form-control" name="jam_keluar" value="<?= $s['jam_keluar'] ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <input type="text" class="form-control" name="keterangan" value="<?= htmlspecialchars($s['keterangan']) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Tabel Ringkasan -->
<div class="sh-table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Shift</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Durasi</th>
                    <th>Keterangan</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no=1; foreach ($_SESSION['shift'] as $s): ?>
            <tr>
                <td class="text-muted"><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($s['nama_shift']) ?></strong></td>
                <td><i class="bi bi-box-arrow-in-right text-success me-1"></i><?= $s['jam_masuk'] ?></td>
                <td><i class="bi bi-box-arrow-right text-danger me-1"></i><?= $s['jam_keluar'] ?></td>
                <td><?= hitungDurasi($s['jam_masuk'], $s['jam_keluar']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($s['keterangan']) ?></td>
                <td style="text-align:center">
                    <button class="sh-btn-edit" data-bs-toggle="modal" data-bs-target="#editShift<?= $s['id'] ?>">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Hapus shift ini?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id"     value="<?= $s['id'] ?>">
                        <button class="sh-btn-del"><i class="bi bi-trash3"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade sh-modal" id="addShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-semibold"><i class="bi bi-plus-circle me-2"></i>Tambah Shift Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Shift</label>
                        <input type="text" class="form-control" name="nama_shift" required placeholder="contoh: Shift Pagi">
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Jam Masuk</label>
                            <input type="time" class="form-control" name="jam_masuk" required>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Jam Keluar</label>
                            <input type="time" class="form-control" name="jam_keluar" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <input type="text" class="form-control" name="keterangan" placeholder="opsional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
