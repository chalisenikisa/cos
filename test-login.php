<?php
session_start();
$loggedIn = isset($_SESSION['customer_id']);
$cid = $_SESSION['customer_id'] ?? 'none';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Page</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-box { background: #90EE90; padding: 20px; margin: 20px 0; border: 3px solid green; }
        .error-box { background: #FFB6C1; padding: 20px; margin: 20px 0; border: 3px solid red; }
    </style>
</head>
<body>
    <h1>🍴 Canteen Test Page</h1>
    
    <?php if($loggedIn): ?>
    <div class="test-box">
        <h2>✅ LOGGED IN</h2>
        <p>Customer ID: <strong><?= $cid ?></strong></p>
    </div>
    <?php else: ?>
    <div class="error-box">
        <h2>❌ NOT LOGGED IN</h2>
        <p>session_id: <?= session_id() ?></p>
        <p>Session is empty or customer_id not set</p>
    </div>
    <?php endif; ?>
    
    <h3>Full Session Data:</h3>
    <pre><?= print_r($_SESSION, true) ?></pre>
    
    <hr>
    <a href="COS/login.php">Login</a> | 
    <a href="index.php">Main Index</a> | 
    <a href="logout.php">Logout</a>
</body>
</html>