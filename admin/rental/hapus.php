<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

checkAdminAuth();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $pdo->beginTransaction();
        $stmt_ph = $pdo->prepare("SELECT image_url FROM vehicle_photos WHERE vehicle_id = ?");
        $stmt_ph->execute([$id]);
        foreach ($stmt_ph->fetchAll() as $photo) {
            $img_path = __DIR__ . '/../../' . $photo['image_url'];
            if (file_exists($img_path)) @unlink($img_path);
        }
        $pdo->prepare("DELETE FROM vehicles WHERE id = ?")->execute([$id]);
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
    }
}
header('Location: index.php?success=1');
exit;
