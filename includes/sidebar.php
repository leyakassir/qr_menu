<nav class="sidebar d-md-block text-white">
    <div class="position-sticky">
        <div class="text-center py-4 mb-3 border-bottom border-secondary">
            <h4 class="text-white m-0 fw-bold"><i class="fa-solid fa-burger text-warning me-2"></i>Crave Wave</h4>
            <small class="text-muted">Crave Wave Admin</small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?= app_url('admin/dashboard.php') ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
            </li>
            <div class="sidebar-heading">Management</div>
            <li class="nav-item">
                <a class="nav-link" href="#categoriesCollapse" data-bs-toggle="collapse">
                    <i class="fa-solid fa-list"></i> Categories <i class="fa-solid fa-chevron-down ms-auto small"></i>
                </a>
                <ul class="sub-menu collapse" id="categoriesCollapse">
                    <li><a href="<?= app_url('admin/categories/index.php') ?>">View Categories</a></li>
                    <li><a href="<?= app_url('admin/categories/add.php') ?>">Add Category</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#itemsCollapse" data-bs-toggle="collapse">
                    <i class="fa-solid fa-utensils"></i> Menu Items <i class="fa-solid fa-chevron-down ms-auto small"></i>
                </a>
                <ul class="sub-menu collapse" id="itemsCollapse">
                    <li><a href="<?= app_url('admin/items/index.php') ?>">View Items</a></li>
                    <li><a href="<?= app_url('admin/items/add.php') ?>">Add Item</a></li>
                </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="<?= app_url('admin/settings/index.php') ?>"><i class="fa-solid fa-store"></i> Business Settings</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= app_url('admin/qr.php') ?>"><i class="fa-solid fa-qrcode"></i> QR Code</a></li>
            
            <div class="sidebar-heading">Account</div>
            <li class="nav-item"><a class="nav-link" href="<?= app_url('admin/profile.php') ?>"><i class="fa-solid fa-user"></i> Profile</a></li>
            <li class="nav-item"><a class="nav-link text-danger" href="<?= app_url('admin/logout.php') ?>"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </div>
</nav>
