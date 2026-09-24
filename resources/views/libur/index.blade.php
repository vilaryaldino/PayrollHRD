@extends('layouts.app')

@section('content')
@php
function cekUpcoming($tgl) {
    return strtotime($tgl) >= strtotime(date('Y-m-d'));
}
@endphp

<style>
.lb-header h1 { font-size: 1.45rem; font-weight: 700; color: #1e293b; margin: 0; }
.lb-header p  { font-size: .84rem; color: #64748b; margin: 3px 0 0; }

.lb-stats { display: flex; gap: 1rem; margin-bottom: 1.2rem; flex-wrap: wrap; }
.lb-stat  {
    background: #fff; border-radius: 10px; border: 1px solid #e8edf2;
    box-shadow: 0 1px 6px rgba(0,0,0,.04);
    padding: .85rem 1.2rem; display: flex; align-items: center; gap: .7rem; min-width: 150px;
}
.lb-stat-icon { font-size: 1.6rem; }
.lb-stat-val  { font-size: 1.35rem; font-weight: 700; color: #1e293b; line-height: 1; }
.lb-stat-lbl  { font-size: .74rem; color: #64748b; }

.lb-card { background: #fff; border-radius: 12px; border: 1px solid #e8edf2; box-shadow: 0 2px 10px rgba(0,0,0,.05); overflow: hidden; }
.lb-card table { margin: 0; }
.lb-card thead th { background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; padding: .85rem 1.2rem; border: none; border-bottom: 2px solid #e2e8f0; }
.lb-card tbody td { padding: .85rem 1.2rem; border-color: #f1f5f9; font-size: .86rem; color: #334155; vertical-align: middle; }
.lb-card tbody tr:hover { background: #f8fafc; }
.lb-card tbody tr.past-row td { opacity: .6; }

.lb-badge-nasional { background: #fee2e2; color: #b91c1c; padding: .25rem .65rem; border-radius: 20px; font-size: .73rem; font-weight: 600; }
.lb-badge-cuti     { background: #fef3c7; color: #b45309; padding: .25rem .65rem; border-radius: 20px; font-size: .73rem; font-weight: 600; }
.lb-badge-khusus   { background: #e0e7ff; color: #4338ca; padding: .25rem .65rem; border-radius: 20px; font-size: .73rem; font-weight: 600; }
</style>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3 lb-header flex-wrap gap-2">
    <div>
        <h1><i class="bi bi-calendar-event me-2 text-primary"></i>Master Hari Libur Nasional (M_LIBUR_NASIONAL)</h1>
        <p>Tersambung langsung dengan Database MySQL: <code>payrollhrd.M_LIBUR_NASIONAL</code></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('jadwal.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-calendar-check me-1"></i> Kalender Jadwal Kerja
        </a>
        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addLiburModal">
            <i class="bi bi-calendar-plus me-1"></i> Tambah Hari Libur
        </button>
    </div>
</div>

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 mb-3">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistik -->
<div class="lb-stats">
    <div class="lb-stat">
        <div class="lb-stat-icon">📅</div>
        <div>
            <div class="lb-stat-val">{{ $totalLibur }}</div>
            <div class="lb-stat-lbl">Total Hari Libur</div>
        </div>
    </div>
    <div class="lb-stat">
        <div class="lb-stat-icon">🏛️</div>
        <div>
            <div class="lb-stat-val">{{ $totalNasional }}</div>
            <div class="lb-stat-lbl">Libur Nasional</div>
        </div>
    </div>
    <div class="lb-stat">
        <div class="lb-stat-icon">🌴</div>
        <div>
            <div class="lb-stat-val">{{ $totalCuti }}</div>
            <div class="lb-stat-lbl">Cuti Bersama</div>
        </div>
    </div>
    <div class="lb-stat">
        <div class="lb-stat-icon">⏳</div>
        <div>
            <div class="lb-stat-val">{{ $totalUpcoming }}</div>
            <div class="lb-stat-lbl">Akan Datang</div>
        </div>
    </div>
</div>

<!-- Tabel Hari Libur -->
<div class="lb-card">
    <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-table me-2 text-muted"></i>Daftar Hari Libur & Cuti Bersama</h6>
        <div>
            <form method="GET" action="{{ route('libur.index') }}" class="d-inline">
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">Semua Tahun</option>
                    @foreach ($tahunList as $yr)
                        <option value="{{ $yr }}" {{ (string)$selectedYear === (string)$yr ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Hari Libur / Peristiwa</th>
                    <th>Tanggal Kalender</th>
                    <th>Hari</th>
                    <th>Jenis Libur</th>
                    <th>Status Gaji (Paid)</th>
                    <th>Status Kalender</th>
                    <th style="width: 80px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($liburList as $l)
                @php 
                    $isPast = !cekUpcoming($l->TANGGAL);
                    $isPaid = (int)$l->IS_DIBAYAR;
                @endphp
                <tr class="{{ $isPast ? 'past-row' : '' }}">
                    <td class="text-muted small">{{ $loop->iteration }}</td>
                    <td><strong>{{ $l->KETERANGAN }}</strong></td>
                    <td><i class="bi bi-calendar3 text-primary me-1"></i>{{ \Carbon\Carbon::parse($l->TANGGAL)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($l->TANGGAL)->locale('id')->translatedFormat('l') }}</td>
                    <td>
                        @if ($l->JENIS_LIBUR === 'Nasional')
                            <span class="lb-badge-nasional"><i class="bi bi-flag-fill me-1"></i>Nasional</span>
                        @elseif ($l->JENIS_LIBUR === 'Cuti Bersama')
                            <span class="lb-badge-cuti"><i class="bi bi-calendar-range me-1"></i>Cuti Bersama</span>
                        @else
                            <span class="lb-badge-khusus"><i class="bi bi-building me-1"></i>Khusus</span>
                        @endif
                    </td>
                    <td>
                        @if ($isPaid === 1)
                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check2 me-1"></i>Berbayar (Paid)</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Unpaid</span>
                        @endif
                    </td>
                    <td>
                        @if (cekUpcoming($l->TANGGAL))
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle"><i class="bi bi-clock me-1"></i>Akan Datang</span>
                        @else
                            <span class="badge bg-light text-muted border"><i class="bi bi-check-all me-1"></i>Sudah Lewat</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <button class="btn btn-sm btn-link text-primary p-0 me-2" data-bs-toggle="modal" data-bs-target="#editLibur{{ $l->ID_LIBUR }}">
                            <i class="bi bi-pencil-square fs-6"></i>
                        </button>
                        <form method="POST" action="{{ route('libur.destroy', $l->ID_LIBUR) }}" class="d-inline" onsubmit="return confirm('Hapus hari libur {{ addslashes($l->KETERANGAN) }} dari database?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash3 fs-6"></i></button>
                        </form>
                    </td>
                </tr>

                <!-- Modal Edit Libur -->
                <div class="modal fade" id="editLibur{{ $l->ID_LIBUR }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="POST" action="{{ route('libur.update', $l->ID_LIBUR) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-header bg-dark text-white rounded-top-4">
                                    <h5 class="modal-title fs-6 fw-semibold"><i class="bi bi-pencil-square me-2"></i>Edit Hari Libur</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 text-start">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Nama Hari Libur / Peristiwa</label>
                                        <input type="text" class="form-control" name="nama_libur" value="{{ $l->KETERANGAN }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Tanggal</label>
                                        <input type="date" class="form-control" name="tanggal" value="{{ $l->TANGGAL }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Jenis Libur (JENIS_LIBUR)</label>
                                        <select class="form-select" name="jenis" required>
                                            <option value="Nasional" {{ $l->JENIS_LIBUR === 'Nasional' ? 'selected' : '' }}>Libur Nasional Resmi</option>
                                            <option value="Cuti Bersama" {{ $l->JENIS_LIBUR === 'Cuti Bersama' ? 'selected' : '' }}>Cuti Bersama Pemerintah</option>
                                            <option value="Khusus Perusahaan" {{ $l->JENIS_LIBUR === 'Khusus Perusahaan' ? 'selected' : '' }}>Khusus Perusahaan</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Perhitungan Upah / Gaji</label>
                                        <select class="form-select" name="is_dibayar" required>
                                            <option value="1" {{ $isPaid === 1 ? 'selected' : '' }}>Dibayar Penuh (Paid Holiday)</option>
                                            <option value="0" {{ $isPaid === 0 ? 'selected' : '' }}>Tidak Dibayar (Unpaid)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-primary px-4">Simpan Perubahan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                        Tidak ada data hari libur pada tahun yang dipilih.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Libur -->
<div class="modal fade" id="addLiburModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('libur.store') }}">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fs-6 fw-semibold"><i class="bi bi-calendar-plus me-2"></i>Tambah Hari Libur (M_LIBUR_NASIONAL)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Hari Libur / Peristiwa</label>
                        <input type="text" class="form-control" name="nama_libur" required placeholder="contoh: Hari Kemerdekaan RI">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal Kalender</label>
                        <input type="date" class="form-control" name="tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jenis Libur (JENIS_LIBUR)</label>
                        <select class="form-select" name="jenis" required>
                            <option value="Nasional" selected>Libur Nasional Resmi</option>
                            <option value="Cuti Bersama">Cuti Bersama Pemerintah</option>
                            <option value="Khusus Perusahaan">Khusus Perusahaan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Perhitungan Upah / Gaji</label>
                        <select class="form-select" name="is_dibayar" required>
                            <option value="1" selected>Dibayar Penuh (Paid Holiday)</option>
                            <option value="0">Tidak Dibayar (Unpaid)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">Simpan Hari Libur ke Database</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
