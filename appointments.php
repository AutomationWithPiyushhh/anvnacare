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
    // 1. Fetch Upcoming Appointments
    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time, d.name as doctor_name, d.specialization, d.fee 
        FROM appointments a 
        JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.user_id = ? AND a.status = 'Upcoming' 
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $stmt->execute([$userId]);
    $upcoming = $stmt->fetchAll();

    // 2. Fetch Past/History Appointments
    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time, a.status, d.name as doctor_name, d.specialization, d.fee 
        FROM appointments a 
        JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.user_id = ? AND a.status != 'Upcoming' 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$userId]);
    $past = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container my-4">
    <!-- Breadcrumbs -->
    <div class="mb-4">
        <h2 class="fw-bold text-success" id="appointmentsTitle" data-testid="appointments-title">Consultation Bookings</h2>
        <p class="text-muted">Review your upcoming consultations and clinical appointment history</p>
    </div>

    <!-- Tab selection -->
    <ul class="nav nav-tabs mb-4" id="appointmentsTab" role="tablist" data-testid="appointments-tabs">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming-panel" type="button" role="tab" aria-controls="upcoming-panel" aria-selected="true" data-testid="upcoming-tab-btn">
                Upcoming Appointments <span class="badge bg-success ms-1"><?= count($upcoming) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past-panel" type="button" role="tab" aria-controls="past-panel" aria-selected="false" data-testid="past-tab-btn">
                Booking History <span class="badge bg-secondary ms-1"><?= count($past) ?></span>
            </button>
        </li>
    </ul>

    <!-- Tab Panels -->
    <div class="tab-content" id="appointmentsTabContent">
        <!-- Upcoming Panel -->
        <div class="tab-pane fade show active" id="upcoming-panel" role="tabpanel" aria-labelledby="upcoming-tab" data-testid="upcoming-tab-panel">
            <?php if (empty($upcoming)): ?>
                <div class="text-center py-5 glass-card border-0">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">You have no upcoming consultations scheduled.</p>
                    <a href="doctors.php" class="btn btn-primary-custom btn-sm mt-2" id="bookNowBtn" data-testid="book-now-btn">Book Consultation Now</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($upcoming as $app): ?>
                        <div class="col-md-6" id="appointment-card-<?= $app['id'] ?>" data-testid="appointment-card">
                            <div class="card glass-card border-0 p-4 border-start border-success border-4">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1" data-testid="appointment-doctor"><?= htmlspecialchars($app['doctor_name']) ?></h5>
                                        <span class="badge bg-success-subtle text-success border mb-3"><?= htmlspecialchars($app['specialization']) ?></span>
                                    </div>
                                    <span class="badge bg-info text-dark" data-testid="appointment-status">Upcoming</span>
                                </div>

                                <div class="row g-2 mb-3 bg-light p-3 rounded">
                                    <div class="col-sm-6 small text-muted">
                                        <i class="bi bi-calendar3 text-success me-1"></i> Date: <strong class="text-dark" data-testid="appointment-date"><?= $app['appointment_date'] ?></strong>
                                    </div>
                                    <div class="col-sm-6 small text-muted">
                                        <i class="bi bi-clock text-success me-1"></i> Time: <strong class="text-dark" data-testid="appointment-time"><?= $app['appointment_time'] ?></strong>
                                    </div>
                                    <div class="col-12 border-top pt-2 mt-2 small text-muted">
                                        <i class="bi bi-wallet2 text-success me-1"></i> Consult Fee: <strong class="text-dark">₹<?= number_format($app['fee'], 2) ?></strong>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm px-4" onclick="cancelAppointment(<?= $app['id'] ?>)" id="cancelAppBtn-<?= $app['id'] ?>" data-testid="cancel-appointment-btn">
                                        Cancel Consultation
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- History Panel -->
        <div class="tab-pane fade" id="past-panel" role="tabpanel" aria-labelledby="past-tab" data-testid="past-tab-panel">
            <?php if (empty($past)): ?>
                <div class="text-center py-5 glass-card border-0">
                    <i class="bi bi-folder-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No historical consultations recorded.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive glass-card border-0 p-4">
                    <table class="table table-hover align-middle" id="historyTable" data-testid="history-table">
                        <thead>
                            <tr class="text-muted small">
                                <th>Doctor Name</th>
                                <th>Speciality</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Fee Paid</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($past as $app): ?>
                                <tr data-testid="history-row" id="history-row-<?= $app['id'] ?>">
                                    <td class="fw-bold" data-testid="history-doctor"><?= htmlspecialchars($app['doctor_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($app['specialization']) ?></span></td>
                                    <td data-testid="history-date"><?= $app['appointment_date'] ?></td>
                                    <td data-testid="history-time"><?= $app['appointment_time'] ?></td>
                                    <td class="fw-semibold">₹<?= number_format($app['fee'], 2) ?></td>
                                    <td>
                                        <?php if ($app['status'] === 'Completed'): ?>
                                            <span class="badge bg-success-subtle text-success border" data-testid="history-status">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border" data-testid="history-status">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Cancel Appointment Function (triggers Confirmation Alert)
function cancelAppointment(appId) {
    if (confirm("Are you sure you want to cancel this doctor consultation appointment?")) {
        showLoader();
        
        fetch('api/appointments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'cancel',
                appointment_id: appId
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(err => {
            hideLoader();
            console.error('Cancel appointment error:', err);
            showToast('Failed to cancel appointment. Connection error.', 'danger');
        });
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>
