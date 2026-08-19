<?php
/**
 * Public Page: Rental Mobil Lombok - Car Rental Catalog
 */
$page_title = 'Sewa & Rental Mobil Lombok Murah (Lepas Kunci / Supir) | MascalTour';
$page_description = 'Rental mobil murah Lombok terpercaya. Pilihan armada lengkap: Avanza, Innova Reborn, Fortuner, Hiace, Brio. Lepas kunci 24 jam atau plus supir & BBM.';
$page_keywords = 'sewa mobil lombok, rental mobil lombok, rental mobil lepas kunci lombok, sewa avanza lombok, sewa innova lombok, sewa hiace lombok, rental mobil murah lombok, mascaltour';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Get query filters
$active_category = $_GET['kategori'] ?? '';
$search = trim($_GET['q'] ?? '');

// Fetch vehicle categories with count
try {
    $cat_stmt = $pdo->query("
        SELECT vc.*, COUNT(v.id) as vehicle_count
        FROM vehicle_categories vc
        LEFT JOIN vehicles v ON v.vehicle_category_id = vc.id AND v.status = 'published'
        GROUP BY vc.id
        ORDER BY vc.sort_order ASC, vc.name ASC
    ");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Fetch vehicles
try {
    $query = "
        SELECT v.*,
               vc.name AS category_name,
               (SELECT image_url FROM vehicle_photos WHERE vehicle_id = v.id ORDER BY sort_order ASC, id ASC LIMIT 1) AS main_image
        FROM vehicles v
        JOIN vehicle_categories vc ON vc.id = v.vehicle_category_id
        WHERE v.status = 'published'
    ";
    $params = [];

    if (!empty($active_category)) {
        $query .= " AND vc.slug = ?";
        $params[] = $active_category;
    }
    if (!empty($search)) {
        $query .= " AND v.name LIKE ?";
        $params[] = '%' . $search . '%';
    }

    $query .= " ORDER BY v.id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    $vehicles = [];
}

$wa_number = getSetting('whatsapp_number', '6281234567890');

// Fallback images
$car_fallbacks = [
    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1598977123418-45f04b615e37?auto=format&fit=crop&w=800&q=80',
];
?>

<!-- Page Hero Banner -->
<section class="relative bg-brand-950 text-white overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1920&q=80"
             alt="Rental Mobil Lombok"
             class="w-full h-full object-cover opacity-25">
        <div class="absolute inset-0 bg-gradient-to-r from-brand-950/95 via-brand-950/75 to-brand-900/40"></div>
    </div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 bg-brand-500/20 border border-brand-400/30 text-brand-300 text-xs font-semibold rounded-full px-4 py-1.5 mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                Armada Rental Terawat &amp; Bersih
            </div>
            <h1 class="font-outfit text-4xl sm:text-5xl font-extrabold leading-tight mb-4">
                Sewa Mobil <span class="text-accent-400">Lombok</span> Terlengkap
            </h1>
            <p class="text-slate-300 text-lg leading-relaxed mb-8">
                Tersedia opsi sewa Lepas Kunci maupun Dengan Supir + BBM. Pilihan armada matic dan manual siap menemani eksplorasi Anda di seluruh penjuru Lombok.
            </p>

            <!-- Search Bar -->
            <form action="" method="GET" class="flex max-w-lg">
                <input type="text" name="q" placeholder="Cari nama mobil (Mis: Avanza, Brio)..." value="<?= e($search) ?>"
                    class="flex-1 px-5 py-4 rounded-l-2xl bg-white/10 backdrop-blur border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-accent-400/50 text-sm">
                <button type="submit" class="px-6 py-4 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-r-2xl transition">
                    Cari Mobil
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Category Filter -->
<section class="sticky top-20 z-40 bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 overflow-x-auto py-3 scrollbar-thin">
            <a href="rental.php<?= !empty($search) ? '?q=' . urlencode($search) : '' ?>"
                class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition duration-200 <?= empty($active_category) ? 'bg-brand-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-brand-50 hover:text-brand-700' ?>">
                Semua Armada
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="rental.php?kategori=<?= urlencode($cat['slug']) ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>"
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition duration-200 <?= $active_category === $cat['slug'] ? 'bg-brand-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-brand-50 hover:text-brand-700' ?>">
                    <?= e($cat['name']) ?>
                    <span class="ml-1 text-xs opacity-70">(<?= $cat['vehicle_count'] ?>)</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Vehicles Grid -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php if (!empty($search)): ?>
            <p class="text-sm text-slate-500 mb-8">
                Menampilkan hasil untuk <strong class="text-slate-800">"<?= e($search) ?>"</strong>
                &mdash; <?= count($vehicles) ?> kendaraan ditemukan.
                <a href="rental.php" class="text-brand-600 hover:underline ml-1">Reset Filter</a>
            </p>
        <?php endif; ?>

        <?php if (empty($vehicles)): ?>
            <div class="text-center py-24">
                <div class="w-20 h-20 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">🚗</span>
                </div>
                <h3 class="font-outfit text-2xl font-bold text-slate-700 mb-2">Kendaraan Tidak Ditemukan</h3>
                <p class="text-slate-500 mb-6">Belum ada armada pada tipe ini atau coba kata kunci pencarian lain.</p>
                <a href="rental.php" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition">
                    Lihat Semua Armada
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($vehicles as $v):
                    $img_src = !empty($v['main_image'])
                        ? BASE_URL . $v['main_image']
                        : $car_fallbacks[crc32($v['slug']) % count($car_fallbacks)];

                    $wa_msg = "Halo Mascal Tour! Saya tertarik sewa mobil *{$v['name']}*. Mohon informasi ketersediaan tanggal sewa.";
                    $wa_link = generateWaLink($wa_number, $wa_msg);
                ?>
                    <div class="group bg-white rounded-3xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 border border-slate-100 flex flex-col hover:-translate-y-1">
                        <!-- Car Image -->
                        <div class="relative h-48 overflow-hidden bg-slate-100">
                            <img src="<?= e($img_src) ?>" alt="<?= e($v['name']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-3 right-3 px-2.5 py-1 bg-brand-950/80 text-white text-[10px] font-bold rounded-full backdrop-blur-sm">
                                <?= e($v['category_name']) ?>
                            </span>
                        </div>

                        <!-- Car Specs & Title -->
                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="font-outfit text-lg font-bold text-slate-900 leading-snug group-hover:text-brand-700 transition mb-3">
                                <?= e($v['name']) ?>
                            </h3>

                            <!-- Badges spec -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                <?php if (!empty($v['seats'])): ?>
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[11px] font-semibold rounded-lg flex items-center gap-1">
                                        💺 <?= e($v['seats']) ?> Kursi
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($v['transmission'])): ?>
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[11px] font-semibold rounded-lg flex items-center gap-1">
                                        ⚙️ <?= e(ucfirst($v['transmission'])) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($v['fuel_type'])): ?>
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[11px] font-semibold rounded-lg flex items-center gap-1">
                                        ⛽ <?= e($v['fuel_type']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Pricing Breakdown -->
                            <div class="space-y-1.5 py-3 border-t border-slate-100 mb-4">
                                <?php if (!empty($v['price_lepas_kunci'])): ?>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-500">Lepas Kunci:</span>
                                        <span class="font-bold text-brand-700"><?= formatRupiah($v['price_lepas_kunci']) ?><span class="text-[10px] text-slate-400 font-normal">/hari</span></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($v['price_dengan_supir'])): ?>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-500">Supir + BBM:</span>
                                        <span class="font-bold text-brand-700"><?= formatRupiah($v['price_dengan_supir']) ?><span class="text-[10px] text-slate-400 font-normal">/hari</span></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-auto flex gap-2 pt-2">
                                <a href="rental-detail.php?slug=<?= urlencode($v['slug']) ?>"
                                    class="flex-1 text-center py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs rounded-xl transition">
                                    Detail
                                </a>
                                <a href="<?= $wa_link ?>" target="_blank"
                                    class="flex-1 text-center py-2.5 bg-green-500 hover:bg-green-600 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.136.56 4.136 1.535 5.874L0 24l6.297-1.52C8.007 23.43 9.954 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.844 0-3.577-.488-5.088-1.339l-.366-.215-3.738.9.935-3.643-.233-.375A9.953 9.953 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                                    Pesan
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Terms Section -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-brand-50 border border-brand-100 rounded-3xl p-8">
            <h3 class="font-outfit text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span>📋</span> Syarat &amp; Ketentuan Sewa Mobil Lepas Kunci
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-600 leading-relaxed">
                <div class="space-y-2">
                    <p>✓ Memiliki SIM A aktif &amp; e-KTP fisik asli.</p>
                    <p>✓ Menunjukkan konfirmasi tiket pesawat / akomodasi di Lombok.</p>
                    <p>✓ Penggunaan kendaraan khusus untuk area pulau Lombok.</p>
                </div>
                <div class="space-y-2">
                    <p>✓ Hitungan sewa per hari (24 jam) / per kalender.</p>
                    <p>✓ Overtime dikenakan biaya tambahan per jam.</p>
                    <p>✓ Kendaraan diserahterimakan dalam kondisi bersih dan BBM terisi.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bg-gradient-to-r from-brand-700 to-brand-900 py-14">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="font-outfit text-3xl font-extrabold mb-3">Butuh Mobil Kelas Khusus atau Bus Pariwisata?</h2>
        <p class="text-brand-100 mb-7">Kami melayani armada Hiace 14 seat, Elf, hingga Bus Pariwisata 35+ seat untuk rombongan group.</p>
        <a href="<?= generateWaLink($wa_number, 'Halo Mascal Tour! Saya berminat sewa armada rombongan (Hiace/Elf/Bus).') ?>" target="_blank"
            class="inline-flex items-center gap-3 px-8 py-4 bg-white text-brand-800 font-extrabold rounded-2xl hover:bg-accent-50 transition shadow-xl text-sm">
            💬 Tanya Admin via WhatsApp
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
