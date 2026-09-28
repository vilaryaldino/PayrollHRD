@extends('layouts.app')

@section('title', 'Master Shift Kerja')

@section('breadcrumb_parent')
<span>Master</span>
<span class="separator">/</span>
@endsection

@section('breadcrumb_active', 'Shift Kerja')

@php
function hitungDurasiShift($mulai, $selesai) {
    $m = strtotime($mulai);
    $s = strtotime($selesai);
    if ($s <= $m) {
        $s += 86400; // Lintas hari
    }
    $diff = ($s - $m) / 3600;
    return number_format($diff, 1) . ' jam';
}
@endphp

@section('content')
<!-- Page Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 page-title-section">
    <div>
        <h1>Master Shift Kerja</h1>
        <p>Konfigurasi jam kerja operasional, rentang toleransi scan absen, dan shift kerja lintas hari.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('jadwal.index') }}" class="btn btn-blue-outline">
            <i class="bi bi-calendar-check"></i>
            <span>Jadwal Roster</span>
        </a>
        <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addShiftModal">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Shift Baru</span>
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

<!-- Summary Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-4 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-blue">
                {{ count($shifts) }}
            </div>
            <div>
                <div class="stat-label-text">Total Pola Shift</div>
                <div class="stat-sublabel-text">Terdaftar di sistem</div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-green">
                {{ $shifts->where('IS_OVERNIGHT', 0)->count() }}
            </div>
            <div>
                <div class="stat-label-text">Shift Reguler</div>
                <div class="stat-sublabel-text">Pagi & Sore normal</div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-purple">
                {{ $shifts->where('IS_OVERNIGHT', 1)->count() }}
            </div>
            <div>
                <div class="stat-label-text">Shift Lintas Hari</div>
                <div class="stat-sublabel-text">Melewati jam 00:00 malam</div>
            </div>
        </div>
    </div>
</div>

<!-- Shift Cards Grid -->
<div class="row g-3 mb-4">
    @foreach ($shifts as $s)
        @php 
            $color = $s->WARNA_LABEL ?? '#f59e0b';
            $isOvernight = !empty($s->IS_OVERNIGHT);
        @endphp
    <div class="col-xl-4 col-md-6">
        <div class="unified-card h-100 position-relative mb-0">
            <div style="height: 4px; background-color: {{ $color }}; width: 100%;"></div>
            <div class="p-4 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="soft-badge" style="background-color: {{ $color }}15; color: {{ $color }}; border: 1px solid {{ $color }}30;">
                                {{ $s->SHIFT_CODE }}
                            </span>
                            <h5 class="fw-bold text-dark mt-2 mb-0" style="font-size: 1.05rem;">{{ $s->NAMA_SHIFT }}</h5>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="action-icon-btn" data-bs-toggle="modal" data-bs-target="#editShift{{ $s->ID_JADWAL }}" title="Edit Shift">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form method="POST" action="{{ route('shift.destroy', $s->ID_JADWAL) }}" class="d-inline" onsubmit="return confirm('Hapus shift {{ addslashes($s->NAMA_SHIFT) }} dari database?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-icon-btn btn-delete" title="Hapus Shift">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Time Box -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="bi bi-clock text-primary me-1"></i> Jam Kerja:</span>
                            <span class="fw-bold text-dark">{{ substr($s->JAM_MULAI, 0, 5) }} - {{ substr($s->JAM_SELESAI, 0, 5) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.76rem;">
                            <span><i class="bi bi-fingerprint text-muted me-1"></i> Rentang Scan:</span>
                            <span>{{ substr($s->JAM_AWAL, 0, 5) }} - {{ substr($s->JAM_AKHIR, 0, 5) }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="text-muted small">
                        <i class="bi bi-hourglass-split me-1"></i> Durasi: <strong class="text-dark">{{ hitungDurasiShift($s->JAM_MULAI, $s->JAM_SELESAI) }}</strong>
                    </span>
                    @if ($isOvernight)
                        <span class="soft-badge badge-harian-soft"><i class="bi bi-moon-stars me-1"></i> Lintas Hari</span>
                    @else
                        <span class="soft-badge badge-staff-soft"><i class="bi bi-sun me-1"></i> Normal</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal Edit Shift -->
        <div class="modal fade" id="editShift{{ $s->ID_JADWAL }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('shift.update', $s->ID_JADWAL) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Shift Kerja</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3 text-start">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark">Kode Shift (SHIFT_CODE)</label>
                                    <input type="text" class="form-control filter-select-pill" name="shift_code" value="{{ $s->SHIFT_CODE }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark">Warna Label</label>
                                    <input type="color" class="form-control form-control-color filter-select-pill w-100 p-1" name="warna" value="{{ $s->WARNA_LABEL ?? '#f59e0b' }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark">Nama Shift (NAMA_SHIFT)</label>
                                    <input type="text" class="form-control filter-select-pill" name="nama_shift" value="{{ $s->NAMA_SHIFT }}" required>
                                </div>

                                <div class="col-6">
                                    <label class="form-label small fw-bold text-success">Jam Mulai</label>
                                    <input type="time" class="form-control filter-select-pill" name="jam_mulai" value="{{ substr($s->JAM_MULAI, 0, 5) }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-danger">Jam Selesai</label>
                                    <input type="time" class="form-control filter-select-pill" name="jam_selesai" value="{{ substr($s->JAM_SELESAI, 0, 5) }}" required>
                                </div>

                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">Batas Scan Awal</label>
                                    <input type="time" class="form-control filter-select-pill" name="jam_awal" value="{{ substr($s->JAM_AWAL, 0, 5) }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">Batas Scan Akhir</label>
                                    <input type="time" class="form-control filter-select-pill" name="jam_akhir" value="{{ substr($s->JAM_AKHIR, 0, 5) }}" required>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_overnight" id="chkOvernight{{ $s->ID_JADWAL }}" {{ $isOvernight ? 'checked' : '' }}>
                                        <label class="form-check-label small text-dark fw-medium" for="chkOvernight{{ $s->ID_JADWAL }}">
                                            Shift Lintas Hari (Melewati jam 00:00 tengah malam)
                                        </label>
                                    </div>
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
    </div>
    @endforeach
</div>

<!-- Modal Tambah Shift Baru -->
<div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('shift.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Shift Kerja Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Kode Shift (SHIFT_CODE)</label>
                            <input type="text" class="form-control filter-select-pill" name="shift_code" placeholder="contoh: SH-SORE2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Warna Label</label>
                            <input type="color" class="form-control form-control-color filter-select-pill w-100 p-1" name="warna" value="#f59e0b">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Nama Shift (NAMA_SHIFT)</label>
                            <input type="text" class="form-control filter-select-pill" name="nama_shift" placeholder="contoh: Shift Operasional 2" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-bold text-success">Jam Mulai</label>
                            <input type="time" class="form-control filter-select-pill" name="jam_mulai" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-danger">Jam Selesai</label>
                            <input type="time" class="form-control filter-select-pill" name="jam_selesai" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Batas Scan Awal</label>
                            <input type="time" class="form-control filter-select-pill" name="jam_awal" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Batas Scan Akhir</label>
                            <input type="time" class="form-control filter-select-pill" name="jam_akhir" required>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_overnight" id="chkAddOvernight">
                                <label class="form-check-label small text-dark fw-medium" for="chkAddOvernight">
                                    Shift Lintas Hari (Melewati tengah malam)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-orange px-4">Simpan Shift</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

