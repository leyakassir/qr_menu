<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

// Fetch all menu items with their category names
$items = [];
$res = $conn->query("
    SELECT mi.*, c.name as category_name 
    FROM menu_items mi 
    LEFT JOIN categories c ON mi.category_id = c.id 
    ORDER BY mi.id DESC
");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu Items - Restaurant QR Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/dashboard_style.css">
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
                    <a class="nav-link active" href="#itemsCollapse" data-bs-toggle="collapse">
                        <i class="fa-solid fa-utensils"></i> Menu Items <i class="fa-solid fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="sub-menu collapse show" id="itemsCollapse">
                        <li><a class="active" href="<?= app_url() ?>admin/items/index.php">View Items</a></li>
                        <li><a href="<?= app_url() ?>admin/items/add.php">Add Item</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= app_url() ?>admin/settings/index.php"><i class="fa-solid fa-store"></i> Business Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= app_url() ?>admin/qr.php"><i class="fa-solid fa-qrcode"></i> QR Code</a></li>

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
                <i class="fa-solid fa-utensils me-2 text-primary"></i> Menu Items Management
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
                        <li><a class="dropdown-item" href="<?= app_url() ?>admin/profile.php"><i class="fa-solid fa-user me-2 text-muted"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="<?= app_url() ?>admin/settings/index.php"><i class="fa-solid fa-gear me-2 text-muted"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= app_url() ?>admin/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Menu Items</h2>
                <p class="text-muted mb-0">Manage food items, prices, availability, and images.</p>
            </div>
            <a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Add New Item</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Image</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($items) > 0): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="../../uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="" class="item-img-thumb">
                                            <?php else: ?>
                                                <div class="bg-light item-img-thumb d-flex align-items-center justify-content-center text-muted"><i class="fa-solid fa-utensils"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><span class="badge bg-secondary bg-opacity-15 text-dark"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span></td>
                                        <td class="fw-bold text-success">$<?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <?php if (isset($item['available']) && $item['available'] == 1): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">Unavailable</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></a>
                                            <a href="delete.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this menu item?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No menu items found. Click "Add New Item" to create one!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
