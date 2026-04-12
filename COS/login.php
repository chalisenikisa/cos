<?php
require_once 'config.php';

if (isLoggedIn()) redirect('index.php');

 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];

            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirect);
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Canteen Food Ordering</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">
            <div class="nav-brand-icon">🍽</div> Canteen Food Ordering
        </a>
    </div>
</nav>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to continue ordering</p>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>
</div>

</body>
</html>