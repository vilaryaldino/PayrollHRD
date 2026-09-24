<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="HRD Dashboard">
    <title>HRD Dashboard - PayrollHRD</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* Sidebar Styles */
        #sidebar-wrapper {
            min-height: 100vh;
            width: 250px;
            transition: margin 0.25s ease-out;
            background-color: #2c3e50;
            color: white;
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 1.5rem 1.25rem;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            background: #1a252f;
            letter-spacing: 1px;
        }

        #sidebar-wrapper .list-group {
            width: 250px;
        }

        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: #b8c7ce;
            border: none;
            padding: 12px 20px;
            transition: all 0.3s;
        }

        #sidebar-wrapper .list-group-item:hover, #sidebar-wrapper .list-group-item.active {
            color: #fff;
            background-color: #1a252f;
            border-left: 4px solid #3498db;
        }
        
        #sidebar-wrapper .list-group-item i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        /* Submenu styling */
        .submenu {
            background-color: #22313f;
            padding-left: 20px;
        }
        .submenu .list-group-item {
            padding: 8px 20px;
            font-size: 0.9rem;
        }
        
        /* Toggle effect */
        body.sb-sidenav-toggled #sidebar-wrapper {
            margin-left: -250px;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-white border-bottom border-dark">
                <i class="bi bi-building"></i> HRD SISTEM
            </div>
            <div class="list-group list-group-flush mt-3">
                <!-- Dashboard -->
                <a class="list-group-item list-group-item-action {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                
                <!-- Master -->
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#masterMenu" role="button" aria-expanded="false">
                    <span><i class="bi bi-database"></i> Master</span>
                    <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse {{ request()->routeIs('pegawai.*') || request()->routeIs('shift.*') || request()->routeIs('libur.*') ? 'show' : '' }}" id="masterMenu">
                    <div class="submenu list-group">
                        <a href="{{ route('pegawai.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('pegawai.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Pegawai</a>
                        <a href="{{ route('shift.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('shift.*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Shift Kerja</a>
                        <a href="{{ route('libur.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('libur.*') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i> Libur Nasional</a>
                    </div>
                </div>

                <!-- Transaksi -->
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#transaksiMenu" role="button" aria-expanded="false">
                    <span><i class="bi bi-wallet2"></i> Transaksi</span>
                    <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse {{ request()->routeIs('jadwal.*') || request()->routeIs('absensi.*') || request()->routeIs('lembur.*') ? 'show' : '' }}" id="transaksiMenu">
                    <div class="submenu list-group">
                        <a href="{{ route('jadwal.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('jadwal.*') ? 'active' : '' }}"><i class="bi bi-calendar-check"></i> Jadwal Kerja</a>
                        <a href="{{ route('absensi.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('absensi.*') ? 'active' : '' }}"><i class="bi bi-person-check"></i> Data Absen</a>
                        <a href="{{ route('lembur.register') }}" class="list-group-item list-group-item-action {{ request()->routeIs('lembur.register') ? 'active' : '' }}"><i class="bi bi-moon-stars"></i> Register Lembur</a>
                    </div>
                </div>

                <!-- Laporan -->
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#laporanMenu" role="button" aria-expanded="false">
                    <span><i class="bi bi-file-earmark-text"></i> Laporan</span>
                    <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse {{ request()->routeIs('laporan.*') ? 'show' : '' }}" id="laporanMenu">
                    <div class="submenu list-group">
                        <a href="{{ route('laporan.uang-makan') }}" class="list-group-item list-group-item-action {{ request()->routeIs('laporan.uang-makan') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i> Uang Makan</a>
                        <a href="{{ route('laporan.lembur') }}" class="list-group-item list-group-item-action {{ request()->routeIs('laporan.lembur') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Lembur</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper" class="w-100 bg-light">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-primary" id="sidebarToggle"><i class="bi bi-list"></i> Menu</button>
                    
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="https://ui-avatars.com/api/?name=Admin+HRD&background=287bb5&color=fff" class="rounded-circle me-2" width="30" height="30" alt="User"> Admin HRD
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#!">Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#!"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid p-4">
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', event => {
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.classList.toggle('sb-sidenav-toggled');
                });
            }
        });
    </script>
</body>
</html>
