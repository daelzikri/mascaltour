<?php
/**
 * Configuration File
 * Lombok Travel Agency & Car Rental Catalog Website
 */

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'lombok_travel');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL configuration (handles subfolder in localhost)
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . $domain . '/lombok-travel/');
} else {
    define('BASE_URL', 'http://localhost/lombok-travel/');
}

// Upload Path configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');
