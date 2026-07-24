<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_styles.php';

$success = '';
$error = '';
$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email)) {
        $error = 'Name and Email cannot be empty.';
    } else {
        // Check if email or name is already taken by another administrator
        $stmt = $conn->prepare("SELECT id FROM administrators WHERE (name = ? OR email = ?) AND id != ?");
        $stmt->bind_param("ssi", $name, $email, $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $error = 'Name or Email is already taken by another account.';
            $stmt->close();
        } else {
            $stmt->close();

            // Handle password update if requested
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error = 'Please enter your current password to set a new one.';
                } else {
                    $stmt = $conn->prepare("SELECT password FROM administrators WHERE id = ?");
                    $stmt->bind_param("i", $admin_id);
                    $stmt->execute();
                    $stmt->bind_result($hashed_password);
                    $stmt->fetch();
                    $stmt->close();

                    if (!password_verify($current_password, $hashed_password)) {
                        $error = 'Incorrect current password.';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'New passwords do not match.';
                    } else {
                        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE administrators SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                        $stmt->bind_param("ssssi", $name, $email, $phone, $new_hashed, $admin_id);
                    }
                }
            } else {
                // Update profile details without changing the password
                $stmt = $conn->prepare("UPDATE administrators SET name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $phone, $admin_id);
            }

            if (empty($error)) {
                if ($stmt->execute()) {
                    $success = 'Profile updated successfully!';
                    $_SESSION['admin_name'] = $name; // Keep session synced
                } else {
                    $error = 'Database error: ' . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

// Fetch current administrator details
$stmt = $conn->prepare("SELECT name, email, phone FROM administrators WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($db_name, $db_email, $db_phone);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Crave Wave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php render_admin_styles(__DIR__ . '/../assets/css/dashboard_style.css', __DIR__ . '/../assets/css/admin-profile.css'); ?>
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
                <li class="nav-item"><a class="nav-link" href="<?= app_url() ?>admin/qr.php"><i class="fa-solid fa-qrcode"></i> QR Code</a></li>
                
                <div class="sidebar-heading">Account</div>
                <li class="nav-item"><a class="nav-link active" href="<?= app_url() ?>admin/profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="<?= app_url() ?>admin/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand navbar-light navbar-top px-4 fixed-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 fw-semibold text-secondary">
                <i class="fa-solid fa-user me-2 text-primary"></i> Admin Profile
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
                <div class="col-lg-8 col-xl-7">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white py-4 px-4 border-0 border-bottom">
                            <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-user-pen text-primary me-2"></i> Account Settings</h4>
                            <p class="text-muted small m-0 mt-1">Update your login credentials and contact info.</p>
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
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold text-secondary">Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0 py-2 shadow-none" id="name" name="name" value="<?php echo htmlspecialchars($db_name ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold text-secondary">Email Address *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" class="form-control border-start-0 ps-0 py-2 shadow-none" id="email" name="email" value="<?php echo htmlspecialchars($db_email ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="phone" class="form-label fw-semibold text-secondary">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-phone"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0 py-2 shadow-none" id="phone" name="phone" value="<?php echo htmlspecialchars($db_phone ?? ''); ?>">
                                    </div>
                                </div>

                                <hr class="my-4 text-muted">
                                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-lock text-warning me-2"></i> Change Password</h5>
                                <p class="text-muted small mb-3">Leave blank if you do not wish to change your password.</p>

                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-semibold text-secondary">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                        <input type="password" class="form-control border-start-0 ps-0 py-2 shadow-none" id="current_password" name="current_password" placeholder="Required only if changing password">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label fw-semibold text-secondary">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" class="form-control border-start-0 ps-0 py-2 shadow-none" id="new_password" name="new_password" placeholder="Enter new password">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-semibold text-secondary">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" class="form-control border-start-0 ps-0 py-2 shadow-none" id="confirm_password" name="confirm_password" placeholder="Re-enter new password">
                                    </div>
                                </div>

                                <div class="text-end pt-3 border-top">
                                    <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-2"></i> Update Profile
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
