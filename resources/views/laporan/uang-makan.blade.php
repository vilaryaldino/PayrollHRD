@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="mb-0 text-primary fw-bold"><i class="bi bi-cup-hot me-2"></i>Laporan Uang Makan</h3>
            <p class="text-muted small mb-0">Tarif Dasar: Rp 15.000 / Hari Hadir</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button class="btn btn-outline-success"><i class="bi bi-printer"></i> Cetak Laporan</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
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
</div>
@endsection
