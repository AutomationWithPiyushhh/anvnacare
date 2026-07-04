<?php
require_once 'includes/header.php';
?>

<!-- Hero Banner Section -->
<div class="container mt-4">
    <div class="hero-section" id="heroBanner" data-testid="hero-banner">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill mb-3 fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> Trusted by 10 Lakh+ Patients
                </span>
                <h1 class="hero-title" id="heroTitle" data-testid="hero-title">
                    Your Complete Digital <br>
                    <span class="text-success">Healthcare Partner</span>
                </h1>
                <p class="lead text-secondary mb-4">
                    Order medicines, book face-to-face consults with top doctors, and request home-sample collections for laboratory diagnostic tests. Simple, fast, and secure.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="medicines.php" class="btn btn-primary-custom btn-lg d-flex align-items-center gap-2" id="heroOrderBtn" data-testid="hero-order-btn">
                        <i class="bi bi-capsule"></i> Order Medicines
                    </a>
                    <a href="doctors.php" class="btn btn-secondary-custom btn-lg d-flex align-items-center gap-2" id="heroConsultBtn" data-testid="hero-consult-btn">
                        <i class="bi bi-person-heart"></i> Book Consultation
                    </a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center">
                <!-- High-quality SVG illustration of medical dashboard -->
                <svg viewBox="0 0 400 350" width="100%" height="320" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="blueGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#0284c7" />
                            <stop offset="100%" stop-color="#0369a1" />
                        </linearGradient>
                        <linearGradient id="greenGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#0a6c42" />
                            <stop offset="100%" stop-color="#074e2f" />
                        </linearGradient>
                    </defs>
                    <!-- Background shapes -->
                    <circle cx="200" cy="175" r="150" fill="#f0faf4" />
                    <!-- Medical Clipboard -->
                    <rect x="120" y="50" width="160" height="230" rx="15" fill="#ffffff" stroke="#e2e8f0" stroke-width="6" />
                    <rect x="170" y="35" width="60" height="25" rx="5" fill="#94a3b8" />
                    <!-- Clipboard lines -->
                    <rect x="145" y="90" width="110" height="15" rx="4" fill="url(#greenGrad)" />
                    <rect x="145" y="125" width="80" height="8" rx="4" fill="#cbd5e1" />
                    <rect x="145" y="145" width="110" height="8" rx="4" fill="#cbd5e1" />
                    <rect x="145" y="165" width="95" height="8" rx="4" fill="#cbd5e1" />
                    <rect x="145" y="195" width="110" height="15" rx="4" fill="url(#blueGrad)" />
                    <rect x="145" y="230" width="90" height="8" rx="4" fill="#cbd5e1" />
                    <rect x="145" y="250" width="105" height="8" rx="4" fill="#cbd5e1" />
                    <!-- Stethoscope -->
                    <path d="M 80,120 C 80,240 320,240 320,120" fill="none" stroke="#64748b" stroke-width="8" stroke-linecap="round" />
                    <path d="M 80,120 L 80,100 M 320,120 L 320,100" fill="none" stroke="#475569" stroke-width="8" stroke-linecap="round" />
                    <circle cx="200" cy="235" r="22" fill="#e2e8f0" stroke="#64748b" stroke-width="4" />
                    <circle cx="200" cy="235" r="12" fill="#ffffff" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Quick Module Selector Grid -->
