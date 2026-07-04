<?php
require_once 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: doctors.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->execute([$id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        header("Location: 404.php");
        exit;
    }

    $sched = json_decode($doc['availability'], true);
    $days = implode(', ', $sched['days'] ?? []);
    $time = $sched['time'] ?? '';

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Back Navigation -->
    <div class="mb-3">
        <a href="doctors.php" class="text-success text-decoration-none fw-semibold" id="backToDoctors" data-testid="back-to-doctors-link">
            <i class="bi bi-arrow-left"></i> Back to Doctors
        </a>
    </div>

    <!-- Doctor details Card -->
    <div class="row g-5">
        <!-- Biography and Info Column -->
        <div class="col-lg-7">
            <div class="card glass-card border-0 p-4" id="doctorBioCard" data-testid="doctor-bio-card">
                <div class="d-flex align-items-center gap-4 mb-4 flex-wrap flex-sm-nowrap">
                    <!-- Placeholder Doctor photo -->
                    <img src="https://placehold.co/150x150/eef7f2/0a6c42?text=MD" class="doctor-profile-img shadow" alt="<?= htmlspecialchars($doc['name']) ?>" id="docDetailsImg" data-testid="doc-details-img">
                    <div>
                        <h2 class="fw-bold text-dark mb-1" id="docDetailsName" data-testid="doc-details-name"><?= htmlspecialchars($doc['name']) ?></h2>
                        <span class="badge bg-success-subtle text-success border mb-2 fs-6" id="docDetailsSpec" data-testid="doc-details-spec"><?= $doc['specialization'] ?></span>
                        <div class="text-muted"><i class="bi bi-award-fill text-warning me-1"></i> <?= $doc['experience'] ?> Years of Clinical Experience</div>
                        <div class="text-muted"><i class="bi bi-translate text-success me-1"></i> Languages: <strong><?= htmlspecialchars($doc['languages']) ?></strong></div>
                    </div>
                </div>

                <hr>

                <h4 class="fw-bold mb-3 text-success">Professional Biography</h4>
                <p class="text-secondary mb-4" id="docDetailsBio" data-testid="doc-details-bio">
                    <?= htmlspecialchars($doc['bio']) ?> Dr. <?= htmlspecialchars(explode(' ', $doc['name'])[2] ?? $doc['name']) ?> is highly dedicated to providing patient-centric care and follows evidence-based practices.
                </p>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="bg-light p-3 rounded" id="availabilityBox" data-testid="availability-box">
                            <span class="small text-muted d-block mb-1">Weekly Availability</span>
                            <strong class="text-dark"><i class="bi bi-calendar-week text-success me-1"></i> <?= $days ?></strong>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-light p-3 rounded" id="timingsBox" data-testid="timings-box">
                            <span class="small text-muted d-block mb-1">Consultation Timings</span>
                            <strong class="text-dark"><i class="bi bi-clock text-success me-1"></i> <?= $time ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment Booking Form Column -->
        <div class="col-lg-5">
            <div class="card glass-card border-0 p-4 border-top border-success border-4" id="bookingCard" data-testid="booking-card">
                <h4 class="fw-bold mb-3"><i class="bi bi-calendar-event-fill text-success me-2"></i> Book Consultation</h4>
                <p class="text-muted small mb-4">Select your preferred date and time slot below to secure your appointment.</p>
                
                <!-- Fee Box -->
                <div class="alert bg-success-subtle border-0 text-success d-flex justify-content-between align-items-center mb-4 py-2" id="consultationFeeBox" data-testid="consultation-fee-box">
                    <span class="fw-semibold">Consultation Fee</span>
                    <strong class="fs-4">₹<?= number_format($doc['fee'], 2) ?></strong>
                </div>

                <!-- Booking Form -->
                <form id="bookingForm" data-testid="booking-form" novalidate>
                    <!-- Date Picker (Calendar) -->
                    <div class="mb-3">
                        <label for="appointmentDate" class="form-label fw-bold text-dark" data-testid="date-label">1. Choose Date</label>
                        <input type="date" class="form-control" id="appointmentDate" name="date" data-testid="appointment-datepicker" required>
                        <div class="invalid-feedback">Please select a valid future date.</div>
                    </div>

                    <!-- Time Slot Dropdown -->
                    <div class="mb-4">
                        <label for="appointmentTime" class="form-label fw-bold text-dark" data-testid="time-label">2. Select Time Slot</label>
                        <select class="form-select" id="appointmentTime" name="time" data-testid="appointment-time-dropdown" required>
                            <option value="">-- Choose Slot --</option>
                            <option value="09:00 AM">09:00 AM (Morning)</option>
                            <option value="10:00 AM">10:00 AM (Morning)</option>
                            <option value="11:00 AM">11:00 AM (Morning)</option>
                            <option value="12:00 PM">12:00 PM (Noon)</option>
                            <option value="02:00 PM">02:00 PM (Afternoon)</option>
                            <option value="03:00 PM">03:00 PM (Afternoon)</option>
                            <option value="04:00 PM">04:00 PM (Evening)</option>
                        </select>
                        <div class="invalid-feedback">Please select an appointment time slot.</div>
                    </div>

                    <!-- Book Slot Button -->
                    <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-6 d-flex align-items-center justify-content-center gap-2" id="bookSlotBtn" data-testid="book-slot-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="bookingLoader" role="status" aria-hidden="true"></span>
                        <span>Confirm Appointment Booking</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bookingForm');
    const dateInput = document.getElementById('appointmentDate');
    const timeInput = document.getElementById('appointmentTime');
    const submitBtn = document.getElementById('bookSlotBtn');
    const loader = document.getElementById('bookingLoader');

    // Restrict datepicker to today and onwards
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        
        let isValid = true;
        
        if (dateInput.value === '') {
            dateInput.classList.add('is-invalid');
            isValid = false;
        } else {
            dateInput.classList.remove('is-invalid');
        }

        if (timeInput.value === '') {
            timeInput.classList.add('is-invalid');
            isValid = false;
        } else {
            timeInput.classList.remove('is-invalid');
        }

        if (!isValid) return;

        // Show loading state
        submitBtn.disabled = true;
        loader.classList.remove('d-none');

        setTimeout(() => {
            fetch('api/appointments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'book',
                    doctor_id: <?= $doc['id'] ?>,
                    date: dateInput.value,
                    time: timeInput.value
                })
            })
            .then(response => {
                return response.json().then(data => {
                    if (!response.ok) {
                        throw new Error(data.message || 'Booking failed.');
                    }
                    return data;
                });
            })
            .then(data => {
                loader.classList.add('d-none');
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'appointments.php';
                    }, 1200);
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                loader.classList.add('d-none');
                
                showToast(err.message, 'danger');
                if (err.message.includes('login') || err.message.includes('Unauthorized')) {
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1000);
                }
            });
        }, 1200); // 1.2s delay for testing waiting state
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>
