<?php
require_once '../config.php';
requireAdmin();

$users = $pdo->query("
    SELECT c.*, COUNT(o.id) AS order_count
    FROM customers c
    LEFT JOIN orders o ON c.id = o.customer_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users — Canteen Food Ordering Admin</title>
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
        <a href="manage-categories.php">📂 Categories</a>
        <a href="orders.php">📋 All Orders</a>
        <a href="manage-users.php" class="active">👥 Users</a>
    </aside>

    <main class="admin-content">
        <h1 style="font-family:var(--font-display);font-size:1.8rem;margin-bottom:24px;">Users</h1>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= sanitize($user['name']) ?></strong></td>
                        <td><?= sanitize($user['email']) ?></td>
                        <td><?= $user['phone'] ? sanitize($user['phone']) : '—' ?></td>
                        <td><?= $user['order_count'] ?></td>
                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px;">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