<div class="container my-5">
    <h2 class="text-center mb-4 fw-bold" id="modulesHeader" data-testid="modules-header">Our Specialized Services</h2>
    <div class="row g-4 justify-content-center">
        <!-- Pharmacy -->
        <div class="col-md-6 col-lg-3">
            <div class="card glass-card h-100 p-3 text-center border-0" id="modulePharmacyCard" data-testid="module-pharmacy-card">
                <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-capsule fs-2"></i>
                </div>
                <h4 class="fw-bold mb-2">Pharmacy</h4>
                <p class="text-muted small mb-3">Get genuine prescription and OTC medicines delivered straight to your doorstep.</p>
                <a href="medicines.php" class="btn btn-outline-primary-custom w-100 mt-auto" id="btnGoPharmacy" data-testid="btn-go-pharmacy">Buy Medicines</a>
            </div>
        </div>
        
        <!-- Doctors -->
        <div class="col-md-6 col-lg-3">
            <div class="card glass-card h-100 p-3 text-center border-0" id="moduleDoctorsCard" data-testid="module-doctors-card">
                <div class="rounded-circle bg-primary-subtle text-primary mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-person-heart fs-2"></i>
                </div>
                <h4 class="fw-bold mb-2">Doctor Consult</h4>
                <p class="text-muted small mb-3">Book slots with top physicians across 15+ specialities for virtual or physical clinic checkups.</p>
                <a href="doctors.php" class="btn btn-outline-primary-custom w-100 mt-auto" id="btnGoDoctors" data-testid="btn-go-doctors">Consult Now</a>
            </div>
        </div>

        <!-- Diagnostics -->
        <div class="col-md-6 col-lg-3">
            <div class="card glass-card h-100 p-3 text-center border-0" id="moduleLabCard" data-testid="module-lab-card">
                <div class="rounded-circle bg-info-subtle text-info mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-clipboard2-pulse fs-2"></i>
                </div>
                <h4 class="fw-bold mb-2">Lab Tests</h4>
                <p class="text-muted small mb-3">Schedule home sample collection for diagnostic checks. Accurate certified lab reports.</p>
                <a href="lab-tests.php" class="btn btn-outline-primary-custom w-100 mt-auto" id="btnGoLabs" data-testid="btn-go-labs">Book Lab Test</a>
            </div>
        </div>

        <!-- Health Store -->
        <div class="col-md-6 col-lg-3">
            <div class="card glass-card h-100 p-3 text-center border-0" id="moduleStoreCard" data-testid="module-store-card">
                <div class="rounded-circle bg-warning-subtle text-warning mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-heart-pulse fs-2"></i>
                </div>
                <h4 class="fw-bold mb-2">Health Store</h4>
                <p class="text-muted small mb-3">Explore health devices, protein supplements, vitamins, and regular daily personal care items.</p>
                <a href="health-store.php" class="btn btn-outline-primary-custom w-100 mt-auto" id="btnGoStore" data-testid="btn-go-store">Shop Store</a>
            </div>
        </div>
    </div>
</div>

<!-- Featured Medicines Showcase -->
<div class="container my-5 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" id="featuredMedHeader" data-testid="featured-med-header">Best Selling Medicines</h2>
        <a href="medicines.php" class="text-success fw-semibold text-decoration-none" id="viewAllMedsLink" data-testid="view-all-meds-link">View All <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM medicines ORDER BY rating DESC LIMIT 4");
            while ($med = $stmt->fetch()) {
                $discountPercentage = round((($med['mrp'] - $med['discount_price']) / $med['mrp']) * 100);
                echo '
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card glass-card h-100 p-3 border-0" data-testid="medicine-card" id="med-card-' . $med['id'] . '">
                        <div class="position-relative text-center mb-3">
                            <span class="position-absolute top-0 start-0 badge bg-danger" data-testid="med-discount-badge-' . $med['id'] . '">' . $discountPercentage . '% OFF</span>
                             <img src="assets/images/categories/' . strtolower($med['category']) . '.png" class="img-fluid rounded" alt="' . htmlspecialchars($med['name']) . '" style="height: 150px; object-fit: contain;">
                        </div>
                        <h6 class="fw-bold text-dark text-truncate mb-1" id="med-name-' . $med['id'] . '" data-testid="med-name">' . htmlspecialchars($med['name']) . '</h6>
                        <p class="text-muted small mb-2">' . htmlspecialchars($med['manufacturer']) . '</p>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2" id="med-rating-' . $med['id'] . '" data-testid="med-rating"><i class="bi bi-star-fill"></i> ' . $med['rating'] . '</span>
                            <span class="text-muted small">(' . rand(10, 80) . ' reviews)</span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span class="fs-5 fw-bold text-success" id="med-price-' . $med['id'] . '" data-testid="med-price">₹' . number_format($med['discount_price'], 2) . '</span>
                            <span class="text-decoration-line-through text-muted small">₹' . number_format($med['mrp'], 2) . '</span>
                        </div>
                        <div class="mt-auto d-flex gap-2">
                            <button class="btn btn-outline-success btn-sm w-100 d-flex align-items-center justify-content-center gap-1" onclick="addToCart(' . $med['id'] . ', \'medicine\')" id="addCartBtn-' . $med['id'] . '" data-testid="add-cart-btn">
                                <i class="bi bi-cart-plus"></i> Add
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="addToWishlist(' . $med['id'] . ', \'medicine\')" id="wishBtn-' . $med['id'] . '" data-testid="wish-btn" aria-label="Add to Wishlist">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>';
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-warning">Failed to load medicines.</div>';
        }
        ?>
    </div>
