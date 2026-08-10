<?php
/**
 * Admin Panel - Pengaturan Profil & Password Admin
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Ensure username column exists in table schema
try {
    $pdo->exec("ALTER TABLE admin_users ADD COLUMN username VARCHAR(255) NULL AFTER id");
} catch (Exception $e) {
    // Column already exists or ignored
}

$admin_id = $_SESSION['admin_id'] ?? 0;
$error = '';
$success = '';

// Fetch current user
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ? LIMIT 1");
    $stmt->execute([$admin_id]);
    $user = $stmt->fetch();
    if (!$user) {
        header('Location: logout.php');
        exit;
    }
} catch (PDOException $e) {
    die("Kesalahan database: " . $e->getMessage());
}

$current_username = $user['username'] ?? $user['name'] ?? $user['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username)) {
        $error = 'Username tidak boleh kosong.';
    } elseif (empty($current_password)) {
        $error = 'Masukkan kata sandi saat ini untuk verifikasi keamanan.';
    } elseif (!password_verify($current_password, $user['password_hash'])) {
        $error = 'Kata sandi saat ini yang Anda masukkan salah.';
    } else {
        // Check username uniqueness if changed
        try {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE (username = ? OR name = ?) AND id != ?");
            $chk->execute([$username, $username, $admin_id]);
            if ($chk->fetchColumn() > 0) {
                $error = 'Username "' . e($username) . '" sudah digunakan oleh akun lain.';
            }
        } catch (PDOException $e) {
            // Ignore if column check failed
        }

        if (empty($error)) {
            // Check password change request
            $newHash = $user['password_hash'];
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $error = 'Kata sandi baru minimal harus 6 karakter.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'Konfirmasi kata sandi baru tidak cocok.';
                } else {
                    $newHash = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }

            if (empty($error)) {
                try {
                    $updateStmt = $pdo->prepare("UPDATE admin_users SET username = ?, name = ?, password_hash = ? WHERE id = ?");
                    $updateStmt->execute([$username, $username, $newHash, $admin_id]);
                } catch (PDOException $e) {
                    $updateStmt = $pdo->prepare("UPDATE admin_users SET name = ?, password_hash = ? WHERE id = ?");
                    $updateStmt->execute([$username, $newHash, $admin_id]);
                }

                // Update session info
                $_SESSION['admin_name'] = $username;
                $_SESSION['admin_username'] = $username;
                $current_username = $username;
                $user['password_hash'] = $newHash;

                $success = 'Profil username dan kata sandi berhasil diperbarui!';
            }
        }
    }
}
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="font-outfit text-2xl font-extrabold text-white">Pengaturan Akun Admin</h1>
        <p class="text-xs text-slate-400 mt-1">Ubah username dan kata sandi akses login panel admin Anda</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-2xl mb-6 flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-2xl mb-6 flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span><?= e($success) ?></span>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6">
        <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-xl space-y-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Ubah Username Admin
            </h2>

            <div>
                <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Username Login</label>
                <input type="text" name="username" id="username" required 
                    value="<?= e($current_username) ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
                <p class="text-[11px] text-slate-500 mt-1.5">Username ini digunakan saat Anda login ke panel admin.</p>
            </div>
        </div>

        <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-xl space-y-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-primary-400 border-b border-slate-850 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Ubah Kata Sandi (Opsional)
            </h2>

            <div>
                <label for="new_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Kata Sandi Baru</label>
                <input type="password" name="new_password" id="new_password" placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>

            <div>
                <label for="confirm_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Konfirmasi Kata Sandi Baru</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Ulangi kata sandi baru"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
        </div>

        <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-xl space-y-6 border-l-4 border-l-amber-500">
            <h2 class="text-sm font-bold uppercase tracking-wider text-amber-400 flex items-center gap-2">
                🔒 Verifikasi Keamanan
            </h2>
            <div>
                <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Kata Sandi Saat Ini (Wajib)</label>
                <input type="password" name="current_password" id="current_password" required placeholder="Masukkan kata sandi aktif Anda"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500/40 text-sm">
                <p class="text-[11px] text-slate-500 mt-1.5">Wajib memasukkan kata sandi saat ini untuk menyimpan segala perubahan.</p>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="dashboard.php" class="px-6 py-3 bg-slate-950 border border-slate-800 hover:bg-slate-900 text-slate-400 text-xs font-bold rounded-xl transition duration-200">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition duration-200 shadow-lg shadow-primary-600/20">
                Simpan Perubahan Akun
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
