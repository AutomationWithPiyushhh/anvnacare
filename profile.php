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
    // Fetch User
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Fetch Addresses
    $addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $addrStmt->execute([$userId]);
    $addresses = $addrStmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Breadcrumbs -->
    <div class="mb-4">
        <h2 class="fw-bold text-success" id="profileTitle" data-testid="profile-title">My Account Settings</h2>
        <p class="text-muted">Update your details, manage addresses, and change security credentials</p>
    </div>

    <div class="row g-4">
        <!-- Profile Side Card (Image upload) -->
        <div class="col-lg-4">
            <div class="card glass-card border-0 p-4 text-center" id="profilePicCard" data-testid="profile-pic-card">
                <!-- Avatar placeholder -->
                <div class="position-relative d-inline-block mx-auto mb-3" style="width: 140px; height: 140px;">
                    <img src="https://placehold.co/150x150/eef7f2/0a6c42?text=Patient" class="img-fluid rounded-circle border border-4 border-success-subtle shadow-sm" alt="Profile Picture" id="profileAvatarImg" data-testid="profile-avatar-img" style="width: 130px; height: 130px; object-fit: cover;">
                    <button class="btn btn-success btn-sm position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" id="uploadTriggerBtn" data-testid="upload-trigger-btn" onclick="triggerPhotoSelect()" aria-label="Upload Photo">
                        <i class="bi bi-camera"></i>
                    </button>
                    <!-- Hidden photo input -->
                    <input type="file" id="profilePhotoInput" class="d-none" name="profile_photo" data-testid="profile-photo-input" onchange="handlePhotoSelect(this)">
                </div>

                <h5 class="fw-bold text-dark mb-1" id="profileNameDisplay" data-testid="profile-name-display"><?= htmlspecialchars($user['name']) ?></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge bg-success-subtle text-success border px-3 py-2 rounded-pill fw-semibold mb-2" data-testid="profile-role-badge">Verified Account</span>
                
                <!-- QA progress notification -->
                <div id="photoUploadProgressBox" class="d-none mt-3" data-testid="photo-progress-box">
                    <div class="small text-muted mb-1">Uploading Image...</div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="photoProgressBar" role="progressbar" style="width: 0%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Panel Tabs -->
        <div class="col-lg-8">
            <div class="card glass-card border-0 p-4" id="profileFormCard" data-testid="profile-form-card">
                <!-- Navigation Tabs -->
                <ul class="nav nav-pills mb-4" id="profileTabs" role="tablist" data-testid="profile-tabs">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="details-tab" data-bs-toggle="pill" data-bs-target="#details-panel" type="button" role="tab" aria-controls="details-panel" aria-selected="true" data-testid="details-tab-btn">
                            Personal Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" data-bs-toggle="pill" data-bs-target="#password-panel" type="button" role="tab" aria-controls="password-panel" aria-selected="false" data-testid="password-tab-btn">
                            Change Password
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="addresses-tab" data-bs-toggle="pill" data-bs-target="#addresses-panel" type="button" role="tab" aria-controls="addresses-panel" aria-selected="false" data-testid="addresses-tab-btn">
                            Saved Addresses
                        </button>
                    </li>
                </ul>

                <!-- Panels Content -->
                <div class="tab-content" id="profileTabsContent">
                    
                    <!-- Tab 1: Edit Details -->
                    <div class="tab-pane fade show active" id="details-panel" role="tabpanel" aria-labelledby="details-tab" data-testid="details-panel">
                        <form id="profileDetailsForm" novalidate>
                            <div class="mb-3">
                                <label for="profileNameInput" class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control" id="profileNameInput" name="name" value="<?= htmlspecialchars($user['name']) ?>" required data-testid="profile-name-input">
                                <div class="invalid-feedback">Name must be at least 3 characters.</div>
                            </div>
                            <div class="mb-3">
                                <label for="profileEmailInput" class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control bg-light" id="profileEmailInput" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly data-testid="profile-email-input">
                                <span class="small text-muted">Email address cannot be changed.</span>
                            </div>
                            <div class="mb-4">
                                <label for="profilePhoneInput" class="form-label fw-bold">Phone Number</label>
                                <input type="tel" class="form-control" id="profilePhoneInput" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required pattern="[0-9]{10}" data-testid="profile-phone-input">
                                <div class="invalid-feedback">Phone must be a valid 10-digit number.</div>
                            </div>
                            <button type="submit" class="btn btn-success px-4" id="saveProfileBtn" data-testid="save-profile-btn">Save Profile Updates</button>
                        </form>
                    </div>

                    <!-- Tab 2: Change Password -->
                    <div class="tab-pane fade" id="password-panel" role="tabpanel" aria-labelledby="password-tab" data-testid="password-panel">
                        <form id="profilePasswordForm" novalidate>
                            <div class="mb-3">
                                <label for="currentPasswordInput" class="form-label fw-bold">Current Password</label>
                                <input type="password" class="form-control" id="currentPasswordInput" name="current_password" required data-testid="current-password-input">
                                <div class="invalid-feedback">Please enter your current password.</div>
                            </div>
                            <div class="mb-3">
                                <label for="newPasswordInput" class="form-label fw-bold">New Password</label>
                                <input type="password" class="form-control" id="newPasswordInput" name="new_password" required minlength="6" data-testid="new-password-input">
                                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                            </div>
                            <div class="mb-4">
                                <label for="confirmNewPasswordInput" class="form-label fw-bold">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmNewPasswordInput" name="confirm_new_password" required data-testid="confirm-new-password-input">
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                            <button type="submit" class="btn btn-success px-4" id="changePasswordBtn" data-testid="change-password-btn">Change Password</button>
                        </form>
                    </div>

                    <!-- Tab 3: Saved Addresses -->
                    <div class="tab-pane fade" id="addresses-panel" role="tabpanel" aria-labelledby="addresses-tab" data-testid="addresses-panel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Manage Delivery Addresses</h6>
                        </div>

                        <div class="list-group list-group-flush" id="addressesListGroup" data-testid="addresses-list-group">
                            <?php if (empty($addresses)): ?>
                                <div class="text-center py-4 text-muted">No saved addresses found.</div>
                            <?php else: ?>
                                <?php foreach ($addresses as $addr): ?>
                                    <div class="list-group-item bg-transparent px-0 py-3 d-flex justify-content-between align-items-start" id="addr-row-<?= $addr['id'] ?>" data-testid="address-row">
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <?= htmlspecialchars($addr['name']) ?>
                                                <?php if ($addr['is_default']): ?>
                                                    <span class="badge bg-success-subtle text-success border ms-1 small" data-testid="default-badge" style="font-size: 0.6rem">Default</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                <?= htmlspecialchars($addr['address_line1']) ?>, <?= htmlspecialchars($addr['address_line2']) ?><br>
                                                <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> - <?= htmlspecialchars($addr['pincode']) ?><br>
                                                Phone: <?= htmlspecialchars($addr['phone']) ?>
                                            </div>
                                        </div>
                                        <div>
                                            <!-- Delete button for addresses (QA action) -->
                                            <button class="btn btn-outline-danger btn-sm" onclick="deleteAddress(<?= $addr['id'] ?>)" id="deleteAddrBtn-<?= $addr['id'] ?>" data-testid="delete-address-btn" aria-label="Delete Address">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Photo File Selection
