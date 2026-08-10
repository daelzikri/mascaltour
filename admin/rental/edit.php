<?php
/**
 * Admin - Edit Kendaraan Rental
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$error = '';
try {
    $cat_stmt = $pdo->query("SELECT id, name FROM vehicle_categories ORDER BY sort_order ASC, name ASC");
    $categories = $cat_stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch();
    if (!$vehicle) { header('Location: index.php'); exit; }

    $stmt_ph = $pdo->prepare("SELECT * FROM vehicle_photos WHERE vehicle_id = ? ORDER BY sort_order ASC");
    $stmt_ph->execute([$id]);
    $photos = $stmt_ph->fetchAll();
} catch (PDOException $e) {
    die("Kesalahan sistem: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $vehicle_category_id = (int)($_POST['vehicle_category_id'] ?? 0);
    $seats = $_POST['seats'] !== '' ? (int)$_POST['seats'] : null;
    $transmission = $_POST['transmission'] !== '' ? $_POST['transmission'] : null;
    $fuel_type = trim($_POST['fuel_type'] ?? '');
    $price_lepas_kunci = $_POST['price_lepas_kunci'] !== '' ? (int)$_POST['price_lepas_kunci'] : null;
    $price_dengan_supir = $_POST['price_dengan_supir'] !== '' ? (int)$_POST['price_dengan_supir'] : null;
    $terms_text = trim($_POST['terms_text'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if (empty($name) || $vehicle_category_id <= 0) {
        $error = 'Nama Kendaraan dan Tipe Kategori wajib diisi/dipilih.';
    } else {
        try {
            $pdo->beginTransaction();

            $slug = $vehicle['slug'];
            if ($name !== $vehicle['name']) {
                $slug = generateSlug($name);
                $chk = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE slug = ? AND id != ?");
                $chk->execute([$slug, $id]);
                if ($chk->fetchColumn() > 0) $slug = $slug . '-' . time();
            }

            $stmt = $pdo->prepare("UPDATE vehicles SET vehicle_category_id=?, name=?, slug=?, seats=?, transmission=?, fuel_type=?, price_lepas_kunci=?, price_dengan_supir=?, terms_text=?, status=? WHERE id=?");
            $stmt->execute([$vehicle_category_id, $name, $slug, $seats, $transmission, $fuel_type ?: null, $price_lepas_kunci, $price_dengan_supir, $terms_text ?: null, $status, $id]);

            // Process photo deletions
            if (!empty($_POST['delete_photos'])) {
                $stmtDelPhoto = $pdo->prepare("DELETE FROM vehicle_photos WHERE id = ? AND vehicle_id = ?");
                foreach ($_POST['delete_photos'] as $photo_id) {
                    $photo_id = (int)$photo_id;
                    $img_stmt = $pdo->prepare("SELECT image_url FROM vehicle_photos WHERE id = ? LIMIT 1");
                    $img_stmt->execute([$photo_id]);
                    $img_url = $img_stmt->fetchColumn();
                    if (!empty($img_url) && file_exists(__DIR__ . '/../../' . $img_url)) @unlink(__DIR__ . '/../../' . $img_url);
                    $stmtDelPhoto->execute([$photo_id, $id]);
                }
            }

            // New photos
            if (!empty($_FILES['photos']['name'][0])) {
                $stmtPhoto = $pdo->prepare("INSERT INTO vehicle_photos (vehicle_id, image_url, sort_order) VALUES (?, ?, ?)");
                $stmt_max = $pdo->prepare("SELECT MAX(sort_order) FROM vehicle_photos WHERE vehicle_id = ?");
                $stmt_max->execute([$id]);
                $max_order = (int)$stmt_max->fetchColumn();

                foreach ($_FILES['photos']['name'] as $i => $name) {
                    if (empty($name) || ($_FILES['photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

                    $fileItem = [
                        'name' => $_FILES['photos']['name'][$i],
                        'type' => $_FILES['photos']['type'][$i] ?? '',
                        'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                        'error' => $_FILES['photos']['error'][$i],
                        'size' => $_FILES['photos']['size'][$i] ?? 0,
                    ];

                    $imgUrl = uploadImage($fileItem, 'vehicles');
                    if ($imgUrl) {
                        $stmtPhoto->execute([$id, $imgUrl, ++$max_order]);
                    }
                }
            }

            $pdo->commit();
            header('Location: index.php?success=1');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal memperbarui kendaraan: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-6">
    <a href="index.php" class="text-xs text-primary-400 hover:underline flex items-center gap-1 mb-2">&larr; Kembali</a>
    <h1 class="font-outfit text-2xl font-extrabold text-white">Ubah Kendaraan Rental</h1>
    <p class="text-xs text-slate-400 mt-1">Mengubah: <span class="text-white font-bold"><?= e($vehicle['name']) ?></span></p>
</div>

<?php if (!empty($error)): ?><div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6"><?= e($error) ?></div><?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Informasi Kendaraan</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Kendaraan</label>
                <input type="text" name="name" required value="<?= e($vehicle['name']) ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tipe Kategori</label>
                <select name="vehicle_category_id" required class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (int)$vehicle['vehicle_category_id'] === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jumlah Kursi</label>
                <input type="number" name="seats" min="1" max="60" value="<?= e($vehicle['seats'] ?? '') ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Transmisi</label>
                <select name="transmission" class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="matic" <?= $vehicle['transmission'] === 'matic' ? 'selected' : '' ?>>Matic</option>
                    <option value="manual" <?= $vehicle['transmission'] === 'manual' ? 'selected' : '' ?>>Manual</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Bahan Bakar</label>
                <input type="text" name="fuel_type" value="<?= e($vehicle['fuel_type'] ?? '') ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tarif Lepas Kunci (Rp/Hari)</label>
                <input type="number" name="price_lepas_kunci" min="0" value="<?= e($vehicle['price_lepas_kunci'] ?? '') ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tarif Dengan Supir &amp; BBM (Rp/Hari)</label>
                <input type="number" name="price_dengan_supir" min="0" value="<?= e($vehicle['price_dengan_supir'] ?? '') ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Syarat &amp; Ketentuan Sewa</label>
            <textarea name="terms_text" rows="5"
                class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm"><?= e($vehicle['terms_text'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status Publikasi</label>
                <select name="status" class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
                    <option value="draft" <?= $vehicle['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $vehicle['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tambah Foto Baru</label>
                <input type="file" name="photos[]" multiple accept="image/*"
                    class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 cursor-pointer">
            </div>
        </div>

        <?php if (!empty($photos)): ?>
        <div class="pt-4 border-t border-slate-900">
            <p class="text-xs font-semibold uppercase text-slate-400 mb-4">Pilih Foto untuk Dihapus</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($photos as $photo): ?>
                    <div class="relative rounded-xl overflow-hidden border border-slate-850 bg-slate-900">
                        <div class="aspect-video w-full overflow-hidden">
                            <img src="<?= BASE_URL . $photo['image_url'] ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="p-2 flex items-center gap-1.5 text-xs text-slate-400">
                            <input type="checkbox" name="delete_photos[]" value="<?= $photo['id'] ?>" class="rounded">
                            <label class="cursor-pointer hover:text-red-400">Hapus</label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="flex justify-end gap-3">
        <a href="index.php" class="px-6 py-3 bg-slate-950 border border-slate-800 text-slate-400 text-xs font-bold rounded-xl transition">Batal</a>
        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition">Simpan Perubahan</button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
