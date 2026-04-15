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
    return isset($_SESSION['customer_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
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
    try {
        $recommendations = [];
        $excludedIds = [];
        
        if ($customerId) {
            $userOrderItems = $pdo->prepare("
                SELECT DISTINCT oi.menu_item_id 
                FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE o.customer_id = ? 
                ORDER BY o.created_at DESC 
                LIMIT 50
            ");
            $userOrderItems->execute([$customerId]);
            $userItemIds = $userOrderItems->fetchAll(PDO::FETCH_COLUMN);
            $excludedIds = $userItemIds;
            
            if (!empty($userItemIds)) {
                $placeholders = implode(',', array_fill(0, count($userItemIds), '?'));
                
                $freqTogether = $pdo->prepare("
                    SELECT oi2.menu_item_id, COUNT(*) as freq
                    FROM order_items oi1
                    JOIN orders o1 ON oi1.order_id = o1.id
                    JOIN order_items oi2 ON oi1.order_id = oi2.order_id
                    JOIN orders o2 ON oi2.order_id = o2.id
                    WHERE oi1.menu_item_id IN ($placeholders)
                    AND oi2.menu_item_id NOT IN ($placeholders)
                    AND o2.customer_id = ?
                    GROUP BY oi2.menu_item_id
                    ORDER BY freq DESC
                    LIMIT 4
                ");
                $freqTogether->execute(array_merge($userItemIds, $userItemIds, [$customerId]));
                $frequentlyBought = $freqTogether->fetchAll();
                
                foreach ($frequentlyBought as $item) {
                    $recommendations[] = [
                        'type' => 'frequently_bought',
                        'reason' => 'Frequently ordered together',
                        'item_id' => $item['menu_item_id']
                    ];
                }
            }
            
            if (count($recommendations) < $limit) {
                $userCategories = $pdo->prepare("
                    SELECT DISTINCT m.category_id
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    JOIN menu_items m ON oi.menu_item_id = m.id
                    WHERE o.customer_id = ?
                    ORDER BY oi.id DESC
                    LIMIT 3
                ");
                $userCategories->execute([$customerId]);
                $favCategories = $userCategories->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($favCategories)) {
                    $catPlaceholders = implode(',', array_fill(0, count($favCategories), '?'));
                    $excludeStr = !empty($excludedIds) ? "AND m.id NOT IN (" . implode(',', array_fill(0, count($excludedIds), '?')) . ")" : "";
                    $params = array_merge($favCategories, $excludedIds);
                    
                    $catRecs = $pdo->prepare("
                        SELECT m.id, COUNT(*) as order_count
                        FROM menu_items m
                        JOIN order_items oi ON m.id = oi.menu_item_id
                        WHERE m.category_id IN ($catPlaceholders)
                        AND m.is_available = 1
                        $excludeStr
                        GROUP BY m.id
                        ORDER BY order_count DESC
                        LIMIT " . ($limit - count($recommendations))
                    );
                    $catRecs->execute($params);
                    $catRecommendations = $catRecs->fetchAll();
                    
                    foreach ($catRecommendations as $item) {
                        $recommendations[] = [
                            'type' => 'category_favorite',
                            'reason' => 'Based on your favorite categories',
                            'item_id' => $item['id']
                        ];
                    }
                }
            }
        }
        
        $excludeForPopular = array_merge($excludedIds, array_column($recommendations, 'item_id'));
        
        if (!empty($excludeForPopular)) {
            $popularPlaceholders = "AND m.id NOT IN (" . implode(',', array_fill(0, count($excludeForPopular), '?')) . ")";
            $popularParams = $excludeForPopular;
            
            $popular = $pdo->prepare("
                SELECT m.id, COUNT(oi.id) as order_count
                FROM menu_items m
                LEFT JOIN order_items oi ON m.id = oi.menu_item_id
                WHERE m.is_available = 1
                AND (m.day_of_week IS NULL OR FIND_IN_SET('" . date('w') . "', m.day_of_week) > 0)
                $popularPlaceholders
                GROUP BY m.id
                ORDER BY order_count DESC
                LIMIT " . ($limit - count($recommendations))
            );
            $popular->execute($popularParams);
        } else {
            $popular = $pdo->query("
                SELECT m.id, COUNT(oi.id) as order_count
                FROM menu_items m
                LEFT JOIN order_items oi ON m.id = oi.menu_item_id
                WHERE m.is_available = 1
                AND (m.day_of_week IS NULL OR FIND_IN_SET('" . date('w') . "', m.day_of_week) > 0)
                GROUP BY m.id
                ORDER BY order_count DESC
                LIMIT " . ($limit - count($recommendations))
            );
        }
        $popularItems = $popular->fetchAll();
        
        foreach ($popularItems as $item) {
            $recommendations[] = [
                'type' => 'popular',
                'reason' => 'Popular this week',
                'item_id' => $item['id']
            ];
        }
        
        if (count($recommendations) < $limit) {
            $remaining = $limit - count($recommendations);
            $alreadyIncluded = array_merge($excludedIds, array_column($recommendations, 'item_id'));
            
            if (!empty($alreadyIncluded)) {
                $remainingPlaceholders = "AND m.id NOT IN (" . implode(',', array_fill(0, count($alreadyIncluded), '?')) . ")";
                
                $newItems = $pdo->prepare("
                    SELECT m.id
                    FROM menu_items m
                    WHERE m.is_available = 1
                    AND (m.day_of_week IS NULL OR FIND_IN_SET('" . date('w') . "', m.day_of_week) > 0)
                    $remainingPlaceholders
                    ORDER BY RAND()
                    LIMIT $remaining
                ");
                $newItems->execute($alreadyIncluded);
            } else {
                $newItems = $pdo->query("
                    SELECT m.id
                    FROM menu_items m
                    WHERE m.is_available = 1
                    AND (m.day_of_week IS NULL OR FIND_IN_SET('" . date('w') . "', m.day_of_week) > 0)
                    ORDER BY RAND()
                    LIMIT $remaining
                ");
            }
            $newItemIds = $newItems->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($newItemIds as $itemId) {
                $recommendations[] = [
                    'type' => 'explore',
                    'reason' => 'You might like this',
                    'item_id' => $itemId
                ];
            }
        }
        
        $recommendations = array_slice($recommendations, 0, $limit);
        
        if (!empty($recommendations)) {
            $recIds = array_column($recommendations, 'item_id');
            $placeholders = implode(',', array_fill(0, count($recIds), '?'));
            
            $itemsQuery = $pdo->prepare("
                SELECT m.*, c.name AS category_name
                FROM menu_items m
                JOIN categories c ON m.category_id = c.id
                WHERE m.id IN ($placeholders)
            ");
            $itemsQuery->execute($recIds);
            $items = $itemsQuery->fetchAll();
            
            $itemsById = [];
            foreach ($items as $item) {
                $itemsById[$item['id']] = $item;
            }
            
            foreach ($recommendations as &$rec) {
                if (isset($itemsById[$rec['item_id']])) {
                    $rec['item'] = $itemsById[$rec['item_id']];
                }
            }
        }
        
        return $recommendations;
    } catch (Exception $e) {
        error_log("Recommendation error: " . $e->getMessage());
        return [];
    }
}
?>