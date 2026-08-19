<?php
/**
 * Configuration File
 * Mascal Tour & Car Rental Catalog Website
 */

// Load server/local specific database credentials if config.local.php exists (ignored in Git)
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// Fallback Database Credentials (used if not defined in config.local.php)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'lombok_travel');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Base URL configuration (auto-detects domain & subfolder)
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];

    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/') : '';
    $dirPath = rtrim(str_replace('\\', '/', realpath(__DIR__)), '/');
    
    $basePath = '/';
    if (!empty($docRoot) && !empty($dirPath) && strpos($dirPath, $docRoot) === 0) {
        $subfolder = trim(substr($dirPath, strlen($docRoot)), '/');
        $basePath = $subfolder ? '/' . $subfolder . '/' : '/';
    }

    define('BASE_URL', $protocol . $domain . $basePath);
} else {
    define('BASE_URL', '/');
}

// Upload Path configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');
