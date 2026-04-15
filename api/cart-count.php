<?php
require_once '../config.php';
header('Content-Type: application/json');

ini_set('session.cookie_path', '/');
$count = 0;
if (isLoggedIn() && isset($_SESSION['cart'])) {
    $count = array_sum($_SESSION['cart']);
}
echo json_encode(['count' => $count]);
echo json_encode(['count' => $count]);
