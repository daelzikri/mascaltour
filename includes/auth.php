<?php
/**
 * Session-based Admin Authentication Guard
 */
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    // Set secure session cookie parameters if possible
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_start();
}

/**
 * Check if the admin is logged in.
 * Redirects to the login page if not authenticated.
 */
function checkAdminAuth() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
    
    // Simple session hijacking check
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        logoutAdmin();
    }
}

/**
 * Log in an admin user and regenerate session ID to prevent fixation.
 * 
 * @param array $user User data from the database
 */
function loginAdmin($user) {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_name'] = $user['name'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['last_activity'] = time();
}

/**
 * Log out the admin and destroy the session.
 */
function logoutAdmin() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}
