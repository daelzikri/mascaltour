<?php
/**
 * Admin - Pengaturan Website
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

// Fetch current settings
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $settings = [];
}

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'site_name', 'site_tagline', 'site_description',
        'contact_phone', 'contact_whatsapp', 'contact_email',
        'address', 'address_maps_url',
        'social_instagram', 'social_facebook', 'social_tiktok',
        'about_text', 'hero_title', 'hero_subtitle',
    ];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($fields as $field) {
            $val = trim($_POST[$field] ?? '');
            $stmt->execute([$field, $val]);
        }

        // Sync legacy setting keys for full backward compatibility
        $legacy_sync = [
            'contact_whatsapp' => 'whatsapp_number',
            'contact_email' => 'email',
            'address' => 'office_address',
            'address_maps_url' => 'google_maps_embed_url',
            'hero_title' => 'hero_headline',
            'hero_subtitle' => 'hero_subheadline',
        ];
        foreach ($legacy_sync as $new_key => $old_key) {
            $val = trim($_POST[$new_key] ?? '');
            $stmt->execute([$old_key, $val]);
        }

        // Handle logo upload
        if (!empty($_FILES['site_logo']['name'])) {
            $logo_path = uploadImage($_FILES['site_logo'], 'settings');
            if ($logo_path) {
                $stmt->execute(['site_logo', $logo_path]);
            }
        }

        // Handle hero image upload
        if (!empty($_FILES['hero_image']['name'])) {
            $hero_path = uploadImage($_FILES['hero_image'], 'settings');
            if ($hero_path) {
                $stmt->execute(['hero_image', $hero_path]);
            }
        }

        $pdo->commit();

        // Re-fetch updated settings
        $stmt2 = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = [];
        while ($row = $stmt2->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $success = 'Pengaturan berhasil disimpan!';
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Gagal menyimpan pengaturan: ' . $e->getMessage();
    }
}

function get_setting(array $s, string $key, string $default = ''): string {
    $aliases = [
        'contact_whatsapp' => ['contact_whatsapp', 'whatsapp_number'],
        'contact_email' => ['contact_email', 'email'],
        'address' => ['address', 'office_address'],
        'address_maps_url' => ['address_maps_url', 'google_maps_embed_url'],
        'hero_title' => ['hero_title', 'hero_headline'],
        'hero_subtitle' => ['hero_subtitle', 'hero_subheadline'],
    ];
    if (isset($aliases[$key])) {
        foreach ($aliases[$key] as $k) {
            if (isset($s[$k]) && trim($s[$k]) !== '') {
                return $s[$k];
            }
        }
    }
    return $s[$key] ?? $default;
}
?>

<div class="mb-6">
    <h1 class="font-outfit text-2xl font-extrabold text-white">Pengaturan Website</h1>
    <p class="text-xs text-slate-400 mt-1">Konfigurasi identitas, kontak, dan penampilan umum website</p>
</div>

<?php if (!empty($success)): ?><div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-xl mb-6"><?= e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6"><?= e($error) ?></div><?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data" class="space-y-8">

    <!-- Identitas Website -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-900 pb-3">Identitas Website</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Website / Brand</label>
                <input type="text" name="site_name" value="<?= e(get_setting($settings, 'site_name', 'LombokTravel')) ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tagline</label>
                <input type="text" name="site_tagline" value="<?= e(get_setting($settings, 'site_tagline', 'Jelajahi Keindahan Lombok')) ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Deskripsi Website (Meta SEO)</label>
            <textarea name="site_description" rows="3"
                class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm"><?= e(get_setting($settings, 'site_description')) ?></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Logo Website (Upload)</label>
                <?php if (!empty($settings['site_logo'])): ?>
                    <div class="mb-2 flex items-center gap-2">
                        <img src="<?= BASE_URL . $settings['site_logo'] ?>" class="h-10 rounded bg-white p-1" alt="Logo">
                        <span class="text-xs text-slate-500">Logo Saat Ini</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="site_logo" accept="image/*"
                    class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 cursor-pointer">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tentang Kami (Teks Singkat)</label>
                <textarea name="about_text" rows="3"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm"><?= e(get_setting($settings, 'about_text')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-900 pb-3">Hero / Banner Beranda</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Judul Hero</label>
                <input type="text" name="hero_title" value="<?= e(get_setting($settings, 'hero_title', 'Jelajahi Lombok Bersama Kami')) ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Sub-Judul Hero</label>
                <input type="text" name="hero_subtitle" value="<?= e(get_setting($settings, 'hero_subtitle', 'Paket Wisata, Antar Jemput Bandara, Rental Mobil Terpercaya')) ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Gambar Hero / Background Beranda</label>
            <?php if (!empty($settings['hero_image'])): ?>
                <div class="mb-2">
                    <img src="<?= BASE_URL . $settings['hero_image'] ?>" class="h-32 rounded-xl object-cover border border-slate-800" alt="Hero">
                    <span class="text-xs text-slate-500 block mt-1">Gambar Hero Saat Ini</span>
                </div>
            <?php endif; ?>
            <input type="file" name="hero_image" accept="image/*"
                class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 cursor-pointer">
            <p class="text-[10px] text-slate-500 mt-1">Disarankan ukuran minimal 1920×800 px. Format: JPG, PNG, WebP.</p>
        </div>
    </div>

    <!-- Kontak -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-900 pb-3">Informasi Kontak</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">No. Telepon</label>
                <input type="text" name="contact_phone" value="<?= e(get_setting($settings, 'contact_phone', '+62 878 0000 0000')) ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">No. WhatsApp (format internasional)</label>
                <input type="text" name="contact_whatsapp" value="<?= e(get_setting($settings, 'contact_whatsapp', '6287800000000')) ?>" placeholder="6281234567890"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
                <p class="text-[10px] text-slate-500 mt-1">Contoh: 628123456789 (tanpa + atau spasi)</p>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Email</label>
                <input type="email" name="contact_email" value="<?= e(get_setting($settings, 'contact_email')) ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Alamat Lengkap</label>
                <textarea name="address" rows="3"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm"><?= e(get_setting($settings, 'address')) ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">URL Google Maps Embed</label>
                <textarea name="address_maps_url" rows="3" placeholder="https://maps.google.com/maps?q=..."
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm"><?= e(get_setting($settings, 'address_maps_url')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Social Media -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-900 pb-3">Media Sosial</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Instagram URL</label>
                <input type="url" name="social_instagram" value="<?= e(get_setting($settings, 'social_instagram')) ?>" placeholder="https://instagram.com/..."
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Facebook URL</label>
                <input type="url" name="social_facebook" value="<?= e(get_setting($settings, 'social_facebook')) ?>" placeholder="https://facebook.com/..."
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">TikTok URL</label>
                <input type="url" name="social_tiktok" value="<?= e(get_setting($settings, 'social_tiktok')) ?>" placeholder="https://tiktok.com/@..."
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="px-8 py-3.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-teal-900/30">Simpan Semua Pengaturan</button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
