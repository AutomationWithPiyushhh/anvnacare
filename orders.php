<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// --- INVOICE DOWNLOAD INTERCEPTOR ---
if (isset($_GET['download_invoice'])) {
    $orderId = (int)$_GET['download_invoice'];
    $userId = $_SESSION['user_id'];

    try {
        // Fetch Order
        $stmt = $pdo->prepare("SELECT o.*, a.name as receiver_name, a.phone, a.address_line1, a.address_line2, a.city, a.state, a.pincode 
            FROM orders o
            LEFT JOIN addresses a ON o.address_id = a.id
            WHERE o.id = ? AND o.user_id = ?");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch();

        if (!$order) {
            die("Order not found or access denied.");
        }

        // Fetch Order Items
        $itemsStmt = $pdo->prepare("SELECT oi.*, 
            COALESCE(m.name, p.name, t.name) as item_name
            FROM order_items oi
            LEFT JOIN medicines m ON oi.item_id = m.id AND oi.item_type = 'medicine'
            LEFT JOIN products p ON oi.item_id = p.id AND oi.item_type = 'product'
            LEFT JOIN tests t ON oi.item_id = t.id AND oi.item_type = 'test'
            WHERE oi.order_id = ?");
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll();

        // Generate download file
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="invoice-' . $order['order_number'] . '.txt"');

        echo "========================================================\n";
        echo "                     ANVNA CARE                         \n";
        echo "            HEALTHCARE COMPANION PLATFORM               \n";
        echo "               https://anvnacare.com                    \n";
        echo "========================================================\n\n";
        echo "INVOICE DETAILS\n";
        echo "--------------------------------------------------------\n";
        echo "Order Number   : " . $order['order_number'] . "\n";
        echo "Order Status   : " . $order['order_status'] . "\n";
        echo "Date & Time    : " . $order['created_at'] . "\n";
        echo "Payment Mode   : " . $order['payment_method'] . "\n";
        echo "Payment Status : " . $order['payment_status'] . "\n\n";

        echo "SHIPPING ADDRESS\n";
        echo "--------------------------------------------------------\n";
        echo "Receiver       : " . $order['receiver_name'] . "\n";
        echo "Phone          : " . $order['phone'] . "\n";
        echo "Address        : " . $order['address_line1'] . ", " . $order['address_line2'] . "\n";
        echo "City/State/Pin : " . $order['city'] . ", " . $order['state'] . " - " . $order['pincode'] . "\n\n";

        echo "BILLING ITEMS\n";
        echo "--------------------------------------------------------\n";
        printf("%-30s %-10s %-10s %-10s\n", "Item Name", "Type", "Qty", "Price");
        echo "--------------------------------------------------------\n";
        foreach ($items as $item) {
            printf("%-30s %-10s %-10s ₹%-10.2f\n", 
                substr($item['item_name'], 0, 28), 
                ucfirst($item['item_type']), 
                $item['quantity'], 
                $item['price']
            );
        }
        echo "--------------------------------------------------------\n";
        printf("%-30s %-10s %-10s ₹%-10.2f\n", "Subtotal Items Total", "", "", $order['total_amount']);
        printf("%-30s %-10s %-10s -₹%-10.2f\n", "Coupon Discount Applied", "", "", $order['discount_amount']);
        echo "--------------------------------------------------------\n";
        printf("%-30s %-10s %-10s ₹%-10.2f\n", "NET AMOUNT PAID", "", "", $order['net_amount']);
        echo "========================================================\n";
        echo "         Thank you for choosing ANVNA Care!             \n";
        echo "========================================================\n";
        exit;

    } catch (PDOException $e) {
        die("Error generating invoice: " . $e->getMessage());
    }
}

require_once 'includes/header.php';
$userId = $_SESSION['user_id'];

try {
    // Fetch all user orders
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Breadcrumb Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-success" id="ordersTitle" data-testid="orders-title">My Orders History</h2>
        <p class="text-muted">Track order status, delivery progress, and download invoices</p>
    </div>

    <!-- Orders Accordion List -->
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <?php if (empty($orders)): ?>
                <div class="text-center py-5 glass-card border-0" id="emptyOrders" data-testid="empty-orders-box">
                    <i class="bi bi-box-seam text-muted" style="font-size: 3.5rem;"></i>
                    <p class="text-muted mt-2">You have not placed any orders yet.</p>
                    <a href="medicines.php" class="btn btn-primary-custom btn-sm" id="shopMedsBtn" data-testid="shop-meds-btn">Shop Medicines</a>
                </div>
            <?php else: ?>
                <div class="accordion" id="ordersAccordion" data-testid="orders-accordion">
                    <?php foreach ($orders as $index => $order): ?>
                        <?php 
                        // Fetch Items inside each order
                        $itemsStmt = $pdo->prepare("SELECT oi.*, 
                            COALESCE(m.name, p.name, t.name) as item_name
                            FROM order_items oi
                            LEFT JOIN medicines m ON oi.item_id = m.id AND oi.item_type = 'medicine'
                            LEFT JOIN products p ON oi.item_id = p.id AND oi.item_type = 'product'
                            LEFT JOIN tests t ON oi.item_id = t.id AND oi.item_type = 'test'
                            WHERE oi.order_id = ?");
                        $itemsStmt->execute([$order['id']]);
                        $orderItems = $itemsStmt->fetchAll();

                        $collapseId = "collapse-" . $order['id'];
                        $headerId = "header-" . $order['id'];
                        ?>
                        
                        <!-- Order Card Item (Accordion style) -->
                        <div class="accordion-item glass-card border-0 mb-3" data-testid="order-accordion-item" id="order-card-<?= $order['id'] ?>">
                            <h2 class="accordion-header" id="<?= $headerId ?>">
                                <button class="accordion-button collapsed bg-transparent py-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>" data-testid="order-accordion-btn-<?= $order['id'] ?>">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center w-100 pe-3 gap-3">
                                        <div>
                                            <span class="text-muted small d-block">ORDER NUMBER</span>
                                            <strong class="text-success" data-testid="order-number"><?= $order['order_number'] ?></strong>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">DATE PLACED</span>
                                            <strong class="text-dark"><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></strong>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">NET PAYABLE</span>
                                            <strong class="text-dark">₹<?= number_format($order['net_amount'], 2) ?></strong>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">ORDER STATUS</span>
                                            <?php 
                                            $badgeClass = 'bg-secondary';
                                            if ($order['order_status'] === 'Placed') $badgeClass = 'bg-info text-dark';
                                            if ($order['order_status'] === 'Processing') $badgeClass = 'bg-warning text-dark';
                                            if ($order['order_status'] === 'Shipped') $badgeClass = 'bg-primary';
                                            if ($order['order_status'] === 'Delivered') $badgeClass = 'bg-success';
                                            if ($order['order_status'] === 'Cancelled') $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>" data-testid="order-status-badge"><?= $order['order_status'] ?></span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="<?= $collapseId ?>" class="accordion-collapse collapse" aria-labelledby="<?= $headerId ?>" data-bs-parent="#ordersAccordion" data-testid="order-collapse-panel">
                                <div class="accordion-body border-top p-4 bg-light bg-opacity-25">
                                    <h6 class="fw-bold mb-3 text-success">Purchased Items Summary</h6>
                                    
                                    <!-- Items table -->
                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm align-middle bg-white rounded shadow-sm">
                                            <thead>
                                                <tr class="text-muted small">
                                                    <th class="ps-3">Item Name</th>
                                                    <th>Type</th>
                                                    <th class="text-center">Quantity</th>
                                                    <th class="text-end pe-3">Unit Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orderItems as $item): ?>
                                                    <tr>
                                                        <td class="ps-3 fw-semibold text-dark"><?= htmlspecialchars($item['item_name']) ?></td>
                                                        <td class="text-capitalize"><?= $item['item_type'] ?></td>
                                                        <td class="text-center"><?= $item['quantity'] ?></td>
                                                        <td class="text-end pe-3 fw-bold text-success">₹<?= number_format($item['price'], 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row align-items-center">
                                        <!-- Shipping / Coupon details -->
                                        <div class="col-md-7 mb-3 mb-md-0 small text-muted">
                                            <div>Payment Method: <strong><?= $order['payment_method'] ?></strong></div>
                                            <div>Payment Status: <strong class="text-success"><?= $order['payment_status'] ?></strong></div>
                                            <div>Subtotal Amount: <strong>₹<?= number_format($order['total_amount'], 2) ?></strong></div>
                                            <div>Coupon Discount: <strong class="text-danger">-₹<?= number_format($order['discount_amount'], 2) ?></strong></div>
                                        </div>

                                        <!-- Invoice Button -->
                                        <div class="col-md-5 text-md-end">
                                            <a href="orders.php?download_invoice=<?= $order['id'] ?>" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-2" id="downloadInvoiceBtn-<?= $order['id'] ?>" data-testid="download-invoice-btn">
                                                <i class="bi bi-file-earmark-arrow-down-fill"></i> Download Invoice Bill (TXT)
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
