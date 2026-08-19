<?php
/**
 * Admin Login Page
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Silakan masukkan username dan password Anda.';
    } else {
        try {
            // Check username (supports username, email, or name column for maximum compatibility)
            $user = false;
            try {
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ? OR name = ? LIMIT 1");
                $stmt->execute([$username, $username, $username]);
                $user = $stmt->fetch();
            } catch (PDOException $ex) {
                // Fallback if 'username' column does not exist yet
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? OR name = ? LIMIT 1");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
            }

            if ($user && password_verify($password, $user['password_hash'])) {
                loginAdmin($user);
                header('Location: ' . BASE_URL . 'admin/dashboard.php');
                exit;
            } else {
                $error = 'Username atau password yang Anda masukkan salah.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Mascal Tour</title>

    <!-- Favicon Icons & App Manifest -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon.svg" />
    <link rel="shortcut icon" href="<?= BASE_URL ?>favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MascalTour" />
    <link rel="manifest" href="<?= BASE_URL ?>site.webmanifest" />
    <meta name="theme-color" content="#ffffff" />
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
            background: linear-gradient(135deg, #021515 0%, #042f2e 100%);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 text-slate-100">

    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <h1 class="font-outfit text-3xl font-extrabold text-white tracking-wide">
                Mascal<span class="text-primary-500">Tour</span>
            </h1>
            <p class="text-slate-400 mt-2 text-sm font-medium">Panel Pengelola Konten & Rental</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl shadow-emerald-950/20">
            <h2 class="font-outfit text-xl font-bold text-white mb-6">Masuk Ke Akun Anda</h2>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6 flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Username Admin</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="username" id="username" required 
                            class="block w-full pl-11 pr-4 py-3 bg-slate-950/50 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/45 focus:border-primary-500 transition duration-200" 
                            placeholder="Masukkan username" 
                            value="<?= isset($_POST['username']) ? e($_POST['username']) : '' ?>">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" required 
                            class="block w-full pl-11 pr-4 py-3 bg-slate-950/50 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/45 focus:border-primary-500 transition duration-200" 
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" 
                    class="w-full py-3 px-4 bg-gradient-to-r from-primary-600 to-teal-500 hover:from-primary-700 hover:to-teal-600 text-white font-bold rounded-xl transition duration-200 transform hover:-translate-y-[1px] focus:outline-none focus:ring-2 focus:ring-teal-500/40 shadow-lg shadow-teal-900/30">
                    Masuk Panel Admin
                </button>
            </form>
        </div>
        
        <p class="text-center text-xs text-slate-500 mt-8">
            &copy; <?= date('Y') ?> Mascal Tour. Hak Cipta Dilindungi.
        </p>
    </div>

</body>
</html>
