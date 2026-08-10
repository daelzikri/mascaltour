<?php
/**
 * Admin - Tambah Rute Antar Jemput
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';
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

            $slug = generateSlug($name);
            // Verify slug uniqueness
            $chk = $pdo->prepare("SELECT COUNT(*) FROM transfer_routes WHERE slug = ?");
            $chk->execute([$slug]);
            if ($chk->fetchColumn() > 0) {
                $slug = $slug . '-' . time();
            }

            // Insert route
            $stmt = $pdo->prepare("
                INSERT INTO transfer_routes (name, slug, origin, destination, duration_estimate_label, status) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $slug, $origin, $destination, $duration_estimate_label ?: null, $status]);
            $routeId = $pdo->lastInsertId();

            // Insert options (repeater)
            if (!empty($_POST['vehicle_name'])) {
                $stmtOpt = $pdo->prepare("
                    INSERT INTO transfer_vehicle_options (route_id, vehicle_name, price) 
                    VALUES (?, ?, ?)
                ");
                foreach ($_POST['vehicle_name'] as $i => $v_name) {
                    $v_name = trim($v_name);
                    $price = (int)($_POST['vehicle_price'][$i] ?? 0);
                    if ($v_name === '') continue;
                    $stmtOpt->execute([$routeId, $v_name, $price]);
                }
            }

            $pdo->commit();
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan rute antar-jemput: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-6">
    <a href="index.php" class="text-xs text-primary-400 hover:underline flex items-center gap-1 mb-2">
        &larr; Kembali ke Daftar Rute
    </a>
    <h1 class="font-outfit text-2xl font-extrabold text-white">Tambah Rute Antar Jemput</h1>
    <p class="text-xs text-slate-400 mt-1">Buat rute baru dan tetapkan pilihan kendaraan serta tarif all-in</p>
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
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>

            <div>
                <label for="origin" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Titik Penjemputan (Asal)</label>
                <input type="text" name="origin" id="origin" required placeholder="Contoh: Bandara LIA Lombok"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>

            <div>
                <label for="destination" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Titik Pengantaran (Tujuan)</label>
                <input type="text" name="destination" id="destination" required placeholder="Contoh: Senggigi, Batu Layar"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="duration_estimate_label" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Estimasi Durasi Perjalanan</label>
                <input type="text" name="duration_estimate_label" id="duration_estimate_label" placeholder="Contoh: 1.5 Jam, 45 Menit"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status Publikasi</label>
                <select name="status" id="status" class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    <option value="draft">Draft (Sembunyikan)</option>
                    <option value="published">Published (Tampilkan)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- SECTION 2: VEHICLE OPTIONS (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Tarif Pilihan Kendaraan (All-In)</h2>
        
        <div id="options-wrapper" class="space-y-3">
            <div class="option-row flex flex-col sm:flex-row gap-3">
                <input type="text" name="vehicle_name[]" placeholder="Nama Mobil (Mis: Toyota Avanza, Innova Reborn)" required
                    class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                
                <input type="number" name="vehicle_price[]" placeholder="Harga Rupiah (Mis: 350000)" required min="0"
                    class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-48">
                
                <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
            </div>
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
            Simpan Rute Transfer
        </button>
    </div>

</form>

<script src="<?= BASE_URL ?>assets/js/repeater.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
