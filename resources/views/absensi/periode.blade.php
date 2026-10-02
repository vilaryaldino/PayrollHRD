@extends('layouts.app')
@section('title', 'Tabel Absensi Periode')
@section('breadcrumb_active', 'Tabel Absensi Periode')

@section('content')
<div class="page-title-section">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Tabel Absensi Periode</h1>
            <p>Data harian, lembur, serta dinas luar (LK/LP)</p>
        </div>
        <div>
            <button type="button" class="btn-orange" data-bs-toggle="modal" data-bs-target="#dinasLuarModal">
                <i class="bi bi-plus-circle"></i> Input Dinas Luar
            </button>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="unified-card">
    <div class="unified-card-header">
        <form method="GET" action="{{ route('absensi.periode') }}" class="d-flex gap-2 align-items-center w-100">
            <select name="bulan" class="filter-select-pill">
                @for($m=1; $m<=12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
            <select name="tahun" class="filter-select-pill">
                @for($y=2023; $y<=date('Y'); $y++)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="btn-blue-outline py-1"><i class="bi bi-search"></i> Tampilkan</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="unified-table table table-bordered mb-0 text-center" style="font-size: 0.8rem; border-color: #e2e8f0;">
            <thead style="background-color: #f8db9f; color: #0f172a;">
                <tr>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">No.</th>
                    <th rowspan="2" class="align-middle text-start" style="background-color: #a7d08c; color: #000; min-width: 150px;">Nama</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">HK</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">HKL</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">LK</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">LP</th>
                    <th colspan="3" style="background-color: #a7d08c; color: #000;">Jam Lembur</th>
                    <th colspan="3" style="background-color: #a7d08c; color: #000;">Nominal Lembur</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">Gaji Luar<br>Pulau/Kota</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">UM LK</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">UM LP</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">Uang<br>Makan</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">Uang Makan<br>Lembur</th>
                    <th rowspan="2" class="align-middle" style="background-color: #a7d08c; color: #000;">Total</th>
                </tr>
                <tr>
                    <th style="background-color: #a7d08c; color: #000;">A</th>
                    <th style="background-color: #a7d08c; color: #000;">B</th>
                    <th style="background-color: #a7d08c; color: #000;">SM</th>
                    <th style="background-color: #a7d08c; color: #000;">A</th>
                    <th style="background-color: #a7d08c; color: #000;">B</th>
                    <th style="background-color: #a7d08c; color: #000;">SM</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($laporan as $row)
                    @php $grandTotal += $row->total_idr; @endphp
                    <tr>
                        <td>{{ $row->no }}</td>
                        <td class="text-start fw-bold">{{ $row->nama }}</td>
                        <td>{{ $row->hk }}</td>
                        <td>{{ $row->hkl }}</td>
                        <td>{{ $row->lk > 0 ? $row->lk : '' }}</td>
                        <td>{{ $row->lp > 0 ? $row->lp : '' }}</td>
                        <td>{{ $row->jam_la > 0 ? $row->jam_la : '' }}</td>
                        <td>{{ $row->jam_lb > 0 ? $row->jam_lb : '' }}</td>
                        <td>{{ $row->jam_sm > 0 ? $row->jam_sm : '' }}</td>
                        
                        <td class="text-end text-nowrap">Rp {{ number_format($rate_la, 0, ',', '.') }}</td>
                        <td class="text-end text-nowrap">Rp {{ number_format($rate_lb, 0, ',', '.') }}</td>
                        <td class="text-end text-nowrap">Rp {{ number_format($rate_sm, 0, ',', '.') }}</td>
                        
                        <td class="text-end text-nowrap">{{ $row->gaji_lk_lp > 0 ? 'Rp '.number_format($row->gaji_lk_lp, 0, ',', '.') : '' }}</td>
                        <td class="text-end text-nowrap">{{ $row->um_lk > 0 ? 'Rp '.number_format($row->um_lk, 0, ',', '.') : '' }}</td>
                        <td class="text-end text-nowrap">{{ $row->um_lp > 0 ? 'Rp '.number_format($row->um_lp, 0, ',', '.') : '' }}</td>
                        
                        <td class="text-end text-nowrap">Rp {{ number_format($row->uang_makan, 0, ',', '.') }}</td>
                        <td class="text-end text-nowrap">Rp {{ number_format($row->uang_makan_lembur, 0, ',', '.') }}</td>
                        <td class="text-end text-nowrap fw-bold">Rp {{ number_format($row->total_idr, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if(count($laporan) == 0)
                    <tr>
                        <td colspan="18" class="text-center py-3 text-muted">Tidak ada data untuk periode ini</td>
                    </tr>
                @endif
            </tbody>
            @if(count($laporan) > 0)
            <tfoot>
                <tr class="fw-bold bg-light">
                    <td colspan="17" class="text-center text-uppercase">Total Gaji</td>
                    <td class="text-end text-nowrap">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<!-- Modal Input Dinas Luar -->
<div class="modal fade" id="dinasLuarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('absensi.dinas-luar.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Input Dinas Luar (Opsional)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Pegawai</label>
                    <select name="id_pegawai" class="form-select" required>
                        <option value="">Pilih Pegawai...</option>
                        @foreach(DB::table('m_pegawai')->where('IS_AKTIF', 1)->orderBy('NM_PEGAWAI')->get() as $p)
                            <option value="{{ $p->ID_PEGAWAI }}">{{ $p->NM_PEGAWAI }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jenis Dinas</label>
                    <select name="jenis_dinas" class="form-select" required>
                        <option value="LK">Luar Kota (LK)</option>
                        <option value="LP">Luar Pulau (LP)</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jumlah Hari</label>
                    <input type="number" name="jumlah_hari" class="form-control" required min="1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Gaji Luar Kota / Pulau - <i>Opsional</i></label>
                    <input type="number" name="gaji_luar" class="form-control" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tarif Uang Makan (per hari) - <i>Opsional</i></label>
                    <input type="number" name="tarif_um" class="form-control" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Uang Makan LK/LP - <i>Opsional</i></label>
                    <input type="number" name="total_uang_makan" class="form-control" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Uang Makan Lembur - <i>Opsional</i></label>
                    <input type="number" name="uang_makan_lembur" class="form-control" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <input type="text" name="catatan" class="form-control" placeholder="Contoh: Kunjungan ke cabang X">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
