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
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; color: #333; }
        .menu-header { background: linear-gradient(135deg, #212529 0%, #343a40 100%); color: white; padding: 50px 20px; text-align: center; border-bottom-left-radius: 35px; border-bottom-right-radius: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .item-card { border: none; border-radius: 15px; transition: transform 0.2s ease, box-shadow 0.2s ease; background: #fff; box-shadow: 0 3px 10px rgba(0,0,0,0.05); height: 100%; }
        .item-card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.08); }
        .item-img { width: 100px; height: 100px; object-fit: cover; border-radius: 12px; }
        .category-title { border-left: 5px solid #ffc107; padding-left: 15px; margin-top: 40px; margin-bottom: 20px; font-weight: 700; color: #212529; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="menu-header mb-4">
        <h1 class="fw-bold mb-2"><i class="fa-solid fa-burger text-warning me-2"></i><?php echo htmlspecialchars($settings['business_name'] ?? 'Our Restaurant'); ?></h1>
        <p class="text-white-50 mb-0">Scan, browse, and enjoy our delicious menu items!</p>
    </div>

    <!-- Menu Content -->
    <div class="container py-3">
        <?php if (count($categories) > 0): ?>
            <?php foreach ($categories as $cat): ?>
                <?php 
                    // Filter items belonging to this category
                    $cat_items = array_filter($items, function($item) use ($cat) {
                        return $item['category_id'] == $cat['id'];
                    });
                ?>

                <?php if (count($cat_items) > 0): ?>
                    <h3 class="category-title"><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <div class="row g-4">
                        <?php foreach ($cat_items as $item): ?>
                            <div class="col-lg-6">
                                <div class="item-card p-3 d-flex align-items-center gap-3">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="" class="item-img flex-shrink-0 shadow-sm">
                                    <?php else: ?>
                                        <div class="item-img bg-light d-flex align-items-center justify-content-center text-muted flex-shrink-0 shadow-sm"><i class="fa-solid fa-utensils fs-3"></i></div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($item['name']); ?></h5>
                                            <span class="badge bg-success fs-6 fw-semibold px-2 py-1">$<?php echo number_format($item['price'], 2); ?></span>
                                        </div>
                                        <p class="text-muted small mb-0 mt-1"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted fs-5">Our menu is currently being updated. Please check back soon!</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="text-center py-4 text-muted small border-top mt-5 bg-white">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['business_name'] ?? 'QR Menu'); ?>. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>