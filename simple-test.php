<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>Test</title></head>
<body>
<h1>Simple Test</h1>
<p>Session ID: <?= session_id() ?></p>
<p>customer_id: <?= isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 'NOT SET' ?></p>
<p>Is Logged In: <?= isset($_SESSION['customer_id']) ? 'YES' : 'NO' ?></p>

<h2>All Session Data:</h2>
<pre><?= print_r($_SESSION, true) ?></pre>

<a href="COS/login.php">Go to Login</a>
</body>
</html>