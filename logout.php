<?php
require_once __DIR__ . '/COS/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = array();
session_destroy();
redirect('index.php');