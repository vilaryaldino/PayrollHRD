@extends('layouts.app')

@section('title', 'Data Pegawai')

@section('breadcrumb_parent')
<span>Master</span>
<span class="separator">/</span>
@endsection

@section('breadcrumb_active', 'Master Data Pegawai')

@php
function getInitials($name) {
    $clean = trim(preg_replace('/[^a-zA-Z\s]/', '', $name));
    $words = explode(' ', $clean);
    if (count($words) >= 2 && !empty($words[0]) && !empty($words[1])) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
    return strtoupper(substr($clean, 0, 2) ?: 'PG');
}
@endphp

@section('content')
<!-- Page Header Section (Matching Screenshot) -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 page-title-section">
    <div>
        <h1>Data Pegawai</h1>
        <p>Kelola data pegawai, jabatan, divisi, dan shift kerja.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('jadwal.index') }}" class="btn btn-blue-outline">
            <i class="bi bi-calendar-check"></i>
            <span>Jadwal Kerja</span>
        </a>
        <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addPegawaiModal">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Pegawai</span>
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

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 px-3 mb-3 d-flex align-items-center justify-content-between" style="background:#fff1f2; color:#9f1239; border-left: 4px solid #e11d48 !important;">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-circle-fill me-2 fs-5 text-danger"></i>
        <span><strong>Validasi Gagal:</strong> {{ session('error') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="font-size:0.75rem;"></button>
</div>
@endif

<!-- Summary Metric Cards (4 Cards matching Reference Image) -->
<div class="row g-3 mb-4">
    <!-- Total Pegawai -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-blue">
                {{ $totalPegawai }}
            </div>
            <div>
                <div class="stat-label-text">Total Pegawai</div>
                <div class="stat-sublabel-text">Database Master</div>
            </div>
        </div>
    </div>

    <!-- Pegawai Aktif -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-green">
                {{ $totalAktif }}
            </div>
            <div>
                <div class="stat-label-text">Pegawai Aktif</div>
                <div class="stat-sublabel-text">Status aktif bekerja</div>
            </div>
        </div>
    </div>

    <!-- Status Staff -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-indigo">
                {{ $totalStaff }}
            </div>
            <div>
                <div class="stat-label-text">Status Staff</div>
                <div class="stat-sublabel-text">Pegawai Bulanan</div>
            </div>
        </div>
    </div>

    <!-- Status Harian -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-purple">
                {{ $totalHarian }}
            </div>
            <div>
                <div class="stat-label-text">Status Harian</div>
                <div class="stat-sublabel-text">Shift Operasional</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search Toolbar Bar (Matching Reference Image) -->
<div class="unified-filter-bar">
    <form method="GET" action="{{ route('pegawai.index') }}" class="row g-2 align-items-center">
        <!-- Search Box -->
        <div class="col-lg-4 col-md-5 col-sm-12">
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem;"></i>
                <input type="text" class="form-control search-input-pill ps-5" name="search"
                       value="{{ $searchQuery }}" placeholder="Cari nama pegawai, ID mesin, alamat...">
            </div>
        </div>

        <!-- Filter Divisi -->
        <div class="col-lg-2 col-md-3 col-sm-6">
            <select name="filter_divisi" class="form-select filter-select-pill" onchange="this.form.submit()">
                <option value="">Filter Divisi</option>
                @foreach ($masterDivisi as $div)
                    <option value="{{ $div->ID_DIVISI }}" {{ (string)$filterDivisi === (string)$div->ID_DIVISI ? 'selected' : '' }}>
                        {{ $div->NAMA_DIVISI }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filter Status -->
        <div class="col-lg-2 col-md-3 col-sm-6">
            <select name="filter_status" class="form-select filter-select-pill" onchange="this.form.submit()">
                <option value="">Filter Status</option>
                <option value="1" {{ (string)$filterStatus === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ (string)$filterStatus === '0' ? 'selected' : '' }}>Non-Aktif / Resign</option>
            </select>
        </div>

        <!-- Counter & Actions -->
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
                {{ $pegawaiList->total() }} pegawai ditemukan
            </span>
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-light border px-3" title="Terapkan Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                @if ($filterDivisi !== '' && $filterDivisi !== null || $filterStatus !== '' && $filterStatus !== null || $searchQuery !== '' && $searchQuery !== null)
                    <a href="{{ route('pegawai.index') }}" class="btn btn-sm btn-light border text-danger" title="Reset Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Main Table Container (Matching Reference Image) -->
<div class="unified-card">
    <div class="table-responsive">
        <table class="unified-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">NO</th>
                    <th>NAMA PEGAWAI</th>
                    <th>DIVISI</th>
                    <th>JABATAN</th>
                    <th>SHIFT AKTIF / REGU</th>
                    <th>STATUS</th>
                    <th style="width: 100px; text-align: center;">AKSI</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($pegawaiList as $p)
                @php 
                    $isAktif = (int)$p->IS_AKTIF;
                    $initials = getInitials($p->NM_PEGAWAI);
                    $empCode = 'EMP-' . str_pad($p->ID_PEGAWAI, 4, '0', STR_PAD_LEFT);
                @endphp
                <tr class="{{ !$isAktif ? 'opacity-75' : '' }}">
                    <td style="text-align: center; color: #94a3b8; font-size: 0.82rem;">
                        {{ $loop->iteration }}
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="table-avatar-circle">
                                {{ $initials }}
                            </div>
                            <div>
                                <div class="table-user-name">{{ $p->NM_PEGAWAI }}</div>
                                <div class="table-user-meta">
                                    <span>{{ $empCode }}</span>
                                    @if($p->ID_PEGAWAI_MESIN)
                                        <span class="mx-1">•</span>
                                        <span>PIN: {{ $p->ID_PEGAWAI_MESIN }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="fw-semibold text-dark small">{{ $p->NAMA_DIVISI ?? '-' }}</span>
                    </td>
                    <td>
                        @if ($p->JENIS_PEGAWAI === 'Staff')
                            <span class="soft-badge badge-staff-soft">Staff</span>
                        @elseif ($p->JENIS_PEGAWAI === 'Harian')
                            <span class="soft-badge badge-harian-soft">Harian</span>
                        @else
                            <span class="soft-badge badge-kontrak-soft">{{ $p->JENIS_PEGAWAI ?: 'Kontrak' }}</span>
                        @endif
                    </td>
                    <td>
                        @if ($p->NAMA_KELOMPOK)
                            <span class="status-dot-indicator dot-shift-pagi">{{ $p->NAMA_KELOMPOK }}</span>
                        @elseif ($p->JENIS_PEGAWAI === 'Staff')
                            <span class="status-dot-indicator dot-shift-pagi">Pagi (Reguler)</span>
                        @else
                            <span class="status-dot-indicator dot-shift-pagi">Pagi</span>
                        @endif
                    </td>
                    <td>
                        @if ($isAktif === 1)
                            <span class="status-dot-indicator dot-aktif">Aktif</span>
                        @else
                            <span class="status-dot-indicator dot-nonaktif">Non-Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <!-- View Detail Button -->
                            <button class="action-icon-btn" title="Lihat Detail Pegawai"
                                    data-bs-toggle="modal" data-bs-target="#viewModal{{ $p->ID_PEGAWAI }}">
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- Edit Button -->
                            <button class="action-icon-btn" title="Edit Pegawai"
                                    data-bs-toggle="modal" data-bs-target="#editModal{{ $p->ID_PEGAWAI }}">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <!-- Delete Form -->
                            <form method="POST" action="{{ route('pegawai.destroy', $p->ID_PEGAWAI) }}" class="d-inline" onsubmit="return confirm('Hapus data pegawai {{ addslashes($p->NM_PEGAWAI) }} dari database?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-icon-btn btn-delete" title="Hapus Pegawai">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Modal Detail Pegawai -->
                <div class="modal fade" id="viewModal{{ $p->ID_PEGAWAI }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-person-badge text-primary me-2"></i>Detail Data Pegawai
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                    <div class="table-avatar-circle" style="width: 52px; height: 52px; font-size: 1.2rem;">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">{{ $p->NM_PEGAWAI }}</h5>
                                        <div class="text-muted small">{{ $empCode }} &bull; PIN Mesin: {{ $p->ID_PEGAWAI_MESIN ?: '-' }}</div>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-muted small">Divisi Kerja</div>
                                        <div class="fw-semibold text-dark">{{ $p->NAMA_DIVISI ?? '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Kelompok / Regu</div>
                                        <div class="fw-semibold text-dark">{{ $p->NAMA_KELOMPOK ?? '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Jenis Pegawai</div>
                                        <div class="fw-semibold text-dark">{{ $p->JENIS_PEGAWAI ?? '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Status Kerja</div>
                                        <div>
                                            @if ($isAktif === 1)
                                                <span class="status-dot-indicator dot-aktif">Aktif Bekerja</span>
                                            @else
                                                <span class="status-dot-indicator dot-nonaktif">Non-Aktif / Resign</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-muted small">No. HP / WhatsApp</div>
                                        <div class="fw-semibold text-dark">{{ $p->NO_TELP_HP ?: '-' }}</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-muted small">Alamat Tinggal</div>
                                        <div class="fw-semibold text-dark">{{ $p->ALAMAT ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Tutup</button>
                                <button type="button" class="btn btn-sm btn-orange" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editModal{{ $p->ID_PEGAWAI }}">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Pegawai -->
                <div class="modal fade" id="editModal{{ $p->ID_PEGAWAI }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <form method="POST" action="{{ route('pegawai.update', $p->ID_PEGAWAI) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="bi bi-pencil-square text-primary me-2"></i>Edit Pegawai: {{ $p->NM_PEGAWAI }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3 text-start">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Nama Lengkap (NM_PEGAWAI)</label>
                                            <input type="text" class="form-control filter-select-pill" name="nama" value="{{ $p->NM_PEGAWAI }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">ID Mesin Fingerprint (ID_PEGAWAI_MESIN)</label>
                                            <input type="text" class="form-control filter-select-pill" name="id_pegawai_mesin" value="{{ $p->ID_PEGAWAI_MESIN ?? '' }}" placeholder="contoh: FP001">
                                            <div class="form-text" style="font-size: 0.72rem; color: #64748b;">
                                                <i class="bi bi-info-circle me-1"></i>Boleh menggunakan ID Mesin dari pegawai yang berstatus <strong>Resign</strong>.
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Divisi (M_DIVISI)</label>
                                            <select class="form-select filter-select-pill" name="id_divisi" required>
                                                @foreach ($masterDivisi as $div)
                                                    <option value="{{ $div->ID_DIVISI }}" {{ (int)$p->ID_DIVISI === (int)$div->ID_DIVISI ? 'selected' : '' }}>
                                                        {{ $div->NAMA_DIVISI }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Kelompok / Regu Kerja (M_KELOMPOK)</label>
                                            <select class="form-select filter-select-pill" name="id_kelompok">
                                                <option value="">-- Tanpa Kelompok --</option>
                                                @foreach ($masterKelompok as $kel)
                                                    <option value="{{ $kel->ID_KELOMPOK }}" {{ (int)$p->ID_KELOMPOK === (int)$kel->ID_KELOMPOK ? 'selected' : '' }}>
                                                        {{ $kel->NAMA_KELOMPOK }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Jenis Pegawai (JENIS_PEGAWAI)</label>
                                            <select class="form-select filter-select-pill" name="jenis" required>
                                                <option value="Staff" {{ $p->JENIS_PEGAWAI === 'Staff' ? 'selected' : '' }}>Staff (Bulanan)</option>
                                                <option value="Harian" {{ $p->JENIS_PEGAWAI === 'Harian' ? 'selected' : '' }}>Harian (Shift Reguler)</option>
                                                <option value="Kontrak" {{ $p->JENIS_PEGAWAI === 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Status Kepegawaian (IS_AKTIF)</label>
                                            <select class="form-select filter-select-pill" name="is_aktif" required>
                                                <option value="1" {{ $isAktif === 1 ? 'selected' : '' }}>Aktif Bekerja</option>
                                                <option value="0" {{ $isAktif === 0 ? 'selected' : '' }}>Non-Aktif / Resign</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Nomor HP / WhatsApp (NO_TELP_HP)</label>
                                            <input type="text" class="form-control filter-select-pill" name="no_telp" value="{{ $p->NO_TELP_HP ?? '' }}" placeholder="08xxxxxxxxx">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Alamat Tinggal (ALAMAT)</label>
                                            <input type="text" class="form-control filter-select-pill" name="alamat" value="{{ $p->ALAMAT ?? '' }}" placeholder="Alamat domisili">
                                        </div>
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
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-muted opacity-50"></i>
                        Tidak ada data pegawai yang sesuai kriteria pencarian / filter.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer / Pagination -->
    <div class="card-footer bg-white py-3 border-top-0 border-bottom-0" style="border-radius: 0 0 12px 12px;">
        {{ $pegawaiList->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Modal Tambah Pegawai (Matching Unified Theme) -->
<div class="modal fade" id="addPegawaiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('pegawai.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus-fill text-primary me-2"></i>Tambah Data Pegawai Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nama Lengkap (NM_PEGAWAI)</label>
                            <input type="text" class="form-control filter-select-pill" name="nama" required placeholder="contoh: Andi Wijaya">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">ID Mesin Fingerprint (ID_PEGAWAI_MESIN)</label>
                            <input type="text" class="form-control filter-select-pill" name="id_pegawai_mesin" placeholder="contoh: FP007">
                            <div class="form-text" style="font-size: 0.72rem; color: #64748b;">
                                <i class="bi bi-info-circle me-1"></i>Boleh menggunakan ID Mesin dari pegawai yang sudah <strong>Resign</strong>.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Divisi (M_DIVISI)</label>
                            <select class="form-select filter-select-pill" name="id_divisi" required>
                                <option value="">-- Pilih Divisi --</option>
                                @foreach ($masterDivisi as $div)
                                    <option value="{{ $div->ID_DIVISI }}">{{ $div->NAMA_DIVISI }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Kelompok / Regu Kerja (M_KELOMPOK)</label>
                            <select class="form-select filter-select-pill" name="id_kelompok">
                                <option value="">-- Tanpa Kelompok --</option>
                                @foreach ($masterKelompok as $kel)
                                    <option value="{{ $kel->ID_KELOMPOK }}">{{ $kel->NAMA_KELOMPOK }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Jenis Pegawai (JENIS_PEGAWAI)</label>
                            <select class="form-select filter-select-pill" name="jenis" required>
                                <option value="Harian" selected>Harian (Shift Reguler)</option>
                                <option value="Staff">Staff (Bulanan)</option>
                                <option value="Kontrak">Kontrak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Status Kepegawaian (IS_AKTIF)</label>
                            <select class="form-select filter-select-pill" name="is_aktif" required>
                                <option value="1" selected>Aktif Bekerja</option>
                                <option value="0">Non-Aktif / Resign</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nomor HP / WhatsApp (NO_TELP_HP)</label>
                            <input type="text" class="form-control filter-select-pill" name="no_telp" placeholder="contoh: 081234567890">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Alamat Tinggal (ALAMAT)</label>
                            <input type="text" class="form-control filter-select-pill" name="alamat" placeholder="Jl. Raya No...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-orange px-4">Simpan Pegawai</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

