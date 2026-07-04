<?php
require_once 'includes/header.php';

// Fetch options for filters
$categories = ['OTC', 'Prescription', 'Vitamins'];
$manufacturers = ['Cipla Ltd', 'Abbott Healthcare', 'GlaxoSmithKline', 'USV Private Ltd', 'Mankind Pharma', 'Sun Pharma', 'Dr. Reddys Laboratories', 'Pfizer India', 'Johnson & Johnson', 'Reckitt Benckiser'];

// Current query states
$selectedCategory = $_GET['category'] ?? '';
$selectedManufacturer = $_GET['manufacturer'] ?? '';
$selectedSort = $_GET['sort'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 8;
$offset = ($page - 1) * $limit;

// Build SQL
$sql = "SELECT * FROM medicines WHERE 1=1";
$params = [];

if (!empty($selectedCategory)) {
    $sql .= " AND category = ?";
    $params[] = $selectedCategory;
}

if (!empty($selectedManufacturer)) {
    $sql .= " AND manufacturer = ?";
    $params[] = $selectedManufacturer;
}

if (!empty($searchQuery)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%" . $searchQuery . "%";
}

if ($selectedSort === 'price_asc') {
    $sql .= " ORDER BY discount_price ASC";
} elseif ($selectedSort === 'price_desc') {
    $sql .= " ORDER BY discount_price DESC";
} elseif ($selectedSort === 'rating') {
    $sql .= " ORDER BY rating DESC";
} else {
    $sql .= " ORDER BY id ASC";
}

// Clone for count before limit
$countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);

