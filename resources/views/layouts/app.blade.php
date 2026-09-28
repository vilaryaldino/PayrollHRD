<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="HRD Dashboard & Payroll System">
    <title>@yield('title', 'HRD Dashboard') - Enterprise Payroll</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --hr-primary: #f59e0b;
            --hr-primary-hover: #d97706;
            --hr-primary-light: #fffbeb;
            --hr-primary-border: #fde68a;
            --hr-blue: #2563eb;
            --hr-blue-hover: #1d4ed8;
            --hr-blue-light: #eff6ff;
            --hr-blue-border: #dbeafe;
            --hr-dark: #0f172a;
            --hr-slate: #334155;
            --hr-muted: #64748b;
            --hr-light-gray: #f8fafc;
            --hr-border: #e2e8f0;
            --hr-card-border: #e8edf2;
            --font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: #f8fafc;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }

        /* ========================================================
           SIDEBAR STYLES (ORIGINAL - UNALTERED)
           ======================================================== */
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
            background-color: #f8fafc;
        }

        /* ========================================================
           GLOBAL UNIFIED STYLES & COMPONENTS
           ======================================================== */
        /* Top Navigation Bar */
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid #e8edf2;
            padding: 0.75rem 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .btn-menu-toggle {
            background: #2563eb;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 7px 15px;
            border-radius: 9px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
        }
        .btn-menu-toggle:hover {
            background: #1d4ed8;
            color: #ffffff;
        }
        .nav-breadcrumbs {
            font-size: 0.84rem;
            font-weight: 500;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 1rem;
        }
        .nav-breadcrumbs a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.15s;
        }
        .nav-breadcrumbs a:hover {
            color: #1e293b;
        }
        .nav-breadcrumbs .breadcrumb-active {
            color: #f59e0b;
            font-weight: 700;
        }
        .nav-breadcrumbs .separator {
            color: #cbd5e1;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f59e0b;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.25);
        }

        /* Buttons */
        .btn-orange {
            background: #f59e0b;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.86rem;
            border-radius: 9px;
            padding: 8px 18px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
        }
        .btn-orange:hover {
            background: #d97706;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .btn-orange:active {
            transform: translateY(0);
        }

        .btn-blue-outline {
            background: #ffffff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            font-weight: 600;
            font-size: 0.86rem;
            border-radius: 9px;
            padding: 8px 16px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .btn-blue-outline:hover {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #93c5fd;
        }

        /* Page Headers */
        .page-title-section {
            margin-bottom: 1.5rem;
        }
        .page-title-section h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .page-title-section p {
            font-size: 0.86rem;
            color: #64748b;
            margin: 4px 0 0;
        }

        /* Metric Cards */
        .stat-card-unified {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e8edf2;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }
        .stat-card-unified:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .stat-number-box {
            min-width: 44px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            padding: 0 8px;
        }
        .stat-number-blue   { background: #eff6ff; color: #2563eb; }
        .stat-number-green  { background: #ecfdf5; color: #059669; }
        .stat-number-indigo { background: #eef2ff; color: #4f46e5; }
        .stat-number-purple { background: #faf5ff; color: #7c3aed; }
        .stat-number-amber  { background: #fffbeb; color: #d97706; }
        .stat-number-rose   { background: #fff1f2; color: #e11d48; }

        .stat-label-text {
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            line-height: 1.3;
        }
        .stat-sublabel-text {
            font-size: 0.74rem;
            color: #94a3b8;
        }

        /* Unified Card Container */
        .unified-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e8edf2;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .unified-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* Toolbar / Filter Box */
        .unified-filter-bar {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e8edf2;
            padding: 12px 16px;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .search-input-pill {
            border-radius: 9px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            font-size: 0.85rem;
            color: #1e293b;
            padding: 7px 12px;
            transition: all 0.2s ease;
        }
        .search-input-pill:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
            outline: none;
        }
        .filter-select-pill {
            border-radius: 9px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            font-size: 0.84rem;
            color: #475569;
            font-weight: 500;
            padding: 7px 12px;
            transition: all 0.2s ease;
        }
        .filter-select-pill:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
            outline: none;
        }

        /* Tables */
        .unified-table {
            margin: 0;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .unified-table thead th {
            background: #ffffff;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.2rem;
            border: none;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .unified-table tbody td {
            padding: 1rem 1.2rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: 0.86rem;
            color: #334155;
            background: #ffffff;
            transition: background 0.15s ease;
        }
        .unified-table tbody tr:hover td {
            background: #f8fafc;
        }
        .unified-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Table Avatar Pill */
        .table-avatar-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #f59e0b;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.82rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .table-user-name {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.88rem;
            line-height: 1.2;
        }
        .table-user-meta {
            font-size: 0.74rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Soft Badges */
        .soft-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 6px;
        }
        .badge-staff-soft   { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .badge-harian-soft  { background: #faf5ff; color: #7c3aed; border: 1px solid #ede9fe; }
        .badge-kontrak-soft { background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }

        /* Dot Status Indicators */
        .status-dot-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
            font-weight: 500;
        }
        .status-dot-indicator::before {
            content: '';
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }
        .dot-aktif { color: #059669; }
        .dot-aktif::before { background-color: #10b981; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); }
        .dot-nonaktif { color: #64748b; }
        .dot-nonaktif::before { background-color: #94a3b8; }
        .dot-shift-pagi { color: #d97706; }
        .dot-shift-pagi::before { background-color: #f59e0b; }
        .dot-shift-sore { color: #ea580c; }
        .dot-shift-sore::before { background-color: #f97316; }
        .dot-shift-malam { color: #4f46e5; }
        .dot-shift-malam::before { background-color: #6366f1; }

        /* Action Buttons in Table */
        .action-icon-btn {
            border: none;
            background: transparent;
            color: #94a3b8;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.92rem;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .action-icon-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .action-icon-btn.btn-delete:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Pagination Styling */
        .unified-pagination {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .unified-pagination .page-item .page-link {
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 600;
            border-radius: 7px;
            padding: 4px 10px;
            min-width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #ffffff;
            transition: all 0.15s ease;
        }
        .unified-pagination .page-item .page-link:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1;
        }
        .unified-pagination .page-item.active .page-link {
            background: #f59e0b;
            border-color: #f59e0b;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.25);
        }
        .unified-pagination .page-item.disabled .page-link {
            opacity: 0.5;
            background: #f8fafc;
            cursor: not-allowed;
        }

        /* Modals */
        .modal-content {
            border-radius: 16px;
            border: 1px solid #e8edf2;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .modal-header {
            padding: 1.25rem 1.5rem;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }
        .modal-header .modal-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
        }
        .modal-body {
            padding: 1.5rem;
            background: #ffffff;
        }
        .modal-footer {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Overlay for Mobile -->
        <div id="sidebar-overlay"></div>

        <!-- Sidebar (Preserved Exactly) -->
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
        
        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper">
            <!-- Modern Top Navigation Bar (Matching Reference Image) -->
            <nav class="top-navbar d-flex align-items-center justify-content-between">
                <!-- Left: Menu Toggle Button + Breadcrumbs -->
                <div class="d-flex align-items-center">
                    <button class="btn-menu-toggle" id="sidebarToggle" type="button" title="Toggle Menu">
                        <i class="bi bi-list fs-5"></i>
                        <span>Menu</span>
                    </button>

                    <div class="nav-breadcrumbs d-none d-md-flex">
                        <span>HRD SISTEM</span>
                        <span class="separator">/</span>
                        @yield('breadcrumb_parent', '<span>Master</span><span class="separator">/</span>')
                        <span class="breadcrumb-active">@yield('breadcrumb_active', 'Master Data Pegawai')</span>
                    </div>
                </div>

                <!-- Right: Notification Bell & Admin Profile Widget -->
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-link text-muted position-relative p-2 text-decoration-none" title="Notifikasi">
                        <i class="bi bi-bell fs-5" style="color: #64748b;"></i>
                        <span class="position-absolute top-2 start-75 translate-middle p-1 bg-warning border border-light rounded-circle" style="width: 8px; height: 8px;"></span>
                    </button>

                    <div class="dropdown">
                        <a class="d-flex align-items-center text-decoration-none gap-2 dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="text-end d-none d-sm-block">
                                <div class="fw-bold" style="font-size: 0.85rem; color: #0f172a; line-height: 1.2;">Admin HRD</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Head of HR & Payroll</div>
                            </div>
                            <div class="admin-avatar">
                                A
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2 mt-2" style="border-radius: 10px; min-width: 180px;">
                            <li><h6 class="dropdown-header text-muted small">Admin HRD</h6></li>
                            <li><a class="dropdown-item py-2 small" href="#"><i class="bi bi-person me-2 text-muted"></i> Profil Pengguna</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 small text-danger"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Main Page View Content -->
            <div class="p-4">
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
    @stack('scripts')
</body>
</html>
