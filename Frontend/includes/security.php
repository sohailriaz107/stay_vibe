<?php
/**
 * Stay Vibes Resort - Security Helper
 * Implements CSRF protection, input sanitization, and session security.
 */

// Start session with secure settings if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // ini_set('session.cookie_secure', 1); // Enable this if using HTTPS
    session_start();
}

/**
 * Generate a CSRF token and store it in the session
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Sanitize user input (prevent XSS)
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Clean data for database (MySQLi)
 * Note: Always use prepared statements for queries.
 */
function db_escape($conn, $data) {
    return mysqli_real_escape_string($conn, $data);
}

/**
 * Check if user is authenticated
 */
function is_authenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect if not authenticated
 */
function require_auth($redirect_to = 'login.php') {
    if (!is_authenticated()) {
        header("Location: " . $redirect_to);
        exit();
    }
}

/**
 * Prevent frame injection (Clickjacking)
 */
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
?>
