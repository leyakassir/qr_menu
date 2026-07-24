<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin_styles.php';

$itemId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($itemId <= 0) {
    header('Location: index.php');
    exit();
}

$categories = [];
$categoryResult = $conn->query('SELECT id, name FROM categories ORDER BY name ASC');
while ($categoryResult && ($row = $categoryResult->fetch_assoc())) {
    $categories[] = $row;
}

$statement = $conn->prepare('SELECT * FROM menu_items WHERE id = ?');
$statement->bind_param('i', $itemId);
$statement->execute();
$item = $statement->get_result()->fetch_assoc();
$statement->close();
if (!$item) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $calories = max(0, (int) ($_POST['calories'] ?? 0));
    $available = isset($_POST['available']) ? 1 : 0;
    $imageName = $item['image'] ?? '';

    if ($name === '' || $categoryId <= 0 || $price <= 0) {
        $error = 'Please complete the item name, category, and price.';
    }

    if ($error === '' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $error = 'Use a JPG, JPEG, PNG, or WEBP image.';
        } else {
            $imageName = uniqid('item_', true) . '.' . $extension;
            $uploadDir = __DIR__ . '/../../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
                $error = 'The new image could not be saved.';
            }
        }
    }

    if ($error === '') {
        $statement = $conn->prepare('UPDATE menu_items SET name = ?, category_id = ?, price = ?, description = ?, available = ?, image = ?, calories = ? WHERE id = ?');
        $statement->bind_param('sidsisii', $name, $categoryId, $price, $description, $available, $imageName, $calories, $itemId);
        if ($statement->execute()) {
            header('Location: index.php?updated=1');
            exit();
        }
        $error = 'Database error: ' . $conn->error;
        $statement->close();
    }

    $item = array_merge($item, ['name' => $name, 'category_id' => $categoryId, 'price' => $price, 'description' => $description, 'calories' => $calories, 'available' => $available, 'image' => $imageName]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item</title>
    <?php render_admin_styles(__DIR__ . '/../../assets/css/dashboard_style.css', __DIR__ . '/../../assets/css/admin-items-add.css', false); ?>
    <style>body{padding:24px}.main-content{max-width:800px;margin:0 auto;padding:24px}.edit-header{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:20px}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}@media(max-width:600px){body{padding:12px}.main-content{padding:12px}.form-grid{grid-template-columns:1fr}.edit-header{align-items:flex-start;flex-direction:column}}</style>
</head>
<body>
<main class="main-content">
    <div class="edit-header"><div><h1>Edit Menu Item</h1><p>Update the details shown on your public menu.</p></div><a class="btn btn-outline-primary" href="index.php">← Back to items</a></div>
    <section class="card"><div class="card-body">
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $itemId ?>">
            <label for="name">Item name</label><input class="form-control" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
            <div class="form-grid"><div><label for="category_id">Category</label><select class="form-select" id="category_id" name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= $category['id'] ?>" <?= (int) $item['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option><?php endforeach; ?></select></div><div><label for="price">Price</label><input class="form-control" id="price" name="price" type="number" step="0.01" min="0.01" value="<?= htmlspecialchars((string) $item['price']) ?>" required></div></div>
            <label for="description">Description</label><textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            <label for="calories">Calories (optional)</label><input class="form-control" id="calories" name="calories" type="number" min="0" step="1" value="<?= htmlspecialchars((string) ($item['calories'] ?? 0)) ?>">
            <label for="image">Replace image (optional)</label><input class="form-control" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            <p><label><input type="checkbox" name="available" value="1" <?= !empty($item['available']) ? 'checked' : '' ?>> Available on the public menu</label></p>
            <button class="btn btn-primary" type="submit">Save changes</button>
        </form>
    </div></section>
</main>
</body>
</html>
