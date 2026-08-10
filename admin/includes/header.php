<?php
/**
 * Admin Panel Header Layout
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Protect admin page
checkAdminAuth();

$current_script = $_SERVER['SCRIPT_NAME'];
$current_dir = basename(dirname($current_script));
$current_file = basename($current_script);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lombok Travel</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fbf7',
                            100: '#dcf6ed',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            950: '#042f2e',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Navigation Bar -->
    <div class="md:hidden flex justify-between items-center bg-slate-950 p-4 border-b border-slate-800">
        <span class="font-outfit text-xl font-extrabold text-white tracking-wide">
            Lombok<span class="text-primary-500">Travel</span>
        </span>
        <button id="mobile-menu-toggle" class="text-slate-400 hover:text-white focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
        </button>
    </div>

    <!-- Sidebar Wrapper (Hidden on mobile by default) -->
    <aside id="sidebar" class="hidden md:flex flex-col w-64 bg-slate-950 border-r border-slate-800 shrink-0 min-h-screen text-slate-300">
        <!-- Sidebar Brand Logo -->
        <div class="p-6 border-b border-slate-900 flex items-center justify-between">
            <span class="font-outfit text-2xl font-extrabold text-white tracking-wide">
                Lombok<span class="text-primary-500">Travel</span>
            </span>
            <span class="text-[10px] bg-primary-500/10 text-primary-500 font-bold border border-primary-500/20 px-2 py-0.5 rounded-full">v1.0</span>
        </div>

        <!-- Admin Profile Info -->
        <a href="<?= BASE_URL ?>admin/profil.php" class="px-6 py-4 border-b border-slate-900 bg-slate-950/50 flex items-center gap-3 hover:bg-slate-900 transition">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary-600 to-teal-400 flex items-center justify-center text-white font-bold font-outfit">
                <?= strtoupper(substr(e($_SESSION['admin_name']), 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-semibold text-white truncate"><?= e($_SESSION['admin_name']) ?></p>
                <p class="text-xs text-slate-500 capitalize truncate"><?= e($_SESSION['admin_role']) ?></p>
            </div>
        </a>

        <!-- Sidebar Navigation Menu -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <!-- Dashboard Link -->
            <a href="<?= BASE_URL ?>admin/dashboard.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 <?= ($current_file === 'dashboard.php') ? 'bg-primary-600 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <div class="pt-4 pb-2 px-4 text-xs font-bold uppercase tracking-wider text-slate-600">Wisata</div>

            <!-- Kategori Paket Link -->
            <a href="<?= BASE_URL ?>admin/kategori/index.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 <?= ($current_dir === 'kategori') ? 'bg-primary-600 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Kategori Paket</span>
            </a>

            <!-- Paket Wisata Link -->
            <a href="<?= BASE_URL ?>admin/paket/index.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 <?= ($current_dir === 'paket') ? 'bg-primary-600 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                <span>Paket Wisata</span>
            </a>

            <div class="pt-4 pb-2 px-4 text-xs font-bold uppercase tracking-wider text-slate-600">Transportasi</div>

            <!-- Antar Jemput Link -->
            <a href="<?= BASE_URL ?>admin/antar-jemput/index.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 <?= ($current_dir === 'antar-jemput') ? 'bg-primary-600 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Antar Jemput Rute</span>
            </a>

            <!-- Kategori Kendaraan Link -->
            <a href="<?= BASE_URL ?>admin/rental/kategori-kendaraan.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 <?= ($current_file === 'kategori-kendaraan.php') ? 'bg-primary-600 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span>Kategori Kendaraan</span>
            </a>

            <!-- Rental Kendaraan Link -->
            <a href="<?= BASE_URL ?>admin/rental/index.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 <?= ($current_dir === 'rental' && $current_file !== 'kategori-kendaraan.php') ? 'bg-primary-600 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M21 16V10a2 2 0 00-2-2h-3m-4 8H4" />
                </svg>
                <span>Rental Kendaraan</span>
            </a>

            <div class="pt-4 pb-2 px-4 text-xs font-bold uppercase tracking-wider text-slate-600">Konten & System</div>



            <!-- FAQ Link -->
            <a href="<?= BASE_URL ?>admin/faq/index.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 <?= ($current_dir === 'faq') ? 'bg-primary-600 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>FAQ</span>
            </a>

            <!-- Akun & Security Link -->
            <a href="<?= BASE_URL ?>admin/profil.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 <?= ($current_file === 'profil.php') ? 'bg-primary-600 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Akun &amp; Password</span>
            </a>

            <!-- Pengaturan Link -->
            <a href="<?= BASE_URL ?>admin/pengaturan.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 <?= ($current_file === 'pengaturan.php') ? 'bg-primary-600 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Pengaturan Situs</span>
            </a>

            <div class="pt-4 border-t border-slate-900"></div>

            <!-- Logout Link -->
            <a href="<?= BASE_URL ?>admin/logout.php" 
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 text-red-400 hover:bg-red-500/10 hover:text-red-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar</span>
            </a>
        </nav>
    </aside>

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
        <!-- Top Workspace Bar (Desktop) -->
        <header class="hidden md:flex justify-between items-center bg-slate-950 p-6 border-b border-slate-800">
            <div>
                <h2 class="font-outfit text-xl font-bold text-white">Panel Pengelolaan Konten</h2>
                <p class="text-xs text-slate-500">Kelola paket liburan dan armada rental mobil dengan mudah</p>
            </div>
            <div class="flex items-center gap-3 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl text-slate-300 text-sm">
                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                <span>Terhubung sebagai: <strong><?= e($_SESSION['admin_name']) ?></strong></span>
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 p-6 md:p-8 bg-slate-900 overflow-y-auto">
