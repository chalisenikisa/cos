<?php
require_once 'COS/config.php';
header('Content-Type: application/json');

 $input = json_decode(file_get_contents('php://input'), true);
 $itemId = (int)($input['item_id'] ?? 0);

if ($itemId > 0 && isset($_SESSION['cart'][$itemId])) {
    unset($_SESSION['cart'][$itemId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Item not in cart.']);
}