$sql .= " LIMIT $limit OFFSET $offset";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $medicines = $stmt->fetchAll();

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = (int)$countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Header banner -->
    <div class="mb-4">
        <h2 class="fw-bold text-success" id="pharmacyTitle" data-testid="pharmacy-title">Online Pharmacy</h2>
        <p class="text-muted">Buy authentic prescription and over-the-counter (OTC) medicines easily</p>
    </div>

    <!-- Controls Row -->
    <div class="row mb-4 align-items-center g-3">
        <!-- Search bar -->
        <div class="col-md-5">
            <form method="GET" action="medicines.php" id="medicineSearchForm" data-testid="medicine-search-form">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search medicines..." name="search" id="medSearchInput" data-testid="med-search-input" value="<?= htmlspecialchars($searchQuery) ?>">
                    <button class="btn btn-success" type="submit" id="medSearchSubmit" data-testid="med-search-submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Sorting -->
        <div class="col-md-3 ms-auto">
            <div class="d-flex align-items-center gap-2">
                <label for="sortBy" class="text-nowrap small fw-bold">Sort By:</label>
                <select class="form-select" id="sortBy" name="sort" data-testid="sort-dropdown" onchange="applyFilters()">
                    <option value="" <?= empty($selectedSort) ? 'selected' : '' ?>>Default</option>
                    <option value="price_asc" <?= $selectedSort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $selectedSort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="rating" <?= $selectedSort === 'rating' ? 'selected' : '' ?>>Average Rating</option>
                </select>
            </div>
        </div>

        <!-- Infinite Scroll Toggle Switch (QA challenge) -->
        <div class="col-md-3 text-end">
            <div class="form-check form-switch d-inline-block text-start">
                <input class="form-check-input" type="checkbox" id="toggleInfiniteScroll" data-testid="toggle-infinite-scroll">
                <label class="form-check-label fw-bold text-success small" for="toggleInfiniteScroll">Enable Infinite Scroll</label>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card glass-card border-0 p-4 sticky-top" style="top: 90px; z-index: 100;" id="filtersCard" data-testid="filters-card">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Filter Products</h5>

                <!-- Category Filters -->
                <div class="mb-4">
                    <h6 class="fw-bold text-success mb-2">Category</h6>
                    <?php foreach ($categories as $cat): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-category" type="checkbox" value="<?= $cat ?>" id="cat-<?= $cat ?>" data-testid="filter-category-<?= strtolower($cat) ?>" <?= $selectedCategory === $cat ? 'checked' : '' ?> onchange="applyFilters()">
                            <label class="form-check-label" for="cat-<?= $cat ?>">
                                <?= $cat ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Manufacturer Filters -->
                <div class="mb-4">
                    <h6 class="fw-bold text-success mb-2">Manufacturer</h6>
                    <div style="max-height: 200px; overflow-y: auto; padding-right: 5px;">
                        <?php foreach ($manufacturers as $man): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input filter-manufacturer" type="checkbox" value="<?= $man ?>" id="man-<?= str_replace(' ', '', $man) ?>" data-testid="filter-manufacturer-<?= strtolower(str_replace(' ', '-', $man)) ?>" <?= $selectedManufacturer === $man ? 'checked' : '' ?> onchange="applyFilters()">
                                <label class="form-check-label small" for="man-<?= str_replace(' ', '', $man) ?>">
                                    <?= $man ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Clear button -->
                <button class="btn btn-outline-danger btn-sm w-100" onclick="clearFilters()" id="clearFiltersBtn" data-testid="clear-filters-btn">Clear All Filters</button>
            </div>
        </div>

        <!-- Catalog list -->
        <div class="col-lg-9">
            <div class="row g-4" id="medicinesContainer" data-testid="medicines-container">
                <?php if (empty($medicines)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search-heart text-muted fs-1 mb-2"></i>
                        <p class="text-muted">No medicines found matching the filters.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($medicines as $med): ?>
                        <?php $discountPercentage = round((($med['mrp'] - $med['discount_price']) / $med['mrp']) * 100); ?>
                        <div class="col-sm-6 col-md-4">
                            <div class="card glass-card h-100 p-3 border-0 medicine-item-card" data-testid="medicine-card" id="med-card-<?= $med['id'] ?>">
                                <div class="position-relative text-center mb-3">
                                    <span class="position-absolute top-0 start-0 badge bg-danger" data-testid="med-discount-badge-<?= $med['id'] ?>"><?= $discountPercentage ?>% OFF</span>
                                    <a href="medicine-details.php?id=<?= $med['id'] ?>" data-testid="med-detail-link-<?= $med['id'] ?>">
                                        <img src="https://placehold.co/240x180/eef7f2/0a6c42?text=<?= urlencode($med['name']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($med['name']) ?>" style="height: 160px; object-fit: contain;">
                                    </a>
                                </div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" id="med-name-<?= $med['id'] ?>" data-testid="med-name">
                                    <a href="medicine-details.php?id=<?= $med['id'] ?>" class="text-decoration-none text-dark hover-accent"><?= htmlspecialchars($med['name']) ?></a>
                                </h6>
                                <p class="text-muted small mb-2"><?= htmlspecialchars($med['manufacturer']) ?> | <span class="badge bg-light text-success border"><?= $med['category'] ?></span></p>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-success me-2" id="med-rating-<?= $med['id'] ?>" data-testid="med-rating"><i class="bi bi-star-fill"></i> <?= $med['rating'] ?></span>
                                    <span class="text-muted small">Stock: <strong class="<?= $med['stock'] > 0 ? 'text-success' : 'text-danger' ?>"><?= $med['stock'] ?> left</strong></span>
                                </div>
                                <div class="d-flex align-items-baseline gap-2 mb-3">
                                    <span class="fs-5 fw-bold text-success" id="med-price-<?= $med['id'] ?>" data-testid="med-price">₹<?= number_format($med['discount_price'], 2) ?></span>
                                    <span class="text-decoration-line-through text-muted small">₹<?= number_format($med['mrp'], 2) ?></span>
                                </div>
                                <div class="mt-auto d-flex gap-2">
                                    <button class="btn btn-outline-success btn-sm w-100 d-flex align-items-center justify-content-center gap-1" onclick="addToCart(<?= $med['id'] ?>, 'medicine')" id="addCartBtn-<?= $med['id'] ?>" data-testid="add-cart-btn" <?= $med['stock'] <= 0 ? 'disabled' : '' ?>>
                                        <i class="bi bi-cart-plus"></i> <?= $med['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="addToWishlist(<?= $med['id'] ?>, 'medicine')" id="wishBtn-<?= $med['id'] ?>" data-testid="wish-btn" aria-label="Add to Wishlist">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Standard Pagination (hidden if Infinite Scroll is checked) -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-5" id="paginationNav" data-testid="pagination-nav">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page-1 ?>&category=<?= $selectedCategory ?>&manufacturer=<?= $selectedManufacturer ?>&sort=<?= $selectedSort ?>&search=<?= urlencode($searchQuery) ?>" id="paginationPrev" data-testid="pagination-prev">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&category=<?= $selectedCategory ?>&manufacturer=<?= $selectedManufacturer ?>&sort=<?= $selectedSort ?>&search=<?= urlencode($searchQuery) ?>" id="paginationPage-<?= $i ?>" data-testid="pagination-page-<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page+1 ?>&category=<?= $selectedCategory ?>&manufacturer=<?= $selectedManufacturer ?>&sort=<?= $selectedSort ?>&search=<?= urlencode($searchQuery) ?>" id="paginationNext" data-testid="pagination-next">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <!-- Infinite Scroll Loader spinner (hidden by default) -->
            <div id="infiniteScrollLoader" class="d-none text-center my-4" data-testid="infinite-scroll-loader">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading next page...</span>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Filter handlers
function applyFilters() {
    const sortBy = document.getElementById('sortBy').value;
    const search = document.getElementById('medSearchInput').value.trim();
    
    // Category check
    let category = '';
    document.querySelectorAll('.filter-category:checked').forEach(cb => {
        category = cb.value; // Taking single category filter for simplicity
    });

    // Manufacturer check
    let manufacturer = '';
    document.querySelectorAll('.filter-manufacturer:checked').forEach(cb => {
        manufacturer = cb.value;
    });

    let url = `medicines.php?sort=${sortBy}&search=${encodeURIComponent(search)}`;
    if (category) url += `&category=${encodeURIComponent(category)}`;
    if (manufacturer) url += `&manufacturer=${encodeURIComponent(manufacturer)}`;

    window.location.href = url;
}

function clearFilters() {
    window.location.href = 'medicines.php';
}

// Infinite Scroll logic
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleInfiniteScroll');
    const pagination = document.getElementById('paginationNav');
    const loader = document.getElementById('infiniteScrollLoader');
    const container = document.getElementById('medicinesContainer');

    let isInfiniteScroll = false;
    let nextPage = 2;
    let loading = false;
    let totalPages = <?= $totalPages ?>;

    // Read initial preference if stored in local storage
    if (localStorage.getItem('infiniteScrollActive') === 'true') {
        toggle.checked = true;
        isInfiniteScroll = true;
        if (pagination) pagination.classList.add('d-none');
    }

    toggle.addEventListener('change', function () {
        isInfiniteScroll = this.checked;
        localStorage.setItem('infiniteScrollActive', isInfiniteScroll);
        
        if (isInfiniteScroll) {
            if (pagination) pagination.classList.add('d-none');
            // Check if page scroll triggers load
            checkScroll();
        } else {
            // Reload page to return to normal paginated layout
            window.location.reload();
        }
    });

    window.addEventListener('scroll', function () {
        if (!isInfiniteScroll) return;
        checkScroll();
    });

    function checkScroll() {
        if (loading || nextPage > totalPages) return;
        
        // If scrolled to bottom (80% of scroll height)
        if ((window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight - 200) {
            loadNextPage();
        }
    }

    function loadNextPage() {
        loading = true;
        loader.classList.remove('d-none');

        const sortBy = document.getElementById('sortBy').value;
        const search = document.getElementById('medSearchInput').value;
        
        let category = '';
        document.querySelectorAll('.filter-category:checked').forEach(cb => { category = cb.value; });
        let manufacturer = '';
        document.querySelectorAll('.filter-manufacturer:checked').forEach(cb => { manufacturer = cb.value; });

        let url = `api/medicines.php?page=${nextPage}&limit=8&sort=${sortBy}&search=${encodeURIComponent(search)}`;
        if (category) url += `&category=${encodeURIComponent(category)}`;
        if (manufacturer) url += `&manufacturer=${encodeURIComponent(manufacturer)}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                loader.classList.add('d-none');
                loading = false;

                if (data.success && data.medicines.length > 0) {
                    data.medicines.forEach(med => {
                        const discount = Math.round(((med.mrp - med.discount_price) / med.mrp) * 100);
                        const disabled = med.stock <= 0 ? 'disabled' : '';
                        const btnText = med.stock > 0 ? 'Add to Cart' : 'Out of Stock';

                        const div = document.createElement('div');
                        div.className = 'col-sm-6 col-md-4';
                        div.innerHTML = `
                            <div class="card glass-card h-100 p-3 border-0 medicine-item-card" data-testid="medicine-card" id="med-card-${med.id}">
                                <div class="position-relative text-center mb-3">
                                    <span class="position-absolute top-0 start-0 badge bg-danger" data-testid="med-discount-badge-${med.id}">${discount}% OFF</span>
                                    <a href="medicine-details.php?id=${med.id}" data-testid="med-detail-link-${med.id}">
                                        <img src="https://placehold.co/240x180/eef7f2/0a6c42?text=${encodeURIComponent(med.name)}" class="img-fluid rounded" alt="${med.name}" style="height: 160px; object-fit: contain;">
                                    </a>
                                </div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" id="med-name-${med.id}" data-testid="med-name">
                                    <a href="medicine-details.php?id=${med.id}" class="text-decoration-none text-dark hover-accent">${med.name}</a>
                                </h6>
                                <p class="text-muted small mb-2">${med.manufacturer} | <span class="badge bg-light text-success border">${med.category}</span></p>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-success me-2" id="med-rating-${med.id}" data-testid="med-rating"><i class="bi bi-star-fill"></i> ${med.rating}</span>
                                    <span class="text-muted small">Stock: <strong class="${med.stock > 0 ? 'text-success' : 'text-danger'}">${med.stock} left</strong></span>
                                </div>
                                <div class="d-flex align-items-baseline gap-2 mb-3">
                                    <span class="fs-5 fw-bold text-success" id="med-price-${med.id}" data-testid="med-price">₹${parseFloat(med.discount_price).toFixed(2)}</span>
                                    <span class="text-decoration-line-through text-muted small">₹${parseFloat(med.mrp).toFixed(2)}</span>
                                </div>
                                <div class="mt-auto d-flex gap-2">
                                    <button class="btn btn-outline-success btn-sm w-100 d-flex align-items-center justify-content-center gap-1" onclick="addToCart(${med.id}, 'medicine')" id="addCartBtn-${med.id}" data-testid="add-cart-btn" ${disabled}>
                                        <i class="bi bi-cart-plus"></i> ${btnText}
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="addToWishlist(${med.id}, 'medicine')" id="wishBtn-${med.id}" data-testid="wish-btn" aria-label="Add to Wishlist">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        container.appendChild(div);
                    });
                    nextPage++;
                    // Check again if page height is still smaller than screen
                    checkScroll();
                }
            })
            .catch(err => {
                loader.classList.add('d-none');
                loading = false;
                console.error(err);
            });
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
