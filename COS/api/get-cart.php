<?php
require_once '../config.php';
header('Content-Type: application/json');

$cart = $_SESSION['cart'] ?? [];
$items = [];

if (!empty($cart) && isLoggedIn()) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("
        SELECT m.*, c.name AS category_name
        FROM menu_items m
        JOIN categories c ON m.category_id = c.id
        WHERE m.id IN ($placeholders)
    ");
    $stmt->execute(array_values($cart));
    $menuItems = $stmt->fetchAll();

    $emojis = [
        'Rice Meals' => '🍛', 'Noodles' => '🍜', 'Snacks' => '🥟',
        'Beverages' => '🧋', 'Desserts' => '🍮', 'Sandwiches' => '🥪'
    ];

    $total = 0;
    foreach ($menuItems as $item) {
        $qty = $cart[$item['id']];
        $itemTotal = $item['price'] * $qty;
        $total += $itemTotal;
        $items[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'qty' => $qty,
            'total' => $itemTotal,
            'emoji' => $emojis[$item['category_name']] ?? '🍽'
        ];
    }

    echo json_encode(['success' => true, 'items' => $items, 'total' => $total, 'count' => array_sum($cart)]);
} else {
    echo json_encode(['success' => true, 'items' => [], 'total' => 0, 'count' => 0]);
}
