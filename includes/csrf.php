<?php
/**
 * ANVNA Care — CSRF Protection Helpers
 * Generates and validates stateless CSRF tokens stored in the PHP session.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate (or return cached) CSRF token for the current session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF input field for use inside HTML <form> tags.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validate the CSRF token from the incoming request.
 * Accepts the token from POST body, JSON body, or X-CSRF-Token header.
 *
 * @param array $data  Parsed POST / JSON data array
 * @return bool
 */
function csrf_valid(array $data = []): bool {
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (empty($sessionToken)) {
        return false;
    }

    // Priority: POST field > JSON body field > custom header
    $requestToken = $data['csrf_token']
        ?? $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';

    return hash_equals($sessionToken, $requestToken);
}

/**
 * Abort with 403 if CSRF validation fails.
 * Used at the top of mutating API endpoints.
 */
function csrf_protect(array $data = []): void {
    if (!csrf_valid($data)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid or missing CSRF token. Please refresh the page and try again.']);
        exit;
    }
}
