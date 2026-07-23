<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

$settings = [];
$res_settings = $conn->query("SELECT * FROM business_settings LIMIT 1");
if ($res_settings && $res_settings->num_rows > 0) {
    $settings = $res_settings->fetch_assoc();
}

$categories = [];
$res_cat = $conn->query("SELECT * FROM categories ORDER BY id ASC");
if ($res_cat) {
    while ($row = $res_cat->fetch_assoc()) {
        $categories[] = $row;
    }
}

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
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Fallback guaranteed inline styles */
        body { font-family: 'Poppins', sans-serif, Arial, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; padding: 0; }
        .menu-header { background: #212529; color: white; padding: 40px 20px; text-align: center; }
        .item-card { border: 1px solid #dee2e6; border-radius: 12px; background: #fff; padding: 15px; margin-bottom: 15px; display: flex; align-items: center; gap: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .item-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; background: #eee; }
        .category-title { border-left: 4px solid #ffc107; padding-left: 10px; margin-top: 30px; margin-bottom: 15px; font-weight: bold; color: #212529; }
        .container { max-width: 800px; margin: auto; padding: 20px; }
    </style>
</head>
<body>

    <div class="menu-header">
        <h1><i class="fa-solid fa-utensils"></i> <?php echo htmlspecialchars($settings['business_name'] ?? 'Our Restaurant'); ?></h1>
        <p>Scan, browse, and enjoy our delicious menu items!</p>
    </div>

    <div class="container">
        <?php if (count($categories) > 0): ?>
            <?php foreach ($categories as $cat): ?>
                <?php 
                    $cat_items = array_filter($items, function($item) use ($cat) {
                        return $item['category_id'] == $cat['id'];
                    });
                ?>

                <?php if (count($cat_items) > 0): ?>
                    <h3 class="category-title"><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <div>
                        <?php foreach ($cat_items as $item): ?>
                            <div class="item-card">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="" class="item-img">
                                <?php else: ?>
                                    <div class="item-img d-flex align-items-center justify-content-center text-muted"><i class="fa-solid fa-burger"></i></div>
                                <?php endif; ?>
                                <div style="flex-grow: 1;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <h4 style="margin: 0 0 5px 0; font-size: 18px;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <span style="color: #198754; font-weight: bold;">$<?php echo number_format($item['price'], 2); ?></span>
                                    </div>
                                    <p style="margin: 0; color: #6c757d; font-size: 14px;"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #6c757d; margin-top: 50px;">Our menu is currently being updated. Please check back soon!</p>
        <?php endif; ?>
    </div>

    <footer style="text-align: center; padding: 20px; color: #6c757d; font-size: 13px; border-top: 1px solid #dee2e6; margin-top: 40px;">
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['business_name'] ?? 'QR Menu'); ?>. All rights reserved.
    </footer>

</body>
</html>