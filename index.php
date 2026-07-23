<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

// Fetch restaurant settings (business name, etc.)
$settings = [];
$res_settings = $conn->query("SELECT * FROM business_settings LIMIT 1");
if ($res_settings && $res_settings->num_rows > 0) {
    $settings = $res_settings->fetch_assoc();
}

// Fetch all categories
$categories = [];
$res_cat = $conn->query("SELECT * FROM categories ORDER BY id ASC");
if ($res_cat) {
    while ($row = $res_cat->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch available menu items
$items = [];
$res_items = $conn->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY id DESC");
if ($res_items) {
    while ($row = $res_items->fetch_assoc()) {
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['business_name'] ?? 'Digital Menu'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; color: #333; }
        .menu-header { background: linear-gradient(135deg, #212529 0%, #343a40 100%); color: white; padding: 40px 20px; text-align: center; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; }
        .item-card { border: none; border-radius: 12px; transition: transform 0.2s; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .item-img { width: 90px; height: 90px; object-fit: cover; border-radius: 10px; }
        .category-title { border-left: 4px solid #ffc107; padding-left: 12px; margin-top: 35px; margin-bottom: 20px; font-weight: 700; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="menu-header shadow-sm">
        <h1 class="fw-bold mb-1"><i class="fa-solid fa-burger text-warning me-2"></i><?php echo htmlspecialchars($settings['business_name'] ?? 'Our Restaurant'); ?></h1>
        <p class="text-white-50 mb-0">Scan, browse, and enjoy our delicious menu items!</p>
    </div>

    <!-- Menu Content -->
    <div class="container py-4">
        <?php if (count($categories) > 0): ?>
            <?php foreach ($categories as $cat): ?>
                <?php 
                    // Filter items belonging to this category
                    $cat_items = array_filter($items, function($item) use ($cat) {
                        return $item['category_id'] == $cat['id'];
                    });
                ?>

                <?php if (count($cat_items) > 0): ?>
                    <h3 class="category-title text-dark"><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <div class="row g-3">
                        <?php foreach ($cat_items as $item): ?>
                            <div class="col-md-6">
                                <div class="item-card p-3 h-100 d-flex align-items-center gap-3">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="" class="item-img flex-shrink-0">
                                    <?php else: ?>
                                        <div class="item-img bg-light d-flex align-items-center justify-content-center text-muted flex-shrink-0"><i class="fa-solid fa-utensils fs-4"></i></div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($item['name']); ?></h5>
                                            <span class="fw-bold text-success">$<?php echo number_format($item['price'], 2); ?></span>
                                        </div>
                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted">Our menu is currently being updated. Please check back soon!</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="text-center py-4 text-muted small border-top mt-5">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['business_name'] ?? 'QR Menu'); ?>. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>