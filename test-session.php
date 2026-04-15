<?php
session_start();

echo "<h1>Test Page</h1>";
echo "<pre>";
echo "SESSION DATA:\n";
print_r($_SESSION);
echo "\n\nsession_id: " . session_id() . "\n";
echo "customer_id in session: " . (isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 'NOT SET') . "\n";
echo "</pre>";

if (isset($_SESSION['customer_id'])) {
    echo "<h2 style='color:green'>✅ User IS logged in!</h2>";
    echo "<p>Customer ID: " . $_SESSION['customer_id'] . "</p>";
} else {
    echo "<h2 style='color:red'>❌ User is NOT logged in</h2>";
}
?>
<a href="COS/login.php">Go to Login</a> | <a href="index.php">Go to Index</a> | <a href="logout.php">Logout</a>