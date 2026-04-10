<?php
require_once 'config.php';
requireLogin();

 $cart = $_SESSION['cart'] ?? [];
if (empty($cart)) redirect('cart.php');

// Calculate total
 $placeholders = implode(',', array_fill(0, count($cart), '?'));
 $stmt = $pdo->prepare("SELECT id, price, name FROM menu_items WHERE id IN ($placeholders)");
 $stmt->execute(array_values($cart));
 $menuItems = $stmt->fetchAll();

 $total = 0;
foreach ($menuItems as $mi) {
    $total += $mi['price'] * $cart[$mi['id']];
}

 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment = sanitize($_POST['payment_method'] ?? 'cash');
    $notes = sanitize($_POST['notes'] ?? '');

    if (!in_array($payment, ['cash', 'gcash', 'card'])) {
        $errors[] = 'Invalid payment method.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (customer_id, total_amount, status, payment_method, notes) VALUES (?, ?, 'pending', ?, ?)");
            $stmt->execute([$_SESSION['customer_id'], $total, $payment, $notes]);
            $orderId = $pdo->lastInsertId();

            // Insert order items
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($menuItems as $mi) {
                $stmt->execute([$orderId, $mi['id'], $cart[$mi['id']], $mi['price']]);
            }

            $pdo->commit();

            // Clear cart
            unset($_SESSION['cart']);

            $_SESSION['last_order_id'] = $orderId;
            redirect('order-success.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — CanteenOS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">
            <div class="nav-brand-icon">🍽</div> CanteenOS
        </a>
    </div>
</nav>

<div class="page-header">
    <h1>Checkout</h1>
    <p>Complete your order below</p>
</div>

<div class="checkout-container">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <h3 style="margin-bottom:16px;">Payment Method</h3>
        <div class="payment-options">
            <label class="payment-option selected">
                <input type="radio" name="payment_method" value="cash" checked>
                💵 Cash
            </label>
            <label class="payment-option">
                <input type="radio" name="payment_method" value="gcash">
                📱 GCash
            </label>
            <label class="payment-option">
                <input type="radio" name="payment_method" value="card">
                💳 Card
            </label>
        </div>

        <div class="form-group">
            <label for="notes">Order Notes (optional)</label>
            <textarea id="notes" name="notes" placeholder="e.g. No onions, extra spicy..."></textarea>
        </div>

        <div style="background:var(--card);border-radius:var(--radius);padding:20px;margin-bottom:20px;box-shadow:var(--shadow);">
            <div class="summary-row"><span>Order Total</span><strong><?= formatPrice($total) ?></strong></div>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:1rem;">
            Place Order — <?= formatPrice($total) ?>
        </button>
        <a href="cart.php" class="btn-secondary" style="display:block;text-align:center;margin-top:10px;padding:12px;">Back to Cart</a>
    </form>
</div>

<footer class="footer">&copy; <?= date('Y') ?> CanteenOS</footer>
<script src="assets/app.js"></script>
</body>
</html>