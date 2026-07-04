<?php
require_once 'includes/header.php';
?>

<div class="container my-4">
    <!-- Header banner -->
    <div class="mb-5 text-center">
        <h2 class="fw-bold text-success" id="contactTitle" data-testid="contact-title">Contact Support Desk</h2>
        <p class="text-muted">Have any queries regarding doctor bookings, lab tests, or pharmacy orders? Message us!</p>
    </div>

    <div class="row g-5 justify-content-center">
        <!-- Contact Details Column -->
        <div class="col-md-5">
            <div class="card glass-card border-0 p-4 h-100" id="contactInfoCard" data-testid="contact-info-card">
                <h4 class="fw-bold text-success mb-4">Get In Touch</h4>
                
                <div class="d-flex align-items-start gap-3 mb-4">
                    <span class="fs-4 text-success"><i class="bi bi-geo-alt-fill"></i></span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Corporate HQ Address</h6>
                        <span class="text-muted small">noida, Uttar Pradesh, 201301</span>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <span class="fs-4 text-success"><i class="bi bi-telephone-fill"></i></span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Helpline Contacts</h6>
                        <span class="text-muted small">+1 (555) 123-4567 | +1 (555) 765-4321</span>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <span class="fs-4 text-success"><i class="bi bi-envelope-fill"></i></span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Email Support Desk</h6>
                        <span class="text-muted small">automation.with.piyush@gmail.com</span>
                    </div>
                </div>

                <hr class="my-4">

                <div>
                    <h6 class="fw-bold text-dark mb-2">Patient Support Timings</h6>
                    <p class="small text-muted mb-0"><i class="bi bi-clock me-1 text-success"></i> 24/7 Clinical Emergency Support</p>
                    <p class="small text-muted"><i class="bi bi-clock me-1 text-success"></i> General Queries: Mon-Sat (9:00 AM - 6:00 PM)</p>
                </div>
            </div>
        </div>

        <!-- Contact Form Column -->
        <div class="col-md-7">
            <div class="card glass-card border-0 p-4 border-top border-4 border-success" id="contactFormCard" data-testid="contact-form-card">
                <h4 class="fw-bold text-dark mb-3">Send Support Query</h4>
                
                <!-- Success feedback alert -->
                <div class="alert alert-success d-none" id="contactSuccessAlert" data-testid="contact-success-alert" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> Thank you! Your support ticket has been submitted. We will contact you soon.
                </div>

                <form id="contactForm" data-testid="contact-form" novalidate>
                    <div class="mb-3">
                        <label for="contactName" class="form-label fw-semibold">Your Name</label>
                        <input type="text" class="form-control" id="contactName" name="name" placeholder="John Doe" required data-testid="contact-name-input">
                        <div class="invalid-feedback">Please enter your name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contactEmail" class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" id="contactEmail" name="email" placeholder="name@domain.com" required data-testid="contact-email-input">
                        <div class="invalid-feedback">Please enter a valid email.</div>
                    </div>

                    <div class="mb-3">
                        <label for="contactSubject" class="form-label fw-semibold">Query Subject</label>
                        <input type="text" class="form-control" id="contactSubject" name="subject" placeholder="e.g. Refund issue, Appointment reschedule" required data-testid="contact-subject-input">
                        <div class="invalid-feedback">Please enter a subject.</div>
                    </div>

                    <div class="mb-4">
                        <label for="contactMessage" class="form-label fw-semibold">Message Description</label>
                        <textarea class="form-control" id="contactMessage" name="message" rows="4" placeholder="Detail your support issue here..." required data-testid="contact-message-input"></textarea>
                        <div class="invalid-feedback">Message cannot be empty.</div>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-6 d-flex align-items-center justify-content-center gap-2" id="contactSubmitBtn" data-testid="contact-submit-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="contactLoader" role="status" aria-hidden="true"></span>
                        <span>Send Query Message</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('contactForm');
    const name = document.getElementById('contactName');
    const email = document.getElementById('contactEmail');
    const subject = document.getElementById('contactSubject');
    const msg = document.getElementById('contactMessage');
    const submitBtn = document.getElementById('contactSubmitBtn');
    const loader = document.getElementById('contactLoader');
    const success = document.getElementById('contactSuccessAlert');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        success.classList.add('d-none');

        let isValid = true;
        
        if (name.value.trim() === '') {
            name.classList.add('is-invalid');
            isValid = false;
        } else {
            name.classList.remove('is-invalid');
        }

        if (!email.value.includes('@')) {
            email.classList.add('is-invalid');
            isValid = false;
        } else {
            email.classList.remove('is-invalid');
        }

        if (subject.value.trim() === '') {
            subject.classList.add('is-invalid');
            isValid = false;
        } else {
            subject.classList.remove('is-invalid');
        }

        if (msg.value.trim() === '') {
            msg.classList.add('is-invalid');
            isValid = false;
        } else {
            msg.classList.remove('is-invalid');
        }

        if (!isValid) return;

        // Simulate AJAX submit
        submitBtn.disabled = true;
        loader.classList.remove('d-none');

        setTimeout(() => {
            submitBtn.disabled = false;
            loader.classList.add('d-none');
            
            success.classList.remove('d-none');
            showToast('Support ticket successfully sent.', 'success');
            
            // Clear fields
            name.value = '';
            email.value = '';
            subject.value = '';
            msg.value = '';
        }, 1200);
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>
