@extends('layouts.app')

@section('title', 'Dashboard HRD')

@section('breadcrumb_parent', '')
@section('breadcrumb_active', 'Dashboard')

@section('content')
<!-- Page Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-3 page-title-section">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: #1e1e2d; letter-spacing: -0.5px;">Dashboard HRD</h1>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">Ringkasan operasional data kepegawaian, jadwal shift, dan absensi.</p>
    </div>
    <div class="d-flex align-items-center bg-white px-3 py-2 rounded-pill shadow-sm border">
        <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; background-color: #eff6ff;">
            <i class="bi bi-calendar-event text-primary"></i>
        </div>
        <span class="fw-semibold text-dark" style="font-size: 0.95rem;">{{ date('d F Y') }}</span>
    </div>
</div>

<!-- Summary Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Pegawai Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 h-100 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f4fbff 100%); transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(0, 158, 247, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0.125rem 0.25rem rgba(0,0,0,0.075)';">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-primary opacity-75" style="font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">
                        Total Pegawai
                    </div>
                    <div class="p-3 rounded-circle d-flex align-items-center justify-content-center" style="background: linear-gradient(45deg, #009ef7, #32baff); width: 48px; height: 48px; box-shadow: 0 4px 10px rgba(0, 158, 247, 0.3);">
                        <i class="bi bi-people-fill text-white fs-5"></i>
                    </div>
                </div>
                <div class="h2 mb-1 fw-bold" style="color: #1e1e2d;">{{ $totalPegawai }}</div>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="badge rounded-pill px-2 py-1" style="background-color: #e8f4ff; color: #009ef7;"><i class="bi bi-check-circle-fill me-1"></i> {{ $totalAktif }} Aktif</span>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-4 px-4">
                <a href="{{ route('pegawai.index') }}" class="text-decoration-none fw-semibold d-flex align-items-center" style="color: #009ef7; font-size: 0.9rem;">
                    Kelola Pegawai <i class="bi bi-arrow-right ms-2 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Jadwal Kerja Hari Ini Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 h-100 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f2fbf5 100%); transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(80, 205, 137, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0.125rem 0.25rem rgba(0,0,0,0.075)';">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="text-success opacity-75" style="font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">
                        Jadwal Hari Ini
                    </div>
                    <div class="p-3 rounded-circle d-flex align-items-center justify-content-center" style="background: linear-gradient(45deg, #50cd89, #7de0a7); width: 48px; height: 48px; box-shadow: 0 4px 10px rgba(80, 205, 137, 0.3);">
                        <i class="bi bi-calendar-check-fill text-white fs-5"></i>
                    </div>
                </div>
                <div class="h2 mb-1 fw-bold" style="color: #1e1e2d;">{{ $jadwalHariIni }}</div>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="badge rounded-pill px-2 py-1" style="background-color: #e8f9f0; color: #50cd89;"><i class="bi bi-person-badge-fill me-1"></i> Terjadwal shift</span>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-4 px-4">
                <a href="{{ route('jadwal.index') }}" class="text-decoration-none fw-semibold d-flex align-items-center" style="color: #50cd89; font-size: 0.9rem;">
                    Lihat Roster <i class="bi bi-arrow-right ms-2 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Master Shift Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 h-100 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f8f5ff 100%); transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(114, 57, 234, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0.125rem 0.25rem rgba(0,0,0,0.075)';">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="opacity-75" style="color: #7239ea; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">
                        Master Shift
                    </div>
                    <div class="p-3 rounded-circle d-flex align-items-center justify-content-center" style="background: linear-gradient(45deg, #7239ea, #9d73f7); width: 48px; height: 48px; box-shadow: 0 4px 10px rgba(114, 57, 234, 0.3);">
                        <i class="bi bi-clock-fill text-white fs-5"></i>
                    </div>
                </div>
                <div class="h2 mb-1 fw-bold" style="color: #1e1e2d;">{{ $totalShift }}</div>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="badge rounded-pill px-2 py-1" style="background-color: #f1ecff; color: #7239ea;"><i class="bi bi-gear-fill me-1"></i> Pola jam kerja</span>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-4 px-4">
                <a href="{{ route('shift.index') }}" class="text-decoration-none fw-semibold d-flex align-items-center" style="color: #7239ea; font-size: 0.9rem;">
                    Atur Shift <i class="bi bi-arrow-right ms-2 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Libur Nasional Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 h-100 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #fff8dd 100%); transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(245, 158, 11, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0.125rem 0.25rem rgba(0,0,0,0.075)';">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="opacity-75" style="color: #f59e0b; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">
                        Libur Tahun {{ date('Y') }}
                    </div>
                    <div class="p-3 rounded-circle d-flex align-items-center justify-content-center" style="background: linear-gradient(45deg, #f59e0b, #fcd34d); width: 48px; height: 48px; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);">
                        <i class="bi bi-calendar-x-fill text-white fs-5"></i>
                    </div>
                </div>
                <div class="h2 mb-1 fw-bold" style="color: #1e1e2d;">{{ $totalLibur }}</div>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="badge rounded-pill px-2 py-1 text-truncate" style="background-color: #fff4ce; color: #d97706; max-width: 150px;">
                        <i class="bi bi-info-circle-fill me-1"></i> {{ $nextLibur ? $nextLibur->KETERANGAN : 'Tidak ada agenda' }}
                    </span>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-4 px-4">
                <a href="{{ route('libur.index') }}" class="text-decoration-none fw-semibold d-flex align-items-center" style="color: #d97706; font-size: 0.9rem;">
                    Agenda Libur <i class="bi bi-arrow-right ms-2 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Section & Quick Status -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 h-100" style="transition: box-shadow 0.3s ease;" onmouseover="this.style.boxShadow='0 10px 30px rgba(0,0,0,0.08)';" onmouseout="this.style.boxShadow='0 0.125rem 0.25rem rgba(0,0,0,0.075)';">
            <div class="card-header bg-white py-4 border-0 rounded-top-4 d-flex align-items-center">
                <div class="p-2 rounded-3 me-3" style="background-color: #f4fbff; color: #009ef7;">
                    <i class="bi bi-hdd-network-fill fs-5"></i>
                </div>
                <h5 class="m-0 fw-bold" style="color: #1e1e2d; letter-spacing: -0.5px;">Status Integrasi Sistem</h5>
            </div>
            <div class="card-body px-4 pb-4 pt-0">
                <div class="alert border-0 rounded-4 p-4 mb-4 d-flex align-items-center" style="background: linear-gradient(to right, #f2fbf5, #ffffff); box-shadow: 0 4px 15px rgba(80, 205, 137, 0.05); border-left: 5px solid #50cd89 !important;">
                    <div class="bg-white rounded-circle p-2 shadow-sm me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-database-check text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1" style="color: #1e1e2d;">Database MySQL Aktif</h6>
                        <span class="text-muted" style="font-size: 0.9rem;">Terhubung langsung ke <code class="bg-success px-2 py-1 rounded text-white fw-semibold" style="opacity: 0.9;">payrollhrd</code> di server.</span>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-4 rounded-4 text-center border-0" style="background-color: #f8f9fa; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f4fbff'; this.style.transform='translateY(-2px)';" onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateY(0)';">
                            <i class="bi bi-people-fill fs-2 d-block mb-2" style="color: #009ef7;"></i>
                            <span class="fw-bold d-block text-dark mb-1">Data Pegawai</span>
                            <span class="badge bg-white text-muted border shadow-sm" style="font-size: 0.7rem;">M_PEGAWAI</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 rounded-4 text-center border-0" style="background-color: #f8f9fa; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f8f5ff'; this.style.transform='translateY(-2px)';" onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateY(0)';">
                            <i class="bi bi-clock-fill fs-2 d-block mb-2" style="color: #7239ea;"></i>
                            <span class="fw-bold d-block text-dark mb-1">Shift Kerja</span>
                            <span class="badge bg-white text-muted border shadow-sm" style="font-size: 0.7rem;">M_SHIFT</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 rounded-4 text-center border-0" style="background-color: #f8f9fa; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f2fbf5'; this.style.transform='translateY(-2px)';" onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateY(0)';">
                            <i class="bi bi-calendar-check-fill fs-2 d-block mb-2" style="color: #50cd89;"></i>
                            <span class="fw-bold d-block text-dark mb-1">Jadwal Roster</span>
                            <span class="badge bg-white text-muted border shadow-sm" style="font-size: 0.7rem;">T_JADWAL_KERJA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 h-100" style="background: linear-gradient(180deg, #ffffff 0%, #fffcf5 100%); transition: box-shadow 0.3s ease;" onmouseover="this.style.boxShadow='0 10px 30px rgba(0,0,0,0.08)';" onmouseout="this.style.boxShadow='0 0.125rem 0.25rem rgba(0,0,0,0.075)';">
            <div class="card-header bg-transparent py-4 border-0 d-flex align-items-center">
                <div class="p-2 rounded-3 me-3" style="background-color: #fff8dd; color: #f59e0b;">
                    <i class="bi bi-lightning-charge-fill fs-5"></i>
                </div>
                <h5 class="m-0 fw-bold" style="color: #1e1e2d; letter-spacing: -0.5px;">Aksi Cepat</h5>
            </div>
            <div class="card-body px-4 pb-4 pt-0 d-flex flex-column gap-3">
                <a href="{{ route('pegawai.index') }}" class="btn text-start p-3 rounded-4 d-flex align-items-center fw-semibold border-0 shadow-sm" style="background-color: #ffffff; color: #009ef7; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#009ef7'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#009ef7';">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; background-color: rgba(0, 158, 247, 0.1);">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                    </div>
                    Tambah Pegawai Baru
                </a>
                <a href="{{ route('jadwal.index') }}" class="btn text-start p-3 rounded-4 d-flex align-items-center fw-semibold border-0 shadow-sm" style="background-color: #ffffff; color: #50cd89; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#50cd89'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#50cd89';">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; background-color: rgba(80, 205, 137, 0.1);">
                        <i class="bi bi-calendar-range-fill fs-5"></i>
                    </div>
                    Atur Jadwal Shift
                </a>
                <a href="{{ route('libur.index') }}" class="btn text-start p-3 rounded-4 d-flex align-items-center fw-semibold border-0 shadow-sm" style="background-color: #ffffff; color: #f1416c; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f1416c'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#f1416c';">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; background-color: rgba(241, 65, 108, 0.1);">
                        <i class="bi bi-calendar-plus-fill fs-5"></i>
                    </div>
                    Tambah Libur Nasional
                </a>
                <a href="{{ route('lembur.register') }}" class="btn text-start p-3 rounded-4 d-flex align-items-center fw-semibold border-0 shadow-sm" style="background-color: #ffffff; color: #f59e0b; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f59e0b'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#f59e0b';">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; background-color: rgba(245, 158, 11, 0.1);">
                        <i class="bi bi-moon-stars-fill fs-5"></i>
                    </div>
                    Form Register Lembur
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
