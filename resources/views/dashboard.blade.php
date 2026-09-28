@extends('layouts.app')

@section('title', 'Dashboard HRD')

@section('breadcrumb_parent', '')
@section('breadcrumb_active', 'Dashboard')

@section('content')
<!-- Page Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 page-title-section">
    <div>
        <h1>Dashboard HRD</h1>
        <p>Ringkasan operasional data kepegawaian, jadwal shift, dan absensi.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="soft-badge badge-staff-soft px-3 py-2 fw-semibold" style="font-size: 0.82rem;">
            <i class="bi bi-calendar-event me-1"></i> {{ date('d F Y') }}
        </span>
    </div>
</div>

<!-- Summary Cards Row (Matching Unified Format) -->
<div class="row g-3 mb-4">
    <!-- Total Pegawai Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-blue">
                {{ $totalPegawai }}
            </div>
            <div>
                <div class="stat-label-text">Total Pegawai</div>
                <div class="stat-sublabel-text text-success"><i class="bi bi-check-circle-fill"></i> {{ $totalAktif }} Aktif Bekerja</div>
            </div>
        </div>
    </div>

    <!-- Jadwal Kerja Hari Ini Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-green">
                {{ $jadwalHariIni }}
            </div>
            <div>
                <div class="stat-label-text">Jadwal Hari Ini</div>
                <div class="stat-sublabel-text">Pegawai terjadwal shift</div>
            </div>
        </div>
    </div>

    <!-- Master Shift Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-indigo">
                {{ $totalShift }}
            </div>
            <div>
                <div class="stat-label-text">Master Shift</div>
                <div class="stat-sublabel-text">Pola jam kerja aktif</div>
            </div>
        </div>
    </div>

    <!-- Libur Nasional Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-unified">
            <div class="stat-number-box stat-number-amber">
                {{ $totalLibur }}
            </div>
            <div>
                <div class="stat-label-text">Libur Nasional {{ date('Y') }}</div>
                <div class="stat-sublabel-text text-truncate" style="max-width: 140px;" title="{{ $nextLibur ? $nextLibur->KETERANGAN : 'Tidak ada agenda' }}">
                    {{ $nextLibur ? $nextLibur->KETERANGAN : 'Tidak ada agenda' }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Section & Quick Status -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="unified-card mb-0">
            <div class="unified-card-header">
                <h6 class="m-0 fw-bold text-dark"><i class="bi bi-check-circle-fill me-2 text-success"></i>Status Integrasi Sistem & Database</h6>
                <span class="soft-badge badge-staff-soft">Realtime Connected</span>
            </div>
            <div class="p-4">
                <div class="alert alert-success border-0 rounded-3 d-flex align-items-center mb-4" style="background:#ecfdf5; color:#065f46;">
                    <i class="bi bi-database-check fs-4 me-3 text-success"></i>
                    <div>
                        <strong class="d-block">Database MySQL Aktif:</strong>
                        <span class="small">Tersambung langsung ke database <code>payrollhrd</code> di MySQL / phpMyAdmin.</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border text-center h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-people fs-3 text-primary d-block mb-1"></i>
                            <span class="fw-bold d-block text-dark small">Data Pegawai</span>
                            <span class="text-muted" style="font-size: 0.75rem;">Tabel <code>M_PEGAWAI</code></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border text-center h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-clock-history fs-3 text-indigo d-block mb-1" style="color:#6366f1;"></i>
                            <span class="fw-bold d-block text-dark small">Shift Kerja</span>
                            <span class="text-muted" style="font-size: 0.75rem;">Tabel <code>M_SHIFT</code></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border text-center h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-calendar-check fs-3 text-success d-block mb-1"></i>
                            <span class="fw-bold d-block text-dark small">Jadwal Roster</span>
                            <span class="text-muted" style="font-size: 0.75rem;">Tabel <code>T_JADWAL_KERJA</code></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="unified-card mb-0">
            <div class="unified-card-header">
                <h6 class="m-0 fw-bold text-dark"><i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Aksi Cepat</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <a href="{{ route('pegawai.index') }}" class="btn btn-light border text-start p-2 d-flex align-items-center justify-content-between text-decoration-none rounded-3" style="font-size: 0.86rem; color: #1e293b;">
                    <div><i class="bi bi-person-plus text-primary me-2"></i> Tambah Pegawai Baru</div>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
                <a href="{{ route('jadwal.index') }}" class="btn btn-light border text-start p-2 d-flex align-items-center justify-content-between text-decoration-none rounded-3" style="font-size: 0.86rem; color: #1e293b;">
                    <div><i class="bi bi-calendar-range text-success me-2"></i> Atur Jadwal Shift Mingguan</div>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
                <a href="{{ route('libur.index') }}" class="btn btn-light border text-start p-2 d-flex align-items-center justify-content-between text-decoration-none rounded-3" style="font-size: 0.86rem; color: #1e293b;">
                    <div><i class="bi bi-calendar-plus text-danger me-2"></i> Tambah Libur Nasional</div>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
                <a href="{{ route('lembur.register') }}" class="btn btn-light border text-start p-2 d-flex align-items-center justify-content-between text-decoration-none rounded-3" style="font-size: 0.86rem; color: #1e293b;">
                    <div><i class="bi bi-moon-stars text-warning me-2"></i> Form Register Lembur</div>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

