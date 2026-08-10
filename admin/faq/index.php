<?php
/**
 * Admin - FAQ Management
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';

// Delete FAQ
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $fid = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM faqs WHERE id = ?")->execute([$fid]);
    } catch (PDOException $e) {}
    header('Location: index.php?success=1');
    exit;
}

// Add FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faq'])) {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (empty($question) || empty($answer)) {
        $error = 'Pertanyaan dan jawaban wajib diisi.';
    } else {
        try {
            $pdo->prepare("INSERT INTO faqs (question, answer, sort_order) VALUES (?, ?, ?)")->execute([$question, $answer, $sort_order]);
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menambah FAQ: ' . $e->getMessage();
        }
    }
}

// Update FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_faq'])) {
    $eid = (int)($_POST['faq_id'] ?? 0);
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if ($eid > 0 && !empty($question) && !empty($answer)) {
        try {
            $pdo->prepare("UPDATE faqs SET question=?, answer=?, sort_order=? WHERE id=?")->execute([$question, $answer, $sort_order, $eid]);
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui FAQ: ' . $e->getMessage();
        }
    }
}

// Fetch all FAQs
try {
    $faqs = $pdo->query("SELECT * FROM faqs ORDER BY sort_order ASC, id DESC")->fetchAll();
} catch (PDOException $e) {
    $faqs = [];
}

$edit_faq = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($faqs as $f) {
        if ($f['id'] == $eid) { $edit_faq = $f; break; }
    }
}
?>

<div class="mb-6">
    <h1 class="font-outfit text-2xl font-extrabold text-white">FAQ (Pertanyaan Umum)</h1>
    <p class="text-xs text-slate-400 mt-1">Kelola pertanyaan yang sering diajukan pelanggan di halaman website</p>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-xl mb-6">FAQ berhasil diperbarui!</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6"><?= e($error) ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Add/Edit FAQ Form -->
    <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl h-fit">
        <h2 class="font-outfit text-base font-bold text-white mb-6"><?= $edit_faq ? 'Ubah FAQ' : 'Tambah FAQ Baru' ?></h2>
        <form action="" method="POST" class="space-y-4">
            <?php if ($edit_faq): ?>
                <input type="hidden" name="update_faq" value="1">
                <input type="hidden" name="faq_id" value="<?= $edit_faq['id'] ?>">
            <?php else: ?>
                <input type="hidden" name="add_faq" value="1">
            <?php endif; ?>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Pertanyaan</label>
                <input type="text" name="question" required placeholder="Mis: Apakah harga sudah termasuk BBM?"
                    value="<?= $edit_faq ? e($edit_faq['question']) : '' ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jawaban</label>
                <textarea name="answer" rows="5" required placeholder="Tulis jawaban lengkap di sini..."
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm"><?= $edit_faq ? e($edit_faq['answer']) : '' ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Urutan Tampil</label>
                <input type="number" name="sort_order" min="0" value="<?= $edit_faq ? $edit_faq['sort_order'] : '0' ?>"
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/40 text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition text-xs">
                    <?= $edit_faq ? 'Simpan Perubahan' : 'Tambah FAQ' ?>
                </button>
                <?php if ($edit_faq): ?>
                    <a href="index.php" class="py-3 px-4 bg-slate-900 border border-slate-800 text-slate-400 text-xs font-bold rounded-xl transition hover:bg-slate-800">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- FAQ List -->
    <div class="lg:col-span-2">
        <?php if (empty($faqs)): ?>
            <div class="bg-slate-950 border border-slate-800 rounded-2xl text-center py-16 text-slate-500">
                <p class="text-sm font-semibold">Belum ada data FAQ.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($faqs as $i => $f): ?>
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-5 h-5 rounded-full bg-primary-500/20 text-primary-400 text-[10px] flex items-center justify-center font-bold shrink-0"><?= $i + 1 ?></span>
                                    <h3 class="font-bold text-white text-sm"><?= e($f['question']) ?></h3>
                                </div>
                                <p class="text-slate-400 text-xs leading-relaxed mt-2 pl-7"><?= nl2br(e($f['answer'])) ?></p>
                            </div>
                            <div class="flex gap-1.5 shrink-0">
                                <a href="index.php?edit=<?= $f['id'] ?>" class="p-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <a href="index.php?delete=<?= $f['id'] ?>" class="p-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition" title="Hapus" onclick="return confirm('Hapus FAQ ini?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
