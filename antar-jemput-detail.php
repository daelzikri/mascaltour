<?php
/**
 * Public Page: Antar Jemput Detail
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) { header('Location: antar-jemput.php'); exit; }

try {
    $stmt = $pdo->prepare("SELECT * FROM transfer_routes WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $route = $stmt->fetch();
    if (!$route) { header('Location: antar-jemput.php'); exit; }

    $stmt_vo = $pdo->prepare("SELECT * FROM transfer_vehicle_options WHERE route_id = ? ORDER BY price ASC");
    $stmt_vo->execute([$route['id']]);
    $vehicles = $stmt_vo->fetchAll();

    $pdo->prepare("INSERT INTO leads (source_type, source_id) VALUES ('transfer', ?)")->execute([$route['id']]);
} catch (PDOException $e) {
    header('Location: antar-jemput.php'); exit;
}

$wa_number = getSetting('whatsapp_number', '6281234567890');
$wa_msg = "Halo Lombok Travel! Saya ingin memesan antar jemput *{$route['name']}* ({$route['origin']} → {$route['destination']}). Mohon informasi lebih lanjut.";
$wa_link = generateWaLink($wa_number, $wa_msg);

$page_title = e($route['name']) . ' - Antar Jemput Lombok | LombokTravel';
$page_description = "Layanan antar jemput {$route['origin']} ke {$route['destination']} di Lombok. Harga transparan, armada lengkap.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="bg-brand-950 text-white py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-xs text-brand-300" aria-label="Breadcrumb">
            <a href="index.php" class="hover:text-white transition">Home</a>
            <span>/</span>
            <a href="antar-jemput.php" class="hover:text-white transition">Antar Jemput</a>
            <span>/</span>
            <span class="text-white"><?= e($route['name']) ?></span>
        </nav>
    </div>
</div>

<div class="bg-slate-50 min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Hero Card -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-md">
                    <div class="h-64 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1596422846543-75c6fc197f0c?auto=format&fit=crop&w=1200&q=80"
                             alt="<?= e($route['name']) ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h1 class="font-outfit text-2xl font-extrabold text-slate-900 mb-4"><?= e($route['name']) ?></h1>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-brand-600"></span>
                                <span class="font-bold text-slate-700"><?= e($route['origin']) ?></span>
                            </div>
                            <div class="flex-1 flex items-center gap-1.5">
                                <div class="h-px flex-1 border-t border-dashed border-slate-300"></div>
                                <?php if (!empty($route['duration_estimate_label'])): ?>
                                    <span class="px-3 py-1 bg-slate-100 text-slate-500 text-xs font-semibold rounded-full whitespace-nowrap">
                                        ⏱ <?= e($route['duration_estimate_label']) ?>
                                    </span>
                                <?php endif; ?>
                                <div class="h-px flex-1 border-t border-dashed border-slate-300"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-700"><?= e($route['destination']) ?></span>
                                <span class="w-3 h-3 rounded-full bg-accent-500"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Options -->
                <?php if (!empty($vehicles)): ?>
                    <div class="bg-white rounded-3xl p-6 shadow-md">
                        <h2 class="font-outfit text-xl font-bold text-slate-900 mb-5">Pilihan Armada Kendaraan</h2>
                        <div class="space-y-3">
                            <?php foreach ($vehicles as $vi => $v):
                                $va_msg = "Halo Lombok Travel! Saya ingin memesan *{$v['vehicle_name']}* untuk rute *{$route['name']}* seharga *" . formatRupiah($v['price']) . "*. Mohon konfirmasi ketersediaan.";
                                $va_link = generateWaLink($wa_number, $va_msg);
                            ?>
                                <div class="flex items-center justify-between bg-slate-50 hover:bg-brand-50 border border-slate-200 hover:border-brand-200 rounded-2xl p-4 transition-all duration-200">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center text-2xl">
                                            🚗
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900"><?= e($v['vehicle_name']) ?></h4>
                                            <p class="text-xs text-slate-500">Harga per perjalanan (1 kendaraan)</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-outfit text-xl font-extrabold text-brand-700"><?= formatRupiah($v['price']) ?></p>
                                        <a href="<?= $va_link ?>" target="_blank"
                                            class="text-[11px] text-green-600 hover:text-green-800 font-bold mt-1 inline-block">Pesan Armada Ini →</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- What's Included -->
                <div class="bg-white rounded-3xl p-6 shadow-md">
                    <h2 class="font-outfit text-xl font-bold text-slate-900 mb-5">Yang Sudah Termasuk</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php
                        $included = [
                            '✅ Biaya Bensin / BBM',
                            '✅ Biaya Tol & Parkir',
                            '✅ Sopir Profesional & Berlisensi',
                            '✅ Kendaraan Ber-AC',
                            '✅ Penjemputan di Titik yang Disepakati',
                            '✅ Monitoring Jadwal Penerbangan',
                        ];
                        foreach ($included as $item):
                        ?>
                            <div class="flex items-center gap-2 text-sm text-slate-700">
                                <?= $item ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-5">
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden sticky top-28">
                    <div class="bg-gradient-to-r from-brand-700 to-brand-900 p-5 text-white">
                        <p class="text-xs text-brand-200 font-semibold uppercase mb-1">Layanan Antar Jemput</p>
                        <h3 class="font-outfit text-lg font-extrabold leading-snug"><?= e($route['name']) ?></h3>
                        <p class="text-brand-200 text-xs mt-2"><?= e($route['origin']) ?> → <?= e($route['destination']) ?></p>
                    </div>
                    <div class="p-5 space-y-3">
                        <a href="<?= $wa_link ?>" target="_blank"
                            class="block w-full py-3.5 bg-green-500 hover:bg-green-600 text-white font-extrabold rounded-2xl transition text-center text-sm shadow-lg">
                            💬 Pesan via WhatsApp Sekarang
                        </a>
                        <p class="text-[10px] text-slate-400 text-center">Respon cepat • Harga tetap & transparan</p>
                    </div>
                </div>

                <div class="bg-brand-50 border border-brand-100 rounded-2xl p-5">
                    <h4 class="font-bold text-brand-900 text-sm mb-3">⚡ Pemesanan Cepat</h4>
                    <p class="text-xs text-brand-700 leading-relaxed">
                        Hubungi kami sekarang dan informasikan: tanggal perjalanan, jumlah penumpang, titik penjemputan, dan nomor penerbangan (jika ada). Kami siap membantu 24 jam.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <a href="antar-jemput.php" class="inline-flex items-center gap-2 text-sm text-brand-700 hover:text-brand-900 font-semibold transition">
                &larr; Kembali ke Semua Rute Antar Jemput
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
