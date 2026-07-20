<?php
// API - Coupon Validator
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$code = trim($_GET['code'] ?? '');
$cartValue = (float)($_GET['cart_value'] ?? 0.00);

if (empty($code)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Coupon code is required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND expiry_date >= CURRENT_DATE()");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if ($coupon) {
        if ($cartValue < (float)$coupon['min_cart_value']) {
            echo json_encode([
                'success' => false,
                'message' => 'Minimum cart value required to apply this coupon is ₹' . number_format($coupon['min_cart_value'], 2)
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Coupon code applied successfully.',
            'coupon' => [
                'code' => $coupon['code'],
                'discount_type' => $coupon['discount_type'],
                'discount_value' => (float)$coupon['discount_value']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
