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
}
.lembur-form .form-control,
.lembur-form .form-select {
    min-height: 40px;
    border-color: #d8e1ec;
    border-radius: 8px;
    color: #1e293b;
    font-size: .85rem;
}
.lembur-form textarea.form-control { min-height: 76px; }
.lembur-form .form-control:focus,
.lembur-form .form-select:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, .15);
}
.lembur-rule {
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: 8px;
    color: #92400e;
    font-size: .72rem;
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
    padding: .5rem 0;
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
.lembur-metric .metric-value { color: #0f172a; font-size: .9rem; font-weight: 700; }
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

@media (max-width: 767.98px) {
    .lembur-card .card-header { padding: .85rem 1rem !important; }
    .lembur-card .card-body { padding: 1rem !important; }
}
</style>

<div class="container-fluid py-4">
    <!-- Header & Breadcrumbs -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="h3 text-gray-800 m-0 fw-bold" style="font-size:1.35rem;">Register Lembur</h2>
            <small class="text-muted" style="font-size:.78rem;">Sistem validasi jam kerja normal, perhitungan durasi lembur, SPL, dan hak uang makan</small>
        </div>
        <span class="badge bg-white text-secondary border shadow-sm p-2"><i class="bi bi-calendar-event text-warning me-1"></i> {{ date('d F Y') }}</span>
    </div>

    <!-- Notifikasi Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
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

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <!-- KOLOM KIRI: FORM REGISTER LEMBUR -->
        <div class="col-xl-7 col-lg-7">
            <div class="card lembur-card h-100">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Form Register Lembur</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1"><i class="bi bi-shield-check"></i> Form</span>
                </div>
                <div class="card-body p-4 lembur-form">
                    <form method="POST" action="{{ route('lembur.store') }}" id="formLembur">
                        @csrf
                        <input type="hidden" name="nama_pegawai" id="inputNamaPegawai" value="{{ old('nama_pegawai') }}">

                        <!-- Pilih Pegawai -->
                        <div class="mb-3">
                            <label for="pegawai" class="form-label fw-semibold">Nama Pegawai <span class="text-danger">*</span></label>
                            <select class="form-select" id="pegawai" name="id_pegawai" required>
                                <option value="">-- Pilih Pegawai Aktif --</option>
                                @foreach ($pegawaiList as $peg)
                                    <option value="{{ $peg->ID_PEGAWAI }}" 
                                            data-nama="{{ $peg->NM_PEGAWAI }}"
                                            {{ old('id_pegawai') == $peg->ID_PEGAWAI ? 'selected' : '' }}>
                                        {{ $peg->NM_PEGAWAI }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal & Hari -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tanggal" class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="hari" class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                                <select class="form-select" id="hari" name="hari" required>
                                    <option value="Hari Kerja" {{ old('hari') === 'Hari Kerja' ? 'selected' : '' }}>Hari Kerja</option>
                                    <option value="Sabtu" {{ old('hari') === 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                                    <option value="Minggu" {{ old('hari') === 'Minggu' ? 'selected' : '' }}>Minggu</option>
                                    <option value="Hari Libur Nasional" {{ old('hari') === 'Hari Libur Nasional' ? 'selected' : '' }}>Hari Libur Nasional</option>
                                </select>
                            </div>
                        </div>

                        <!-- Jam Mulai & Jam Selesai -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="jam_mulai" class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai', '17:00') }}" required>
                                <small class="text-muted" style="font-size:.7rem;">*Jika < 17:00, perhitungan lembur dimulai pukul 17:00</small>
                            </div>
                            <div class="col-md-6">
                                <label for="jam_selesai" class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai', '20:00') }}" required>
                                <small class="text-muted" style="font-size:.7rem;">*Wajib lebih besar dari jam mulai</small>
                            </div>
                        </div>

                        <!-- Jenis SPL -->
                        <div class="mt-3">
                            <label for="jenis_spl" class="form-label fw-semibold">Jenis SPL <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_spl" name="jenis_spl" required>
                                <option value="SPL Jam Lembur" {{ old('jenis_spl') === 'SPL Jam Lembur' ? 'selected' : '' }}>SPL Jam Lembur</option>
                                <option value="SPL Hari Libur" {{ old('jenis_spl') === 'SPL Hari Libur' ? 'selected' : '' }}>SPL Hari Libur</option>
                                <option value="SPL Hari Libur + Jam Lembur" {{ old('jenis_spl') === 'SPL Hari Libur + Jam Lembur' ? 'selected' : '' }}>SPL Hari Libur + Jam Lembur</option>
                            </select>
                        </div>

                        <!-- Catatan -->
                        <div class="mt-3">
                            <label for="catatan" class="form-label fw-semibold">Catatan / Keterangan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Contoh: Pengecekan genset pabrik, closing rekap bulanan, support maintenance...">{{ old('catatan') }}</textarea>
                        </div>

                        <!-- Banner Aturan Bisnis & Tombol Submit -->
                        <div class="d-flex justify-content-between align-items-center mt-4 gap-3 flex-wrap">
                            <div class="lembur-rule flex-grow-1">
                                <i class="bi bi-info-circle-fill me-1 text-warning"></i>
                                <strong>Aturan Bisnis:</strong> Jam normal berakhir <strong>17.00</strong>. Lembur dihitung hanya setelah 17.00. Melewati <strong>20.00</strong> otomatis dapat uang makan <strong>Rp 15.000</strong>.
                            </div>
                            <button type="submit" class="btn btn-warning text-white fw-bold px-4 py-2 shadow-sm">
                                <i class="bi bi-save me-2"></i> Simpan Register
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: RINGKASAN PERHITUNGAN -->
        <div class="col-xl-5 col-lg-5">
            <div class="card lembur-card lembur-summary mb-3">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-warning"><i class="bi bi-calculator me-2"></i>Ringkasan Perhitungan</h5>
                    <span class="badge bg-warning bg-opacity-25 text-dark font-monospace" style="font-size:.72rem;">Live Summary</span>
                </div>
                <div class="card-body p-4">
                    <dl class="lembur-summary-list">
                        <div class="lembur-summary-item">
                            <dt><i class="bi bi-person me-1"></i> Pegawai</dt>
                            <dd id="summaryPegawai" class="text-primary">{{ $summaryData['pegawai'] }}</dd>
                        </div>
                        <div class="lembur-summary-item">
                            <dt><i class="bi bi-calendar2-check me-1"></i> Tanggal</dt>
                            <dd id="summaryTanggal">{{ \Carbon\Carbon::parse($summaryData['tanggal'])->translatedFormat('d F Y') }}</dd>
                        </div>
                        <div class="lembur-summary-item">
                            <dt><i class="bi bi-clock-history me-1"></i> Jam Lembur</dt>
                            <dd id="summaryJam">{{ $summaryData['jam_mulai'] }}{{ $summaryData['jam_selesai'] !== '—' ? ' - ' . $summaryData['jam_selesai'] : '' }}</dd>
                        </div>
                        <div class="lembur-summary-item">
                            <dt><i class="bi bi-file-earmark-ruled me-1"></i> Jenis SPL</dt>
                            <dd id="summarySPL">{{ $summaryData['jenis_spl'] }}</dd>
                        </div>
                    </dl>

                    @php
                        function formatJamLembur($value) {
                            if ($value === null || $value === '' || (float)$value <= 0) {
                                return '0 jam';
                            }
                            $totalMenit = (int)round((float)$value * 60);
                            $jam = intdiv($totalMenit, 60);
                            $menit = $totalMenit % 60;
                            if ($jam > 0 && $menit > 0) return "{$jam} jam {$menit} menit";
                            elseif ($jam > 0) return "{$jam} jam";
                            else return "{$menit} menit";
                        }
                    @endphp

                    <!-- Metric Cards: Durasi, Hari, Uang Makan -->
                    <div class="mt-3 row g-2">
                        <div class="col-4">
                            <div class="card lembur-metric border-0 bg-warning bg-opacity-10 h-100 shadow-none">
                                <div class="card-body d-flex align-items-center">
                                    <span class="metric-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-stopwatch"></i></span>
                                    <div>
                                        <div class="metric-label">Durasi Lembur</div>
                                        <div class="metric-value text-warning" id="summaryDurasi">{{ formatJamLembur($summaryData['durasi_lembur']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card lembur-metric border-0 bg-primary bg-opacity-10 h-100 shadow-none">
                                <div class="card-body d-flex align-items-center">
                                    <span class="metric-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-calendar3"></i></span>
                                    <div>
                                        <div class="metric-label">Hari</div>
                                        <div class="metric-value text-primary" id="summaryHari">{{ $summaryData['hari'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card lembur-metric border-0 bg-success bg-opacity-10 h-100 shadow-none">
                                <div class="card-body d-flex align-items-center">
                                    <span class="metric-icon bg-success bg-opacity-25 text-success"><i class="bi bi-cash-stack"></i></span>
                                    <div>
                                        <div class="metric-label">Uang Makan</div>
                                        <div class="metric-value text-success" id="summaryUangMakan">
                                            {{ $summaryData['uang_makan'] > 0 ? 'Rp ' . number_format($summaryData['uang_makan'], 0, ',', '.') : 'Rp 0' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kebijakan Uang Makan Box -->
                    <div class="alert alert-info lembur-policy mt-3 mb-0 border-0 d-flex align-items-start" id="policyBox">
                        <i class="bi bi-info-circle-fill me-2 fs-6 text-info mt-1"></i>
                        <div>
                            <strong>Kebijakan Uang Makan Lembur:</strong><br>
                            <span id="policyText">
                                @if ($summaryData['uang_makan'] > 0)
                                    Jam selesai melewati pukul <strong>20.00</strong>, sehingga pegawai berhak mendapatkan uang makan sebesar <strong>Rp 15.000</strong>.
                                @else
                                    Uang makan sebesar <strong>Rp 15.000</strong> diberikan apabila jam selesai dinas lembur melewati pukul <strong>20.00</strong>.
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL DAFTAR REGISTER LEMBUR -->
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
                            <th>Jam Kerja</th>
                            <th>Durasi Lembur</th>
                            <th>Jenis SPL</th>
                            <th>Uang Makan</th>
                            <th>Catatan</th>
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
                                    <span class="font-monospace text-primary fw-semibold">
                                        {{ substr($row->jam_mulai, 0, 5) }} - {{ substr($row->jam_selesai, 0, 5) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-25 text-dark fw-bold">
                                        <i class="bi bi-clock me-1"></i>{{ formatJamLembur($row->durasi_lembur) }}
                                    </span>
                                    <small class="d-block text-muted" style="font-size: .7rem;">{{ number_format($row->durasi_lembur, 2) }} jam</small>
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
                                    <small class="text-muted">{{ $row->catatan ?: '—' }}</small>
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
                                <td colspan="9" class="text-center py-4 text-muted">
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
    const pegawaiSelect = document.getElementById('pegawai');
    const inputNamaPegawai = document.getElementById('inputNamaPegawai');
    const tanggalInput = document.getElementById('tanggal');
    const hariSelect = document.getElementById('hari');
    const jamMulaiInput = document.getElementById('jam_mulai');
    const jamSelesaiInput = document.getElementById('jam_selesai');
    const jenisSplSelect = document.getElementById('jenis_spl');
    
    const summaryPegawai = document.getElementById('summaryPegawai');
    const summaryTanggal = document.getElementById('summaryTanggal');
    const summaryJam = document.getElementById('summaryJam');
    const summarySPL = document.getElementById('summarySPL');
    const summaryDurasi = document.getElementById('summaryDurasi');
    const summaryHari = document.getElementById('summaryHari');
    const summaryUangMakan = document.getElementById('summaryUangMakan');
    const policyText = document.getElementById('policyText');

    function updatePreview() {
        const selectedOption = pegawaiSelect.options[pegawaiSelect.selectedIndex];
        const nama = selectedOption ? selectedOption.getAttribute('data-nama') || selectedOption.text : '—';
        if (selectedOption && selectedOption.value) {
            inputNamaPegawai.value = nama;
            summaryPegawai.textContent = nama;
        }

        const tglVal = tanggalInput.value;
        if (tglVal) {
            const dateObj = new Date(tglVal + 'T00:00:00');
            if (!isNaN(dateObj)) {
                const options = { day: '2-digit', month: 'long', year: 'numeric' };
                summaryTanggal.textContent = dateObj.toLocaleDateString('id-ID', options);
            }
        }

        const hariVal = hariSelect.value;
        summaryHari.textContent = hariVal;

        const splVal = jenisSplSelect.value;
        summarySPL.textContent = splVal;

        const jamMulai = jamMulaiInput.value || '17:00';
        const jamSelesai = jamSelesaiInput.value || '20:00';
        summaryJam.textContent = jamMulai + ' - ' + jamSelesai;

        const parseTime = (str) => {
            const parts = str.split(':');
            return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
        };

        const mulaiMins = parseTime(jamMulai);
        const selesaiMins = parseTime(jamSelesai);
        const batasNormalMins = 17 * 60;
        const batasUangMakanMins = 20 * 60;

        if (selesaiMins > mulaiMins) {
            const jamMulaiLembur = Math.max(mulaiMins, batasNormalMins);
            let durasiMins = 0;
            if (selesaiMins > jamMulaiLembur) {
                durasiMins = selesaiMins - jamMulaiLembur;
            }

            const jam = Math.floor(durasiMins / 60);
            const menit = durasiMins % 60;
            let formattedDurasi = '0 jam';
            if (jam > 0 && menit > 0) {
                formattedDurasi = jam + ' jam ' + menit + ' menit';
            } else if (jam > 0) {
                formattedDurasi = jam + ' jam';
            } else if (menit > 0) {
                formattedDurasi = menit + ' menit';
            }
            summaryDurasi.textContent = formattedDurasi;

            if (selesaiMins > batasUangMakanMins) {
                summaryUangMakan.textContent = 'Rp 15.000';
                policyText.innerHTML = 'Jam selesai melewati pukul <strong>20.00</strong>, sehingga pegawai berhak mendapatkan uang makan sebesar <strong>Rp 15.000</strong>.';
            } else {
                summaryUangMakan.textContent = 'Rp 0';
                policyText.innerHTML = 'Uang makan sebesar <strong>Rp 15.000</strong> diberikan apabila jam selesai dinas lembur melewati pukul <strong>20.00</strong>.';
            }
        } else {
            summaryDurasi.textContent = '0 jam';
            summaryUangMakan.textContent = 'Rp 0';
        }
    }

    tanggalInput.addEventListener('change', function () {
        const val = this.value;
        if (!val) return;
        const dateObj = new Date(val + 'T00:00:00');
        if (isNaN(dateObj)) return;
        const dayOfWeek = dateObj.getDay();
        if (dayOfWeek === 0) {
            hariSelect.value = 'Minggu';
        } else if (dayOfWeek === 6) {
            hariSelect.value = 'Sabtu';
        } else {
            if (hariSelect.value !== 'Hari Libur Nasional') {
                hariSelect.value = 'Hari Kerja';
            }
        }
        updatePreview();
    });

    document.getElementById('formLembur').addEventListener('submit', function (e) {
        const mulai = jamMulaiInput.value;
        const selesai = jamSelesaiInput.value;

        if (selesai <= mulai) {
            e.preventDefault();
            alert('Validasi Gagal: Jam selesai (' + selesai + ') tidak boleh lebih awal atau sama dengan jam mulai (' + mulai + ')!');
            jamSelesaiInput.focus();
            return false;
        }

        const selectedOption = pegawaiSelect.options[pegawaiSelect.selectedIndex];
        if (selectedOption) {
            inputNamaPegawai.value = selectedOption.getAttribute('data-nama') || selectedOption.text;
        }
    });

    pegawaiSelect.addEventListener('change', updatePreview);
    hariSelect.addEventListener('change', updatePreview);
    jamMulaiInput.addEventListener('input', updatePreview);
    jamSelesaiInput.addEventListener('input', updatePreview);
    jenisSplSelect.addEventListener('change', updatePreview);

    if (pegawaiSelect.value) {
        const opt = pegawaiSelect.options[pegawaiSelect.selectedIndex];
        if (opt) inputNamaPegawai.value = opt.getAttribute('data-nama') || opt.text;
    }
});
</script>
@endsection
