<?php
require_once 'includes/header.php';

// Fetch options for filters
$categories = ['Devices', 'Supplements', 'Wellness'];

// Current query states
$selectedCategory = $_GET['category'] ?? '';
$selectedSort = $_GET['sort'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 8;
$offset = ($page - 1) * $limit;

// Build SQL
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($selectedCategory)) {
    $sql .= " AND category = ?";
    $params[] = $selectedCategory;
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
    $products = $stmt->fetchAll();

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
        <h2 class="fw-bold text-success" id="storeTitle" data-testid="store-title">Health Store</h2>
        <p class="text-muted">Shop healthcare equipment, organic supplements, wellness wellness checks, and personal care</p>
    </div>

    <!-- Controls Row -->
    <div class="row mb-4 align-items-center g-3">
        <!-- Search bar -->
        <div class="col-md-6">
            <form method="GET" action="health-store.php" id="productSearchForm" data-testid="product-search-form">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search products..." name="search" id="prodSearchInput" data-testid="prod-search-input" value="<?= htmlspecialchars($searchQuery) ?>">
                    <button class="btn btn-success" type="submit" id="prodSearchSubmit" data-testid="prod-search-submit">Search</button>
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

                <!-- Clear button -->
                <button class="btn btn-outline-danger btn-sm w-100" onclick="clearFilters()" id="clearFiltersBtn" data-testid="clear-filters-btn">Clear All Filters</button>
            </div>
        </div>

        <!-- Catalog list -->
        <div class="col-lg-9">
            <!-- Simulated Skeleton Loader (Visible for 1.2 seconds to allow students to automate waiting for loaders/skeletons) -->
            <div id="skeletonLoaderContainer" class="row g-4 d-none" data-testid="skeleton-loader-container">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="col-sm-6 col-md-4">
                        <div class="card border-0 p-3 h-100" style="min-height: 350px; background-color: #fafbfc;">
                            <div class="skeleton-box mb-3" style="height: 160px;"></div>
                            <div class="skeleton-box mb-2" style="height: 20px; width: 80%;"></div>
                            <div class="skeleton-box mb-2" style="height: 15px; width: 60%;"></div>
                            <div class="skeleton-box mb-3" style="height: 15px; width: 40%;"></div>
                            <div class="d-flex gap-2 mt-auto">
                                <div class="skeleton-box" style="height: 30px; width: 75%;"></div>
                                <div class="skeleton-box" style="height: 30px; width: 25%;"></div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Product Grid -->
            <div class="row g-4" id="productsContainer" data-testid="products-container">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search-heart text-muted fs-1 mb-2"></i>
                        <p class="text-muted">No products found matching the filters.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $prod): ?>
                        <?php $discountPercentage = round((($prod['mrp'] - $prod['discount_price']) / $prod['mrp']) * 100); ?>
                        <div class="col-sm-6 col-md-4 product-item-col">
                            <div class="card glass-card h-100 p-3 border-0" data-testid="product-card" id="prod-card-<?= $prod['id'] ?>">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-danger" data-testid="prod-discount-badge-<?= $prod['id'] ?>"><?= $discountPercentage ?>% OFF</span>
                                    <span class="badge bg-light text-success border"><?= $prod['category'] ?></span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" id="prod-name-<?= $prod['id'] ?>" data-testid="prod-name"><?= htmlspecialchars($prod['name']) ?></h6>
                                <p class="text-muted small mb-2">Stock: <strong class="<?= $prod['stock'] > 0 ? 'text-success' : 'text-danger' ?>"><?= $prod['stock'] ?> left</strong></p>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-success me-2" id="prod-rating-<?= $prod['id'] ?>" data-testid="prod-rating"><i class="bi bi-star-fill"></i> <?= $prod['rating'] ?></span>
                                    <span class="text-muted small">Rating: <?= $prod['rating'] ?> / 5</span>
                                </div>
                                <div class="d-flex align-items-baseline gap-2 mb-3">
                                    <span class="fs-5 fw-bold text-success" id="prod-price-<?= $prod['id'] ?>" data-testid="prod-price">₹<?= number_format($prod['discount_price'], 2) ?></span>
                                    <span class="text-decoration-line-through text-muted small">₹<?= number_format($prod['mrp'], 2) ?></span>
                                </div>
                                <div class="mt-auto d-flex gap-2">
                                    <button class="btn btn-outline-success btn-sm w-100 d-flex align-items-center justify-content-center gap-1" onclick="addToCart(<?= $prod['id'] ?>, 'product')" id="addCartBtn-<?= $prod['id'] ?>" data-testid="add-cart-btn" <?= $prod['stock'] <= 0 ? 'disabled' : '' ?>>
                                        <i class="bi bi-cart-plus"></i> <?= $prod['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="addToWishlist(<?= $prod['id'] ?>, 'product')" id="wishBtn-<?= $prod['id'] ?>" data-testid="wish-btn" aria-label="Add to Wishlist">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Standard Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-5" id="paginationNav" data-testid="pagination-nav">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page-1 ?>&category=<?= $selectedCategory ?>&sort=<?= $selectedSort ?>&search=<?= urlencode($searchQuery) ?>" id="paginationPrev" data-testid="pagination-prev">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&category=<?= $selectedCategory ?>&sort=<?= $selectedSort ?>&search=<?= urlencode($searchQuery) ?>" id="paginationPage-<?= $i ?>" data-testid="pagination-page-<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page+1 ?>&category=<?= $selectedCategory ?>&sort=<?= $selectedSort ?>&search=<?= urlencode($searchQuery) ?>" id="paginationNext" data-testid="pagination-next">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Filter handlers
function applyFilters() {
    const sortBy = document.getElementById('sortBy').value;
    const search = document.getElementById('prodSearchInput').value.trim();
    
    let category = '';
    document.querySelectorAll('.filter-category:checked').forEach(cb => {
        category = cb.value;
    });

    let url = `health-store.php?sort=${sortBy}&search=${encodeURIComponent(search)}`;
    if (category) url += `&category=${encodeURIComponent(category)}`;

    // Simulate skeleton loader on filter changes!
    const skeleton = document.getElementById('skeletonLoaderContainer');
    const container = document.getElementById('productsContainer');
    const pagination = document.getElementById('paginationNav');

    if (skeleton && container) {
        container.classList.add('d-none');
        if (pagination) pagination.classList.add('d-none');
        skeleton.classList.remove('d-none');
    }

    setTimeout(() => {
        window.location.href = url;
    }, 1200); // 1.2 second simulated skeleton loader delay
}

function clearFilters() {
    window.location.href = 'health-store.php';
}
</script>

<?php
require_once 'includes/footer.php';
?>
