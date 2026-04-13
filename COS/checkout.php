<?php
require_once 'config.php';
requireLogin();

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) redirect('../cart.php');

$placeholders = implode(',', array_fill(0, count($cart), '?'));
$stmt = $pdo->prepare("SELECT id, price, name FROM menu_items WHERE id IN ($placeholders)");
$stmt->execute(array_keys($cart));
$menuItems = $stmt->fetchAll();

$total = 0;
foreach ($menuItems as $mi) {
    $total += $mi['price'] * $cart[$mi['id']];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment = sanitize($_POST['payment_method'] ?? 'cash');
    $notes = sanitize($_POST['notes'] ?? '');

    if (!in_array($payment, ['cash', 'gcash', 'card', 'esewa'])) {
        $errors[] = 'Invalid payment method.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO orders (customer_id, total_amount, status, payment_method, notes) VALUES (?, ?, 'pending', ?, ?)");
            $stmt->execute([$_SESSION['customer_id'], $total, $payment, $notes]);
            $orderId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($menuItems as $mi) {
                $stmt->execute([$orderId, $mi['id'], $cart[$mi['id']], $mi['price']]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            $_SESSION['last_order_id'] = $orderId;
            redirect('../order-success.php');
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
    <title>Checkout — Canteen Food Ordering</title>
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
            <li><a href="my-orders.php">My Orders</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
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
            <label class="payment-option">
                <input type="radio" name="payment_method" value="esewa">
                📲 eSewa
            </label>
        </div>

        <div id="esewa-qr" style="display:none;">
            <div style="background:var(--card);border-radius:var(--radius);padding:20px;text-align:center;margin-bottom:20px;box-shadow:var(--shadow);">
                <h4 style="margin:0 0 15px 0;">Scan to Pay with eSewa</h4>
                <img src="assets/esewa-qr.png" alt="eSewa QR" style="max-width:200px;width:100%;border-radius:8px;">
                <p style="margin:15px 0 5px 0;font-size:0.9rem;color:var(--text-light);">Total: <strong><?= formatPrice($total) ?></strong></p>
                <p style="margin:0;font-size:0.85rem;color:var(--text-light);">Send payment screenshot as proof</p>
            </div>
            <div class="form-group">
                <label for="payment_proof">Transaction ID (optional)</label>
                <input type="text" id="payment_proof" name="payment_proof" placeholder="Enter transaction ID">
            </div>
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

<footer class="footer">&copy; <?= date('Y') ?> Canteen Food Ordering</footer>
<script>
document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-option').forEach(function(opt) { opt.classList.remove('selected'); });
        this.closest('.payment-option').classList.add('selected');
        var qrSection = document.getElementById('esewa-qr');
        qrSection.style.display = this.value === 'esewa' ? 'block' : 'none';
    });
});
</script>
</body>
</html>