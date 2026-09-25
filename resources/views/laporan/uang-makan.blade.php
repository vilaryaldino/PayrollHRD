@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center d-print-none">
        <div class="col-md-6">
            <h3 class="mb-0 text-primary fw-bold"><i class="bi bi-cup-hot me-2"></i>Laporan Uang Makan</h3>
            <p class="text-muted small mb-0">Tarif Dasar: Rp 15.000 / Hari Hadir</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button class="btn btn-outline-success" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Laporan</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 d-print-none">
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('laporan.uang-makan') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Bulan</label>
                    <select name="bulan" class="form-select">
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ \Carbon\Carbon::create()->month($i)->format('m') }}" {{ $bulan == \Carbon\Carbon::create()->month($i)->format('m') ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for($y=now()->year; $y>=now()->year-3; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Tampilkan</button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>PIN / ID Pegawai</th>
                            <th>Nama Pegawai</th>
                            <th class="text-center">Total Hari Hadir</th>
                            <th class="text-end pe-4">Total Uang Makan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $row)
                            <tr>
                                <td class="ps-4">{{ $loop->iteration }}</td>
                                <td><span class="badge bg-secondary">{{ $row->id_pegawai }}</span></td>
                                <td class="fw-medium">{{ $row->nama_pegawai }}</td>
                                <td class="text-center fw-bold text-success">{{ $row->total_hari_hadir }} Hari</td>
                                <td class="text-end pe-4 fw-bold">Rp {{ number_format($row->total_uang_makan, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Tidak ada data kehadiran di bulan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">GRAND TOTAL :</td>
                            <td class="text-end pe-4 text-primary fs-5">Rp {{ number_format(collect($laporan)->sum('total_uang_makan'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- PRINT Laporan FORMAT -->
    <div class="d-none d-print-block Laporan-print text-dark">
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
                    <h5 class="fw-bold mb-0">Laporan</h5>
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
                    <tr><td width="100" class="py-0 px-1">Tgl Laporan</td><td class="py-0 px-1">: {{ date('d F Y') }}</td></tr>
                    <tr><td class="py-0 px-1">No. Laporan</td><td class="py-0 px-1">: {{ date('d') }}/UBP-INV/UM/{{ date('m/Y') }}</td></tr>
                    <tr><td class="py-0 px-1">Periode</td><td class="py-0 px-1">: {{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} {{ $tahun }}</td></tr>
                    <tr><td class="py-0 px-1">Pembayaran</td><td class="py-0 px-1">: Termin 30 Hari</td></tr>
                </table>
            </div>
        </div>

        <table class="table table-bordered border-dark table-sm mb-0" style="font-size: 12px;">
            <thead class="text-center">
                <tr>
                    <th width="5%">NO</th>
                    <th width="45%">DESKRIPSI</th>
                    <th width="15%">JUMLAH (HARI)</th>
                    <th width="15%">HARGA SATUAN</th>
                    <th width="20%">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->nama_pegawai }} - Uang Makan</td>
                    <td class="text-center">{{ $row->total_hari_hadir }}</td>
                    <td class="text-end">Rp. 15,000</td>
                    <td class="text-end">Rp. {{ number_format($row->total_uang_makan, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-center fw-bold">Total</td>
                    <td class="text-end fw-bold">Rp. {{ number_format(collect($laporan)->sum('total_uang_makan'), 0, ',', '.') }}</td>
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
</div>

<style>
@media print {
    body { background-color: #fff; margin: 0; padding: 0; }
    #sidebar-wrapper, nav.navbar, .d-print-none { display: none !important; }
    .d-print-block { display: block !important; }
    main.content { padding: 0 !important; margin: 0 !important; width: 100% !important; }
    .Laporan-print { width: 100%; margin: 0 auto; padding: 20px; }
    .table-bordered > :not(caption) > * > * { border-width: 1px 1px; border-color: #000; }
}
</style>
@endsection
