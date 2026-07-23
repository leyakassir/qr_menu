<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin_styles.php';

// Fetch categories for the dropdown selector
$categories = [];
$res_cat = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($res_cat) {
    while ($row = $res_cat->fetch_assoc()) {
        $categories[] = $row;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price       = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $available   = isset($_POST['available']) ? 1 : 0;
    
    $image_name  = '';

    // Handle image upload if a file was provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed_extensions)) {
            $new_image_name = uniqid('item_', true) . '.' . $file_ext;
            $upload_dir = __DIR__ . '/../../uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_dir . $new_image_name)) {
                $image_name = $new_image_name;
            } else {
                $error = 'Failed to move uploaded image file.';
            }
        } else {
            $error = 'Invalid image format. Only JPG, JPEG, PNG, and WEBP files are allowed.';
        }
    }

    if (empty($error)) {
        if (empty($name) || $category_id <= 0 || $price <= 0) {
            $error = 'Please fill out all required fields with valid data.';
        } else {
            $stmt = $conn->prepare("INSERT INTO menu_items (name, category_id, price, description, available, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sidsis", $name, $category_id, $price, $description, $available, $image_name);
            
            if ($stmt->execute()) {
                $success = 'Menu item added successfully!';
                header("Refresh: 1; URL=index.php");
            } else {
                $error = 'Database error: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Item - Restaurant QR Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php render_admin_styles(__DIR__ . '/../../assets/css/dashboard_style.css', __DIR__ . '/../../assets/css/admin-items-add.css'); ?>
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
                    <a class="nav-link" href="../dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                </li>
                <div class="sidebar-heading">Management</div>
                <li class="nav-item">
                    <a class="nav-link" href="#categoriesCollapse" data-bs-toggle="collapse">
                        <i class="fa-solid fa-list"></i> Categories <i class="fa-solid fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="sub-menu collapse" id="categoriesCollapse">
                        <li><a href="../categories/index.php">View Categories</a></li>
                        <li><a href="../categories/add.php">Add Category</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#itemsCollapse" data-bs-toggle="collapse">
                        <i class="fa-solid fa-utensils"></i> Menu Items <i class="fa-solid fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="sub-menu collapse show" id="itemsCollapse">
                        <li><a href="index.php">View Items</a></li>
                        <li><a class="active" href="add.php">Add Item</a></li>
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
                <i class="fa-solid fa-plus-circle me-2 text-primary"></i> Add Menu Item
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-0">
                        <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-utensils text-primary me-2"></i> Item Information</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Item Name *</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Cheeseburger, Margherita Pizza" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label fw-semibold">Category *</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label fw-semibold">Price ($) *</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-semibold">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief ingredients or description of the item..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label fw-semibold">Item Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">Recommended formats: JPG, PNG, WEBP.</div>
                            </div>

                            <div class="mb-4 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="available" name="available" value="1" checked>
                                <label class="form-check-label fw-semibold" for="available">Available for ordering</label>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Save Menu Item</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
