<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_styles.php';

// Fetch dynamic statistics from database
$total_categories = 0;
$total_items = 0;
$available_items = 0;
$unavailable_items = 0;

$res_cat = $conn->query("SELECT COUNT(*) as count FROM categories");
if ($res_cat) {
    $total_categories = $res_cat->fetch_assoc()['count'];
}

$res_items = $conn->query("SELECT COUNT(*) as count FROM menu_items");
if ($res_items) {
    $total_items = $res_items->fetch_assoc()['count'];
}

$res_avail = $conn->query("SELECT COUNT(*) as count FROM menu_items WHERE available = 1");
if ($res_avail) {
    $available_items = $res_avail->fetch_assoc()['count'];
}

$res_unavail = $conn->query("SELECT COUNT(*) as count FROM menu_items WHERE available = 0 OR available IS NULL");
if ($res_unavail) {
    $unavailable_items = $res_unavail->fetch_assoc()['count'];
}

// Fetch recent menu items (limit 5-10)
$recent_items = [];
$res_recent = $conn->query("
    SELECT mi.*, c.name as category_name 
    FROM menu_items mi 
    LEFT JOIN categories c ON mi.category_id = c.id 
    ORDER BY mi.id DESC LIMIT 5
");
if ($res_recent) {
    while ($row = $res_recent->fetch_assoc()) {
        $recent_items[] = $row;
    }
}

// Fetch category counts breakdown
$category_breakdown = [];
$res_breakdown = $conn->query("
    SELECT c.name, COUNT(mi.id) as item_count 
    FROM categories c 
    LEFT JOIN menu_items mi ON c.id = mi.category_id 
    GROUP BY c.id, c.name
");
if ($res_breakdown) {
    while ($row = $res_breakdown->fetch_assoc()) {
        $category_breakdown[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Crave Wave</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- External Dashboard CSS -->
    <?php render_admin_styles(__DIR__ . '/../assets/css/dashboard_style.css', __DIR__ . '/../assets/css/admin-dashboard.css'); ?>
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar d-md-block text-white">
        <div class="position-sticky">
            <div class="text-center py-4 mb-3 border-bottom border-secondary">
                <h4 class="text-white m-0 fw-bold"><i class="fa-solid fa-burger text-warning me-2"></i>Crave Wave</h4>
                <small class="text-muted">Crave Wave Admin</small>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard
                    </a>
                </li>
                
                <div class="sidebar-heading">Management</div>
                <li class="nav-item">
                    <a class="nav-link" href="#categoriesCollapse" data-bs-toggle="collapse">
                        <i class="fa-solid fa-list"></i> Categories <i class="fa-solid fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="sub-menu collapse" id="categoriesCollapse">
                        <li><a href="categories/index.php">View Categories</a></li>
                        <li><a href="categories/add.php">Add Category</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#itemsCollapse" data-bs-toggle="collapse">
                        <i class="fa-solid fa-utensils"></i> Menu Items <i class="fa-solid fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="sub-menu collapse" id="itemsCollapse">
                        <li><a href="items/index.php">View Items</a></li>
                        <li><a href="items/add.php">Add Item</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings/index.php">
                        <i class="fa-solid fa-store"></i> Business Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="qr.php">
                        <i class="fa-solid fa-qrcode"></i> QR Code
                    </a>
                </li>
                
                <div class="sidebar-heading">Account</div>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="fa-solid fa-user"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand navbar-light navbar-top px-4 fixed-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 fw-semibold text-secondary">
                <i class="fa-solid fa-house-chimney me-2 text-primary"></i> Dashboard Overview
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
                        <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-user me-2 text-muted"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="settings/index.php"><i class="fa-solid fa-gear me-2 text-muted"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Banner -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white p-4 shadow-sm">
                    <div class="card-body">
                        <h2 class="fw-bold mb-1">Welcome Back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>! 👋</h2>
                        <p class="mb-0 text-white-50">Here is a quick overview of your restaurant's performance and live menu statuses.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card primary p-3 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-semibold mb-1">Total Categories</h6>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo $total_categories; ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="fa-solid fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card primary p-3 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-semibold mb-1">Total Menu Items</h6>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo $total_items; ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="fa-solid fa-utensils fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card success p-3 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-semibold mb-1">Available Items</h6>
                            <h3 class="fw-bold mb-0 text-success"><?php echo $available_items; ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                            <i class="fa-solid fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card danger p-3 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-semibold mb-1">Unavailable Items</h6>
                            <h3 class="fw-bold mb-0 text-danger"><?php echo $unavailable_items; ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                            <i class="fa-solid fa-ban fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Recent Items & Category Overview -->
            <div class="col-lg-8">
                <!-- Recent Menu Items Table -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                        <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Recent Menu Items</h5>
                        <a href="items/index.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th class="pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_items) > 0): ?>
                                        <?php foreach ($recent_items as $item): ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <?php if (!empty($item['image'])): ?>
                                                        <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="" class="item-img-thumb">
                                                    <?php else: ?>
                                                        <div class="bg-light item-img-thumb d-flex align-items-center justify-content-center text-muted"><i class="fa-solid fa-utensils"></i></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td><span class="badge bg-secondary bg-opacity-15 text-dark"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span></td>
                                                <td class="fw-bold text-success">$<?php echo number_format($item['price'], 2); ?></td>
                                                <td class="pe-3">
                                                    <?php if (isset($item['available']) && $item['available'] == 1): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Available</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">Unavailable</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No menu items found yet. Add your first item!</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Category Overview Breakdown -->
                <div class="card">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-chart-pie text-primary me-2"></i> Category Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php if (count($category_breakdown) > 0): ?>
                                <?php foreach ($category_breakdown as $cat): ?>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($cat['name']); ?></span>
                                            <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo $cat['item_count']; ?> items</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3 m-0">No categories recorded yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Quick Actions, Restaurant Info, System Status -->
            <div class="col-lg-4">
                <!-- Quick Actions Card -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-bolt text-warning me-2"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="categories/add.php" class="btn btn-outline-primary text-start py-2.5 px-3 fw-medium">
                            <i class="fa-solid fa-plus-circle me-2"></i> Add Category
                        </a>
                        <a href="items/add.php" class="btn btn-outline-primary text-start py-2.5 px-3 fw-medium">
                            <i class="fa-solid fa-plus-circle me-2"></i> Add Menu Item
                        </a>
                        <a href="settings/index.php" class="btn btn-outline-secondary text-start py-2.5 px-3 fw-medium">
                            <i class="fa-solid fa-gear me-2"></i> Business Settings
                        </a>
                        <a href="qr.php" class="btn btn-outline-success text-start py-2.5 px-3 fw-medium">
                            <i class="fa-solid fa-qrcode me-2"></i> View QR Code
                        </a>
                    </div>
                </div>

                <!-- Restaurant Information Card -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-store text-primary me-2"></i> Restaurant Info</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li class="mb-2 d-flex justify-content-between">
                                <span class="fw-semibold text-dark">Status:</span> 
                                <span class="text-success fw-medium">Active & Open</span>
                            </li>
                            <li class="mb-2 d-flex justify-content-between">
                                <span class="fw-semibold text-dark">Total Menu Entries:</span> 
                                <span class="text-dark fw-medium"><?php echo $total_items; ?> Items</span>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span class="fw-semibold text-dark">Active Categories:</span> 
                                <span class="text-dark fw-medium"><?php echo $total_categories; ?> Categories</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- System Status Card -->
                <div class="card">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-server text-secondary me-2"></i> System Status</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2 d-flex align-items-center">
                                <i class="fa-solid fa-circle text-success me-2 small"></i> Database Connected
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <i class="fa-solid fa-circle text-success me-2 small"></i> Server Online (Apache)
                            </li>
                            <li class="d-flex align-items-center">
                                <i class="fa-solid fa-circle text-success me-2 small"></i> Crave Wave Active
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
