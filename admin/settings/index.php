<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurant_name = trim($_POST['restaurant_name'] ?? '');
    $currency        = trim($_POST['currency'] ?? '$');

    if (empty($restaurant_name)) {
        $error = 'Restaurant name cannot be empty.';
    } else {
        $check = $conn->query("SELECT id FROM business_settings LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE business_settings SET restaurant_name = ?, currency = ?");
            $stmt->bind_param("ss", $restaurant_name, $currency);
        } else {
            $stmt = $conn->prepare("INSERT INTO business_settings (restaurant_name, currency) VALUES (?, ?)");
            $stmt->bind_param("ss", $restaurant_name, $currency);
        }

        if ($stmt->execute()) {
            $success = 'Settings updated successfully!';
        } else {
            $error = 'Database error: ' . $conn->error;
        }
        $stmt->close();
    }
}

$settings = [
    'restaurant_name' => '',
    'currency' => '$'
];
$res = $conn->query("SELECT * FROM business_settings LIMIT 1");
if ($res && $res->num_rows > 0) {
    $settings = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Settings - Restaurant QR Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/qr_menu_Leya_Kassir/assets/css/dashboard_style.css">
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
                    <a class="nav-link" href="/qr_menu_Leya_Kassir/admin/dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                </li>
                <div class="sidebar-heading">Management</div>
                <li class="nav-item">
                    <a class="nav-link" href="#categoriesCollapse" data-bs-toggle="collapse">
                        <i class="fa-solid fa-list"></i> Categories <i class="fa-solid fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="sub-menu collapse" id="categoriesCollapse">
                        <li><a href="/qr_menu_Leya_Kassir/admin/categories/index.php">View Categories</a></li>
                        <li><a href="/qr_menu_Leya_Kassir/admin/categories/add.php">Add Category</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#itemsCollapse" data-bs-toggle="collapse">
                        <i class="fa-solid fa-utensils"></i> Menu Items <i class="fa-solid fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="sub-menu collapse" id="itemsCollapse">
                        <li><a href="/qr_menu_Leya_Kassir/admin/items/index.php">View Items</a></li>
                        <li><a href="/qr_menu_Leya_Kassir/admin/items/add.php">Add Item</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link active" href="/qr_menu_Leya_Kassir/admin/settings/index.php"><i class="fa-solid fa-store"></i> Business Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="/qr_menu_Leya_Kassir/admin/qr.php"><i class="fa-solid fa-qrcode"></i> QR Code</a></li>
                
                <div class="sidebar-heading">Account</div>
                <li class="nav-item"><a class="nav-link" href="/qr_menu_Leya_Kassir/admin/profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="/qr_menu_Leya_Kassir/admin/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand navbar-light navbar-top px-4 fixed-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 fw-semibold text-secondary">
                <i class="fa-solid fa-store me-2 text-primary"></i> Business Settings
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
                        <li><a class="dropdown-item py-2" href="/qr_menu_Leya_Kassir/admin/profile.php"><i class="fa-solid fa-user me-2 text-muted"></i> Profile</a></li>
                        <li><a class="dropdown-item py-2" href="/qr_menu_Leya_Kassir/admin/settings/index.php"><i class="fa-solid fa-gear me-2 text-muted"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="/qr_menu_Leya_Kassir/admin/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid py-2">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white py-4 px-4 border-0 border-bottom">
                            <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-gear text-primary me-2"></i> Restaurant Profile</h4>
                            <p class="text-muted small m-0 mt-1">Configure your main public menu identity and symbols.</p>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fa-solid fa-circle-check me-2"></i> <?php echo $success; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label for="restaurant_name" class="form-label fw-semibold text-secondary">Restaurant Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-utensils"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0 py-2 shadow-none" id="restaurant_name" name="restaurant_name" value="<?php echo htmlspecialchars($settings['restaurant_name'] ?? ''); ?>" placeholder="e.g. Burger House" required>
                                    </div>
                                    <div class="form-text mt-1 text-muted">This title will display at the top of your public QR menu page.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="currency" class="form-label fw-semibold text-secondary">Currency Symbol</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-money-bill"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0 py-2 shadow-none" id="currency" name="currency" value="<?php echo htmlspecialchars($settings['currency'] ?? '$'); ?>" placeholder="e.g. $, €, L.L.">
                                    </div>
                                    <div class="form-text mt-1 text-muted">The symbol used beside your menu item prices.</div>
                                </div>

                                <div class="text-end pt-3 border-top">
                                    <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>