</div>

<!-- Promo Banner Section -->
<div class="container my-5">
    <div class="bg-success text-white p-4 rounded-3 shadow d-flex flex-wrap align-items-center justify-content-between" id="promoCouponBanner" data-testid="promo-coupon-banner">
        <div class="mb-3 mb-md-0">
            <h3 class="fw-bold mb-1"><i class="bi bi-tags-fill me-2"></i> Get Flat 10% Discount on First Order</h3>
            <p class="mb-0 text-white-50">Apply coupon code <strong class="text-white bg-dark px-2 py-1 rounded" id="couponCodeWelcome" data-testid="coupon-code-welcome">WELCOME</strong> during checkout. Minimum value ₹500.</p>
        </div>
        <a href="medicines.php" class="btn btn-light text-success fw-bold px-4 py-2" id="couponShopBtn" data-testid="coupon-shop-btn" style="border-radius: var(--border-radius-sm)">Shop Now</a>
    </div>
</div>

<!-- Featured Lab Diagnostics -->
<div class="container my-5 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" id="featuredTestHeader" data-testid="featured-test-header">Popular Diagnostic Packages</h2>
        <a href="lab-tests.php" class="text-success fw-semibold text-decoration-none" id="viewAllTestsLink" data-testid="view-all-tests-link">View All <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM tests WHERE category = 'Packages' LIMIT 3");
            while ($test = $stmt->fetch()) {
                echo '
                <div class="col-md-4">
                    <div class="card glass-card h-100 p-4 border-0" data-testid="test-package-card" id="test-card-' . $test['id'] . '">
                        <span class="badge bg-info text-dark align-self-start mb-3" data-testid="test-badge-' . $test['id'] . '"><i class="bi bi-activity"></i> Full Body Checkup</span>
                        <h5 class="fw-bold text-dark mb-2" id="test-name-' . $test['id'] . '" data-testid="test-name">' . htmlspecialchars($test['name']) . '</h5>
                        <p class="text-muted small mb-3 text-truncate-2" style="height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">' . htmlspecialchars($test['description']) . '</p>
                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span class="fs-4 fw-bold text-success" id="test-price-' . $test['id'] . '" data-testid="test-price">₹' . number_format($test['discount_price'], 2) . '</span>
                            <span class="text-decoration-line-through text-muted small">₹' . number_format($test['mrp'], 2) . '</span>
                        </div>
                        <div class="mt-auto">
                            <button class="btn btn-primary-custom w-100 d-flex align-items-center justify-content-center gap-2" onclick="addToCart(' . $test['id'] . ', \'test\')" id="bookTestBtn-' . $test['id'] . '" data-testid="book-test-btn">
                                <i class="bi bi-calendar-plus"></i> Add Diagnostic Package
                            </button>
                        </div>
                    </div>
                </div>';
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-warning">Failed to load diagnostic tests.</div>';
        }
        ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
