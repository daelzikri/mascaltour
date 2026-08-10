<?php
/**
 * Global Helper Functions
 */
require_once __DIR__ . '/db.php';

/**
 * Generate clean URL-friendly slugs from strings
 */
function generateSlug($string) {
    // Convert to lowercase and trim
    $slug = strtolower(trim($string));
    // Replace non-alphanumeric characters with hyphens
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    // Trim duplicate/leading/trailing hyphens
    return trim(preg_replace('/-+/', '-', $slug), '-');
}

/**
 * Generate a prefilled WhatsApp booking link
 */
function generateWaLink($waNumber, $message) {
    // Clean WhatsApp number (remove +, spaces, hyphens)
    $cleanNumber = preg_replace('/[^0-9]/', '', $waNumber);
    // If number starts with 0, convert to 62
    if (strpos($cleanNumber, '0') === 0) {
        $cleanNumber = '62' . substr($cleanNumber, 1);
    }
    return "https://wa.me/{$cleanNumber}?text=" . urlencode($message);
}

/**
 * Upload an image with security validation (MIME-type check)
 * 
 * @param array $fileInput Array element from $_FILES
 * @param string $targetFolder Folder name inside uploads/ (e.g., 'packages')
 * @return string|null Path to the uploaded file relative to project root, or null if no upload
 * @throws Exception if file type is invalid
 */
function uploadImage($fileInput, $targetFolder) {
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    
    if (!isset($fileInput) || $fileInput['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $tmpPath = $fileInput['tmp_name'];
    
    // Validate file existence and mime type
    if (!is_uploaded_file($tmpPath)) {
        return null;
    }
    
    $mime = mime_content_type($tmpPath);
    if (!in_array($mime, $allowedMimes)) {
        throw new Exception('Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.');
    }
    
    // Determine extension
    $ext = 'jpg';
    if ($mime === 'image/png') {
        $ext = 'png';
    } elseif ($mime === 'image/webp') {
        $ext = 'webp';
    }
    
    // Generate unique name
    $newName = uniqid('img_') . '.' . $ext;
    
    // Create destination folder if not exists
    $targetDir = __DIR__ . '/../uploads/' . $targetFolder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $destination = $targetDir . '/' . $newName;
    if (move_uploaded_file($tmpPath, $destination)) {
        return 'uploads/' . $targetFolder . '/' . $newName;
    }
    
    return null;
}

/**
 * Format integer to Indonesian Rupiah currency format
 */
function formatRupiah($number) {
    if ($number === null || $number === '') return 'Hubungi Kami';
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Get setting value by key (with static caching to prevent repeat queries)
 */
function getSetting($key, $default = '') {
    global $pdo;
    static $settings = null;
    
    if ($settings === null) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            $settings = [];
        }
    }
    
    // Key aliases for backward compatibility and fallback mapping
    $aliases = [
        'whatsapp_number' => ['contact_whatsapp', 'whatsapp_number'],
        'contact_whatsapp' => ['contact_whatsapp', 'whatsapp_number'],
        'email' => ['contact_email', 'email'],
        'contact_email' => ['contact_email', 'email'],
        'office_address' => ['address', 'office_address'],
        'address' => ['address', 'office_address'],
        'google_maps_embed_url' => ['address_maps_url', 'google_maps_embed_url'],
        'address_maps_url' => ['address_maps_url', 'google_maps_embed_url'],
        'hero_headline' => ['hero_title', 'hero_headline'],
        'hero_title' => ['hero_title', 'hero_headline'],
        'hero_subheadline' => ['hero_subtitle', 'hero_subheadline'],
        'hero_subtitle' => ['hero_subtitle', 'hero_subheadline'],
    ];

    if (isset($aliases[$key])) {
        foreach ($aliases[$key] as $aliasKey) {
            if (isset($settings[$aliasKey]) && trim($settings[$aliasKey]) !== '') {
                return $settings[$aliasKey];
            }
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Helper to escape output for safe rendering in HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
