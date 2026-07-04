<?php
// API - Place Order
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to complete your order.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Parse Input
$data = [];
$raw_input = file_get_contents('php://input');
if (!empty($raw_input)) {
    $decoded = json_decode($raw_input, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
$data = array_merge($_POST, $data);

$addressId = (int)($data['address_id'] ?? 0);
$paymentMethod = trim($data['payment_method'] ?? 'Card');
$deliveryMethod = trim($data['delivery_method'] ?? 'Standard');
$couponCode = trim($data['coupon_code'] ?? '');

if ($addressId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please select a delivery address.']);
    exit;
}

try {
    // 1. Fetch Cart Items
    $cartStmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
    $cartStmt->execute([$userId]);
    $cartItems = $cartStmt->fetchAll();

    if (empty($cartItems)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
        exit;
    }

    // 2. Fetch Address details
    $addrStmt = $pdo->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
    $addrStmt->execute([$addressId, $userId]);
    if (!$addrStmt->fetchColumn()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid delivery address.']);
        exit;
    }

    // 3. Calculate Cart Totals
    $subtotal = 0;
    $itemsToProcess = [];

    foreach ($cartItems as $item) {
        $itemType = $item['item_type'];
        $itemId = $item['item_id'];
        $qty = $item['quantity'];

        $tableName = ($itemType === 'medicine') ? 'medicines' : (($itemType === 'product') ? 'products' : 'tests');
        $stmt = $pdo->prepare("SELECT id, name, discount_price, " . ($itemType !== 'test' ? 'stock' : '999 as stock') . " FROM $tableName WHERE id = ?");
        $stmt->execute([$itemId]);
        $details = $stmt->fetch();

        if (!$details) {
            throw new Exception("Item not found during checkout processing.");
        }

        if ($itemType !== 'test' && $details['stock'] < $qty) {
            throw new Exception("Item '" . $details['name'] . "' is out of stock. Max: " . $details['stock']);
        }

        $price = (float)$details['discount_price'];
        $subtotal += $price * $qty;

        $itemsToProcess[] = [
            'item_type' => $itemType,
            'item_id' => $itemId,
            'qty' => $qty,
            'price' => $price,
            'is_physical' => ($itemType !== 'test')
        ];
    }

    // 4. Calculate Discount via Coupon
    $discount = 0.00;
    if (!empty($couponCode)) {
        $couponStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND expiry_date >= CURRENT_DATE()");
        $couponStmt->execute([$couponCode]);
        $coupon = $couponStmt->fetch();

        if ($coupon) {
            if ($subtotal >= (float)$coupon['min_cart_value']) {
                if ($coupon['discount_type'] === 'percentage') {
                    $discount = $subtotal * ((float)$coupon['discount_value'] / 100);
                } else {
                    $discount = (float)$coupon['discount_value'];
                }
                // Cap discount at subtotal
                $discount = min($discount, $subtotal);
            }
        }
    }

    $netAmount = $subtotal - $discount;

    // 5. Place order inside transaction
    $pdo->beginTransaction();

    $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    $orderStatus = 'Placed';
    $paymentStatus = ($paymentMethod === 'Card') ? 'Paid' : 'Pending';

    // Insert Order
    $insertOrder = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, discount_amount, net_amount, address_id, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertOrder->execute([
        $userId,
        $orderNumber,
        $subtotal,
        $discount,
        $netAmount,
        $addressId,
        $paymentMethod,
        $paymentStatus,
        $orderStatus
    ]);

    $orderId = $pdo->lastInsertId();

    // Insert Order Items and Update Stocks
    $insertItem = $pdo->prepare("INSERT INTO order_items (order_id, item_type, item_id, price, quantity) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($itemsToProcess as $procItem) {
        $insertItem->execute([
            $orderId,
            $procItem['item_type'],
            $procItem['item_id'],
            $procItem['price'],
            $procItem['qty']
        ]);

        if ($procItem['is_physical']) {
            $tableName = ($procItem['item_type'] === 'medicine') ? 'medicines' : 'products';
            $updateStock = $pdo->prepare("UPDATE $tableName SET stock = stock - ? WHERE id = ?");
            $updateStock->execute([$procItem['qty'], $procItem['item_id']]);
        }
    }

    // 6. Clear User Cart
    $clearCart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearCart->execute([$userId]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'net_amount' => $netAmount
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
