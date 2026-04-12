<?php
require_once 'config.php';
requireLogin();

$stmt = $pdo->prepare("
    SELECT o.*,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
    FROM orders o
    WHERE o.customer_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['customer_id']]);
$orders = $stmt->fetchAll();

$orderItems = [];
if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $ph = implode(',', array_fill(0, count($orderIds), '?'));
    $stmt = $pdo->prepare("
        SELECT oi.*, m.name AS item_name
        FROM order_items oi
        JOIN menu_items m ON oi.menu_item_id = m.id
        WHERE oi.order_id IN ($ph)
        ORDER BY oi.id
    ");
    $stmt->execute($orderIds);
    $allItems = $stmt->fetchAll();
    foreach ($allItems as $item) {
        $orderItems[$item['order_id']][] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — Canteen Food Ordering</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">
            <div class="nav-brand-icon">🍽</div> Canteen Food Ordering
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Menu</a></li>
            <li>
                <a href="cart.php" class="cart-btn">
                    🛒 Cart <span class="cart-badge" style="display:none">0</span>
                </a>
            </li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="page-header">
    <h1>📋 My Orders</h1>
    <p>Track your order history</p>
</div>

<?php if (empty($orders)): ?>
<div style="text-align:center;padding:60px 20px;color:var(--muted);">
    <div style="font-size:3.5rem;margin-bottom:16px;">📦</div>
    <h3 style="color:var(--fg);margin-bottom:10px;">No orders yet</h3>
    <p>Your order history will appear here after you place an order.</p>
    <br><a href="index.php" class="btn-primary">Start Ordering</a>
</div>
<?php else: ?>
<div class="orders-list">
    <?php foreach ($orders as $order): ?>
    <div class="order-card">
        <div class="order-card-header">
            <div>
                <div class="order-id">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></div>
                <div class="order-date"><?= date('M d, Y • g:i A', strtotime($order['created_at'])) ?></div>
            </div>
            <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
        </div>
        <div class="order-card-body">
            <?php foreach ($orderItems[$order['id']] ?? [] as $item): ?>
            <div class="order-item-row">
                <span><?= sanitize($item['item_name']) ?> <span class="order-item-qty">×<?= $item['quantity'] ?></span></span>
                <span><?= formatPrice($item['price'] * $item['quantity']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (!empty($order['notes'])): ?>
            <div style="margin-top:10px;font-size:0.85rem;color:var(--muted);">
                <strong>Notes:</strong> <?= sanitize($order['notes']) ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="order-card-footer">
            <span><?= $order['item_count'] ?> item(s)</span>
            <span style="color:var(--accent);"><?= formatPrice($order['total_amount']) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<footer class="footer">&copy; <?= date('Y') ?> Canteen Food Ordering. Built for hungry students and staff.</footer>

</body>
</html>
