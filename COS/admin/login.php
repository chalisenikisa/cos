<?php
require_once '../config.php';

if (isAdminLoggedIn()) redirect('dashboard.php');

 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        redirect('dashboard.php');
    } else {
        $errors[] = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — CanteenOS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="../index.php" class="nav-brand">
            <div class="nav-brand-icon">🍽</div> CanteenOS <span style="font-size:0.7rem;background:var(--accent);color:#fff;padding:2px 8px;border-radius:50px;margin-left:6px;">Admin</span>
        </a>
    </div>
</nav>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Admin Login</h2>
        <p class="subtitle">Enter your credentials to access the admin panel</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Sign In</button>
        </form>

        <div class="auth-footer">
            <a href="../index.php">← Back to Canteen</a>
        </div>
    </div>
</div>

</body>
</html>