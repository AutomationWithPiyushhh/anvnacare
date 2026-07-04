<?php
require_once 'includes/header.php';

// Fetch categories
$categories = ['General', 'Diabetes', 'Organ Health', 'Cardiac', 'Hormones', 'Infections', 'Packages'];

// Current query states
$selectedCategory = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build SQL
$sql = "SELECT * FROM tests WHERE 1=1";
$params = [];

if (!empty($selectedCategory)) {
    $sql .= " AND category = ?";
    $params[] = $selectedCategory;
}

if (!empty($searchQuery)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%" . $searchQuery . "%";
}

$sql .= " ORDER BY id ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tests = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Header banner -->
    <div class="mb-4">
        <h2 class="fw-bold text-success" id="labsTitle" data-testid="labs-title">Diagnostic Lab Tests</h2>
        <p class="text-muted">Schedule home sample collection or book walk-ins at our certified diagnostic labs</p>
    </div>

    <!-- Controls Row -->
    <div class="row mb-4 align-items-center g-3">
        <!-- Search bar -->
        <div class="col-md-6">
            <form method="GET" action="lab-tests.php" id="testSearchForm" data-testid="test-search-form">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search blood tests or health checkups..." name="search" id="testSearchInput" data-testid="test-search-input" value="<?= htmlspecialchars($searchQuery) ?>">
                    <button class="btn btn-success" type="submit" id="testSearchSubmit" data-testid="test-search-submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Home Collection vs Lab Visit preference (Global radio control for automation practice) -->
        <div class="col-md-5 ms-auto text-md-end">
            <div class="bg-light p-2 rounded d-inline-flex align-items-center gap-3">
                <span class="small fw-bold text-muted ps-2">Collection Mode:</span>
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="radio" name="collectionMode" id="collectionHome" value="home" checked data-testid="collection-home-radio">
                    <label class="form-check-label small fw-semibold" for="collectionHome">Home Sample Collection</label>
                </div>
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="radio" name="collectionMode" id="collectionLab" value="lab" data-testid="collection-lab-radio">
                    <label class="form-check-label small fw-semibold" for="collectionLab">Visit Lab Center</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card glass-card border-0 p-4 sticky-top" style="top: 90px; z-index: 100;" id="filtersCard" data-testid="filters-card">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Filter Categories</h5>

                <!-- Category Checkboxes -->
                <div class="mb-4">
                    <?php foreach ($categories as $cat): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-category" type="checkbox" value="<?= $cat ?>" id="cat-<?= str_replace(' ', '', $cat) ?>" data-testid="filter-category-<?= strtolower(str_replace(' ', '-', $cat)) ?>" <?= $selectedCategory === $cat ? 'checked' : '' ?> onchange="applyFilters()">
                            <label class="form-check-label small" for="cat-<?= str_replace(' ', '', $cat) ?>">
                                <?= $cat ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Clear button -->
                <button class="btn btn-outline-danger btn-sm w-100" onclick="clearFilters()" id="clearFiltersBtn" data-testid="clear-filters-btn">Clear Filters</button>
            </div>
        </div>

        <!-- Catalog list -->
        <div class="col-lg-9">
            <div class="row g-4" id="testsContainer" data-testid="tests-container">
                <?php if (empty($tests)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search-heart text-muted fs-1 mb-2"></i>
                        <p class="text-muted">No diagnostic tests or packages found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tests as $t): ?>
                        <?php 
                        $discountPercentage = round((($t['mrp'] - $t['discount_price']) / $t['mrp']) * 100); 
                        $badgeBg = ($t['category'] === 'Packages') ? 'bg-warning text-dark' : 'bg-success-subtle text-success border';
                        ?>
                        <div class="col-md-6 test-item-col">
                            <div class="card glass-card h-100 p-4 border-0 border-top border-3 <?= $t['category'] === 'Packages' ? 'border-warning' : 'border-success' ?>" data-testid="test-card" id="test-card-<?= $t['id'] ?>">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge <?= $badgeBg ?>" data-testid="test-category-<?= $t['id'] ?>"><?= $t['category'] ?></span>
                                    <span class="badge bg-danger" data-testid="test-discount-badge-<?= $t['id'] ?>"><?= $discountPercentage ?>% OFF</span>
                                </div>

                                <h5 class="fw-bold text-dark mb-2" id="test-name-<?= $t['id'] ?>" data-testid="test-name"><?= htmlspecialchars($t['name']) ?></h5>
                                <p class="text-muted small mb-3" style="min-height: 45px;"><?= htmlspecialchars($t['description']) ?></p>

                                <div class="d-flex align-items-baseline gap-2 mb-3 mt-auto bg-light p-2 rounded">
                                    <span class="small text-muted fw-bold">Package Price:</span>
                                    <span class="fs-4 fw-bold text-success" id="test-price-<?= $t['id'] ?>" data-testid="test-price">₹<?= number_format($t['discount_price'], 2) ?></span>
                                    <span class="text-decoration-line-through text-muted small">₹<?= number_format($t['mrp'], 2) ?></span>
                                </div>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary-custom w-100 d-flex align-items-center justify-content-center gap-2" onclick="bookDiagnosticTest(<?= $t['id'] ?>)" id="bookTestBtn-<?= $t['id'] ?>" data-testid="book-test-btn">
                                        <i class="bi bi-calendar-plus"></i> Add diagnostic checkup
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('testSearchInput').value.trim();
    let category = '';
    document.querySelectorAll('.filter-category:checked').forEach(cb => {
        category = cb.value;
    });

    let url = `lab-tests.php?search=${encodeURIComponent(search)}`;
    if (category) url += `&category=${encodeURIComponent(category)}`;

    window.location.href = url;
}

// Clear filters
function clearFilters() {
    window.location.href = 'lab-tests.php';
}

// Add test to cart
function bookDiagnosticTest(testId) {
    // Check which collection radio is checked
    const mode = document.querySelector('input[name="collectionMode"]:checked').value;
    
    // Add item to cart
    showLoader();
    setTimeout(() => {
        fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                item_id: testId,
                item_type: 'test',
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                // Update badge count
                const badge = document.getElementById('navCartCount');
                if (badge) {
                    badge.innerText = data.cart_count;
                }
                const modeLabel = (mode === 'home') ? 'Home collection sample scheduled.' : 'Lab walkthrough walk-in registered.';
                showToast(`${data.message} ${modeLabel}`, 'success');
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(err => {
            hideLoader();
            console.error(err);
            showToast('Failed to add diagnostic package. Connection error.', 'danger');
        });
    }, 600);
}
</script>

<?php
require_once 'includes/footer.php';
?>
