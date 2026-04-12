<?php
require_once '../config.php';
requireAdmin();

$categories = $pdo->query("
    SELECT c.*, COUNT(m.id) AS item_count
    FROM categories c
    LEFT JOIN menu_items m ON c.id = m.category_id
    GROUP BY c.id
    ORDER BY c.name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories — Canteen Food Ordering Admin</title>
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
        <a href="manage-categories.php" class="active">📂 Categories</a>
        <a href="orders.php">📋 All Orders</a>
        <a href="manage-users.php">👥 Users</a>
    </aside>

    <main class="admin-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h1 style="font-family:var(--font-display);font-size:1.8rem;">Categories</h1>
            <a href="add-category.php" class="btn-primary btn-sm">+ Add Category</a>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Icon</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><strong><?= sanitize($cat['name']) ?></strong></td>
                        <td><?= sanitize($cat['icon']) ?: '—' ?></td>
                        <td><?= $cat['item_count'] ?> item(s)</td>
                        <td class="actions">
                            <a href="edit-category.php?id=<?= $cat['id'] ?>" class="btn-secondary btn-sm">Edit</a>
                            <?php if ($cat['item_count'] == 0): ?>
                            <a href="delete-category.php?id=<?= $cat['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this category?')">Delete</a>
                            <?php else: ?>
                            <span class="btn-sm" style="opacity:0.5;cursor:not-allowed;" title="Has items">Delete</span>
                            <?php endif; ?>
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
