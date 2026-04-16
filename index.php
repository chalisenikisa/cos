<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'COS/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$customerId = $_SESSION['customer_id'] ?? null;
$isLoggedIn = isset($_SESSION['customer_id']);

$recommendations = getRecommendations($pdo, $customerId, 6);

$emojis = [
    'Rice Meals' => ['🍛','🍚','🥘','🍳','🥩'],
    'Noodles' => ['🍜','🍝','🥣'],
    'Snacks' => ['🥟','🍟','🍗','🧆'],
    'Beverages' => ['🧊','🥥','☕','🧋'],
    'Desserts' => ['🍮','🍧'],
    'Sandwiches' => ['🥪','🥪','🥪'],
];
$catEmojiMap = [];
$menuCatEmojiMap = [];

function getFoodEmoji($category, &$catMap, $emojiMap) {
    if (!isset($catMap[$category])) $catMap[$category] = 0;
    $list = $emojiMap[$category] ?? ['🍽'];
    $emoji = $list[$catMap[$category] % count($list)];
    $catMap[$category]++;
    return $emoji;
}
?>
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
                <li><a href="logout.php" class="cart-btn">Logout</a></li>
            <?php else: ?>
                <li><a href="COS/login.php">Sign In</a></li>
                <li><a href="register.php" class="btn-primary btn-sm">Create Account</a></li>
                <li><a href="COS/admin/login.php" class="btn-primary btn-sm">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

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

<?php if ($isLoggedIn): ?>
<?php 
$mostPopular = array_filter($recommendations, fn($r) => $r['type'] === 'most_popular');
$recommended = array_filter($recommendations, fn($r) => $r['type'] === 'recommended');
$recEmojiMap = ['Momo' => '🥟', 'Chowmein' => '🍜', 'Rice' => '🍛', 'Beverages' => '🧋', 'Snacks' => '🥪', 'Sweets' => '🍮'];
$recEmojis = ['🍛','🍜','🥟','🧋','🍮','🥪','🍕','🍔','🌮','🥗'];
?>
<?php if (!empty($mostPopular)): ?>
<div style="background:linear-gradient(135deg,#ff6b6b,#ee5a24);color:white;padding:25px;margin:20px 0;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.2);">
    <h2 style="margin:0 0 10px 0;font-size:24px;">🔥 Most Popular</h2>
    <p style="margin:0;font-size:14px;opacity:0.9;">Top ordered items by everyone</p>
    <div style="margin-top:15px;display:flex;gap:15px;overflow-x:auto;padding:10px 0;">
        <?php foreach ($mostPopular as $rec): ?>
            <?php if (!isset($rec['item'])) continue; 
                $item = $rec['item'];
                $cat = $item['category_name'] ?? '';
                $emoji = $recEmojiMap[$cat] ?? ($recEmojis[array_rand($recEmojis)]);
                $imgPath = 'COS/uploads/' . $item['image'];
                $hasImage = $item['image'] && file_exists($imgPath);
            ?>
            <div style="flex:0 0 220px;background:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <div style="height:140px;background:linear-gradient(135deg,#f5f7fa,#c3cfe2);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
                    <?php if ($hasImage): ?>
                        <img src="<?= $imgPath ?>" alt="<?= sanitize($item['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <span style="font-size:60px;"><?= $emoji ?></span>
                    <?php endif; ?>
                </div>
                <div style="padding:12px;">
                    <h3 style="margin:0 0 5px 0;font-size:16px;color:#333;"><?= sanitize($item['name']) ?></h3>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-weight:bold;font-size:18px;color:#E85D26;"><?= formatPrice($item['price']) ?></span>
                        <button class="add-cart-btn" data-id="<?= $item['id'] ?>" data-name="<?= sanitize($item['name']) ?>" style="background:#E85D26;color:white;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;font-size:12px;">+ Add</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($recommended)): ?>
<div style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;padding:25px;margin:20px 0;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.2);">
    <h2 style="margin:0 0 10px 0;font-size:24px;">🍴 Recommended For You</h2>
    <p style="margin:0;font-size:14px;opacity:0.9;">Based on popular categories</p>
    <div style="margin-top:15px;display:flex;gap:15px;overflow-x:auto;padding:10px 0;">
        <?php foreach ($recommended as $rec): ?>
            <?php if (!isset($rec['item'])) continue; 
                $item = $rec['item'];
                $cat = $item['category_name'] ?? '';
                $emoji = $recEmojiMap[$cat] ?? ($recEmojis[array_rand($recEmojis)]);
                $imgPath = 'COS/uploads/' . $item['image'];
                $hasImage = $item['image'] && file_exists($imgPath);
            ?>
            <div style="flex:0 0 220px;background:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <div style="height:140px;background:linear-gradient(135deg,#f5f7fa,#c3cfe2);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
                    <?php if ($hasImage): ?>
                        <img src="<?= $imgPath ?>" alt="<?= sanitize($item['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <span style="font-size:60px;"><?= $emoji ?></span>
                    <?php endif; ?>
                </div>
                <div style="padding:12px;">
                    <h3 style="margin:0 0 5px 0;font-size:16px;color:#333;"><?= sanitize($item['name']) ?></h3>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-weight:bold;font-size:18px;color:#E85D26;"><?= formatPrice($item['price']) ?></span>
                        <button class="add-cart-btn" data-id="<?= $item['id'] ?>" data-name="<?= sanitize($item['name']) ?>" style="background:#E85D26;color:white;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;font-size:12px;">+ Add</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="category-filter" id="menu">
    <button class="cat-btn active" data-category="all">All Items</button>
    <?php
    $cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    foreach ($cats as $cat):
    ?>
    <button class="cat-btn" data-category="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></button>
    <?php endforeach; ?>
</div>

<section class="menu-section">
    <h2>Today's Menu</h2>
    <p class="subtitle">Freshly prepared daily by our kitchen staff</p>

    <div class="menu-grid">
        <?php

        $items = $pdo->query("
            SELECT m.*, c.name AS category_name
            FROM menu_items m
            JOIN categories c ON m.category_id = c.id
            WHERE m.is_available = 1 AND (m.day_of_week IS NULL OR FIND_IN_SET('" . date('w') . "', m.day_of_week) > 0)
            ORDER BY c.name, m.name
        ")->fetchAll();

        foreach ($items as $item):
            $cat = $item['category_name'];
            $emoji = getFoodEmoji($cat, $menuCatEmojiMap, $emojis);
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