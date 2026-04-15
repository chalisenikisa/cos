<?php
require_once '../config.php';
header('Content-Type: application/json');

$count = 0;
if (isLoggedIn() && isset($_SESSION['cart'])) {
    $count = array_sum($_SESSION['cart']);
}
echo json_encode(['count' => $count]);

if (isset($_GET['debug'])) {
    error_log("cart-count: isLoggedIn=" . (isLoggedIn() ? 'yes' : 'no') . ", cart=" . print_r($_SESSION['cart'] ?? [], true));
}