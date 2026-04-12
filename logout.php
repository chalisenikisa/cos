<?php
require_once __DIR__ . '/COS/config.php';
$_SESSION = [];
session_destroy();
redirect('COS/index.php');