<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <!-- Glassmorphic Login Card -->
            <div class="card glass-card border-0 p-4" id="loginCard" data-testid="login-card">
                <div class="text-center mb-4">
                    <img src="assets/images/logo.svg" alt="ANVNA Care Logo" class="mb-3" style="height: 50px;">
                    <h3 class="fw-bold text-success" id="loginTitle" data-testid="login-title">Welcome Back</h3>
                    <p class="text-muted small">Access your patient profile or administrator panel</p>
                </div>

                <!-- Tabs for Patient and Admin Logins -->
                <ul class="nav nav-pills nav-justified mb-4" id="loginTabs" role="tablist" data-testid="login-tabs">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="patientTab" data-bs-toggle="pill" data-bs-target="#patientPanel" type="button" role="tab" aria-controls="patientPanel" aria-selected="true" data-testid="patient-tab-btn">
                            <i class="bi bi-person me-2"></i>Patient
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="adminTab" data-bs-toggle="pill" data-bs-target="#adminPanel" type="button" role="tab" aria-controls="adminPanel" aria-selected="false" data-testid="admin-tab-btn">
                            <i class="bi bi-shield-lock me-2"></i>Admin
                        </button>
                    </li>
                </ul>

                <!-- Error Alert Box -->
                <div class="alert alert-danger d-none" id="errorAlert" data-testid="error-alert-box" role="alert">
                    <span id="errorMessage" data-testid="error-message"></span>
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="loginTabsContent">
                    <!-- Patient & Admin shared logic via Javascript (email prefix controls dashboard routing) -->
                    <form id="loginForm" data-testid="login-form" novalidate>
                        <!-- Email Input -->
                        <div class="mb-3">
                            <label for="emailInput" class="form-label fw-semibold" id="emailLabel" data-testid="email-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-success"></i></span>
                                <input type="email" class="form-control border-start-0" id="emailInput" name="email" placeholder="Enter your registered email" data-testid="email-input" required autocomplete="email">
                            </div>
                            <div class="invalid-feedback" id="emailFeedback" data-testid="email-feedback">Please enter a valid email.</div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="passwordInput" class="form-label fw-semibold" id="passwordLabel" data-testid="password-label">Password</label>
                                <a href="forgot-password.php" class="text-success text-decoration-none small" id="forgotPasswordLink" data-testid="forgot-password-link">Forgot Password?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-success"></i></span>
                                <input type="password" class="form-control border-start-0" id="passwordInput" name="password" placeholder="Enter your password" data-testid="password-input" required>
                                <span class="input-group-text bg-light cursor-pointer" id="togglePasswordBtn" data-testid="toggle-password-btn" style="cursor: pointer;"><i class="bi bi-eye-slash"></i></span>
                            </div>
                            <div class="invalid-feedback" id="passwordFeedback" data-testid="password-feedback">Password is required.</div>
                        </div>

                        <!-- Remember Me Cookie and Role Toggles -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMeCheckbox" name="remember" data-testid="remember-me-checkbox">
                                <label class="form-check-label text-muted small" for="rememberMeCheckbox" id="rememberMeLabel" data-testid="remember-me-label">
                                    Remember Me
                                </label>
                            </div>
                            <span class="badge bg-light text-success-emphasis border" id="selectedRoleBadge" data-testid="role-badge">Role: Patient</span>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-6 d-flex align-items-center justify-content-center gap-2" id="loginButton" data-testid="login-submit-btn">
                            <span class="spinner-border spinner-border-sm d-none" id="submitBtnLoader" data-testid="submit-btn-loader" role="status" aria-hidden="true"></span>
                            <span id="submitBtnText">Sign In</span>
                        </button>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted small mb-0">Don't have an account? <a href="register.php" class="text-success fw-bold text-decoration-none" id="registerLink" data-testid="register-link">Register Here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    const roleBadge = document.getElementById('selectedRoleBadge');
    const patientTab = document.getElementById('patientTab');
    const adminTab = document.getElementById('adminTab');
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    
    const submitBtn = document.getElementById('loginButton');
    const submitLoader = document.getElementById('submitBtnLoader');
    const submitText = document.getElementById('submitBtnText');

    let currentRole = 'user'; // Defaults to user (Patient)

    // Toggle Password Visibility
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function () {
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'bi bi-eye';
            } else {
                passwordInput.type = 'password';
                icon.className = 'bi bi-eye-slash';
            }
        });
    }

    // Role Tab triggers
    if (patientTab) {
        patientTab.addEventListener('click', () => {
            currentRole = 'user';
            roleBadge.innerText = 'Role: Patient';
            roleBadge.className = 'badge bg-light text-success-emphasis border';
            // Auto fill helper for user automation testing
            emailInput.value = 'amit.kumar@anvnacare.com';
            passwordInput.value = 'password123';
        });
    }

    if (adminTab) {
        adminTab.addEventListener('click', () => {
            currentRole = 'admin';
            roleBadge.innerText = 'Role: Admin';
            roleBadge.className = 'badge bg-warning-subtle text-warning-emphasis border';
            // Auto fill helper for admin automation testing
            emailInput.value = 'admin@anvnacare.com';
            passwordInput.value = 'password123';
        });
    }

    // Form Submit handling
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errorAlert.classList.add('d-none');
        
        let isValid = true;

        // Validations
        if (!emailInput.value.includes('@') || emailInput.value.trim() === '') {
            emailInput.classList.add('is-invalid');
            isValid = false;
        } else {
            emailInput.classList.remove('is-invalid');
        }

        if (passwordInput.value.trim() === '') {
            passwordInput.classList.add('is-invalid');
            isValid = false;
        } else {
            passwordInput.classList.remove('is-invalid');
        }

        if (!isValid) return;

        // Show Loader & Disable Button
        submitBtn.disabled = true;
        submitLoader.classList.remove('d-none');
        submitText.innerText = 'Verifying...';

        // Add simulated wait of 1 second for automation synchronization practice
        setTimeout(() => {
            fetch('api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: emailInput.value.trim(),
                    password: passwordInput.value,
                    remember: document.getElementById('rememberMeCheckbox').checked
                })
            })
            .then(response => {
                return response.json().then(data => {
                    if (!response.ok) {
                        throw new Error(data.message || 'Login failed.');
                    }
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    showToast('Welcome to ANVNA Care!', 'success');
                    setTimeout(() => {
                        if (data.user.role === 'admin') {
                            window.location.href = 'admin/index.php';
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    }, 800);
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitLoader.classList.add('d-none');
                submitText.innerText = 'Sign In';
                
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
