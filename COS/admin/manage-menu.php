<?php
require_once '../config.php';
requireAdmin();

 $items = $pdo->query("
    SELECT m.*, c.name AS category_name
    FROM menu_items m
    JOIN categories c ON m.category_id = c.id
    ORDER BY c.name, m.name
")->fetchAll();

$dayLabels = ['0' => 'Sun', '1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri', '6' => 'Sat'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu — Canteen Food Ordering Admin</title>
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
        <a href="manage-menu.php" class="active">🍱 Menu Items</a>
        <a href="orders.php">📋 All Orders</a>
        <a href="add-item.php">➕ Add Item</a>
    </aside>

    <main class="admin-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h1 style="font-family:var(--font-display);font-size:1.8rem;">Menu Items</h1>
            <a href="add-item.php" class="btn-primary btn-sm">+ Add New Item</a>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Days</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><strong><?= sanitize($item['name']) ?></strong></td>
                        <td><?= sanitize($item['category_name']) ?></td>
                        <td><?= formatPrice($item['price']) ?></td>
                        <td>
                            <?php
                            $itemDays = isset($item['day_of_week']) && $item['day_of_week'] ? explode(',', $item['day_of_week']) : [];
                            if (empty($itemDays)): ?>
                                <span style="color:#888;">All days</span>
                            <?php else: ?>
                                <?php foreach ($itemDays as $d): ?>
                                    <span style="background:var(--primary);color:#fff;padding:2px 6px;border-radius:4px;font-size:0.75rem;margin-right:2px;"><?= $dayLabels[$d] ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['is_available']): ?>
                                <span style="color:var(--success);font-weight:600;">Yes</span>
                            <?php else: ?>
                                <span style="color:var(--danger);font-weight:600;">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="edit-item.php?id=<?= $item['id'] ?>" class="btn-secondary btn-sm">Edit</a>
                            <a href="delete-item.php?id=<?= $item['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this item?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>