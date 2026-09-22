<?php
// ===== INISIALISASI DATA DUMMY (Session) =====
// Reset session jika data lama tidak punya key 'shift'
if (!isset($_SESSION['pegawai']) || !isset($_SESSION['pegawai'][0]['shift'])) {
    $_SESSION['pegawai'] = array(
        array('id'=>1, 'nama'=>'Budi Santoso',  'divisi'=>'IT',       'jabatan'=>'Staff',  'shift'=>'-'),
        array('id'=>2, 'nama'=>'Siti Aminah',   'divisi'=>'Produksi', 'jabatan'=>'Harian', 'shift'=>'Pagi'),
        array('id'=>3, 'nama'=>'Agus Setiawan', 'divisi'=>'Keamanan', 'jabatan'=>'Harian', 'shift'=>'Malam'),
        array('id'=>4, 'nama'=>'Dewi Rahayu',   'divisi'=>'HRD',      'jabatan'=>'Staff',  'shift'=>'-'),
        array('id'=>5, 'nama'=>'Rendi Pratama', 'divisi'=>'Finance',  'jabatan'=>'Harian', 'shift'=>'Sore'),
    );
}

// ===== PROSES CRUD =====
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $jabatan = trim($_POST['jabatan']);
    $shift   = ($jabatan === 'Harian') ? trim($_POST['shift']) : '-';

    if ($_POST['action'] == 'add') {
        $ids = array();
        foreach ($_SESSION['pegawai'] as $row) { $ids[] = $row['id']; }
        $newId = count($ids) > 0 ? (max($ids) + 1) : 1;
        $_SESSION['pegawai'][] = array(
            'id'      => $newId,
            'nama'    => trim($_POST['nama']),
            'divisi'  => trim($_POST['divisi']),
            'jabatan' => $jabatan,
            'shift'   => $shift,
        );
        header("Location: ?page=pegawai&msg=added"); exit;

    } elseif ($_POST['action'] == 'edit') {
        foreach ($_SESSION['pegawai'] as $key => $peg) {
            if ($peg['id'] == $_POST['id']) {
                $_SESSION['pegawai'][$key]['nama']    = trim($_POST['nama']);
                $_SESSION['pegawai'][$key]['divisi']  = trim($_POST['divisi']);
                $_SESSION['pegawai'][$key]['jabatan'] = $jabatan;
                $_SESSION['pegawai'][$key]['shift']   = $shift;
                break;
            }
        }
        header("Location: ?page=pegawai&msg=edited"); exit;

    } elseif ($_POST['action'] == 'delete') {
        foreach ($_SESSION['pegawai'] as $key => $peg) {
            if ($peg['id'] == $_POST['id']) {
                unset($_SESSION['pegawai'][$key]);
                $_SESSION['pegawai'] = array_values($_SESSION['pegawai']);
                break;
            }
        }
        header("Location: ?page=pegawai&msg=deleted"); exit;
    }
}

// ===== HELPER FUNCTIONS =====
$avatarColors = array('#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6');

function countJabatan($data, $jabatan) {
    $c = 0;
    foreach ($data as $row) { if ($row['jabatan'] == $jabatan) $c++; }
    return $c;
}
function countShift($data, $shift) {
    $c = 0;
    foreach ($data as $row) {
        $s = isset($row['shift']) ? $row['shift'] : '-';
        if ($s == $shift) $c++;
    }
    return $c;
}
function getShiftBadge($shift) {
    $map = array(
        'Pagi'  => array('badge-pagi',  'bi-sun',        'Pagi'),
        'Sore'  => array('badge-sore',  'bi-cloud-sun',  'Sore'),
        'Malam' => array('badge-malam', 'bi-moon-stars', 'Malam'),
    );
    return isset($map[$shift]) ? $map[$shift] : array('badge-pagi','bi-clock',$shift);
}
?>

