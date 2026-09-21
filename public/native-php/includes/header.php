<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="HRD Dashboard">

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
