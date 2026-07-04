<?php
// Logout Script
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear Session Variables
$_SESSION = [];

// Destroy Session Cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear "Remember Me" Cookie
setcookie('remember_user', '', time() - 3600, "/");

// Destroy Session
session_destroy();

// Redirect to Home page
header("Location: index.php");
exit;
