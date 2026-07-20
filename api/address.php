<?php
// API - Manage Saved Addresses
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to manage addresses.']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Parse Inputs
$data = [];
$raw_input = file_get_contents('php://input');
if (!empty($raw_input)) {
    $decoded = json_decode($raw_input, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
$data = array_merge($_POST, $data);

try {
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt->execute([$userId]);
        $addresses = $stmt->fetchAll();

        echo json_encode(['success' => true, 'addresses' => $addresses]);
        exit;
    } 
    
    if ($method === 'POST') {
        // CSRF protection
        csrf_protect($data);

        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $line1 = trim($data['address_line1'] ?? '');
        $line2 = trim($data['address_line2'] ?? '');
        $city = trim($data['city'] ?? '');
        $state = trim($data['state'] ?? '');
        $pincode = trim($data['pincode'] ?? '');
        $isDefault = isset($data['is_default']) ? 1 : 0;

        // Validations
        if (empty($name) || empty($phone) || empty($line1) || empty($city) || empty($state) || empty($pincode)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'All address fields (except Line 2) are required.']);
            exit;
        }

        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Phone must be a 10-digit number.']);
            exit;
        }

        if (!preg_match('/^[0-9]{6}$/', $pincode)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Pincode must be a 6-digit number.']);
            exit;
        }

        // If this is set as default, unset other defaults first
        if ($isDefault === 1) {
            $update = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            $update->execute([$userId]);
        }

        $stmt = $pdo->prepare("INSERT INTO addresses (user_id, name, phone, address_line1, address_line2, city, state, pincode, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $name, $phone, $line1, $line2, $city, $state, $pincode, $isDefault]);
        
        $newId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Address saved successfully.',
            'address' => [
                'id' => $newId,
                'name' => $name,
                'phone' => $phone,
                'address_line1' => $line1,
                'address_line2' => $line2,
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode,
                'is_default' => $isDefault
            ]
        ]);
        exit;
    }

    // DELETE — Remove a saved address
    if ($method === 'DELETE' || ($method === 'POST' && ($data['_method'] ?? '') === 'DELETE')) {
        // CSRF protection
        csrf_protect($data);

        $addressId = (int)($data['address_id'] ?? 0);
        if ($addressId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid address ID.']);
            exit;
        }

        // Verify ownership before delete
        $checkStmt = $pdo->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
        $checkStmt->execute([$addressId, $userId]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Address not found.']);
            exit;
        }

        $delStmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
        $delStmt->execute([$addressId, $userId]);

        echo json_encode(['success' => true, 'message' => 'Address deleted successfully.']);
        exit;
    }

    // POST action=set_default — Mark an address as default
    if ($method === 'POST' && ($data['action'] ?? '') === 'set_default') {
        // CSRF protection
        csrf_protect($data);

        $addressId = (int)($data['address_id'] ?? 0);
        if ($addressId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid address ID.']);
            exit;
        }

        // Verify ownership
        $checkStmt = $pdo->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
        $checkStmt->execute([$addressId, $userId]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Address not found.']);
            exit;
        }

        // Unset all defaults for this user, then set selected
        $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$addressId, $userId]);

        echo json_encode(['success' => true, 'message' => 'Default address updated.']);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
