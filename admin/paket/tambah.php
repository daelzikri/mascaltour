<?php
/**
 * Admin - Tambah Paket Wisata
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';
try {
    // Fetch categories for selection
    $cats_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC");
    $categories = $cats_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $badge = trim($_POST['badge'] ?? '');
    $rating = $_POST['rating'] !== '' ? (float)$_POST['rating'] : null;
    $duration_label = trim($_POST['duration_label'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if (empty($name) || $category_id <= 0) {
        $error = 'Nama paket dan Kategori wilayah wajib diisi/dipilih.';
    } else {
        try {
            $pdo->beginTransaction();

            $slug = generateSlug($name);
            // Verify slug uniqueness
            $chk = $pdo->prepare("SELECT COUNT(*) FROM packages WHERE slug = ?");
            $chk->execute([$slug]);
            if ($chk->fetchColumn() > 0) {
                $slug = $slug . '-' . time();
            }

            // Insert packages
            $stmt = $pdo->prepare("
                INSERT INTO packages (category_id, name, slug, badge, rating, duration_label, short_description, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$category_id, $name, $slug, $badge ?: null, $rating, $duration_label, $short_description, $status]);
            $packageId = $pdo->lastInsertId();

            // Insert highlights (repeater)
            if (!empty($_POST['highlights'])) {
                $stmtHighlight = $pdo->prepare("INSERT INTO package_highlights (package_id, title, sort_order) VALUES (?, ?, ?)");
                $h_index = 0;
                foreach ($_POST['highlights'] as $hl_text) {
                    $hl_text = trim($hl_text);
                    if ($hl_text === '') continue;
                    $stmtHighlight->execute([$packageId, $hl_text, $h_index++]);
                }
            }

            // Insert itinerary items (repeater)
            if (!empty($_POST['itinerary_time'])) {
                $stmtIt = $pdo->prepare("
                    INSERT INTO itinerary_items (package_id, time_label, activity, description, sort_order) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $it_index = 0;
                foreach ($_POST['itinerary_time'] as $i => $time) {
                    $activity = trim($_POST['itinerary_activity'][$i] ?? '');
                    $desc = trim($_POST['itinerary_description'][$i] ?? '');
                    if ($activity === '') continue;
                    $stmtIt->execute([$packageId, trim($time), $activity, $desc ?: null, $it_index++]);
                }
            }

            // Insert inclusions/exclusions (repeater)
            if (!empty($_POST['inc_text'])) {
                $stmtInc = $pdo->prepare("
                    INSERT INTO package_inclusions (package_id, type, text, sort_order) 
                    VALUES (?, ?, ?, ?)
                ");
                $inc_index = 0;
                foreach ($_POST['inc_text'] as $i => $text) {
                    $text = trim($text);
                    $type = $_POST['inc_type'][$i] ?? 'include';
                    if ($text === '') continue;
                    $stmtInc->execute([$packageId, $type, $text, $inc_index++]);
                }
            }

            // Insert prices (repeater)
            if (!empty($_POST['price_label'])) {
                $stmtPrice = $pdo->prepare("
                    INSERT INTO package_prices (package_id, label, price, min_pax) 
                    VALUES (?, ?, ?, ?)
                ");
                foreach ($_POST['price_label'] as $i => $label) {
                    $label = trim($label);
                    $price = (int)($_POST['price_amount'][$i] ?? 0);
                    $min_pax = $_POST['price_min_pax'][$i] !== '' ? (int)$_POST['price_min_pax'][$i] : null;
                    if ($label === '') continue;
                    $stmtPrice->execute([$packageId, $label, $price, $min_pax]);
                }
            }

            // Multiple image upload processing
            if (!empty($_FILES['photos']['name'][0])) {
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                $stmtPhoto = $pdo->prepare("INSERT INTO package_photos (package_id, image_url, sort_order) VALUES (?, ?, ?)");
                
                $destDir = __DIR__ . '/../../uploads/packages';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                foreach ($_FILES['photos']['tmp_name'] as $i => $tmpPath) {
                    if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;

                    $mime = mime_content_type($tmpPath);
                    if (!in_array($mime, $allowedMimes)) {
                        throw new Exception("File foto '{$_FILES['photos']['name'][$i]}' bukan tipe JPG/PNG/WEBP.");
                    }

                    $ext = 'jpg';
                    if ($mime === 'image/png') $ext = 'png';
                    if ($mime === 'image/webp') $ext = 'webp';

                    $newName = uniqid('pkg_') . '_' . $i . '.' . $ext;
                    if (move_uploaded_file($tmpPath, $destDir . '/' . $newName)) {
                        $stmtPhoto->execute([$packageId, 'uploads/packages/' . $newName, $i]);
                    }
                }
            }

            $pdo->commit();
            header('Location: index.php?success=1');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan paket wisata: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-6">
    <a href="index.php" class="text-xs text-primary-400 hover:underline flex items-center gap-1 mb-2">
        &larr; Kembali ke Daftar Paket
    </a>
    <h1 class="font-outfit text-2xl font-extrabold text-white">Tambah Paket Wisata</h1>
    <p class="text-xs text-slate-400 mt-1">Buat program tur pariwisata baru untuk pengunjung Lombok</p>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
    
    <!-- SECTION 1: MAIN INFO -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Informasi Utama Paket</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Paket Wisata</label>
                <input type="text" name="name" id="name" required placeholder="Contoh: Paket Snorkeling 3 Gili Lombok"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>

            <div>
                <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Wilayah / Kategori</label>
                <select name="category_id" id="category_id" required
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    <option value="">-- Pilih Wilayah --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <label for="badge" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Label Promosi / Badge (Opsional)</label>
                <input type="text" name="badge" id="badge" placeholder="Contoh: Best Seller, Promo, Diskon"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>

            <div>
                <label for="rating" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Rating Bintang (Desimal, Maks: 5.0)</label>
                <input type="number" name="rating" id="rating" min="1" max="5" step="0.1" placeholder="Contoh: 4.8"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>

            <div>
                <label for="duration_label" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Label Durasi Paket</label>
                <input type="text" name="duration_label" id="duration_label" required placeholder="Contoh: 1 Hari / Full Day, 3D2N"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
            </div>
        </div>

        <div>
            <label for="short_description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Deskripsi Singkat</label>
            <textarea name="short_description" id="short_description" rows="3" placeholder="Ringkasan singkat paket wisata untuk ditampilkan di grid card..."
                class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status Publikasi</label>
                <select name="status" id="status" class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    <option value="draft">Draft (Disembunyikan dari Publik)</option>
                    <option value="published">Published (Ditampilkan di Website)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Foto Galeri Paket (Multiple)</label>
                <input type="file" name="photos[]" multiple accept="image/*"
                    class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-750 cursor-pointer">
            </div>
        </div>
    </div>

    <!-- SECTION 2: HIGHLIGHTS (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Highlight Destinasi Wisata</h2>
        
        <div id="inclusion-wrapper" class="space-y-3">
            <!-- Row Inclusion -->
            <div class="inclusion-row flex items-center gap-3">
                <input type="hidden" name="inc_type[]" value="include"> <!-- Dummy field, highlights will be read separately as highlights -->
                <input type="text" name="highlights[]" placeholder="Contoh: Snorkeling Gili Trawangan, Sunset Bukit Merese"
                    class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
            </div>
        </div>
        <button type="button" id="add-inclusion" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-primary-400 text-xs font-bold rounded-xl transition flex items-center gap-1">
            + Tambah Highlight
        </button>
    </div>

    <!-- SECTION 3: ITINERARY (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Jadwal Perjalanan (Itinerary)</h2>
        
        <div id="itinerary-wrapper" class="space-y-4">
            <div class="itinerary-row bg-slate-900/40 p-4 border border-slate-850 rounded-2xl flex flex-col sm:flex-row gap-4 relative">
                <div class="w-full sm:w-36 shrink-0">
                    <input type="text" name="itinerary_time[]" placeholder="Jam (08:00)"
                        class="w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                </div>
                <div class="flex-1 space-y-3">
                    <input type="text" name="itinerary_activity[]" placeholder="Nama Kegiatan / Destinasi"
                        class="w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    <textarea name="itinerary_description[]" rows="2" placeholder="Rincian deskripsi kegiatan (Opsional)"
                        class="w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"></textarea>
                </div>
                <div class="flex items-end justify-end sm:self-start">
                    <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
                </div>
            </div>
        </div>
        <button type="button" id="add-itinerary" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-primary-400 text-xs font-bold rounded-xl transition">
            + Tambah Jadwal Kegiatan
        </button>
    </div>

    <!-- SECTION 4: INCLUSIONS / EXCLUSIONS (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Fasilitas Include &amp; Exclude</h2>
        
        <div id="options-wrapper" class="space-y-3">
            <div class="option-row flex flex-col sm:flex-row gap-3">
                <select name="inc_type[]" 
                    class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-36">
                    <option value="include">Include</option>
                    <option value="exclude">Exclude</option>
                </select>
                <input type="text" name="inc_text[]" placeholder="Contoh: Air Mineral Dingin, Tiket Masuk Objek Wisata"
                    class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
            </div>
        </div>
        <button type="button" id="add-option" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-primary-400 text-xs font-bold rounded-xl transition">
            + Tambah Fasilitas
        </button>
    </div>

    <!-- SECTION 5: PRICING (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Tabel Tarif Harga Paket (Berdasarkan Jumlah Pax)</h2>
        
        <div id="prices-wrapper" class="space-y-3">
            <div class="price-row flex flex-col sm:flex-row gap-3">
                <input type="text" name="price_label[]" placeholder="Label (Mis: 2 Pax, 4-6 Pax)" required
                    class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                
                <input type="number" name="price_amount[]" placeholder="Harga Rupiah (Mis: 350000)" required min="0"
                    class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-48">
                
                <input type="number" name="price_min_pax[]" placeholder="Min Pax (Mis: 2)" min="1"
                    class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-32">
                
                <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
            </div>
        </div>
        <button type="button" id="add-price" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-primary-400 text-xs font-bold rounded-xl transition">
            + Tambah Baris Harga
        </button>
    </div>

    <!-- SUBMIT -->
    <div class="flex justify-end gap-3">
        <a href="index.php" class="px-6 py-3 bg-slate-950 border border-slate-800 hover:bg-slate-900 text-slate-400 text-xs font-bold rounded-xl transition duration-200">
            Batal
        </a>
        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition duration-200">
            Simpan Paket Wisata
        </button>
    </div>

</form>

<!-- Include Repeater JS Helper -->
<script src="<?= BASE_URL ?>assets/js/repeater.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
