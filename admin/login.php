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
    // CSRF or rate-limiting checks could go here
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Silakan masukkan email dan password Anda.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Successfully verified, establish admin session
                loginAdmin($user);
                header('Location: ' . BASE_URL . 'admin/dashboard.php');
                exit;
            } else {
                $error = 'Email atau password yang Anda masukkan salah.';
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
    <title>Login Admin - Lombok Travel</title>
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
                Lombok<span class="text-primary-500">Travel</span>
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
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" required 
                            class="block w-full pl-11 pr-4 py-3 bg-slate-950/50 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/45 focus:border-primary-500 transition duration-200" 
                            placeholder="nama@email.com" 
                            value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>">
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
            &copy; <?= date('Y') ?> Lombok Travel Agency. Hak Cipta Dilindungi.
        </p>
    </div>

</body>
</html>
