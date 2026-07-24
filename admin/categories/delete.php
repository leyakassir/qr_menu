<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../login.php'); exit(); }
require_once __DIR__ . '/../../config/database.php';

$categoryId = (int) ($_GET['id'] ?? 0);
if ($categoryId > 0) {
    $check = $conn->prepare('SELECT COUNT(*) FROM menu_items WHERE category_id = ?');
    $check->bind_param('i', $categoryId); $check->execute(); $check->bind_result($itemCount); $check->fetch(); $check->close();
    if ((int) $itemCount === 0) {
        $statement = $conn->prepare('DELETE FROM categories WHERE id = ?');
        $statement->bind_param('i', $categoryId); $statement->execute(); $statement->close();
        header('Location: index.php?deleted=1'); exit();
    }
    header('Location: index.php?error=has-items'); exit();
}
header('Location: index.php');
exit();
