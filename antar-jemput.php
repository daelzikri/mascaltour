<?php
/**
 * Public Page: Antar Jemput Bandara - Transfer Routes
 */
$page_title = 'Antar Jemput Bandara Lombok (LOP) & Pelabuhan Bangsal / Lembar | MascalTour';
$page_description = 'Layanan antar jemput resmi Bandara Internasional Lombok (LOP), Pelabuhan Lembar & Pelabuhan Bangsal ke hotel, villa, dan destinasi wisata Lombok. Harga fix tanpa nego.';
$page_keywords = 'antar jemput bandara lombok, transfer airport lombok, taksi bandara lombok, pelabuhan bangsal gili, pelabuhan lembar lombok, shuttle bandara lombok, mascaltour';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch transfer routes
try {
    $stmt = $pdo->query("
        SELECT tr.*,
               GROUP_CONCAT(tvo.vehicle_name ORDER BY tvo.price ASC SEPARATOR '|') as vehicle_names,
               GROUP_CONCAT(tvo.price ORDER BY tvo.price ASC SEPARATOR '|') as vehicle_prices,
               MIN(tvo.price) as min_price
        FROM transfer_routes tr
        LEFT JOIN transfer_vehicle_options tvo ON tvo.route_id = tr.id
        WHERE tr.status = 'published'
        GROUP BY tr.id
        ORDER BY tr.id ASC
    ");
    $routes = $stmt->fetchAll();
} catch (PDOException $e) {
    $routes = [];
}

$wa_number = getSetting('whatsapp_number', '6281234567890');
?>

<!-- Page Hero -->
<section class="relative bg-brand-950 text-white overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1596422846543-75c6fc197f0c?auto=format&fit=crop&w=1920&q=80"
             alt="Bandara Lombok"
             class="w-full h-full object-cover opacity-25">
        <div class="absolute inset-0 bg-gradient-to-r from-brand-950/90 via-brand-950/70 to-brand-900/40"></div>
    </div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 bg-accent-500/20 border border-accent-400/30 text-accent-300 text-xs font-semibold rounded-full px-4 py-1.5 mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                Antar Jemput Resmi Lombok
            </div>
            <h1 class="font-outfit text-4xl sm:text-5xl font-extrabold leading-tight mb-4">
                Transfer <span class="text-accent-400">Airport &amp; Pelabuhan</span> Lombok
            </h1>
            <p class="text-slate-300 text-lg leading-relaxed mb-8">
                Layanan antar jemput dari Bandara Internasional Lombok, Pelabuhan Lembar, dan Pelabuhan Bangsal — langsung ke hotel, villa, atau destinasi wisata pilihan Anda. Sopir profesional, tepat waktu, harga tetap tanpa negosiasi.
            </p>
            <!-- Feature chips -->
            <div class="flex flex-wrap gap-3">
                <span class="px-4 py-2 bg-white/10 backdrop-blur border border-white/20 text-white text-xs font-semibold rounded-full flex items-center gap-1.5">
                    ✓ Harga Fix / Tidak Nawar
                </span>
                <span class="px-4 py-2 bg-white/10 backdrop-blur border border-white/20 text-white text-xs font-semibold rounded-full flex items-center gap-1.5">
                    ✓ Penjemputan Tepat Waktu
                </span>
                <span class="px-4 py-2 bg-white/10 backdrop-blur border border-white/20 text-white text-xs font-semibold rounded-full flex items-center gap-1.5">
                    ✓ Armada Ber-AC & Terawat
                </span>
                <span class="px-4 py-2 bg-white/10 backdrop-blur border border-white/20 text-white text-xs font-semibold rounded-full flex items-center gap-1.5">
                    ✓ Termasuk BBM & Parkir
                </span>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="bg-white border-b border-slate-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <?php
            $steps = [
                ['icon' => '📱', 'title' => 'Pesan via WA', 'desc' => 'Hubungi kami dan informasikan rute, tanggal & jumlah penumpang'],
                ['icon' => '✅', 'title' => 'Konfirmasi Booking', 'desc' => 'Kami konfirmasi & kirim detail driver sebelum hari keberangkatan'],
                ['icon' => '🚗', 'title' => 'Driver Menjemput', 'desc' => 'Driver tiba tepat waktu di titik penjemputan yang disepakati'],
                ['icon' => '🏝️', 'title' => 'Sampai Tujuan', 'desc' => 'Diantar langsung ke destinasi dengan nyaman & aman'],
            ];
            foreach ($steps as $i => $step):
            ?>
                <div class="p-4">
                    <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center mx-auto text-3xl mb-4">
                        <?= $step['icon'] ?>
                    </div>
                    <div class="text-xs text-brand-600 font-bold uppercase tracking-wider mb-1">Langkah <?= $i + 1 ?></div>
                    <h3 class="font-outfit font-bold text-slate-900 text-base mb-1.5"><?= $step['title'] ?></h3>
                    <p class="text-slate-500 text-xs leading-relaxed"><?= $step['desc'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Routes Section -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="font-outfit text-3xl sm:text-4xl font-extrabold text-slate-900 mb-3">
                Rute Antar Jemput Tersedia
            </h2>
            <p class="text-slate-500 max-w-2xl mx-auto">Pilih rute sesuai kebutuhan Anda. Tersedia berbagai pilihan armada kendaraan untuk setiap rute perjalanan.</p>
        </div>

        <?php if (empty($routes)): ?>
            <div class="text-center py-16 text-slate-500">
                <p class="text-sm">Belum ada rute tersedia. Hubungi kami untuk informasi lebih lanjut.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($routes as $idx => $route):
                    $vehicle_names = !empty($route['vehicle_names']) ? explode('|', $route['vehicle_names']) : [];
                    $vehicle_prices = !empty($route['vehicle_prices']) ? explode('|', $route['vehicle_prices']) : [];

                    $wa_msg = "Halo Mascal Tour! Saya ingin memesan antar jemput rute *{$route['name']}* (dari {$route['origin']} ke {$route['destination']}). Mohon konfirmasi ketersediaan.";
                    $wa_link = generateWaLink($wa_number, $wa_msg);
                ?>
                    <div class="bg-white rounded-3xl shadow-md hover:shadow-xl transition-all duration-300 border border-slate-100 hover:-translate-y-0.5 p-6 flex flex-col md:flex-row gap-6">
                        <!-- Route Details -->
                        <div class="flex-1">
                            <h3 class="font-outfit text-xl font-extrabold text-slate-900 mb-2"><?= e($route['name']) ?></h3>

                            <!-- Route Arrow Display -->
                            <div class="flex items-center gap-2 mb-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                                    <span class="text-sm font-bold text-slate-700"><?= e($route['origin']) ?></span>
                                </div>
                                <div class="flex-1 flex items-center gap-1 text-slate-300">
                                    <div class="h-px flex-1 bg-slate-200"></div>
                                    <?php if (!empty($route['duration_estimate_label'])): ?>
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-semibold rounded-full whitespace-nowrap">
                                            ⏱ <?= e($route['duration_estimate_label']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <div class="h-px flex-1 bg-slate-200"></div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-bold text-slate-700"><?= e($route['destination']) ?></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-accent-500"></span>
                                </div>
                            </div>

                            <!-- Vehicle Options Table -->
                            <?php if (!empty($vehicle_names)): ?>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Pilihan Armada &amp; Harga</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <?php foreach ($vehicle_names as $vi => $vname): ?>
                                            <div class="flex items-center justify-between bg-brand-50 rounded-xl px-3 py-2.5 border border-brand-100">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-brand-600 text-sm">🚗</span>
                                                    <span class="text-xs font-semibold text-slate-700"><?= e($vname) ?></span>
                                                </div>
                                                <span class="text-xs font-extrabold text-brand-700 whitespace-nowrap"><?= formatRupiah((int)($vehicle_prices[$vi] ?? 0)) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- CTA Side -->
                        <div class="md:w-44 flex flex-col justify-center items-center gap-3 text-center shrink-0 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6">
                            <?php if (!empty($route['min_price'])): ?>
                                <div>
                                    <p class="text-[10px] text-slate-400 font-semibold uppercase">Mulai dari</p>
                                    <p class="font-outfit text-xl font-extrabold text-brand-700"><?= formatRupiah($route['min_price']) ?></p>
                                </div>
                            <?php endif; ?>
                            <a href="<?= $wa_link ?>" target="_blank"
                                class="w-full py-3 px-4 bg-green-500 hover:bg-green-600 text-white text-xs font-extrabold rounded-2xl transition flex items-center justify-center gap-1.5 shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.136.56 4.136 1.535 5.874L0 24l6.297-1.52C8.007 23.43 9.954 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.844 0-3.577-.488-5.088-1.339l-.366-.215-3.738.9.935-3.643-.233-.375A9.953 9.953 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                                Pesan Sekarang
                            </a>
                            <a href="antar-jemput-detail.php?slug=<?= urlencode($route['slug']) ?>"
                                class="text-xs text-brand-600 hover:underline font-semibold">Lihat Detail →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Info Section: Tips Jemput -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 bg-brand-50 text-brand-700 text-xs font-semibold rounded-full px-4 py-1.5 mb-5">
                    💡 Tips Berguna
                </div>
                <h2 class="font-outfit text-3xl font-extrabold text-slate-900 mb-6">Panduan Pemesanan Antar Jemput</h2>
                <div class="space-y-4">
                    <?php
                    $tips = [
                        ['no' => '01', 'title' => 'Pesan Minimal H-1 Kedatangan', 'desc' => 'Untuk memastikan ketersediaan driver dan kendaraan, kami sarankan pesan minimal sehari sebelum kedatangan.'],
                        ['no' => '02', 'title' => 'Informasikan Nomor Penerbangan', 'desc' => 'Informasikan nomor flight Anda agar driver dapat memantau jadwal landing dan siap di waktu yang tepat.'],
                        ['no' => '03', 'title' => 'Bagasi Besar = Konfirmasi Jumlah', 'desc' => 'Jika membawa banyak bagasi atau jumlah penumpang lebih dari 7 orang, hubungi kami agar kami siapkan armada yang sesuai.'],
                        ['no' => '04', 'title' => 'Pembayaran di Tempat / Transfer', 'desc' => 'Pembayaran bisa dilakukan tunai saat di kendaraan atau via transfer rekening kami sebelum keberangkatan.'],
                    ];
                    foreach ($tips as $tip):
                    ?>
                        <div class="flex gap-4">
                            <span class="font-outfit text-2xl font-extrabold text-brand-200 shrink-0 w-8"><?= $tip['no'] ?></span>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm mb-1"><?= $tip['title'] ?></h4>
                                <p class="text-slate-500 text-xs leading-relaxed"><?= $tip['desc'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="relative">
                <div class="rounded-3xl overflow-hidden shadow-2xl aspect-[4/3]">
                    <img src="https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=800&q=80"
                         alt="Armada Antar Jemput Lombok"
                         class="w-full h-full object-cover">
                </div>
                <!-- Floating Card -->
                <div class="absolute -bottom-5 -left-5 bg-white rounded-2xl shadow-xl p-4 flex items-center gap-3 border border-slate-100">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center text-2xl">⭐</div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Pelanggan Puas</p>
                        <p class="text-[10px] text-slate-400">500+ perjalanan / bulan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bg-gradient-to-r from-brand-700 to-brand-900 py-14">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="font-outfit text-3xl font-extrabold mb-3">Butuh Rute Lain atau Custom?</h2>
        <p class="text-brand-100 mb-7">Kami melayani rute ke seluruh penjuru Lombok. Hubungi kami untuk penawaran terbaik.</p>
        <a href="<?= generateWaLink($wa_number, 'Halo Mascal Tour! Saya membutuhkan layanan antar jemput. Mohon informasi rute dan harga.') ?>" target="_blank"
            class="inline-flex items-center gap-3 px-8 py-4 bg-white text-brand-800 font-extrabold rounded-2xl hover:bg-accent-50 transition shadow-xl text-sm">
            💬 Tanyakan Rute Custom via WhatsApp
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
