<?php
/**
 * Public Page: Paket Wisata Detail
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: paket-wisata.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM packages p JOIN categories c ON c.id = p.category_id WHERE p.slug = ? AND p.status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $pkg = $stmt->fetch();

    if (!$pkg) {
        header('Location: paket-wisata.php');
        exit;
    }

    // Fetch photos
    $stmt_ph = $pdo->prepare("SELECT * FROM package_photos WHERE package_id = ? ORDER BY sort_order ASC");
    $stmt_ph->execute([$pkg['id']]);
    $photos = $stmt_ph->fetchAll();

    // Fetch itinerary
    $stmt_it = $pdo->prepare("SELECT * FROM itinerary_items WHERE package_id = ? ORDER BY sort_order ASC");
    $stmt_it->execute([$pkg['id']]);
    $itinerary = $stmt_it->fetchAll();

    // Fetch inclusions
    $stmt_in = $pdo->prepare("SELECT * FROM package_inclusions WHERE package_id = ? ORDER BY type ASC, sort_order ASC");
    $stmt_in->execute([$pkg['id']]);
    $inclusions_all = $stmt_in->fetchAll();
    $includes = array_filter($inclusions_all, fn($i) => $i['type'] === 'include');
    $excludes = array_filter($inclusions_all, fn($i) => $i['type'] === 'exclude');

    // Fetch prices
    $stmt_pr = $pdo->prepare("SELECT * FROM package_prices WHERE package_id = ? ORDER BY price ASC");
    $stmt_pr->execute([$pkg['id']]);
    $prices = $stmt_pr->fetchAll();

    // Fetch highlights
    $stmt_hl = $pdo->prepare("SELECT * FROM package_highlights WHERE package_id = ? ORDER BY sort_order ASC");
    $stmt_hl->execute([$pkg['id']]);
    $highlights = $stmt_hl->fetchAll();

    // Log lead
    $pdo->prepare("INSERT INTO leads (source_type, source_id) VALUES ('package', ?)")->execute([$pkg['id']]);

} catch (PDOException $e) {
    header('Location: paket-wisata.php');
    exit;
}

$wa_number = getSetting('whatsapp_number', '6281234567890');
$wa_msg = "Halo Lombok Travel! Saya ingin memesan paket wisata *{$pkg['name']}*. Mohon informasi lebih lanjut mengenai ketersediaan dan harga.";
$wa_link = generateWaLink($wa_number, $wa_msg);

$main_image = !empty($photos) ? BASE_URL . $photos[0]['image_url'] : 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1200&q=80';
$min_price = !empty($prices) ? min(array_column($prices, 'price')) : null;

$page_title = e($pkg['name']) . ' - Paket Wisata Lombok | MascalTour';
$page_description = !empty($pkg['short_description']) ? e($pkg['short_description']) : 'Paket wisata ' . e($pkg['name']) . ' di Lombok. Fasilitas lengkap, armada nyaman, guide profesional.';
$page_keywords = e($pkg['name']) . ', paket wisata lombok, tour ' . e($pkg['category_name']) . ', liburan lombok, mascaltour';
$page_og_image = $main_image;

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Hero Breadcrumb -->
<div class="bg-brand-950 text-white py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-xs text-brand-300" aria-label="Breadcrumb">
            <a href="index.php" class="hover:text-white transition">Home</a>
            <span>/</span>
            <a href="paket-wisata.php" class="hover:text-white transition">Paket Wisata</a>
            <span>/</span>
            <span class="text-white"><?= e($pkg['name']) ?></span>
        </nav>
    </div>
</div>

<article class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">

                <!-- Gallery -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-md">
                    <div class="h-80 sm:h-96 overflow-hidden">
                        <img id="gallery-main" src="<?= e($main_image) ?>" alt="<?= e($pkg['name']) ?>"
                             class="w-full h-full object-cover">
                    </div>
                    <?php if (count($photos) > 1): ?>
                        <div class="flex gap-2 p-4 overflow-x-auto">
                            <?php foreach ($photos as $i => $photo): ?>
                                <button onclick="document.getElementById('gallery-main').src='<?= e(BASE_URL . $photo['image_url']) ?>'"
                                    class="shrink-0 w-20 h-16 rounded-xl overflow-hidden border-2 border-transparent hover:border-brand-500 focus:border-brand-600 transition">
                                    <img src="<?= e(BASE_URL . $photo['image_url']) ?>" alt="Foto <?= $i + 1 ?>" class="w-full h-full object-cover">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Title & Meta -->
                <div class="bg-white rounded-3xl p-6 shadow-md">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                        <div>
                            <span class="text-xs font-bold text-brand-600 uppercase tracking-wider"><?= e($pkg['category_name']) ?></span>
                            <h1 class="font-outfit text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 leading-tight"><?= e($pkg['name']) ?></h1>
                        </div>
                        <div class="flex flex-col items-end">
                            <?php if (!empty($pkg['rating'])): ?>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-amber-400 text-lg">★</span>
                                    <span class="font-bold text-slate-800 text-lg"><?= number_format($pkg['rating'], 1) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($pkg['duration_label'])): ?>
                                <span class="text-sm text-slate-500 mt-1"><?= e($pkg['duration_label']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($pkg['short_description'])): ?>
                        <p class="text-slate-600 leading-relaxed text-base"><?= nl2br(e($pkg['short_description'])) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Highlights -->
                <?php if (!empty($highlights)): ?>
                    <div class="bg-white rounded-3xl p-6 shadow-md">
                        <h2 class="font-outfit text-xl font-bold text-slate-900 mb-5 flex items-center gap-2">
                            <span class="w-6 h-6 bg-brand-50 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            </span>
                            Highlight Paket
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <?php foreach ($highlights as $hl): ?>
                                <div class="flex items-center gap-3 p-3 bg-brand-50 rounded-xl">
                                    <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-xs font-bold flex items-center justify-center shrink-0">✓</span>
                                    <span class="text-sm font-semibold text-brand-900"><?= e($hl['title']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Itinerary -->
                <?php if (!empty($itinerary)): ?>
                    <div class="bg-white rounded-3xl p-6 shadow-md">
                        <h2 class="font-outfit text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <span class="w-6 h-6 bg-accent-50 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-accent-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            Itinerary / Rundown Kegiatan
                        </h2>
                        <div class="relative">
                            <!-- Vertical timeline line -->
                            <div class="absolute top-0 left-[1.1rem] bottom-0 w-0.5 bg-slate-200 z-0"></div>
                            <div class="space-y-6 relative">
                                <?php foreach ($itinerary as $item): ?>
                                    <div class="flex gap-5 relative z-10">
                                        <div class="w-9 h-9 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center shrink-0 shadow-md">
                                            <?= !empty($item['time_label']) ? e($item['time_label']) : '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>' ?>
                                        </div>
                                        <div class="flex-1 pb-1">
                                            <h4 class="font-bold text-slate-900 text-sm leading-snug"><?= e($item['activity']) ?></h4>
                                            <?php if (!empty($item['description'])): ?>
                                                <p class="text-slate-500 text-xs mt-1 leading-relaxed"><?= e($item['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Include & Exclude -->
                <?php if (!empty($includes) || !empty($excludes)): ?>
                    <div class="bg-white rounded-3xl p-6 shadow-md">
                        <h2 class="font-outfit text-xl font-bold text-slate-900 mb-5">Termasuk &amp; Tidak Termasuk</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <?php if (!empty($includes)): ?>
                                <div>
                                    <h3 class="text-sm font-bold text-emerald-700 mb-3 flex items-center gap-2">
                                        <span class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center">✓</span>
                                        Sudah Termasuk
                                    </h3>
                                    <ul class="space-y-2">
                                        <?php foreach ($includes as $inc): ?>
                                            <li class="flex items-start gap-2 text-sm text-slate-700">
                                                <span class="text-emerald-500 mt-0.5 shrink-0">●</span>
                                                <?= e($inc['text']) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($excludes)): ?>
                                <div>
                                    <h3 class="text-sm font-bold text-red-600 mb-3 flex items-center gap-2">
                                        <span class="w-6 h-6 bg-red-50 rounded-full flex items-center justify-center">✕</span>
                                        Tidak Termasuk
                                    </h3>
                                    <ul class="space-y-2">
                                        <?php foreach ($excludes as $exc): ?>
                                            <li class="flex items-start gap-2 text-sm text-slate-500">
                                                <span class="text-red-400 mt-0.5 shrink-0">●</span>
                                                <?= e($exc['text']) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sticky Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Pricing Card -->
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden sticky top-28">
                    <div class="bg-gradient-to-r from-brand-700 to-brand-900 p-5 text-white">
                        <p class="text-xs text-brand-200 font-semibold uppercase tracking-wider mb-1">Harga Mulai</p>
                        <?php if ($min_price): ?>
                            <p class="font-outfit text-3xl font-extrabold"><?= formatRupiah($min_price) ?></p>
                            <p class="text-brand-200 text-xs mt-1">per orang</p>
                        <?php else: ?>
                            <p class="font-outfit text-xl font-extrabold">Hubungi Admin</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($prices)): ?>
                        <div class="p-5 border-b border-slate-100">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Tabel Harga</h4>
                            <div class="space-y-2.5">
                                <?php foreach ($prices as $price): ?>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-sm font-semibold text-slate-800"><?= e($price['label']) ?></span>
                                            <?php if (!empty($price['min_pax'])): ?>
                                                <span class="text-xs text-slate-400 ml-1">(min <?= $price['min_pax'] ?> pax)</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm font-bold text-brand-700"><?= formatRupiah($price['price']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="p-5 space-y-3">
                        <a href="<?= $wa_link ?>" target="_blank"
                            class="block w-full py-3.5 bg-green-500 hover:bg-green-600 text-white font-extrabold rounded-2xl transition text-center text-sm shadow-lg shadow-green-400/20">
                            💬 Pesan via WhatsApp Sekarang
                        </a>
                        <p class="text-[10px] text-slate-400 text-center leading-relaxed">
                            Respon cepat dalam &lt;1 jam • Tanpa biaya admin • Konsultasi gratis
                        </p>
                    </div>
                </div>

                <!-- Why Choose Us -->
                <div class="bg-white rounded-2xl p-5 shadow-md">
                    <h4 class="font-bold text-slate-800 text-sm mb-4">Mengapa Pilih Kami?</h4>
                    <ul class="space-y-3 text-xs text-slate-600">
                        <li class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 shrink-0 text-sm">🏆</span>
                            Berpengalaman 10+ tahun di industri wisata Lombok
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 shrink-0 text-sm">👨‍✈️</span>
                            Guide lokal profesional &amp; berlisensi resmi
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 shrink-0 text-sm">🚗</span>
                            Armada kendaraan nyaman ber-AC &amp; terawat
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 shrink-0 text-sm">💰</span>
                            Harga transparan, tidak ada biaya tersembunyi
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</article>

<!-- Back to Packages -->
<div class="bg-slate-100 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="paket-wisata.php" class="inline-flex items-center gap-2 text-sm text-brand-700 hover:text-brand-900 font-semibold transition">
            &larr; Kembali ke Semua Paket Wisata
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
