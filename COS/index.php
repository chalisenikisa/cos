<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Food Ordering — Order Fresh, Eat Well</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .food-emoji { font-size: 3.5rem; line-height: 1; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">
            <div class="nav-brand-icon">🍽</div>
            Canteen Food Ordering
        </a>
        <ul class="nav-links">
            <?php if (isLoggedIn()): ?>
                <li><a href="my-orders.php">📋 My Orders</a></li>
                <li>
                    <button class="cart-btn" id="cart-toggle">
                        🛒 Cart <span class="cart-badge" style="display:none">0</span>
                    </button>
                </li>
                <li><a href="../logout.php" class="cart-btn">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Sign In</a></li>
                <li><a href="register.php" class="btn-primary btn-sm">Create Account</a></li>
                <li><a href="admin/login.php" class="btn-primary btn-sm">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-text">
        <h1>Good Food,<br><span>Fast Service</span></h1>
        <p>Skip the long queue. Browse our menu, customize your meal, and pick it up piping hot — all from your phone.</p>
        <a href="#menu" class="btn-primary" style="font-size:1rem;padding:14px 32px;">Browse Menu ↓</a>
    </div>
    <div class="hero-visual">
        <div class="hero-plate">🍛</div>
        <div class="hero-stat">🔥 <span>50+</span> Items</div>
        <div class="hero-stat">⚡ <span>5 min</span> Avg. Wait</div>
    </div>
</section>

<!-- CATEGORY FILTER -->
<div class="category-filter" id="menu">
    <button class="cat-btn active" data-category="all">All Items</button>
    <?php
    $cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    foreach ($cats as $cat):
    ?>
    <button class="cat-btn" data-category="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></button>
    <?php endforeach; ?>
</div>

<!-- MENU GRID -->
<section class="menu-section">
    <h2>Today's Menu</h2>
    <p class="subtitle">Freshly prepared daily by our kitchen staff</p>

    <div class="menu-grid">
        <?php
        // Food emoji mapping by category
        $emojis = [
            'Rice Meals' => ['🍛','🍚','🥘','🍳','🥩'],
            'Noodles' => ['🍜','🍝','🥣'],
            'Snacks' => ['🥟','🍟','🍗','🧆'],
            'Beverages' => ['🧊','🥥','☕','🧋'],
            'Desserts' => ['🍮','🍧'],
            'Sandwiches' => ['🥪','🥪','🥪'],
        ];

        $items = $pdo->query("
            SELECT m.*, c.name AS category_name
            FROM menu_items m
            JOIN categories c ON m.category_id = c.id
            ORDER BY c.name, m.name
        ")->fetchAll();

        $catEmojiIndex = [];
        foreach ($items as $item):
            $cat = $item['category_name'];
            if (!isset($catEmojiIndex[$cat])) $catEmojiIndex[$cat] = 0;
            $emojiList = $emojis[$cat] ?? ['🍽'];
            $emoji = $emojiList[$catEmojiIndex[$cat] % count($emojiList)];
            $catEmojiIndex[$cat]++;
        ?>
        <div class="food-card <?= $item['is_available'] ? '' : 'unavailable' ?>" data-category="<?= $item['category_id'] ?>">
            <div class="food-card-img">
                <span class="food-emoji"><?= $emoji ?></span>
                <span class="food-card-category"><?= sanitize($cat) ?></span>
            </div>
            <div class="food-card-body">
                <h3><?= sanitize($item['name']) ?></h3>
                <p class="desc"><?= sanitize($item['description']) ?></p>
                <div class="food-card-footer">
                    <span class="food-price"><?= formatPrice($item['price']) ?></span>
                    <button class="add-cart-btn" data-id="<?= $item['id'] ?>" data-name="<?= sanitize($item['name']) ?>" title="Add to cart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<footer class="footer">
    &copy; <?= date('Y') ?> Canteen Food Ordering. Built for hungry students and staff.
</footer>

<script src="assets/appli.js"></script>
</body>
</html>