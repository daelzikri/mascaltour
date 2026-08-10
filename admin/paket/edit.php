<?php
/**
 * Admin - Edit Paket Wisata
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
    // Fetch categories
    $cats_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC");
    $categories = $cats_stmt->fetchAll();

    // Fetch current package
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $package = $stmt->fetch();

    if (!$package) {
        header('Location: index.php');
        exit;
    }

    // Fetch related highlights
    $stmt_hl = $pdo->prepare("SELECT * FROM package_highlights WHERE package_id = ? ORDER BY sort_order ASC");
    $stmt_hl->execute([$id]);
    $highlights = $stmt_hl->fetchAll();

    // Fetch related itinerary items
    $stmt_it = $pdo->prepare("SELECT * FROM itinerary_items WHERE package_id = ? ORDER BY sort_order ASC");
    $stmt_it->execute([$id]);
    $itinerary = $stmt_it->fetchAll();

    // Fetch related inclusions/exclusions
    $stmt_inc = $pdo->prepare("SELECT * FROM package_inclusions WHERE package_id = ? ORDER BY sort_order ASC");
    $stmt_inc->execute([$id]);
    $inclusions = $stmt_inc->fetchAll();

    // Fetch related prices
    $stmt_pr = $pdo->prepare("SELECT * FROM package_prices WHERE package_id = ? ORDER BY price ASC");
    $stmt_pr->execute([$id]);
    $prices = $stmt_pr->fetchAll();

    // Fetch gallery photos
    $stmt_ph = $pdo->prepare("SELECT * FROM package_photos WHERE package_id = ? ORDER BY sort_order ASC");
    $stmt_ph->execute([$id]);
    $photos = $stmt_ph->fetchAll();

} catch (PDOException $e) {
    die("Kesalahan sistem: " . $e->getMessage());
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

            $slug = $package['slug'];
            if ($name !== $package['name']) {
                $slug = generateSlug($name);
                $chk = $pdo->prepare("SELECT COUNT(*) FROM packages WHERE slug = ? AND id != ?");
                $chk->execute([$slug, $id]);
                if ($chk->fetchColumn() > 0) {
                    $slug = $slug . '-' . time();
                }
            }

            // Update main packages
            $stmt = $pdo->prepare("
                UPDATE packages 
                SET category_id = ?, name = ?, slug = ?, badge = ?, rating = ?, duration_label = ?, short_description = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$category_id, $name, $slug, $badge ?: null, $rating, $duration_label, $short_description, $status, $id]);

            // Sync highlights: Delete and Re-insert
            $del_hl = $pdo->prepare("DELETE FROM package_highlights WHERE package_id = ?");
            $del_hl->execute([$id]);
            if (!empty($_POST['highlights'])) {
                $stmtHighlight = $pdo->prepare("INSERT INTO package_highlights (package_id, title, sort_order) VALUES (?, ?, ?)");
                $h_index = 0;
                foreach ($_POST['highlights'] as $hl_text) {
                    $hl_text = trim($hl_text);
                    if ($hl_text === '') continue;
                    $stmtHighlight->execute([$id, $hl_text, $h_index++]);
                }
            }

            // Sync itinerary: Delete and Re-insert
            $del_it = $pdo->prepare("DELETE FROM itinerary_items WHERE package_id = ?");
            $del_it->execute([$id]);
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
                    $stmtIt->execute([$id, trim($time), $activity, $desc ?: null, $it_index++]);
                }
            }

            // Sync inclusions: Delete and Re-insert
            $del_inc = $pdo->prepare("DELETE FROM package_inclusions WHERE package_id = ?");
            $del_inc->execute([$id]);
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
                    $stmtInc->execute([$id, $type, $text, $inc_index++]);
                }
            }

            // Sync pricing: Delete and Re-insert
            $del_pr = $pdo->prepare("DELETE FROM package_prices WHERE package_id = ?");
            $del_pr->execute([$id]);
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
                    $stmtPrice->execute([$id, $label, $price, $min_pax]);
                }
            }

            // Process image deletions
            if (!empty($_POST['delete_photos'])) {
                $stmtDelPhoto = $pdo->prepare("DELETE FROM package_photos WHERE id = ? AND package_id = ?");
                foreach ($_POST['delete_photos'] as $photo_id) {
                    $photo_id = (int)$photo_id;
                    
                    // Fetch photo details to unlink from disk
                    $img_stmt = $pdo->prepare("SELECT image_url FROM package_photos WHERE id = ? LIMIT 1");
                    $img_stmt->execute([$photo_id]);
                    $img_url = $img_stmt->fetchColumn();

                    if (!empty($img_url) && file_exists(__DIR__ . '/../../' . $img_url)) {
                        @unlink(__DIR__ . '/../../' . $img_url);
                    }
                    $stmtDelPhoto->execute([$photo_id, $id]);
                }
            }

            // Multiple image upload processing
            if (!empty($_FILES['photos']['name'][0])) {
                $stmtPhoto = $pdo->prepare("INSERT INTO package_photos (package_id, image_url, sort_order) VALUES (?, ?, ?)");
                
                // Determine next photo index for ordering
                $stmt_max = $pdo->prepare("SELECT MAX(sort_order) FROM package_photos WHERE package_id = ?");
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

                    $imgUrl = uploadImage($fileItem, 'packages');
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
            $error = 'Gagal memperbarui paket wisata: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-6">
    <a href="index.php" class="text-xs text-primary-400 hover:underline flex items-center gap-1 mb-2">
        &larr; Kembali ke Daftar Paket
    </a>
    <h1 class="font-outfit text-2xl font-extrabold text-white">Ubah Paket Wisata</h1>
    <p class="text-xs text-slate-400 mt-1">Ubah rincian informasi untuk paket: <span class="text-white font-bold"><?= e($package['name']) ?></span></p>
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
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                    value="<?= e($package['name']) ?>">
            </div>

            <div>
                <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Wilayah / Kategori</label>
                <select name="category_id" id="category_id" required
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    <option value="">-- Pilih Wilayah --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (int)$package['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <label for="badge" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Label Promosi / Badge (Opsional)</label>
                <input type="text" name="badge" id="badge" placeholder="Contoh: Best Seller, Promo, Diskon"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                    value="<?= e($package['badge'] ?? '') ?>">
            </div>

            <div>
                <label for="rating" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Rating Bintang (Desimal, Maks: 5.0)</label>
                <input type="number" name="rating" id="rating" min="1" max="5" step="0.1" placeholder="Contoh: 4.8"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                    value="<?= e($package['rating'] ?? '') ?>">
            </div>

            <div>
                <label for="duration_label" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Label Durasi Paket</label>
                <input type="text" name="duration_label" id="duration_label" required placeholder="Contoh: 1 Hari / Full Day, 3D2N"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                    value="<?= e($package['duration_label'] ?? '') ?>">
            </div>
        </div>

        <div>
            <label for="short_description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Deskripsi Singkat</label>
            <textarea name="short_description" id="short_description" rows="3" placeholder="Ringkasan singkat paket wisata untuk ditampilkan di grid card..."
                class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"><?= e($package['short_description'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status Publikasi</label>
                <select name="status" id="status" class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    <option value="draft" <?= $package['status'] === 'draft' ? 'selected' : '' ?>>Draft (Disembunyikan dari Publik)</option>
                    <option value="published" <?= $package['status'] === 'published' ? 'selected' : '' ?>>Published (Ditampilkan di Website)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tambah Foto Galeri Paket (Multiple)</label>
                <input type="file" name="photos[]" multiple accept="image/*"
                    class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-750 cursor-pointer">
            </div>
        </div>

        <!-- Current Photos List -->
        <?php if (!empty($photos)): ?>
            <div class="pt-4 border-t border-slate-900">
                <p class="text-xs font-semibold uppercase text-slate-400 mb-4">Pilih Foto untuk Dihapus</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php foreach ($photos as $photo): ?>
                        <div class="relative group rounded-xl overflow-hidden border border-slate-850 bg-slate-900 flex flex-col">
                            <div class="aspect-video w-full overflow-hidden">
                                <img src="<?= BASE_URL . $photo['image_url'] ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="p-2 flex items-center gap-1.5 text-xs text-slate-400">
                                <input type="checkbox" name="delete_photos[]" value="<?= $photo['id'] ?>" id="del-photo-<?= $photo['id'] ?>"
                                    class="rounded border-slate-850 text-red-600 focus:ring-red-500 bg-slate-950">
                                <label for="del-photo-<?= $photo['id'] ?>" class="cursor-pointer font-medium hover:text-red-400">Hapus</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- SECTION 2: HIGHLIGHTS (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Highlight Destinasi Wisata</h2>
        
        <div id="inclusion-wrapper" class="space-y-3">
            <?php if (empty($highlights)): ?>
                <div class="inclusion-row flex items-center gap-3">
                    <input type="hidden" name="inc_type[]" value="include">
                    <input type="text" name="highlights[]" placeholder="Contoh: Snorkeling Gili Trawangan, Sunset Bukit Merese"
                        class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
                </div>
            <?php else: ?>
                <?php foreach ($highlights as $hl): ?>
                    <div class="inclusion-row flex items-center gap-3">
                        <input type="hidden" name="inc_type[]" value="include">
                        <input type="text" name="highlights[]" placeholder="Contoh: Snorkeling Gili Trawangan, Sunset Bukit Merese"
                            class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                            value="<?= e($hl['title']) ?>">
                        <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="add-inclusion" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-primary-400 text-xs font-bold rounded-xl transition flex items-center gap-1">
            + Tambah Highlight
        </button>
    </div>

    <!-- SECTION 3: ITINERARY (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Jadwal Perjalanan (Itinerary)</h2>
        
        <div id="itinerary-wrapper" class="space-y-4">
            <?php if (empty($itinerary)): ?>
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
            <?php else: ?>
                <?php foreach ($itinerary as $it): ?>
                    <div class="itinerary-row bg-slate-900/40 p-4 border border-slate-850 rounded-2xl flex flex-col sm:flex-row gap-4 relative">
                        <div class="w-full sm:w-36 shrink-0">
                            <input type="text" name="itinerary_time[]" placeholder="Jam (08:00)"
                                class="w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                                value="<?= e($it['time_label']) ?>">
                        </div>
                        <div class="flex-1 space-y-3">
                            <input type="text" name="itinerary_activity[]" placeholder="Nama Kegiatan / Destinasi"
                                class="w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                                value="<?= e($it['activity']) ?>">
                            <textarea name="itinerary_description[]" rows="2" placeholder="Rincian deskripsi kegiatan (Opsional)"
                                class="w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"><?= e($it['description']) ?></textarea>
                        </div>
                        <div class="flex items-end justify-end sm:self-start">
                            <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="add-itinerary" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-primary-400 text-xs font-bold rounded-xl transition">
            + Tambah Jadwal Kegiatan
        </button>
    </div>

    <!-- SECTION 4: INCLUSIONS / EXCLUSIONS (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Fasilitas Include &amp; Exclude</h2>
        
        <div id="options-wrapper" class="space-y-3">
            <?php if (empty($inclusions)): ?>
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
            <?php else: ?>
                <?php foreach ($inclusions as $inc): ?>
                    <div class="option-row flex flex-col sm:flex-row gap-3">
                        <select name="inc_type[]" 
                            class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-36">
                            <option value="include" <?= $inc['type'] === 'include' ? 'selected' : '' ?>>Include</option>
                            <option value="exclude" <?= $inc['type'] === 'exclude' ? 'selected' : '' ?>>Exclude</option>
                        </select>
                        <input type="text" name="inc_text[]" placeholder="Contoh: Air Mineral Dingin, Tiket Masuk Objek Wisata"
                            class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                            value="<?= e($inc['text']) ?>">
                        <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="add-option" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-primary-400 text-xs font-bold rounded-xl transition">
            + Tambah Fasilitas
        </button>
    </div>

    <!-- SECTION 5: PRICING (REPEATER) -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3">Tabel Tarif Harga Paket (Berdasarkan Jumlah Pax)</h2>
        
        <div id="prices-wrapper" class="space-y-3">
            <?php if (empty($prices)): ?>
                <div class="price-row flex flex-col sm:flex-row gap-3">
                    <input type="text" name="price_label[]" placeholder="Label (Mis: 2 Pax, 4-6 Pax)" required
                        class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm">
                    
                    <input type="number" name="price_amount[]" placeholder="Harga Rupiah (Mis: 350000)" required min="0"
                        class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-48">
                    
                    <input type="number" name="price_min_pax[]" placeholder="Min Pax (Mis: 2)" min="1"
                        class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-32">
                    
                    <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
                </div>
            <?php else: ?>
                <?php foreach ($prices as $pr): ?>
                    <div class="price-row flex flex-col sm:flex-row gap-3">
                        <input type="text" name="price_label[]" placeholder="Label (Mis: 2 Pax, 4-6 Pax)" required
                            class="flex-1 px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm"
                            value="<?= e($pr['label']) ?>">
                        
                        <input type="number" name="price_amount[]" placeholder="Harga Rupiah (Mis: 350000)" required min="0"
                            class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-48"
                            value="<?= e($pr['price']) ?>">
                        
                        <input type="number" name="price_min_pax[]" placeholder="Min Pax (Mis: 2)" min="1"
                            class="px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 text-sm w-full sm:w-32"
                            value="<?= e($pr['min_pax'] ?? '') ?>">
                        
                        <button type="button" class="remove-row p-3 text-red-500 hover:bg-red-500/10 rounded-xl transition">Hapus</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
            Simpan Perubahan Paket
        </button>
    </div>

</form>

<!-- Include Repeater JS Helper -->
<script src="<?= BASE_URL ?>assets/js/repeater.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
