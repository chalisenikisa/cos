<?php
require_once '../config.php';
requireAdmin();

// Optional status filter
 $filter = sanitize($_GET['status'] ?? '');

if ($filter && in_array($filter, ['pending','preparing','ready','delivered','cancelled'])) {
    $orders = $pdo->prepare("
        SELECT o.*, c.name AS customer_name
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        WHERE o.status = ?
        ORDER BY o.created_at DESC
    ");
    $orders->execute([$filter]);
} else {
    $orders = $pdo->query("
        SELECT o.*, c.name AS customer_name
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        ORDER BY o.created_at DESC
    ")->fetchAll();
}

// Fetch items for all orders
 $orderItems = [];
if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $ph = implode(',', array_fill(0, count($orderIds), '?'));
    $stmt = $pdo->prepare("
        SELECT oi.*, m.name AS item_name
        FROM order_items oi
        JOIN menu_items m ON oi.menu_item_id = m.id
        WHERE oi.order_id IN ($ph)
    ");
    $stmt->execute($orderIds);
    foreach ($stmt->fetchAll() as $item) {
        $orderItems[$item['order_id']][] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders — Canteen Food Ordering  Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="dashboard.php" class="nav-brand">
            <div class="nav-brand-icon">🍽</div> Canteen Food Ordering
            <span style="font-size:0.65rem;background:var(--accent);color:#fff;padding:2px 8px;border-radius:50px;">Admin</span>
        </a>
        <ul class="nav-links">
            <li><a href="../index.php">View Canteen</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>Overview</h3>
        <a href="dashboard.php">📊 Dashboard</a>
        <h3>Management</h3>
        <a href="manage-menu.php">🍱 Menu Items</a>
        <a href="orders.php" class="active">📋 All Orders</a>
        <a href="add-item.php">➕ Add Item</a>
    </aside>

    <main class="admin-content">
        <h1 style="font-family:var(--font-display);font-size:1.8rem;margin-bottom:24px;">Orders</h1>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <!-- Status filter buttons -->
        <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
            <a href="orders.php" class="cat-btn <?= empty($filter) ? 'active' : '' ?>">All</a>
            <a href="orders.php?status=pending" class="cat-btn <?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="orders.php?status=preparing" class="cat-btn <?= $filter === 'preparing' ? 'active' : '' ?>">Preparing</a>
            <a href="orders.php?status=ready" class="cat-btn <?= $filter === 'ready' ? 'active' : '' ?>">Ready</a>
            <a href="orders.php?status=delivered" class="cat-btn <?= $filter === 'delivered' ? 'active' : '' ?>">Delivered</a>
            <a href="orders.php?status=cancelled" class="cat-btn <?= $filter === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
        </div>

        <?php if (empty($orders)): ?>
            <div style="text-align:center;padding:40px;color:var(--muted);">No orders found.</div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <div class="order-card" style="margin-bottom:16px;">
                <div class="order-card-header">
                    <div>
                        <div class="order-id">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></div>
                        <div class="order-date"><?= sanitize($order['customer_name']) ?> — <?= date('M d, Y g:i A', strtotime($order['created_at'])) ?></div>
                    </div>
                    <form method="POST" action="update-order-status.php" style="display:flex;align-items:center;gap:8px;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" style="padding:6px 10px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:inherit;font-size:0.82rem;cursor:pointer;">
                            <?php foreach (['pending','preparing','ready','delivered','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-primary btn-sm">Update</button>
                    </form>
                </div>
                <div class="order-card-body">
                    <?php foreach ($orderItems[$order['id']] ?? [] as $item): ?>
                    <div class="order-item-row">
                        <span><?= sanitize($item['item_name']) ?> <span class="order-item-qty">x<?= $item['quantity'] ?></span></span>
                        <span><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($order['notes']): ?>
                    <div style="margin-top:10px;padding:8px 12px;background:var(--bg);border-radius:var(--radius-sm);font-size:0.82rem;color:var(--muted);">
                        📝 <?= sanitize($order['notes']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="order-card-footer">
                    <span><?= $order['payment_method'] ? ucfirst($order['payment_method']) : 'Cash' ?></span>
                    <span><?= formatPrice($order['total_amount']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

</body>
</html>