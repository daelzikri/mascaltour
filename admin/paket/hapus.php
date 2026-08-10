<?php
/**
 * Admin - Hapus Paket Wisata
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check Auth
checkAdminAuth();

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $pdo->beginTransaction();

        // 1. Fetch related photos to unlink from disk
        $stmt_ph = $pdo->prepare("SELECT image_url FROM package_photos WHERE package_id = ?");
        $stmt_ph->execute([$id]);
        $photos = $stmt_ph->fetchAll();

        foreach ($photos as $photo) {
            $img_path = __DIR__ . '/../../' . $photo['image_url'];
            if (file_exists($img_path)) {
                @unlink($img_path);
            }
        }

        // 2. Delete package (cascades database deletions for package_highlights, itinerary_items, package_inclusions, package_prices, package_photos)
        $stmt_del = $pdo->prepare("DELETE FROM packages WHERE id = ?");
        $stmt_del->execute([$id]);

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        // Log database error
    }
}

header('Location: index.php?success=1');
exit;
