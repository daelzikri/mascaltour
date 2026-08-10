<?php
/**
 * Admin - Tambah Kendaraan Rental
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

try {
    $cat_stmt = $pdo->query("SELECT id, name FROM vehicle_categories ORDER BY sort_order ASC, name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $vehicle_category_id = (int)($_POST['vehicle_category_id'] ?? 0);
    $seats = $_POST['seats'] !== '' ? (int)$_POST['seats'] : null;
    $transmission = $_POST['transmission'] ?? null;
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

            $slug = generateSlug($name);
            $chk = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE slug = ?");
            $chk->execute([$slug]);
            if ($chk->fetchColumn() > 0) {
                $slug = $slug . '-' . time();
            }

            $stmt = $pdo->prepare("
                INSERT INTO vehicles (vehicle_category_id, name, slug, seats, transmission, fuel_type, price_lepas_kunci, price_dengan_supir, terms_text, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$vehicle_category_id, $name, $slug, $seats, $transmission ?: null, $fuel_type ?: null, $price_lepas_kunci, $price_dengan_supir, $terms_text ?: null, $status]);
            $vehicleId = $pdo->lastInsertId();

            // Multiple photo upload
            if (!empty($_FILES['photos']['name'][0])) {
                $stmtPhoto = $pdo->prepare("INSERT INTO vehicle_photos (vehicle_id, image_url, sort_order) VALUES (?, ?, ?)");
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
                        $stmtPhoto->execute([$vehicleId, $imgUrl, $i]);
                    }
                }
            }

            $pdo->commit();
            header('Location: index.php?success=1');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan kendaraan: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-6">
    <a href="index.php" class="text-xs text-primary-400 hover:underline flex items-center gap-1 mb-2">&larr; Kembali ke Daftar Kendaraan</a>
    <h1 class="font-outfit text-2xl font-extrabold text-white">Tambah Kendaraan Rental</h1>
    <p class="text-xs text-slate-400 mt-1">Daftarkan armada kendaraan baru ke katalog rental</p>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6"><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data" class="space-y-8">

    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Informasi Kendaraan</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Kendaraan</label>
                <input type="text" name="name" required placeholder="Contoh: Toyota All New Avanza"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tipe Kategori Kendaraan</label>
                <select name="vehicle_category_id" required
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
                    <option value="">-- Pilih Tipe --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jumlah Kursi</label>
                <input type="number" name="seats" min="1" max="60" placeholder="Mis: 7"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Transmisi</label>
                <select name="transmission"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
                    <option value="">-- Pilih Transmisi --</option>
                    <option value="matic">Matic (Otomatis)</option>
                    <option value="manual">Manual</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jenis Bahan Bakar</label>
                <input type="text" name="fuel_type" placeholder="Mis: Bensin, Solar, Hybrid"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tarif Lepas Kunci (Harga/Hari)</label>
                <input type="number" name="price_lepas_kunci" min="0" placeholder="Mis: 300000"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
                <p class="text-[10px] text-slate-500 mt-1">Kosongkan jika tidak menyediakan opsi lepas kunci</p>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tarif Dengan Supir &amp; BBM (Harga/Hari)</label>
                <input type="number" name="price_dengan_supir" min="0" placeholder="Mis: 550000"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Syarat &amp; Ketentuan Sewa Lepas Kunci</label>
            <textarea name="terms_text" rows="5" placeholder="Tuliskan syarat dokumen (KTP, SIM A), ketentuan overtime, dll. Tampil di halaman detail kendaraan."
                class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status Publikasi</label>
                <select name="status"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
                    <option value="draft">Draft (Disembunyikan)</option>
                    <option value="published">Published (Tampilkan)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Foto Kendaraan (Multiple)</label>
                <input type="file" name="photos[]" multiple accept="image/*"
                    class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer">
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="index.php" class="px-6 py-3 bg-slate-950 border border-slate-800 text-slate-400 text-xs font-bold rounded-xl transition">Batal</a>
        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition">Simpan Kendaraan</button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
