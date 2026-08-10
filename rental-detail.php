<?php
/**
 * Public Page: Rental Mobil Detail
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) { header('Location: rental.php'); exit; }

try {
    $stmt = $pdo->prepare("SELECT v.*, vc.name as category_name FROM vehicles v JOIN vehicle_categories vc ON vc.id = v.vehicle_category_id WHERE v.slug = ? AND v.status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $vehicle = $stmt->fetch();
    if (!$vehicle) { header('Location: rental.php'); exit; }

    $stmt_ph = $pdo->prepare("SELECT * FROM vehicle_photos WHERE vehicle_id = ? ORDER BY sort_order ASC");
    $stmt_ph->execute([$vehicle['id']]);
    $photos = $stmt_ph->fetchAll();

    $pdo->prepare("INSERT INTO leads (source_type, source_id) VALUES ('vehicle', ?)")->execute([$vehicle['id']]);
} catch (PDOException $e) {
    header('Location: rental.php'); exit;
}

$wa_number = getSetting('whatsapp_number', '6281234567890');
$wa_msg = "Halo Lombok Travel! Saya ingin berkonsultasi sewa mobil *{$vehicle['name']}*. Mohon cek ketersediaan armada.";
$wa_link = generateWaLink($wa_number, $wa_msg);

$main_image = !empty($photos) ? BASE_URL . $photos[0]['image_url'] : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80';

$page_title = e($vehicle['name']) . ' - Rental Mobil Lombok | LombokTravel';
$page_description = "Sewa mobil {$vehicle['name']} di Lombok. Lepas kunci atau dengan supir. Harga murah, unit terawat & bersih.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="bg-brand-950 text-white py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-xs text-brand-300" aria-label="Breadcrumb">
            <a href="index.php" class="hover:text-white transition">Home</a>
            <span>/</span>
            <a href="rental.php" class="hover:text-white transition">Rental Mobil</a>
            <span>/</span>
            <span class="text-white"><?= e($vehicle['name']) ?></span>
        </nav>
    </div>
</div>

<div class="bg-slate-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            <!-- Main Info & Photos -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Gallery -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-md">
                    <div class="h-80 sm:h-96 overflow-hidden bg-slate-100">
                        <img id="gallery-main" src="<?= e($main_image) ?>" alt="<?= e($vehicle['name']) ?>"
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

                <!-- Specs -->
                <div class="bg-white rounded-3xl p-6 shadow-md">
                    <span class="text-xs font-bold text-brand-600 uppercase tracking-wider"><?= e($vehicle['category_name']) ?></span>
                    <h1 class="font-outfit text-3xl font-extrabold text-slate-900 mt-1 mb-4"><?= e($vehicle['name']) ?></h1>

                    <div class="grid grid-cols-3 gap-4 py-4 border-y border-slate-100 my-4 text-center">
                        <div class="p-3 bg-slate-50 rounded-2xl">
                            <span class="text-2xl block mb-1">💺</span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Kapasitas</span>
                            <span class="text-sm font-bold text-slate-800"><?= $vehicle['seats'] ? e($vehicle['seats']) . ' Kursi' : '-' ?></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl">
                            <span class="text-2xl block mb-1">⚙️</span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Transmisi</span>
                            <span class="text-sm font-bold text-slate-800"><?= $vehicle['transmission'] ? e(ucfirst($vehicle['transmission'])) : '-' ?></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl">
                            <span class="text-2xl block mb-1">⛽</span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Bahan Bakar</span>
                            <span class="text-sm font-bold text-slate-800"><?= $vehicle['fuel_type'] ? e($vehicle['fuel_type']) : '-' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Terms Text -->
                <div class="bg-white rounded-3xl p-6 shadow-md">
                    <h2 class="font-outfit text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span>📋</span> Syarat &amp; Ketentuan Sewa
                    </h2>
                    <?php if (!empty($vehicle['terms_text'])): ?>
                        <div class="text-slate-600 text-sm leading-relaxed whitespace-pre-line"><?= e($vehicle['terms_text']) ?></div>
                    <?php else: ?>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li>✓ Wajib melampirkan e-KTP dan SIM A yang masih berlaku.</li>
                            <li>✓ Konfirmasi pemesanan dilakukan minimal H-1 sebelum penjemputan.</li>
                            <li>✓ Penggunaan armada mencakup seluruh area daratan pulau Lombok.</li>
                            <li>✓ Durasi sewa harian dihitung 24 jam atau per tanggal kalender.</li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Tariff Box -->
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden sticky top-28">
                    <div class="bg-gradient-to-r from-brand-700 to-brand-900 p-5 text-white">
                        <p class="text-xs text-brand-200 font-semibold uppercase mb-1">Tarif Sewa</p>
                        <h3 class="font-outfit text-xl font-extrabold"><?= e($vehicle['name']) ?></h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <?php if ($vehicle['price_lepas_kunci']): ?>
                            <div class="p-4 bg-brand-50 border border-brand-100 rounded-2xl flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-brand-900 block">Sewa Lepas Kunci</span>
                                    <span class="text-[10px] text-slate-500">Tanpa driver</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-outfit text-xl font-extrabold text-brand-700 block"><?= formatRupiah($vehicle['price_lepas_kunci']) ?></span>
                                    <span class="text-[10px] text-slate-400">/24 jam</span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($vehicle['price_dengan_supir']): ?>
                            <div class="p-4 bg-accent-50/50 border border-accent-100 rounded-2xl flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Dengan Supir + BBM</span>
                                    <span class="text-[10px] text-slate-500">All in driver & bensin</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-outfit text-xl font-extrabold text-accent-700 block"><?= formatRupiah($vehicle['price_dengan_supir']) ?></span>
                                    <span class="text-[10px] text-slate-400">/hari (12 jam)</span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <a href="<?= $wa_link ?>" target="_blank"
                            class="block w-full py-3.5 bg-green-500 hover:bg-green-600 text-white font-extrabold rounded-2xl transition text-center text-sm shadow-lg shadow-green-400/20">
                            💬 Pesan Mobil Ini via WA
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-8">
            <a href="rental.php" class="inline-flex items-center gap-2 text-sm text-brand-700 hover:text-brand-900 font-semibold transition">
                &larr; Kembali ke Katalog Sewa Mobil
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