function triggerPhotoSelect() {
    document.getElementById('profilePhotoInput').click();
}

// Simulating Profile Photo Upload (QA challenge progress bars)
function handlePhotoSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const progressBox = document.getElementById('photoUploadProgressBox');
        const progressBar = document.getElementById('photoProgressBar');
        const avatar = document.getElementById('profileAvatarImg');

        progressBox.classList.remove('d-none');
        progressBar.style.width = '0%';

        let progress = 0;
        const interval = setInterval(() => {
            progress += 20;
            progressBar.style.width = progress + '%';

            if (progress >= 100) {
                clearInterval(interval);
                setTimeout(() => {
                    progressBox.classList.add('d-none');
                    // Render locally loaded temporary image path as verification
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        avatar.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    showToast('Profile photo updated successfully (Mock uploader).', 'success');
                }, 300);
            }
        }, 150); // Total 0.75 seconds upload
    }
}

// Submit Profile Details Update
document.getElementById('profileDetailsForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const name = document.getElementById('profileNameInput');
    const phone = document.getElementById('profilePhoneInput');

    let isValid = true;
    if (name.value.trim().length < 3) {
        name.classList.add('is-invalid');
        isValid = false;
    } else {
        name.classList.remove('is-invalid');
    }

    if (!/^[0-9]{10}$/.test(phone.value.trim())) {
        phone.classList.add('is-invalid');
        isValid = false;
    } else {
        phone.classList.remove('is-invalid');
    }

    if (!isValid) return;

    showLoader();
    fetch('api/profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update_profile',
            name: name.value.trim(),
            phone: phone.value.trim()
        })
    })
    .then(res => res.json())
    .then(data => {
        hideLoader();
        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('profileNameDisplay').innerText = name.value.trim();
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(err => {
        hideLoader();
        console.error(err);
        showToast('Connection error.', 'danger');
    });
});

// Submit Profile Change Password
document.getElementById('profilePasswordForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const curr = document.getElementById('currentPasswordInput');
    const news = document.getElementById('newPasswordInput');
    const conf = document.getElementById('confirmNewPasswordInput');

    let isValid = true;
    if (curr.value === '') {
        curr.classList.add('is-invalid');
        isValid = false;
    } else {
        curr.classList.remove('is-invalid');
    }

    if (news.value.length < 6) {
        news.classList.add('is-invalid');
        isValid = false;
    } else {
        news.classList.remove('is-invalid');
    }

    if (news.value !== conf.value || conf.value === '') {
        conf.classList.add('is-invalid');
        isValid = false;
    } else {
        conf.classList.remove('is-invalid');
    }

    if (!isValid) return;

    showLoader();
    fetch('api/profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'change_password',
            current_password: curr.value,
            new_password: news.value
        })
    })
    .then(res => {
        return res.json().then(data => {
            if (!res.ok) throw new Error(data.message || 'Failed to change password.');
            return data;
        });
    })
    .then(data => {
        hideLoader();
        showToast(data.message, 'success');
        curr.value = '';
        news.value = '';
        conf.value = '';
    })
    .catch(err => {
        hideLoader();
        showToast(err.message, 'danger');
    });
});

// Delete Saved Address
function deleteAddress(addrId) {
    if (confirm("Are you sure you want to delete this saved address?")) {
        showLoader();
        
        // Simulating address deletion via a mock POST request
        setTimeout(() => {
            hideLoader();
            showToast('Address deleted successfully.', 'success');
            const row = document.getElementById(`addr-row-${addrId}`);
            if (row) row.remove();
        }, 800);
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>
