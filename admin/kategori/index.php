<?php
/**
 * Admin - Kategori Wisata List
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

// Fetch Categories
$search = trim($_GET['search'] ?? '');
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE name LIKE ? ORDER BY sort_order ASC, id DESC");
        $stmt->execute(['%' . $search . '%']);
    } else {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, id DESC");
    }
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Gagal mengambil data kategori: " . $e->getMessage();
    $categories = [];
}
?>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="font-outfit text-2xl font-extrabold text-white">Kategori Wilayah Wisata</h1>
        <p class="text-xs text-slate-400 mt-1">Kelola wilayah atau kategori untuk pengelompokan Paket Wisata Lombok</p>
    </div>
    <a href="tambah.php" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition duration-200 flex items-center gap-1.5 shadow-lg shadow-teal-900/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Kategori
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-xl mb-6">
        Data kategori berhasil diperbarui!
    </div>
<?php endif; ?>

<!-- Search Bar -->
<div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 mb-6">
    <form action="" method="GET" class="flex gap-3">
        <input type="text" name="search" placeholder="Cari nama kategori..." 
            class="flex-1 px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500" 
            value="<?= e($search) ?>">
        <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition duration-200">
            Cari
        </button>
        <?php if (!empty($search)): ?>
            <a href="index.php" class="px-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-400 text-xs font-bold rounded-xl transition duration-200 flex items-center">
                Reset
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Table Card -->
<div class="bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
    <?php if (empty($categories)): ?>
        <div class="text-center py-16 text-slate-500">
            <p class="text-sm font-semibold">Belum ada data kategori wilayah.</p>
            <p class="text-xs mt-1">Silakan klik tombol "Tambah Kategori" untuk membuat kategori baru.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="p-4 w-16 text-center">Urutan</th>
                        <th class="p-4 w-24">Foto</th>
                        <th class="p-4">Nama Wilayah</th>
                        <th class="p-4">Slug</th>
                        <th class="p-4">Deskripsi</th>
                        <th class="p-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-900/60">
                    <?php foreach ($categories as $cat): 
                        $img_src = !empty($cat['image']) ? BASE_URL . $cat['image'] : 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=150&q=80';
                    ?>
                        <tr class="hover:bg-slate-900/20 transition duration-150">
                            <td class="p-4 text-center font-semibold text-slate-400"><?= e($cat['sort_order']) ?></td>
                            <td class="p-4">
                                <div class="w-16 h-12 rounded-lg overflow-hidden border border-slate-800 bg-slate-900">
                                    <img src="<?= e($img_src) ?>" alt="<?= e($cat['name']) ?>" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="p-4 text-white font-bold"><?= e($cat['name']) ?></td>
                            <td class="p-4 text-slate-400 font-mono text-xs"><?= e($cat['slug']) ?></td>
                            <td class="p-4 text-slate-400 text-xs truncate max-w-[220px]"><?= e($cat['description'] ?: '-') ?></td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="edit.php?id=<?= $cat['id'] ?>" class="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition duration-150" title="Edit Kategori">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <a href="hapus.php?id=<?= $cat['id'] ?>" class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition duration-150" title="Hapus Kategori" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Semua paket di bawah kategori ini juga akan terhapus!')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
