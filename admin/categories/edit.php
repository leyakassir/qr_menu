<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../login.php'); exit(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin_styles.php';

$categoryId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($categoryId <= 0) { header('Location: index.php'); exit(); }
$statement = $conn->prepare('SELECT id, name FROM categories WHERE id = ?');
$statement->bind_param('i', $categoryId); $statement->execute();
$category = $statement->get_result()->fetch_assoc(); $statement->close();
if (!$category) { header('Location: index.php'); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') { $error = 'Category name is required.'; }
    else {
        $statement = $conn->prepare('UPDATE categories SET name = ? WHERE id = ?');
        $statement->bind_param('si', $name, $categoryId);
        if ($statement->execute()) { header('Location: index.php?updated=1'); exit(); }
        $error = 'Unable to update the category.'; $statement->close();
    }
    $category['name'] = $name;
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Edit Category | Crave Wave</title><?php render_admin_styles(__DIR__ . '/../../assets/css/dashboard_style.css', __DIR__ . '/../../assets/css/admin-categories-add.css', false); ?><style>body{padding:24px}.main-content{max-width:650px;margin:0 auto;padding:24px}@media(max-width:600px){body{padding:12px}.main-content{padding:12px}}</style></head><body><main class="main-content"><a class="btn btn-outline-primary" href="index.php">← Back to categories</a><section class="card" style="margin-top:18px"><div class="card-body"><h1>Edit Category</h1><?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="id" value="<?= $categoryId ?>"><label for="name">Category name</label><input class="form-control" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required><br><button class="btn btn-primary" type="submit">Save changes</button></form></div></section></main></body></html>
