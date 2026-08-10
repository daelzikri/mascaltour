<?php
/**
 * Public Website Navbar Component
 */
$current_page = basename($_SERVER['SCRIPT_NAME']);
$site_name = getSetting('site_name', 'LombokTravel');
$site_logo = getSetting('site_logo');
$wa_number = getSetting('contact_whatsapp', getSetting('whatsapp_number', '6281234567890'));
$wa_url = generateWaLink($wa_number, "Halo {$site_name}, saya ingin berkonsultasi mengenai paket wisata / sewa mobil.");
?>
<header class="sticky top-0 z-50 w-full bg-white/80 backdrop-blur-md border-b border-slate-200/60 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Brand Logo & Site Name -->
            <a href="<?= BASE_URL ?>index.php" class="flex items-center gap-3 group">
                <?php if (!empty($site_logo)): ?>
                    <img src="<?= BASE_URL . e($site_logo) ?>" alt="<?= e($site_name) ?>" class="h-10 w-auto object-contain">
                <?php endif; ?>
                <span class="font-outfit text-xl font-extrabold text-brand-950 tracking-wide">
                    <?= e($site_name) ?>
                </span>
            </a>

            <!-- Desktop Navigation Menu -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="<?= BASE_URL ?>index.php" 
                    class="text-sm font-semibold transition duration-200 <?= ($current_page === 'index.php') ? 'text-brand-600' : 'text-slate-600 hover:text-brand-600' ?>">
                    Home
                </a>
                <a href="<?= BASE_URL ?>paket-wisata.php" 
                    class="text-sm font-semibold transition duration-200 <?= ($current_page === 'paket-wisata.php' || $current_page === 'paket-wisata-detail.php') ? 'text-brand-600' : 'text-slate-600 hover:text-brand-600' ?>">
                    Paket Wisata
                </a>
                <a href="<?= BASE_URL ?>antar-jemput.php" 
                    class="text-sm font-semibold transition duration-200 <?= ($current_page === 'antar-jemput.php' || $current_page === 'antar-jemput-detail.php') ? 'text-brand-600' : 'text-slate-600 hover:text-brand-600' ?>">
                    Antar Jemput
                </a>
                <a href="<?= BASE_URL ?>rental.php" 
                    class="text-sm font-semibold transition duration-200 <?= ($current_page === 'rental.php' || $current_page === 'rental-detail.php') ? 'text-brand-600' : 'text-slate-600 hover:text-brand-600' ?>">
                    Rental Mobil
                </a>
            </nav>

            <!-- CTA Call Button -->
            <div class="hidden md:flex items-center gap-4">
                <a href="<?= $wa_url ?>" target="_blank" 
                    class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl transition duration-200 shadow-md shadow-brand-600/20 transform hover:-translate-y-[1px] flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Tanya Admin (WA)
                </a>
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="md:hidden flex items-center">
                <button id="public-menu-toggle" class="text-slate-600 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/25 p-2 rounded-xl transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Menu (Hidden by default) -->
    <div id="public-mobile-menu" class="hidden md:hidden bg-white border-b border-slate-200 transition-all duration-200">
        <div class="px-4 pt-2 pb-6 space-y-2">
            <a href="<?= BASE_URL ?>index.php" 
                class="block px-4 py-3 text-base font-semibold rounded-xl transition <?= ($current_page === 'index.php') ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600' ?>">
                Home
            </a>
            <a href="<?= BASE_URL ?>paket-wisata.php" 
                class="block px-4 py-3 text-base font-semibold rounded-xl transition <?= ($current_page === 'paket-wisata.php' || $current_page === 'paket-wisata-detail.php') ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600' ?>">
                Paket Wisata
            </a>
            <a href="<?= BASE_URL ?>antar-jemput.php" 
                class="block px-4 py-3 text-base font-semibold rounded-xl transition <?= ($current_page === 'antar-jemput.php' || $current_page === 'antar-jemput-detail.php') ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600' ?>">
                Antar Jemput
            </a>
            <a href="<?= BASE_URL ?>rental.php" 
                class="block px-4 py-3 text-base font-semibold rounded-xl transition <?= ($current_page === 'rental.php' || $current_page === 'rental-detail.php') ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600' ?>">
                Rental Mobil
            </a>
            <div class="pt-4 px-4">
                <a href="<?= $wa_url ?>" target="_blank" 
                    class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-center shadow-md shadow-brand-600/10 block transition duration-200">
                    Hubungi WhatsApp
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('public-menu-toggle');
        const mobileMenu = document.getElementById('public-mobile-menu');
        
        if (toggleBtn && mobileMenu) {
            toggleBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
</script>
