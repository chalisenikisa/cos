<?php
require_once 'config.php';

// Redirect if already logged in  change
if (isLoggedIn()) redirect('index.php');

 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO customers (name, email, password, phone) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed, $phone]);

            $_SESSION['customer_id'] = $pdo->lastInsertId();
            $_SESSION['customer_name'] = $name;
            flash('success', 'Account created successfully! Welcome, ' . $name);
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — CanteenOS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">
            <div class="nav-brand-icon">🍽</div> CanteenOS
        </a>
    </div>
</nav>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p class="subtitle">Join to start ordering from the canteen</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <div><?= $e ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?= $_POST['name'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number (optional)</label>
                <input type="text" id="phone" name="phone" value="<?= $_POST['phone'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:8px;">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</div>

</body>
</html>