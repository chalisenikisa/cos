<?php
require_once '../COS/config.php';
header('Content-Type: application/json');

 $input = json_decode(file_get_contents('php://input'), true);
 $itemId = (int)($input['item_id'] ?? 0);
 $delta = (int)($input['delta'] ?? 0);

if ($itemId <= 0 || !isset($_SESSION['cart'][$itemId])) {
    echo json_encode(['success' => false, 'message' => 'Invalid item.']);
    exit();
}

 $_SESSION['cart'][$itemId] += $delta;

if ($_SESSION['cart'][$itemId] <= 0) {
    unset($_SESSION['cart'][$itemId]);
    echo json_encode(['success' => true, 'reload' => true]);
    exit();
}

 $qty = $_SESSION['cart'][$itemId];
 $stmt = $pdo->prepare("SELECT price FROM menu_items WHERE id = ?");
 $stmt->execute([$itemId]);
 $price = $stmt->fetchColumn();

 $itemTotal = $price * $qty;
 $subtotal = 0;
foreach ($_SESSION['cart'] as $id => $q) {
    $s = $pdo->prepare("SELECT price FROM menu_items WHERE id = ?");
    $s->execute([$id]);
    $subtotal += $s->fetchColumn() * $q;
}

echo json_encode([
    'success' => true,
    'quantity' => $qty,
    'item_total' => formatPrice($itemTotal),
    'subtotal' => formatPrice($subtotal),
    'total' => formatPrice($subtotal),
    'cart_count' => array_sum($_SESSION['cart'])
]);
