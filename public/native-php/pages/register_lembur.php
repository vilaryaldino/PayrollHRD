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
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 text-gray-800 m-0">Register Lembur</h2>
        <small class="text-muted">Sistem perhitungan lembur, SPL, dan uang makan</small>
    </div>
    <span class="text-muted"><i class="bi bi-calendar-day"></i> <?php echo date('d F Y'); ?></span>
</div>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h5 class="m-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Form Lembur</h5>
            </div>
            <div class="card-body p-4">
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
                        <span class="text-muted small">Aturan: lembur dihitung setelah pukul 17.00 dan uang makan diberikan jika melewati pukul 20.00</span>
                        <button type="submit" class="btn btn-warning text-white fw-semibold px-4">
                            <i class="bi bi-save me-2"></i> Simpan Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h5 class="m-0 fw-bold text-warning"><i class="bi bi-calculator me-2"></i>Ringkasan Perhitungan</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($submitted && $summary): ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light">
                                <div class="text-muted small">Pegawai</div>
                                <div class="fw-bold fs-6"><?php echo htmlspecialchars($summary['pegawai']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light">
                                <div class="text-muted small">Tanggal</div>
                                <div class="fw-bold fs-6"><?php echo date('d F Y', strtotime($summary['tanggal'])); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light">
                                <div class="text-muted small">Jam Kerja</div>
                                <div class="fw-bold fs-6"><?php echo htmlspecialchars($summary['jam_mulai']); ?> - <?php echo htmlspecialchars($summary['jam_selesai']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light">
                                <div class="text-muted small">Jenis SPL</div>
                                <div class="fw-bold fs-6"><?php echo htmlspecialchars($summary['jenis_spl']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 row g-3">
                        <div class="col-md-4">
                            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                                <div class="card-body">
                                    <div class="text-primary small fw-semibold">Total Lembur</div>
                                    <div class="fs-4 fw-bold text-primary">
                                        <?php echo formatJam($summary['lembur_di_luar_jam_kerja']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-success bg-opacity-10 h-100">
                                <div class="card-body">
                                    <div class="text-success small fw-semibold">Hari</div>
                                    <div class="fs-4 fw-bold text-success"><?php echo htmlspecialchars($summary['hari']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                                <div class="card-body">
                                    <div class="text-warning small fw-semibold">Uang Makan</div>
                                    <div class="fs-4 fw-bold text-warning">
                                        <?php echo $summary['uang_makan'] > 0 ? 'Rp ' . number_format($summary['uang_makan'], 0, ',', '.') : 'Rp 0'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0 border-0 shadow-sm">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <?php if ($summary['uang_makan'] > 0): ?>
                            Lembur melewati pukul 20.00, sehingga pegawai berhak mendapatkan uang makan sebesar <strong>Rp 15.000</strong>.
                        <?php else: ?>
                            Lembur belum melewati pukul 20.00, maka tidak mendapatkan uang makan tambahan.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-0">
                        <i class="bi bi-hourglass-split me-2"></i> Isi form di sebelah kiri untuk menghitung lembur, SPL, dan uang makan.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
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
