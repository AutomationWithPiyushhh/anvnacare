<?php
require_once 'includes/header.php';
?>

<div class="container my-4">
    <!-- Hero Header -->
    <div class="hero-section text-center py-5 mb-5" id="aboutHero" data-testid="about-hero">
        <h1 class="hero-title text-success mb-2" id="aboutTitle" data-testid="about-title">About ANVNA Care</h1>
        <p class="lead text-secondary mx-auto" style="max-width: 600px;">
            Redefining digital healthcare deliveries by combining certified medical experts, genuine pharmacies, and diagnostic laboratories.
        </p>
    </div>

    <!-- Details Box -->
    <div class="row g-5 align-items-center mb-5">
        <div class="col-md-6" id="aboutStory" data-testid="about-story">
            <h3 class="fw-bold text-success mb-3">Our Founding Story</h3>
            <p class="text-secondary">
                ANVNA Care was founded in 2026 with a simple mission: to make healthcare accessible, affordable, and simple for everyone. We believe that securing doctor consultations, blood tests, and life-saving medicines should never be a complex hurdle.
            </p>
            <p class="text-secondary">
                By bridging technology and clinical infrastructure, we provide a unified startup healthcare app built from the ground up for convenience and speed. Today, we serve over 10 Lakh patients across the nation.
            </p>
        </div>
        <div class="col-md-6 text-center">
            <!-- Leaf Logo SVG illustration -->
            <svg viewBox="0 0 200 200" width="100%" height="180" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="80" fill="#f0faf4" />
                <path d="M 100 40 C 130 70, 130 110, 100 140 C 70 110, 70 70, 100 40 Z" fill="#0a6c42" />
                <path d="M 100 40 C 105 60, 105 100, 100 140" stroke="#d4af37" stroke-width="4" fill="none" />
                <path d="M 70,120 Q 100,160 130,120" fill="none" stroke="#0284c7" stroke-width="6" stroke-linecap="round" />
            </svg>
        </div>
    </div>

    <!-- Core Values -->
    <div class="row g-4" id="aboutValues" data-testid="about-values">
        <h3 class="fw-bold text-center text-success mb-4">Our Core Pillars</h3>
        <div class="col-md-4">
            <div class="card glass-card border-0 p-4 h-100 text-center">
                <div class="fs-1 text-success mb-2"><i class="bi bi-heart-pulse-fill"></i></div>
                <h5 class="fw-bold text-dark">Patient First</h5>
                <p class="text-muted small">Every clinical decisions, scheduling flow, and delivery promise centers entirely around the comfort of our patients.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card glass-card border-0 p-4 h-100 text-center">
                <div class="fs-1 text-primary mb-2"><i class="bi bi-shield-fill-check"></i></div>
                <h5 class="fw-bold text-dark">Genuine Quality</h5>
                <p class="text-muted small">We supply only certified pharmaceutical drugs, partner with accredited pathology labs, and list certified specialists.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card glass-card border-0 p-4 h-100 text-center">
                <div class="fs-1 text-warning mb-2"><i class="bi bi-lightning-charge-fill"></i></div>
                <h5 class="fw-bold text-dark">Lightning Speed</h5>
                <p class="text-muted small">From instant 24-hour express shipping of medicines to immediate 10-minute digital diagnostic report delivery.</p>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
