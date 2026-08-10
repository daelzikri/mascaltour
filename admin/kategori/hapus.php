<?php
/**
 * Admin - Hapus Kategori Wilayah Wisata
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check Auth
checkAdminAuth();

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        // Fetch image path to delete from disk
        $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();

        if (!empty($image) && file_exists(__DIR__ . '/../../' . $image)) {
            @unlink(__DIR__ . '/../../' . $image);
        }

        // Delete from database (foreign key cascades will automatically delete related packages)
        $del_stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $del_stmt->execute([$id]);

    } catch (PDOException $e) {
        // Log error
    }
}

header('Location: index.php?success=1');
exit;
