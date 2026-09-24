@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 text-gray-800 m-0">Dashboard HRD</h2>
        <small class="text-muted">Ringkasan operasional data kepegawaian dan jadwal shift</small>
    </div>
    <span class="badge bg-light text-dark border p-2"><i class="bi bi-calendar-event me-1 text-primary"></i> {{ date('d F Y') }}</span>
</div>

<!-- Summary Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Pegawai Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-primary border-4 rounded-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="font-size: 0.8rem; font-weight: 700;">
                            Total Pegawai
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $totalPegawai }}</div>
                        <small class="text-success"><i class="bi bi-check-circle"></i> {{ $totalAktif }} Aktif</small>
                    </div>
                    <div>
                        <i class="bi bi-people text-primary opacity-50" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('pegawai.index') }}" class="text-decoration-none text-primary small">Kelola Pegawai <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Jadwal Kerja Hari Ini Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-success border-4 rounded-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.8rem; font-weight: 700;">
                            Jadwal Kerja Hari Ini
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $jadwalHariIni }}</div>
                        <small class="text-muted">Pegawai terjadwal shift</small>
                    </div>
                    <div>
                        <i class="bi bi-calendar-check text-success opacity-50" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('jadwal.index') }}" class="text-decoration-none text-success small">Lihat Roster <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Master Shift Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-info border-4 rounded-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1" style="font-size: 0.8rem; font-weight: 700;">
                            Master Shift
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $totalShift }}</div>
                        <small class="text-muted">Pola jam kerja</small>
                    </div>
                    <div>
                        <i class="bi bi-clock-history text-info opacity-50" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('shift.index') }}" class="text-decoration-none text-info small">Atur Shift <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Libur Nasional Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-danger border-4 rounded-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1" style="font-size: 0.8rem; font-weight: 700;">
                            Libur Nasional {{ date('Y') }}
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $totalLibur }}</div>
                        <small class="text-muted text-truncate d-block" style="max-width: 140px;">
                            {{ $nextLibur ? $nextLibur->KETERANGAN : 'Tidak ada agenda' }}
                        </small>
                    </div>
                    <div>
                        <i class="bi bi-calendar-event text-danger opacity-50" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('libur.index') }}" class="text-decoration-none text-danger small">Agenda Libur <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Section & Quick Status -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-check-circle-fill me-2"></i>Status Integrasi Sistem & Database</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-3">
                    <i class="bi bi-database-check fs-4 me-3"></i>
                    <div>
                        <strong>Database MySQL Aktif:</strong> Terhubung langsung ke <code>payrollhrd</code> di MySQL / phpMyAdmin.
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded border text-center">
                            <i class="bi bi-people fs-4 text-primary d-block mb-1"></i>
                            <span class="fw-bold d-block small">Data Pegawai</span>
                            <span class="text-muted" style="font-size: 0.75rem;">Terhubung ke M_PEGAWAI</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded border text-center">
                            <i class="bi bi-clock-history fs-4 text-info d-block mb-1"></i>
                            <span class="fw-bold d-block small">Shift Kerja</span>
                            <span class="text-muted" style="font-size: 0.75rem;">Terhubung ke M_SHIFT</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded border text-center">
                            <i class="bi bi-calendar-check fs-4 text-success d-block mb-1"></i>
                            <span class="fw-bold d-block small">Jadwal Roster</span>
                            <span class="text-muted" style="font-size: 0.75rem;">Terhubung ke T_JADWAL_KERJA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="bi bi-lightning-charge me-1 text-warning"></i>Aksi Cepat</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('pegawai.index') }}" class="btn btn-outline-primary text-start btn-sm p-2">
                    <i class="bi bi-person-plus me-2"></i> Tambah Pegawai Baru
                </a>
                <a href="{{ route('jadwal.index') }}" class="btn btn-outline-success text-start btn-sm p-2">
                    <i class="bi bi-calendar-range me-2"></i> Atur Jadwal Shift Mingguan
                </a>
                <a href="{{ route('libur.index') }}" class="btn btn-outline-danger text-start btn-sm p-2">
                    <i class="bi bi-calendar-plus me-2"></i> Tambah Libur Nasional
                </a>
                <a href="#" class="btn btn-outline-warning text-dark text-start btn-sm p-2">
                    <i class="bi bi-moon-stars me-2"></i> Form Register Lembur
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
