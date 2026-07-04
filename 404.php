<?php
require_once 'includes/header.php';
?>

<div class="container my-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card glass-card border-0 p-5 shadow-sm" id="error404Card" data-testid="error-404-card">
                <div class="fs-1 text-danger mb-3" id="errorIcon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <h1 class="display-4 fw-bold text-dark mb-2">404</h1>
                <h3 class="fw-bold mb-3 text-success" id="errorHeader" data-testid="error-header">Page Not Found</h3>
                <p class="text-muted mb-4">The medical service page or clinical profile directory you requested does not exist or has been relocated.</p>
                <a href="index.php" class="btn btn-primary-custom w-100 py-2" id="backHomeBtn" data-testid="back-home-btn">Return to Home Portal</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
