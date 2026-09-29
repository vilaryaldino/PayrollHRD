@extends('layouts.app')

@section('title', 'Data Absensi')

@section('breadcrumb_parent')
<span>Master</span>
<span class="separator">/</span>
@endsection

@section('breadcrumb_active', 'Data Absensi')

@php
if (!function_exists('getInitials')) {
    function getInitials($name) {
        $clean = trim(preg_replace('/[^a-zA-Z\s]/', '', $name));
        $words = explode(' ', $clean);
        if (count($words) >= 2 && !empty($words[0]) && !empty($words[1])) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($clean, 0, 2) ?: 'PG');
    }
}
@endphp

@section('content')
<!-- Page Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 page-title-section">
    <div>
        <h1>Data Absensi</h1>
        <p>Kelola data kehadiran, riwayat clock in/out, dan import data absen.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-blue-outline" data-bs-toggle="modal" data-bs-target="#modalManual">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Manual</span>
        </button>
        <button type="button" class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#modalImport">
            <i class="bi bi-file-earmark-excel"></i>
            <span>Import Fingerspot</span>
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 px-3 mb-3 d-flex align-items-center justify-content-between" style="background:#ecfdf5; color:#065f46; border-left: 4px solid #10b981 !important;">
    <div class="d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
        <span>{{ session('success') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="font-size:0.75rem;"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 px-3 mb-3 d-flex align-items-center justify-content-between" style="background:#fff1f2; color:#9f1239; border-left: 4px solid #e11d48 !important;">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-circle-fill me-2 fs-5 text-danger"></i>
        <span><strong>Gagal:</strong> {{ session('error') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="font-size:0.75rem;"></button>
</div>
@endif

<div class="unified-filter-bar mb-4">
    <form method="GET" action="{{ route('absensi.index') }}" class="row g-2 align-items-center">
        <!-- Search Box -->
        <div class="col-lg-4 col-md-5 col-sm-12">
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem;"></i>
                <input type="text" class="form-control search-input-pill ps-5" name="search"
                       value="{{ request('search') }}" placeholder="Cari nama pegawai / pin...">
            </div>
        </div>
        
        <div class="col-lg-2 col-md-3 col-sm-6">
            <input type="date" name="start_date" class="form-control filter-select-pill" value="{{ request('start_date') }}" title="Mulai Tanggal" onchange="this.form.submit()">
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6">
            <input type="date" name="end_date" class="form-control filter-select-pill" value="{{ request('end_date') }}" title="Sampai Tanggal" onchange="this.form.submit()">
        </div>

        <div class="col-lg-4 col-md-12 d-flex align-items-center justify-content-lg-end justify-content-between gap-2 mt-2 mt-lg-0">
            <div class="d-flex align-items-center gap-2 me-2">
                <span class="small text-muted">Tampil:</span>
                <select name="per_page" class="form-select filter-select-pill form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            <span class="text-muted small fw-medium">
                {{ $absensis->total() }} data ditemukan
            </span>
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-light border px-3" title="Terapkan Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                @if (request('search') || request('start_date') || request('end_date') || request('per_page'))
                    <a href="{{ route('absensi.index') }}" class="btn btn-sm btn-light border text-danger" title="Reset Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Main Table Container -->
<div class="unified-card">
    <div class="table-responsive">
        <table class="unified-table">
            <thead>
                <tr>
                    <th style="width: 120px;">TANGGAL</th>
                    <th>NAMA PEGAWAI</th>
                    <th style="text-align: center;">CLOCK IN</th>
                    <th style="text-align: center;">CLOCK OUT</th>
                    <th>DEPARTEMEN</th>
                    <th>METHOD</th>
                    <th style="width: 100px; text-align: center;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensis as $absen)
                @php 
                    $initials = getInitials($absen->nama_pegawai);
                    $pin = $absen->id_mesin_pegawai ?? $absen->id_mesin ?? $absen->id_pegawai_mesin;
                @endphp
                <tr>
                    <td>
                        <span class="fw-semibold text-dark small">{{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d M Y') }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="table-avatar-circle">
                                {{ $initials }}
                            </div>
                            <div>
                                <div class="table-user-name">
                                    @if(str_contains($absen->nama_pegawai, 'Tidak Dikenal'))
                                        <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> {{ $absen->nama_pegawai }}</span>
                                    @else
                                        {{ $absen->nama_pegawai }}
                                    @endif
                                </div>
                                <div class="table-user-meta">
                                    <span>PIN: {{ $pin }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        @if($absen->jam_kehadiran)
                            <span class="status-dot-indicator dot-aktif">{{ \Carbon\Carbon::parse($absen->jam_kehadiran)->format('H:i') }}</span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($absen->jam_kepulangan)
                            <span class="status-dot-indicator dot-shift-pagi">{{ \Carbon\Carbon::parse($absen->jam_kepulangan)->format('H:i') }}</span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="soft-badge badge-staff-soft">{{ $absen->departemen ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="text-muted small fw-medium">{{ $absen->method ?? '-' }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <button type="button" class="action-icon-btn" title="Detail" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $absen->id }}">
                                <i class="bi bi-eye"></i>
                            </button>
                            <form method="POST" action="{{ route('absensi.destroy', $absen->id) }}" class="d-inline" onsubmit="return confirm('Hapus data absensi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-icon-btn btn-delete" title="Hapus">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-inbox fs-1 mb-2"></i>
                            <span>Belum ada data absensi yang ditemukan.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white py-3 border-top-0 border-bottom-0" style="border-radius: 0 0 12px 12px;">
        {{ $absensis->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- MODAL IMPORT EXCEL --}}
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('absensi.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-excel me-2"></i>Import Data Fingerspot</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Pastikan format file Excel Anda (.xls / .xlsx) memiliki Header: <b>pin_mesin, tanggal, jam_in, jam_out, lokasi</b>.</p>
                    <div class="mb-3">
                        <label for="file_excel" class="form-label">Pilih File Excel</label>
                        <input class="form-control" type="file" id="file_excel" name="file_excel" required accept=".xls,.xlsx">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Proses Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL TAMBAH MANUAL --}}
<div class="modal fade" id="modalManual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('absensi.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Absensi Manual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pegawai (PIN Mesin)</label>
                        <select name="id_pegawai" class="form-select" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->ID_PEGAWAI_MESIN }}">{{ $pegawai->NM_PEGAWAI }} (PIN: {{ $pegawai->ID_PEGAWAI_MESIN }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam In (Contoh: 08:00)</label>
                            <input type="time" name="jam_kehadiran" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Out (Contoh: 17:00)</label>
                            <input type="time" name="jam_kepulangan" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi/Keterangan</label>
                        <input type="text" name="lokasi_absen" class="form-control" placeholder="Misal: WFH / Dinas Luar">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL DETAIL ABSENSI --}}
@foreach($absensis as $absen)
<div class="modal fade" id="modalDetail{{ $absen->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white border-0">
                <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detail Absensi Pegawai</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th width="35%" class="text-muted">Tanggal</th><td class="fw-medium">: {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d M Y') }}</td></tr>
                    <tr><th class="text-muted">ID Pegawai</th><td class="fw-medium">: {{ $absen->id_mesin_pegawai ?? $absen->id_mesin ?? $absen->id_pegawai_mesin }}</td></tr>
                    <tr><th class="text-muted">Nama Pegawai</th><td class="fw-medium">: {{ $absen->nama_pegawai }}</td></tr>
                    <tr><th class="text-muted">Clock In</th><td class="text-success fw-bold">: {{ $absen->jam_kehadiran ? \Carbon\Carbon::parse($absen->jam_kehadiran)->format('H:i') : '-' }}</td></tr>
                    <tr><th class="text-muted">Clock Out</th><td class="text-primary fw-bold">: {{ $absen->jam_kepulangan ? \Carbon\Carbon::parse($absen->jam_kepulangan)->format('H:i') : '-' }}</td></tr>
                    <tr><th class="text-muted">Lokasi</th><td>: {{ $absen->lokasi_absen ?? '-' }}</td></tr>
                    <tr><th class="text-muted">Departemen</th><td>: {{ $absen->departemen ?? '-' }}</td></tr>
                    <tr><th class="text-muted">Posisi</th><td>: {{ $absen->posisi ?? '-' }}</td></tr>
                    <tr><th class="text-muted">SN Perangkat</th><td>: {{ $absen->sn_perangkat ?? '-' }}</td></tr>
                    <tr><th class="text-muted">Method</th><td>: {{ $absen->method ?? '-' }}</td></tr>
                    <tr><th class="text-muted">Keterangan</th><td>: {{ $absen->keterangan ?? '-' }}</td></tr>
                    <tr><th class="text-muted">Status Data</th><td>: 
                        @if($absen->jam_kehadiran && $absen->jam_kepulangan)
                            <span class="badge bg-success rounded-pill">Lengkap</span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill">Tidak Lengkap</span>
                        @endif
                    </td></tr>
                </table>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
