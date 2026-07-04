<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/header.php';
$userId = $_SESSION['user_id'];

try {
    // 1. Fetch Stats counts
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $dbCartCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wishlistCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([$userId]);
    $ordersCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND status = 'Upcoming'");
    $stmt->execute([$userId]);
    $appointmentsCount = $stmt->fetchColumn();

    // 2. Fetch Upcoming Appointments
    $appStmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time, d.name as doctor_name, d.specialization 
        FROM appointments a 
        JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.user_id = ? AND a.status = 'Upcoming' 
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 3
    ");
    $appStmt->execute([$userId]);
    $upcomingAppointments = $appStmt->fetchAll();

    // 3. Fetch Recent Orders
    $orderStmt = $pdo->prepare("
        SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC 
        LIMIT 3
    ");
    $orderStmt->execute([$userId]);
    $recentOrders = $orderStmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Welcome Banner -->
    <div class="card bg-success text-white p-4 border-0 mb-4 shadow-sm" id="welcomeBanner" data-testid="welcome-banner">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="fw-bold mb-1" id="userGreeting" data-testid="user-greeting">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
                <p class="mb-0 text-white-50">Manage your online medical appointments, prescriptions, and orders in one single place.</p>
            </div>
            <div class="d-none d-md-block fs-1">
                <i class="bi bi-heart-pulse"></i>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row g-4 mb-4">
        <!-- Cart Count -->
        <div class="col-6 col-lg-3">
            <a href="cart.php" class="text-decoration-none">
                <div class="card glass-card border-0 p-3 text-center" id="statCartCard" data-testid="stat-cart-card">
                    <span class="fs-1 text-success mb-2" id="statCartIcon"><i class="bi bi-cart3"></i></span>
                    <h5 class="fw-bold text-dark mb-1" id="statCartCount" data-testid="stat-cart-count"><?= $dbCartCount ?></h5>
                    <span class="text-muted small">Items in Cart</span>
                </div>
            </a>
        </div>

        <!-- Wishlist Count -->
        <div class="col-6 col-lg-3">
            <a href="wishlist.php" class="text-decoration-none">
                <div class="card glass-card border-0 p-3 text-center" id="statWishlistCard" data-testid="stat-wishlist-card">
                    <span class="fs-1 text-danger mb-2" id="statWishlistIcon"><i class="bi bi-heart"></i></span>
                    <h5 class="fw-bold text-dark mb-1" id="statWishlistCount" data-testid="stat-wishlist-count"><?= $wishlistCount ?></h5>
                    <span class="text-muted small">My Wishlist</span>
                </div>
            </a>
        </div>

        <!-- Total Orders -->
        <div class="col-6 col-lg-3">
            <a href="orders.php" class="text-decoration-none">
                <div class="card glass-card border-0 p-3 text-center" id="statOrdersCard" data-testid="stat-orders-card">
                    <span class="fs-1 text-primary mb-2" id="statOrdersIcon"><i class="bi bi-box-seam"></i></span>
                    <h5 class="fw-bold text-dark mb-1" id="statOrdersCount" data-testid="stat-orders-count"><?= $ordersCount ?></h5>
                    <span class="text-muted small">Total Orders</span>
                </div>
            </a>
        </div>

        <!-- Upcoming Appointments -->
        <div class="col-6 col-lg-3">
            <a href="appointments.php" class="text-decoration-none">
                <div class="card glass-card border-0 p-3 text-center" id="statAppCard" data-testid="stat-app-card">
                    <span class="fs-1 text-info mb-2" id="statAppIcon"><i class="bi bi-calendar-event"></i></span>
                    <h5 class="fw-bold text-dark mb-1" id="statAppCount" data-testid="stat-app-count"><?= $appointmentsCount ?></h5>
                    <span class="text-muted small">Active Bookings</span>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Upcoming Appointments Panel -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 glass-card h-100" id="appointmentsPanel" data-testid="appointments-panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0 text-success"><i class="bi bi-calendar-check me-2"></i> Upcoming Consultations</h4>
                    <a href="appointments.php" class="text-success small fw-semibold text-decoration-none" id="viewAllAppBtn" data-testid="view-all-app-btn">View All</a>
                </div>

                <?php if (empty($upcomingAppointments)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-plus text-muted fs-1 mb-2"></i>
                        <p class="text-muted">No upcoming consultations booked.</p>
                        <a href="doctors.php" class="btn btn-primary-custom btn-sm" id="bookConsultBtn" data-testid="book-consult-btn">Book Appointment</a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush" id="upcomingList" data-testid="upcoming-appointments-list">
                        <?php foreach ($upcomingAppointments as $app): ?>
                            <div class="list-group-item bg-transparent px-0 py-3 d-flex justify-content-between align-items-center" data-testid="appointment-row" id="app-row-<?= $app['id'] ?>">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1" data-testid="appointment-doctor"><?= htmlspecialchars($app['doctor_name']) ?></h6>
                                    <span class="badge bg-success-subtle text-success mb-2" data-testid="appointment-spec"><?= htmlspecialchars($app['specialization']) ?></span>
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i> <span data-testid="appointment-date"><?= $app['appointment_date'] ?></span> 
                                        <span class="mx-2">|</span> 
                                        <i class="bi bi-clock me-1"></i> <span data-testid="appointment-time"><?= $app['appointment_time'] ?></span>
                                    </div>
                                </div>
                                <button class="btn btn-outline-danger btn-sm" onclick="cancelAppointment(<?= $app['id'] ?>)" id="cancelAppBtn-<?= $app['id'] ?>" data-testid="cancel-appointment-btn">
                                    Cancel
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Orders Panel -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 glass-card h-100" id="ordersPanel" data-testid="orders-panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0 text-success"><i class="bi bi-box-seam me-2"></i> Recent Orders</h4>
                    <a href="orders.php" class="text-success small fw-semibold text-decoration-none" id="viewAllOrdersBtn" data-testid="view-all-orders-btn">View All</a>
                </div>

                <?php if (empty($recentOrders)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-capsule text-muted fs-1 mb-2"></i>
                        <p class="text-muted">No orders placed yet.</p>
                        <a href="medicines.php" class="btn btn-primary-custom btn-sm" id="shopMedsBtn" data-testid="shop-meds-btn">Order Medicines</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="recentOrdersTable" data-testid="recent-orders-table">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th class="text-end">Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr data-testid="order-row" id="order-row-<?= $order['id'] ?>">
                                        <td class="fw-bold text-success" data-testid="order-number"><?= $order['order_number'] ?></td>
                                        <td class="small" data-testid="order-date"><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <?php 
                                            $badgeClass = 'bg-secondary';
                                            if ($order['order_status'] === 'Placed') $badgeClass = 'bg-info text-dark';
                                            if ($order['order_status'] === 'Processing') $badgeClass = 'bg-warning text-dark';
                                            if ($order['order_status'] === 'Shipped') $badgeClass = 'bg-primary';
                                            if ($order['order_status'] === 'Delivered') $badgeClass = 'bg-success';
                                            if ($order['order_status'] === 'Cancelled') $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $order['order_status'] ?></span>
                                        </td>
                                        <td class="fw-bold">₹<?= number_format($order['net_amount'], 2) ?></td>
                                        <td class="text-end">
                                            <a href="orders.php?download_invoice=<?= $order['id'] ?>" class="btn btn-link btn-sm text-success p-0" id="downloadInvoiceBtn-<?= $order['id'] ?>" data-testid="download-invoice-btn" aria-label="Download Invoice PDF">
                                                <i class="bi bi-file-earmark-pdf fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Cancel Appointment Function (triggers Confirmation Alert)
function cancelAppointment(appId) {
    if (confirm("Are you sure you want to cancel this doctor consultation appointment?")) {
        showLoader();
        
        // POST Cancel request
        fetch('api/appointments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'cancel',
                appointment_id: appId
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                showToast(data.message, 'success');
                // Reload dashboard after a small delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(err => {
            hideLoader();
            console.error('Cancel appointment error:', err);
            showToast('Failed to cancel appointment. Connection error.', 'danger');
        });
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>
