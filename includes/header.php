<?php
/**
 * Public Website Header Component
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$site_name = getSetting('site_name', 'MascalTour Lombok');
$site_tagline = getSetting('site_tagline', 'Paket Wisata, Rental Mobil & Antar Jemput Bandara Lombok');
$site_description_setting = getSetting('site_description', getSetting('hero_subtitle', 'Layanan Paket Wisata, Rental Mobil & Transfer Antar Jemput Bandara Lombok Terpercaya.'));

// Dynamically set page title, description, keywords, and canonical URL if not set
if (!isset($page_title)) {
    $page_title = $site_name . ($site_tagline ? ' - ' . $site_tagline : '');
}
if (!isset($page_description)) {
    $page_description = $site_description_setting;
}
if (!isset($page_keywords)) {
    $page_keywords = 'paket wisata lombok, tour lombok, rental mobil lombok, sewa mobil lombok, antar jemput bandara lombok, transfer airport lombok, lombok travel, mascaltour, tour gili trawangan, pantai kuta lombok, sewa avanza lombok, sewa innova lombok';
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$current_url = isset($_SERVER['HTTP_HOST']) ? $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : BASE_URL;
$og_image = isset($page_og_image) ? $page_og_image : BASE_URL . 'favicon-96x96.png';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= e($page_title) ?></title>
    <meta name="description" content="<?= e($page_description) ?>">
    <meta name="keywords" content="<?= e($page_keywords) ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="<?= e($site_name) ?>">
    <link rel="canonical" href="<?= e($current_url) ?>">

    <!-- Favicon Icons & App Manifest -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon.svg" />
    <link rel="shortcut icon" href="<?= BASE_URL ?>favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MascalTour" />
    <link rel="manifest" href="<?= BASE_URL ?>site.webmanifest" />
    <meta name="theme-color" content="#ffffff" />
    
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($site_name) ?>">
    <meta property="og:title" content="<?= e($page_title) ?>">
    <meta property="og:description" content="<?= e($page_description) ?>">
    <meta property="og:url" content="<?= e($current_url) ?>">
    <meta property="og:image" content="<?= e($og_image) ?>">
    <meta property="og:locale" content="id_ID">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($page_title) ?>">
    <meta name="twitter:description" content="<?= e($page_description) ?>">
    <meta name="twitter:image" content="<?= e($og_image) ?>">

    <!-- Schema.org JSON-LD Structured Data for Google Search Crawler -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "TravelAgency",
      "name": <?= json_encode($site_name) ?>,
      "description": <?= json_encode($page_description) ?>,
      "url": <?= json_encode(BASE_URL) ?>,
      "telephone": <?= json_encode(getSetting('contact_phone', '+6281234567890')) ?>,
      "priceRange": "$$",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Lombok",
        "addressRegion": "Nusa Tenggara Barat",
        "addressCountry": "ID"
      }
    }
    </script>

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
