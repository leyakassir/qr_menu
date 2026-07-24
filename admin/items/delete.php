<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$itemId = (int) ($_GET['id'] ?? 0);
if ($itemId > 0) {
    $statement = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
    $statement->bind_param('i', $itemId);
    $statement->execute();
    $statement->close();
}

header('Location: index.php?deleted=1');
exit();
