<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

require_once 'includes/header.php';
$userId = $_SESSION['user_id'];

try {
    // Fetch Saved Addresses
    $addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $addrStmt->execute([$userId]);
    $addresses = $addrStmt->fetchAll();

    // Fetch Cart Items
    $cartStmt = $pdo->prepare("SELECT c.item_type, c.item_id, c.quantity, m.name, m.discount_price, m.mrp, m.stock
        FROM cart c
        JOIN medicines m ON c.item_id = m.id AND c.item_type = 'medicine'
        WHERE c.user_id = ?
        UNION
        SELECT c.item_type, c.item_id, c.quantity, p.name, p.discount_price, p.mrp, p.stock
        FROM cart c
        JOIN products p ON c.item_id = p.id AND c.item_type = 'product'
        WHERE c.user_id = ?
        UNION
        SELECT c.item_type, c.item_id, c.quantity, t.name, t.discount_price, t.mrp, 999 as stock
        FROM cart c
        JOIN tests t ON c.item_id = t.id AND c.item_type = 'test'
        WHERE c.user_id = ?");
    $cartStmt->execute([$userId, $userId, $userId]);
    $cartItems = $cartStmt->fetchAll();

    if (empty($cartItems)) {
        header("Location: cart.php");
        exit;
    }

    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += (float)$item['discount_price'] * (int)$item['quantity'];
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Wizard Progress Line -->
    <div class="card glass-card border-0 p-4 mb-4" id="checkoutWizardCard" data-testid="checkout-wizard-card">
        <h4 class="fw-bold mb-4 text-center">Complete Your Purchase</h4>
        <div class="wizard-steps mx-auto" style="max-width: 600px;" data-testid="wizard-steps">
            <div class="wizard-step-line-active" id="wizardLine" style="width: 0%;"></div>
            <div class="wizard-step active" id="step1Indicator" data-testid="step1-indicator">1</div>
            <div class="wizard-step" id="step2Indicator" data-testid="step2-indicator">2</div>
            <div class="wizard-step" id="step3Indicator" data-testid="step3-indicator">3</div>
            <div class="wizard-step" id="step4Indicator" data-testid="step4-indicator">4</div>
            <div class="wizard-step" id="step5Indicator" data-testid="step5-indicator">5</div>
        </div>
        <div class="d-flex justify-content-between mx-auto mt-2 text-muted small" style="max-width: 630px; width: 100%;">
            <span>Address</span>
            <span>Delivery</span>
            <span>Review</span>
            <span>Payment</span>
            <span>Success</span>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
            <div class="card glass-card border-0 p-4" style="min-height: 400px;" id="checkoutStepsCard" data-testid="checkout-steps-card">
                
                <!-- STEP 1: Address Selection -->
                <div id="step1Container" data-testid="step1-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0 text-success"><i class="bi bi-geo-alt me-2"></i> Select Shipping Address</h4>
                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal" id="addNewAddressBtn" data-testid="add-new-address-btn">
                            <i class="bi bi-plus-lg"></i> Add New Address
                        </button>
                    </div>

                    <div id="addressList" class="row g-3 mb-4" data-testid="address-list">
                        <?php if (empty($addresses)): ?>
                            <div class="col-12 text-center py-4 text-muted" id="noAddressText">
                                <p>No saved addresses found. Please add a new delivery address.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($addresses as $addr): ?>
                                <div class="col-md-6" id="address-card-<?= $addr['id'] ?>" data-testid="address-card">
                                    <div class="border p-3 rounded h-100 cursor-pointer address-box <?= $addr['is_default'] ? 'border-success bg-success-subtle' : '' ?>" onclick="selectAddress(<?= $addr['id'] ?>)" style="position: relative;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="selectedAddress" id="addrRadio-<?= $addr['id'] ?>" value="<?= $addr['id'] ?>" <?= $addr['is_default'] ? 'checked' : '' ?> data-testid="address-radio">
                                            <label class="form-check-label fw-bold text-dark" for="addrRadio-<?= $addr['id'] ?>">
                                                <?= htmlspecialchars($addr['name']) ?>
                                            </label>
                                        </div>
                                        <div class="small text-muted mt-2 ps-4">
                                            <?= htmlspecialchars($addr['address_line1']) ?>, <?= htmlspecialchars($addr['address_line2']) ?><br>
                                            <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> - <?= htmlspecialchars($addr['pincode']) ?><br>
                                            Phone: <?= htmlspecialchars($addr['phone']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-primary-custom float-end mt-3 px-5" id="nextToDeliveryBtn" data-testid="next-delivery-btn" onclick="goToStep2()" <?= empty($addresses) ? 'disabled' : '' ?>>
                        Next: Delivery Method <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                <!-- STEP 2: Delivery Method -->
                <div id="step2Container" class="d-none" data-testid="step2-container">
                    <h4 class="fw-bold mb-4 text-success"><i class="bi bi-truck me-2"></i> Choose Delivery Option</h4>
                    
                    <div class="list-group mb-4" data-testid="delivery-list">
                        <label class="list-group-item d-flex gap-3 py-3 border rounded mb-3 cursor-pointer" style="cursor: pointer;">
                            <input class="form-check-input flex-shrink-0" type="radio" name="deliveryMethod" id="deliveryStandard" value="Standard" checked data-testid="delivery-standard-radio" onchange="updateDeliveryCharge(50.00)">
                            <span>
                                <strong class="d-block text-dark">Standard Home Delivery (₹50.00)</strong>
                                <small class="text-muted">Takes 3-5 business days. Safe, contactless shipping.</small>
                            </span>
                        </label>
                        <label class="list-group-item d-flex gap-3 py-3 border rounded cursor-pointer" style="cursor: pointer;">
                            <input class="form-check-input flex-shrink-0" type="radio" name="deliveryMethod" id="deliveryExpress" value="Express" data-testid="delivery-express-radio" onchange="updateDeliveryCharge(150.00)">
                            <span>
                                <strong class="d-block text-dark">Express 24-Hour Shipping (₹150.00)</strong>
                                <small class="text-muted">Guaranteed delivery within 24 hours. Priority packing.</small>
                            </span>
                        </label>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-outline-secondary px-4" onclick="backToStep1()" id="backToAddressBtn" data-testid="back-address-btn"><i class="bi bi-arrow-left"></i> Back</button>
                        <button class="btn btn-primary-custom px-5" onclick="goToStep3()" id="nextToReviewBtn" data-testid="next-review-btn">Next: Review Order <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- STEP 3: Review Order -->
                <div id="step3Container" class="d-none" data-testid="step3-container">
                    <h4 class="fw-bold mb-4 text-success"><i class="bi bi-clipboard2-check me-2"></i> Review Your Order</h4>

                    <div class="table-responsive mb-4">
                        <table class="table align-middle" data-testid="review-items-table">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Product Name</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <td>
                                            <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                        </td>
                                        <td class="text-center fw-semibold"><?= $item['quantity'] ?></td>
                                        <td class="text-end fw-bold text-success">₹<?= number_format($item['discount_price'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-outline-secondary px-4" onclick="backToStep2()" id="backToDeliveryBtn" data-testid="back-delivery-btn"><i class="bi bi-arrow-left"></i> Back</button>
                        <button class="btn btn-primary-custom px-5" onclick="goToStep4()" id="nextToPaymentBtn" data-testid="next-payment-btn">Next: Payment <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- STEP 4: Payment -->
                <div id="step4Container" class="d-none" data-testid="step4-container">
                    <h4 class="fw-bold mb-3 text-success"><i class="bi bi-credit-card me-2"></i> Payment Details</h4>
                    <p class="text-muted small mb-4">Submit your test payment details below. <strong>No actual amount is charged.</strong></p>

                    <!-- Dummy credentials helper -->
                    <div class="alert bg-success-subtle text-success small mb-4 p-3" id="paymentTestCreds" data-testid="payment-test-creds">
                        <i class="bi bi-info-circle-fill me-1"></i> Use following test details:<br>
                        Card: <strong>4111 1111 1111 1111</strong> | Expiry: <strong>12/30</strong> | CVV: <strong>123</strong> | OTP: <strong>123456</strong>
                    </div>

                    <!-- Card Form sub-stage -->
                    <div id="cardFields" data-testid="card-fields-container">
                        <form id="paymentCardForm" novalidate>
                            <div class="mb-3">
                                <label for="cardNumber" class="form-label fw-bold" data-testid="card-num-label">Card Number</label>
                                <input type="text" class="form-control text-center fs-5" id="cardNumber" placeholder="4111 1111 1111 1111" data-testid="card-number-input" required maxlength="19">
                                <div class="invalid-feedback">Please enter valid card: 4111111111111111.</div>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <label for="cardExpiry" class="form-label fw-bold" data-testid="card-expiry-label">Expiry Date</label>
                                    <input type="text" class="form-control text-center" id="cardExpiry" placeholder="12/30" data-testid="card-expiry-input" required maxlength="5">
                                    <div class="invalid-feedback">Enter expiry: 12/30.</div>
                                </div>
                                <div class="col-6">
                                    <label for="cardCvv" class="form-label fw-bold" data-testid="card-cvv-label">CVV</label>
                                    <input type="password" class="form-control text-center" id="cardCvv" placeholder="123" data-testid="card-cvv-input" required maxlength="3">
                                    <div class="invalid-feedback">Enter CVV: 123.</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-outline-secondary px-4" onclick="backToStep3()" id="backToReviewBtn" data-testid="back-review-btn"><i class="bi bi-arrow-left"></i> Back</button>
                                <button type="submit" class="btn btn-primary-custom px-5" id="payNowBtn" data-testid="pay-now-btn">Pay Now</button>
                            </div>
                        </form>
                    </div>

                    <!-- OTP sub-stage (hidden initially) -->
                    <div id="otpFields" class="d-none" data-testid="otp-fields-container">
                        <form id="paymentOtpForm" novalidate>
                            <div class="mb-4 text-center">
                                <label for="otpInput" class="form-label fw-bold d-block text-muted">Enter One-Time Password (OTP)</label>
                                <p class="small text-muted">Enter the 6-digit OTP code sent to your mock mobile number</p>
                                <input type="text" class="form-control text-center fs-4 mx-auto" id="otpInput" placeholder="123456" data-testid="otp-input" required maxlength="6" style="max-width: 200px; letter-spacing: 5px;">
                                <div class="invalid-feedback">Invalid OTP. Enter 123456.</div>
                            </div>

                            <div class="d-flex justify-content-center gap-3">
                                <button type="submit" class="btn btn-success px-5 py-2 fw-bold" id="confirmPaymentBtn" data-testid="confirm-payment-btn">
                                    <span class="spinner-border spinner-border-sm d-none" id="paymentSpinner" role="status" aria-hidden="true"></span>
                                    <span>Verify & Complete Order</span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- STEP 5: Success Summary -->
                <div id="step5Container" class="d-none text-center py-5" data-testid="step5-container">
                    <div class="mb-4 text-success" style="font-size: 5rem;" id="successIcon" data-testid="success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h2 class="fw-bold text-success mb-2" id="successHeader" data-testid="success-header">Order Placed Successfully!</h2>
                    <p class="text-muted mb-4">Your order has been logged. We will notify you once dispatched.</p>

                    <div class="card bg-light border-0 p-4 mb-4 mx-auto" style="max-width: 400px;" id="successSummaryBox" data-testid="success-summary-box">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Order Number:</span>
                            <strong class="text-success" id="successOrderNumber" data-testid="order-number">ORD-0000</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Amount Paid:</span>
                            <strong class="text-dark" id="successOrderAmount" data-testid="order-amount">₹0.00</strong>
                        </div>
                    </div>

                    <a href="orders.php" class="btn btn-primary-custom px-5" id="successOrdersBtn" data-testid="success-orders-btn">Go to My Orders</a>
                </div>

            </div>
        </div>

        <!-- Checkout Summary Sidebar (right panel) -->
        <div class="col-lg-4" id="checkoutSummaryPanel" data-testid="checkout-summary-panel">
            <div class="card glass-card border-0 p-4 border-top border-success border-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-4 border-bottom pb-2">Order Pricing Breakdown</h5>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Cart Subtotal:</span>
                    <strong class="text-dark" id="checkoutSubtotal">₹<?= number_format($subtotal, 2) ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Promo Coupon Discount:</span>
                    <strong class="text-danger" id="checkoutDiscount">-₹0.00</strong>
                </div>

                <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                    <span class="text-muted">Delivery Charges:</span>
                    <?php $initialShipping = ($subtotal >= 1000) ? 0.00 : 50.00; ?>
                    <strong class="text-dark" id="checkoutShipping">₹<?= number_format($initialShipping, 2) ?></strong>
                </div>

                <div class="d-flex justify-content-between align-items-baseline mb-4">
                    <span class="fw-bold text-dark">Total Payable Amount:</span>
                    <strong class="fs-4 text-success" id="checkoutNetTotal">₹<?= number_format($subtotal + $initialShipping, 2) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dialog - Add New Address (QA Modal Challenge) -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true" data-testid="add-address-modal">
    <div class="modal-dialog">
        <div class="modal-content glass-card border-0 p-3">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-success" id="addAddressModalLabel">Add Delivery Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeModalBtn" data-testid="close-modal-btn"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="modalErrorAlert" data-testid="modal-error-alert"></div>
                <form id="addressForm" data-testid="address-form" novalidate>
                    <div class="mb-3">
                        <label for="modalAddressName" class="form-label small fw-bold">Receiver Name</label>
                        <input type="text" class="form-control" id="modalAddressName" name="name" required data-testid="modal-name-input">
                    </div>
                    <div class="mb-3">
                        <label for="modalAddressPhone" class="form-label small fw-bold">Contact Phone</label>
                        <input type="text" class="form-control" id="modalAddressPhone" name="phone" required data-testid="modal-phone-input" pattern="[0-9]{10}">
                    </div>
                    <div class="mb-3">
                        <label for="modalAddressLine1" class="form-label small fw-bold">Address Line 1</label>
                        <input type="text" class="form-control" id="modalAddressLine1" name="address_line1" required data-testid="modal-line1-input">
                    </div>
                    <div class="mb-3">
                        <label for="modalAddressLine2" class="form-label small fw-bold">Address Line 2 (Optional)</label>
                        <input type="text" class="form-control" id="modalAddressLine2" name="address_line2" data-testid="modal-line2-input">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="modalAddressCity" class="form-label small fw-bold">City</label>
                            <input type="text" class="form-control" id="modalAddressCity" name="city" required data-testid="modal-city-input">
                        </div>
                        <div class="col-6">
                            <label for="modalAddressState" class="form-label small fw-bold">State</label>
                            <input type="text" class="form-control" id="modalAddressState" name="state" required data-testid="modal-state-input">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="modalAddressPincode" class="form-label small fw-bold">Pincode (6-Digit)</label>
                        <input type="text" class="form-control" id="modalAddressPincode" name="pincode" required data-testid="modal-pincode-input" pattern="[0-9]{6}">
                    </div>
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="modalAddressDefault" name="is_default" data-testid="modal-default-checkbox">
                        <label class="form-check-label text-muted small" for="modalAddressDefault">Set as default shipping address</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold" id="saveAddressBtn" data-testid="save-address-btn">Save Delivery Address</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let selectedAddressId = <?= count($addresses) > 0 ? (int)$addresses[0]['id'] : 0 ?>;
let cartSubtotal = <?= $subtotal ?>;
let deliveryCharge = <?= $initialShipping ?>;
let discountAmt = 0.00;
let appliedCouponCode = '';

// Check Session storage for coupon discount applied on cart page
document.addEventListener('DOMContentLoaded', function () {
    const savedCoupon = sessionStorage.getItem('applied_coupon_code');
    const savedDiscount = sessionStorage.getItem('applied_coupon_discount');
    
    if (savedCoupon && savedDiscount) {
        appliedCouponCode = savedCoupon;
        discountAmt = parseFloat(savedDiscount);
        
        document.getElementById('checkoutDiscount').innerText = `-₹${discountAmt.toFixed(2)}`;
        recalculateTotals();
    }
});

// Update delivery charges dynamically based on radios
function updateDeliveryCharge(value) {
    deliveryCharge = value;
    document.getElementById('checkoutShipping').innerText = `₹${deliveryCharge.toFixed(2)}`;
    recalculateTotals();
}

// Re-calculate pricing panels
function recalculateTotals() {
    const netAmount = cartSubtotal - discountAmt + deliveryCharge;
    document.getElementById('checkoutNetTotal').innerText = `₹${netAmount.toFixed(2)}`;
}

// Handle Address Select Highlight
function selectAddress(addrId) {
    selectedAddressId = addrId;
    document.querySelectorAll('.address-box').forEach(box => {
        box.classList.remove('border-success', 'bg-success-subtle');
    });
    const selectedBox = document.getElementById(`address-card-${addrId}`).querySelector('.address-box');
    selectedBox.classList.add('border-success', 'bg-success-subtle');
    
    const radio = document.getElementById(`addrRadio-${addrId}`);
    radio.checked = true;
}

// Navigation Step Controls
function updateWizardLine(percentage) {
    document.getElementById('wizardLine').style.width = percentage + '%';
}

function goToStep2() {
    if (!selectedAddressId) {
        showToast('Please select a delivery address.', 'danger');
        return;
    }
    
    // UI steps toggle
    document.getElementById('step1Container').classList.add('d-none');
    document.getElementById('step2Container').classList.remove('d-none');
    
    document.getElementById('step2Indicator').classList.add('active');
    updateWizardLine(25);
    currentStep = 2;
}

function backToStep1() {
    document.getElementById('step2Container').classList.add('d-none');
    document.getElementById('step1Container').classList.remove('d-none');
    
    document.getElementById('step2Indicator').classList.remove('active');
    updateWizardLine(0);
    currentStep = 1;
}

function goToStep3() {
    document.getElementById('step2Container').classList.add('d-none');
    document.getElementById('step3Container').classList.remove('d-none');
    
    document.getElementById('step2Indicator').classList.add('completed');
    document.getElementById('step3Indicator').classList.add('active');
    updateWizardLine(50);
    currentStep = 3;
}

function backToStep2() {
    document.getElementById('step3Container').classList.add('d-none');
    document.getElementById('step2Container').classList.remove('d-none');
    
    document.getElementById('step2Indicator').classList.remove('completed');
    document.getElementById('step3Indicator').classList.remove('active');
    updateWizardLine(25);
    currentStep = 2;
}

function goToStep4() {
    document.getElementById('step3Container').classList.add('d-none');
    document.getElementById('step4Container').classList.remove('d-none');
    
    document.getElementById('step3Indicator').classList.add('completed');
    document.getElementById('step4Indicator').classList.add('active');
    updateWizardLine(75);
    currentStep = 4;
}

function backToStep3() {
    document.getElementById('step4Container').classList.add('d-none');
    document.getElementById('step3Container').classList.remove('d-none');
    
    document.getElementById('step3Indicator').classList.remove('completed');
    document.getElementById('step4Indicator').classList.remove('active');
    updateWizardLine(50);
    currentStep = 3;
}

// STEP 4 Card form validation
document.addEventListener('DOMContentLoaded', function () {
    const cardForm = document.getElementById('paymentCardForm');
    const cardNum = document.getElementById('cardNumber');
    const cardExp = document.getElementById('cardExpiry');
    const cardCvv = document.getElementById('cardCvv');

    cardForm.addEventListener('submit', function (e) {
        e.preventDefault();
        
        let isValid = true;
        
        // Match strict card requirement values
        if (cardNum.value.replace(/\s/g, '') !== '4111111111111111') {
            cardNum.classList.add('is-invalid');
            isValid = false;
        } else {
            cardNum.classList.remove('is-invalid');
        }

        if (cardExp.value.trim() !== '12/30') {
            cardExp.classList.add('is-invalid');
            isValid = false;
        } else {
            cardExp.classList.remove('is-invalid');
        }

        if (cardCvv.value.trim() !== '123') {
            cardCvv.classList.add('is-invalid');
            isValid = false;
        } else {
            cardCvv.classList.remove('is-invalid');
        }

        if (!isValid) return;

        // If card details correct, move to OTP sub-stage
        document.getElementById('cardFields').classList.add('d-none');
        document.getElementById('otpFields').classList.remove('d-none');
        showToast('Mock card verified. Please enter OTP sent to mobile.', 'info');
    });

    // STEP 4 OTP Form validation and final placing order
    const otpForm = document.getElementById('paymentOtpForm');
    const otpInput = document.getElementById('otpInput');
    const payBtn = document.getElementById('confirmPaymentBtn');
    const spinner = document.getElementById('paymentSpinner');

    otpForm.addEventListener('submit', function (e) {
        e.preventDefault();

        if (otpInput.value.trim() !== '123456') {
            otpInput.classList.add('is-invalid');
            return;
        }
        otpInput.classList.remove('is-invalid');

        // Verify and place order
        payBtn.disabled = true;
        spinner.classList.remove('d-none');

        const deliveryMethod = document.querySelector('input[name="deliveryMethod"]:checked').value;

        // Simulate a payment loader delay (1.2 seconds)
        setTimeout(() => {
            fetch('api/order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    address_id: selectedAddressId,
                    payment_method: 'Card',
                    delivery_method: deliveryMethod,
                    coupon_code: appliedCouponCode
                })
            })
            .then(response => response.json())
            .then(data => {
                payBtn.disabled = false;
                spinner.classList.add('d-none');

                if (data.success) {
                    // Update header cart badge
                    const badge = document.getElementById('navCartCount');
                    if (badge) badge.innerText = '0';
                    
                    // Clear Session variables
                    sessionStorage.removeItem('applied_coupon_code');
                    sessionStorage.removeItem('applied_coupon_discount');

                    // Show step 5
                    document.getElementById('step4Container').classList.add('d-none');
                    document.getElementById('checkoutSummaryPanel').classList.add('d-none');
                    document.getElementById('step5Container').classList.remove('d-none');

                    document.getElementById('step4Indicator').classList.add('completed');
                    document.getElementById('step5Indicator').classList.add('completed', 'active');
                    updateWizardLine(100);

                    // Render summary values
                    document.getElementById('successOrderNumber').innerText = data.order_number;
                    document.getElementById('successOrderAmount').innerText = '₹' + parseFloat(data.net_amount).toFixed(2);
                    showToast('Payment verified. Order completed successfully!', 'success');
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(err => {
                payBtn.disabled = false;
                spinner.classList.add('d-none');
                console.error(err);
                showToast('Failed to complete order. Server error.', 'danger');
            });
        }, 1200);
    });
});

// Modal Address submit AJAX
document.addEventListener('DOMContentLoaded', function () {
    const addrForm = document.getElementById('addressForm');
    const modalError = document.getElementById('modalErrorAlert');

    addrForm.addEventListener('submit', function (e) {
        e.preventDefault();
        modalError.classList.add('d-none');

        const name = document.getElementById('modalAddressName').value.trim();
        const phone = document.getElementById('modalAddressPhone').value.trim();
        const line1 = document.getElementById('modalAddressLine1').value.trim();
        const line2 = document.getElementById('modalAddressLine2').value.trim();
        const city = document.getElementById('modalAddressCity').value.trim();
        const state = document.getElementById('modalAddressState').value.trim();
        const pincode = document.getElementById('modalAddressPincode').value.trim();
        const isDefault = document.getElementById('modalAddressDefault').checked;

        // Perform AJAX save
        showLoader();
        fetch('api/address.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                phone: phone,
                address_line1: line1,
                address_line2: line2,
                city: city,
                state: state,
                pincode: pincode,
                is_default: isDefault
            })
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to save address.');
                }
                return data;
            });
        })
        .then(data => {
            hideLoader();
            showToast('New delivery address added successfully.', 'success');
            
            // Hide bootstrap modal
            const modalEl = document.getElementById('addAddressModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            // Reload page to refresh address radios
            window.location.reload();
        })
        .catch(err => {
            hideLoader();
            modalError.innerText = err.message;
            modalError.classList.remove('d-none');
            showToast(err.message, 'danger');
        });
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>
