<?php
// Dummy data libur nasional
if (!isset($_SESSION['libur'])) {
    $_SESSION['libur'] = [
        ['id'=>1,  'nama_libur'=>'Tahun Baru Masehi',         'tanggal'=>'2025-01-01', 'jenis'=>'Nasional'],
        ['id'=>2,  'nama_libur'=>'Isra Mi\'raj Nabi Muhammad', 'tanggal'=>'2025-01-27', 'jenis'=>'Keagamaan'],
        ['id'=>3,  'nama_libur'=>'Hari Raya Nyepi',           'tanggal'=>'2025-03-29', 'jenis'=>'Keagamaan'],
        ['id'=>4,  'nama_libur'=>'Wafat Isa Al-Masih',        'tanggal'=>'2025-04-18', 'jenis'=>'Keagamaan'],
        ['id'=>5,  'nama_libur'=>'Hari Buruh Internasional',  'tanggal'=>'2025-05-01', 'jenis'=>'Nasional'],
        ['id'=>6,  'nama_libur'=>'Hari Raya Idul Fitri',      'tanggal'=>'2025-03-31', 'jenis'=>'Keagamaan'],
        ['id'=>7,  'nama_libur'=>'Hari Kemerdekaan RI',       'tanggal'=>'2025-08-17', 'jenis'=>'Nasional'],
        ['id'=>8,  'nama_libur'=>'Hari Raya Idul Adha',       'tanggal'=>'2025-06-06', 'jenis'=>'Keagamaan'],
        ['id'=>9,  'nama_libur'=>'Hari Natal',                'tanggal'=>'2025-12-25', 'jenis'=>'Keagamaan'],
    ];
    usort($_SESSION['libur'], fn($a,$b) => strcmp($a['tanggal'], $b['tanggal']));
}

// CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $newId = count($_SESSION['libur']) > 0 ? max(array_column($_SESSION['libur'], 'id')) + 1 : 1;
        $_SESSION['libur'][] = [
            'id'         => $newId,
            'nama_libur' => trim($_POST['nama_libur']),
            'tanggal'    => $_POST['tanggal'],
            'jenis'      => $_POST['jenis'],
        ];
        usort($_SESSION['libur'], fn($a,$b) => strcmp($a['tanggal'], $b['tanggal']));
        header("Location: ?page=libur&msg=added"); exit;
    } elseif ($_POST['action'] == 'edit') {
        foreach ($_SESSION['libur'] as $k => $l) {
            if ($l['id'] == $_POST['id']) {
                $_SESSION['libur'][$k] = [
                    'id'         => $l['id'],
                    'nama_libur' => trim($_POST['nama_libur']),
                    'tanggal'    => $_POST['tanggal'],
                    'jenis'      => $_POST['jenis'],
                ];
                break;
            }
        }
        usort($_SESSION['libur'], fn($a,$b) => strcmp($a['tanggal'], $b['tanggal']));
        header("Location: ?page=libur&msg=edited"); exit;
    } elseif ($_POST['action'] == 'delete') {
        foreach ($_SESSION['libur'] as $k => $l) {
            if ($l['id'] == $_POST['id']) { unset($_SESSION['libur'][$k]); break; }
        }
        $_SESSION['libur'] = array_values($_SESSION['libur']);
        header("Location: ?page=libur&msg=deleted"); exit;
    }
}

$bulanIndo = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function formatTanggal($tgl, $bulan) {
    $d = explode('-', $tgl);
    return $d[2] . ' ' . $bulan[(int)$d[1]] . ' ' . $d[0];
}
function hariIndo($tgl) {
    $hari = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    return $hari[date('l', strtotime($tgl))] ?? '';
}
function isUpcoming($tgl) { return strtotime($tgl) >= strtotime(date('Y-m-d')); }
function isPast($tgl)     { return strtotime($tgl) <  strtotime(date('Y-m-d')); }
?>

