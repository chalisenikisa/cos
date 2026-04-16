<?php
require_once '../config.php';
requireAdmin();

 $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
 $totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
 $pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
 $todayOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
 $totalItems = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

 $lowStockThreshold = 10;
 $lowStockCount = 0;
 $unreadNotifications = 0;
 $recentNotifications = [];

 $columns = $pdo->query("SHOW COLUMNS FROM menu_items LIKE 'stock_quantity'")->fetch();
 if ($columns) {
     $lowStockCount = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE stock_quantity IS NOT NULL AND stock_quantity <= ?");
     $lowStockCount->execute([$lowStockThreshold]);
     $lowStockCount = $lowStockCount->fetchColumn();
 }

 $tables = $pdo->query("SHOW TABLES LIKE 'notifications'")->fetch();
 if ($tables) {
     $unreadNotifications = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();
     $recentNotifications = $pdo->query("SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5")->fetchAll();
 }

// Recent orders
 $recentOrders = $pdo->query("
    SELECT o.*, c.name AS customer_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    ORDER BY o.created_at DESC
    LIMIT 8
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Canteen Food Ordering Admin</title>
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
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <h3>Management</h3>
        <a href="manage-menu.php">🍱 Menu Items</a>
        <a href="orders.php">📋 All Orders</a>
        <a href="add-item.php">➕ Add Item</a>
    </aside>

    <main class="admin-content">
        <h1 style="font-family:var(--font-display);font-size:1.8rem;margin-bottom:24px;">Dashboard</h1>

        <?php if ($unreadNotifications > 0): ?>
        <div style="background:#fff3e0;border:1px solid #ff9800;border-radius:var(--radius);padding:16px 20px;margin-bottom:24px;box-shadow:0 2px 8px rgba(255,152,0,0.15);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h4 style="margin:0;color:#e65100;">🔔 Notifications (<?= $unreadNotifications ?>)</h4>
                <button onclick="markAllNotificationsRead()" style="background:#ff9800;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;font-size:0.85rem;">Mark all read</button>
            </div>
            <?php foreach ($recentNotifications as $notif): ?>
            <div style="background:#fff;padding:10px 14px;border-radius:6px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;border-left:3px solid <?= $notif['type'] === 'low_stock' ? '#ff9800' : ($notif['type'] === 'out_of_stock' ? '#f44336' : '#2196f3') ?>;">
                <div>
                    <strong style="color:#333;"><?= sanitize($notif['title']) ?></strong>
                    <p style="margin:4px 0 0 0;color:#666;font-size:0.9rem;"><?= sanitize($notif['message']) ?></p>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <?php if ($notif['item_id']): ?>
                    <a href="edit-item.php?id=<?= $notif['item_id'] ?>" style="background:#4caf50;color:#fff;text-decoration:none;padding:4px 10px;border-radius:4px;font-size:0.8rem;">Restock</a>
                    <?php endif; ?>
                    <button onclick="dismissNotification(<?= $notif['id'] ?>)" style="background:none;border:none;color:#999;cursor:pointer;font-size:1.1rem;">&times;</button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if ($unreadNotifications > 5): ?>
            <a href="notifications.php" style="color:#ff9800;text-decoration:none;font-size:0.9rem;">View all <?= $unreadNotifications ?> notifications →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon orange">📦</div>
                <div class="stat-value"><?= $totalOrders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">💰</div>
                <div class="stat-value"><?= formatPrice($totalRevenue) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow">⏳</div>
                <div class="stat-value"><?= $pendingOrders ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">🍽</div>
                <div class="stat-value"><?= $totalItems ?></div>
                <div class="stat-label">Menu Items</div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="admin-table-wrapper">
            <h3>Recent Orders</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td><strong>#<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= sanitize($o['customer_name']) ?></td>
                        <td><?= formatPrice($o['total_amount']) ?></td>
                        <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td><?= date('M d, g:i A', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px;">No orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
async function markAllNotificationsRead() {
    try {
        const res = await fetch('../api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'mark_all_read' })
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        }
    } catch(e) {
        alert('Error: ' + e.message);
    }
}

async function dismissNotification(id) {
    try {
        const res = await fetch('../api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'dismiss', id: id })
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        }
    } catch(e) {
        alert('Error: ' + e.message);
    }
}
</script>
</body>
</html>