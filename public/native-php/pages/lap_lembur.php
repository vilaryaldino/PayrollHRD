<?php
// pages/lap_lembur.php - Laporan Lembur Pegawai (dari t_register_lembur)
$currentPage = 'lap_lembur';
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ============================================================
// FILTER
// ============================================================
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
// FUNGSI FORMAT JAM
// ============================================================
function fmtJam($desimal) {
    if ((float)$desimal <= 0) return '<span class="text-muted">0 jam</span>';
    $mnt  = (int)round((float)$desimal * 60);
    $j    = intdiv($mnt, 60);
    $m    = $mnt % 60;
    if ($j > 0 && $m > 0) return "{$j} jam {$m} mnt";
    if ($j > 0) return "{$j} jam";
    return "{$m} menit";
}

// ============================================================
// AJAX: Detail per hari (dipanggil modal via fetch)
// ============================================================
if (isset($_GET['ajax_detail'])) {
    header('Content-Type: application/json');
    $idPeg = (int)($_GET['id_pegawai'] ?? 0);
    $bl    = (int)($_GET['bulan'] ?? date('m'));
    $thn   = (int)($_GET['tahun'] ?? date('Y'));
    $result = [];
    if ($idPeg > 0) {
        try {
            $stD = $pdo->prepare("
                SELECT tanggal, hari, jenis_spl, jam_mulai, jam_selesai, durasi_lembur, uang_makan, catatan
                FROM t_register_lembur
                WHERE id_pegawai = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                ORDER BY tanggal ASC, jam_mulai ASC
            ");
            $stD->execute([$idPeg, $bl, $thn]);
            foreach ($stD->fetchAll() as $dr) {
                $mnt  = (int)round((float)$dr['durasi_lembur'] * 60);
                $jj   = intdiv($mnt, 60); $mm = $mnt % 60;
                $dur  = ($jj > 0 && $mm > 0) ? "{$jj}j {$mm}m" : ($jj > 0 ? "{$jj} jam" : "{$mm} menit");
                $result[] = [
                    'tanggal'    => date('d/m/Y', strtotime($dr['tanggal'])),
                    'hari'       => $dr['hari'],
                    'jenis_spl'  => $dr['jenis_spl'],
                    'jam_mulai'  => substr($dr['jam_mulai'], 0, 5),
                    'jam_selesai'=> substr($dr['jam_selesai'], 0, 5),
                    'durasi'     => $dur,
                    'uang_makan' => (float)$dr['uang_makan'],
                    'catatan'    => $dr['catatan'] ?? '-',
                ];
            }
        } catch (Exception $e) { $result = []; }
    }
    echo json_encode($result);
    exit;
}

// ============================================================
// QUERY REKAP LEMBUR
// ============================================================
$laporan = []; $grandDurasi = 0; $grandUangMakan = 0; $grandSesi = 0;

try {
    // Buat tabel jika belum ada
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
        `durasi_lembur` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        `uang_makan` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->prepare("
        SELECT
            rl.id_pegawai,
            rl.nama_pegawai,
            COUNT(rl.id)          AS total_sesi,
            SUM(rl.durasi_lembur) AS total_durasi,
            SUM(rl.uang_makan)    AS total_uang_makan,
            MIN(rl.tanggal)       AS tgl_pertama,
            MAX(rl.tanggal)       AS tgl_terakhir
        FROM t_register_lembur rl
        WHERE MONTH(rl.tanggal) = :bulan AND YEAR(rl.tanggal) = :tahun
        GROUP BY rl.id_pegawai, rl.nama_pegawai
        ORDER BY total_durasi DESC
    ");
    $stmt->execute([':bulan' => $bulan, ':tahun' => $tahun]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $r) {
        $grandDurasi    += (float)$r['total_durasi'];
        $grandUangMakan += (float)$r['total_uang_makan'];
        $grandSesi      += (int)$r['total_sesi'];
        $laporan[] = $r;
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
            <i class="bi bi-moon-stars-fill me-2"></i>Laporan Lembur Pegawai
        </h4>
        <small class="text-muted">
            Rekapitulasi lembur (SPL) berdasarkan registrasi &mdash;
            Uang makan lembur <strong class="text-warning">Rp 15.000</strong> jika selesai lewat pukul 20:00
        </small>
    </div>
    <button class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak / PDF
    </button>
</div>

<!-- FILTER -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="lap_lembur">
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
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left:4px solid #dc3545 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#fdecea;">
                    <i class="bi bi-people-fill fs-2 text-danger"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Pegawai Lembur</div>
                    <div class="fs-2 fw-bold text-danger lh-1"><?= count($laporan) ?></div>
                    <div class="text-muted small">orang</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left:4px solid #6c757d !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#f2f3f4;">
                    <i class="bi bi-clipboard2-check-fill fs-2 text-secondary"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Sesi Lembur</div>
                    <div class="fs-2 fw-bold text-secondary lh-1"><?= number_format($grandSesi) ?></div>
                    <div class="text-muted small">sesi / SPL</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left:4px solid #212529 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#e9ecef;">
                    <i class="bi bi-hourglass-split fs-2 text-dark"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Jam Lembur</div>
                    <div class="fs-4 fw-bold text-dark lh-1"><?= number_format($grandDurasi, 1) ?> jam</div>
                    <div class="text-muted small">akumulasi seluruh pegawai</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left:4px solid #ffc107 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#fff8e1;">
                    <i class="bi bi-wallet2 fs-2 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Uang Makan Lembur</div>
                    <div class="fs-5 fw-bold text-dark lh-1">Rp <?= number_format($grandUangMakan, 0, ',', '.') ?></div>
                    <div class="text-muted small">yang wajib dibayarkan</div>
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
                <i class="bi bi-table me-2 text-danger"></i>
                Rincian Lembur &mdash; <?= $namaBulan[$bulan] ?> <?= $tahun ?>
            </h6>
            <small class="text-muted">Klik <strong>Detail</strong> untuk melihat breakdown per hari</small>
        </div>
        <?php if (!empty($laporan)): ?>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
            <i class="bi bi-moon-stars me-1"></i><?= count($laporan) ?> Pegawai Lembur
        </span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th class="ps-4 py-3" style="width:50px">No</th>
                        <th class="py-3">Nama Pegawai</th>
                        <th class="text-center py-3">Sesi Lembur</th>
                        <th class="text-center py-3">Total Durasi</th>
                        <th class="text-center py-3">Rata-rata / Sesi</th>
                        <th class="text-end py-3">Uang Makan Lembur</th>
                        <th class="text-center pe-4 py-3">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-moon fs-1 d-block mb-2 opacity-50"></i>
                                <strong>Tidak ada data lembur</strong> untuk periode
                                <strong><?= $namaBulan[$bulan] ?> <?= $tahun ?></strong>.
                                <div class="small mt-1">
                                    Silakan input melalui menu
                                    <a href="?page=register_lembur" class="text-decoration-none fw-semibold">Register Lembur</a>.
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($laporan as $i => $row): ?>
                    <?php
                        $rataRata  = (int)$row['total_sesi'] > 0 ? (float)$row['total_durasi'] / (int)$row['total_sesi'] : 0;
                        $pctDurasi = $grandDurasi > 0 ? round((float)$row['total_durasi'] / $grandDurasi * 100) : 0;
                    ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?= $i + 1 ?></td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($row['nama_pegawai']) ?></div>
                            <small class="text-muted">
                                <?= date('d/m', strtotime($row['tgl_pertama'])) ?> &ndash; <?= date('d/m/Y', strtotime($row['tgl_terakhir'])) ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fw-semibold">
                                <?= (int)$row['total_sesi'] ?> Sesi
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="fw-bold text-dark"><?= fmtJam($row['total_durasi']) ?></div>
                            <div class="progress mt-1 mx-auto" style="height:5px;width:80px;" title="<?= $pctDurasi ?>% dari total lembur">
                                <div class="progress-bar bg-danger" style="width:<?= $pctDurasi ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $pctDurasi ?>% dari total</small>
                        </td>
                        <td class="text-center">
                            <span class="text-secondary small"><?= fmtJam($rataRata) ?></span>
                        </td>
                        <td class="text-end">
                            <?php if ((float)$row['total_uang_makan'] > 0): ?>
                                <strong class="text-success">Rp <?= number_format($row['total_uang_makan'], 0, ',', '.') ?></strong>
                            <?php else: ?>
                                <span class="text-muted small">Rp 0 <br><em>(selesai sebelum 20:00)</em></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-4">
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal" data-bs-target="#modalDetail"
                                onclick="loadDetail(<?= (int)$row['id_pegawai'] ?>, '<?= htmlspecialchars(addslashes($row['nama_pegawai'])) ?>', <?= $bulan ?>, <?= $tahun ?>)">
                                <i class="bi bi-eye me-1"></i>Detail
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($laporan)): ?>
                <tfoot>
                    <tr style="background:#f0f6ff; border-top:2px solid #dee2e6;">
                        <td colspan="2" class="ps-4 py-3 fw-bold text-muted">TOTAL (<?= count($laporan) ?> Pegawai)</td>
                        <td class="text-center py-3 fw-bold"><?= number_format($grandSesi) ?> Sesi</td>
                        <td class="text-center py-3 fw-bold text-dark"><?= fmtJam($grandDurasi) ?></td>
                        <td></td>
                        <td class="text-end py-3">
                            <div class="text-muted small fw-semibold">GRAND TOTAL UANG MAKAN</div>
                            <div class="fs-5 fw-bold text-success">Rp <?= number_format($grandUangMakan, 0, ',', '.') ?></div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php if (!empty($laporan)): ?>
    <div class="card-footer bg-light text-muted small py-2 px-4 border-top">
        <i class="bi bi-info-circle me-1"></i>
        Uang makan lembur (<strong>Rp 15.000</strong>) hanya diberikan jika jam selesai lembur melewati pukul <strong>20:00</strong>,
        sesuai aturan SPL yang berlaku di perusahaan.
    </div>
    <?php endif; ?>
</div>

<!-- ======================================================
     MODAL DETAIL PER HARI
     ====================================================== -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white border-0 rounded-top" style="background:linear-gradient(135deg,#212529,#495057)">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-clock-history me-2"></i>Detail Lembur per Hari
                    </h5>
                    <small id="modalSubtitle" class="opacity-75"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modalDetailBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-secondary" role="status"></div>
                    <div class="mt-2 text-muted">Memuat data...</div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <small class="text-muted me-auto">
                    <i class="bi bi-lightbulb me-1"></i>
                    Uang makan lembur dibayarkan jika jam selesai &gt; 20:00
                </small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    #sidebar-wrapper, nav.navbar, .btn, form, #modalDetail { display: none !important; }
    .card { border: 1px solid #ccc !important; box-shadow: none !important; page-break-inside: avoid; }
    body { font-size: 12px; }
    h4 { font-size: 16px; }
}
</style>

<script>
function loadDetail(idPegawai, nama, bulan, tahun) {
    document.getElementById('modalSubtitle').textContent = nama + ' — ' + bulan + '/' + tahun;
    document.getElementById('modalDetailBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-secondary" role="status"></div>
            <div class="mt-2 text-muted">Memuat data lembur...</div>
        </div>`;

    fetch('?page=lap_lembur&ajax_detail=1&id_pegawai=' + idPegawai + '&bulan=' + bulan + '&tahun=' + tahun)
        .then(r => r.json())
        .then(data => {
            if (!data || data.length === 0) {
                document.getElementById('modalDetailBody').innerHTML =
                    '<p class="text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Tidak ada detail data ditemukan.</p>';
                return;
            }

            let totalDurasi = 0, totalUM = 0;
            let rows = data.map((r, idx) => {
                totalUM += parseFloat(r.uang_makan);
                const umTxt = r.uang_makan > 0
                    ? `<span class="badge bg-success-subtle text-success border border-success-subtle">Rp ${parseInt(r.uang_makan).toLocaleString('id-ID')}</span>`
                    : `<span class="text-muted small">—</span>`;
                return `<tr>
                    <td class="ps-3 text-muted small">${idx+1}</td>
                    <td class="fw-semibold">${r.tanggal}</td>
                    <td>${r.hari}</td>
                    <td><span class="badge bg-secondary-subtle text-secondary border">${r.jenis_spl}</span></td>
                    <td class="text-center fw-semibold text-success">${r.jam_mulai}</td>
                    <td class="text-center fw-semibold text-primary">${r.jam_selesai}</td>
                    <td class="text-center"><span class="badge bg-dark text-white">${r.durasi}</span></td>
                    <td class="text-center">${umTxt}</td>
                    <td class="pe-3 text-muted small">${r.catatan}</td>
                </tr>`;
            }).join('');

            let html = `
            <div class="p-3 bg-light border-bottom d-flex gap-4">
                <div><strong class="text-danger">${data.length}</strong> <span class="text-muted small">Sesi Lembur</span></div>
                <div><strong class="text-dark">Rp ${totalUM.toLocaleString('id-ID')}</strong> <span class="text-muted small">Uang Makan</span></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">No</th>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Jenis SPL</th>
                            <th class="text-center">Jam Mulai</th>
                            <th class="text-center">Jam Selesai</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-center">Uang Makan</th>
                            <th class="pe-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
            document.getElementById('modalDetailBody').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('modalDetailBody').innerHTML =
                '<p class="text-center text-danger py-5"><i class="bi bi-exclamation-triangle-fill me-1"></i>Gagal memuat data detail.</p>';
        });
}
</script>
