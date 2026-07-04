<?php
require_once 'includes/header.php';
?>

<div class="container my-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card glass-card border-0 p-5 shadow-sm" id="error500Card" data-testid="error-500-card">
                <div class="fs-1 text-danger mb-3" id="errorIcon"><i class="bi bi-gear-fill"></i></div>
                <h1 class="display-4 fw-bold text-dark mb-2">500</h1>
                <h3 class="fw-bold mb-3 text-success" id="errorHeader" data-testid="error-header">Internal Server Error</h3>
                <p class="text-muted mb-4">We encountered an internal database query mismatch or Apache server configuration issue. Please refresh or try again later.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-success py-2" onclick="window.location.reload()" id="refreshBtn" data-testid="refresh-btn">Refresh Page</button>
                    <a href="index.php" class="btn btn-primary-custom py-2" id="backHomeBtn" data-testid="back-home-btn">Return to Home Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
