<?php
require_once '../config.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
}
flash('success', 'Category deleted.');
redirect('manage-categories.php');
