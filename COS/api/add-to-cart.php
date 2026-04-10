<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please sign in first.']);
    exit();
}

 $input = json_decode(file_get_contents('php://input'), true);
 $itemId = (int)($input['item_id'] ?? 0);

if ($itemId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item.']);
    exit();
}

// Verify item exists and is available
 $stmt = $pdo->prepare("SELECT id, name, is_available FROM menu_items WHERE id = ?");
 $stmt->execute([$itemId]);
 $item = $stmt->fetch();

if (!$item || !$item['is_available']) {
    echo json_encode(['success' => false, 'message' => 'Item is not available.']);
    exit();
}

// Add to session cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_SESSION['cart'][$itemId])) {
    if ($_SESSION['cart'][$itemId] >= 20) {
        echo json_encode(['success' => false, 'message' => 'Maximum 20 per item.']);
        exit();
    }
    $_SESSION['cart'][$itemId]++;
} else {
    $_SESSION['cart'][$itemId] = 1;
}

 $cartCount = array_sum($_SESSION['cart']);

echo json_encode(['success' => true, 'cart_count' => $cartCount]);