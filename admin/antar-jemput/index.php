<?php
/**
 * Admin - Rute Antar Jemput List
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$search = trim($_GET['search'] ?? '');
try {
    $query = "
        SELECT tr.*, 
               (SELECT COUNT(*) FROM transfer_vehicle_options WHERE route_id = tr.id) as vehicle_count
        FROM transfer_routes tr
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (tr.name LIKE ? OR tr.origin LIKE ? OR tr.destination LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $query .= " ORDER BY tr.id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $routes = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Gagal mengambil data rute antar jemput: " . $e->getMessage();
    $routes = [];
}
?>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="font-outfit text-2xl font-extrabold text-white">Rute Antar Jemput</h1>
        <p class="text-xs text-slate-400 mt-1">Kelola rute transfer bandara, hotel, pelabuhan, dan tarif pilihan kendaraan</p>
    </div>
    <a href="tambah.php" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition duration-200 flex items-center gap-1.5 shadow-lg shadow-teal-900/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Rute Transfer
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-xl mb-6">
        Data rute transfer berhasil diperbarui!
    </div>
<?php endif; ?>

<!-- Search Bar -->
<div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 mb-6">
    <form action="" method="GET" class="flex gap-3">
        <input type="text" name="search" placeholder="Cari berdasarkan nama rute, asal, atau tujuan..." 
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
    <?php if (empty($routes)): ?>
        <div class="text-center py-16 text-slate-500">
            <p class="text-sm font-semibold">Belum ada data rute antar jemput.</p>
            <p class="text-xs mt-1">Silakan klik tombol "Tambah Rute" untuk memulai.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="p-4">Nama Rute</th>
                        <th class="p-4">Titik Penjemputan (Asal)</th>
                        <th class="p-4">Titik Pengantaran (Tujuan)</th>
                        <th class="p-4">Estimasi Durasi</th>
                        <th class="p-4">Opsi Armada</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-900/60">
                    <?php foreach ($routes as $route): ?>
                        <tr class="hover:bg-slate-900/20 transition duration-150">
                            <td class="p-4">
                                <div class="font-bold text-white"><?= e($route['name']) ?></div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5"><?= e($route['slug']) ?></div>
                            </td>
                            <td class="p-4 text-slate-350"><?= e($route['origin']) ?></td>
                            <td class="p-4 text-slate-350"><?= e($route['destination']) ?></td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 text-xs bg-slate-900 border border-slate-800 text-slate-400 rounded-md">
                                    <?= e($route['duration_estimate_label'] ?: '-') ?>
                                </span>
                            </td>
                            <td class="p-4 text-xs text-slate-400 font-semibold">
                                🚗 <?= $route['vehicle_count'] ?> Pilihan Mobil
                            </td>
                            <td class="p-4 text-center">
                                <?php if ($route['status'] === 'published'): ?>
                                    <span class="px-2.5 py-1 text-[10px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full font-bold uppercase tracking-wider">Published</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 text-[10px] bg-slate-800 border border-slate-700 text-slate-400 rounded-full font-bold uppercase tracking-wider">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="<?= BASE_URL ?>antar-jemput.php?search=<?= e($route['origin']) ?>&preview=1" target="_blank" 
                                        class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition duration-150" title="Pratinjau Rute">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="edit.php?id=<?= $route['id'] ?>" class="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition duration-150" title="Edit Rute">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <a href="hapus.php?id=<?= $route['id'] ?>" class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition duration-150" title="Hapus Rute" onclick="return confirm('Apakah Anda yakin ingin menghapus rute antar-jemput ini?')">
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
