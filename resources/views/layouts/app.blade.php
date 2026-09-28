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
            width: 260px;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            transition: all 0.3s ease-in-out;
            background: #1e1e2d; /* Elegant dark color */
            color: #a1a5b7;
            z-index: 1040;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }

        /* Custom Scrollbar */
        #sidebar-wrapper::-webkit-scrollbar {
            width: 5px;
        }
        #sidebar-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        #sidebar-wrapper:hover::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 1.25rem 1.25rem;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            background: #151521;
            color: #ffffff;
            letter-spacing: 1px;
            border-bottom: 1px solid #2b2b40 !important;
        }

        #sidebar-wrapper .list-group {
            width: 100%;
        }

        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: #a1a5b7;
            border: none;
            padding: 12px 20px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        #sidebar-wrapper .list-group-item:hover, 
        #sidebar-wrapper .list-group-item:focus {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        #sidebar-wrapper .list-group-item.active {
            color: #ffffff;
            background-color: #2b2b40;
            border-left: 4px solid #009ef7; /* Elegant primary color */
            border-radius: 0 5px 5px 0;
            margin-right: 10px;
        }
        
        #sidebar-wrapper .list-group-item i:first-child {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 25px;
            text-align: center;
        }

        /* Submenu styling */
        .submenu {
            background-color: #1a1a27;
        }
        .submenu .list-group-item {
            padding: 10px 20px 10px 55px; /* Extra indent */
            font-size: 0.9rem;
        }
        .submenu .list-group-item.active {
            background-color: transparent;
            color: #009ef7;
            border-left: none;
            font-weight: 600;
        }
        
        /* Chevron animation */
        .list-group-item[data-bs-toggle="collapse"] .bi-chevron-down {
            transition: transform 0.3s ease;
        }
        .list-group-item[data-bs-toggle="collapse"][aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }

        /* Mobile Overlay */
        #sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1030;
        }

        /* Desktop vs Mobile Toggle */
        @media (min-width: 768px) {
            body.sb-sidenav-toggled #sidebar-wrapper {
                margin-left: -260px;
            }
        }
        @media (max-width: 767.98px) {
            #sidebar-wrapper {
                position: fixed;
                margin-left: -260px;
            }
            body.sb-sidenav-toggled #sidebar-wrapper {
                margin-left: 0;
            }
            body.sb-sidenav-toggled #sidebar-overlay {
                display: block;
            }
        }

        #page-content-wrapper {
            min-width: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Overlay -->
        <div id="sidebar-overlay"></div>

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
                <div class="collapse {{ request()->routeIs('jadwal.*') || request()->routeIs('absensi.*') || request()->routeIs('lembur.*') || request()->routeIs('spl.*') ? 'show' : '' }}" id="transaksiMenu">
                    <div class="submenu list-group">
                        <a href="{{ route('jadwal.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('jadwal.*') ? 'active' : '' }}"><i class="bi bi-calendar-check"></i> Jadwal Kerja</a>
                        <a href="{{ route('absensi.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('absensi.*') ? 'active' : '' }}"><i class="bi bi-person-check"></i> Data Absen</a>
                        <a href="{{ route('lembur.register') }}" class="list-group-item list-group-item-action {{ request()->routeIs('lembur.register') ? 'active' : '' }}"><i class="bi bi-moon-stars"></i> Register Lembur</a>
                        <!-- <a href="{{ route('spl.create') }}" class="list-group-item list-group-item-action {{ request()->routeIs('spl.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-plus"></i> Input SPL</a> -->
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
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top" style="z-index: 1020; padding: 0.75rem 0;">
                <div class="container-fluid px-4">
                    <button class="btn btn-light d-flex align-items-center justify-content-center" id="sidebarToggle" style="width: 42px; height: 42px; border-radius: 10px; border: 1px solid #e4e6ef; background: #ffffff; color: #7e8299;">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0 align-items-center">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color: #5e6278; font-weight: 500;">
                                <img src="https://ui-avatars.com/api/?name=Admin+HRD&background=009ef7&color=fff&rounded=true&bold=true" class="rounded-circle me-2 shadow-sm" width="35" height="35" alt="User"> 
                                <span class="d-none d-md-inline">Admin HRD</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdown" style="border-radius: 8px; margin-top: 15px;">
                                <a class="dropdown-item py-2" href="#!"><i class="bi bi-person me-2 text-muted"></i> Profile</a>
                                <div class="dropdown-divider my-1"></div>
                                <a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
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
            const sidebarOverlay = document.body.querySelector('#sidebar-overlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.classList.toggle('sb-sidenav-toggled');
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', event => {
                    document.body.classList.remove('sb-sidenav-toggled');
                });
            }
        });
    </script>
</body>
</html>
