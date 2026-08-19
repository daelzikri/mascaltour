        </main>
        
        <!-- System Footer -->
        <footer class="bg-slate-950 p-6 border-t border-slate-800 text-center text-xs text-slate-500">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <p>&copy; <?= date('Y') ?> Mascal Tour. Hak Cipta Dilindungi.</p>
                <p>Dikembangkan dengan PHP Native & Tailwind CSS</p>
            </div>
        </footer>
    </div>

    <!-- Script for mobile menu interaction & upload validation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('sidebar');
            
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                    sidebar.classList.toggle('flex');
                    sidebar.classList.toggle('fixed');
                    sidebar.classList.toggle('inset-0');
                    sidebar.classList.toggle('z-40');
                    sidebar.classList.toggle('top-[57px]'); // Height of mobile top navbar
                });
            }
        });

        // Global Client-Side File Validation (Max 2MB)
        document.addEventListener('change', function(e) {
            if (e.target && e.target.type === 'file') {
                const files = e.target.files;
                const maxSize = 2 * 1024 * 1024; // 2MB
                for (let i = 0; i < files.length; i++) {
                    if (files[i].size > maxSize) {
                        alert('Ukuran file "' + files[i].name + '" terlalu besar (Maksimal 2 MB).\nFile akan otomatis dikonversi ke format WebP.');
                        e.target.value = ''; // Reset file input
                        return;
                    }
                }
            }
        });
    </script>
</body>
</html>
