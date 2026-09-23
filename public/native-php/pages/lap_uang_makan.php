<?php
// pages/lap_uang_makan.php - Laporan Uang Makan Pegawai
$currentPage = 'lap_uang_makan';
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ============================================================
// KONFIGURASI & FILTER
// ============================================================
$TARIF_UANG_MAKAN = 15000;

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
$bulan = max(1, min(12, $bulan));
$tahun = max(2020, min(2099, $tahun));

$namaBulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
    5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
    9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];

// ============================================================
// QUERY REKAP KEHADIRAN PER PEGAWAI
// ============================================================
$laporan = []; $grandTotal = 0; $totalHariAkumulasi = 0;

try {
    $stmt = $pdo->prepare("
        SELECT
            id_mesin,
            nama_pegawai,
            COUNT(*) AS total_hari_hadir
        FROM t_data_absensi
        WHERE
            MONTH(tanggal) = :bulan
            AND YEAR(tanggal)  = :tahun
            AND jam_kehadiran IS NOT NULL
            AND nama_pegawai NOT LIKE '%Tidak Dikenal%'
        GROUP BY id_mesin, nama_pegawai
        ORDER BY nama_pegawai ASC
    ");
    $stmt->execute([':bulan' => $bulan, ':tahun' => $tahun]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $r) {
        $totalUangMakan      = (int)$r['total_hari_hadir'] * $TARIF_UANG_MAKAN;
        $grandTotal         += $totalUangMakan;
        $totalHariAkumulasi += (int)$r['total_hari_hadir'];
        $laporan[] = [
            'id_mesin'         => $r['id_mesin'],
            'nama_pegawai'     => $r['nama_pegawai'],
            'total_hari_hadir' => (int)$r['total_hari_hadir'],
            'total_uang_makan' => $totalUangMakan,
        ];
    }
} catch (PDOException $e) {
    $laporan = [];
}
?>

<!-- ======================================================
     PAGE HEADER
     ====================================================== -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold text-primary">
            <i class="bi bi-receipt-cutoff me-2"></i>Laporan Uang Makan Pegawai
        </h4>
        <small class="text-muted">
            Rekap tunjangan makan berdasarkan kehadiran &mdash; Tarif
            <strong class="text-success">Rp <?= number_format($TARIF_UANG_MAKAN, 0, ',', '.') ?></strong> / hari hadir
        </small>
    </div>
    <button class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak / PDF
    </button>
</div>

<!-- FILTER BULAN & TAHUN -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="lap_uang_makan">
            <div class="col-auto">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-calendar-month me-1"></i>Periode Laporan</label>
                <div class="d-flex gap-2">
                    <select name="bulan" class="form-select" style="width:160px">
                        <?php foreach ($namaBulan as $n => $nm): ?>
                        <option value="<?= $n ?>" <?= $bulan == $n ? 'selected' : '' ?>><?= $nm ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="tahun" class="form-select" style="width:110px">
                        <?php for ($y = (int)date('Y'); $y >= 2023; $y--): ?>
                        <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-funnel me-1"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KARTU RINGKASAN -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #0d6efd !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#e7f0ff;">
                    <i class="bi bi-people-fill fs-2 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Jumlah Pegawai Hadir</div>
                    <div class="fs-2 fw-bold text-primary lh-1"><?= count($laporan) ?></div>
                    <div class="text-muted small">orang di <?= $namaBulan[$bulan] ?> <?= $tahun ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#e8f7ef;">
                    <i class="bi bi-calendar2-check-fill fs-2 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Hari Hadir (Akumulasi)</div>
                    <div class="fs-2 fw-bold text-success lh-1"><?= number_format($totalHariAkumulasi) ?></div>
                    <div class="text-muted small">hari dari seluruh pegawai</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#fff8e1;">
                    <i class="bi bi-cash-coin fs-2 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Grand Total Uang Makan</div>
                    <div class="fs-4 fw-bold text-dark lh-1">Rp <?= number_format($grandTotal, 0, ',', '.') ?></div>
                    <div class="text-muted small">seluruh pegawai bulan ini</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL LAPORAN -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-table me-2 text-primary"></i>
                Rincian Uang Makan &mdash; <?= $namaBulan[$bulan] ?> <?= $tahun ?>
            </h6>
            <small class="text-muted">Data diambil dari modul Data Absensi</small>
        </div>
        <?php if (!empty($laporan)): ?>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
            <i class="bi bi-check-circle me-1"></i><?= count($laporan) ?> Pegawai
        </span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th class="ps-4 py-3" style="width:50px">No</th>
                        <th class="py-3">ID Mesin</th>
                        <th class="py-3">Nama Pegawai</th>
                        <th class="text-center py-3">Hari Hadir</th>
                        <th class="text-center py-3">Tarif / Hari</th>
                        <th class="text-end pe-4 py-3">Total Uang Makan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                                <strong>Tidak ada data kehadiran</strong> untuk periode
                                <strong><?= $namaBulan[$bulan] ?> <?= $tahun ?></strong>.
                                <div class="small mt-1">
                                    Pastikan data absensi sudah diinput melalui menu
                                    <a href="?page=absen" class="text-decoration-none fw-semibold">Data Absensi</a>.
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($laporan as $i => $row): ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?= $i + 1 ?></td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2">
                                <?= htmlspecialchars($row['id_mesin']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($row['nama_pegawai']) ?></div>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-2">
                                <div class="progress" style="width:60px;height:8px;" title="<?= $row['total_hari_hadir'] ?> hari hadir">
                                    <?php $pct = min(100, round($row['total_hari_hadir'] / 26 * 100)); ?>
                                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                                </div>
                                <strong class="text-success"><?= $row['total_hari_hadir'] ?> hari</strong>
                            </div>
                        </td>
                        <td class="text-center text-muted small">
                            Rp <?= number_format($TARIF_UANG_MAKAN, 0, ',', '.') ?>
                        </td>
                        <td class="text-end pe-4">
                            <strong class="text-primary fs-6">Rp <?= number_format($row['total_uang_makan'], 0, ',', '.') ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($laporan)): ?>
                <tfoot>
                    <tr style="background:#f0f6ff; border-top:2px solid #dee2e6;">
                        <td colspan="3" class="ps-4 py-3 fw-bold text-muted">
                            TOTAL (<?= count($laporan) ?> Pegawai)
                        </td>
                        <td class="text-center py-3 fw-bold text-success fs-6">
                            <?= number_format($totalHariAkumulasi) ?> hari
                        </td>
                        <td></td>
                        <td class="text-end pe-4 py-3">
                            <div class="text-muted small fw-semibold">GRAND TOTAL</div>
                            <div class="fs-5 fw-bold text-primary">Rp <?= number_format($grandTotal, 0, ',', '.') ?></div>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php if (!empty($laporan)): ?>
    <div class="card-footer bg-light text-muted small py-2 px-4 border-top">
        <i class="bi bi-info-circle me-1"></i>
        Laporan ini hanya mencakup pegawai yang tercatat <strong>jam masuk (hadir)</strong> di sistem absensi.
        Rata-rata hari hadir: <strong><?= count($laporan) > 0 ? number_format($totalHariAkumulasi / count($laporan), 1) : 0 ?> hari / orang</strong>.
    </div>
    <?php endif; ?>
</div>

<style>
@media print {
    #sidebar-wrapper, nav.navbar, .btn, form { display: none !important; }
    .card { border: 1px solid #ccc !important; box-shadow: none !important; page-break-inside: avoid; }
    .card-header { background: #f5f5f5 !important; }
    body { font-size: 12px; }
    h4 { font-size: 16px; }
}
</style>
