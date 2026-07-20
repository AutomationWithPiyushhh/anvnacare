<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/csrf.php';

// Check "Remember Me" Cookie if user is not logged in
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    $email = $_COOKIE['remember_user'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
    }
}

// Calculate Cart Item Count
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total_qty FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = (int)$stmt->fetchColumn();
} else if (isset($_SESSION['guest_cart'])) {
    foreach ($_SESSION['guest_cart'] as $qty) {
        $cartCount += $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANVNA Care - Premium Healthcare Platform</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token()) ?>">
</head>
<body>

<!-- Global Skeleton Loader (Shown during page load simulations if triggered) -->
<div id="globalLoader" class="d-none" data-testid="global-loader">
    <div class="text-center">
        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-secondary font-weight-bold">Connecting to ANVNA Care...</p>
    </div>
</div>

<!-- Header Navigation Bar -->
<header class="fixed-top glass-nav" id="headerNav" data-testid="header-nav">
    <nav class="navbar navbar-expand-lg navbar-light py-2">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center" href="index.php" id="brandLogoLink" data-testid="brand-logo-link">
                <img src="assets/images/logo.svg" alt="ANVNA Care Logo" id="brandLogoImg" data-testid="brand-logo-img">
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation" id="navbarToggleButton" data-testid="navbar-toggle-btn">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links & Search -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Nav Links -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-medium" href="medicines.php" id="navPharmacy" data-testid="nav-pharmacy">Pharmacy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-medium" href="doctors.php" id="navDoctors" data-testid="nav-doctors">Consult Doctors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-medium" href="lab-tests.php" id="navDiagnostics" data-testid="nav-diagnostics">Lab Tests</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-medium" href="health-store.php" id="navStore" data-testid="nav-store">Health Store</a>
                    </li>
                </ul>

                <!-- Global Search Input -->
                <div class="d-flex align-items-center me-3 autosuggest-container" style="min-width: 280px; max-width: 380px; width: 100%;">
                    <div class="input-group">
                        <input class="form-control" type="search" placeholder="Search medicines, doctors..." aria-label="Search" id="globalSearchInput" name="globalQuery" data-testid="global-search-input" autocomplete="off" style="border-radius: var(--border-radius-sm) 0 0 var(--border-radius-sm); color: #0f172a; font-weight: 500;">
                        <button class="btn btn-outline-success" type="button" id="globalSearchButton" data-testid="global-search-btn" style="border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <!-- Auto-suggestion Box (hidden by default) -->
                    <div id="globalSearchSuggestions" class="autosuggest-list d-none" data-testid="search-suggestions-box"></div>
                </div>

                <!-- Right Side Actions (Cart & Auth) -->
                <div class="d-flex align-items-center gap-3">
                    <!-- Cart -->
                    <a class="btn position-relative px-2 py-1" href="cart.php" id="navCart" data-testid="nav-cart" aria-label="Shopping Cart">
                        <i class="bi bi-cart3 fs-4 text-success"></i>
                        <span class="cart-badge" id="navCartCount" data-testid="nav-cart-count"><?= $cartCount ?></span>
                    </a>

                    <!-- User Account / Login Buttons -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown dropdown-hover">
                            <button class="btn btn-outline-success dropdown-toggle d-flex align-items-center gap-2 fw-medium" type="button" id="userMenuDropdown" name="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-testid="user-menu-dropdown" style="border-radius: var(--border-radius-sm);">
                                <i class="bi bi-person-circle fs-5"></i> Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 mt-2" aria-labelledby="userMenuDropdown" data-testid="user-dropdown-list">
                                <li><a class="dropdown-item py-2" href="dashboard.php" id="dropdownDashboard" data-testid="dropdown-dashboard"><i class="bi bi-speedometer2 me-2 text-success"></i> Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="profile.php" id="dropdownProfile" data-testid="dropdown-profile"><i class="bi bi-person me-2 text-success"></i> My Profile</a></li>
                                <li><a class="dropdown-item py-2" href="orders.php" id="dropdownOrders" data-testid="dropdown-orders"><i class="bi bi-box-seam me-2 text-success"></i> My Orders</a></li>
                                <li><a class="dropdown-item py-2" href="appointments.php" id="dropdownAppointments" data-testid="dropdown-appointments"><i class="bi bi-calendar-event me-2 text-success"></i> Appointments</a></li>
                                <li><a class="dropdown-item py-2" href="wishlist.php" id="dropdownWishlist" data-testid="dropdown-wishlist"><i class="bi bi-heart me-2 text-success"></i> Wishlist</a></li>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item py-2 fw-bold text-primary" href="admin/index.php" id="dropdownAdminPanel" data-testid="dropdown-admin-panel"><i class="bi bi-shield-lock-fill me-2"></i> Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2 text-danger" href="logout.php" id="dropdownLogout" data-testid="dropdown-logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a class="btn btn-outline-primary-custom d-flex align-items-center gap-2" href="login.php" id="navLogin" data-testid="nav-login-btn">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                        <a class="btn btn-primary-custom" href="register.php" id="navRegister" data-testid="nav-register-btn">
                            Register
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </nav>
</header>

<main class="py-4">
