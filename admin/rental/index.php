<?php
/**
 * Admin - Kendaraan Rental List
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$search = trim($_GET['search'] ?? '');
$cat_filter = (int)($_GET['category_id'] ?? 0);

try {
    $cat_stmt = $pdo->query("SELECT id, name FROM vehicle_categories ORDER BY sort_order ASC, name ASC");
    $categories = $cat_stmt->fetchAll();

    $query = "
        SELECT v.*, vc.name as category_name,
               (SELECT image_url FROM vehicle_photos WHERE vehicle_id = v.id ORDER BY sort_order ASC, id ASC LIMIT 1) as main_image
        FROM vehicles v
        JOIN vehicle_categories vc ON v.vehicle_category_id = vc.id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $query .= " AND v.name LIKE ?";
        $params[] = '%' . $search . '%';
    }
    if ($cat_filter > 0) {
        $query .= " AND v.vehicle_category_id = ?";
        $params[] = $cat_filter;
    }

    $query .= " ORDER BY v.id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    $vehicles = [];
    $categories = [];
}
?>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="font-outfit text-2xl font-extrabold text-white">Kendaraan Rental</h1>
        <p class="text-xs text-slate-400 mt-1">Kelola armada mobil sewa, spesifikasi, tarif, dan syarat penyewaan</p>
    </div>
    <div class="flex gap-3">
        <a href="kategori-kendaraan.php" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition duration-200 flex items-center gap-1.5">
            Kelola Tipe
        </a>
        <a href="tambah.php" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition duration-200 flex items-center gap-1.5 shadow-lg shadow-teal-900/30">
            + Tambah Kendaraan
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-xl mb-6">
        Data kendaraan berhasil diperbarui!
    </div>
<?php endif; ?>

<!-- Filter Bar -->
<div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 mb-6">
    <form action="" method="GET" class="flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" placeholder="Cari nama kendaraan..."
            class="flex-1 px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
            value="<?= e($search) ?>">
        <select name="category_id"
            class="px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 min-w-[180px]">
            <option value="">-- Semua Tipe --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat_filter === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition">Filter</button>
        <?php if (!empty($search) || $cat_filter > 0): ?>
            <a href="index.php" class="px-4 py-2.5 bg-slate-900 border border-slate-800 text-slate-400 text-xs font-bold rounded-xl transition flex items-center">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Vehicle Grid Cards -->
<?php if (empty($vehicles)): ?>
    <div class="bg-slate-950 border border-slate-800 rounded-2xl text-center py-16 text-slate-500">
        <p class="text-sm font-semibold">Belum ada data kendaraan rental.</p>
        <p class="text-xs mt-1">Klik "Tambah Kendaraan" untuk menambah armada pertama.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($vehicles as $v):
            $img_src = !empty($v['main_image']) ? BASE_URL . $v['main_image'] : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=400&q=80';
        ?>
            <div class="bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden hover:border-primary-500/30 transition duration-300 flex flex-col">
                <div class="h-44 w-full overflow-hidden bg-slate-900">
                    <img src="<?= e($img_src) ?>" alt="<?= e($v['name']) ?>" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                </div>
                <div class="p-4 flex flex-col flex-1">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider"><?= e($v['category_name']) ?></span>
                    <h3 class="font-outfit text-sm font-bold text-white mt-1 leading-snug"><?= e($v['name']) ?></h3>
                    <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2 text-[11px] text-slate-400">
                        <?php if ($v['seats']): ?><span>💺 <?= e($v['seats']) ?> kursi</span><?php endif; ?>
                        <?php if ($v['transmission']): ?><span>⚙️ <?= e(ucfirst($v['transmission'])) ?></span><?php endif; ?>
                    </div>
                    <div class="mt-3 text-xs">
                        <?php if ($v['price_lepas_kunci']): ?>
                            <span class="text-primary-400 font-bold"><?= formatRupiah($v['price_lepas_kunci']) ?></span>
                            <span class="text-slate-500">/hari (lepas kunci)</span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-auto pt-4 border-t border-slate-900 flex items-center justify-between gap-2">
                        <?php if ($v['status'] === 'published'): ?>
                            <span class="px-2 py-0.5 text-[10px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full font-bold">Published</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 text-[10px] bg-slate-800 border border-slate-700 text-slate-400 rounded-full font-bold">Draft</span>
                        <?php endif; ?>
                        <div class="flex gap-1.5">
                            <a href="edit.php?id=<?= $v['id'] ?>" class="p-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                            <a href="hapus.php?id=<?= $v['id'] ?>" class="p-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition" title="Hapus" onclick="return confirm('Hapus kendaraan ini?')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
