<?php
/**
 * Admin - Kategori Kendaraan Rental
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);

    if (empty($name)) {
        $error = 'Nama Kategori Kendaraan wajib diisi.';
    } else {
        try {
            $slug = generateSlug($name);
            
            // Check slug uniqueness
            $chk = $pdo->prepare("SELECT COUNT(*) FROM vehicle_categories WHERE slug = ?");
            $chk->execute([$slug]);
            if ($chk->fetchColumn() > 0) {
                $slug = $slug . '-' . time();
            }

            $stmt = $pdo->prepare("INSERT INTO vehicle_categories (name, slug, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $sort_order]);
            $success = 'Kategori kendaraan berhasil ditambahkan!';
        } catch (PDOException $e) {
            $error = 'Gagal menambahkan kategori: ' . $e->getMessage();
        }
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if ($del_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM vehicle_categories WHERE id = ?");
            $stmt->execute([$del_id]);
            $success = 'Kategori kendaraan berhasil dihapus!';
        } catch (PDOException $e) {
            $error = 'Gagal menghapus kategori: ' . $e->getMessage();
        }
    }
}

// Fetch Categories
try {
    $categories = $pdo->query("SELECT * FROM vehicle_categories ORDER BY sort_order ASC, id DESC")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>

<div class="mb-6">
    <h1 class="font-outfit text-2xl font-extrabold text-white">Kategori Kendaraan Rental</h1>
    <p class="text-xs text-slate-400 mt-1">Kelola pembagian tipe kendaraan (misal: City Car, SUV, Bus, Motor)</p>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-xl mb-6">
        <?= e($success) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Add Category Form -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl h-fit">
        <h2 class="font-outfit text-lg font-bold text-white mb-6">Tambah Kategori Tipe</h2>
        
        <form action="" method="POST" class="space-y-5">
            <input type="hidden" name="add_category" value="1">
            
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Kategori Tipe</label>
                <input type="text" name="name" id="name" required placeholder="Contoh: City Car, SUV, Bus, Motor"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>

            <div>
                <label for="sort_order" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Urutan Tampil (Prioritas)</label>
                <input type="number" name="sort_order" id="sort_order" min="0" value="0" required
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>

            <button type="submit" class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition duration-200 text-xs shadow-lg shadow-teal-900/20">
                Tambah Kategori Tipe
            </button>
        </form>
    </div>

    <!-- Category List Table -->
    <div class="lg:col-span-2 bg-slate-950 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <?php if (empty($categories)): ?>
            <div class="text-center py-16 text-slate-500">
                <p class="text-sm font-semibold">Belum ada data tipe kendaraan.</p>
                <p class="text-xs mt-1">Silakan tambah melalui form di samping.</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="p-4 w-20 text-center">Urutan</th>
                        <th class="p-4">Nama Tipe Kategori</th>
                        <th class="p-4">Slug</th>
                        <th class="p-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-900/60">
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-slate-900/20 transition duration-150">
                            <td class="p-4 text-center font-semibold text-slate-400"><?= e($cat['sort_order']) ?></td>
                            <td class="p-4 font-bold text-white"><?= e($cat['name']) ?></td>
                            <td class="p-4 font-mono text-slate-400 text-xs"><?= e($cat['slug']) ?></td>
                            <td class="p-4 text-center">
                                <a href="kategori-kendaraan.php?delete=<?= $cat['id'] ?>" 
                                    class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg inline-block transition"
                                    title="Hapus Kategori"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus kategori kendaraan ini? Seluruh mobil di bawah kategori ini juga akan terhapus!')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
