<?php
require_once 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card glass-card border-0 p-4" id="termsCard" data-testid="terms-card">
                <h3 class="fw-bold text-success mb-3" id="termsHeader" data-testid="terms-header">Terms & Conditions</h3>
                <p class="text-muted small">Please scroll down to the bottom of the terms container to accept our terms agreements.</p>

                <!-- Scrollable terms container (QA scroll challenge) -->
                <div class="border p-3 rounded mb-4 text-secondary small bg-light" id="termsScrollBox" data-testid="terms-scrollbox" style="overflow-y: scroll; height: 250px;">
                    <h6 class="fw-bold text-dark">1. Acceptance of Terms</h6>
                    <p>By accessing the website at https://anvnacare.com ("ANVNA Care"), you are agreeing to be bound by these terms of service, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local laws.</p>
                    
                    <h6 class="fw-bold text-dark mt-3">2. Mock Platform Limitations</h6>
                    <p>ANVNA Care is a simulated sandbox platform built specifically for QA test automation students. It does not sell real pharmaceutical medicines, schedule actual professional doctor consultations, or arrange home sample collection diagnostic tests. No real credit card details should be used here.</p>

                    <h6 class="fw-bold text-dark mt-3">3. User Credentials & Safety</h6>
                    <p>You are responsible for safeguarding the credentials you use to access the service. Do not enter any real-world production passwords or confidential clinical health history documents on this site.</p>

                    <h6 class="fw-bold text-dark mt-3">4. Intellectual Property</h6>
                    <p>The materials contained in this website are protected by applicable copyright and trademark law. Any reproduction of the leaf-gold branding layout is strictly limited to learning sandboxes.</p>

                    <h6 class="fw-bold text-dark mt-3">5. Disclaimer of Warranties</h6>
                    <p>The materials on ANVNA Care's website are provided on an 'as is' basis. ANVNA Care makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
                    
                    <h6 class="fw-bold text-dark mt-3">6. Governing Law</h6>
                    <p>These terms and conditions are governed by and construed in accordance with the laws of California, and you irrevocably submit to the exclusive jurisdiction of the courts in that State or location.</p>
                    
                    <p class="fw-bold text-success mt-4">--- END OF TERMS OF AGREEMENT ---</p>
                </div>

                <!-- Checkbox and Button (disabled initially until scrolled) -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agreeCheckbox" data-testid="agree-checkbox" disabled>
                        <label class="form-check-label small fw-bold text-dark" for="agreeCheckbox">
                            I have read, understood, and agree to the terms of service document.
                        </label>
                    </div>
                </div>

                <button class="btn btn-success w-100 py-2" id="termsSubmitBtn" data-testid="terms-submit-btn" disabled onclick="showToast('Thank you for accepting our terms!', 'success')">
                    Submit Terms Acceptance
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const scrollBox = document.getElementById('termsScrollBox');
    const checkbox = document.getElementById('agreeCheckbox');
    const btn = document.getElementById('termsSubmitBtn');

    if (scrollBox && checkbox) {
        scrollBox.addEventListener('scroll', function () {
            // Check if user scrolled to bottom (allow 5px buffer)
            const isAtBottom = scrollBox.scrollHeight - scrollBox.scrollTop <= scrollBox.clientHeight + 5;
            
            if (isAtBottom) {
                checkbox.disabled = false;
                showToast('Scroll complete. You can now accept the terms check.', 'info');
            }
        });
        
        checkbox.addEventListener('change', function () {
            btn.disabled = !checkbox.checked;
        });
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
