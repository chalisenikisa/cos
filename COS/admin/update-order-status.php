<?php
require_once '../config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');

    $validStatuses = ['pending','preparing','ready','delivered','cancelled'];

    if ($orderId > 0 && in_array($status, $validStatuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
    }
}

flash('success', 'Order status updated.');
redirect('orders.php');