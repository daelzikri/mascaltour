<?php
/**
 * XML Sitemap Generator for Search Engines (Google, Bing, Yandex, etc.)
 */
header('Content-Type: application/xml; charset=utf-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= BASE_URL ?></loc>
        <priority>1.0</priority>
        <changefreq>daily</changefreq>
    </url>
    <url>
        <loc><?= BASE_URL ?>paket-wisata.php</loc>
        <priority>0.9</priority>
        <changefreq>daily</changefreq>
    </url>
    <url>
        <loc><?= BASE_URL ?>antar-jemput.php</loc>
        <priority>0.9</priority>
        <changefreq>daily</changefreq>
    </url>
    <url>
        <loc><?= BASE_URL ?>rental.php</loc>
        <priority>0.9</priority>
        <changefreq>daily</changefreq>
    </url>
<?php
try {
    $packages = $pdo->query("SELECT slug FROM packages WHERE status = 'published'")->fetchAll();
    foreach ($packages as $p) {
        echo "    <url>\n";
        echo "        <loc>" . BASE_URL . "paket-wisata-detail.php?slug=" . urlencode($p['slug']) . "</loc>\n";
        echo "        <priority>0.8</priority>\n";
        echo "        <changefreq>weekly</changefreq>\n";
        echo "    </url>\n";
    }

    $vehicles = $pdo->query("SELECT slug FROM vehicles WHERE status = 'published'")->fetchAll();
    foreach ($vehicles as $v) {
        echo "    <url>\n";
        echo "        <loc>" . BASE_URL . "rental-detail.php?slug=" . urlencode($v['slug']) . "</loc>\n";
        echo "        <priority>0.8</priority>\n";
        echo "        <changefreq>weekly</changefreq>\n";
        echo "    </url>\n";
    }

    $routes = $pdo->query("SELECT slug FROM transfer_routes WHERE status = 'published'")->fetchAll();
    foreach ($routes as $r) {
        echo "    <url>\n";
        echo "        <loc>" . BASE_URL . "antar-jemput-detail.php?slug=" . urlencode($r['slug']) . "</loc>\n";
        echo "        <priority>0.8</priority>\n";
        echo "        <changefreq>weekly</changefreq>\n";
        echo "    </url>\n";
    }
} catch (Exception $e) {
    // Ignore db errors in sitemap
}
?>
</urlset>
