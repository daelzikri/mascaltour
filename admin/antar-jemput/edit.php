<?php
/**
 * Admin - Edit Rute Antar Jemput
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$error = '';
try {
    // Fetch route
    $stmt = $pdo->prepare("SELECT * FROM transfer_routes WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $route = $stmt->fetch();

    if (!$route) {
        header('Location: index.php');
        exit;
    }

    // Fetch related vehicle options
    $stmt_opt = $pdo->prepare("SELECT * FROM transfer_vehicle_options WHERE route_id = ? ORDER BY price ASC");
    $stmt_opt->execute([$id]);
    $options = $stmt_opt->fetchAll();

} catch (PDOException $e) {
    die("Kesalahan sistem: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $duration_estimate_label = trim($_POST['duration_estimate_label'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if (empty($name) || empty($origin) || empty($destination)) {
        $error = 'Nama Rute, Titik Asal, dan Titik Tujuan wajib diisi.';
    } else {
        try {
            $pdo->beginTransaction();

            $slug = $route['slug'];
            if ($name !== $route['name']) {
                $slug = generateSlug($name);
                $chk = $pdo->prepare("SELECT COUNT(*) FROM transfer_routes WHERE slug = ? AND id != ?");
                $chk->execute([$slug, $id]);
                if ($chk->fetchColumn() > 0) {
                    $slug = $slug . '-' . time();
                }
            }

            // Update route
            $stmt = $pdo->prepare("
                UPDATE transfer_routes 
                SET name = ?, slug = ?, origin = ?, destination = ?, duration_estimate_label = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $slug, $origin, $destination, $duration_estimate_label ?: null, $status, $id]);

            // Sync options: Delete and Re-insert
            $del_stmt = $pdo->prepare("DELETE FROM transfer_vehicle_options WHERE route_id = ?");
            $del_stmt->execute([$id]);

            if (!empty($_POST['vehicle_name'])) {
                $stmtOpt = $pdo->prepare("
                    INSERT INTO transfer_vehicle_options (route_id, vehicle_name, price) 
                    VALUES (?, ?, ?)
                ");
                foreach ($_POST['vehicle_name'] as $i => $v_name) {
                    $v_name = trim($v_name);
                    $price = (int)($_POST['vehicle_price'][$i] ?? 0);
                    if ($v_name === '') continue;
                    $stmtOpt->execute([$id, $v_name, $price]);
                }
            }

            $pdo->commit();
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Gagal memperbarui rute antar-jemput: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-6">
    <a href="index.php" class="text-xs text-primary-400 hover:underline flex items-center gap-1 mb-2">
        &larr; Kembali ke Daftar Rute
    </a>
    <h1 class="font-outfit text-2xl font-extrabold text-white">Ubah Rute Antar Jemput</h1>
    <p class="text-xs text-slate-400 mt-1">Ubah rincian rute: <span class="text-white font-bold"><?= e($route['name']) ?></span></p>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<form action="" method="POST" class="space-y-8">
    
    <!-- SECTION 1: ROUTE INFO -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Detail Rute</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Rute / Judul Layanan</label>
                <input type="text" name="name" id="name" required placeholder="Contoh: Transfer Bandara Lombok (LIA) ke Senggigi"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                    value="<?= e($route['name']) ?>">
            </div>

            <div>
                <label for="origin" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Titik Penjemputan (Asal)</label>
                <input type="text" name="origin" id="origin" required placeholder="Contoh: Bandara LIA Lombok"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                    value="<?= e($route['origin']) ?>">
            </div>

            <div>
                <label for="destination" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Titik Pengantaran (Tujuan)</label>
                <input type="text" name="destination" id="destination" required placeholder="Contoh: Senggigi, Batu Layar"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                    value="<?= e($route['destination']) ?>">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="duration_estimate_label" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Estimasi Durasi Perjalanan</label>
                <input type="text" name="duration_estimate_label" id="duration_estimate_label" placeholder="Contoh: 1.5 Jam, 45 Menit"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                    value="<?= e($route['duration_estimate_label'] ?? '') ?>">
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status Publikasi</label>
                <select name="status" id="status" class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    <option value="draft" <?= $route['status'] === 'draft' ? 'selected' : '' ?>>Draft (Sembunyikan)</option>
                    <option value="published" <?= $route['status'] === 'published' ? 'selected' : '' ?>>Published (Tampilkan)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- SECTION 2: VEHICLE OPTIONS (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Tarif Pilihan Kendaraan (All-In)</h2>
        
        <div id="options-wrapper" class="space-y-3">
            <?php if (empty($options)): ?>
                <div class="option-row flex flex-col sm:flex-row gap-3">
                    <input type="text" name="vehicle_name[]" placeholder="Nama Mobil (Mis: Toyota Avanza, Innova Reborn)" required
                        class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    
                    <input type="number" name="vehicle_price[]" placeholder="Harga Rupiah (Mis: 350000)" required min="0"
                        class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-48">
                    
                    <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
                </div>
            <?php else: ?>
                <?php foreach ($options as $opt): ?>
                    <div class="option-row flex flex-col sm:flex-row gap-3">
                        <input type="text" name="vehicle_name[]" placeholder="Nama Mobil (Mis: Toyota Avanza, Innova Reborn)" required
                            class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                            value="<?= e($opt['vehicle_name']) ?>">
                        
                        <input type="number" name="vehicle_price[]" placeholder="Harga Rupiah (Mis: 350000)" required min="0"
                            class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-48"
                            value="<?= e($opt['price']) ?>">
                        
                        <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="add-option" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-primary-400 text-xs font-bold rounded-xl transition">
            + Tambah Pilihan Armada &amp; Tarif
        </button>
    </div>

    <!-- SUBMIT -->
    <div class="flex justify-end gap-3">
        <a href="index.php" class="px-6 py-3 bg-slate-950 border border-slate-800 hover:bg-slate-900 text-slate-400 text-xs font-bold rounded-xl transition duration-200">
            Batal
        </a>
        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition duration-200">
            Simpan Perubahan Rute
        </button>
    </div>

</form>

<script src="<?= BASE_URL ?>assets/js/repeater.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
