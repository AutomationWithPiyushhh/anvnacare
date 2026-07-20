<?php
// API - Cart Manager
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];

// Parse Input
$data = [];
$raw_input = file_get_contents('php://input');
if (!empty($raw_input)) {
    $decoded = json_decode($raw_input, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}

// Merge standard parameters depending on request method
if ($method === 'POST') {
    $data = array_merge($_POST, $data);
} else if ($method === 'DELETE' || $method === 'PUT') {
    // If query parameters exist, merge them
    parse_str($_SERVER['QUERY_STRING'] ?? '', $queryData);
    $data = array_merge($queryData, $data);
}

$action = $data['action'] ?? '';
$itemId = (int)($data['item_id'] ?? 0);
$itemType = $data['item_type'] ?? ''; // 'medicine', 'product', 'test'
$quantity = (int)($data['quantity'] ?? 1);

// CSRF protection for state-changing requests
if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE' || $action !== '') {
    csrf_protect($data);
}


// Standard helper to calculate cart count
function getCartCount($pdo) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return (int)$stmt->fetchColumn();
    } else {
        $count = 0;
        if (isset($_SESSION['guest_cart'])) {
            foreach ($_SESSION['guest_cart'] as $qty) {
                $count += $qty;
            }
        }
        return $count;
    }
}

try {
    // Handling DELETE method
    if ($method === 'DELETE' || $action === 'remove') {
        if (empty($itemType) || $itemId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing item type or item ID.']);
            exit;
        }

        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND item_type = ? AND item_id = ?");
            $stmt->execute([$_SESSION['user_id'], $itemType, $itemId]);
        } else {
            $key = "{$itemType}_{$itemId}";
            unset($_SESSION['guest_cart'][$key]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart.',
            'cart_count' => getCartCount($pdo)
        ]);
        exit;
    }

    // Handling POST / PUT add or update
    if ($action === 'add') {
        if (empty($itemType) || $itemId <= 0 || $quantity <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
            exit;
        }

        // Verify item exists and check stock if applicable (tests don't have stock limits)
        $tableName = ($itemType === 'medicine') ? 'medicines' : (($itemType === 'product') ? 'products' : 'tests');
        $stmt = $pdo->prepare("SELECT name, " . ($itemType !== 'test' ? 'stock' : '999 as stock') . " FROM $tableName WHERE id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();

        if (!$item) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
            exit;
        }

        if ($itemType !== 'test' && $item['stock'] < $quantity) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Requested quantity is out of stock. Only ' . $item['stock'] . ' left.']);
            exit;
        }

        if (isset($_SESSION['user_id'])) {
            // DB-backed cart
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND item_type = ? AND item_id = ?");
            $stmt->execute([$_SESSION['user_id'], $itemType, $itemId]);
            $existing = $stmt->fetch();

            if ($existing) {
                $newQty = $existing['quantity'] + $quantity;
                if ($itemType !== 'test' && $item['stock'] < $newQty) {
                    echo json_encode(['success' => false, 'message' => 'Cannot add more. Exceeds available stock.']);
                    exit;
                }
                $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $update->execute([$newQty, $existing['id']]);
            } else {
                $insert = $pdo->prepare("INSERT INTO cart (user_id, item_type, item_id, quantity) VALUES (?, ?, ?, ?)");
                $insert->execute([$_SESSION['user_id'], $itemType, $itemId, $quantity]);
            }
        } else {
            // Guest Session-backed cart
            if (!isset($_SESSION['guest_cart'])) {
                $_SESSION['guest_cart'] = [];
            }
            $key = "{$itemType}_{$itemId}";
            $currentQty = $_SESSION['guest_cart'][$key] ?? 0;
            $newQty = $currentQty + $quantity;
            if ($itemType !== 'test' && $item['stock'] < $newQty) {
                echo json_encode(['success' => false, 'message' => 'Cannot add more. Exceeds available stock.']);
                exit;
            }
            $_SESSION['guest_cart'][$key] = $newQty;
        }

        echo json_encode([
            'success' => true,
            'message' => htmlspecialchars($item['name']) . ' added to cart.',
            'cart_count' => getCartCount($pdo)
        ]);
        exit;

    } elseif ($action === 'update') {
        if (empty($itemType) || $itemId <= 0 || $quantity <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
            exit;
        }

        $tableName = ($itemType === 'medicine') ? 'medicines' : (($itemType === 'product') ? 'products' : 'tests');
        $stmt = $pdo->prepare("SELECT " . ($itemType !== 'test' ? 'stock' : '999 as stock') . " FROM $tableName WHERE id = ?");
        $stmt->execute([$itemId]);
        $stock = $stmt->fetchColumn();

        if ($itemType !== 'test' && $stock < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Quantity exceeds available stock. Max: ' . $stock]);
            exit;
        }

        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND item_type = ? AND item_id = ?");
            $stmt->execute([$quantity, $_SESSION['user_id'], $itemType, $itemId]);
        } else {
            $key = "{$itemType}_{$itemId}";
            $_SESSION['guest_cart'][$key] = $quantity;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Cart updated.',
            'cart_count' => getCartCount($pdo)
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
