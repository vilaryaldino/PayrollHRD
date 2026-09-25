@extends('layouts.app')

@section('content')
@php
$namaBulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
    5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
    9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];

function fmtJam($desimal) {
    if ((float)$desimal <= 0) return '<span class="text-muted">0 jam</span>';
    $mnt  = (int)round((float)$desimal * 60);
    $j    = intdiv($mnt, 60);
    $m    = $mnt % 60;
    if ($j > 0 && $m > 0) return "{$j} jam {$m} mnt";
    if ($j > 0) return "{$j} jam";
    return "{$m} menit";
}
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 d-print-none">
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
        <form method="GET" action="{{ route('laporan.lembur') }}" class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-calendar-month me-1"></i>Periode Laporan</label>
                <div class="d-flex gap-2">
                    <select name="bulan" class="form-select" style="width:160px">
                        @foreach ($namaBulan as $n => $nm)
                        <option value="{{ $n }}" {{ $bulan == $n ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                    <select name="tahun" class="form-select" style="width:110px">
                        @for ($y = (int)date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
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
                    <div class="fs-2 fw-bold text-danger lh-1">{{ count($laporan) }}</div>
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
                    <div class="fs-2 fw-bold text-secondary lh-1">{{ number_format($grandSesi) }}</div>
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
                    <div class="fs-4 fw-bold text-dark lh-1">{{ number_format($grandDurasi, 1) }} jam</div>
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
                    <div class="fs-5 fw-bold text-dark lh-1">Rp {{ number_format($grandUangMakan, 0, ',', '.') }}</div>
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
                Rincian Lembur &mdash; {{ $namaBulan[(int)$bulan] }} {{ $tahun }}
            </h6>
            <small class="text-muted">Klik <strong>Detail</strong> untuk melihat breakdown per hari</small>
        </div>
        @if (!empty($laporan))
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
            <i class="bi bi-moon-stars me-1"></i>{{ count($laporan) }} Pegawai Lembur
        </span>
        @endif
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
                    @forelse ($laporan as $i => $row)
                        @php
                            $rataRata  = (int)$row->total_sesi > 0 ? (float)$row->total_durasi / (int)$row->total_sesi : 0;
                            $pctDurasi = $grandDurasi > 0 ? round((float)$row->total_durasi / $grandDurasi * 100) : 0;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted small">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $row->nama_pegawai }}</div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($row->tgl_pertama)->format('d/m') }} &ndash; {{ \Carbon\Carbon::parse($row->tgl_terakhir)->format('d/m/Y') }}
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fw-semibold">
                                    {{ (int)$row->total_sesi }} Sesi
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark">{!! fmtJam($row->total_durasi) !!}</div>
                                <div class="progress mt-1 mx-auto" style="height:5px;width:80px;" title="{{ $pctDurasi }}% dari total lembur">
                                    <div class="progress-bar bg-danger" style="width:{{ $pctDurasi }}%"></div>
                                </div>
                                <small class="text-muted">{{ $pctDurasi }}% dari total</small>
                            </td>
                            <td class="text-center">
                                <span class="text-secondary small">{!! fmtJam($rataRata) !!}</span>
                            </td>
                            <td class="text-end">
                                @if ((float)$row->total_uang_makan > 0)
                                    <strong class="text-success">Rp {{ number_format($row->total_uang_makan, 0, ',', '.') }}</strong>
                                @else
                                    <span class="text-muted small">Rp 0 <br><em>(selesai sebelum 20:00)</em></span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-outline-dark btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#modalDetail"
                                    onclick="loadDetail({{ (int)$row->id_pegawai }}, '{{ addslashes($row->nama_pegawai) }}', {{ $bulan }}, {{ $tahun }})">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-moon fs-1 d-block mb-2 opacity-50"></i>
                                    <strong>Tidak ada data lembur</strong> untuk periode
                                    <strong>{{ $namaBulan[(int)$bulan] }} {{ $tahun }}</strong>.
                                    <div class="small mt-1">
                                        Silakan input melalui menu
                                        <a href="{{ route('lembur.register') }}" class="text-decoration-none fw-semibold">Register Lembur</a>.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if (count($laporan) > 0)
                <tfoot>
                    <tr style="background:#f0f6ff; border-top:2px solid #dee2e6;">
                        <td colspan="2" class="ps-4 py-3 fw-bold text-muted">TOTAL ({{ count($laporan) }} Pegawai)</td>
                        <td class="text-center py-3 fw-bold">{{ number_format($grandSesi) }} Sesi</td>
                        <td class="text-center py-3 fw-bold text-dark">{!! fmtJam($grandDurasi) !!}</td>
                        <td></td>
                        <td class="text-end py-3">
                            <div class="text-muted small fw-semibold">GRAND TOTAL UANG MAKAN</div>
                            <div class="fs-5 fw-bold text-success">Rp {{ number_format($grandUangMakan, 0, ',', '.') }}</div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    @if (count($laporan) > 0)
    <div class="card-footer bg-light text-muted small py-2 px-4 border-top">
        <i class="bi bi-info-circle me-1"></i>
        Uang makan lembur (<strong>Rp 15.000</strong>) hanya diberikan jika jam selesai lembur melewati pukul <strong>20:00</strong>,
        sesuai aturan SPL yang berlaku di perusahaan.
    </div>
    @endif
</div>

<!-- MODAL DETAIL PER HARI -->
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
    body { background-color: #fff; margin: 0; padding: 0; }
    #sidebar-wrapper, nav.navbar, .d-print-none, .btn, form, #modalDetail, .card { display: none !important; }
    .d-print-block { display: block !important; }
    main.content { padding: 0 !important; margin: 0 !important; width: 100% !important; }
    .invoice-print { width: 100%; margin: 0 auto; padding: 20px; }
    .table-bordered > :not(caption) > * > * { border-width: 1px 1px; border-color: #000; }
}
</style>

<!-- PRINT INVOICE FORMAT -->
<div class="d-none d-print-block invoice-print text-dark">
    <div class="row mb-3">
        <div class="col-8">
            <h6 class="fw-bold mb-0">PT. USAHA BAKTI PERKASA</h6>
            <div style="font-size: 11px; line-height: 1.2;">
                GENERAL CONTRACTOR, STEEL FABRICATOR & GALVANIZE<br>
                Jl. Raya Lingkar Timur No. 1 - Kemiri - Sidoarjo<br>
                No. Telp : (031) 8073893, 8965651, Fax : (031) 8956560
            </div>
        </div>
        <div class="col-4 text-end">
            <div class="border border-dark p-2 text-center ms-auto" style="width: 150px; letter-spacing: 2px;">
                <h5 class="fw-bold mb-0">INVOICE</h5>
            </div>
        </div>
    </div>

    <div class="row g-0 mb-3" style="font-size: 12px;">
        <div class="col-7 border border-dark p-2">
            <table class="table table-borderless table-sm mb-0">
                <tr><td width="80" class="py-0 px-1">Kepada</td><td class="py-0 px-1">: </td></tr>
                <tr><td class="py-0 px-1">Alamat</td><td class="py-0 px-1">: </td></tr>
                <tr><td class="py-0 px-1">NPWP</td><td class="py-0 px-1">: </td></tr>
            </table>
        </div>
        <div class="col-5 border border-dark p-2 border-start-0">
            <table class="table table-borderless table-sm mb-0">
                <tr><td width="100" class="py-0 px-1">Tgl Invoice</td><td class="py-0 px-1">: {{ date('d F Y') }}</td></tr>
                <tr><td class="py-0 px-1">No. Invoice</td><td class="py-0 px-1">: {{ date('d') }}/UBP-INV/LBM/{{ date('m/Y') }}</td></tr>
                <tr><td class="py-0 px-1">Periode</td><td class="py-0 px-1">: {{ $namaBulan[(int)$bulan] }} {{ $tahun }}</td></tr>
                <tr><td class="py-0 px-1">Pembayaran</td><td class="py-0 px-1">: Termin 30 Hari</td></tr>
            </table>
        </div>
    </div>

    <table class="table table-bordered border-dark table-sm mb-0" style="font-size: 12px;">
        <thead class="text-center">
            <tr>
                <th width="5%">NO</th>
                <th width="40%">DESKRIPSI</th>
                <th width="15%">TOTAL SESI</th>
                <th width="20%">TOTAL DURASI</th>
                <th width="20%">UANG MAKAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->nama_pegawai }} - Lembur</td>
                <td class="text-center">{{ (int)$row->total_sesi }}</td>
                <td class="text-center">{!! fmtJam($row->total_durasi) !!}</td>
                <td class="text-end">Rp. {{ number_format($row->total_uang_makan, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-center fw-bold">Total Uang Makan Lembur</td>
                <td class="text-end fw-bold">Rp. {{ number_format($grandUangMakan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="row mt-4" style="font-size: 11px;">
        <div class="col-8">
            * Pembayaran mohon di transfer ke :<br>
            Bank&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Mandiri / BCA<br>
            Cabang&nbsp;&nbsp;&nbsp;&nbsp;: Sidoarjo<br>
            Penerima&nbsp;&nbsp;: PT. USAHA BAKTI PERKASA<br>
            No. Rek&nbsp;&nbsp;&nbsp;: 141 0088 5757 88 / 018 501 5900<br>
            No. NPWP : 02.169.653.5 - 609.000<br><br>
            <b>UBP-FIN-INV-02</b>
        </div>
        <div class="col-4 text-center">
            PT. USAHA BAKTI PERKASA<br><br><br><br>
            <span class="text-decoration-underline fw-bold">PUJI RAHAYU</span><br>
            Finance
        </div>
    </div>
</div>

<script>
function loadDetail(idPegawai, nama, bulan, tahun) {
    document.getElementById('modalSubtitle').textContent = nama + ' — ' + bulan + '/' + tahun;
    document.getElementById('modalDetailBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-secondary" role="status"></div>
            <div class="mt-2 text-muted">Memuat data lembur...</div>
        </div>`;

    fetch('{{ route('laporan.lembur.detail') }}?id_pegawai=' + idPegawai + '&bulan=' + bulan + '&tahun=' + tahun)
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
@endsection
