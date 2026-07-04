<?php
require_once 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card glass-card border-0 p-4" id="privacyCard" data-testid="privacy-card">
                <h3 class="fw-bold text-success mb-4" id="privacyHeader" data-testid="privacy-header">Privacy Policy</h3>

                <div class="text-secondary small" id="privacyContent" data-testid="privacy-content">
                    <p class="mb-3">
                        At ANVNA Care, we value patient privacy and are committed to protecting personal health records (PHRs) and shopper credentials. This Privacy Policy details how we collect, store, and manage customer data.
                    </p>

                    <h5 class="fw-bold text-dark mt-4 h6">1. Information Collection</h5>
                    <p class="mb-3">
                        We collect standard customer inputs such as receiver names, email addresses, 10-digit mobile numbers, shipping addresses, and doctor appointment scheduling preferences during mock account creations.
                    </p>

                    <h5 class="fw-bold text-dark mt-4 h6">2. Test Environment Data</h5>
                    <p class="mb-3">
                        All information submitted on this site is logged into a temporary testing database schema designed exclusively for QA students practicing automation. We do not transmit or sell any information to third-party entities.
                    </p>

                    <h5 class="fw-bold text-dark mt-4 h6">3. Cookies usage</h5>
                    <p class="mb-3">
                        We utilize cookies for the "Remember Me" login checks. This preserves user sessions across page reloads for up to 30 days. You can clear these cookies at any time from your browser settings.
                    </p>

                    <h5 class="fw-bold text-dark mt-4 h6">4. Data Erasure</h5>
                    <p class="mb-3">
                        Administrators can truncate or clean database tables at regular intervals during batch automated test sweeps. You may also update or wipe your profiles from the Settings panel.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
