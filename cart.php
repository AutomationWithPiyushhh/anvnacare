<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/header.php';

$cartItems = [];
$subtotal = 0.00;

try {
    if (isset($_SESSION['user_id'])) {
        // Logged-in Cart
        $stmt = $pdo->prepare("SELECT c.id as cart_record_id, c.item_type, c.item_id, c.quantity, m.name, m.discount_price, m.mrp, m.stock, m.manufacturer, m.category
            FROM cart c
            JOIN medicines m ON c.item_id = m.id AND c.item_type = 'medicine'
            WHERE c.user_id = ?
            UNION
            SELECT c.id as cart_record_id, c.item_type, c.item_id, c.quantity, p.name, p.discount_price, p.mrp, p.stock, 'Health Store' as manufacturer, p.category
            FROM cart c
            JOIN products p ON c.item_id = p.id AND c.item_type = 'product'
            WHERE c.user_id = ?
            UNION
            SELECT c.id as cart_record_id, c.item_type, c.item_id, c.quantity, t.name, t.discount_price, t.mrp, 999 as stock, 'Diagnostics' as manufacturer, t.category
            FROM cart c
            JOIN tests t ON c.item_id = t.id AND c.item_type = 'test'
            WHERE c.user_id = ?");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
        $cartItems = $stmt->fetchAll();
    } else {
        // Guest Cart
        if (isset($_SESSION['guest_cart'])) {
            foreach ($_SESSION['guest_cart'] as $key => $qty) {
                list($itemType, $itemId) = explode('_', $key);
                $tableName = ($itemType === 'medicine') ? 'medicines' : (($itemType === 'product') ? 'products' : 'tests');
                $stmt = $pdo->prepare("SELECT id, name, discount_price, mrp, category, " . ($itemType !== 'test' ? 'stock' : '999 as stock') . ", " . ($itemType === 'medicine' ? 'manufacturer' : "'Health Store' as manufacturer") . " FROM $tableName WHERE id = ?");
                $stmt->execute([$itemId]);
                $details = $stmt->fetch();
                if ($details) {
                    $cartItems[] = [
                        'cart_record_id' => 0,
                        'item_type' => $itemType,
                        'item_id' => $itemId,
                        'quantity' => $qty,
                        'name' => $details['name'],
                        'discount_price' => $details['discount_price'],
                        'mrp' => $details['mrp'],
                        'stock' => $details['stock'],
                        'manufacturer' => $details['manufacturer'],
                        'category' => $details['category']
                    ];
                }
            }
        }
    }

    // Calculate Subtotal
    foreach ($cartItems as $item) {
        $subtotal += (float)$item['discount_price'] * (int)$item['quantity'];
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Breadcrumb Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-success" id="cartTitle" data-testid="cart-title">My Shopping Cart</h2>
        <p class="text-muted">Review your selected items before proceeding to payment checkout</p>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5 glass-card border-0" id="emptyCartContainer" data-testid="empty-cart-container">
            <div class="mb-3"><i class="bi bi-cart-x text-muted" style="font-size: 5rem;"></i></div>
            <h4 class="fw-bold text-dark">Your Cart is Empty</h4>
            <p class="text-muted">Explore our medical services to add products and prescriptions.</p>
            <div class="d-flex justify-content-center gap-2 mt-3">
                <a href="medicines.php" class="btn btn-primary-custom" id="cartShopMeds" data-testid="cart-shop-meds">Buy Medicines</a>
                <a href="doctors.php" class="btn btn-secondary-custom" id="cartConsultDocs" data-testid="cart-consult-docs">Consult Doctor</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4" id="cartContentContainer" data-testid="cart-content-container">
            <!-- Cart Items List -->
            <div class="col-lg-8">
                <div class="card glass-card border-0 p-4" id="cartItemsCard" data-testid="cart-items-card">
                    <div class="list-group list-group-flush" id="cartList" data-testid="cart-list">
                        <?php foreach ($cartItems as $item): ?>
                            <?php 
                            $itemId = $item['item_id'];
                            $itemType = $item['item_type'];
                            $rowKey = "{$itemType}_{$itemId}";
                            ?>
                            <div class="list-group-item bg-transparent px-0 py-3 d-flex flex-wrap align-items-center justify-content-between border-bottom" data-testid="cart-row" id="cart-row-<?= $rowKey ?>">
                                <div class="d-flex align-items-center gap-3 col-12 col-sm-6 mb-2 mb-sm-0">
                                    <!-- Category image with fallback -->
                                    <?php
                                    $catClean = 'wellness';
                                    if (isset($item['category'])) {
                                        $c = strtolower($item['category']);
                                        if (in_array($c, ['otc', 'prescription', 'vitamins', 'supplements', 'devices', 'wellness'])) {
                                            $catClean = $c;
                                        }
                                    }
                                    ?>
                                    <img src="assets/images/categories/<?= $catClean ?>.png" class="img-fluid rounded border" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 70px; height: 60px; object-fit: contain;">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0" data-testid="cart-item-name"><?= htmlspecialchars($item['name']) ?></h6>
                                        <span class="text-muted small"><?= htmlspecialchars($item['manufacturer']) ?></span>
                                        <span class="badge bg-light text-dark border ms-1" style="font-size: 0.7rem; text-transform: capitalize;"><?= $itemType ?></span>
                                    </div>
                                </div>

                                <!-- Quantity adjuster -->
                                <div class="col-6 col-sm-3 d-flex align-items-center justify-content-start justify-content-sm-center">
                                    <div class="input-group input-group-sm" style="width: 100px;">
                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity('<?= $itemType ?>', <?= $itemId ?>, -1)" id="minusBtn-<?= $rowKey ?>" data-testid="qty-minus-btn">-</button>
                                        <input type="text" class="form-control text-center px-1" id="qtyInput-<?= $rowKey ?>" data-testid="qty-input" value="<?= $item['quantity'] ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity('<?= $itemType ?>', <?= $itemId ?>, 1)" id="plusBtn-<?= $rowKey ?>" data-testid="qty-plus-btn" <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>+</button>
                                    </div>
                                </div>

                                <!-- Price and Remove -->
                                <div class="col-6 col-sm-3 d-flex align-items-center justify-content-end gap-3">
                                    <div class="text-end">
                                        <h6 class="fw-bold text-success mb-0" id="rowTotal-<?= $rowKey ?>" data-testid="cart-row-total">₹<?= number_format($item['discount_price'] * $item['quantity'], 2) ?></h6>
                                        <span class="text-muted small">₹<?= number_format($item['discount_price'], 2) ?> each</span>
                                    </div>
                                    <button class="btn btn-outline-danger btn-sm" onclick="removeCartItem('<?= $itemType ?>', <?= $itemId ?>)" id="removeBtn-<?= $rowKey ?>" data-testid="remove-cart-item-btn" aria-label="Remove Item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Price Breakdown Summary -->
            <div class="col-lg-4">
                <div class="card glass-card border-0 p-4 border-top border-success border-4" id="cartSummaryCard" data-testid="cart-summary-card">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Order Price Summary</h5>

                    <!-- Coupon Code Input Box -->
                    <div class="mb-4">
                        <label for="couponInput" class="form-label small fw-bold text-muted" id="couponLabel" data-testid="coupon-label">Apply Promocode</label>
                        <div class="input-group">
                            <input type="text" class="form-control text-uppercase" placeholder="Enter coupon (e.g. SAVE10)" id="couponInput" data-testid="coupon-input" autocomplete="off">
                            <button class="btn btn-outline-success" type="button" id="applyCouponBtn" data-testid="apply-coupon-btn" onclick="applyPromoCoupon()">Apply</button>
                        </div>
                        <div class="text-success small fw-semibold mt-2 d-none" id="couponSuccessMessage" data-testid="coupon-success-msg"></div>
                        <div class="text-danger small fw-semibold mt-2 d-none" id="couponErrorMessage" data-testid="coupon-error-msg"></div>
                    </div>

                    <!-- Price breakdown list -->
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal Items Total:</span>
                        <strong class="text-dark" id="summarySubtotal" data-testid="summary-subtotal">₹<?= number_format($subtotal, 2) ?></strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Promo Coupon Discount:</span>
                        <strong class="text-danger" id="summaryDiscount" data-testid="summary-discount">-₹0.00</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                        <span class="text-muted">Home Collection / Shipping:</span>
                        <?php 
                        $shipping = ($subtotal >= 1000 || $subtotal == 0) ? 0.00 : 50.00; 
                        ?>
                        <strong class="text-dark" id="summaryShipping" data-testid="summary-shipping"><?= $shipping > 0 ? '₹' . number_format($shipping, 2) : 'FREE' ?></strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-baseline mb-4">
                        <span class="fw-bold text-dark fs-5">Net Payable Amount:</span>
                        <strong class="fs-4 text-success" id="summaryTotal" data-testid="summary-total">₹<?= number_format($subtotal + $shipping, 2) ?></strong>
                    </div>

                    <!-- Checkout Button -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="checkout.php" class="btn btn-primary-custom w-100 py-2 fs-6 d-flex align-items-center justify-content-center gap-2" id="checkoutBtn" data-testid="checkout-btn">
                            Proceed to Checkout <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php?redirect=checkout.php" class="btn btn-primary-custom w-100 py-2 fs-6 d-flex align-items-center justify-content-center gap-2" id="checkoutBtn" data-testid="checkout-btn">
                            Login to Checkout <i class="bi bi-box-arrow-in-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
let cartSubtotal = <?= $subtotal ?>;
let activeCoupon = null;

// Adjust Quantity AJAX
function changeQuantity(itemType, itemId, delta) {
    const rowKey = `${itemType}_${itemId}`;
    const input = document.getElementById(`qtyInput-${rowKey}`);
    const currentQty = parseInt(input.value);
    const newQty = currentQty + delta;

    if (newQty <= 0) return; // Cannot go lower than 1

    showLoader();
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update',
            item_id: itemId,
            item_type: itemType,
            quantity: newQty
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        if (data.success) {
            window.location.reload(); // Quick refresh to update all totals properly
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(err => {
        hideLoader();
        console.error(err);
        showToast('Failed to update cart.', 'danger');
    });
}

// Remove Item AJAX
function removeCartItem(itemType, itemId) {
    showLoader();
    fetch('api/cart.php', {
        method: 'DELETE',
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
            // Remove row
            const rowKey = `${itemType}_${itemId}`;
            const row = document.getElementById(`cart-row-${rowKey}`);
            if (row) row.remove();
            
            // Check if cart is now empty
            const rows = document.querySelectorAll('[data-testid="cart-row"]');
            if (rows.length === 0) {
                window.location.reload();
            } else {
                window.location.reload(); // Reload to refresh pricing structures
            }
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(err => {
        hideLoader();
        console.error(err);
        showToast('Failed to remove cart item.', 'danger');
    });
}

// Apply Promo Coupon AJAX
function applyPromoCoupon() {
    const input = document.getElementById('couponInput');
    const successMsg = document.getElementById('couponSuccessMessage');
    const errorMsg = document.getElementById('couponErrorMessage');
    const code = input.value.trim().toUpperCase();

    successMsg.classList.add('d-none');
    errorMsg.classList.add('d-none');

    if (code === '') {
        errorMsg.innerText = 'Please enter a coupon code.';
        errorMsg.classList.remove('d-none');
        return;
    }

    showLoader();
    fetch(`api/coupon.php?code=${encodeURIComponent(code)}&cart_value=${cartSubtotal}`)
    .then(res => res.json())
    .then(data => {
        hideLoader();
        if (data.success) {
            activeCoupon = data.coupon;
            
            // Calculate discount
            let discountAmt = 0;
            if (activeCoupon.discount_type === 'percentage') {
                discountAmt = cartSubtotal * (activeCoupon.discount_value / 100);
            } else {
                discountAmt = activeCoupon.discount_value;
            }
            discountAmt = Math.min(discountAmt, cartSubtotal);

            // Update DOM
            document.getElementById('summaryDiscount').innerText = `-₹${discountAmt.toFixed(2)}`;
            
            // Re-calculate Net Total
            const shippingVal = (cartSubtotal >= 1000) ? 0.00 : 50.00;
            const netPayable = cartSubtotal - discountAmt + shippingVal;
            document.getElementById('summaryTotal').innerText = `₹${netPayable.toFixed(2)}`;

            successMsg.innerText = `${data.message} Discount applied: ₹${discountAmt.toFixed(2)}`;
            successMsg.classList.remove('d-none');
            showToast(data.message, 'success');

            // Save coupon details in session storage for checkout use
            sessionStorage.setItem('applied_coupon_code', activeCoupon.code);
            sessionStorage.setItem('applied_coupon_discount', discountAmt.toFixed(2));
        } else {
            errorMsg.innerText = data.message;
            errorMsg.classList.remove('d-none');
            showToast(data.message, 'danger');
            
            // Clean storage
            sessionStorage.removeItem('applied_coupon_code');
            sessionStorage.removeItem('applied_coupon_discount');
        }
    })
    .catch(err => {
        hideLoader();
        console.error(err);
        showToast('Error validating coupon.', 'danger');
    });
}
</script>

<?php
require_once 'includes/footer.php';
?>
