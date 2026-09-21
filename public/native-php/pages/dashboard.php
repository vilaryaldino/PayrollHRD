<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 text-gray-800 m-0">Dashboard</h2>
    <span class="text-muted"><i class="bi bi-calendar-day"></i> <?php echo date('d F Y'); ?></span>
</div>

<!-- Summary Cards Row -->
<div class="row mb-4">
    <!-- Total Pegawai Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-primary border-4 rounded-3">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="font-size: 0.85rem; font-weight: 700;">
                            Total Pegawai
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">150</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-muted opacity-50" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="?page=pegawai" class="text-decoration-none text-primary" style="font-size: 0.85rem;">Lihat Detail <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Total Absen Hari Ini Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-success border-4 rounded-3">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.85rem; font-weight: 700;">
                            Total Absen Hari Ini
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">142</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-check fa-2x text-muted opacity-50" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="?page=absen" class="text-decoration-none text-success" style="font-size: 0.85rem;">Lihat Detail <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Total Lembur Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-warning border-4 rounded-3">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1" style="font-size: 0.85rem; font-weight: 700;">
                            Total Lembur (Bulan Ini)
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">24 Jam</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-moon-stars fa-2x text-muted opacity-50" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="?page=register_lembur" class="text-decoration-none text-warning" style="font-size: 0.85rem;">Lihat Detail <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Section -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Sistem</h6>
            </div>
            <div class="card-body">
                <p>Selamat datang di Dashboard Sistem HRD. Gunakan menu di sebelah kiri untuk mengelola data pegawai, transaksi kehadiran, dan laporan.</p>
                <div class="alert alert-info border-0 shadow-sm">
                    <i class="bi bi-info-circle-fill me-2"></i> Aplikasi ini menggunakan Native PHP dan Bootstrap 5 dengan arsitektur modular sederhana.
                </div>
            </div>
        </div>
    </div>
</div>
