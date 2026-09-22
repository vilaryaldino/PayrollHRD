<?php
// pages/jadwal.php - Pengaturan Jadwal Kerja & Shift terhubung langsung ke tabel T_JADWAL_KERJA di MySQL
require_once __DIR__ . '/../includes/db.php';

// Ambil Master Shift dari MySQL
$stmtShift = $pdo->query("SELECT * FROM M_SHIFT ORDER BY JAM_MULAI ASC");
$shifts = $stmtShift->fetchAll();
$shiftMap = [];
foreach ($shifts as $s) {
    $shiftMap[$s['ID_JADWAL']] = $s;
}

// Ambil Master Divisi untuk filter
$stmtDiv = $pdo->query("SELECT ID_DIVISI, NAMA_DIVISI FROM M_DIVISI ORDER BY NAMA_DIVISI ASC");
$masterDivisi = $stmtDiv->fetchAll();

// Handle Form Submission (Simpan ke tabel T_JADWAL_KERJA)
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'set_jadwal') {
        $idPegawai = (int)$_POST['id_pegawai'];
        $tanggal   = trim($_POST['tanggal']);
        $idShift   = !empty($_POST['id_jadwal']) ? (int)$_POST['id_jadwal'] : null;
        $status    = trim($_POST['status_kerja']);
        $ket       = !empty($_POST['keterangan']) ? trim($_POST['keterangan']) : null;

        if ($status === 'Hapus') {
            $stmt = $pdo->prepare("DELETE FROM T_JADWAL_KERJA WHERE ID_PEGAWAI = ? AND TANGGAL = ?");
            $stmt->execute([$idPegawai, $tanggal]);
        } else {
            if ($status !== 'Kerja') {
                $idShift = null;
            }
            $stmt = $pdo->prepare("INSERT INTO T_JADWAL_KERJA (ID_PEGAWAI, TANGGAL, ID_JADWAL, STATUS_KERJA, KETERANGAN, IS_OVERWRITE, ASSIGNED_BY) 
                                   VALUES (?, ?, ?, ?, ?, 1, 'admin_hrd') 
                                   ON DUPLICATE KEY UPDATE 
                                   ID_JADWAL = VALUES(ID_JADWAL), 
                                   STATUS_KERJA = VALUES(STATUS_KERJA), 
                                   KETERANGAN = VALUES(KETERANGAN), 
                                   IS_OVERWRITE = 1, 
                                   UPDATED_AT = CURRENT_TIMESTAMP");
            $stmt->execute([$idPegawai, $tanggal, $idShift, $status, $ket]);
        }

        header("Location: ?page=jadwal&date=" . urlencode($_POST['week_ref'] ?? '') . "&msg=updated");
        exit;

    } elseif ($_POST['action'] === 'bulk_set') {
        $idPegawai = (int)$_POST['id_pegawai'];
        $idShift   = !empty($_POST['id_jadwal']) ? (int)$_POST['id_jadwal'] : null;
        $status    = trim($_POST['status_kerja']);
        $startDate = trim($_POST['start_date']);
        $endDate   = trim($_POST['end_date']);

        if ($status !== 'Kerja') {
            $idShift = null;
        }

        $start = new DateTime($startDate);
        $end   = new DateTime($endDate);
        $end->modify('+1 day');
        $period = new DatePeriod($start, new DateInterval('P1D'), $end);

        $stmt = $pdo->prepare("INSERT INTO T_JADWAL_KERJA (ID_PEGAWAI, TANGGAL, ID_JADWAL, STATUS_KERJA, KETERANGAN, IS_OVERWRITE, ASSIGNED_BY) 
                               VALUES (?, ?, ?, ?, 'Penugasan Kolektif', 1, 'admin_hrd') 
                               ON DUPLICATE KEY UPDATE 
                               ID_JADWAL = VALUES(ID_JADWAL), 
                               STATUS_KERJA = VALUES(STATUS_KERJA), 
                               KETERANGAN = VALUES(KETERANGAN), 
                               IS_OVERWRITE = 1, 
                               UPDATED_AT = CURRENT_TIMESTAMP");

        foreach ($period as $dt) {
            $tgl = $dt->format('Y-m-d');
            $stmt->execute([$idPegawai, $tgl, $idShift, $status]);
        }

        header("Location: ?page=jadwal&date=" . urlencode($_POST['week_ref'] ?? '') . "&msg=bulk_success");
        exit;
    }
}

// Navigasi Rentang Kalender Mingguan
$selectedDate = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$filterDivisi = isset($_GET['divisi']) && $_GET['divisi'] !== '' ? (int)$_GET['divisi'] : '';
$filterJenis  = isset($_GET['jenis']) ? trim($_GET['jenis']) : '';

$dt = new DateTime($selectedDate);
$dayOfWeek = (int)$dt->format('N'); // 1 (Senin) - 7 (Minggu)
$dt->modify('-' . ($dayOfWeek - 1) . ' days');
$startOfWeek = clone $dt;

$weekDays = [];
$namaHariIndo = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
for ($i = 0; $i < 7; $i++) {
    $tglStr = $dt->format('Y-m-d');
    $weekDays[] = [
        'tanggal'   => $tglStr,
        'hari'      => $namaHariIndo[$i],
        'tgl_angka' => $dt->format('d M'),
        'is_today'  => ($tglStr === date('Y-m-d'))
    ];
    $dt->modify('+1 day');
}

$startDateStr = $weekDays[0]['tanggal'];
$endDateStr   = $weekDays[6]['tanggal'];

// Tombol Prev & Next Week
$prevWeek = (clone $startOfWeek)->modify('-7 days')->format('Y-m-d');
$nextWeek = (clone $startOfWeek)->modify('+7 days')->format('Y-m-d');

// Ambil Libur Nasional dalam rentang minggu ini dari MySQL (M_LIBUR_NASIONAL)
$stmtLibur = $pdo->prepare("SELECT TANGGAL, KETERANGAN FROM M_LIBUR_NASIONAL WHERE TANGGAL BETWEEN ? AND ?");
$stmtLibur->execute([$startDateStr, $endDateStr]);
$liburRows = $stmtLibur->fetchAll();
$liburMap = [];
foreach ($liburRows as $lr) {
    $liburMap[$lr['TANGGAL']] = $lr['KETERANGAN'];
}

// Ambil Pegawai Aktif dari MySQL (M_PEGAWAI)
$sqlPeg = "SELECT p.*, d.NAMA_DIVISI 
           FROM M_PEGAWAI p 
           LEFT JOIN M_DIVISI d ON p.ID_DIVISI = d.ID_DIVISI 
           WHERE p.IS_AKTIF = 1";
$paramsPeg = [];
if ($filterDivisi !== '') {
    $sqlPeg .= " AND p.ID_DIVISI = ?";
    $paramsPeg[] = $filterDivisi;
}
if ($filterJenis !== '') {
    $sqlPeg .= " AND p.JENIS_PEGAWAI = ?";
    $paramsPeg[] = $filterJenis;
}
$sqlPeg .= " ORDER BY p.ID_PEGAWAI ASC";
$stmtPeg = $pdo->prepare($sqlPeg);
$stmtPeg->execute($paramsPeg);
$pegawaiList = $stmtPeg->fetchAll();

// Ambil Data Jadwal Kerja Mingguan dari MySQL (T_JADWAL_KERJA)
$stmtJadwal = $pdo->prepare("SELECT * FROM T_JADWAL_KERJA WHERE TANGGAL BETWEEN ? AND ?");
$stmtJadwal->execute([$startDateStr, $endDateStr]);
$jadwalRows = $stmtJadwal->fetchAll();

$jadwalMap = [];
foreach ($jadwalRows as $j) {
    $key = $j['ID_PEGAWAI'] . '_' . $j['TANGGAL'];
    $jadwalMap[$key] = $j;
}
?>

<style>
.jadwal-header h1 { font-size: 1.45rem; font-weight: 700; color: #1e293b; margin: 0; }
.jadwal-header p { font-size: .84rem; color: #64748b; margin: 3px 0 0; }

.jadwal-card {
    background: #fff; border-radius: 12px;
    border: 1px solid #e8edf2; box-shadow: 0 2px 10px rgba(0,0,0,.05);
    overflow: hidden;
}

.table-jadwal th {
    text-align: center;
    vertical-align: middle;
    font-size: 0.8rem;
    padding: 0.75rem 0.5rem;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}
.table-jadwal th.today-col {
    background: #eff6ff;
    color: #1d4ed8;
    border-top: 3px solid #3b82f6;
}
.table-jadwal td {
    vertical-align: middle;
    padding: 0.6rem 0.4rem;
    font-size: 0.82rem;
    border-color: #f1f5f9;
}
.table-jadwal td.today-cell {
    background: #f8faff;
}

.shift-slot {
    min-height: 48px;
    border-radius: 8px;
    padding: 6px 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px dashed #cbd5e1;
    background: #ffffff;
    color: #64748b;
}
.shift-slot:hover {
    border-color: #3b82f6;
    background: #f0f7ff;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

.shift-filled {
    border: none !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}
.shift-filled .shift-name { font-weight: 700; font-size: 0.75rem; }
.shift-filled .shift-time { font-size: 0.68rem; opacity: 0.9; }

.status-off {
    background: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    color: #64748b !important;
    font-weight: 600;
}
.status-cuti {
    background: #fed7aa !important;
    color: #9a3412 !important;
    font-weight: 600;
}
.status-libur-nasional {
    background: #fee2e2 !important;
    color: #b91c1c !important;
    font-weight: 600;
}
.badge-legend {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
}
</style>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 jadwal-header gap-2">
    <div>
        <h1><i class="bi bi-calendar-check me-2 text-primary"></i>Pengaturan Jadwal Kerja & Shift (T_JADWAL_KERJA)</h1>
        <p>Roster penugasan shift kerja harian tersambung langsung dengan Database MySQL</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkModal">
            <i class="bi bi-calendar-range me-1"></i> Penugasan Kolektif
        </button>
        <a href="?page=shift" class="btn btn-light border btn-sm">
            <i class="bi bi-gear me-1"></i> Master Shift
        </a>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 mb-3">
    <i class="bi bi-check-circle-fill me-2"></i>
    <?= $_GET['msg'] === 'bulk_success' ? 'Penugasan shift kolektif berhasil disimpan ke MySQL.' : 'Jadwal kerja pegawai berhasil disimpan ke MySQL.' ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filter & Toolbar Periode -->
<div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white">
    <div class="row g-2 align-items-center">
        <!-- Prev & Next Week -->
        <div class="col-lg-5 col-md-6 d-flex align-items-center gap-2">
            <a href="?page=jadwal&date=<?= $prevWeek ?>&divisi=<?= $filterDivisi ?>&jenis=<?= urlencode($filterJenis) ?>" class="btn btn-light border btn-sm">
                <i class="bi bi-chevron-left"></i>
            </a>
            <div class="fw-bold text-dark px-2 text-center" style="min-width: 190px;">
                <?= $weekDays[0]['tgl_angka'] ?> — <?= $weekDays[6]['tgl_angka'] ?> <?= $startOfWeek->format('Y') ?>
            </div>
            <a href="?page=jadwal&date=<?= $nextWeek ?>&divisi=<?= $filterDivisi ?>&jenis=<?= urlencode($filterJenis) ?>" class="btn btn-light border btn-sm">
                <i class="bi bi-chevron-right"></i>
            </a>
            <a href="?page=jadwal&date=<?= date('Y-m-d') ?>&divisi=<?= $filterDivisi ?>&jenis=<?= urlencode($filterJenis) ?>" class="btn btn-outline-secondary btn-sm">
                Hari Ini
            </a>
        </div>

        <!-- Filter Form -->
        <div class="col-lg-7 col-md-6">
            <form method="GET" class="d-flex flex-wrap gap-2 justify-content-md-end">
                <input type="hidden" name="page" value="jadwal">
                <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
                
                <select name="divisi" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                    <option value="">-- Semua Divisi --</option>
                    <?php foreach ($masterDivisi as $div): ?>
                        <option value="<?= $div['ID_DIVISI'] ?>" <?= $filterDivisi === (int)$div['ID_DIVISI'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($div['NAMA_DIVISI']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="jenis" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    <option value="Staff" <?= $filterJenis === 'Staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="Harian" <?= $filterJenis === 'Harian' ? 'selected' : '' ?>>Harian</option>
                </select>
                
                <?php if ($filterDivisi !== '' || $filterJenis !== ''): ?>
                    <a href="?page=jadwal&date=<?= htmlspecialchars($selectedDate) ?>" class="btn btn-sm btn-light border text-danger" title="Reset Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- Legend Shift Kerja -->
<div class="d-flex flex-wrap gap-2 align-items-center mb-3 px-1">
    <span class="text-muted small fw-bold me-1">Keterangan Shift:</span>
    <?php foreach ($shifts as $s): 
        $warna = $s['WARNA_LABEL'] ?? '#3b82f6';
    ?>
        <span class="badge-legend" style="background-color: <?= $warna ?>15; color: <?= $warna ?>; border: 1px solid <?= $warna ?>40;">
            <i class="bi bi-circle-fill" style="font-size: 0.55rem;"></i> <?= htmlspecialchars($s['NAMA_SHIFT']) ?> (<?= substr($s['JAM_MULAI'], 0, 5) ?> - <?= substr($s['JAM_SELESAI'], 0, 5) ?>)
        </span>
    <?php endforeach; ?>
    <span class="badge-legend bg-light text-secondary border">OFF / Libur</span>
    <span class="badge-legend" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">Libur Nasional</span>
</div>

<!-- Tabel Kalender Roster -->
<div class="jadwal-card">
    <div class="table-responsive">
        <table class="table table-bordered table-jadwal m-0">
            <thead>
                <tr>
                    <th style="width: 220px; text-align: left; padding-left: 1rem;">Pegawai</th>
                    <?php foreach ($weekDays as $day): 
                        $isHoliday = isset($liburMap[$day['tanggal']]);
                    ?>
                        <th class="<?= $day['is_today'] ? 'today-col' : '' ?> <?= $isHoliday ? 'text-danger' : '' ?>">
                            <div><?= $day['hari'] ?></div>
                            <div class="fw-normal small"><?= $day['tgl_angka'] ?></div>
                            <?php if ($isHoliday): ?>
                                <span class="badge bg-danger-subtle text-danger p-0 mt-1 d-block" style="font-size: 0.65rem;" title="<?= htmlspecialchars($liburMap[$day['tanggal']]) ?>">
                                    <i class="bi bi-flag-fill"></i> Libur
                                </span>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pegawaiList)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-3 d-block mb-2"></i>
                            Tidak ada pegawai sesuai filter yang dipilih di database.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pegawaiList as $peg): 
                        $avatarLetter = strtoupper(substr($peg['NM_PEGAWAI'], 0, 1));
                    ?>
                    <tr>
                        <td style="padding-left: 1rem;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white fw-bold" style="width: 34px; height: 34px; font-size: 0.8rem; flex-shrink: 0;">
                                    <?= $avatarLetter ?>
                                </div>
                                <div class="text-truncate">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 140px;"><?= htmlspecialchars($peg['NM_PEGAWAI']) ?></div>
                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($peg['NAMA_DIVISI'] ?? 'Divisi') ?></span>
                                        <?= htmlspecialchars($peg['JENIS_PEGAWAI']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <?php foreach ($weekDays as $day): 
                            $tgl = $day['tanggal'];
                            $key = $peg['ID_PEGAWAI'] . '_' . $tgl;
                            $hasJadwal = isset($jadwalMap[$key]);
                            $dataJadwal = $hasJadwal ? $jadwalMap[$key] : null;

                            $isHoliday = isset($liburMap[$tgl]);
                            $idShift = $dataJadwal['ID_JADWAL'] ?? null;
                            $statusKerja = $dataJadwal['STATUS_KERJA'] ?? ($isHoliday ? 'Libur Nasional' : '-');

                            // Styling slot
                            $slotClass = 'shift-slot';
                            $slotStyle = '';
                            $jamDisplay = '';

                            if ($statusKerja === 'Off') {
                                $slotClass .= ' status-off';
                            } elseif ($statusKerja === 'Cuti') {
                                $slotClass .= ' status-cuti';
                            } elseif ($statusKerja === 'Libur Nasional') {
                                $slotClass .= ' status-libur-nasional';
                            } elseif (!empty($idShift) && isset($shiftMap[$idShift])) {
                                $sh = $shiftMap[$idShift];
                                $color = $sh['WARNA_LABEL'] ?? '#3b82f6';
                                $slotClass .= ' shift-filled';
                                $slotStyle = "background-color: {$color};";
                                $jamDisplay = substr($sh['JAM_MULAI'], 0, 5) . ' - ' . substr($sh['JAM_SELESAI'], 0, 5);
                            }
                        ?>
                            <td class="<?= $day['is_today'] ? 'today-cell' : '' ?>">
                                <div class="<?= $slotClass ?>" style="<?= $slotStyle ?>"
                                     data-bs-toggle="modal"
                                     data-bs-target="#editSlotModal"
                                     data-id-pegawai="<?= $peg['ID_PEGAWAI'] ?>"
                                     data-nama-pegawai="<?= htmlspecialchars($peg['NM_PEGAWAI']) ?>"
                                     data-tanggal="<?= $tgl ?>"
                                     data-tgl-label="<?= $day['hari'] . ', ' . $day['tgl_angka'] ?>"
                                     data-id-jadwal="<?= htmlspecialchars($idShift ?? '') ?>"
                                     data-status="<?= htmlspecialchars($statusKerja) ?>"
                                     data-keterangan="<?= htmlspecialchars($dataJadwal['KETERANGAN'] ?? '') ?>">

                                    <?php if ($statusKerja === 'Off'): ?>
                                        <span style="font-size: 0.72rem;"><i class="bi bi-dash-circle"></i> OFF</span>
                                    <?php elseif ($statusKerja === 'Cuti'): ?>
                                        <span style="font-size: 0.72rem;"><i class="bi bi-airplane"></i> CUTI</span>
                                    <?php elseif ($statusKerja === 'Libur Nasional'): ?>
                                        <span style="font-size: 0.68rem;" class="text-truncate px-1"><i class="bi bi-flag-fill"></i> LIBUR</span>
                                    <?php elseif (!empty($idShift) && isset($shiftMap[$idShift])): ?>
                                        <div class="shift-name text-truncate"><?= htmlspecialchars($shiftMap[$idShift]['NAMA_SHIFT']) ?></div>
                                        <div class="shift-time text-truncate"><?= $jamDisplay ?></div>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-plus"></i> Atur</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Atur Shift Pegawai Harian / Individual Slot -->
<div class="modal fade" id="editSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="action" value="set_jadwal">
            <input type="hidden" name="id_pegawai" id="modal_id_pegawai">
            <input type="hidden" name="tanggal" id="modal_tanggal">
            <input type="hidden" name="week_ref" value="<?= htmlspecialchars($selectedDate) ?>">

            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fs-6 fw-semibold">
                        <i class="bi bi-clock-history me-2"></i>Atur Penugasan Shift (MySQL)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <div class="fw-bold text-dark fs-6" id="modal_nama_pegawai">-</div>
                        <div class="text-muted small"><i class="bi bi-calendar-event me-1"></i>Tanggal: <span id="modal_tgl_label" class="fw-semibold text-primary">-</span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Status Kehadiran / Jadwal</label>
                        <select class="form-select" name="status_kerja" id="modal_status" required onchange="toggleShiftSelect(this.value)">
                            <option value="Kerja">Kerja (Sesuai Shift)</option>
                            <option value="Off">OFF (Hari Bebas Shift)</option>
                            <option value="Cuti">Cuti Tahunan / Khusus</option>
                            <option value="Libur Nasional">Libur Nasional</option>
                            <option value="Hapus">-- Kosongkan / Reset --</option>
                        </select>
                    </div>

                    <div class="mb-3" id="wrap_shift_select">
                        <label class="form-label small fw-bold text-secondary">Pilih Shift Kerja (M_SHIFT)</label>
                        <select class="form-select" name="id_jadwal" id="modal_id_jadwal">
                            <?php foreach ($shifts as $s): ?>
                                <option value="<?= $s['ID_JADWAL'] ?>">
                                    <?= htmlspecialchars($s['NAMA_SHIFT']) ?> (<?= substr($s['JAM_MULAI'], 0, 5) ?> - <?= substr($s['JAM_SELESAI'], 0, 5) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Catatan / Keterangan (Opsional)</label>
                        <input type="text" class="form-control" name="keterangan" id="modal_keterangan" placeholder="contoh: Ganti shift atau SPL standby">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">Simpan ke Database</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Bulk Assignment (Penugasan Kolektif Rentang Tanggal) -->
<div class="modal fade" id="bulkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="action" value="bulk_set">
            <input type="hidden" name="week_ref" value="<?= htmlspecialchars($selectedDate) ?>">

            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fs-6 fw-semibold">
                        <i class="bi bi-calendar-range me-2"></i>Penugasan Shift Kolektif (Rentang Tanggal)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Pilih Pegawai (M_PEGAWAI)</label>
                        <select class="form-select" name="id_pegawai" required>
                            <option value="">-- Pilih Pegawai --</option>
                            <?php foreach ($pegawaiList as $p): ?>
                                <option value="<?= $p['ID_PEGAWAI'] ?>">
                                    <?= htmlspecialchars($p['NM_PEGAWAI']) ?> (<?= htmlspecialchars($p['NAMA_DIVISI'] ?? '') ?> - <?= htmlspecialchars($p['JENIS_PEGAWAI']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Dari Tanggal</label>
                            <input type="date" class="form-control" name="start_date" value="<?= $weekDays[0]['tanggal'] ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="end_date" value="<?= $weekDays[6]['tanggal'] ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Status Kerja</label>
                        <select class="form-select" name="status_kerja" required>
                            <option value="Kerja">Kerja (Sesuai Shift)</option>
                            <option value="Off">OFF / Libur Rutin</option>
                            <option value="Cuti">Cuti</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Pilih Shift Kerja (M_SHIFT)</label>
                        <select class="form-select" name="id_jadwal">
                            <?php foreach ($shifts as $s): ?>
                                <option value="<?= $s['ID_JADWAL'] ?>">
                                    <?= htmlspecialchars($s['NAMA_SHIFT']) ?> (<?= substr($s['JAM_MULAI'], 0, 5) ?> - <?= substr($s['JAM_SELESAI'], 0, 5) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">Terapkan Jadwal ke MySQL</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editSlotModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id-pegawai');
        var nama = button.getAttribute('data-nama-pegawai');
        var tgl = button.getAttribute('data-tanggal');
        var tglLabel = button.getAttribute('data-tgl-label');
        var idJadwal = button.getAttribute('data-id-jadwal');
        var status = button.getAttribute('data-status');
        var ket = button.getAttribute('data-keterangan');

        document.getElementById('modal_id_pegawai').value = id;
        document.getElementById('modal_tanggal').value = tgl;
        document.getElementById('modal_nama_pegawai').innerText = nama;
        document.getElementById('modal_tgl_label').innerText = tglLabel;
        document.getElementById('modal_keterangan').value = ket || '';

        var statusSelect = document.getElementById('modal_status');
        statusSelect.value = (status && status !== '-') ? status : 'Kerja';
        
        var shiftSelect = document.getElementById('modal_id_jadwal');
        if (idJadwal) {
            shiftSelect.value = idJadwal;
        }

        toggleShiftSelect(statusSelect.value);
    });
});

function toggleShiftSelect(val) {
    var wrap = document.getElementById('wrap_shift_select');
    if (val === 'Kerja') {
        wrap.style.display = 'block';
    } else {
        wrap.style.display = 'none';
    }
}
</script>
