@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="mb-0 text-primary fw-bold"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Laporan Rekapitulasi Lembur & UM</h3>
            <p class="text-muted small mb-0">Hitungan otomatis berdasarkan Data Absensi Kehadiran Mesin</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('laporan.rekap') }}" class="row g-3 align-items-end">
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
                            <th class="text-center">Kehadiran / UM</th>
                            <th class="text-center">Lembur A</th>
                            <th class="text-center">Lembur B</th>
                            <th class="text-center">UM Lembur</th>
                            <th class="text-end">Total IDR</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $row)
                            <tr>
                                <td class="ps-4">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $row->ID_PEGAWAI_MESIN ?: $row->id_pegawai }}</span>
                                </td>
                                <td class="fw-medium">{{ $row->nama_pegawai }}</td>
                                <td class="text-center">
                                    <span class="fw-bold">{{ $row->total_hari_kerja }}</span><br>
                                    <small class="text-muted">Rp {{ number_format($row->amt_um, 0, ',', '.') }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-primary">{{ $row->total_la }}</span><br>
                                    <small class="text-muted">Rp {{ number_format($row->amt_la, 0, ',', '.') }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-danger">{{ $row->total_lb }}</span><br>
                                    <small class="text-muted">Rp {{ number_format($row->amt_lb, 0, ',', '.') }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-success">{{ $row->total_uml }}</span><br>
                                    <small class="text-muted">Rp {{ number_format($row->amt_uml, 0, ',', '.') }}</small>
                                </td>
                                <td class="text-end fw-bold">Rp {{ number_format($row->total_idr, 0, ',', '.') }}</td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('laporan.cetak-rekap', ['id_pegawai' => $row->id_pegawai, 'bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-printer"></i> Cetak Rekap
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Tidak ada data kehadiran di bulan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="7" class="text-end">GRAND TOTAL :</td>
                            <td class="text-end text-primary fs-5">Rp {{ number_format(collect($laporan)->sum('total_idr'), 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
