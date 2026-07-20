<?php
// API - Login Endpoint
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Support both JSON input and standard Form URL-encoded post
$data = [];
$raw_input = file_get_contents('php://input');
if (!empty($raw_input)) {
    $decoded = json_decode($raw_input, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
$data = array_merge($_POST, $data);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$remember = isset($data['remember']);

// --- Rate Limiting: max 5 failed attempts per 10 minutes per IP ---
$ipKey = 'login_attempts_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$attempts   = $_SESSION[$ipKey]['count'] ?? 0;
$lastAttempt = $_SESSION[$ipKey]['last'] ?? 0;

// Reset counter if 10 minutes have passed since last failed attempt
if ((time() - $lastAttempt) > 600) {
    $attempts = 0;
    unset($_SESSION[$ipKey]);
}

if ($attempts >= 5) {
    $retryAfter = 600 - (time() - $lastAttempt);
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Too many failed login attempts. Please wait ' . ceil($retryAfter / 60) . ' minute(s) and try again.'
    ]);
    exit;
}
// --- End Rate Limiting ---

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and Password are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Login success — clear any rate limit counter
        unset($_SESSION[$ipKey]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Merge guest cart into DB cart on login
        if (!empty($_SESSION['guest_cart'])) {
            foreach ($_SESSION['guest_cart'] as $key => $qty) {
                [$itemType, $itemId] = explode('_', $key, 2);
                $itemId = (int)$itemId;
                $qty = (int)$qty;
                if ($qty > 0 && $itemId > 0) {
                    $existStmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND item_type = ? AND item_id = ?");
                    $existStmt->execute([$user['id'], $itemType, $itemId]);
                    $existing = $existStmt->fetch();
                    if ($existing) {
                        $updStmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
                        $updStmt->execute([$qty, $existing['id']]);
                    } else {
                        $insStmt = $pdo->prepare("INSERT INTO cart (user_id, item_type, item_id, quantity) VALUES (?, ?, ?, ?)");
                        $insStmt->execute([$user['id'], $itemType, $itemId, $qty]);
                    }
                }
            }
            unset($_SESSION['guest_cart']);
        }

        // Handle "Remember Me" Cookie (30 days)
        if ($remember) {
            setcookie('remember_user', $user['email'], time() + (30 * 24 * 60 * 60), "/");
        } else {
            // Delete cookie if unchecked
            setcookie('remember_user', '', time() - 3600, "/");
        }

        echo json_encode([
            'success' => true,
            'message' => 'Login successful.',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        // Increment failed attempt counter
        $_SESSION[$ipKey] = [
            'count' => $attempts + 1,
            'last'  => time()
        ];
        http_response_code(401);
        $remaining = 5 - ($attempts + 1);
        $msg = 'Invalid email or password.';
        if ($remaining > 0) {
            $msg .= ' ' . $remaining . ' attempt(s) remaining before lockout.';
        }
        echo json_encode(['success' => false, 'message' => $msg]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