<style>
.lb-header h1 { font-size:1.45rem; font-weight:700; color:#1e293b; margin:0; }
.lb-header p  { font-size:.84rem; color:#64748b; margin:3px 0 0; }

.lb-stats { display:flex; gap:1rem; margin-bottom:1.2rem; flex-wrap:wrap; }
.lb-stat  {
    background:#fff; border-radius:10px; border:1px solid #e8edf2;
    box-shadow:0 1px 6px rgba(0,0,0,.04);
    padding:.8rem 1.2rem; display:flex; align-items:center; gap:.7rem; min-width:140px;
}
.lb-stat-icon { font-size:1.5rem; }
.lb-stat-val  { font-size:1.3rem; font-weight:700; color:#1e293b; line-height:1; }
.lb-stat-lbl  { font-size:.73rem; color:#64748b; }

.lb-card { background:#fff; border-radius:12px; border:1px solid #e8edf2; box-shadow:0 2px 10px rgba(0,0,0,.05); overflow:hidden; }
.lb-card table { margin:0; }
.lb-card thead th { background:#f1f5f9; color:#475569; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; padding:.8rem 1.2rem; border:none; border-bottom:2px solid #e2e8f0; }
.lb-card tbody td { padding:.85rem 1.2rem; border-color:#f1f5f9; font-size:.87rem; color:#334155; vertical-align:middle; }
.lb-card tbody tr:last-child td { border-bottom:none; }
.lb-card tbody tr:hover { background:#f8fafc; }
.lb-card tbody tr.past-row td { opacity:.55; }

.lb-badge-nasional  { background:#dbeafe; color:#1d4ed8; padding:.25rem .65rem; border-radius:20px; font-size:.73rem; font-weight:600; }
.lb-badge-keagamaan { background:#d1fae5; color:#065f46; padding:.25rem .65rem; border-radius:20px; font-size:.73rem; font-weight:600; }
.lb-badge-upcoming  { background:#fef9c3; color:#854d0e; padding:.2rem .6rem; border-radius:12px; font-size:.7rem; font-weight:600; }
.lb-badge-past      { background:#f1f5f9; color:#94a3b8; padding:.2rem .6rem; border-radius:12px; font-size:.7rem; }

.lb-btn-edit, .lb-btn-del { border:none; background:transparent; width:30px; height:30px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center; font-size:.9rem; cursor:pointer; transition:background .15s; }
.lb-btn-edit { color:#2563eb; } .lb-btn-edit:hover { background:#dbeafe; }
.lb-btn-del  { color:#dc2626; } .lb-btn-del:hover  { background:#fee2e2; }

.lb-modal .modal-content { border-radius:14px; border:none; box-shadow:0 8px 40px rgba(0,0,0,.15); }
.lb-modal .modal-header  { background:#1e293b; color:#fff; border-radius:14px 14px 0 0; padding:1rem 1.5rem; border:none; }
.lb-modal .modal-header .btn-close { filter:invert(1); opacity:.7; }
.lb-modal .modal-body    { padding:1.4rem 1.5rem; }
.lb-modal .modal-footer  { padding:.85rem 1.5rem; background:#f8fafc; border-radius:0 0 14px 14px; border-top:1px solid #e2e8f0; }
.lb-modal .form-label    { font-size:.8rem; font-weight:600; color:#475569; margin-bottom:.3rem; }
.lb-modal .form-control,
.lb-modal .form-select   { border-radius:8px; border-color:#cbd5e1; font-size:.87rem; }
.lb-modal .form-control:focus,
.lb-modal .form-select:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
</style>

<?php
$totalNasional  = count(array_filter($_SESSION['libur'], fn($l)=>$l['jenis']=='Nasional'));
$totalKeagamaan = count(array_filter($_SESSION['libur'], fn($l)=>$l['jenis']=='Keagamaan'));
$totalUpcoming  = count(array_filter($_SESSION['libur'], fn($l)=>isUpcoming($l['tanggal'])));
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3 lb-header">
    <div>
        <h1><i class="bi bi-calendar-event me-2 text-primary"></i>Libur Nasional</h1>
        <p>Daftar hari libur nasional dan keagamaan tahun <?= date('Y') ?></p>
    </div>
    <button class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#addLiburModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Libur
    </button>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php $m=['added'=>'ditambahkan','edited'=>'diperbarui','deleted'=>'dihapus']; ?>
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 mb-3">
        <i class="bi bi-check-circle-fill me-2"></i> Data libur berhasil <strong><?= $m[$_GET['msg']] ?? '' ?></strong>.
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistik -->
<div class="lb-stats">
    <div class="lb-stat">
        <div class="lb-stat-icon">📅</div>
        <div><div class="lb-stat-val"><?= count($_SESSION['libur']) ?></div><div class="lb-stat-lbl">Total Hari Libur</div></div>
    </div>
    <div class="lb-stat">
        <div class="lb-stat-icon">🏛️</div>
        <div><div class="lb-stat-val"><?= $totalNasional ?></div><div class="lb-stat-lbl">Libur Nasional</div></div>
    </div>
    <div class="lb-stat">
        <div class="lb-stat-icon">🕌</div>
        <div><div class="lb-stat-val"><?= $totalKeagamaan ?></div><div class="lb-stat-lbl">Libur Keagamaan</div></div>
    </div>
    <div class="lb-stat">
        <div class="lb-stat-icon">⏳</div>
        <div><div class="lb-stat-val"><?= $totalUpcoming ?></div><div class="lb-stat-lbl">Akan Datang</div></div>
    </div>
</div>

<!-- Tabel Libur -->
<div class="lb-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Hari Libur</th>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no=1; foreach ($_SESSION['libur'] as $l): ?>
            <tr class="<?= isPast($l['tanggal']) ? 'past-row' : '' ?>">
                <td class="text-muted"><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($l['nama_libur']) ?></strong></td>
                <td><?= formatTanggal($l['tanggal'], $bulanIndo) ?></td>
                <td><?= hariIndo($l['tanggal']) ?></td>
                <td>
                    <?php if ($l['jenis']=='Nasional'): ?>
                        <span class="lb-badge-nasional"><i class="bi bi-flag me-1"></i>Nasional</span>
                    <?php else: ?>
                        <span class="lb-badge-keagamaan"><i class="bi bi-moon me-1"></i>Keagamaan</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (isUpcoming($l['tanggal'])): ?>
                        <span class="lb-badge-upcoming"><i class="bi bi-clock me-1"></i>Akan Datang</span>
                    <?php else: ?>
                        <span class="lb-badge-past"><i class="bi bi-check2 me-1"></i>Sudah Lewat</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center">
                    <button class="lb-btn-edit" data-bs-toggle="modal" data-bs-target="#editLibur<?= $l['id'] ?>">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Hapus hari libur ini?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id"     value="<?= $l['id'] ?>">
                        <button class="lb-btn-del"><i class="bi bi-trash3"></i></button>
                    </form>
                </td>
            </tr>

            <!-- Modal Edit -->
            <div class="modal fade lb-modal" id="editLibur<?= $l['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id"     value="<?= $l['id'] ?>">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fs-6 fw-semibold"><i class="bi bi-pencil-square me-2"></i>Edit Hari Libur</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nama Hari Libur</label>
                                    <input type="text" class="form-control" name="nama_libur" value="<?= htmlspecialchars($l['nama_libur']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" value="<?= $l['tanggal'] ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis</label>
                                    <select class="form-select" name="jenis" required>
                                        <option value="Nasional"  <?= $l['jenis']=='Nasional'  ?'selected':'' ?>>Nasional</option>
                                        <option value="Keagamaan" <?= $l['jenis']=='Keagamaan' ?'selected':'' ?>>Keagamaan</option>
                                    </select>
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
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade lb-modal" id="addLiburModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-semibold"><i class="bi bi-calendar-plus me-2"></i>Tambah Hari Libur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Hari Libur</label>
                        <input type="text" class="form-control" name="nama_libur" required placeholder="contoh: Hari Kemerdekaan RI">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis</label>
                        <select class="form-select" name="jenis" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Nasional">Nasional</option>
                            <option value="Keagamaan">Keagamaan</option>
                        </select>
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
