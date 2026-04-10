<?php
require_once '../config.php';
header('Content-Type: application/json');

 $count = 0;
if (isLoggedIn() && isset($_SESSION['cart'])) {
    $count = array_sum($_SESSION['cart']);
}
echo json_encode(['count' => $count]);