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
    // UNION SQL to get both medicines and products in a single list
    $stmt = $pdo->prepare("
        SELECT w.id as wishlist_id, w.item_type, w.item_id, m.name, m.discount_price, m.mrp, m.rating, m.manufacturer, m.stock, m.category, m.image
        FROM wishlist w
        JOIN medicines m ON w.item_id = m.id AND w.item_type = 'medicine'
        WHERE w.user_id = ?
        UNION
        SELECT w.id as wishlist_id, w.item_type, w.item_id, p.name, p.discount_price, p.mrp, p.rating, 'Health Store' as manufacturer, p.stock, p.category, p.image
        FROM wishlist w
        JOIN products p ON w.item_id = p.id AND w.item_type = 'product'
        WHERE w.user_id = ?
    ");
    $stmt->execute([$userId, $userId]);
    $wishlistItems = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Breadcrumb Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-success" id="wishlistTitle" data-testid="wishlist-title">My Wishlist</h2>
        <p class="text-muted">Manage items you have saved for later purchase</p>
    </div>

    <!-- Wishlist Grid -->
    <div class="row g-4" id="wishlistContainer" data-testid="wishlist-container">
        <?php if (empty($wishlistItems)): ?>
            <div class="col-12 text-center py-5">
                <div class="mb-3"><i class="bi bi-heartbreak text-muted" style="font-size: 4rem;"></i></div>
                <h4 class="fw-bold text-dark">Your Wishlist is Empty</h4>
                <p class="text-muted">Explore our pharmacy catalog or health store to save products.</p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <a href="medicines.php" class="btn btn-primary-custom" id="wishlistShopMeds" data-testid="wishlist-shop-meds">Buy Medicines</a>
                    <a href="health-store.php" class="btn btn-secondary-custom" id="wishlistShopStore" data-testid="wishlist-shop-store">Shop Health Store</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($wishlistItems as $item): ?>
                <?php 
                $discountPercentage = round((($item['mrp'] - $item['discount_price']) / $item['mrp']) * 100); 
                $itemId = $item['item_id'];
                $itemType = $item['item_type'];
                
                $imgSrc = 'assets/images/categories/' . strtolower($item['category']) . '.png';
                if (!empty($item['image'])) {
                    if (file_exists(__DIR__ . '/' . $item['image'])) {
                        $imgSrc = $item['image'];
                    } else {
                        $svgPath = str_replace('.png', '.svg', $item['image']);
                        if (file_exists(__DIR__ . '/' . $svgPath)) {
                            $imgSrc = $svgPath;
                        }
                    }
                }
                ?>
                <div class="col-sm-6 col-md-4 col-lg-3" id="wishlist-item-card-<?= $item['wishlist_id'] ?>" data-testid="wishlist-card">
                    <div class="card glass-card h-100 p-3 border-0">
                        <!-- Badges -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-danger"><?= $discountPercentage ?>% OFF</span>
                            <span class="badge bg-light text-dark border capitalize" style="text-transform: capitalize;"><?= $itemType ?></span>
                        </div>

                        <!-- Details -->
                        <h6 class="fw-bold text-dark mb-1 text-truncate" data-testid="wishlist-item-name"><?= htmlspecialchars($item['name']) ?></h6>
                        <p class="text-muted small mb-2 text-truncate"><?= htmlspecialchars($item['manufacturer']) ?></p>
                        
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2"><i class="bi bi-star-fill"></i> <?= $item['rating'] ?></span>
                            <span class="text-muted small">Stock: <?= $item['stock'] > 0 ? $item['stock'] . ' left' : '<span class="text-danger">Out of stock</span>' ?></span>
                        </div>

                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span class="fs-5 fw-bold text-success">₹<?= number_format($item['discount_price'], 2) ?></span>
                            <span class="text-decoration-line-through text-muted small">₹<?= number_format($item['mrp'], 2) ?></span>
                        </div>

                        <!-- Actions -->
                        <div class="mt-auto d-flex gap-2">
                            <button class="btn btn-outline-success btn-sm w-100 d-flex align-items-center justify-content-center gap-1" onclick="moveToCart(<?= $itemId ?>, '<?= $itemType ?>', <?= $item['wishlist_id'] ?>)" id="addCartBtn-<?= $item['wishlist_id'] ?>" data-testid="add-cart-btn" <?= $item['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="removeWishlistItem(<?= $itemId ?>, '<?= $itemType ?>', <?= $item['wishlist_id'] ?>)" id="removeWishBtn-<?= $item['wishlist_id'] ?>" data-testid="remove-wish-btn" aria-label="Remove item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Remove Item from Wishlist via AJAX
function removeWishlistItem(itemId, itemType, wishlistId) {
    showLoader();
    fetch('api/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            item_id: itemId,
            item_type: itemType
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        if (data.success) {
            showToast(data.message, 'success');
            // Remove element from DOM
            const element = document.getElementById(`wishlist-item-card-${wishlistId}`);
            if (element) {
                element.remove();
            }
            
            // Check if wishlist container is now empty
            const cards = document.querySelectorAll('[data-testid="wishlist-card"]');
            if (cards.length === 0) {
                window.location.reload(); // Reload to show empty state
            }
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(err => {
        hideLoader();
        console.error(err);
        showToast('Failed to remove item. Connection error.', 'danger');
    });
}

// Move to Cart: Add to Cart and remove from Wishlist
function moveToCart(itemId, itemType, wishlistId) {
    // Add to cart
    showLoader();
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            item_id: itemId,
            item_type: itemType,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update header badge
            const badge = document.getElementById('navCartCount');
            if (badge) badge.innerText = data.cart_count;

            // Remove from wishlist
            return fetch('api/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    item_id: itemId,
                    item_type: itemType
                })
            });
        } else {
            throw new Error(data.message);
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        if (data.success) {
            showToast('Item moved to shopping cart successfully.', 'success');
            const element = document.getElementById(`wishlist-item-card-${wishlistId}`);
            if (element) element.remove();
            
            const cards = document.querySelectorAll('[data-testid="wishlist-card"]');
            if (cards.length === 0) {
                window.location.reload();
            }
        }
    })
    .catch(err => {
        hideLoader();
        console.error(err);
        showToast(err.message || 'Error occurred.', 'danger');
    });
}
</script>

<?php
require_once 'includes/footer.php';
?>
