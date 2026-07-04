<?php
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <!-- Glassmorphic Card -->
            <div class="card glass-card border-0 p-4" id="forgotPasswordCard" data-testid="forgot-password-card">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-success" id="forgotTitle" data-testid="forgot-title">Reset Password</h3>
                    <p class="text-muted small">Recover your ANVNA Care login credentials</p>
                </div>

                <!-- Wizard Progress Bar -->
                <div class="wizard-steps mb-4" data-testid="forgot-steps">
                    <div class="wizard-step-line-active" id="wizardLine" style="width: 0%;"></div>
                    <div class="wizard-step active" id="step1Indicator" data-testid="step-1-indicator">1</div>
                    <div class="wizard-step" id="step2Indicator" data-testid="step-2-indicator">2</div>
                    <div class="wizard-step" id="step3Indicator" data-testid="step-3-indicator">3</div>
                </div>

                <!-- Step 1: Email Form -->
                <div id="step1Container" class="" data-testid="step-1-container">
                    <form id="emailForm" novalidate>
                        <div class="mb-3">
                            <label for="emailInput" class="form-label fw-semibold" data-testid="email-label">Enter Registered Email</label>
                            <input type="email" class="form-control" id="emailInput" placeholder="name@domain.com" data-testid="email-input" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100 py-2" id="sendOtpButton" data-testid="send-otp-btn">Send OTP Code</button>
                    </form>
                </div>

                <!-- Step 2: OTP Verification Form -->
                <div id="step2Container" class="d-none" data-testid="step-2-container">
                    <div class="alert alert-info text-center small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> For testing, please enter OTP: <strong>123456</strong>
                    </div>
                    <form id="otpForm" novalidate>
                        <div class="mb-3">
                            <label for="otpInput" class="form-label fw-semibold" data-testid="otp-label">Enter 6-Digit OTP</label>
                            <input type="text" class="form-control text-center fs-4 letter-spacing-lg" id="otpInput" placeholder="0 0 0 0 0 0" maxlength="6" data-testid="otp-input" required pattern="^[0-9]{6}$">
                            <div class="invalid-feedback">Please enter the correct 6-digit OTP.</div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100 py-2" id="verifyOtpButton" data-testid="verify-otp-btn">Verify OTP</button>
                    </form>
                </div>

                <!-- Step 3: New Password Form -->
                <div id="step3Container" class="d-none" data-testid="step-3-container">
                    <form id="passwordResetForm" novalidate>
                        <div class="mb-3">
                            <label for="newPasswordInput" class="form-label fw-semibold" data-testid="new-password-label">New Password</label>
                            <input type="password" class="form-control" id="newPasswordInput" placeholder="Min 6 characters" minlength="6" data-testid="new-password-input" required>
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPasswordInput" class="form-label fw-semibold" data-testid="confirm-password-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPasswordInput" placeholder="Confirm your password" data-testid="confirm-password-input" required>
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100 py-2" id="resetPasswordButton" data-testid="reset-password-btn">Update Password</button>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <a href="login.php" class="text-success small text-decoration-none fw-bold" id="backToLoginLink" data-testid="back-to-login-link"><i class="bi bi-arrow-left"></i> Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wizardLine = document.getElementById('wizardLine');
    const step1 = document.getElementById('step1Container');
    const step2 = document.getElementById('step2Container');
    const step3 = document.getElementById('step3Container');

    const ind1 = document.getElementById('step1Indicator');
    const ind2 = document.getElementById('step2Indicator');
    const ind3 = document.getElementById('step3Indicator');

    // Forms
    const emailForm = document.getElementById('emailForm');
    const otpForm = document.getElementById('otpForm');
    const passwordForm = document.getElementById('passwordResetForm');

    // Form Inputs
    const emailInput = document.getElementById('emailInput');
    const otpInput = document.getElementById('otpInput');
    const newPass = document.getElementById('newPasswordInput');
    const confPass = document.getElementById('confirmPasswordInput');

    let savedEmail = '';

    // Step 1: Submit Email
    emailForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!emailInput.value.includes('@')) {
            emailInput.classList.add('is-invalid');
            return;
        }
        emailInput.classList.remove('is-invalid');
        savedEmail = emailInput.value.trim();

        showLoader();
        setTimeout(() => {
            hideLoader();
            // Move to Step 2
            step1.classList.add('d-none');
            step2.classList.remove('d-none');
            ind2.classList.add('active');
            wizardLine.style.width = '50%';
            showToast('OTP code sent successfully to ' + savedEmail, 'info');
        }, 800);
    });

    // Step 2: Submit OTP
    otpForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (otpInput.value.trim() !== '123456') {
            otpInput.classList.add('is-invalid');
            return;
        }
        otpInput.classList.remove('is-invalid');

        showLoader();
        setTimeout(() => {
            hideLoader();
            // Move to Step 3
            step2.classList.add('d-none');
            step3.classList.remove('d-none');
            ind2.classList.add('completed');
            ind3.classList.add('active');
            wizardLine.style.width = '100%';
            showToast('OTP verified successfully.', 'success');
        }, 800);
    });

    // Step 3: Reset Password
    passwordForm.addEventListener('submit', function (e) {
        e.preventDefault();
        let isValid = true;

        if (newPass.value.length < 6) {
            newPass.classList.add('is-invalid');
            isValid = false;
        } else {
            newPass.classList.remove('is-invalid');
        }

        if (newPass.value !== confPass.value || confPass.value === '') {
            confPass.classList.add('is-invalid');
            isValid = false;
        } else {
            confPass.classList.remove('is-invalid');
        }

        if (!isValid) return;

        showLoader();
        setTimeout(() => {
            // We simulate hitting a DB update password API endpoint
            hideLoader();
            showToast('Password reset successfully. Please login with your new credentials.', 'success');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
        }, 1000);
    });
});
</script>

<?php
require_once 'includes/header.php';
?>
