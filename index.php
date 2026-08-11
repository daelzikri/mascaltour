<?php
/**
 * Public Landing Page (Home)
 */
$page_title = "MascalTour - Paket Wisata Lombok, Sewa Mobil & Antar Jemput Bandara";
$page_description = "Layanan agen travel resmi Lombok. Menyediakan paket wisata Lombok murah, rental mobil lepas kunci & supir, serta shuttle antar jemput Bandara Lombok (LOP) & pelabuhan.";
$page_keywords = "paket wisata lombok, tour lombok, sewa mobil lombok, rental mobil lombok, antar jemput bandara lombok, transfer airport lombok, lombok travel, mascaltour, tour gili trawangan, pantai kuta lombok, wisata rinjani, sewa avanza lombok, sewa innova lombok";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch Featured Packages
try {
    $packages_stmt = $pdo->query("
        SELECT p.*, c.name as category_name, 
               (SELECT image_url FROM package_photos WHERE package_id = p.id ORDER BY sort_order ASC, id ASC LIMIT 1) as main_image,
               (SELECT price FROM package_prices WHERE package_id = p.id ORDER BY price ASC LIMIT 1) as min_price
        FROM packages p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'
        ORDER BY p.id DESC LIMIT 3
    ");
    $featured_packages = $packages_stmt->fetchAll();
} catch (PDOException $e) {
    $featured_packages = [];
}

// Fetch Featured Transfer Routes
try {
    $routes_stmt = $pdo->query("
        SELECT tr.*,
               (SELECT MIN(price) FROM transfer_vehicle_options WHERE route_id = tr.id) as min_price
        FROM transfer_routes tr
        WHERE tr.status = 'published'
        ORDER BY tr.id ASC LIMIT 3
    ");
    $featured_routes = $routes_stmt->fetchAll();
} catch (PDOException $e) {
    $featured_routes = [];
}

// Fetch Featured Vehicles
try {
    $vehicles_stmt = $pdo->query("
        SELECT v.*, vc.name as category_name,
               (SELECT image_url FROM vehicle_photos WHERE vehicle_id = v.id ORDER BY sort_order ASC, id ASC LIMIT 1) as main_image
        FROM vehicles v
        JOIN vehicle_categories vc ON v.vehicle_category_id = vc.id
        WHERE v.status = 'published'
        ORDER BY v.id DESC LIMIT 3
    ");
    $featured_vehicles = $vehicles_stmt->fetchAll();
} catch (PDOException $e) {
    $featured_vehicles = [];
}


// Fetch FAQs
try {
    $faqs_stmt = $pdo->query("
        SELECT * FROM faqs 
        ORDER BY sort_order ASC, id ASC LIMIT 8
    ");
    $faqs = $faqs_stmt->fetchAll();
} catch (PDOException $e) {
    $faqs = [];
}

// Fallback values for settings
$site_name = getSetting('site_name', 'Lombok Travel Agency');
$hero_headline = getSetting('hero_title', getSetting('hero_headline', 'Jelajahi Keindahan Lombok Tanpa Cemas'));
$hero_subheadline = getSetting('hero_subtitle', getSetting('hero_subheadline', 'Layanan Paket Wisata, Antar Jemput Bandara & Sewa Mobil Premium Terpercaya di Lombok'));
$hero_image = getSetting('hero_image');
if (empty($hero_image)) {
    $hero_image = 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1920&q=80';
} else {
    $hero_image = BASE_URL . $hero_image;
}
$wa_number = getSetting('contact_whatsapp', getSetting('whatsapp_number', '6281234567890'));

// Fallback Unsplash image arrays by theme
$pkg_fallbacks = [
    'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1573790387438-4da905039392?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1501179691627-eeaa65ea017c?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=600&q=80',
];
$car_fallbacks = [
    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1598977123418-45f04b615e37?auto=format&fit=crop&w=600&q=80',
];
?>

<!-- 1. Hero Section -->
<section class="relative min-h-[85vh] flex items-center bg-slate-950 overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-[10000ms] scale-105 ease-out" style="background-image: url('<?= e($hero_image) ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-teal-950/95 via-teal-950/80 to-transparent"></div>
    <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-slate-50 to-transparent"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 z-10">
        <div class="max-w-2xl text-white space-y-6">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-brand-500/20 border border-brand-400/30 rounded-full text-brand-300 text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                <span class="w-2 h-2 bg-brand-400 rounded-full animate-ping"></span> Official <?= e($site_name) ?>
            </span>
            <h1 class="font-outfit text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-none">
                <?= e($hero_headline) ?>
            </h1>
            <p class="text-slate-300 text-base sm:text-lg font-medium leading-relaxed max-w-lg">
                <?= e($hero_subheadline) ?>
            </p>
            <div class="flex flex-wrap gap-4 pt-4">
                <a href="#paket-wisata" class="px-8 py-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-2xl transition duration-200 shadow-lg shadow-brand-600/30 transform hover:-translate-y-0.5 text-center min-w-[170px]">
                    Paket Wisata
                </a>
                <a href="#antar-jemput" class="px-8 py-4 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-2xl transition duration-200 shadow-lg shadow-accent-500/30 transform hover:-translate-y-0.5 text-center min-w-[170px]">
                    Antar Jemput
                </a>
                <a href="#rental" class="px-8 py-4 bg-white hover:bg-slate-100 text-slate-900 font-bold rounded-2xl transition duration-200 shadow-lg transform hover:-translate-y-0.5 text-center min-w-[170px]">
                    Sewa Mobil
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 2. Value Proposition Section -->
<section class="py-20 bg-slate-50 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="font-outfit text-xs font-bold text-brand-600 uppercase tracking-widest">Mengapa Memilih Kami</h2>
            <p class="font-outfit text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">Komitmen Layanan Liburan Terbaik Anda</p>
            <p class="text-slate-500 text-sm mt-4 leading-relaxed">Kami mendedikasikan seluruh kenyamanan transportasi dan agenda liburan Anda di Lombok dengan standar kualitas tertinggi.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:border-brand-500/20 transition duration-300 group">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-6 group-hover:bg-brand-600 group-hover:text-white transition duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <h3 class="font-outfit text-lg font-bold text-slate-900 mb-2">Armada Prima</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Seluruh armada rental &amp; transfer kami selalu dalam kondisi bersih, harum, dan diservis berkala secara ketat.</p>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:border-brand-500/20 transition duration-300 group">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-6 group-hover:bg-brand-600 group-hover:text-white transition duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </div>
                <h3 class="font-outfit text-lg font-bold text-slate-900 mb-2">Driver Profesional</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Pengemudi lokal berlisensi resmi, ramah, dan menguasai rute serta rekomendasi wisata terbaik Lombok.</p>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:border-brand-500/20 transition duration-300 group">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-6 group-hover:bg-brand-600 group-hover:text-white transition duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="font-outfit text-lg font-bold text-slate-900 mb-2">Harga Transparan</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Tarif all-in bersaing tanpa ada tambahan biaya siluman di tengah perjalanan. Jujur &amp; terpercaya.</p>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:border-brand-500/20 transition duration-300 group">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-6 group-hover:bg-brand-600 group-hover:text-white transition duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="font-outfit text-lg font-bold text-slate-900 mb-2">Layanan 24/7</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Tim customer support kami siap melayani reservasi dan konsultasi darurat Anda sepanjang waktu.</p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 1: PAKET WISATA -->
<section id="paket-wisata" class="py-20 bg-white border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
            <div>
                <h2 class="font-outfit text-xs font-bold text-brand-600 uppercase tracking-widest">Rekomendasi Liburan</h2>
                <p class="font-outfit text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">1. Paket Wisata Populer</p>
            </div>
            <a href="<?= BASE_URL ?>paket-wisata.php" class="text-brand-600 hover:text-brand-700 font-bold text-sm inline-flex items-center gap-1">
                Semua Paket Wisata &rarr;
            </a>
        </div>

        <?php if (empty($featured_packages)): ?>
            <div class="text-center py-12 text-slate-400">Belum ada paket wisata yang dipublikasikan.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($featured_packages as $idx => $package): 
                    $pkg_image = !empty($package['main_image'])
                        ? BASE_URL . $package['main_image']
                        : $pkg_fallbacks[$idx % count($pkg_fallbacks)];
                ?>
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-xl transition duration-300 flex flex-col h-full">
                        <div class="relative h-60 w-full overflow-hidden">
                            <img src="<?= e($pkg_image) ?>" alt="<?= e($package['name']) ?>" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                            <?php if (!empty($package['badge'])): ?>
                                <span class="absolute top-4 left-4 bg-brand-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase"><?= e($package['badge']) ?></span>
                            <?php endif; ?>
                            <?php if ($package['rating']): ?>
                                <div class="absolute top-4 right-4 bg-slate-950/70 backdrop-blur-sm text-amber-400 text-xs font-bold px-2.5 py-1 rounded-lg flex items-center gap-1">
                                    <span>★ <?= e($package['rating']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6 flex flex-col flex-1">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <span><?= e($package['category_name']) ?></span>
                                <?php if ($package['duration_label']): ?>
                                    <span class="lowercase text-slate-500 font-semibold">⏱ <?= e($package['duration_label']) ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="font-outfit text-xl font-bold text-slate-900 mt-2"><?= e($package['name']) ?></h3>
                            <p class="text-slate-500 text-sm mt-3 flex-1 line-clamp-2"><?= e($package['short_description']) ?></p>
                            <div class="pt-6 border-t border-slate-100 flex items-center justify-between mt-6">
                                <div>
                                    <span class="text-[10px] text-slate-400 block uppercase font-semibold">Harga mulai</span>
                                    <span class="text-brand-600 font-extrabold text-lg"><?= formatRupiah($package['min_price']) ?><?php if ($package['min_price']): ?><span class="text-xs text-slate-500 font-normal">/pax</span><?php endif; ?></span>
                                </div>
                                <a href="paket-wisata-detail.php?slug=<?= e($package['slug']) ?>" class="px-4 py-2 bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold rounded-xl transition duration-200">
                                    Detail Paket
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- SECTION 2: ANTAR JEMPUT -->
<section id="antar-jemput" class="py-20 bg-slate-50 border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
            <div>
                <h2 class="font-outfit text-xs font-bold text-accent-600 uppercase tracking-widest">Transfer Airport &amp; Pelabuhan</h2>
                <p class="font-outfit text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">2. Antar Jemput Bandara &amp; Dermaga</p>
            </div>
            <a href="<?= BASE_URL ?>antar-jemput.php" class="text-brand-600 hover:text-brand-700 font-bold text-sm inline-flex items-center gap-1">
                Semua Rute Antar Jemput &rarr;
            </a>
        </div>

        <?php if (empty($featured_routes)): ?>
            <div class="text-center py-12 text-slate-400">Belum ada rute antar jemput yang dipublikasikan.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($featured_routes as $route): ?>
                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-xl transition duration-300 flex flex-col">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mb-4">✈️</div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400"><?= e($route['duration_estimate_label'] ?? 'Transfer Fix') ?></span>
                        <h3 class="font-outfit text-xl font-bold text-slate-900 mt-1 mb-3"><?= e($route['name']) ?></h3>
                        <p class="text-slate-500 text-xs leading-relaxed mb-6 flex-1">Dari <?= e($route['origin']) ?> langsung menuju <?= e($route['destination']) ?>. Driver tepat waktu &amp; profesional.</p>
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Mulai dari</span>
                                <span class="text-brand-700 font-extrabold text-lg"><?= formatRupiah($route['min_price']) ?></span>
                            </div>
                            <a href="antar-jemput-detail.php?slug=<?= e($route['slug']) ?>" class="px-4 py-2 bg-brand-600 text-white text-xs font-bold rounded-xl hover:bg-brand-700 transition">Detail Rute</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- SECTION 3: RENTAL MOBIL -->
<section id="rental" class="py-20 bg-white border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
            <div>
                <h2 class="font-outfit text-xs font-bold text-brand-600 uppercase tracking-widest">Sewa Mobil Terpercaya</h2>
                <p class="font-outfit text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">3. Rental Mobil Terlaris</p>
            </div>
            <a href="<?= BASE_URL ?>rental.php" class="text-brand-600 hover:text-brand-700 font-bold text-sm inline-flex items-center gap-1">
                Semua Mobil Rental &rarr;
            </a>
        </div>

        <?php if (empty($featured_vehicles)): ?>
            <div class="text-center py-12 text-slate-400">Belum ada armada sewa mobil yang dipublikasikan.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($featured_vehicles as $idx => $vehicle): 
                    $vehicle_img = !empty($vehicle['main_image'])
                        ? BASE_URL . $vehicle['main_image']
                        : $car_fallbacks[$idx % count($car_fallbacks)];
                ?>
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-xl transition duration-300 flex flex-col h-full">
                        <div class="relative h-56 w-full overflow-hidden bg-slate-100">
                            <img src="<?= e($vehicle_img) ?>" alt="<?= e($vehicle['name']) ?>" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                        </div>
                        <div class="p-6 flex flex-col flex-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider"><?= e($vehicle['category_name']) ?></span>
                            <h3 class="font-outfit text-xl font-bold text-slate-900 mt-2"><?= e($vehicle['name']) ?></h3>
                            <div class="flex flex-wrap gap-x-4 gap-y-2 mt-3 text-xs text-slate-500 font-medium">
                                <?php if ($vehicle['seats']): ?><span>💺 <?= e($vehicle['seats']) ?> Kursi</span><?php endif; ?>
                                <?php if ($vehicle['transmission']): ?><span>⚙️ <?= e(ucfirst($vehicle['transmission'])) ?></span><?php endif; ?>
                                <?php if ($vehicle['fuel_type']): ?><span>⛽ <?= e($vehicle['fuel_type']) ?></span><?php endif; ?>
                            </div>
                            <p class="text-slate-500 text-xs mt-3 flex-1 line-clamp-2">Pilihan armada terbaik dengan jaminan perawatan kondisi berkala.</p>
                            <div class="pt-6 border-t border-slate-100 flex items-center justify-between mt-6">
                                <div>
                                    <span class="text-[10px] text-slate-400 block uppercase font-semibold">Harga mulai</span>
                                    <span class="text-brand-600 font-extrabold text-lg">
                                        <?= $vehicle['price_lepas_kunci'] ? formatRupiah($vehicle['price_lepas_kunci']) : formatRupiah($vehicle['price_dengan_supir']) ?>
                                        <span class="text-xs text-slate-500 font-normal">/hari</span>
                                    </span>
                                </div>
                                <a href="rental-detail.php?slug=<?= e($vehicle['slug']) ?>" class="px-4 py-2 bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold rounded-xl transition duration-200">
                                    Pesan Mobil
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- 4. How to Book (3 Steps) -->
<section class="py-20 bg-slate-50 border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="font-outfit text-xs font-bold text-brand-600 uppercase tracking-widest">Sangat Sederhana</h2>
            <p class="font-outfit text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">Cara Booking Tiket / Rental</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
            <div class="relative z-10 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-brand-600 text-white font-extrabold text-xl flex items-center justify-center mx-auto shadow-lg shadow-brand-600/30">1</div>
                <h3 class="font-outfit text-lg font-bold text-slate-900">Pilih Layanan</h3>
                <p class="text-slate-500 text-sm leading-relaxed max-w-xs mx-auto">Pilih paket wisata favorit, rute antar jemput, atau armada sewa mobil yang Anda butuhkan.</p>
            </div>

            <div class="relative z-10 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-brand-600 text-white font-extrabold text-xl flex items-center justify-center mx-auto shadow-lg shadow-brand-600/30">2</div>
                <h3 class="font-outfit text-lg font-bold text-slate-900">Hubungi WhatsApp</h3>
                <p class="text-slate-500 text-sm leading-relaxed max-w-xs mx-auto">Klik tombol pesan WhatsApp. Draf pesan berisi rincian pilihan Anda akan terisi otomatis.</p>
            </div>

            <div class="relative z-10 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-brand-600 text-white font-extrabold text-xl flex items-center justify-center mx-auto shadow-lg shadow-brand-600/30">3</div>
                <h3 class="font-outfit text-lg font-bold text-slate-900">Konfirmasi &amp; Berangkat</h3>
                <p class="text-slate-500 text-sm leading-relaxed max-w-xs mx-auto">Sepakati tanggal dengan admin kami, dan driver siap menjemput Anda tepat waktu di lokasi.</p>
            </div>
        </div>
    </div>
</section>


<!-- 6. FAQ Accordion Section -->
<section class="py-20 bg-slate-50 border-t border-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="font-outfit text-xs font-bold text-brand-600 uppercase tracking-widest">Pertanyaan Umum</h2>
            <p class="font-outfit text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">FAQ Pelanggan</p>
        </div>

        <div class="space-y-4">
            <?php if (empty($faqs)): ?>
                <div class="text-center py-8 text-slate-400">Belum ada pertanyaan FAQ.</div>
            <?php else: ?>
                <?php foreach ($faqs as $faq): ?>
                    <div class="faq-item border border-slate-200 rounded-2xl overflow-hidden bg-white">
                        <button class="faq-toggle w-full px-6 py-5 text-left font-bold text-slate-900 hover:bg-slate-50 flex justify-between items-center transition">
                            <span><?= e($faq['question']) ?></span>
                            <span class="faq-icon text-brand-600 font-bold text-lg">&plus;</span>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 bg-slate-50">
                            <p class="p-6 text-sm text-slate-600 leading-relaxed"><?= nl2br(e($faq['answer'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Slider & Accordion JS Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Accordion Toggle
    const toggles = document.querySelectorAll('.faq-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.faq-icon');
            
            const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';
            
            document.querySelectorAll('.faq-content').forEach(c => c.style.maxHeight = '0px');
            document.querySelectorAll('.faq-icon').forEach(i => i.innerHTML = '&plus;');

            if (!isOpen) {
                content.style.maxHeight = content.scrollHeight + 'px';
                icon.innerHTML = '&minus;';
            }
        });
    });

});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
