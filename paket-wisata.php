<?php
/**
 * Public Page: Paket Wisata - Tour Package Catalog
 */
$page_title = 'Katalog Paket Wisata Lombok Murah & Terlengkap | MascalTour';
$page_description = 'Pilihan paket tour & wisata Lombok 1 Hari, 2D1N, 3D2N, 4D3N. Wisata Gili Trawangan, Pantai Kuta Mandalika, Desa Sade, dan Bukit Merese dengan harga terjangkau.';
$page_keywords = 'paket wisata lombok, paket tour lombok, tour gili trawangan, pantai kuta lombok, wisata lombok mandalika, paket liburan lombok murah, mascaltour';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Get query filters
$active_category = $_GET['kategori'] ?? '';
$search = trim($_GET['q'] ?? '');

// Fetch categories with package counts
try {
    $cat_stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as pkg_count
        FROM categories c
        LEFT JOIN packages p ON p.category_id = c.id AND p.status = 'published'
        GROUP BY c.id
        ORDER BY c.sort_order ASC, c.name ASC
    ");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Fetch packages
try {
    $query = "
        SELECT p.*,
               c.name AS category_name,
               (SELECT image_url FROM package_photos WHERE package_id = p.id ORDER BY sort_order ASC, id ASC LIMIT 1) AS main_image,
               (SELECT price FROM package_prices WHERE package_id = p.id ORDER BY price ASC LIMIT 1) AS min_price
        FROM packages p
        JOIN categories c ON c.id = p.category_id
        WHERE p.status = 'published'
    ";
    $params = [];

    if (!empty($active_category)) {
        $query .= " AND c.slug = ?";
        $params[] = $active_category;
    }
    if (!empty($search)) {
        $query .= " AND (p.name LIKE ? OR p.short_description LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $query .= " ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $packages = $stmt->fetchAll();
} catch (PDOException $e) {
    $packages = [];
}

$wa_number = getSetting('whatsapp_number', '6281234567890');
?>

<!-- Hero Sub-Page Banner -->
<section class="relative bg-brand-950 text-white overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1920&q=80"
             alt="Pantai Lombok"
             class="w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-r from-brand-950/90 to-brand-900/60"></div>
    </div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 bg-brand-600/20 border border-brand-400/30 text-brand-300 text-xs font-semibold rounded-full px-4 py-1.5 mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Wisata Pulau Lombok & Gili
            </div>
            <h1 class="font-outfit text-4xl sm:text-5xl font-extrabold leading-tight mb-4">
                Paket Wisata <span class="text-accent-400">Terbaik</span> di Lombok
            </h1>
            <p class="text-slate-300 text-lg leading-relaxed mb-8">
                Dari pantai eksotis, gili-gili berpasir putih, hingga puncak Rinjani yang megah — temukan paket wisata yang sempurna untuk petualangan Anda.
            </p>
            <!-- Search Bar -->
            <form action="" method="GET" class="flex max-w-lg">
                <input type="text" name="q" placeholder="Cari paket wisata..." value="<?= e($search) ?>"
                    class="flex-1 px-5 py-4 rounded-l-2xl bg-white/10 backdrop-blur border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-accent-400/50 text-sm">
                <button type="submit" class="px-6 py-4 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-r-2xl transition">
                    Cari
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Category Filter Tabs -->
<section class="sticky top-20 z-40 bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 overflow-x-auto py-3 scrollbar-thin">
            <a href="paket-wisata.php<?= !empty($search) ? '?q=' . urlencode($search) : '' ?>"
                class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition duration-200 <?= empty($active_category) ? 'bg-brand-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-brand-50 hover:text-brand-700' ?>">
                Semua
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="paket-wisata.php?kategori=<?= urlencode($cat['slug']) ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>"
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition duration-200 <?= $active_category === $cat['slug'] ? 'bg-brand-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-brand-50 hover:text-brand-700' ?>">
                    <?= e($cat['name']) ?>
                    <span class="ml-1 text-xs opacity-70">(<?= $cat['pkg_count'] ?>)</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Packages Grid -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php if (!empty($search)): ?>
            <p class="text-sm text-slate-500 mb-8">
                Menampilkan hasil untuk <strong class="text-slate-800">"<?= e($search) ?>"</strong>
                &mdash; <?= count($packages) ?> paket ditemukan.
                <a href="paket-wisata.php" class="text-brand-600 hover:underline ml-1">Reset</a>
            </p>
        <?php endif; ?>

        <?php if (empty($packages)): ?>
            <div class="text-center py-24">
                <div class="w-20 h-20 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                </div>
                <h3 class="font-outfit text-2xl font-bold text-slate-700 mb-2">Paket Tidak Ditemukan</h3>
                <p class="text-slate-500 mb-6">Belum ada paket wisata untuk kategori ini atau coba kata kunci lain.</p>
                <a href="paket-wisata.php" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition">
                    Lihat Semua Paket
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($packages as $pkg):
                    // Fallback images by category theme
                    $fallback_images = [
                        'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1573790387438-4da905039392?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1501179691627-eeaa65ea017c?auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=800&q=80',
                    ];
                    $img_src = !empty($pkg['main_image'])
                        ? BASE_URL . $pkg['main_image']
                        : $fallback_images[crc32($pkg['slug']) % count($fallback_images)];

                    $wa_msg = "Halo Mascal Tour! Saya tertarik dengan paket *{$pkg['name']}*. Mohon informasi lebih lanjut.";
                    $wa_link = generateWaLink($wa_number, $wa_msg);
                ?>
                    <div class="group bg-white rounded-3xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 border border-slate-100 flex flex-col hover:-translate-y-1">
                        <!-- Card Image -->
                        <div class="relative h-52 overflow-hidden">
                            <img src="<?= e($img_src) ?>" alt="<?= e($pkg['name']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>

                            <!-- Badge -->
                            <?php if (!empty($pkg['badge'])): ?>
                                <span class="absolute top-3 left-3 px-3 py-1 bg-accent-500 text-white text-[11px] font-bold rounded-full shadow">
                                    <?= e($pkg['badge']) ?>
                                </span>
                            <?php endif; ?>

                            <!-- Duration Chip -->
                            <?php if (!empty($pkg['duration_label'])): ?>
                                <span class="absolute bottom-3 left-3 px-3 py-1 bg-white/90 text-brand-800 text-[11px] font-bold rounded-full flex items-center gap-1.5 shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <?= e($pkg['duration_label']) ?>
                                </span>
                            <?php endif; ?>

                            <!-- Category tag -->
                            <span class="absolute top-3 right-3 px-2.5 py-1 bg-brand-600/80 text-white text-[10px] font-bold rounded-full backdrop-blur-sm">
                                <?= e($pkg['category_name']) ?>
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex flex-col flex-1">
                            <!-- Rating -->
                            <?php if (!empty($pkg['rating'])): ?>
                                <div class="flex items-center gap-1 mb-2">
                                    <span class="text-amber-400 text-sm">★</span>
                                    <span class="text-sm font-bold text-slate-800"><?= number_format($pkg['rating'], 1) ?></span>
                                    <span class="text-xs text-slate-400">penilaian</span>
                                </div>
                            <?php endif; ?>

                            <h3 class="font-outfit text-base font-bold text-slate-900 leading-snug group-hover:text-brand-700 transition mb-2">
                                <?= e($pkg['name']) ?>
                            </h3>

                            <?php if (!empty($pkg['short_description'])): ?>
                                <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 mb-3">
                                    <?= e($pkg['short_description']) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Price + CTA -->
                            <div class="mt-auto pt-4 border-t border-slate-100">
                                <?php if (!empty($pkg['min_price'])): ?>
                                    <div class="mb-3">
                                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">Mulai dari</span>
                                        <span class="text-brand-700 font-extrabold text-lg font-outfit"><?= formatRupiah($pkg['min_price']) ?></span>
                                        <span class="text-slate-400 text-xs">/pax</span>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <span class="text-brand-600 font-bold text-sm">Harga: Hubungi Admin</span>
                                    </div>
                                <?php endif; ?>

                                <div class="flex gap-2">
                                    <a href="paket-wisata-detail.php?slug=<?= urlencode($pkg['slug']) ?>"
                                        class="flex-1 text-center py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs rounded-xl transition">
                                        Lihat Detail
                                    </a>
                                    <a href="<?= $wa_link ?>" target="_blank"
                                        class="flex-1 text-center py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.136.56 4.136 1.535 5.874L0 24l6.297-1.52C8.007 23.43 9.954 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.844 0-3.577-.488-5.088-1.339l-.366-.215-3.738.9.935-3.643-.233-.375A9.953 9.953 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                                        Pesan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Banner -->
<section class="bg-gradient-to-r from-brand-700 to-brand-900 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="font-outfit text-3xl font-extrabold mb-4">Tidak Menemukan Paket Yang Sesuai?</h2>
        <p class="text-brand-100 mb-8">Hubungi kami dan kami akan merancang itinerary wisata Lombok yang sesuai dengan kebutuhan dan anggaran Anda.</p>
        <a href="<?= generateWaLink($wa_number, 'Halo Mascal Tour! Saya ingin membuat paket wisata custom Lombok.') ?>" target="_blank"
            class="inline-flex items-center gap-3 px-8 py-4 bg-white text-brand-800 font-extrabold rounded-2xl hover:bg-accent-50 transition shadow-xl text-base">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.136.56 4.136 1.535 5.874L0 24l6.297-1.52C8.007 23.43 9.954 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.844 0-3.577-.488-5.088-1.339l-.366-.215-3.738.9.935-3.643-.233-.375A9.953 9.953 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
            Konsultasi Paket Custom via WhatsApp
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
