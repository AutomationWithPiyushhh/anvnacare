<?php
require_once 'includes/header.php';
?>

<div class="container my-4">
    <div class="mb-4">
        <h2 class="fw-bold text-success" id="playgroundTitle" data-testid="playground-title"><i class="bi bi-cpu"></i> QA Automation Sandbox</h2>
        <p class="text-muted">A dedicated laboratory containing explicit widgets to practice Selenium, Playwright, and Cypress automation challenges.</p>
    </div>

    <div class="row g-4">
        <!-- 1. Native JS Alerts -->
        <div class="col-md-6">
            <div class="card glass-card border-0 p-4 h-100" id="alertsCard" data-testid="alerts-card">
                <h5 class="fw-bold text-success mb-3"><i class="bi bi-exclamation-octagon me-2"></i> JavaScript Popups & Alerts</h5>
                <p class="text-muted small">Practice handling native browser window alert dialogs, confirmations, and user prompt inputs.</p>
                
                <div class="d-grid gap-3">
                    <div>
                        <button class="btn btn-outline-success w-100" id="simpleAlertBtn" data-testid="simple-alert-btn" onclick="triggerSimpleAlert()">Trigger Simple Alert</button>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary w-100" id="confirmAlertBtn" data-testid="confirm-alert-btn" onclick="triggerConfirmAlert()">Trigger Confirmation Alert</button>
                        <div class="text-center mt-2 small text-muted">Result: <strong class="text-success" id="confirmResult" data-testid="confirm-result">None</strong></div>
                    </div>
                    <div>
                        <button class="btn btn-outline-warning text-dark w-100" id="promptAlertBtn" data-testid="prompt-alert-btn" onclick="triggerPromptAlert()">Trigger User Prompt Alert</button>
                        <div class="text-center mt-2 small text-muted">Result: <strong class="text-success" id="promptResult" data-testid="prompt-result">None</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Hidden Elements and Disabled Buttons -->
        <div class="col-md-6">
            <div class="card glass-card border-0 p-4 h-100" id="controlsCard" data-testid="controls-card">
                <h5 class="fw-bold text-success mb-3"><i class="bi bi-toggle-on me-2"></i> Hidden Fields & Disabled Buttons</h5>
                <p class="text-muted small">Practice toggling field visibility, working with hidden attributes, and checking disabled elements.</p>

                <!-- Hidden Input Box -->
                <div class="mb-4">
                    <button class="btn btn-sm btn-outline-secondary mb-2" id="toggleHiddenBtn" data-testid="toggle-hidden-btn" onclick="toggleHiddenInput()">Toggle Hidden Input</button>
                    <div id="hiddenInputContainer" class="d-none">
                        <input type="text" class="form-control" id="hiddenInput" placeholder="I was hidden!" data-testid="hidden-input">
                        <span class="small text-success mt-1 d-block"><i class="bi bi-eye-fill"></i> You found me!</span>
                    </div>
                </div>

                <hr>

                <!-- Disabled Button Checkbox -->
                <div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="enableButtonCheckbox" data-testid="enable-btn-checkbox" onchange="toggleButtonDisable(this)">
                        <label class="form-check-label small fw-bold" for="enableButtonCheckbox">
                            Accept terms to enable the submission button
                        </label>
                    </div>
                    <button class="btn btn-success w-100 py-2" id="sandboxSubmitBtn" data-testid="sandbox-submit-btn" disabled onclick="showToast('Submission accepted!', 'success')">
                        Disabled Submission Button
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. Drag and Drop Sandbox -->
        <div class="col-md-6">
            <div class="card glass-card border-0 p-4 h-100" id="dragDropCard" data-testid="drag-drop-card">
                <h5 class="fw-bold text-success mb-3"><i class="bi bi-cursor-move me-2"></i> Drag and Drop Action</h5>
                <p class="text-muted small">Practice dragging elements from one container and dropping them into another.</p>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="border rounded p-3 text-center bg-light" id="dragSourceZone" data-testid="drag-source-zone" style="min-height: 120px;">
                            <span class="small text-muted d-block mb-2">Source Box</span>
                            <div class="btn btn-success btn-sm cursor-grab" draggable="true" id="draggableItem" data-testid="draggable-item" ondragstart="drag(event)">
                                <i class="bi bi-capsule"></i> Drag Me!
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border border-success border-dashed rounded p-3 text-center" id="dragTargetZone" data-testid="drag-target-zone" ondragover="allowDrop(event)" ondrop="drop(event)" style="min-height: 120px; border: 2px dashed #0a6c42;">
                            <span class="small text-muted d-block mb-2">Drop Target Box</span>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3 small text-success d-none" id="dragSuccessMsg" data-testid="drag-success-msg">
                    <i class="bi bi-check-circle-fill"></i> Element dropped successfully!
                </div>
            </div>
        </div>

        <!-- 4. Windows & Tabs spawner -->
        <div class="col-md-6">
            <div class="card glass-card border-0 p-4 h-100" id="windowsCard" data-testid="windows-card">
                <h5 class="fw-bold text-success mb-3"><i class="bi bi-windows me-2"></i> Multiple Windows & Tabs</h5>
                <p class="text-muted small">Practice switching frames, windows, and browser tabs during automation executions.</p>

                <div class="d-flex flex-wrap gap-3">
                    <button class="btn btn-outline-success flex-grow-1" id="openNewWindowBtn" data-testid="open-new-window-btn" onclick="spawnNewWindow()">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Spawn Popup Window
                    </button>
                    
                    <a href="about.php" target="_blank" class="btn btn-outline-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1" id="openNewTabLink" data-testid="open-new-tab-link">
                        <i class="bi bi-plus-square me-1"></i> Open About Page in New Tab
                    </a>
                </div>

                <hr>

                <!-- Cookies and Storage check actions -->
                <div>
                    <h6 class="fw-bold text-dark mb-2">Browser Storage Verification</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light border text-dark" onclick="writeSampleCookie()" id="cookieWriteBtn" data-testid="cookie-write-btn">Write Test Cookie</button>
                        <button class="btn btn-sm btn-light border text-dark" onclick="writeSampleLocalStorage()" id="storageWriteBtn" data-testid="storage-write-btn">Write Local Storage</button>
                    </div>
                    <div class="mt-2 small text-muted" id="storageStatusText" data-testid="storage-status-text">Click buttons to populate mock credentials data.</div>
                </div>
            </div>
        </div>

        <!-- 5. IFrame Container challenge -->
        <div class="col-12">
            <div class="card glass-card border-0 p-4" id="iframeCard" data-testid="iframe-card">
                <h5 class="fw-bold text-success mb-3"><i class="bi bi-bounding-box-circles me-2"></i> Nested Inline Frames (IFrames)</h5>
                <p class="text-muted small">Switch context into the IFrame below to interact with the input form and buttons nested inside.</p>
                
                <div class="border rounded overflow-hidden" style="height: 180px;">
                    <iframe src="iframe-content.php" id="sandboxIframe" data-testid="sandbox-iframe" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 1. Alerts functions
