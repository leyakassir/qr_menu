<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_styles.php';

// Generate the public menu link dynamically based on the current server request
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Automatically detects the path up to the admin folder and links to the public menu root
$base_path = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$public_menu_url = $protocol . "://" . $host . $base_path . "/index.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - Restaurant QR Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php render_admin_styles(__DIR__ . '/../assets/css/dashboard_style.css', __DIR__ . '/../assets/css/admin-qr.css'); ?>
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar d-md-block text-white">
        <div class="position-sticky">
            <div class="text-center py-4 mb-3 border-bottom border-secondary">
                <h4 class="text-white m-0 fw-bold"><i class="fa-solid fa-burger text-warning me-2"></i>QR Menu</h4>
                <small class="text-muted">Admin Control Panel</small>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?= app_url() ?>admin/dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                </li>
                <div class="sidebar-heading">Management</div>
                <li class="nav-item">
                    <a class="nav-link" href="#categoriesCollapse" data-bs-toggle="collapse">
                        <i class="fa-solid fa-list"></i> Categories <i class="fa-solid fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="sub-menu collapse" id="categoriesCollapse">
                        <li><a href="<?= app_url() ?>admin/categories/index.php">View Categories</a></li>
                        <li><a href="<?= app_url() ?>admin/categories/add.php">Add Category</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#itemsCollapse" data-bs-toggle="collapse">
                        <i class="fa-solid fa-utensils"></i> Menu Items <i class="fa-solid fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="sub-menu collapse" id="itemsCollapse">
                        <li><a href="<?= app_url() ?>admin/items/index.php">View Items</a></li>
                        <li><a href="<?= app_url() ?>admin/items/add.php">Add Item</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= app_url() ?>admin/settings/index.php"><i class="fa-solid fa-store"></i> Business Settings</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?= app_url() ?>admin/qr.php"><i class="fa-solid fa-qrcode"></i> QR Code</a></li>
                
                <div class="sidebar-heading">Account</div>
                <li class="nav-item"><a class="nav-link" href="<?= app_url() ?>admin/profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="<?= app_url() ?>admin/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand navbar-light navbar-top px-4 fixed-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 fw-semibold text-secondary">
                <i class="fa-solid fa-qrcode me-2 text-primary"></i> QR Code Generator
            </span>
            <div class="navbar-nav ms-auto align-items-center">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px; font-weight: 600;">
                            <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <span class="fw-medium"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item py-2" href="<?= app_url() ?>admin/profile.php"><i class="fa-solid fa-user me-2 text-muted"></i> Profile</a></li>
                        <li><a class="dropdown-item py-2" href="<?= app_url() ?>admin/settings/index.php"><i class="fa-solid fa-gear me-2 text-muted"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?= app_url() ?>admin/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid py-2">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-xl-6 text-center">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white py-4 px-4 border-0 border-bottom">
                            <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-qrcode text-primary me-2"></i> Your Menu QR Code</h4>
                            <p class="text-muted small m-0 mt-1">Display this QR code at your tables so customers can easily scan and view your menu.</p>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            
                            <!-- QR Code Render container using a reliable public QR API generator -->
                            <div class="bg-light p-4 rounded-4 d-inline-block shadow-sm mb-4 border">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?php echo urlencode($public_menu_url); ?>" alt="Menu QR Code" class="img-fluid rounded">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-secondary">Public Menu Link</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light text-center text-muted shadow-none" id="menuUrl" value="<?php echo htmlspecialchars($public_menu_url); ?>" readonly>
                                    <button class="btn btn-outline-primary px-3" type="button" onclick="copyLink()" id="copyBtn">
                                        <i class="fa-solid fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center gap-3">
                                <a href="https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=<?php echo urlencode($public_menu_url); ?>" target="_blank" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
                                    <i class="fa-solid fa-download me-2"></i> Download QR
                                </a>
                                <a href="<?php echo $public_menu_url; ?>" target="_blank" class="btn btn-outline-secondary px-4 py-2 fw-semibold shadow-sm">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Preview Menu
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to handle copy feedback -->
    <script>
        function copyLink() {
            var copyText = document.getElementById("menuUrl");
            copyText.select();
            copyText.setSelectionRange(0, 99999); 
            navigator.clipboard.writeText(copyText.value);
            
            var btn = document.getElementById("copyBtn");
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
            setTimeout(() => {
                btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copy';
            }, 2000);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
