<?php
require_once 'COS/config.php';
requireLogin();

 $orderId = $_SESSION['last_order_id'] ?? null;
if (!$orderId) redirect('index.php');
unset($_SESSION['last_order_id']);

// Fetch order details
 $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
 $stmt->execute([$orderId]);
 $order = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed — Canteen Food Ordering</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">
            <div class="nav-brand-icon">🍽</div> Canteen Food Ordering
        </a>
    </div>
</nav>

<div class="success-container">
    <div class="success-icon">✓</div>
    <h1 style="font-family:var(--font-display);font-size:2rem;margin-bottom:8px;">Order Placed!</h1>
    <p style="color:var(--muted);margin-bottom:24px;">
        Your order <strong>#<?= str_pad($orderId, 5, '0', STR_PAD_LEFT) ?></strong> has been received and is being prepared.
    </p>

    <div style="background:var(--card);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);text-align:left;margin-bottom:28px;">
        <div class="summary-row"><span>Status</span><span class="status-badge status-pending">Pending</span></div>
        <div class="summary-row"><span>Payment</span><span style="text-transform:capitalize;"><?= sanitize($order['payment_method']) ?></span></div>
        <div class="summary-row"><span>Total</span><strong><?= formatPrice($order['total_amount']) ?></strong></div>
    </div>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="my-orders.php" class="btn-primary">View My Orders</a>
        <a href="index.php" class="btn-secondary">Order More</a>
    </div>
</div>

</body>
</html>