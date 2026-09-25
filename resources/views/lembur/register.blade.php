@extends('layouts.app')

@section('content')
<style>
.lembur-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    overflow: hidden;
    border: 1px solid #e8edf2;
}
.lembur-card .card-header {
    padding: .9rem 1.5rem !important;
    border-bottom: 1px solid #e8edf2 !important;
}
.lembur-card .card-body { padding: 1.5rem !important; }
.lembur-form .form-label {
    color: #475569;
    font-size: .75rem;
    margin-bottom: .35rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-weight: 600;
}
.lembur-form .form-control,
.lembur-form .form-select {
    min-height: 40px;
    border-color: #d8e1ec;
    border-radius: 8px;
    color: #1e293b;
    font-size: .85rem;
}
.lembur-form .input-group-text {
    background-color: #f8fafc;
    border-color: #d8e1ec;
    color: #64748b;
    font-size: .82rem;
    font-weight: 600;
}
.lembur-form textarea.form-control { min-height: 68px; }
.lembur-form .form-control:focus,
.lembur-form .form-select:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, .15);
}
.lembur-section-title {
    font-size: .88rem;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: .4rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.lembur-rule {
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: 8px;
    color: #92400e;
    font-size: .75rem;
    line-height: 1.5;
    padding: .65rem .85rem;
}
.lembur-summary .card-body { padding: 1.25rem !important; }
.lembur-summary-list { margin: 0; }
.lembur-summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: .45rem 0;
    border-bottom: 1px solid #eef2f7;
    font-size: .8rem;
}
.lembur-summary-item:last-child { border-bottom: 0; }
.lembur-summary-item dt { color: #64748b; font-weight: 500; }
.lembur-summary-item dd { color: #0f172a; font-weight: 600; margin: 0; text-align: right; }
.lembur-metric { border-radius: 10px; min-height: 58px; }
.lembur-metric .card-body { padding: .75rem .9rem !important; }
.lembur-metric .metric-icon {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin-right: .6rem;
    font-size: 1.1rem;
}
.lembur-metric .metric-label { color: #64748b; font-size: .68rem; font-weight: 500; }
.lembur-metric .metric-value { color: #0f172a; font-size: .92rem; font-weight: 700; }
.lembur-policy { font-size: .75rem; line-height: 1.5; border-radius: 8px; }

.lembur-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: .75rem 1rem;
    border-bottom: 2px solid #e2e8f0;
}
.lembur-table tbody td {
    padding: .75rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: .84rem;
    color: #334155;
}
.lembur-table tbody tr:hover { background: #f8fafc; }

.itemized-table {
    width: 100%;
    font-size: .78rem;
    border-collapse: collapse;
}
.itemized-table th {
    color: #64748b;
    font-size: .7rem;
    text-transform: uppercase;
    font-weight: 700;
    padding: .35rem .45rem;
    border-bottom: 1px solid #e2e8f0;
}
.itemized-table td {
    padding: .4rem .45rem;
    border-bottom: 1px dashed #eef2f7;
}

@media (max-width: 767.98px) {
    .lembur-card .card-header { padding: .85rem 1rem !important; }
    .lembur-card .card-body { padding: 1rem !important; }
}
</style>

<div class="container-fluid py-4">
    <!-- Header & Breadcrumbs -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="h3 text-gray-800 m-0 fw-bold" style="font-size:1.35rem;">Register Lembur & Surat Perintah Lembur (SPL)</h2>
            <small class="text-muted" style="font-size:.78rem;">Sistem kalkulasi rekapitulasi SPL: Hari Kerja, Lembur A (LA), Lembur B (LB), Luar Kota, dan Uang Makan</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                <i class="bi bi-building me-1"></i> PT. Usaha Bakti Perkasa
            </span>
            <span class="badge bg-white text-secondary border shadow-sm p-2">
                <i class="bi bi-calendar-event text-warning me-1"></i> {{ date('d F Y') }}
            </span>
        </div>
    </div>

    <!-- Notifikasi Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
            <div>{!! session('success') !!}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <!-- KOLOM KIRI: FORM REGISTER & PERHITUNGAN SPL -->
        <div class="col-xl-7 col-lg-7">
            <div class="card lembur-card h-100">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Form Register & Kalkulasi SPL</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1"><i class="bi bi-calculator me-1"></i> Logika SPL</span>
                </div>
                <div class="card-body p-4 lembur-form">
                    <form method="POST" action="{{ route('spl.calculate') }}" id="formLembur">
                        @csrf

                        <!-- SECTION 1: DATA KARYAWAN & PERIODE -->
                        <div class="lembur-section-title">
                            <i class="bi bi-person-badge text-primary"></i> Data Karyawan & Periode
                        </div>

                        <!-- Pilih Pegawai dari Master Data -->
                        <div class="mb-3">
                            <label for="pegawaiSelect" class="form-label">Pilih dari Master Pegawai</label>
                            <select class="form-select" id="pegawaiSelect" name="id_pegawai">
                                <option value="">-- Pilih Pegawai untuk Auto-Fill (Opsional) --</option>
                                @foreach ($pegawaiList as $peg)
                                    <option value="{{ $peg->ID_PEGAWAI }}" 
                                            data-nama="{{ $peg->NM_PEGAWAI }}"
                                            data-no="{{ $peg->ID_PEGAWAI_MESIN ?: ('KRY-' . str_pad($peg->ID_PEGAWAI, 3, '0', STR_PAD_LEFT)) }}"
                                            {{ old('id_pegawai') == $peg->ID_PEGAWAI ? 'selected' : '' }}>
                                        {{ $peg->NM_PEGAWAI }} ({{ $peg->ID_PEGAWAI_MESIN ?: ('KRY-' . str_pad($peg->ID_PEGAWAI, 3, '0', STR_PAD_LEFT)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Periode & Lokasi -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label for="periode" class="form-label">Periode Laporan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="periode" id="periode" value="{{ old('periode', '01 - 31 Agustus 2026') }}" placeholder="Contoh: 01 - 31 Agustus 2026" required>
                            </div>
                            <div class="col-md-5">
                                <label for="lokasi" class="form-label">Lokasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="lokasi" id="lokasi" value="{{ old('lokasi', 'KANTOR') }}" required>
                            </div>
                        </div>

                        <!-- Nama Karyawan & No Karyawan -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-7">
                                <label for="nama_karyawan" class="form-label">Nama Karyawan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_karyawan" id="nama_karyawan" value="{{ old('nama_karyawan', 'John Doe') }}" placeholder="Contoh: John Doe" required>
                            </div>
                            <div class="col-md-5">
                                <label for="no_karyawan" class="form-label">No. Karyawan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="no_karyawan" id="no_karyawan" value="{{ old('no_karyawan', 'KRY-001') }}" placeholder="Contoh: KRY-001" required>
                            </div>
                        </div>

                        <!-- SECTION 2: DETAIL LEMBUR & NOMINAL -->
                        <div class="lembur-section-title">
                            <i class="bi bi-clock-history text-warning"></i> Detail Hari Kerja & Lembur
                        </div>

                        <!-- Total Hari Kerja (J A M) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="hari_kerja_qty" class="form-label">Total Hari Kerja (J A M) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="hari_kerja_qty" id="hari_kerja_qty" value="{{ old('hari_kerja_qty', 19) }}" min="0" required>
                                    <span class="input-group-text">Jam</span>
                                </div>
                            </div>
                        </div>

                        <!-- Lembur A (LA) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="lembur_a_qty" class="form-label">Jumlah Pegawai Lembur A (LA) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="lembur_a_qty" id="lembur_a_qty" value="{{ old('lembur_a_qty', 2) }}" min="0" required>
                                    <span class="input-group-text">Org/Jam</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="lembur_a_rate" class="form-label">Nominal Lembur A <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="lembur_a_rate" id="lembur_a_rate" value="{{ old('lembur_a_rate', 29200) }}" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Lembur B (LB) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="lembur_b_qty" class="form-label">Jumlah Pegawai Lembur B (LB) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="lembur_b_qty" id="lembur_b_qty" value="{{ old('lembur_b_qty', 4) }}" min="0" required>
                                    <span class="input-group-text">Org/Jam</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="lembur_b_rate" class="form-label">Nominal Lembur B <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="lembur_b_rate" id="lembur_b_rate" value="{{ old('lembur_b_rate', 34400) }}" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Luar Kota -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="luar_kota_qty" class="form-label">Jumlah Pegawai Luar Kota <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="luar_kota_qty" id="luar_kota_qty" value="{{ old('luar_kota_qty', 0) }}" min="0" required>
                                    <span class="input-group-text">Org/Hari</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="luar_kota_rate" class="form-label">Nominal Luar Kota <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="luar_kota_rate" id="luar_kota_rate" value="{{ old('luar_kota_rate', 30000) }}" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: UANG MAKAN -->
                        <div class="lembur-section-title">
                            <i class="bi bi-cup-hot text-success"></i> Hak Uang Makan
                        </div>

                        <!-- Uang Makan Harian -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="uang_makan_qty" class="form-label">Jumlah Pegawai/Hari (Uang Makan) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="uang_makan_qty" id="uang_makan_qty" value="{{ old('uang_makan_qty', 19) }}" min="0" required>
                                    <span class="input-group-text">Hari</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="uang_makan_rate" class="form-label">Nominal Uang Makan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="uang_makan_rate" id="uang_makan_rate" value="{{ old('uang_makan_rate', 15000) }}" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Uang Makan Lembur -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="uang_makan_lembur_qty" class="form-label">Jumlah Pegawai (Uang Makan Lembur) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="uang_makan_lembur_qty" id="uang_makan_lembur_qty" value="{{ old('uang_makan_lembur_qty', 2) }}" min="0" required>
                                    <span class="input-group-text">Org</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="uang_makan_lembur_rate" class="form-label">Nominal Uang Makan Lembur <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="uang_makan_lembur_rate" id="uang_makan_lembur_rate" value="{{ old('uang_makan_lembur_rate', 15000) }}" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan / Keterangan -->
                        <div class="mb-4">
                            <label for="catatan" class="form-label">Catatan / Keterangan SPL (Opsional)</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Contoh: Pekerjaan maintenance genset, lembur deadline proyek baja...">{{ old('catatan') }}</textarea>
                        </div>

                        <!-- Banner Aturan Tarif Bisnis & Tombol Submit -->
                        <div class="lembur-rule mb-3">
                            <i class="bi bi-info-circle-fill me-1 text-warning"></i>
                            <strong>Aturan Tarif Perusahaan:</strong> Lembur A: <strong>Rp 29.200</strong>, Lembur B: <strong>Rp 34.400</strong>, Luar Kota: <strong>Rp 30.000</strong>, Uang Makan: <strong>Rp 15.000</strong>/hari.
                        </div>

                        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <button type="submit" formaction="{{ route('spl.calculate') }}" formtarget="_blank" class="btn btn-outline-primary fw-bold px-3 py-2 shadow-sm">
                                <i class="bi bi-printer me-2"></i> Preview Cetak Slip SPL
                            </button>
                            <button type="submit" formaction="{{ route('lembur.store') }}" class="btn btn-warning text-white fw-bold px-4 py-2 shadow-sm">
                                <i class="bi bi-save me-2"></i> Simpan Register Lembur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: RINGKASAN PERHITUNGAN SPL (LIVE SUMMARY) -->
        <div class="col-xl-5 col-lg-5">
            <div class="card lembur-card lembur-summary mb-3">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-warning"><i class="bi bi-calculator me-2"></i>Ringkasan Perhitungan SPL</h5>
                    <span class="badge bg-warning bg-opacity-25 text-dark font-monospace" style="font-size:.72rem;">Live Summary</span>
                </div>
                <div class="card-body p-4">
                    <dl class="lembur-summary-list">
                        <div class="lembur-summary-item">
                            <dt><i class="bi bi-person me-1"></i> Nama Karyawan</dt>
                            <dd id="summaryPegawai" class="text-primary">John Doe</dd>
                        </div>
                        <div class="lembur-summary-item">
                            <dt><i class="bi bi-upc-scan me-1"></i> No. Karyawan</dt>
                            <dd id="summaryNoKaryawan">KRY-001</dd>
                        </div>
                        <div class="lembur-summary-item">
                            <dt><i class="bi bi-calendar2-range me-1"></i> Periode</dt>
                            <dd id="summaryPeriode">01 - 31 Agustus 2026</dd>
                        </div>
                        <div class="lembur-summary-item">
                            <dt><i class="bi bi-geo-alt me-1"></i> Lokasi</dt>
                            <dd id="summaryLokasi">KANTOR</dd>
                        </div>
                        <div class="lembur-summary-item">
                            <dt><i class="bi bi-hourglass-split me-1"></i> Total Hari Kerja</dt>
                            <dd id="summaryHariKerja">19 Jam</dd>
                        </div>
                    </dl>

                    <!-- Metric Cards: Lembur A, Lembur B, Uang Makan -->
                    <div class="mt-3 row g-2">
                        <div class="col-4">
                            <div class="card lembur-metric border-0 bg-primary bg-opacity-10 h-100 shadow-none">
                                <div class="card-body d-flex align-items-center">
                                    <span class="metric-icon bg-primary bg-opacity-25 text-primary"><i class="bi bi-clock-history"></i></span>
                                    <div>
                                        <div class="metric-label">Lembur A</div>
                                        <div class="metric-value text-primary" id="summaryMetricLA">Rp 58.400</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card lembur-metric border-0 bg-warning bg-opacity-10 h-100 shadow-none">
                                <div class="card-body d-flex align-items-center">
                                    <span class="metric-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-stopwatch"></i></span>
                                    <div>
                                        <div class="metric-label">Lembur B</div>
                                        <div class="metric-value text-warning" id="summaryMetricLB">Rp 137.600</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card lembur-metric border-0 bg-info bg-opacity-10 h-100 shadow-none">
                                <div class="card-body d-flex align-items-center">
                                    <span class="metric-icon bg-info bg-opacity-25 text-info"><i class="bi bi-cup-hot"></i></span>
                                    <div>
                                        <div class="metric-label">Uang Makan</div>
                                        <div class="metric-value text-info" id="summaryMetricUM">Rp 315.000</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight Grand Total IDR Card -->
                    <div class="card border-0 bg-success bg-opacity-10 rounded-3 mt-3 shadow-none p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success text-white text-uppercase px-2 py-1 mb-1" style="font-size:.68rem;">Total SPL Rekapitulasi</span>
                                <div class="text-muted small fw-semibold">JUMLAH KESELURUHAN (IDR)</div>
                            </div>
                            <div class="text-end">
                                <div class="fs-4 fw-bold text-success" id="summaryGrandTotal">Rp 511.000</div>
                                <small class="text-muted" style="font-size:.7rem;">Sesuai Slip Rekap</small>
                            </div>
                        </div>
                    </div>

                    <!-- Itemized Breakdown Table -->
                    <div class="mt-3 border rounded-3 p-2 bg-light">
                        <div class="d-flex justify-content-between align-items-center px-1 mb-1">
                            <span class="fw-bold text-dark" style="font-size: .75rem;"><i class="bi bi-list-check me-1"></i>Rincian Komponen SPL</span>
                            <span class="badge bg-white text-dark border font-monospace" style="font-size: .68rem;">Formula Otomatis</span>
                        </div>
                        <table class="itemized-table">
                            <thead>
                                <tr>
                                    <th>Kategori / Item</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Nominal</th>
                                    <th class="text-end">Jumlah IDR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Hari Kerja</strong></td>
                                    <td class="text-end font-monospace" id="tableQtyHK">19</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end font-monospace">Rp 0</td>
                                </tr>
                                <tr>
                                    <td>Lembur A (LA)</td>
                                    <td class="text-end font-monospace" id="tableQtyLA">2</td>
                                    <td class="text-end font-monospace" id="tableRateLA">Rp 29.200</td>
                                    <td class="text-end font-monospace text-primary fw-semibold" id="tableAmtLA">Rp 58.400</td>
                                </tr>
                                <tr>
                                    <td>Lembur B (LB)</td>
                                    <td class="text-end font-monospace" id="tableQtyLB">4</td>
                                    <td class="text-end font-monospace" id="tableRateLB">Rp 34.400</td>
                                    <td class="text-end font-monospace text-warning fw-semibold" id="tableAmtLB">Rp 137.600</td>
                                </tr>
                                <tr>
                                    <td>Luar Kota</td>
                                    <td class="text-end font-monospace" id="tableQtyLK">0</td>
                                    <td class="text-end font-monospace" id="tableRateLK">Rp 30.000</td>
                                    <td class="text-end font-monospace" id="tableAmtLK">Rp 0</td>
                                </tr>
                                <tr>
                                    <td>Uang Makan/</td>
                                    <td class="text-end font-monospace" id="tableQtyUM">19</td>
                                    <td class="text-end font-monospace" id="tableRateUM">Rp 15.000</td>
                                    <td class="text-end font-monospace text-success fw-semibold" id="tableAmtUM">Rp 285.000</td>
                                </tr>
                                <tr>
                                    <td>Uang Makan Lembur</td>
                                    <td class="text-end font-monospace" id="tableQtyUML">2</td>
                                    <td class="text-end font-monospace" id="tableRateUML">Rp 15.000</td>
                                    <td class="text-end font-monospace text-success fw-semibold" id="tableAmtUML">Rp 30.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Kebijakan SPL Box -->
                    <div class="alert alert-info lembur-policy mt-3 mb-0 border-0 d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-2 fs-6 text-info mt-1"></i>
                        <div>
                            <strong>Informasi Rekapitulasi SPL:</strong><br>
                            Kalkulasi ini terhubung dengan template cetak <strong>Slip Rekapitulasi Lembur PT. Usaha Bakti Perkasa</strong>. Anda dapat mengklik tombol <em>Preview Cetak Slip SPL</em> untuk memeriksa format dokumen sebelum disimpan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL DAFTAR REGISTER LEMBUR TERSIMPAN -->
    <div class="card lembur-card mt-4">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-table me-2 text-primary"></i>Daftar Register Lembur</h5>
                <small class="text-muted">Data transaksi tersimpan pada tabel <code>t_register_lembur</code></small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary px-3 py-2"><i class="bi bi-database me-1"></i> Total {{ count($daftarLembur) }} Data</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle lembur-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Pegawai</th>
                            <th>Tanggal & Hari</th>
                            <th>Jam Kerja / Durasi</th>
                            <th>Jenis SPL</th>
                            <th>Uang Makan</th>
                            <th>Catatan / Rincian</th>
                            <th class="text-center" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarLembur as $idx => $row)
                            <tr class="{{ $targetId == $row->id ? 'table-warning' : '' }}">
                                <td class="text-muted fw-bold">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row->nama_pegawai ?: ($row->pegawai_master ?? '—') }}</div>
                                    <small class="text-muted">ID: #{{ $row->id }}</small>
                                </td>
                                <td>
                                    <div>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</div>
                                    <span class="badge bg-light text-dark border">{{ $row->hari }}</span>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge bg-warning bg-opacity-25 text-dark fw-bold">
                                            <i class="bi bi-clock me-1"></i>{{ number_format($row->durasi_lembur, 1) }} jam
                                        </span>
                                    </div>
                                    <small class="text-muted font-monospace">{{ substr($row->jam_mulai, 0, 5) }} - {{ substr($row->jam_selesai, 0, 5) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                        {{ $row->jenis_spl }}
                                    </span>
                                </td>
                                <td>
                                    @if ($row->uang_makan > 0)
                                        <span class="badge bg-success">Rp {{ number_format($row->uang_makan, 0, ',', '.') }}</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-50">Rp 0</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted text-break">{{ $row->catatan ?: '—' }}</small>
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('lembur.destroy', $row->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data lembur ini?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus Data">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-1 opacity-50"></i>
                                    Belum ada data transaksi register lembur. Silakan isi form di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pegawaiSelect = document.getElementById('pegawaiSelect');
    const namaKaryawanInput = document.getElementById('nama_karyawan');
    const noKaryawanInput = document.getElementById('no_karyawan');
    const periodeInput = document.getElementById('periode');
    const lokasiInput = document.getElementById('lokasi');

    const hariKerjaQty = document.getElementById('hari_kerja_qty');
    const lemburAQty = document.getElementById('lembur_a_qty');
    const lemburARate = document.getElementById('lembur_a_rate');
    const lemburBQty = document.getElementById('lembur_b_qty');
    const lemburBRate = document.getElementById('lembur_b_rate');
    const luarKotaQty = document.getElementById('luar_kota_qty');
    const luarKotaRate = document.getElementById('luar_kota_rate');
    const uangMakanQty = document.getElementById('uang_makan_qty');
    const uangMakanRate = document.getElementById('uang_makan_rate');
    const uangMakanLemburQty = document.getElementById('uang_makan_lembur_qty');
    const uangMakanLemburRate = document.getElementById('uang_makan_lembur_rate');

    // Summary Elements
    const summaryPegawai = document.getElementById('summaryPegawai');
    const summaryNoKaryawan = document.getElementById('summaryNoKaryawan');
    const summaryPeriode = document.getElementById('summaryPeriode');
    const summaryLokasi = document.getElementById('summaryLokasi');
    const summaryHariKerja = document.getElementById('summaryHariKerja');

    const summaryMetricLA = document.getElementById('summaryMetricLA');
    const summaryMetricLB = document.getElementById('summaryMetricLB');
    const summaryMetricUM = document.getElementById('summaryMetricUM');
    const summaryGrandTotal = document.getElementById('summaryGrandTotal');

    // Table itemized elements
    const tableQtyHK = document.getElementById('tableQtyHK');
    const tableQtyLA = document.getElementById('tableQtyLA');
    const tableRateLA = document.getElementById('tableRateLA');
    const tableAmtLA = document.getElementById('tableAmtLA');

    const tableQtyLB = document.getElementById('tableQtyLB');
    const tableRateLB = document.getElementById('tableRateLB');
    const tableAmtLB = document.getElementById('tableAmtLB');

    const tableQtyLK = document.getElementById('tableQtyLK');
    const tableRateLK = document.getElementById('tableRateLK');
    const tableAmtLK = document.getElementById('tableAmtLK');

    const tableQtyUM = document.getElementById('tableQtyUM');
    const tableRateUM = document.getElementById('tableRateUM');
    const tableAmtUM = document.getElementById('tableAmtUM');

    const tableQtyUML = document.getElementById('tableQtyUML');
    const tableRateUML = document.getElementById('tableRateUML');
    const tableAmtUML = document.getElementById('tableAmtUML');

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num || 0).toLocaleString('id-ID');
    }

    function calculateLive() {
        const nama = namaKaryawanInput.value || 'John Doe';
        const noKaryawan = noKaryawanInput.value || 'KRY-001';
        const periode = periodeInput.value || '01 - 31 Agustus 2026';
        const lokasi = lokasiInput.value || 'KANTOR';

        summaryPegawai.textContent = nama;
        summaryNoKaryawan.textContent = noKaryawan;
        summaryPeriode.textContent = periode;
        summaryLokasi.textContent = lokasi;

        const hkQty = parseFloat(hariKerjaQty.value) || 0;
        summaryHariKerja.textContent = hkQty + ' Jam';
        tableQtyHK.textContent = hkQty;

        // Lembur A
        const laQty = parseFloat(lemburAQty.value) || 0;
        const laRate = parseFloat(lemburARate.value) || 0;
        const laAmt = laQty * laRate;
        tableQtyLA.textContent = laQty;
        tableRateLA.textContent = formatRupiah(laRate);
        tableAmtLA.textContent = formatRupiah(laAmt);
        summaryMetricLA.textContent = formatRupiah(laAmt);

        // Lembur B
        const lbQty = parseFloat(lemburBQty.value) || 0;
        const lbRate = parseFloat(lemburBRate.value) || 0;
        const lbAmt = lbQty * lbRate;
        tableQtyLB.textContent = lbQty;
        tableRateLB.textContent = formatRupiah(lbRate);
        tableAmtLB.textContent = formatRupiah(lbAmt);
        summaryMetricLB.textContent = formatRupiah(lbAmt);

        // Luar Kota
        const lkQty = parseFloat(luarKotaQty.value) || 0;
        const lkRate = parseFloat(luarKotaRate.value) || 0;
        const lkAmt = lkQty * lkRate;
        tableQtyLK.textContent = lkQty;
        tableRateLK.textContent = formatRupiah(lkRate);
        tableAmtLK.textContent = formatRupiah(lkAmt);

        // Uang Makan Harian
        const umQty = parseFloat(uangMakanQty.value) || 0;
        const umRate = parseFloat(uangMakanRate.value) || 0;
        const umAmt = umQty * umRate;
        tableQtyUM.textContent = umQty;
        tableRateUM.textContent = formatRupiah(umRate);
        tableAmtUM.textContent = formatRupiah(umAmt);

        // Uang Makan Lembur
        const umlQty = parseFloat(uangMakanLemburQty.value) || 0;
        const umlRate = parseFloat(uangMakanLemburRate.value) || 0;
        const umlAmt = umlQty * umlRate;
        tableQtyUML.textContent = umlQty;
        tableRateUML.textContent = formatRupiah(umlRate);
        tableAmtUML.textContent = formatRupiah(umlAmt);

        // Total Uang Makan
        const totalUM = umAmt + umlAmt;
        summaryMetricUM.textContent = formatRupiah(totalUM);

        // Grand Total IDR
        const grandTotal = laAmt + lbAmt + lkAmt + totalUM;
        summaryGrandTotal.textContent = formatRupiah(grandTotal);
    }

    // Auto-fill dari master pegawai
    pegawaiSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            const nama = opt.getAttribute('data-nama');
            const no = opt.getAttribute('data-no');
            if (nama) namaKaryawanInput.value = nama;
            if (no) noKaryawanInput.value = no;
        }
        calculateLive();
    });

    const formInputs = [
        namaKaryawanInput, noKaryawanInput, periodeInput, lokasiInput,
        hariKerjaQty, lemburAQty, lemburARate, lemburBQty, lemburBRate,
        luarKotaQty, luarKotaRate, uangMakanQty, uangMakanRate,
        uangMakanLemburQty, uangMakanLemburRate
    ];

    formInputs.forEach(input => {
        if (input) {
            input.addEventListener('input', calculateLive);
            input.addEventListener('change', calculateLive);
        }
    });

    calculateLive();
});
</script>
@endsection
