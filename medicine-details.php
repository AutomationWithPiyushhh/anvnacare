<?php
require_once 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: medicines.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
    $stmt->execute([$id]);
    $med = $stmt->fetch();

    if (!$med) {
        // Redirect to 404
        header("Location: 404.php");
        exit;
    }
    
    $discountPercentage = round((($med['mrp'] - $med['discount_price']) / $med['mrp']) * 100);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Back Navigation -->
    <div class="mb-3">
        <a href="medicines.php" class="text-success text-decoration-none fw-semibold" id="backToMeds" data-testid="back-to-meds-link">
            <i class="bi bi-arrow-left"></i> Back to Pharmacy
        </a>
    </div>

    <!-- Product Details Grid -->
    <div class="row g-5">
        <!-- Image Area -->
        <div class="col-md-5">
            <div class="card glass-card p-4 text-center border-0" id="detailsImageCard" data-testid="details-image-card">
                <span class="badge bg-danger position-absolute top-0 start-0 m-3 fs-6" data-testid="discount-tag"><?= $discountPercentage ?>% OFF</span>
                <img src="https://placehold.co/400x320/eef7f2/0a6c42?text=<?= urlencode($med['name']) ?>" class="img-fluid rounded details-image mx-auto" alt="<?= htmlspecialchars($med['name']) ?>" id="medDetailsImg" data-testid="med-details-img">
            </div>
        </div>

        <!-- Details Area -->
        <div class="col-md-7">
            <div id="detailsTextContainer" data-testid="details-text-container">
                <span class="badge bg-light text-success border mb-2 fs-6" id="detailsCategory" data-testid="details-category"><?= $med['category'] ?></span>
                <h2 class="fw-bold text-dark mb-1" id="medDetailsName" data-testid="med-details-name"><?= htmlspecialchars($med['name']) ?></h2>
                <p class="text-muted mb-3" id="medDetailsManufacturer" data-testid="med-details-manufacturer">Manufacturer: <strong><?= htmlspecialchars($med['manufacturer']) ?></strong></p>

                <!-- Rating -->
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-success fs-6 me-2" id="medDetailsRating" data-testid="med-details-rating"><i class="bi bi-star-fill"></i> <?= $med['rating'] ?></span>
                    <span class="text-muted small">| Checked and Verified Product</span>
                </div>

                <!-- Price Card -->
                <div class="card glass-card p-3 border-0 bg-success-subtle mb-4" id="detailsPriceCard" data-testid="details-price-card">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fs-2 fw-bold text-success" id="medDetailsPrice" data-testid="med-details-price">₹<?= number_format($med['discount_price'], 2) ?></span>
                        <span class="text-decoration-line-through text-muted">MRP ₹<?= number_format($med['mrp'], 2) ?></span>
                    </div>
                    <span class="text-danger small fw-semibold mt-1">Inclusive of all taxes</span>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-2">Product Description</h5>
                    <p class="text-secondary" id="medDetailsDesc" data-testid="med-details-desc"><?= htmlspecialchars($med['description']) ?></p>
                </div>

                <!-- Quantity and Cart Action -->
                <div class="row align-items-center g-3 mb-4">
                    <div class="col-auto">
                        <label for="quantityInput" class="fw-bold text-dark me-2">Qty:</label>
                        <div class="input-group" style="width: 130px;">
                            <button class="btn btn-outline-secondary" type="button" id="qtyMinusBtn" data-testid="qty-minus-btn" onclick="adjustQty(-1)"><i class="bi bi-dash"></i></button>
                            <input type="number" class="form-control text-center" id="quantityInput" name="quantity" value="1" min="1" max="<?= $med['stock'] ?>" data-testid="quantity-input" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="qtyPlusBtn" data-testid="qty-plus-btn" onclick="adjustQty(1)"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>

                    <div class="col">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary-custom py-2 px-4 flex-grow-1" id="addToCartBtn" data-testid="add-to-cart-btn" onclick="triggerAddToCart()" <?= $med['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-cart-plus me-2"></i> <?= $med['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                            </button>
                            <button class="btn btn-outline-danger py-2" id="addToWishlistBtn" data-testid="add-to-wishlist-btn" onclick="addToWishlist(<?= $med['id'] ?>, 'medicine')" aria-label="Add to Wishlist">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stock Alert Status -->
                <div class="mb-4">
                    <span class="text-muted small">Availability Status: </span>
                    <?php if ($med['stock'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border fw-bold" id="stockStatus" data-testid="stock-status">In Stock (<?= $med['stock'] ?> left)</span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border fw-bold" id="stockStatus" data-testid="stock-status">Out of Stock</span>
                    <?php endif; ?>
                </div>

                <!-- QA CHALLENGE: Prescription File Uploader -->
                <?php if ($med['category'] === 'Prescription'): ?>
                    <div class="card border border-warning p-4 bg-warning-subtle mb-4" id="prescriptionSection" data-testid="prescription-section" style="border-radius: var(--border-radius)">
                        <h5 class="fw-bold text-dark mb-2"><i class="bi bi-file-earmark-medical-fill text-warning me-2"></i> Prescription Required</h5>
                        <p class="text-muted small mb-3">This medicine requires a valid doctor's prescription. Please upload a clear image or PDF document to proceed.</p>
                        
                        <!-- Drag and Drop uploader zone -->
                        <div class="drag-drop-zone mb-3" id="prescriptionDropZone" data-testid="prescription-dropzone" onclick="triggerFileSelect()">
                            <i class="bi bi-cloud-upload-fill text-success fs-1 mb-2"></i>
                            <h6 class="fw-bold mb-1">Drag & Drop prescription file here</h6>
                            <span class="text-muted small">Supports JPG, PNG, PDF (Max 5MB)</span>
                            <input type="file" id="prescriptionFileInput" name="prescription" class="d-none" data-testid="prescription-file-input" onchange="handleFileSelect(this)">
                        </div>

                        <!-- Selected File indicator and Upload Progress -->
                        <div id="uploadStatusContainer" class="d-none" data-testid="upload-status-container">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark text-truncate small" id="uploadedFileName" data-testid="uploaded-file-name">file.jpg</span>
                                <span class="badge bg-success small" id="uploadPercent" data-testid="upload-percent">0%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" id="uploadProgressBar" data-testid="upload-progress-bar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-success small fw-semibold mt-2 d-none" id="uploadSuccessAlert" data-testid="upload-success-alert">
                                <i class="bi bi-check-circle-fill me-1"></i> Prescription verified successfully! You can now checkout.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
// Adjustment of Qty
function adjustQty(amount) {
    const qtyInput = document.getElementById('quantityInput');
    let val = parseInt(qtyInput.value) + amount;
    const min = parseInt(qtyInput.min);
    const max = parseInt(qtyInput.max);

    if (val >= min && val <= max) {
        qtyInput.value = val;
    }
}

// Add to Cart
function triggerAddToCart() {
    const qty = parseInt(document.getElementById('quantityInput').value);
    addToCart(<?= $med['id'] ?>, 'medicine', qty);
}

// Prescription Upload simulation
function triggerFileSelect() {
    const fileInput = document.getElementById('prescriptionFileInput');
    if (fileInput) fileInput.click();
}

function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        simulateUpload(file.name);
    }
}

// Drag & drop handlers
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('prescriptionDropZone');
    
    if (dropZone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropZone.classList.remove('dragover');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                simulateUpload(files[0].name);
            }
        });
    }
});

function simulateUpload(filename) {
    const statusContainer = document.getElementById('uploadStatusContainer');
    const nameLabel = document.getElementById('uploadedFileName');
    const percentLabel = document.getElementById('uploadPercent');
    const progressBar = document.getElementById('uploadProgressBar');
    const successAlert = document.getElementById('uploadSuccessAlert');

    if (!statusContainer) return;

    statusContainer.classList.remove('d-none');
    successAlert.classList.add('d-none');
    nameLabel.innerText = filename;
    progressBar.style.width = '0%';
    percentLabel.innerText = '0%';

    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        progressBar.style.width = progress + '%';
        percentLabel.innerText = progress + '%';

        if (progress >= 100) {
            clearInterval(interval);
            successAlert.classList.remove('d-none');
            showToast('Prescription uploaded successfully.', 'success');
        }
    }, 150); // Takes 1.5 seconds in total
}
</script>

<?php
require_once 'includes/footer.php';
?>
