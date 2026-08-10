<?php
/**
 * Admin - Hapus Rute Antar Jemput
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check Auth
checkAdminAuth();

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        // Delete route (cascade constraint automatically removes vehicle options)
        $stmt = $pdo->prepare("DELETE FROM transfer_routes WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        // Log error
    }
}

header('Location: index.php?success=1');
exit;
