<?php
/**
 * Session Management System
 * ProFix Masters - Service Marketplace Platform
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

// Set session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

/**
 * Check if session is still valid
 * @return bool
 */
function isSessionValid() {
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        if (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }
    }
    $_SESSION['LAST_ACTIVITY'] = time();
    return true;
}

/**
 * Check if user is logged in (any role)
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['provider_id']) || isset($_SESSION['admin_id']);
}

/**
 * Check if logged in as user
 * @return bool
 */
function isUser() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'user';
}

/**
 * Check if logged in as provider
 * @return bool
 */
function isProvider() {
    return isset($_SESSION['provider_id']) && $_SESSION['role'] === 'provider';
}

/**
 * Check if logged in as admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['admin_id']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user/provider/admin ID
 * @return int|null
 */
function getCurrentUserId() {
    if (isUser()) return $_SESSION['user_id'];
    if (isProvider()) return $_SESSION['provider_id'];
    if (isAdmin()) return $_SESSION['admin_id'];
    return null;
}

/**
 * Get current user role
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Require user login
 * Redirects to user login page if not logged in as user
 */
function requireUserLogin() {
    if (!isSessionValid() || !isUser()) {
        header("Location: /user/login.php");
        exit();
    }
}

/**
 * Require provider login
 * Redirects to provider login page if not logged in as provider
 */
function requireProviderLogin() {
    if (!isSessionValid() || !isProvider()) {
        header("Location: /provider/login.php");
        exit();
    }
}

/**
 * Require admin login
 * Redirects to admin login page if not logged in as admin
 */
function requireAdminLogin() {
    if (!isSessionValid() || !isAdmin()) {
        header("Location: /admin/login.php");
        exit();
    }
}

/**
 * Set user session
 * @param int $user_id
 * @param string $full_name
 * @param string $email
 */
function setUserSession($user_id, $full_name, $email) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = 'user';
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;
    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * Set provider session
 * @param int $provider_id
 * @param string $full_name
 * @param string $email
 * @param string $verification_status
 */
function setProviderSession($provider_id, $full_name, $email, $verification_status) {
    session_regenerate_id(true);
    $_SESSION['provider_id'] = $provider_id;
    $_SESSION['role'] = 'provider';
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;
    $_SESSION['verification_status'] = $verification_status;
    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * Set admin session
 * @param int $admin_id
 * @param string $username
 * @param string $email
 */
function setAdminSession($admin_id, $username, $email) {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin_id;
    $_SESSION['role'] = 'admin';
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * Destroy current session and logout
 */
function destroySession() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Set flash message
 * @param string $message
 * @param string $type (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message
 * @return array|null ['message' => string, 'type' => string]
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type']
        ];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return $flash;
    }
    return null;
}
?>
