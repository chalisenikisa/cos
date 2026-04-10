<?php
require_once '../config.php';
requireAdmin();

 $id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
}
flash('success', 'Item deleted.');
redirect('manage-menu.php');