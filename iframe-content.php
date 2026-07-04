<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IFrame Content Sandbox</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 15px;
        }
    </style>
</head>
<body>

<div class="card p-3 border-0 shadow-sm">
    <h6 class="fw-bold text-success mb-2"><i class="bi bi-shield-lock-fill"></i> Secure IFrame Form</h6>
    <form id="iframeForm" onsubmit="submitIframeForm(event)" novalidate>
        <div class="input-group input-group-sm">
            <input type="text" class="form-control" id="iframeInput" name="iframeText" placeholder="Type text inside IFrame" required data-testid="iframe-input">
            <button class="btn btn-success" type="submit" id="iframeSubmitBtn" data-testid="iframe-submit-btn">Submit inside Frame</button>
        </div>
        <div id="iframeSuccessMessage" class="small text-success mt-2 d-none" data-testid="iframe-success-message">
            Success! Input processed inside nested frame.
        </div>
    </form>
</div>

<script>
function submitIframeForm(event) {
    event.preventDefault();
    const input = document.getElementById('iframeInput');
    const msg = document.getElementById('iframeSuccessMessage');
    
    if (input.value.trim() !== '') {
        msg.classList.remove('d-none');
        input.classList.remove('is-invalid');
    } else {
        input.classList.add('is-invalid');
    }
}
</script>

</body>
</html>
