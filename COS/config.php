<?php
// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'canteen_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PDO connection with error handling
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['customer_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('admin/login.php');
    }
}

function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

function formatPrice($price) {
    return '₹' . number_format((int)$price);
}

function getRecommendations($pdo, $customerId = null, $limit = 6) {
    $recommendations = [];
    
    $halfLimit = (int)($limit / 2);
    if ($halfLimit < 1) $halfLimit = 1;
    
    $stmt = $pdo->prepare("
        SELECT m.*, c.name AS category_name,
               COUNT(oi.id) as order_count
        FROM menu_items m
        JOIN categories c ON m.category_id = c.id
        LEFT JOIN order_items oi ON oi.menu_item_id = m.id
        LEFT JOIN orders o ON o.id = oi.order_id AND o.status != 'cancelled'
        WHERE m.is_available = 1
        GROUP BY m.id
        ORDER BY order_count DESC, m.name ASC
        LIMIT ?
    ");
    $stmt->bindValue(1, $halfLimit, PDO::PARAM_INT);
    $stmt->execute();
    $mostOrdered = $stmt->fetchAll();
    
    foreach ($mostOrdered as $item) {
        $recommendations[] = [
            'type' => 'most_popular',
            'reason' => 'Most Popular',
            'item' => $item
        ];
    }
    
    if ($customerId) {
        $stmt = $pdo->prepare("
            SELECT m.*, c.name AS category_name,
                   COUNT(oi.id) as category_order_count
            FROM menu_items m
            JOIN categories c ON m.category_id = c.id
            LEFT JOIN order_items oi ON oi.menu_item_id = m.id
            LEFT JOIN orders o ON o.id = oi.order_id AND o.status != 'cancelled'
            WHERE m.is_available = 1
              AND m.id NOT IN (
                  SELECT DISTINCT oi2.menu_item_id 
                  FROM order_items oi2 
                  JOIN orders o2 ON o2.id = oi2.order_id 
                  WHERE o2.customer_id = ? AND o2.status != 'cancelled'
              )
            GROUP BY m.id
            ORDER BY category_order_count DESC, m.name ASC
            LIMIT ?
        ");
        $stmt->execute([$customerId, $halfLimit]);
    } else {
        $stmt = $pdo->prepare("
            SELECT m.*, c.name AS category_name
            FROM menu_items m
            JOIN categories c ON m.category_id = c.id
            WHERE m.is_available = 1
            ORDER BY RAND()
            LIMIT ?
        ");
        $stmt->bindValue(1, $halfLimit, PDO::PARAM_INT);
        $stmt->execute();
    }
    $recommended = $stmt->fetchAll();
    
    foreach ($recommended as $item) {
        $recommendations[] = [
            'type' => 'recommended',
            'reason' => 'Recommended for you',
            'item' => $item
        ];
    }
    
    return $recommendations;
}
?>