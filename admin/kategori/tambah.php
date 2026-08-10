<?php
/**
 * Admin - Tambah Kategori Wilayah Wisata
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    
    if (empty($name)) {
        $error = 'Nama Kategori / Wilayah wajib diisi.';
    } else {
        try {
            $slug = generateSlug($name);
            
            // Check if slug already exists
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
            $check_stmt->execute([$slug]);
            if ($check_stmt->fetchColumn() > 0) {
                // Append unique identifier to slug
                $slug = $slug . '-' . time();
            }

            // Handle Image Upload
            $image_path = null;
            if (!empty($_FILES['image']['name'])) {
                $image_path = uploadImage($_FILES['image'], 'categories');
            }

            // Insert Category
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, image, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $image_path, $sort_order]);

            header('Location: index.php?success=1');
            exit;
        } catch (Exception $e) {
            $error = 'Gagal menyimpan kategori: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-6">
    <a href="index.php" class="text-xs text-primary-400 hover:underline flex items-center gap-1 mb-2">
        &larr; Kembali ke Daftar Kategori
    </a>
    <h1 class="font-outfit text-2xl font-extrabold text-white">Tambah Kategori Wilayah</h1>
    <p class="text-xs text-slate-400 mt-1">Buat wilayah wisata baru untuk mengelompokkan paket liburan</p>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<!-- Form -->
<div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 max-w-2xl shadow-xl">
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Kategori / Wilayah</label>
            <input type="text" name="name" id="name" required placeholder="Contoh: Gili Islands, Sembalun"
                class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition text-sm">
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Deskripsi Wilayah</label>
            <textarea name="description" id="description" rows="4" placeholder="Jelaskan daya tarik wisata wilayah ini secara ringkas..."
                class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition text-sm"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="sort_order" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Urutan Tampil (Prioritas)</label>
                <input type="number" name="sort_order" id="sort_order" min="0" value="0" required
                    class="block w-full px-4 py-3 bg-slate-900 border border-slate-850 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Foto Kategori (Opsional)</label>
                <div class="relative flex items-center justify-center w-full">
                    <label for="image-upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-800 border-dashed rounded-xl cursor-pointer bg-slate-900 hover:bg-slate-850 transition duration-200">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs text-slate-400"><span class="font-bold text-primary-500">Pilih berkas</span> JPG, PNG, WEBP</p>
                            <p id="file-name" class="text-[10px] text-slate-500 mt-1">Ukuran maks: 2MB</p>
                        </div>
                        <input id="image-upload" type="file" name="image" class="hidden" accept="image/*" onchange="previewFile()">
                    </label>
                </div>
            </div>
        </div>

        <div id="preview-container" class="hidden pt-2">
            <p class="text-xs font-semibold uppercase text-slate-400 mb-2">Pratinjau Foto</p>
            <div class="w-48 h-32 rounded-xl overflow-hidden border border-slate-800 bg-slate-900">
                <img id="image-preview" src="#" class="w-full h-full object-cover">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-900 flex justify-end gap-3">
            <a href="index.php" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-bold rounded-xl transition duration-200">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition duration-200">
                Simpan Kategori
            </button>
        </div>

    </form>
</div>

<script>
    function previewFile() {
        const fileInput = document.getElementById('image-upload');
        const fileName = document.getElementById('file-name');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('image-preview');

        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
