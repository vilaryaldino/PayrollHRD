@extends('layouts.app')

@section('title', 'Master Hari Libur Nasional')

@section('breadcrumb_parent')
<span>Master</span>
<span class="separator">/</span>
@endsection

@section('breadcrumb_active', 'Libur Nasional')

@php
function cekUpcoming($tgl) {
    return strtotime($tgl) >= strtotime(date('Y-m-d'));
}
@endphp

@section('content')
<!-- Page Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 page-title-section">
    <div>
        <h1>Master Hari Libur Nasional</h1>
        <p>Pengaturan kalender hari libur resmi, cuti bersama, dan ketentuan perhitungan upah (paid holiday).</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('jadwal.index') }}" class="btn btn-blue-outline">
            <i class="bi bi-calendar-check"></i>
            <span>Jadwal Kerja</span>
        </a>
        <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addLiburModal">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Hari Libur</span>
        </button>
    </div>
</div>

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 px-3 mb-3 d-flex align-items-center justify-content-between" style="background:#ecfdf5; color:#065f46; border-left: 4px solid #10b981 !important;">
    <div class="d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
        <span>{{ session('success') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="font-size:0.75rem;"></button>
</div>
@endif

<!-- Summary Metric Cards (4 Cards matching Reference Format) -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-blue">
                {{ $totalLibur }}
            </div>
            <div>
                <div class="stat-label-text">Total Hari Libur</div>
                <div class="stat-sublabel-text">Semua agenda tahun ini</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-green">
                {{ $totalNasional }}
            </div>
            <div>
                <div class="stat-label-text">Libur Nasional</div>
                <div class="stat-sublabel-text">Hari libur resmi</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-amber">
                {{ $totalCuti }}
            </div>
            <div>
                <div class="stat-label-text">Cuti Bersama</div>
                <div class="stat-sublabel-text">Agenda cuti pemerintah</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-purple">
                {{ $totalUpcoming }}
            </div>
            <div>
                <div class="stat-label-text">Akan Datang</div>
                <div class="stat-sublabel-text">Mendatang di kalender</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Toolbar Bar -->
<div class="unified-filter-bar">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small fw-medium"><i class="bi bi-funnel me-1"></i> Filter Tahun:</span>
            <form method="GET" action="{{ route('libur.index') }}" class="d-inline">
                <select name="year" class="form-select filter-select-pill" onchange="this.form.submit()" style="min-width: 140px;">
                    <option value="all">Semua Tahun</option>
                    @foreach ($tahunList as $yr)
                        <option value="{{ $yr }}" {{ (string)$selectedYear === (string)$yr ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <span class="text-muted small fw-medium">
            {{ count($liburList) }} hari libur ditemukan
        </span>
    </div>
</div>

<!-- Tabel Hari Libur -->
<div class="unified-card">
    <div class="table-responsive">
        <table class="unified-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">NO</th>
                    <th>NAMA HARI LIBUR / PERISTIWA</th>
                    <th>TANGGAL KALENDER</th>
                    <th>HARI</th>
                    <th>JENIS LIBUR</th>
                    <th>STATUS GAJI</th>
                    <th>STATUS KALENDER</th>
                    <th style="width: 90px; text-align: center;">AKSI</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($liburList as $l)
                @php 
                    $isPast = !cekUpcoming($l->TANGGAL);
                    $isPaid = (int)$l->IS_DIBAYAR;
                @endphp
                <tr class="{{ $isPast ? 'opacity-75' : '' }}">
                    <td style="text-align: center; color: #94a3b8; font-size: 0.82rem;">
                        {{ $loop->iteration }}
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $l->KETERANGAN }}</div>
                    </td>
                    <td>
                        <span class="text-dark"><i class="bi bi-calendar3 text-primary me-1"></i>{{ \Carbon\Carbon::parse($l->TANGGAL)->locale('id')->translatedFormat('d F Y') }}</span>
                    </td>
                    <td>
                        <span class="text-muted small fw-medium">{{ \Carbon\Carbon::parse($l->TANGGAL)->locale('id')->translatedFormat('l') }}</span>
                    </td>
                    <td>
                        @if ($l->JENIS_LIBUR === 'Nasional')
                            <span class="soft-badge" style="background:#fff1f2; color:#e11d48; border:1px solid #ffe4e6;">Nasional</span>
                        @elseif ($l->JENIS_LIBUR === 'Cuti Bersama')
                            <span class="soft-badge badge-kontrak-soft">Cuti Bersama</span>
                        @else
                            <span class="soft-badge badge-harian-soft">Khusus</span>
                        @endif
                    </td>
                    <td>
                        @if ($isPaid === 1)
                            <span class="status-dot-indicator dot-aktif">Dibayar (Paid)</span>
                        @else
                            <span class="status-dot-indicator dot-nonaktif">Unpaid</span>
                        @endif
                    </td>
                    <td>
                        @if (cekUpcoming($l->TANGGAL))
                            <span class="soft-badge badge-staff-soft"><i class="bi bi-clock me-1"></i>Akan Datang</span>
                        @else
                            <span class="soft-badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;">Sudah Lewat</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <button class="action-icon-btn" data-bs-toggle="modal" data-bs-target="#editLibur{{ $l->ID_LIBUR }}" title="Edit Hari Libur">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form method="POST" action="{{ route('libur.destroy', $l->ID_LIBUR) }}" class="d-inline" onsubmit="return confirm('Hapus hari libur {{ addslashes($l->KETERANGAN) }} dari database?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-icon-btn btn-delete" title="Hapus"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Modal Edit Libur -->
                <div class="modal fade" id="editLibur{{ $l->ID_LIBUR }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="POST" action="{{ route('libur.update', $l->ID_LIBUR) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Hari Libur</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 text-start">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Nama Hari Libur / Peristiwa</label>
                                        <input type="text" class="form-control filter-select-pill" name="nama_libur" value="{{ $l->KETERANGAN }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Tanggal</label>
                                        <input type="date" class="form-control filter-select-pill" name="tanggal" value="{{ $l->TANGGAL }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Jenis Libur (JENIS_LIBUR)</label>
                                        <select class="form-select filter-select-pill" name="jenis" required>
                                            <option value="Nasional" {{ $l->JENIS_LIBUR === 'Nasional' ? 'selected' : '' }}>Libur Nasional Resmi</option>
                                            <option value="Cuti Bersama" {{ $l->JENIS_LIBUR === 'Cuti Bersama' ? 'selected' : '' }}>Cuti Bersama Pemerintah</option>
                                            <option value="Khusus Perusahaan" {{ $l->JENIS_LIBUR === 'Khusus Perusahaan' ? 'selected' : '' }}>Khusus Perusahaan</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Perhitungan Upah / Gaji</label>
                                        <select class="form-select filter-select-pill" name="is_dibayar" required>
                                            <option value="1" {{ $isPaid === 1 ? 'selected' : '' }}>Dibayar Penuh (Paid Holiday)</option>
                                            <option value="0" {{ $isPaid === 0 ? 'selected' : '' }}>Tidak Dibayar (Unpaid)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-orange px-4">Simpan Perubahan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-muted opacity-50"></i>
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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus text-primary me-2"></i>Tambah Hari Libur Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Nama Hari Libur / Peristiwa</label>
                        <input type="text" class="form-control filter-select-pill" name="nama_libur" required placeholder="contoh: Hari Kemerdekaan RI">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Tanggal Kalender</label>
                        <input type="date" class="form-control filter-select-pill" name="tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Jenis Libur (JENIS_LIBUR)</label>
                        <select class="form-select filter-select-pill" name="jenis" required>
                            <option value="Nasional" selected>Libur Nasional Resmi</option>
                            <option value="Cuti Bersama">Cuti Bersama Pemerintah</option>
                            <option value="Khusus Perusahaan">Khusus Perusahaan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Perhitungan Upah / Gaji</label>
                        <select class="form-select filter-select-pill" name="is_dibayar" required>
                            <option value="1" selected>Dibayar Penuh (Paid Holiday)</option>
                            <option value="0">Tidak Dibayar (Unpaid)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-orange px-4">Simpan Hari Libur</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

