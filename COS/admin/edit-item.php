<?php
require_once '../config.php';
requireAdmin();

 $id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('manage-menu.php');

 $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
 $stmt->execute([$id]);
 $item = $stmt->fetch();
if (!$item) redirect('manage-menu.php');

 $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catId = (int)($_POST['category_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $available = isset($_POST['is_available']) ? 1 : 0;

    if ($catId <= 0) $errors[] = 'Select a category.';
    if (empty($name)) $errors[] = 'Item name is required.';
    if ($price <= 0) $errors[] = 'Price must be greater than zero.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE menu_items SET category_id=?, name=?, description=?, price=?, is_available=? WHERE id=?");
        $stmt->execute([$catId, $name, $desc, $price, $available, $id]);
        flash('success', 'Item updated successfully.');
        redirect('manage-menu.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item — CanteenOS Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="dashboard.php" class="nav-brand">
            <div class="nav-brand-icon">🍽</div> CanteenOS
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
        <h1 style="font-family:var(--font-display);font-size:1.8rem;margin-bottom:24px;">Edit Menu Item</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?></div>
        <?php endif; ?>

        <div class="admin-form-card">
            <h3>Item Details</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($item['category_id'] == $c['id']) ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="name">Item Name</label>
                    <input type="text" id="name" name="name" value="<?= sanitize($item['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= sanitize($item['description']) ?></textarea>
                </div>
                <div class="admin-form-row">
                    <div class="form-group">
                        <label for="price">Price (₱)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?= $item['price'] ?>" required>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="is_available" value="1" <?= $item['is_available'] ? 'checked' : '' ?> style="width:18px;height:18px;">
                            Available
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="margin-top:8px;">Save Changes</button>
                <a href="manage-menu.php" class="btn-secondary" style="margin-left:12px;">Cancel</a>
            </form>
        </div>
    </main>
</div>

</body>
</html>