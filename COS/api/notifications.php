<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lowStockThreshold = 10;
    
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE stock_quantity IS NOT NULL AND stock_quantity <= ? ORDER BY stock_quantity ASC");
    $stmt->execute([$lowStockThreshold]);
    $lowStockItems = $stmt->fetchAll();
    
    foreach ($lowStockItems as $item) {
        $checkStmt = $pdo->prepare("SELECT id FROM notifications WHERE type = 'low_stock' AND item_id = ? AND is_read = 0");
        $checkStmt->execute([$item['id']]);
        $existing = $checkStmt->fetch();
        
        if (!$existing) {
            $insertStmt = $pdo->prepare("INSERT INTO notifications (type, title, message, item_id) VALUES ('low_stock', ?, ?, ?)");
            $insertStmt->execute([
                'Low Stock Alert',
                $item['name'] . ' is running low (' . $item['stock_quantity'] . ' units remaining)',
                $item['id']
            ]);
        }
    }
    
    $notifications = $pdo->query("SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 20")->fetchAll();
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'count' => count($notifications)
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    if ($action === 'mark_read') {
        $id = intval($data['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
        exit;
    }
    
    if ($action === 'mark_all_read') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($action === 'dismiss') {
        $id = intval($data['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
        exit;
    }
    
    if ($action === 'update_stock') {
        $itemId = intval($data['item_id'] ?? 0);
        $quantity = intval($data['quantity'] ?? 0);
        
        if ($itemId > 0 && $quantity >= 0) {
            $stmt = $pdo->prepare("UPDATE menu_items SET stock_quantity = ? WHERE id = ?");
            $stmt->execute([$quantity, $itemId]);
            
            $checkStmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND stock_quantity <= 10");
            $checkStmt->execute([$itemId]);
            $item = $checkStmt->fetch();
            
            if ($item && $item['stock_quantity'] > 0) {
                $notifStmt = $pdo->prepare("DELETE FROM notifications WHERE type = 'low_stock' AND item_id = ?");
                $notifStmt->execute([$itemId]);
                
                $insertStmt = $pdo->prepare("INSERT INTO notifications (type, title, message, item_id) VALUES ('low_stock', ?, ?, ?)");
                $insertStmt->execute([
                    'Low Stock Alert',
                    $item['name'] . ' is running low (' . $item['stock_quantity'] . ' units remaining)',
                    $itemId
                ]);
            } elseif ($item && $item['stock_quantity'] == 0) {
                $notifStmt = $pdo->prepare("DELETE FROM notifications WHERE type = 'out_of_stock' AND item_id = ?");
                $notifStmt->execute([$itemId]);
                
                $insertStmt = $pdo->prepare("INSERT INTO notifications (type, title, message, item_id) VALUES ('out_of_stock', ?, ?, ?)");
                $insertStmt->execute([
                    'Out of Stock',
                    $item['name'] . ' is out of stock!',
                    $itemId
                ]);
            } else {
                $notifStmt = $pdo->prepare("DELETE FROM notifications WHERE type = 'low_stock' AND item_id = ?");
                $notifStmt->execute([$itemId]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Stock updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
