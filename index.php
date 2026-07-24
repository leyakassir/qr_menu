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

$categoryItemCounts = [];
foreach ($items as $item) {
    $categoryId = (int) $item['category_id'];
    $categoryItemCounts[$categoryId] = ($categoryItemCounts[$categoryId] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['business_name'] ?? 'Crave Wave'); ?></title>
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
        .category-nav { background: #fff; border-bottom: 1px solid #e9ecef; padding: 10px 16px; position: sticky; top: 0; z-index: 10; }
        .category-nav-inner { max-width: 800px; margin: auto; display: flex; gap: 8px; overflow-x: auto; }
        .category-nav a { white-space: nowrap; color: #495057; text-decoration: none; background: #f1f3f5; border-radius: 999px; padding: 7px 13px; font-size: 14px; font-weight: 500; }
        .category-nav a:hover { color: #fff; background: #212529; }
        /* Refined public menu visual design */
        * { box-sizing: border-box; }
        body { background: #fff8ed; color: #212529; }
        .menu-header { background: linear-gradient(135deg, #212529, #3b4550); padding: 52px 20px 44px; border-bottom: 5px solid #ffc107; }
        .menu-header h1 { margin: 0 0 8px; font-size: clamp(1.8rem, 5vw, 2.7rem); }.menu-header p { margin: 0; color: #fde68a; }.menu-brand-logo { width: 118px; height: 118px; object-fit: cover; display: block; margin: 0 auto 14px; border-radius: 50%; border: 4px solid #f7b733; box-shadow: 0 10px 26px rgba(0,0,0,.28); background: #fff; }
        .container { max-width: 900px; padding: 22px 20px 36px; }
        .item-card { border-color: #f1e4cc; border-radius: 16px; padding: 16px; gap: 16px; box-shadow: 0 7px 20px rgba(100,70,20,.08); transition: transform .2s, box-shadow .2s; }
        .item-card:hover { transform: translateY(-2px); box-shadow: 0 11px 24px rgba(100,70,20,.14); }.item-img { width: 86px; height: 86px; flex: 0 0 86px; border-radius: 12px; background: #fff1cf; }
        .category-title { border-left-color: #f97316; border-left-width: 5px; padding-left: 12px; margin-top: 38px; font-weight: 700; }
        .category-nav { background: rgba(255,255,255,.96); border-bottom-color: #f3dfbd; padding: 11px 16px; backdrop-filter: blur(8px); }.category-nav-inner { max-width: 900px; gap: 9px; }
        .category-nav a { background: #fff4dc; padding: 8px 14px; font-weight: 600; }.category-nav a:hover { background: #f97316; }
        @media (max-width: 560px) { .menu-header { padding: 36px 16px 32px; }.menu-brand-logo { width: 88px; height: 88px; }.container { padding: 16px 12px 28px; }.item-card { gap: 12px; padding: 12px; align-items: flex-start; }.item-img { width: 68px; height: 68px; flex-basis: 68px; }.item-card h4 { font-size: 16px !important; }.category-title { margin-top: 28px; } }
        /* Appetite-focused restaurant palette */
        body { background: #fff8ed; }.menu-header { background: linear-gradient(135deg, #2b1b17, #6b2f21); border-bottom-color: #f7b733; }.menu-header p { color: #fde4a3; }.category-title { border-left-color: #f76707; }.category-nav a:hover { background: #d94841; }.item-card { border-color: #f0dbc1; }.item-img { background: #fff0c9; }
    </style>
</head>
<body>

    <div class="menu-header">
        <img class="menu-brand-logo" src="assets/images/crave-wave-logo.png" alt="Crave Wave logo">
        <h1><?php echo htmlspecialchars($settings['business_name'] ?? 'Crave Wave'); ?></h1>
        <p>Scan, browse, and enjoy our delicious menu items!</p>
    </div>

    <?php if (!empty($categoryItemCounts)): ?>
        <nav class="category-nav" aria-label="Menu categories">
            <div class="category-nav-inner">
                <?php foreach ($categories as $cat): ?>
                    <?php if (!empty($categoryItemCounts[$cat['id']])): ?>
                        <a href="#category-<?php echo (int) $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </nav>
    <?php endif; ?>

    <div class="container">
        <?php if (count($categories) > 0): ?>
            <?php foreach ($categories as $cat): ?>
                <?php 
                    $cat_items = array_filter($items, function($item) use ($cat) {
                        return $item['category_id'] == $cat['id'];
                    });
                ?>

                <?php if (count($cat_items) > 0): ?>
                    <h3 id="category-<?php echo (int) $cat['id']; ?>" class="category-title"><?php echo htmlspecialchars($cat['name']); ?></h3>
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
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['business_name'] ?? 'Crave Wave'); ?>. All rights reserved.
    </footer>

</body>
</html>
