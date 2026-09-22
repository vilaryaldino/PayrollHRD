<?php
ob_start();
session_start();

// index.php - Main Router
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$pagePath = 'pages/' . $page . '.php';

// Jika ada request POST, eksekusi file page SEBELUM menghasilkan output HTML apapun
// Ini menjamin header("Location: ...") dapat berjalan bersih tanpa "headers already sent"
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && file_exists($pagePath)) {
    include $pagePath;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>HRD Dashboard</title>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Page Content -->
        <div id="page-content-wrapper" class="w-100 bg-light">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-primary" id="sidebarToggle"><i class="bi bi-list"></i> Menu</button>
                    
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="https://ui-avatars.com/api/?name=Admin+HRD" class="rounded-circle me-2" width="30" height="30" alt="User"> Admin HRD
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#!">Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#!">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid p-4">
                <?php
                if (file_exists($pagePath)) {
                    include $pagePath;
                } else {
                    echo "<div class='alert alert-danger'>Page not found!</div>";
                }
                ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
<?php
ob_end_flush();
?>
