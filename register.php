<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <!-- Glassmorphic Registration Card -->
            <div class="card glass-card border-0 p-4" id="registerCard" data-testid="register-card">
                <div class="text-center mb-4">
                    <img src="assets/images/logo.svg" alt="ANVNA Care Logo" class="mb-3" style="height: 50px;">
                    <h3 class="fw-bold text-success" id="registerTitle" data-testid="register-title">Create Account</h3>
                    <p class="text-muted small">Register to book doctors, order medicines, and track records</p>
                </div>

                <!-- Error Alert Box -->
                <div class="alert alert-danger d-none" id="errorAlert" data-testid="error-alert-box" role="alert">
                    <span id="errorMessage" data-testid="error-message"></span>
                </div>

                <!-- Registration Form -->
                <form id="registerForm" data-testid="register-form" novalidate>
                    <!-- Name Input -->
                    <div class="mb-3">
                        <label for="nameInput" class="form-label fw-semibold" id="nameLabel" data-testid="name-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-success"></i></span>
                            <input type="text" class="form-control border-start-0" id="nameInput" name="name" placeholder="Enter your full name" data-testid="name-input" required minlength="3">
                        </div>
                        <div class="invalid-feedback" id="nameFeedback" data-testid="name-feedback">Name must be at least 3 characters.</div>
                    </div>

                    <!-- Email Input -->
                    <div class="mb-3">
                        <label for="emailInput" class="form-label fw-semibold" id="emailLabel" data-testid="email-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-success"></i></span>
                            <input type="email" class="form-control border-start-0" id="emailInput" name="email" placeholder="Enter your email address" data-testid="email-input" required>
                        </div>
                        <div class="invalid-feedback" id="emailFeedback" data-testid="email-feedback">A valid email address is required.</div>
                    </div>

                    <!-- Phone Input -->
                    <div class="mb-3">
                        <label for="phoneInput" class="form-label fw-semibold" id="phoneLabel" data-testid="phone-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-success"></i></span>
                            <input type="tel" class="form-control border-start-0" id="phoneInput" name="phone" placeholder="Enter 10-digit mobile number" data-testid="phone-input" required pattern="[0-9]{10}">
                        </div>
                        <div class="invalid-feedback" id="phoneFeedback" data-testid="phone-feedback">Phone must be a valid 10-digit number.</div>
                    </div>

                    <!-- Password Input -->
                    <div class="mb-3">
                        <label for="passwordInput" class="form-label fw-semibold" id="passwordLabel" data-testid="password-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-success"></i></span>
                            <input type="password" class="form-control border-start-0" id="passwordInput" name="password" placeholder="Create a strong password" data-testid="password-input" required minlength="6">
                        </div>
                        <div class="invalid-feedback" id="passwordFeedback" data-testid="password-feedback">Password must be at least 6 characters.</div>
                    </div>

                    <!-- Terms and Conditions Checkbox -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="termsCheckbox" name="terms" data-testid="terms-checkbox" required>
                            <label class="form-check-label text-muted small" for="termsCheckbox" id="termsLabel" data-testid="terms-label">
                                I agree to the <a href="terms.php" class="text-success text-decoration-none fw-bold" target="_blank" data-testid="terms-link">Terms & Conditions</a> and <a href="privacy.php" class="text-success text-decoration-none fw-bold" target="_blank" data-testid="privacy-link">Privacy Policy</a>
                            </label>
                            <div class="invalid-feedback" id="termsFeedback" data-testid="terms-feedback">You must agree before submitting.</div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-6 d-flex align-items-center justify-content-center gap-2" id="registerButton" data-testid="register-submit-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="submitBtnLoader" data-testid="submit-btn-loader" role="status" aria-hidden="true"></span>
                        <span id="submitBtnText">Create Profile</span>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted small mb-0">Already have an account? <a href="login.php" class="text-success fw-bold text-decoration-none" id="loginLink" data-testid="login-link">Login Here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const nameInput = document.getElementById('nameInput');
    const emailInput = document.getElementById('emailInput');
    const phoneInput = document.getElementById('phoneInput');
    const passwordInput = document.getElementById('passwordInput');
    const termsCheckbox = document.getElementById('termsCheckbox');
    
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    const submitBtn = document.getElementById('registerButton');
    const submitLoader = document.getElementById('submitBtnLoader');
    const submitText = document.getElementById('submitBtnText');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errorAlert.classList.add('d-none');
        
        let isValid = true;

        // Validations
        if (nameInput.value.trim().length < 3) {
            nameInput.classList.add('is-invalid');
            isValid = false;
        } else {
            nameInput.classList.remove('is-invalid');
        }

        if (!emailInput.value.includes('@') || emailInput.value.trim() === '') {
            emailInput.classList.add('is-invalid');
            isValid = false;
        } else {
            emailInput.classList.remove('is-invalid');
        }

        if (!/^[0-9]{10}$/.test(phoneInput.value.trim())) {
            phoneInput.classList.add('is-invalid');
            isValid = false;
        } else {
            phoneInput.classList.remove('is-invalid');
        }

        if (passwordInput.value.length < 6) {
            passwordInput.classList.add('is-invalid');
            isValid = false;
        } else {
            passwordInput.classList.remove('is-invalid');
        }

        if (!termsCheckbox.checked) {
            termsCheckbox.classList.add('is-invalid');
            isValid = false;
        } else {
            termsCheckbox.classList.remove('is-invalid');
        }

        if (!isValid) return;

        // Show Loader & Disable Button
        submitBtn.disabled = true;
        submitLoader.classList.remove('d-none');
        submitText.innerText = 'Creating Profile...';

        setTimeout(() => {
            fetch('api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: nameInput.value.trim(),
                    email: emailInput.value.trim(),
                    phone: phoneInput.value.trim(),
                    password: passwordInput.value
                })
            })
            .then(response => {
                return response.json().then(data => {
                    if (!response.ok) {
                        throw new Error(data.message || 'Registration failed.');
                    }
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    showToast('Registration successful! Logging in...', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 800);
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitLoader.classList.add('d-none');
                submitText.innerText = 'Create Profile';
                
                errorMessage.innerText = err.message;
                errorAlert.classList.remove('d-none');
                showToast(err.message, 'danger');
            });
        }, 1000);
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>
