<?php
require_once '../config.php';
requireAdmin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $icon = sanitize($_POST['icon'] ?? '');

    if (empty($name)) $errors[] = 'Category name is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
        $stmt->execute([$name, $icon]);
        flash('success', 'Category added successfully.');
        redirect('manage-categories.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category — Canteen Food Ordering Admin</title>
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
        <a href="manage-users.php">👥 Users</a>
    </aside>

    <main class="admin-content">
        <h1 style="font-family:var(--font-display);font-size:1.8rem;margin-bottom:24px;">Add Category</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?></div>
        <?php endif; ?>

        <div class="admin-form-card">
            <h3>Category Details</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" value="<?= $_POST['name'] ?? '' ?>" placeholder="e.g., Beverages, Desserts" required>
                </div>
                <div class="form-group">
                    <label for="icon">Icon (optional)</label>
                    <input type="text" id="icon" name="icon" value="<?= $_POST['icon'] ?? '' ?>" placeholder="e.g., mug-hot, ice-cream">
                </div>
                <button type="submit" class="btn-primary" style="margin-top:8px;">Add Category</button>
                <a href="manage-categories.php" class="btn-secondary" style="margin-left:12px;">Cancel</a>
            </form>
        </div>
    </main>
</div>

</body>
</html>
