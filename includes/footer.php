<?php
/**
 * Public Website Footer Component
 */
$site_name = getSetting('site_name', 'Lombok Travel Agency');
$site_logo = getSetting('site_logo');
$about_text = getSetting('about_text', 'Layanan agen perjalanan dan rental mobil terpercaya di Pulau Lombok. Menyediakan paket liburan terbaik dengan armada kendaraan prima demi kenyamanan petualangan Anda.');
$phone = getSetting('contact_phone');
$wa_number = getSetting('contact_whatsapp', getSetting('whatsapp_number', '6281234567890'));
$email = getSetting('contact_email', getSetting('email', 'info@lomboktravel.com'));
$address = getSetting('address', getSetting('office_address', 'Jl. Raya Senggigi No. 12, Senggigi, Batu Layar, Lombok Barat, NTB'));
$instagram = getSetting('social_instagram');
$facebook = getSetting('social_facebook');
$tiktok = getSetting('social_tiktok');
$raw_maps_url = getSetting('address_maps_url', getSetting('google_maps_embed_url'));
$maps_url = getGoogleMapsEmbedUrl($raw_maps_url, $address);
?>
<footer class="bg-slate-950 text-slate-400 pt-16 pb-8 border-t border-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            
            <!-- Column 1: Profile & Brand -->
            <div class="space-y-5">
                <a href="<?= BASE_URL ?>index.php" class="flex items-center gap-3">
                    <?php if (!empty($site_logo)): ?>
                        <img src="<?= BASE_URL . e($site_logo) ?>" alt="<?= e($site_name) ?>" class="h-12 w-auto object-contain">
                    <?php endif; ?>
                    <span class="font-outfit text-2xl font-extrabold text-white tracking-wide">
                        <?= e($site_name) ?>
                    </span>
                </a>
                <p class="text-sm leading-relaxed">
                    <?= e($about_text) ?>
                </p>
                <div class="p-4 bg-slate-900 rounded-2xl border border-slate-800/80 inline-flex items-center gap-3">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                    <div class="text-[11px]">
                        <p class="text-white font-bold uppercase tracking-wider"><?= e($site_name) ?></p>
                        <p class="text-slate-500">Official Travel & Transport Service</p>
                    </div>
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div>
                <h4 class="font-outfit text-white font-bold text-base mb-6 tracking-wide">Tautan Langsung</h4>
                <ul class="space-y-3.5 text-sm">
                    <li><a href="<?= BASE_URL ?>index.php" class="hover:text-white transition duration-200">Home &amp; Beranda</a></li>
                    <li><a href="<?= BASE_URL ?>paket-wisata.php" class="hover:text-white transition duration-200">Katalog Paket Wisata</a></li>
                    <li><a href="<?= BASE_URL ?>antar-jemput.php" class="hover:text-white transition duration-200">Layanan Antar Jemput</a></li>
                    <li><a href="<?= BASE_URL ?>rental.php" class="hover:text-white transition duration-200">Rental Kendaraan</a></li>
                </ul>
            </div>

            <!-- Column 3: Contact Info -->
            <div>
                <h4 class="font-outfit text-white font-bold text-base mb-6 tracking-wide">Hubungi Kami</h4>
                <ul class="space-y-4 text-sm">
                    <?php if (!empty($address)): ?>
                    <li class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span><?= e($address) ?></span>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($phone)): ?>
                    <li class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="hover:text-white transition duration-200"><?= e($phone) ?></a>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($wa_number)): ?>
                    <li class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $wa_number) ?>" target="_blank" class="hover:text-white transition duration-200 font-semibold font-mono">WA: <?= e($wa_number) ?></a>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($email)): ?>
                    <li class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <a href="mailto:<?= e($email) ?>" class="hover:text-white transition duration-200"><?= e($email) ?></a>
                    </li>
                    <?php endif; ?>

                    <!-- Social Media Links -->
                    <?php if (!empty($instagram) || !empty($facebook) || !empty($tiktok)): ?>
                    <li class="pt-2 flex items-center gap-4">
                        <?php if (!empty($instagram)): 
                            $ig_url = (strpos($instagram, 'http') === 0) ? $instagram : 'https://instagram.com/' . ltrim($instagram, '@');
                        ?>
                            <a href="<?= e($ig_url) ?>" target="_blank" class="text-slate-400 hover:text-brand-400 transition" title="Instagram">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($facebook)): 
                            $fb_url = (strpos($facebook, 'http') === 0) ? $facebook : 'https://facebook.com/' . $facebook;
                        ?>
                            <a href="<?= e($fb_url) ?>" target="_blank" class="text-slate-400 hover:text-brand-400 transition" title="Facebook">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($tiktok)): 
                            $tt_url = (strpos($tiktok, 'http') === 0) ? $tiktok : 'https://tiktok.com/@' . ltrim($tiktok, '@');
                        ?>
                            <a href="<?= e($tt_url) ?>" target="_blank" class="text-slate-400 hover:text-brand-400 transition" title="TikTok">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.82.56-1.31 1.52-1.31 2.52.02 1.16.65 2.25 1.65 2.82.97.56 2.19.61 3.19.14 1.02-.47 1.74-1.47 1.86-2.58.04-1.27.02-2.54.02-3.81V.02z"/></svg>
                            </a>
                        <?php endif; ?>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Column 4: Google Maps Embed -->
            <div class="h-full min-h-[200px]">
                <h4 class="font-outfit text-white font-bold text-base mb-6 tracking-wide">Lokasi Kantor Kami</h4>
                <div class="w-full h-44 rounded-2xl overflow-hidden border border-slate-800 shadow-inner">
                    <?php if (!empty($maps_url)): ?>
                        <iframe src="<?= e($maps_url) ?>" class="w-full h-full border-0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    <?php else: ?>
                        <div class="w-full h-full bg-slate-900 flex items-center justify-center text-xs text-slate-600">Maps belum disetting</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Divider -->
        <div class="pt-8 border-t border-slate-900 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs">
            <p>&copy; <?= date('Y') ?> <?= e($site_name) ?>. Seluruh hak cipta dilindungi undang-undang.</p>
            <div class="flex gap-4">
                <a href="#" class="hover:text-white transition duration-200">Kebijakan Privasi</a>
                <a href="#" class="hover:text-white transition duration-200">Syarat &amp; Ketentuan</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
