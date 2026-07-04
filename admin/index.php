<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Restrict access to Admins only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

try {
    // 1. Gather Metrics
    $userCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $medCount = $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();
    $docCount = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
    $orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $revenue = $pdo->query("SELECT SUM(net_amount) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 0.00;
    $appCount = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

    // 2. Fetch Users
    $users = $pdo->query("SELECT id, name, email, phone, created_at FROM users WHERE role = 'user' ORDER BY id DESC LIMIT 10")->fetchAll();

    // 3. Fetch Medicines
    $medicines = $pdo->query("SELECT id, name, manufacturer, mrp, discount_price, stock FROM medicines ORDER BY id ASC LIMIT 10")->fetchAll();

    // 4. Fetch Doctors
    $doctors = $pdo->query("SELECT id, name, specialization, experience, fee FROM doctors ORDER BY id ASC LIMIT 10")->fetchAll();

    // 5. Fetch Appointments
    $appointments = $pdo->query("
        SELECT a.id, a.appointment_date, a.appointment_time, a.status, u.name as user_name, d.name as doctor_name 
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN doctors d ON a.doctor_id = d.id 
        ORDER BY a.id DESC LIMIT 10
    ")->fetchAll();

    // 6. Fetch Orders
    $orders = $pdo->query("
        SELECT o.id, o.order_number, o.net_amount, o.order_status, o.created_at, u.name as user_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.id DESC LIMIT 10
    ")->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle Order Status Update Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        header("Location: index.php?status_updated=1");
        exit;
    } catch (PDOException $e) {
        die("Error updating status: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANVNA Care - Administrator Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #0a6c42;
            --dark-slate: #0f172a;
            --light-bg: #f8fafc;
            --border-radius: 12px;
        }
        body {
            background-color: var(--light-bg);
            font-family: system-ui, -apple-system, sans-serif;
        }
        .admin-sidebar {
            background-color: var(--dark-slate);
            min-height: 100vh;
            color: #f8fafc;
        }
        .nav-link-admin {
            color: #94a3b8;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.2s ease;
        }
        .nav-link-admin:hover, .nav-link-admin.active {
            background-color: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .admin-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }
        .metric-icon {
            font-size: 2.2rem;
            opacity: 0.85;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar px-3 py-4" id="adminSidebar" data-testid="admin-sidebar">
            <div class="d-flex align-items-center gap-2 mb-4 px-2">
                <i class="bi bi-shield-lock-fill text-success fs-3"></i>
                <div>
                    <h5 class="fw-bold mb-0">ANVNA Admin</h5>
                    <span class="small text-muted">Portal Panel</span>
                </div>
            </div>
            
            <hr class="text-secondary">

            <div class="nav flex-column" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link-admin active" id="v-pills-dash-tab" data-bs-toggle="pill" href="#v-pills-dash" role="tab" aria-controls="v-pills-dash" aria-selected="true" data-testid="tab-dash">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link-admin" id="v-pills-users-tab" data-bs-toggle="pill" href="#v-pills-users" role="tab" aria-controls="v-pills-users" aria-selected="false" data-testid="tab-users">
                    <i class="bi bi-people"></i> Manage Users
                </a>
                <a class="nav-link-admin" id="v-pills-meds-tab" data-bs-toggle="pill" href="#v-pills-meds" role="tab" aria-controls="v-pills-meds" aria-selected="false" data-testid="tab-meds">
                    <i class="bi bi-capsule"></i> Medicines Catalog
                </a>
                <a class="nav-link-admin" id="v-pills-docs-tab" data-bs-toggle="pill" href="#v-pills-docs" role="tab" aria-controls="v-pills-docs" aria-selected="false" data-testid="tab-docs">
                    <i class="bi bi-person-heart"></i> Manage Doctors
                </a>
                <a class="nav-link-admin" id="v-pills-apps-tab" data-bs-toggle="pill" href="#v-pills-apps" role="tab" aria-controls="v-pills-apps" aria-selected="false" data-testid="tab-apps">
                    <i class="bi bi-calendar-check"></i> Appointments
                </a>
                <a class="nav-link-admin" id="v-pills-orders-tab" data-bs-toggle="pill" href="#v-pills-orders" role="tab" aria-controls="v-pills-orders" aria-selected="false" data-testid="tab-orders">
                    <i class="bi bi-box-seam"></i> Patient Orders
                </a>
            </div>

            <hr class="text-secondary mt-5">
            <div class="px-2">
                <a href="../index.php" class="btn btn-outline-light btn-sm w-100 mb-2" id="backToHomeBtn" data-testid="admin-home-btn">Exit Portal</a>
                <a href="../logout.php" class="btn btn-danger btn-sm w-100" id="logoutBtn" data-testid="admin-logout-btn">Log Out</a>
            </div>
        </nav>

        <!-- Main Panel Body -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4" id="adminMain" data-testid="admin-main">
            
            <!-- Success Status alert -->
            <?php if (isset($_GET['status_updated'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="statusAlert" data-testid="status-alert">
                    <i class="bi bi-check-circle-fill me-1"></i> Order status updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="tab-content" id="v-pills-tabContent">
                
                <!-- 1. DASHBOARD OVERVIEW PANEL -->
                <div class="tab-pane fade show active" id="v-pills-dash" role="tabpanel" aria-labelledby="v-pills-dash-tab">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
                        <h1 class="h2 fw-bold text-dark" data-testid="dash-header">Management Metrics</h1>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4 col-lg-3">
                            <div class="card admin-card p-3 bg-white d-flex flex-row align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold">TOTAL PATIENTS</span>
                                    <h3 class="fw-bold text-dark mt-1" data-testid="metric-users"><?= $userCount ?></h3>
                                </div>
                                <span class="text-success metric-icon"><i class="bi bi-people-fill"></i></span>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <div class="card admin-card p-3 bg-white d-flex flex-row align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold">ACTIVE CONSULTANTS</span>
                                    <h3 class="fw-bold text-dark mt-1" data-testid="metric-doctors"><?= $docCount ?></h3>
                                </div>
                                <span class="text-primary metric-icon"><i class="bi bi-person-fill-add"></i></span>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <div class="card admin-card p-3 bg-white d-flex flex-row align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold">APPOINTMENTS</span>
                                    <h3 class="fw-bold text-dark mt-1" data-testid="metric-appointments"><?= $appCount ?></h3>
                                </div>
                                <span class="text-info metric-icon"><i class="bi bi-calendar-event-fill"></i></span>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <div class="card admin-card p-3 bg-white d-flex flex-row align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold">TOTAL REVENUE</span>
                                    <h3 class="fw-bold text-dark mt-1" data-testid="metric-revenue">₹<?= number_format($revenue, 2) ?></h3>
                                </div>
                                <span class="text-success metric-icon"><i class="bi bi-currency-rupee"></i></span>
                            </div>
                        </div>
                    </div>

                    <!-- Welcome Box -->
                    <div class="card bg-success text-white p-4 border-0 mb-4">
                        <h4 class="fw-bold">Hello, Administrator!</h4>
                        <p class="mb-0">This portal allows monitoring and executing status updates for orders and appointments during test automation scenarios.</p>
                    </div>
                </div>

                <!-- 2. MANAGE USERS PANEL -->
                <div class="tab-pane fade" id="v-pills-users" role="tabpanel" aria-labelledby="v-pills-users-tab" data-testid="panel-users">
                    <h3 class="fw-bold mb-4">Registered Patient Directory</h3>
                    <div class="card admin-card p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" data-testid="admin-users-table">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>ID</th>
                                        <th>Patient Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Joined Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr data-testid="user-row">
                                            <td><?= $u['id'] ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td><?= htmlspecialchars($u['phone']) ?></td>
                                            <td><?= $u['created_at'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 3. MEDICINES CATALOG PANEL -->
                <div class="tab-pane fade" id="v-pills-meds" role="tabpanel" aria-labelledby="v-pills-meds-tab" data-testid="panel-meds">
                    <h3 class="fw-bold mb-4">Pharmacy Medicines List</h3>
                    <div class="card admin-card p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" data-testid="admin-meds-table">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>ID</th>
                                        <th>Medicine Name</th>
                                        <th>Manufacturer</th>
                                        <th>MRP</th>
                                        <th>Discount Price</th>
                                        <th>Stock Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($medicines as $m): ?>
                                        <tr data-testid="medicine-row">
                                            <td><?= $m['id'] ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($m['name']) ?></td>
                                            <td><?= htmlspecialchars($m['manufacturer']) ?></td>
                                            <td>₹<?= number_format($m['mrp'], 2) ?></td>
                                            <td class="text-success fw-bold">₹<?= number_format($m['discount_price'], 2) ?></td>
                                            <td class="fw-bold"><?= $m['stock'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4. MANAGE DOCTORS PANEL -->
                <div class="tab-pane fade" id="v-pills-docs" role="tabpanel" aria-labelledby="v-pills-docs-tab" data-testid="panel-docs">
                    <h3 class="fw-bold mb-4">Doctor Specialists Panel</h3>
                    <div class="card admin-card p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" data-testid="admin-docs-table">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>ID</th>
                                        <th>Doctor Name</th>
                                        <th>Specialization</th>
                                        <th>Experience</th>
                                        <th>Consultation Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($doctors as $d): ?>
                                        <tr data-testid="doctor-row">
                                            <td><?= $d['id'] ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($d['name']) ?></td>
                                            <td><span class="badge bg-success-subtle text-success border"><?= $d['specialization'] ?></span></td>
                                            <td><?= $d['experience'] ?> Years</td>
                                            <td class="fw-bold">₹<?= number_format($d['fee'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 5. APPOINTMENTS PANEL -->
                <div class="tab-pane fade" id="v-pills-apps" role="tabpanel" aria-labelledby="v-pills-apps-tab" data-testid="panel-apps">
                    <h3 class="fw-bold mb-4">Scheduled Consultations List</h3>
                    <div class="card admin-card p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" data-testid="admin-apps-table">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>ID</th>
                                        <th>Patient Name</th>
                                        <th>Doctor Name</th>
                                        <th>Date</th>
                                        <th>Time Slot</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $a): ?>
                                        <tr data-testid="appointment-row">
                                            <td><?= $a['id'] ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($a['user_name']) ?></td>
                                            <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                                            <td><?= $a['appointment_date'] ?></td>
                                            <td><?= $a['appointment_time'] ?></td>
                                            <td>
                                                <?php 
                                                $badgeClass = 'bg-secondary';
                                                if ($a['status'] === 'Upcoming') $badgeClass = 'bg-info text-dark';
                                                if ($a['status'] === 'Completed') $badgeClass = 'bg-success';
                                                if ($a['status'] === 'Cancelled') $badgeClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= $a['status'] ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 6. PATIENT ORDERS PANEL -->
                <div class="tab-pane fade" id="v-pills-orders" role="tabpanel" aria-labelledby="v-pills-orders-tab" data-testid="panel-orders">
                    <h3 class="fw-bold mb-4">Patient Orders Management</h3>
                    <div class="card admin-card p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="adminOrdersTable" data-testid="admin-orders-table">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Order #</th>
                                        <th>Patient Name</th>
                                        <th>Date</th>
                                        <th>Net Amount</th>
                                        <th>Current Status</th>
                                        <th class="text-end">Update Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr data-testid="order-row" id="admin-order-row-<?= $order['id'] ?>">
                                            <td class="fw-bold text-success"><?= $order['order_number'] ?></td>
                                            <td><?= htmlspecialchars($order['user_name']) ?></td>
                                            <td class="small"><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
                                            <td class="fw-bold">₹<?= number_format($order['net_amount'], 2) ?></td>
                                            <td>
                                                <?php 
                                                $badgeClass = 'bg-secondary';
                                                if ($order['order_status'] === 'Placed') $badgeClass = 'bg-info text-dark';
                                                if ($order['order_status'] === 'Processing') $badgeClass = 'bg-warning text-dark';
                                                if ($order['order_status'] === 'Shipped') $badgeClass = 'bg-primary';
                                                if ($order['order_status'] === 'Delivered') $badgeClass = 'bg-success';
                                                if ($order['order_status'] === 'Cancelled') $badgeClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?= $badgeClass ?>" data-testid="order-status-<?= $order['id'] ?>"><?= $order['order_status'] ?></span>
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" action="index.php" class="d-inline-flex gap-2 align-items-center">
                                                    <input type="hidden" name="action" value="update_order_status">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <select class="form-select form-select-sm" name="status" data-testid="update-status-dropdown-<?= $order['id'] ?>" style="width: 130px;">
                                                        <option value="Placed" <?= $order['order_status'] === 'Placed' ? 'selected' : '' ?>>Placed</option>
                                                        <option value="Processing" <?= $order['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                                        <option value="Shipped" <?= $order['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                                        <option value="Delivered" <?= $order['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                        <option value="Cancelled" <?= $order['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-success btn-sm" id="updateStatusBtn-<?= $order['id'] ?>" data-testid="update-status-btn-<?= $order['id'] ?>">Update</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