function triggerSimpleAlert() {
    alert("This is a simple alert message from ANVNA Care sandbox!");
}

function triggerConfirmAlert() {
    const res = confirm("Do you agree to proceed with the medical test execution?");
    const display = document.getElementById('confirmResult');
    display.innerText = res ? "User clicked OK (True)" : "User clicked Cancel (False)";
}

function triggerPromptAlert() {
    const name = prompt("Please enter patient verification code (Enter: ANVNA):");
    const display = document.getElementById('promptResult');
    if (name === null) {
        display.innerText = "Cancelled";
    } else if (name.trim() === '') {
        display.innerText = "Empty string submitted";
    } else {
        display.innerText = name;
    }
}

// 2. Hidden elements
function toggleHiddenInput() {
    const container = document.getElementById('hiddenInputContainer');
    if (container.classList.contains('d-none')) {
        container.classList.remove('d-none');
    } else {
        container.classList.add('d-none');
    }
}

function toggleButtonDisable(checkbox) {
    const btn = document.getElementById('sandboxSubmitBtn');
    if (checkbox.checked) {
        btn.disabled = false;
        btn.innerText = "Enabled Submission Button";
    } else {
        btn.disabled = true;
        btn.innerText = "Disabled Submission Button";
    }
}

// 3. Drag and Drop handlers
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function allowDrop(ev) {
    ev.preventDefault();
}

function drop(ev) {
    ev.preventDefault();
    const data = ev.dataTransfer.getData("text");
    const item = document.getElementById(data);
    const target = ev.target.closest('#dragTargetZone');
    
    if (target && item) {
        target.appendChild(item);
        document.getElementById('dragSuccessMsg').classList.remove('d-none');
        showToast('Element successfully dragged and dropped!', 'success');
    }
}

// 4. Spawn Window
function spawnNewWindow() {
    window.open('faq.php', 'ANVNAPopup', 'width=600,height=500,left=100,top=100,resizable=yes,scrollbars=yes');
}

// Write Cookie / Storage
function writeSampleCookie() {
    document.cookie = "sandbox_session_id=ANVNA-COOKIE-XYZ987; max-age=3600; path=/";
    document.getElementById('storageStatusText').innerText = "Successfully wrote cookie 'sandbox_session_id'";
    showToast('Cookie saved.', 'success');
}

function writeSampleLocalStorage() {
    localStorage.setItem('sandbox_user_token', 'ANVNA-LOCAL-STORAGE-TOKEN-123456');
    document.getElementById('storageStatusText').innerText = "Successfully wrote Local Storage key 'sandbox_user_token'";
    showToast('Local Storage saved.', 'success');
}
</script>

<?php
require_once 'includes/footer.php';
?>
