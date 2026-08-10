<?php
/**
 * Public Website Header Component
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$site_name = getSetting('site_name', 'Lombok Travel Agency');
$site_tagline = getSetting('site_tagline', 'Jelajahi Keindahan Lombok');
$site_description_setting = getSetting('site_description', getSetting('hero_subtitle', 'Layanan Paket Wisata & Rental Mobil Premium Terpercaya di Lombok'));

// Dynamically set page title and description if not set
if (!isset($page_title)) {
    $page_title = $site_name . ($site_tagline ? ' - ' . $site_tagline : '');
}
if (!isset($page_description)) {
    $page_description = $site_description_setting;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= e($page_title) ?></title>
    <meta name="description" content="<?= e($page_description) ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="<?= e($site_name) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($page_title) ?>">
    <meta property="og:description" content="<?= e($page_description) ?>">
    <meta property="og:url" content="<?= BASE_URL ?>">
    <meta property="og:image" content="<?= BASE_URL ?>assets/img/og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?= e($page_title) ?>">
    <meta property="twitter:description" content="<?= e($page_description) ?>">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                            950: '#042f2e',
                        },
                        accent: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .text-gradient {
            background: linear-gradient(135deg, #0d9488 0%, #115e59 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-gradient {
            background: linear-gradient(180deg, rgba(4, 47, 46, 0.85) 0%, rgba(4, 47, 46, 0.4) 60%, rgba(248, 250, 252, 1) 100%);
        }
    </style>
</head>
<body class="text-slate-800 antialiased overflow-x-hidden">
