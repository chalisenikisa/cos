<?php
require_once __DIR__ . '/COS/config.php';
$_SESSION = array();
session_destroy();
session_start();
session_regenerate_id(true);
redirect('index.php');