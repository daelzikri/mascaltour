<?php
/**
 * Admin Dashboard Home - Intuitive & User-Friendly for Non-Technical Admins
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch count statistics
try {
    // Packages Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM packages");
    $total_packages = $stmt->fetchColumn();

    // Vehicles Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM vehicles");
    $total_vehicles = $stmt->fetchColumn();

    // Routes Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM transfer_routes");
    $total_routes = $stmt->fetchColumn();

    // Leads/Clicks Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM leads");
    $total_leads = $stmt->fetchColumn();

    // Fetch Recent Leads
    $leads_stmt = $pdo->query("
        SELECT l.id, l.source_type, l.clicked_at,
               CASE 
                   WHEN l.source_type = 'package' THEN p.name
                   WHEN l.source_type = 'transfer' THEN tr.name
                   WHEN l.source_type = 'vehicle' THEN v.name
               END as item_name
        FROM leads l
        LEFT JOIN packages p ON l.source_type = 'package' AND l.source_id = p.id
        LEFT JOIN transfer_routes tr ON l.source_type = 'transfer' AND l.source_id = tr.id
        LEFT JOIN vehicles v ON l.source_type = 'vehicle' AND l.source_id = v.id
        ORDER BY l.clicked_at DESC LIMIT 5
    ");
    $recent_leads = $leads_stmt->fetchAll();
} catch (PDOException $e) {
    $total_packages = 0;
    $total_vehicles = 0;
    $total_routes = 0;
    $total_leads = 0;
    $recent_leads = [];
}
?>

<!-- Welcome Banner -->
<div class="mb-8 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 border border-slate-800 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 shadow-xl">
    <div>
        <span class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold rounded-full uppercase tracking-wider mb-2 inline-block">
            Panel Admin Pengisian Konten
        </span>
        <h1 class="font-outfit text-2xl md:text-3xl font-extrabold text-white">Selamat Datang, <?= e($_SESSION['admin_name']) ?>! 👋</h1>
        <p class="text-slate-400 text-xs sm:text-sm mt-1">Pilih menu di bawah ini untuk menambah atau mengubah data di website dengan mudah.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="<?= BASE_URL ?>" target="_blank" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-2">
            🌐 Lihat Website Utama
        </a>
        <a href="pengaturan.php" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-lg shadow-teal-900/30">
            ⚙️ Pengaturan Kontak &amp; WA
        </a>
    </div>
</div>

<!-- Quick Action Section (Tombol Aksi Cepat Pengisian Data) -->
<div class="mb-8 bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-lg">
    <h2 class="font-outfit text-sm font-bold uppercase tracking-wider text-primary-400 mb-4 flex items-center gap-2">
        <span>⚡</span> Aksi Cepat Tambah Konten Baru
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="paket/tambah.php" class="p-4 bg-slate-900 hover:bg-primary-600/20 border border-slate-800 hover:border-primary-500/40 rounded-2xl transition duration-200 group flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center font-bold text-xl group-hover:scale-110 transition shrink-0">
                🌴
            </div>
            <div>
                <span class="text-xs font-bold text-white block group-hover:text-primary-400 transition">+ Tambah Paket Wisata</span>
                <span class="text-[10px] text-slate-500">Form tour baru</span>
            </div>
        </a>

        <a href="rental/tambah.php" class="p-4 bg-slate-900 hover:bg-primary-600/20 border border-slate-800 hover:border-primary-500/40 rounded-2xl transition duration-200 group flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-xl group-hover:scale-110 transition shrink-0">
                🚗
            </div>
            <div>
                <span class="text-xs font-bold text-white block group-hover:text-primary-400 transition">+ Tambah Mobil Rental</span>
                <span class="text-[10px] text-slate-500">Form armada sewa</span>
            </div>
        </a>

        <a href="antar-jemput/tambah.php" class="p-4 bg-slate-900 hover:bg-primary-600/20 border border-slate-800 hover:border-primary-500/40 rounded-2xl transition duration-200 group flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-xl group-hover:scale-110 transition shrink-0">
                🚐
            </div>
            <div>
                <span class="text-xs font-bold text-white block group-hover:text-primary-400 transition">+ Tambah Rute Jemput</span>
                <span class="text-[10px] text-slate-500">Form transfer airport</span>
            </div>
        </a>

        <a href="faq/index.php" class="p-4 bg-slate-900 hover:bg-primary-600/20 border border-slate-800 hover:border-primary-500/40 rounded-2xl transition duration-200 group flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center font-bold text-xl group-hover:scale-110 transition shrink-0">
                ❓
            </div>
            <div>
                <span class="text-xs font-bold text-white block group-hover:text-primary-400 transition">+ Tambah FAQ</span>
                <span class="text-[10px] text-slate-500">Pertanyaan umum</span>
            </div>
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Packages Card -->
    <a href="paket/index.php" class="bg-slate-950 border border-slate-800 rounded-2xl p-6 flex items-center gap-5 hover:border-primary-500/40 transition duration-300 group">
        <div class="w-12 h-12 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center group-hover:bg-teal-500 group-hover:text-white transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Paket Wisata Aktif</p>
            <p class="text-3xl font-extrabold text-white mt-1"><?= $total_packages ?> <span class="text-xs text-primary-400 font-normal">Kelola →</span></p>
        </div>
    </a>

    <!-- Vehicles Card -->
    <a href="rental/index.php" class="bg-slate-950 border border-slate-800 rounded-2xl p-6 flex items-center gap-5 hover:border-primary-500/40 transition duration-300 group">
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M21 16V10a2 2 0 00-2-2h-3m-4 8H4" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mobil Rental</p>
            <p class="text-3xl font-extrabold text-white mt-1"><?= $total_vehicles ?> <span class="text-xs text-primary-400 font-normal">Kelola →</span></p>
        </div>
    </a>

    <!-- Routes Card -->
    <a href="antar-jemput/index.php" class="bg-slate-950 border border-slate-800 rounded-2xl p-6 flex items-center gap-5 hover:border-primary-500/40 transition duration-300 group">
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rute Antar Jemput</p>
            <p class="text-3xl font-extrabold text-white mt-1"><?= $total_routes ?> <span class="text-xs text-primary-400 font-normal">Kelola →</span></p>
        </div>
    </a>

    <!-- Leads Card -->
    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-6 flex items-center gap-5">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Klik WA</p>
            <p class="text-3xl font-extrabold text-white mt-1"><?= $total_leads ?></p>
        </div>
    </div>

</div>

<!-- Main Dashboard Grid Content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Recent Leads / WA Clicks List -->
    <div class="lg:col-span-2 bg-slate-950 border border-slate-800 rounded-2xl p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-outfit text-lg font-bold text-white">Inkuiri WhatsApp Terkini</h3>
                <p class="text-xs text-slate-500">Daftar pengunjung yang mengklik tombol booking WA di website</p>
            </div>
            <span class="text-xs bg-slate-900 border border-slate-800 px-3 py-1 rounded-lg text-slate-400">Terbaru</span>
        </div>

        <?php if (empty($recent_leads)): ?>
            <div class="text-center py-12 text-slate-600 border-2 border-dashed border-slate-800 rounded-xl">
                <p class="text-sm font-medium">Belum ada aktivitas pesan WhatsApp terdeteksi.</p>
                <p class="text-xs mt-1">Saat pelanggan mengklik tombol WA, riwayat akan terdaftar di sini.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                            <th class="pb-3 w-10">#</th>
                            <th class="pb-3">Tipe Layanan</th>
                            <th class="pb-3">Nama Produk / Rute</th>
                            <th class="pb-3 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-900">
                        <?php foreach ($recent_leads as $i => $lead): ?>
                            <tr class="hover:bg-slate-900/30 transition duration-150">
                                <td class="py-3.5 text-slate-500 font-medium"><?= $i + 1 ?></td>
                                <td class="py-3.5">
                                    <?php if ($lead['source_type'] === 'package'): ?>
                                        <span class="px-2 py-0.5 text-xs bg-teal-500/10 border border-teal-500/20 text-teal-400 rounded-full font-semibold">Paket Wisata</span>
                                    <?php elseif ($lead['source_type'] === 'vehicle'): ?>
                                        <span class="px-2 py-0.5 text-xs bg-blue-500/10 border border-blue-500/20 text-blue-400 rounded-full font-semibold">Rental Mobil</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 text-xs bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-full font-semibold">Antar Jemput</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 text-white font-semibold truncate max-w-[200px]">
                                    <?= e($lead['item_name'] ?? 'Item Terhapus (ID: ' . $lead['id'] . ')') ?>
                                </td>
                                <td class="py-3.5 text-slate-400 text-right text-xs">
                                    <?= date('d M Y - H:i', strtotime($lead['clicked_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Friendly Guide Box for Non-Technical Users -->
    <div class="space-y-6">
        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-6">
            <h3 class="font-outfit text-lg font-bold text-white mb-3 flex items-center gap-2">
                <span>💡</span> Panduan Pengisian Awam
            </h3>
            <div class="space-y-3 text-xs text-slate-400 leading-relaxed">
                <div class="p-3 bg-slate-900 rounded-xl border border-slate-850">
                    <p class="font-bold text-slate-200 mb-1">1. Tidak Perlu Mengetik Kode / URL</p>
                    <p>Sistem akan membuat link WhatsApp dan link halaman secara otomatis dari nama yang Anda ketik.</p>
                </div>
                <div class="p-3 bg-slate-900 rounded-xl border border-slate-850">
                    <p class="font-bold text-slate-200 mb-1">2. Upload Foto Mudah</p>
                    <p>Cukup pilih foto dari HP / Komputer Anda (JPG, PNG, WebP). Foto akan otomatis terpasang rapi.</p>
                </div>
                <div class="p-3 bg-slate-900 rounded-xl border border-slate-850">
                    <p class="font-bold text-slate-200 mb-1">3. Status Draft / Published</p>
                    <p>Pilih <strong>Draft</strong> jika masih ingin menyimpan sementara, atau <strong>Published</strong> agar langsung tampil di website.</p>
                </div>
            </div>
        </div>

        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-6">
            <h3 class="font-outfit text-lg font-bold text-white mb-4">Kontak Agen &amp; WA Active</h3>
            <div class="space-y-4 text-sm">
                <div>
                    <span class="text-xs text-slate-500 block uppercase font-semibold">Nomor WhatsApp Admin</span>
                    <span class="font-mono text-emerald-400 text-base font-bold"><?= e(getSetting('contact_whatsapp', getSetting('whatsapp_number', '-'))) ?></span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block uppercase font-semibold">Email</span>
                    <span class="text-white font-semibold"><?= e(getSetting('contact_email', getSetting('email', '-'))) ?></span>
                </div>
                <div class="pt-2 border-t border-slate-900 flex justify-between items-center text-xs">
                    <span class="text-slate-500">Ingin ganti nomor WA?</span>
                    <a href="pengaturan.php" class="text-primary-400 hover:underline font-bold">Ubah di sini →</a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
