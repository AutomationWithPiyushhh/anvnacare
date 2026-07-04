<?php
// API - User Profile Manager
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
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
        // Fetch profile
        $stmt = $pdo->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }

        // Fetch addresses
        $addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $addrStmt->execute([$userId]);
        $addresses = $addrStmt->fetchAll();

        echo json_encode([
            'success' => true,
            'user' => $user,
            'addresses' => $addresses
        ]);
        exit;
    } 
    
    // For modifying profile (PUT/POST)
    if ($method === 'POST' || $method === 'PUT' || isset($data['_method'])) {
        $action = $data['action'] ?? 'update_profile';

        if ($action === 'update_profile') {
            $name = trim($data['name'] ?? '');
            $phone = trim($data['phone'] ?? '');

            if (empty($name) || strlen($name) < 3) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Name must be at least 3 characters.']);
                exit;
            }
            if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Phone must be a valid 10-digit number.']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $userId]);
            
            // Sync Session
            $_SESSION['user_name'] = $name;

            echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
            exit;

        } elseif ($action === 'change_password') {
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Current password and New password are required.']);
                exit;
            }
            if (strlen($newPassword) < 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
                exit;
            }

            // Get current password hash
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($currentPassword, $hash)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
                exit;
            }

            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$newHash, $userId]);

            echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
            exit;

        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid update action.']);
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
