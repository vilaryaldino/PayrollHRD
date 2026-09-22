<?php
$currentPage = 'register_lembur';

$pegawaiList = [
    'Andi Wijaya',
    'Budi Santoso',
    'Citra Lestari',
    'Dewi Anggraini',
    'Eko Prasetyo',
];

function formatJam($value)
{
    if ($value === null || $value === '') {
        return '0 jam';
    }

    $detik = (float) $value * 3600;
    $jam = floor($detik / 3600);
    $menit = floor(($detik % 3600) / 60);

    if ($jam > 0 && $menit > 0) {
        return $jam . ' jam ' . $menit . ' menit';
    }

    if ($jam > 0) {
        return $jam . ' jam';
    }

    return $menit . ' menit';
}

$submitted = false;
$summary = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;

    $pegawai = trim($_POST['pegawai'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
    $jamMulai = trim($_POST['jam_mulai'] ?? '17:00');
    $jamSelesai = trim($_POST['jam_selesai'] ?? '20:00');
    $hari = trim($_POST['hari'] ?? 'Hari Kerja');
    $jenisSPL = trim($_POST['jenis_spl'] ?? 'SPL Jam Lembur');
    $catatan = trim($_POST['catatan'] ?? '');

    $mulai = strtotime($jamMulai);
    $selesai = strtotime($jamSelesai);
    $jamKerjaNormal = strtotime('17:00');
    $batasUangMakan = strtotime('20:00');

    $durasiTotal = max(0, ($selesai - $mulai) / 3600);
    $lemburDiLuarJamKerja = $selesai > $jamKerjaNormal
        ? max(0, ($selesai - max($mulai, $jamKerjaNormal)) / 3600)
        : 0;
    $uangMakan = $selesai > $batasUangMakan ? 15000 : 0;

    $summary = [
        'pegawai' => $pegawai ?: 'Belum dipilih',
        'tanggal' => $tanggal,
        'jam_mulai' => $jamMulai,
        'jam_selesai' => $jamSelesai,
        'hari' => $hari,
        'jenis_spl' => $jenisSPL,
        'catatan' => $catatan ?: 'Tidak ada catatan tambahan',
        'durasi_total' => $durasiTotal,
        'lembur_di_luar_jam_kerja' => $lemburDiLuarJamKerja,
        'uang_makan' => $uangMakan,
    ];
}

$displaySummary = $summary ?: [
    'pegawai' => '—',
    'tanggal' => date('Y-m-d'),
    'jam_mulai' => '—',
    'jam_selesai' => '—',
    'hari' => 'Hari Kerja',
    'jenis_spl' => 'SPL Jam Lembur',
    'catatan' => '',
    'lembur_di_luar_jam_kerja' => 0,
    'uang_makan' => 0,
];
?>

<style>
.lembur-card {
    background:#fff; border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,.07);
    overflow:hidden; border:1px solid #e8edf2;
}
.lembur-card .card-header {
    padding:.85rem 1.5rem !important;
    border-bottom:1px solid #e8edf2 !important;
}
.lembur-card .card-body { padding:1.5rem !important; }
.lembur-form .form-label {
    color:#475569; font-size:.72rem; margin-bottom:.35rem;
    text-transform:uppercase; letter-spacing:.03em;
}
.lembur-form .form-control,
.lembur-form .form-select {
    min-height:38px; border-color:#d8e1ec; border-radius:7px;
    color:#334155; font-size:.82rem;
}
.lembur-form textarea.form-control { min-height:74px; }
.lembur-form .form-control:focus,
.lembur-form .form-select:focus {
    border-color:#93c5fd; box-shadow:0 0 0 3px rgba(59,130,246,.1);
}
.lembur-rule {
    background:#fffbeb; border:1px solid #fcd34d; border-radius:7px;
    color:#92400e; font-size:.68rem; line-height:1.45; padding:.6rem .75rem;
}
.lembur-summary .card-body { padding:1rem !important; }
.lembur-summary-list { margin:0; }
.lembur-summary-item {
    display:flex; justify-content:space-between; align-items:center;
    gap:1rem; padding:.45rem 0; border-bottom:1px solid #eef2f7;
    font-size:.72rem;
}
.lembur-summary-item:last-child { border-bottom:0; }
.lembur-summary-item dt { color:#94a3b8; font-weight:400; }
.lembur-summary-item dd { color:#1e293b; font-weight:600; margin:0; text-align:right; }
.lembur-metric { border-radius:10px; min-height:54px; }
.lembur-metric .card-body { padding:.7rem .85rem !important; }
.lembur-metric .metric-icon {
    width:28px; height:28px; display:inline-flex; align-items:center;
    justify-content:center; border-radius:8px; margin-right:.5rem;
}
.lembur-metric .metric-label { color:#94a3b8; font-size:.65rem; }
.lembur-metric .metric-value { color:#1e293b; font-size:.82rem; font-weight:700; }
.lembur-policy { font-size:.67rem; line-height:1.45; }
.lembur-card .table thead th {
    background:#f1f5f9; color:#475569;
    font-size:.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em;
    padding:.8rem 1.2rem; border:none;
    border-bottom:2px solid #e2e8f0;
}
.lembur-card .table tbody td {
    padding:.85rem 1.2rem; border-color:#f1f5f9;
    vertical-align:middle; font-size:.87rem; color:#334155;
}
.lembur-card .table tbody tr:last-child td { border-bottom:none; }
.lembur-card .table tbody tr:hover { background:#f8fafc; }

@media (max-width: 767.98px) {
    .lembur-card .card-header { padding:.85rem 1rem !important; }
    .lembur-card .card-body { padding:1rem !important; }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="h3 text-gray-800 m-0" style="font-size:1.35rem;font-weight:700;">Register Lembur</h2>
        <small class="text-muted" style="font-size:.75rem;">Sistem perhitungan lembur, SPL, dan uang makan</small>
    </div>
    <span class="text-muted"><i class="bi bi-calendar-day"></i> <?php echo date('d F Y'); ?></span>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card lembur-card h-100">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h5 class="m-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Form Lembur</h5>
            </div>
            <div class="card-body p-4 lembur-form">
                <form method="POST" action="?page=register_lembur">
                    <div class="mb-3">
                        <label for="pegawai" class="form-label fw-semibold">Nama Pegawai</label>
                        <select class="form-select" id="pegawai" name="pegawai" required>
                            <option value="">-- Pilih pegawai --</option>
                            <?php foreach ($pegawaiList as $pegawai): ?>
                                <option value="<?php echo htmlspecialchars($pegawai); ?>" <?php echo ($summary['pegawai'] ?? '') === $pegawai ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pegawai); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tanggal" class="form-label fw-semibold">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($summary['tanggal'] ?? date('Y-m-d')); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="hari" class="form-label fw-semibold">Hari</label>
                            <select class="form-select" id="hari" name="hari" required>
                                <option value="Hari Kerja" <?php echo (($summary['hari'] ?? 'Hari Kerja') === 'Hari Kerja') ? 'selected' : ''; ?>>Hari Kerja</option>
                                <option value="Sabtu" <?php echo (($summary['hari'] ?? 'Hari Kerja') === 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                                <option value="Minggu" <?php echo (($summary['hari'] ?? 'Hari Kerja') === 'Minggu') ? 'selected' : ''; ?>>Minggu</option>
                                <option value="Hari Libur Nasional" <?php echo (($summary['hari'] ?? 'Hari Kerja') === 'Hari Libur Nasional') ? 'selected' : ''; ?>>Hari Libur Nasional</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="jam_mulai" class="form-label fw-semibold">Jam Mulai</label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" value="<?php echo htmlspecialchars($summary['jam_mulai'] ?? '17:00'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="jam_selesai" class="form-label fw-semibold">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" value="<?php echo htmlspecialchars($summary['jam_selesai'] ?? '20:00'); ?>" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="jenis_spl" class="form-label fw-semibold">Jenis SPL</label>
                        <select class="form-select" id="jenis_spl" name="jenis_spl" required>
                            <option value="SPL Jam Lembur" <?php echo (($summary['jenis_spl'] ?? 'SPL Jam Lembur') === 'SPL Jam Lembur') ? 'selected' : ''; ?>>SPL Jam Lembur</option>
                            <option value="SPL Hari Libur" <?php echo (($summary['jenis_spl'] ?? 'SPL Jam Lembur') === 'SPL Hari Libur') ? 'selected' : ''; ?>>SPL Hari Libur</option>
                            <option value="SPL Hari Libur + Jam Lembur" <?php echo (($summary['jenis_spl'] ?? 'SPL Jam Lembur') === 'SPL Hari Libur + Jam Lembur') ? 'selected' : ''; ?>>SPL Hari Libur + Jam Lembur</option>
                        </select>
                    </div>

                    <div class="mt-3">
                        <label for="catatan" class="form-label fw-semibold">Catatan / Keterangan</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Contoh: pengecekan stok, maintenance server, penutupan gudang"><?php echo htmlspecialchars($summary['catatan'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 gap-3 flex-wrap">
                        <span class="lembur-rule grow"><i class="bi bi-info-circle me-1"></i>Aturan Perhitungan: Lembur dihitung setelah pukul 17.00. Uang makan sebesar Rp 15.000 diberikan apabila jam selesai melewati pukul 20.00.</span>
                        <button type="submit" class="btn btn-warning text-white fw-semibold px-4">
                            <i class="bi bi-save me-2"></i> Simpan Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card lembur-card lembur-summary mb-3">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h5 class="m-0 fw-bold text-warning"><i class="bi bi-calculator me-2"></i>Ringkasan Perhitungan</h5>
            </div>
            <div class="card-body p-4">
                <dl class="lembur-summary-list">
                    <div class="lembur-summary-item">
                        <dt>Pegawai</dt>
                        <dd><?php echo htmlspecialchars($displaySummary['pegawai']); ?></dd>
                    </div>
                    <div class="lembur-summary-item">
                        <dt>Tanggal</dt>
                        <dd><?php echo date('d F Y', strtotime($displaySummary['tanggal'])); ?></dd>
                    </div>
                    <div class="lembur-summary-item">
                        <dt>Jam Kerja</dt>
                        <dd><?php echo htmlspecialchars($displaySummary['jam_mulai']); ?><?php echo $displaySummary['jam_selesai'] !== '—' ? ' - ' . htmlspecialchars($displaySummary['jam_selesai']) : ''; ?></dd>
                    </div>
                    <div class="lembur-summary-item">
                        <dt>Jenis SPL</dt>
                        <dd><?php echo htmlspecialchars($displaySummary['jenis_spl']); ?></dd>
                    </div>
                </dl>

                    <div class="mt-3 row g-2">
                        <div class="col-md-4">
                            <div class="card lembur-metric border-0 bg-warning bg-opacity-10 h-100">
                                <div class="card-body d-flex align-items-center">
                                    <span class="metric-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-clock"></i></span>
                                    <div><div class="metric-label">Total Lembur</div><div class="metric-value"><?php echo formatJam($displaySummary['lembur_di_luar_jam_kerja']); ?></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card lembur-metric border-0 bg-primary bg-opacity-10 h-100">
                                <div class="card-body d-flex align-items-center">
                                    <span class="metric-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-calendar3"></i></span>
                                    <div><div class="metric-label">Hari</div><div class="metric-value"><?php echo htmlspecialchars($displaySummary['hari']); ?></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card lembur-metric border-0 bg-light h-100">
                                <div class="card-body d-flex align-items-center">
                                    <span class="metric-icon bg-secondary bg-opacity-10 text-dark"><i class="bi bi-currency-dollar"></i></span>
                                    <div><div class="metric-label">Uang Makan</div><div class="metric-value"><?php echo $displaySummary['uang_makan'] > 0 ? 'Rp ' . number_format($displaySummary['uang_makan'], 0, ',', '.') : 'Rp 0'; ?></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info lembur-policy mt-3 mb-0 border-0">
                        <strong><i class="bi bi-info-circle-fill me-1"></i> Kebijakan Uang Makan</strong><br>
                        <?php if ($displaySummary['uang_makan'] > 0): ?>
                            Lembur melewati pukul 20.00, sehingga pegawai berhak mendapatkan uang makan sebesar <strong>Rp 15.000</strong>.
                        <?php else: ?>
                            Uang makan sebesar Rp 15.000 diberikan kepada pegawai yang menyelesaikan lembur melewati pukul 20.00.
                        <?php endif; ?>
                    </div>
            </div>
        </div>

        <div class="card lembur-card">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-table me-2"></i>Daftar Register Lembur</h5>
                <span class="badge bg-warning text-dark"><?php echo count($pegawaiList); ?> Pegawai</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>SPL</th>
                                <th>Uang Makan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Andi Wijaya</td>
                                <td>Sabtu</td>
                                <td>17.30 - 21.15</td>
                                <td>SPL Hari Libur + Jam Lembur</td>
                                <td>Rp 15.000</td>
                            </tr>
                            <tr>
                                <td>Budi Santoso</td>
                                <td>Hari Kerja</td>
                                <td>17.00 - 19.00</td>
                                <td>SPL Jam Lembur</td>
                                <td>Rp 0</td>
                            </tr>
                            <tr>
                                <td>Citra Lestari</td>
                                <td>Minggu</td>
                                <td>17.15 - 21.00</td>
                                <td>SPL Hari Libur</td>
                                <td>Rp 15.000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