<style>
.pg-header h1 { font-size:1.45rem; font-weight:700; color:#1e293b; margin:0; }
.pg-header p  { font-size:.84rem; color:#64748b; margin:3px 0 0; }

.pg-card {
    background:#fff; border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,.07);
    overflow:hidden; border:1px solid #e8edf2;
}
.pg-stats {
    display:flex; gap:1.2rem; flex-wrap:wrap; align-items:center;
    padding:.85rem 1.5rem; background:#f8fafc; border-bottom:1px solid #e8edf2;
}
.pg-stat-item { font-size:.8rem; color:#64748b; display:flex; align-items:center; gap:5px; }
.pg-stat-item strong { color:#1e293b; }

.pg-card .table { margin:0; }
.pg-card .table thead th {
    background:#f1f5f9; color:#475569;
    font-size:.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em;
    padding:.8rem 1.2rem; border:none;
    border-bottom:2px solid #e2e8f0;
}
.pg-card .table tbody td {
    padding:.85rem 1.2rem; border-color:#f1f5f9;
    vertical-align:middle; font-size:.87rem; color:#334155;
}
.pg-card .table tbody tr:last-child td { border-bottom:none; }
.pg-card .table tbody tr:hover { background:#f8fafc; }

.pg-avatar {
    width:38px; height:38px; border-radius:50%;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:.85rem; font-weight:700; color:#fff; flex-shrink:0;
}
.pg-name  { font-weight:600; color:#1e293b; font-size:.87rem; }
.pg-empid { font-size:.73rem; color:#94a3b8; }

.pg-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:.73rem; font-weight:600;
    padding:.28rem .65rem; border-radius:20px;
}
.badge-staff  { background:#dbeafe; color:#1d4ed8; }
.badge-harian { background:#e0e7ff; color:#4338ca; }
.badge-pagi   { background:#d1fae5; color:#065f46; }
.badge-sore   { background:#fef3c7; color:#92400e; }
.badge-malam  { background:#ede9fe; color:#5b21b6; }

.pg-btn-edit, .pg-btn-del {
    border:none; background:transparent;
    width:30px; height:30px; border-radius:7px;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:.9rem; cursor:pointer; transition:background .15s;
}
.pg-btn-edit { color:#2563eb; } .pg-btn-edit:hover { background:#dbeafe; }
.pg-btn-del  { color:#dc2626; } .pg-btn-del:hover  { background:#fee2e2; }

.pg-modal .modal-content { border-radius:14px; border:none; box-shadow:0 8px 40px rgba(0,0,0,.15); }
.pg-modal .modal-header  { background:#1e293b; color:#fff; border-radius:14px 14px 0 0; padding:1rem 1.5rem; border:none; }
.pg-modal .modal-header .btn-close { filter:invert(1); opacity:.7; }
.pg-modal .modal-body    { padding:1.4rem 1.5rem; }
.pg-modal .modal-footer  { padding:.85rem 1.5rem; background:#f8fafc; border-radius:0 0 14px 14px; border-top:1px solid #e2e8f0; }
.pg-modal .form-label    { font-size:.8rem; font-weight:600; color:#475569; margin-bottom:.3rem; }
.pg-modal .form-control,
.pg-modal .form-select   { border-radius:8px; border-color:#cbd5e1; font-size:.87rem; }
.pg-modal .form-control:focus,
.pg-modal .form-select:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.15); }

.shift-row { display:none; }
.shift-row.show { display:table-row; }
</style>

<!-- ===== HEADER ===== -->
<div class="d-flex justify-content-between align-items-center mb-3 pg-header">
    <div>
        <h1><i class="bi bi-people-fill me-2 text-primary"></i>Data Pegawai</h1>
        <p>Kelola data karyawan beserta jabatan dan shift kerja harian</p>
    </div>
    <button class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Pegawai
    </button>
</div>

<?php if (isset($_GET['msg'])) :
    $msgMap = array('added'=>'ditambahkan','edited'=>'diperbarui','deleted'=>'dihapus');
    $msgText = isset($msgMap[$_GET['msg']]) ? $msgMap[$_GET['msg']] : $_GET['msg'];
?>
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 mb-3">
    <i class="bi bi-check-circle-fill me-2"></i> Data pegawai berhasil <strong><?php echo $msgText; ?></strong>.
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ===== TABEL ===== -->
<div class="pg-card">
    <div class="pg-stats">
        <div class="pg-stat-item"><i class="bi bi-people text-primary"></i> Total: <strong><?php echo count($_SESSION['pegawai']); ?></strong></div>
        <div class="pg-stat-item"><i class="bi bi-person-badge" style="color:#1d4ed8"></i> Staff: <strong><?php echo countJabatan($_SESSION['pegawai'],'Staff'); ?></strong></div>
        <div class="pg-stat-item"><i class="bi bi-person-lines-fill" style="color:#4338ca"></i> Harian: <strong><?php echo countJabatan($_SESSION['pegawai'],'Harian'); ?></strong></div>
        <div class="pg-stat-item"><i class="bi bi-sun text-success"></i> Pagi: <strong><?php echo countShift($_SESSION['pegawai'],'Pagi'); ?></strong></div>
        <div class="pg-stat-item"><i class="bi bi-cloud-sun text-warning"></i> Sore: <strong><?php echo countShift($_SESSION['pegawai'],'Sore'); ?></strong></div>
        <div class="pg-stat-item"><i class="bi bi-moon-stars" style="color:#7c3aed"></i> Malam: <strong><?php echo countShift($_SESSION['pegawai'],'Malam'); ?></strong></div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width:4%">No</th>
                    <th>Nama Pegawai</th>
                    <th>Divisi</th>
                    <th>Jabatan</th>
                    <th>Shift Aktif</th>
                    <th style="width:9%;text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($_SESSION['pegawai']) > 0) : ?>
                <?php $no = 1; foreach ($_SESSION['pegawai'] as $p) : ?>
                <?php $color = $avatarColors[($p['id'] - 1) % count($avatarColors)]; ?>
                <tr>
                    <td class="text-muted"><?php echo $no++; ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="pg-avatar" style="background:<?php echo $color; ?>">
                                <?php echo strtoupper(substr($p['nama'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="pg-name"><?php echo htmlspecialchars($p['nama']); ?></div>
                                <div class="pg-empid">EMP-<?php echo str_pad($p['id'], 4, '0', STR_PAD_LEFT); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><i class="bi bi-building me-1 text-muted"></i><?php echo htmlspecialchars($p['divisi']); ?></td>
                    <td>
                        <?php if ($p['jabatan'] === 'Staff') : ?>
                            <span class="pg-badge badge-staff"><i class="bi bi-person-badge"></i> Staff</span>
                        <?php else : ?>
                            <span class="pg-badge badge-harian"><i class="bi bi-person-lines-fill"></i> Harian</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['jabatan'] === 'Harian') :
                            $sb = getShiftBadge($p['shift']); ?>
                            <span class="pg-badge <?php echo $sb[0]; ?>">
                                <i class="bi <?php echo $sb[1]; ?>"></i> <?php echo htmlspecialchars($p['shift']); ?>
                            </span>
                        <?php else : ?>
                            <span class="text-muted" style="font-size:.8rem">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <button class="pg-btn-edit" title="Edit"
                            data-bs-toggle="modal" data-bs-target="#editModal<?php echo $p['id']; ?>">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <form method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus data <?php echo htmlspecialchars(addslashes($p['nama'])); ?>?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"     value="<?php echo $p['id']; ?>">
                            <button type="submit" class="pg-btn-del" title="Hapus">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- Modal Edit -->
                <div class="modal fade pg-modal" id="editModal<?php echo $p['id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="POST">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id"     value="<?php echo $p['id']; ?>">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-6 fw-semibold">
                                        <i class="bi bi-pencil-square me-2"></i>Edit Pegawai
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Pegawai</label>
                                        <input type="text" class="form-control" name="nama"
                                            value="<?php echo htmlspecialchars($p['nama']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Divisi</label>
                                        <input type="text" class="form-control" name="divisi"
                                            value="<?php echo htmlspecialchars($p['divisi']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Jabatan</label>
                                        <select class="form-select jabatan-sel" name="jabatan"
                                            data-target="shiftWrap<?php echo $p['id']; ?>" required>
                                            <option value="Staff"  <?php echo ($p['jabatan']=='Staff'  ? 'selected' : ''); ?>>Staff</option>
                                            <option value="Harian" <?php echo ($p['jabatan']=='Harian' ? 'selected' : ''); ?>>Harian</option>
                                        </select>
                                    </div>
                                    <div class="mb-3" id="shiftWrap<?php echo $p['id']; ?>"
                                         style="<?php echo ($p['jabatan']=='Harian' ? '' : 'display:none;'); ?>">
                                        <label class="form-label">
                                            <i class="bi bi-clock me-1 text-primary"></i>Shift Kerja
                                        </label>
                                        <select class="form-select" name="shift">
                                            <option value="Pagi"  <?php echo ($p['shift']=='Pagi'  ? 'selected' : ''); ?>>☀️  Shift Pagi</option>
                                            <option value="Sore"  <?php echo ($p['shift']=='Sore'  ? 'selected' : ''); ?>>🌤️  Shift Sore</option>
                                            <option value="Malam" <?php echo ($p['shift']=='Malam' ? 'selected' : ''); ?>>🌙  Shift Malam</option>
                                        </select>
                                        <div class="form-text">Shift bisa diubah kapan saja.</div>
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

            <?php else : ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Belum ada data pegawai.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade pg-modal" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-semibold">
                        <i class="bi bi-person-plus-fill me-2"></i>Tambah Pegawai Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" required placeholder="contoh: Budi Santoso">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Divisi</label>
                        <input type="text" class="form-control" name="divisi" required placeholder="contoh: IT, HRD, Produksi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <select class="form-select jabatan-sel" name="jabatan" required data-target="shiftWrapAdd">
                            <option value="">-- Pilih Jabatan --</option>
                            <option value="Staff">Staff</option>
                            <option value="Harian">Harian</option>
                        </select>
                    </div>
                    <div class="mb-3" id="shiftWrapAdd" style="display:none;">
                        <label class="form-label">
                            <i class="bi bi-clock me-1 text-primary"></i>Shift Kerja
                        </label>
                        <select class="form-select" name="shift">
                            <option value="Pagi">☀️  Shift Pagi</option>
                            <option value="Sore">🌤️  Shift Sore</option>
                            <option value="Malam">🌙  Shift Malam</option>
                        </select>
                        <div class="form-text">Shift bisa diubah kapan saja.</div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sels = document.querySelectorAll('.jabatan-sel');
    for (var i = 0; i < sels.length; i++) {
        sels[i].addEventListener('change', function () {
            var target = document.getElementById(this.getAttribute('data-target'));
            if (target) {
                target.style.display = (this.value === 'Harian') ? 'block' : 'none';
            }
        });
    }
});
</script>
