<?php
require_once 'COS/config.php';
requireLogin();

// Get cart from session
 $cart = $_SESSION['cart'] ?? [];
 $cartItems = [];
 $total = 0;

if (!empty($cart)) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("
        SELECT m.*, c.name AS category_name
        FROM menu_items m
        JOIN categories c ON m.category_id = c.id
        WHERE m.id IN ($placeholders)
    ");
    $stmt->execute(array_values($cart));
    $items = $stmt->fetchAll();

    // Emoji map
    $emojis = [
        'Rice Meals'=>'🍛','Noodles'=>'🍜','Snacks'=>'🥟',
        'Beverages'=>'🧋','Desserts'=>'🍮','Sandwiches'=>'🥪'
    ];

    foreach ($items as $item) {
        $qty = $cart[$item['id']];
        $itemTotal = $item['price'] * $qty;
        $total += $itemTotal;
        $cartItems[] = [
            'item' => $item,
            'qty' => $qty,
            'total' => $itemTotal,
            'emoji' => $emojis[$item['category_name']] ?? '🍽'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart — Canteen Food Ordering</title>
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
            <li>
                <a href="cart.php" class="cart-btn" style="pointer-events:none;">
                    🛒 Cart <span class="cart-badge"><?= array_sum($cart) ?></span>
                </a>
            </li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="page-header">
    <h1>Your Cart</h1>
    <p><?= count($cartItems) ?> item(s) in your cart</p>
</div>

<?php if (empty($cartItems)): ?>
<div class="cart-container">
    <div class="cart-empty">
        <div class="icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything yet.</p>
        <br>
        <a href="COS/index.php" class="btn-primary">Browse Menu</a>
    </div>
</div>
<?php else: ?>
<div class="cart-container">
    <div class="cart-items">
        <?php foreach ($cartItems as $ci): ?>
        <div class="cart-item" data-id="<?= $ci['item']['id'] ?>">
            <div class="cart-item-img"><?= $ci['emoji'] ?></div>
            <div class="cart-item-info">
                <h3><?= sanitize($ci['item']['name']) ?></h3>
                <div class="price item-total"><?= formatPrice($ci['total']) ?></div>
            </div>
            <div class="qty-control">
                <button class="qty-minus">−</button>
                <span class="qty-value"><?= $ci['qty'] ?></span>
                <button class="qty-plus">+</button>
            </div>
            <button class="cart-item-remove" title="Remove">✕</button>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="cart-summary">
        <h3>Order Summary</h3>
        <div class="summary-row">
            <span>Subtotal (<?= array_sum($cart) ?> items)</span>
            <span class="summary-subtotal"><?= formatPrice($total) ?></span>
        </div>
        <div class="summary-row">
            <span>Service fee</span>
            <span>Free</span>
        </div>
        <div class="summary-row total">
            <span>Total</span>
            <span class="summary-total"><?= formatPrice($total) ?></span>
        </div>
        <a href="COS/checkout.php" class="btn-primary" style="width:100%;justify-content:center;margin-top:20px;padding:14px;">Proceed to Checkout</a>
        <a href="COS/index.php" class="btn-secondary" style="width:100%;justify-content:center;margin-top:10px;padding:12px;">Continue Shopping</a>
    </div>
</div>
<?php endif; ?>

<footer class="footer">&copy; <?= date('Y') ?> Canteen Food Ordering</footer>
<script src="assets/appli.js"></script>
</body>
</html>