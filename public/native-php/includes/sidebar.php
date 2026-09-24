<?php
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<div class="border-end" id="sidebar-wrapper">
    <div class="sidebar-heading text-white border-bottom border-dark">
        <i class="bi bi-building"></i> HRD SISTEM
    </div>
    <div class="list-group list-group-flush mt-3">
        <!-- Dashboard -->
        <a class="list-group-item list-group-item-action <?= $currentPage == 'dashboard' ? 'active' : '' ?>" href="?page=dashboard">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <!-- Master -->
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#masterMenu" role="button" aria-expanded="false">
            <span><i class="bi bi-database"></i> Master</span>
            <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
        </a>
        <div class="collapse <?= in_array($currentPage, ['pegawai', 'shift', 'libur']) ? 'show' : '' ?>" id="masterMenu">
            <div class="submenu list-group">
                <a href="?page=pegawai" class="list-group-item list-group-item-action <?= $currentPage == 'pegawai' ? 'active' : '' ?>"><i class="bi bi-people"></i> Pegawai</a>
                <a href="?page=shift" class="list-group-item list-group-item-action <?= $currentPage == 'shift' ? 'active' : '' ?>"><i class="bi bi-clock-history"></i> Shift Kerja</a>
                <a href="?page=libur" class="list-group-item list-group-item-action <?= $currentPage == 'libur' ? 'active' : '' ?>"><i class="bi bi-calendar-event"></i> Libur Nasional</a>
            </div>
        </div>

        <!-- Transaksi -->
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#transaksiMenu" role="button" aria-expanded="false">
            <span><i class="bi bi-wallet2"></i> Transaksi</span>
            <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
        </a>
        <div class="collapse <?= in_array($currentPage, ['jadwal', 'absen', 'register_lembur']) ? 'show' : '' ?>" id="transaksiMenu">
            <div class="submenu list-group">
                <a href="?page=jadwal" class="list-group-item list-group-item-action <?= $currentPage == 'jadwal' ? 'active' : '' ?>"><i class="bi bi-calendar-check"></i> Jadwal Kerja</a>
                <a href="?page=absen" class="list-group-item list-group-item-action <?= $currentPage == 'absen' ? 'active' : '' ?>"><i class="bi bi-person-check"></i> Data Absen</a>
                <a href="?page=register_lembur" class="list-group-item list-group-item-action <?= $currentPage == 'register_lembur' ? 'active' : '' ?>"><i class="bi bi-moon-stars"></i> Register Lembur</a>
            </div>
        </div>

        <!-- Laporan -->
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#laporanMenu" role="button" aria-expanded="false">
            <span><i class="bi bi-file-earmark-text"></i> Laporan</span>
            <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
        </a>
        <div class="collapse <?= in_array($currentPage, ['lap_uang_makan', 'lap_lembur']) ? 'show' : '' ?>" id="laporanMenu">
            <div class="submenu list-group">
                <a href="?page=lap_uang_makan" class="list-group-item list-group-item-action <?= $currentPage == 'lap_uang_makan' ? 'active' : '' ?>"><i class="bi bi-cash-stack"></i> Uang Makan</a>
                <a href="?page=lap_lembur" class="list-group-item list-group-item-action <?= $currentPage == 'lap_lembur' ? 'active' : '' ?>"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Lembur</a>
            </div>
        </div>
    </div>
</div>
