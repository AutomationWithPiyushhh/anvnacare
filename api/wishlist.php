<?php
// API - Wishlist Manager
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please login to manage your wishlist.',
        'redirect' => 'login.php'
    ]);
    exit;
}

$data = [];
$raw_input = file_get_contents('php://input');
if (!empty($raw_input)) {
    $decoded = json_decode($raw_input, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
$data = array_merge($_POST, $data);

$action = $data['action'] ?? '';
$itemId = (int)($data['item_id'] ?? 0);
$itemType = $data['item_type'] ?? ''; // 'medicine', 'product'

// CSRF protection
csrf_protect($data);

if (empty($action) || $itemId <= 0 || !in_array($itemType, ['medicine', 'product'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

try {
    if ($action === 'add') {
        // Verify item exists
        $tableName = ($itemType === 'medicine') ? 'medicines' : 'products';
        $stmt = $pdo->prepare("SELECT name FROM $tableName WHERE id = ?");
        $stmt->execute([$itemId]);
        $itemName = $stmt->fetchColumn();

        if (!$itemName) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
            exit;
        }

        // Insert into wishlist (unique constraint will catch duplicates, or check first)
        $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, item_type, item_id) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $itemType, $itemId]);
        
        echo json_encode([
            'success' => true,
            'message' => htmlspecialchars($itemName) . ' added to wishlist.'
        ]);
        exit;

    } elseif ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND item_type = ? AND item_id = ?");
        $stmt->execute([$_SESSION['user_id'], $itemType, $itemId]);

        echo json_encode([
            'success' => true,
            'message' => 'Item removed from wishlist.'
        ]);
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
