<?php
require_once __DIR__ . '/COS/config.php';
$_SESSION = array();
session_destroy();
redirect('../index.php');