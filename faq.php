<?php
require_once 'includes/header.php';
?>

<div class="container my-4">
    <!-- Header banner -->
    <div class="mb-5 text-center">
        <h2 class="fw-bold text-success" id="faqTitle" data-testid="faq-title">Frequently Asked Questions</h2>
        <p class="text-muted">Quick answers to common questions about doctor bookings, diagnostic lab tests, and pharmacy orders</p>
    </div>

    <!-- Accordion Section (QA Accordions challenge) -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="accordion glass-card border-0 p-4" id="faqAccordion" data-testid="faq-accordion">
                
                <!-- Q1 -->
                <div class="accordion-item bg-transparent border-bottom py-2" data-testid="faq-item">
                    <h2 class="accordion-header" id="faqHeader-1">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse-1" aria-expanded="false" aria-controls="faqCollapse-1" id="faqBtn-1" data-testid="faq-btn-1">
                            1. How do I order prescription medicines?
                        </button>
                    </h2>
                    <div id="faqCollapse-1" class="accordion-collapse collapse" aria-labelledby="faqHeader-1" data-bs-parent="#faqAccordion" data-testid="faq-panel-1">
                        <div class="accordion-body text-secondary small">
                            Prescription medicines are marked with a yellow "Prescription Required" alert. To order them, go to the medicine details page, click on the upload zone to choose your prescription file (PDF/JPG), let the progress loader finish verification, and click "Add to Cart".
                        </div>
                    </div>
                </div>

                <!-- Q2 -->
                <div class="accordion-item bg-transparent border-bottom py-2" data-testid="faq-item">
                    <h2 class="accordion-header" id="faqHeader-2">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse-2" aria-expanded="false" aria-controls="faqCollapse-2" id="faqBtn-2" data-testid="faq-btn-2">
                            2. What details are required for the dummy payment sandbox?
                        </button>
                    </h2>
                    <div id="faqCollapse-2" class="accordion-collapse collapse" aria-labelledby="faqHeader-2" data-bs-parent="#faqAccordion" data-testid="faq-panel-2">
                        <div class="accordion-body text-secondary small">
                            For testing the checkout payment wizard, use Card: <strong>4111 1111 1111 1111</strong>, Expiry: <strong>12/30</strong>, CVV: <strong>123</strong>, and OTP: <strong>123456</strong>. These fields are strictly validated on the frontend.
                        </div>
                    </div>
                </div>

                <!-- Q3 -->
                <div class="accordion-item bg-transparent border-bottom py-2" data-testid="faq-item">
                    <h2 class="accordion-header" id="faqHeader-3">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse-3" aria-expanded="false" aria-controls="faqCollapse-3" id="faqBtn-3" data-testid="faq-btn-3">
                            3. Can I cancel a booked doctor consultation?
                        </button>
                    </h2>
                    <div id="faqCollapse-3" class="accordion-collapse collapse" aria-labelledby="faqHeader-3" data-bs-parent="#faqAccordion" data-testid="faq-panel-3">
                        <div class="accordion-body text-secondary small">
                            Yes! Go to your Patient Dashboard or My Appointments tab, locate the upcoming appointment card, and click "Cancel Consultation". You will be prompted with a native browser Confirmation dialog to verify the cancellation.
                        </div>
                    </div>
                </div>

                <!-- Q4 -->
                <div class="accordion-item bg-transparent border-bottom py-2" data-testid="faq-item">
                    <h2 class="accordion-header" id="faqHeader-4">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse-4" aria-expanded="false" aria-controls="faqCollapse-4" id="faqBtn-4" data-testid="faq-btn-4">
                            4. Where can I find all the QA Automation challenges in one place?
                        </button>
                    </h2>
                    <div id="faqCollapse-4" class="accordion-collapse collapse" aria-labelledby="faqHeader-4" data-bs-parent="#faqAccordion" data-testid="faq-panel-4">
                        <div class="accordion-body text-secondary small">
                            Navigate to the "QA Playground" tab in the main header navigation menu. It has specific sections covering alerts, nested IFrames, drag-and-drop boxes, window popups, local storage cookies, and custom progress widgets.
                        </div>
                    </div>
                </div>

                <!-- Q5 -->
                <div class="accordion-item bg-transparent py-2" data-testid="faq-item">
                    <h2 class="accordion-header" id="faqHeader-5">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse-5" aria-expanded="false" aria-controls="faqCollapse-5" id="faqBtn-5" data-testid="faq-btn-5">
                            5. How does the Infinite Scroll mode work on Pharmacy page?
                        </button>
                    </h2>
                    <div id="faqCollapse-5" class="accordion-collapse collapse" aria-labelledby="faqHeader-5" data-bs-parent="#faqAccordion" data-testid="faq-panel-5">
                        <div class="accordion-body text-secondary small">
                            When "Enable Infinite Scroll" is toggled on in the Pharmacy page, standard pagination is hidden. As you scroll down the page, next-page items are automatically requested via JSON API calls and appended dynamically to the container, demonstrating infinite scroll automation scripts.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
