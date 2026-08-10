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
 * Upload an image with security validation, size limit (max 2MB),
 * and automatic conversion to WebP format with transparency preservation.
 * 
 * @param array $fileInput Single element from $_FILES
 * @param string $targetFolder Subfolder name inside uploads/ (e.g., 'packages', 'vehicles', 'settings', 'categories')
 * @param int $maxSizeBytes Maximum allowed file size in bytes (default 2MB = 2,097,152 bytes)
 * @return string|null Path to the uploaded file relative to project root, or null if no file uploaded
 * @throws Exception if file exceeds 2MB or has an invalid format
 */
function uploadImage($fileInput, $targetFolder, $maxSizeBytes = 2097152) {
    if (!isset($fileInput) || !is_array($fileInput) || ($fileInput['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $tmpPath = $fileInput['tmp_name'];
    $fileSize = $fileInput['size'] ?? 0;
    
    if (!is_uploaded_file($tmpPath)) {
        return null;
    }
    
    // 1. Validate file size (Max 2MB)
    if ($fileSize > $maxSizeBytes) {
        throw new Exception('Ukuran file foto terlalu besar. Maksimal 2 MB.');
    }
    
    // 2. Validate MIME type
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime = mime_content_type($tmpPath);
    if (!in_array($mime, $allowedMimes)) {
        throw new Exception('Format gambar tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.');
    }
    
    // Prepare target directory
    $targetDir = __DIR__ . '/../uploads/' . trim($targetFolder, '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // 3. Attempt GD WebP conversion with Truecolor Canvas
    $srcImg = null;
    switch ($mime) {
        case 'image/jpeg':
            $srcImg = @imagecreatefromjpeg($tmpPath);
            break;
        case 'image/png':
            $srcImg = @imagecreatefrompng($tmpPath);
            break;
        case 'image/webp':
            $srcImg = @imagecreatefromwebp($tmpPath);
            break;
        case 'image/gif':
            $srcImg = @imagecreatefromgif($tmpPath);
            break;
    }
    
    if ($srcImg) {
        $width = imagesx($srcImg);
        $height = imagesy($srcImg);

        // Create a truecolor canvas to safely process palette/transparent images
        $truecolor = imagecreatetruecolor($width, $height);
        imagealphablending($truecolor, false);
        imagesavealpha($truecolor, true);
        
        $transparent = imagecolorallocatealpha($truecolor, 0, 0, 0, 127);
        imagefilledrectangle($truecolor, 0, 0, $width, $height, $transparent);
        imagecopy($truecolor, $srcImg, 0, 0, 0, 0, $width, $height);

        $newName = uniqid('img_') . '_' . time() . '.webp';
        $destination = $targetDir . '/' . $newName;

        $saved = @imagewebp($truecolor, $destination, 85);
        imagedestroy($truecolor);
        imagedestroy($srcImg);

        if ($saved && file_exists($destination) && filesize($destination) > 0) {
            return 'uploads/' . trim($targetFolder, '/') . '/' . $newName;
        }
    }

    // 4. Fallback: Save original file directly if WebP conversion fails
    $ext = 'jpg';
    if ($mime === 'image/png') $ext = 'png';
    elseif ($mime === 'image/webp') $ext = 'webp';
    elseif ($mime === 'image/gif') $ext = 'gif';

    $fallbackName = uniqid('img_') . '_' . time() . '.' . $ext;
    $fallbackDestination = $targetDir . '/' . $fallbackName;

    if (move_uploaded_file($tmpPath, $fallbackDestination)) {
        return 'uploads/' . trim($targetFolder, '/') . '/' . $fallbackName;
    }
    
    throw new Exception('Gagal menyimpan file gambar.');
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
